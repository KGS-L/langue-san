"""Récolte locale de petits sous-ensembles texte Hugging Face déjà inspectés.

Le collecteur utilise l'API Dataset Viewer `/rows` afin de récupérer uniquement
les configs/splits SAN ciblés, par pages de 100 lignes maximum. Il conserve les
lignes source avec leur index et leurs métadonnées de provenance.

Avant un téléchargement complet, `--probe-only` interroge seulement la première
ligne de chaque split pour obtenir `num_rows_total` et le statut `partial`.

Le collecteur tolère les limitations temporaires du Hub (HTTP 429/5xx) avec
retry/backoff et reprend automatiquement un fichier JSONL partiellement récolté
sans recommencer depuis zéro.

Cette étape est technique : aucune donnée n'est validée linguistiquement et
aucune autorisation ML n'est accordée automatiquement.
"""

from __future__ import annotations

import argparse
import json
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
import yaml


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "huggingface_harvest.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface"
VIEWER_BASE = "https://datasets-server.huggingface.co"
USER_AGENT = "langue-san-data-ingestion/1.0 (+hf-target-harvest)"
PAGE_SIZE = 100
DEFAULT_REQUEST_DELAY = 0.5
DEFAULT_MAX_RETRIES = 8
RETRYABLE_STATUS_CODES = {429, 500, 502, 503, 504}


class HuggingFaceTextHarvestError(RuntimeError):
    """Erreur contrôlée pendant la récolte d'un sous-ensemble texte."""


def load_targets(path: Path = CONFIG_PATH) -> list[dict[str, Any]]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    targets = payload.get("text_subsets") if isinstance(payload, dict) else None
    if not isinstance(targets, list):
        raise HuggingFaceTextHarvestError("config/huggingface_harvest.yaml invalide : text_subsets absent.")
    return [item for item in targets if isinstance(item, dict) and item.get("enabled", False)]


def select_targets(targets: list[dict[str, Any]], keys: list[str] | None) -> list[dict[str, Any]]:
    if not keys:
        return targets
    wanted = set(keys)
    selected = [item for item in targets if str(item.get("key")) in wanted]
    missing = sorted(wanted - {str(item.get("key")) for item in selected})
    if missing:
        raise HuggingFaceTextHarvestError("Cibles inconnues : " + ", ".join(missing))
    return selected


def _retry_after_seconds(response: Any, attempt: int) -> float:
    headers = getattr(response, "headers", {}) or {}
    raw = headers.get("Retry-After") if hasattr(headers, "get") else None
    if raw is not None:
        try:
            return max(0.0, float(raw))
        except (TypeError, ValueError):
            pass
    return min(60.0, 2.0 ** attempt)


def _get_rows(
    session: requests.Session,
    *,
    dataset: str,
    config: str,
    split: str,
    offset: int,
    length: int = PAGE_SIZE,
    timeout: int = 90,
    max_retries: int = DEFAULT_MAX_RETRIES,
) -> dict[str, Any]:
    params = {
        "dataset": dataset,
        "config": config,
        "split": split,
        "offset": offset,
        "length": min(PAGE_SIZE, max(1, length)),
    }

    for attempt in range(max_retries + 1):
        response = session.get(
            f"{VIEWER_BASE}/rows",
            params=params,
            timeout=timeout,
            headers={"User-Agent": USER_AGENT},
        )
        status_code = int(getattr(response, "status_code", 200) or 200)
        if status_code not in RETRYABLE_STATUS_CODES:
            response.raise_for_status()
            payload = response.json()
            if not isinstance(payload, dict):
                raise HuggingFaceTextHarvestError(
                    f"Réponse /rows invalide pour {dataset}/{config}/{split}."
                )
            return payload

        if attempt >= max_retries:
            response.raise_for_status()

        wait_seconds = _retry_after_seconds(response, attempt)
        print(
            f"HTTP {status_code} pour {dataset}/{config}/{split} offset={offset}; "
            f"nouvel essai dans {wait_seconds:g}s ({attempt + 1}/{max_retries})."
        )
        time.sleep(wait_seconds)

    raise HuggingFaceTextHarvestError(
        f"Échec inattendu des retries pour {dataset}/{config}/{split} offset={offset}."
    )


def probe_split(
    target: dict[str, Any],
    split: str,
    *,
    session: requests.Session,
) -> dict[str, Any]:
    repo_id = str(target["repo_id"])
    config = str(target["config"])
    family = str(target["family"])
    payload = _get_rows(
        session,
        dataset=repo_id,
        config=config,
        split=split,
        offset=0,
        length=1,
    )
    rows = payload.get("rows", [])
    first_row_idx = None
    if isinstance(rows, list) and rows and isinstance(rows[0], dict):
        first_row_idx = rows[0].get("row_idx")
    return {
        "repo_id": repo_id,
        "family": family,
        "config": config,
        "split": split,
        "num_rows_total_reported": payload.get("num_rows_total"),
        "partial": bool(payload.get("partial", False)),
        "first_row_available": bool(rows),
        "first_row_idx": first_row_idx,
    }


def probe_targets(
    targets: list[dict[str, Any]],
    *,
    session: requests.Session | None = None,
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    results: list[dict[str, Any]] = []
    for target in targets:
        for split in target.get("splits", []):
            results.append(probe_split(target, str(split), session=client))
    return results


def _resume_state(path: Path) -> tuple[int, int]:
    """Retourne (lignes_valides, prochain_offset) pour un JSONL partiel."""

    if not path.exists() or path.stat().st_size == 0:
        return 0, 0

    count = 0
    row_indices: list[int] = []
    with path.open("r", encoding="utf-8") as handle:
        for line_no, line in enumerate(handle, start=1):
            if not line.strip():
                continue
            try:
                record = json.loads(line)
            except json.JSONDecodeError as exc:
                raise HuggingFaceTextHarvestError(
                    f"Fichier partiel invalide {path} à la ligne {line_no}. "
                    "Supprimer ce fichier pour recommencer proprement."
                ) from exc
            count += 1
            row_idx = record.get("row_idx") if isinstance(record, dict) else None
            if isinstance(row_idx, int):
                row_indices.append(row_idx)

    next_offset = (max(row_indices) + 1) if row_indices else count
    return count, next_offset


def harvest_split(
    target: dict[str, Any],
    split: str,
    *,
    session: requests.Session,
    output_root: Path = RAW_ROOT,
    request_delay: float = DEFAULT_REQUEST_DELAY,
) -> dict[str, Any]:
    repo_id = str(target["repo_id"])
    config = str(target["config"])
    family = str(target["family"])
    target_dir = output_root / family / config
    target_dir.mkdir(parents=True, exist_ok=True)
    output_path = target_dir / f"{split}.jsonl"

    existing_count, offset = _resume_state(output_path)
    resumed = existing_count > 0
    total_expected: int | None = None
    partial_seen = False
    written = existing_count
    new_rows_written = 0

    if resumed:
        print(
            f"Reprise détectée pour {family}/{config}/{split}: "
            f"{existing_count} lignes déjà locales, reprise à offset={offset}."
        )

    mode = "a" if resumed else "w"
    with output_path.open(mode, encoding="utf-8") as handle:
        while True:
            payload = _get_rows(
                session,
                dataset=repo_id,
                config=config,
                split=split,
                offset=offset,
            )
            rows = payload.get("rows", [])
            if not isinstance(rows, list):
                raise HuggingFaceTextHarvestError(f"Champ rows invalide pour {repo_id}/{config}/{split}.")

            if total_expected is None and isinstance(payload.get("num_rows_total"), int):
                total_expected = int(payload["num_rows_total"])
            partial_seen = partial_seen or bool(payload.get("partial", False))

            if not rows:
                break

            api_rows_consumed = 0
            for item in rows:
                api_rows_consumed += 1
                if not isinstance(item, dict):
                    continue
                row = item.get("row")
                if not isinstance(row, dict):
                    continue
                record = {
                    "source": "huggingface",
                    "repo_id": repo_id,
                    "family": family,
                    "config": config,
                    "split": split,
                    "row_idx": item.get("row_idx"),
                    "iso_639_3": target.get("iso_639_3"),
                    "variety": target.get("variety"),
                    "license": target.get("license"),
                    "rights_status": target.get("rights_status"),
                    "domain": target.get("domain"),
                    "publication_approved": bool(target.get("publication_approved", False)),
                    "training_approved": bool(target.get("training_approved", False)),
                    "validation_status": "external_unverified",
                    "source_row": row,
                }
                handle.write(json.dumps(record, ensure_ascii=False) + "\n")
                written += 1
                new_rows_written += 1

            handle.flush()
            offset += api_rows_consumed
            if total_expected is not None and offset >= total_expected:
                break
            if len(rows) < PAGE_SIZE:
                break
            if request_delay > 0:
                time.sleep(request_delay)

    return {
        "repo_id": repo_id,
        "family": family,
        "config": config,
        "split": split,
        "rows_written": written,
        "rows_written_this_run": new_rows_written,
        "resumed_from_existing_rows": existing_count,
        "num_rows_total_reported": total_expected,
        "partial_seen": partial_seen,
        "complete_by_count": total_expected is None or written == total_expected,
        "output": str(output_path),
    }


def harvest_targets(
    targets: list[dict[str, Any]],
    *,
    output_root: Path = RAW_ROOT,
    session: requests.Session | None = None,
    request_delay: float = DEFAULT_REQUEST_DELAY,
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    results: list[dict[str, Any]] = []
    for target in targets:
        for split in target.get("splits", []):
            results.append(
                harvest_split(
                    target,
                    str(split),
                    session=client,
                    output_root=output_root,
                    request_delay=max(0.0, request_delay),
                )
            )
    return results


def write_probe_summary(results: list[dict[str, Any]], output_root: Path = RAW_ROOT) -> Path:
    output_root.mkdir(parents=True, exist_ok=True)
    path = output_root / "huggingface_text_probe_summary.json"
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "probe_only": True,
        "content_harvested": False,
        "results": results,
    }
    path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return path


def write_summary(results: list[dict[str, Any]], output_root: Path = RAW_ROOT) -> Path:
    output_root.mkdir(parents=True, exist_ok=True)
    path = output_root / "huggingface_text_harvest_summary.json"
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "result_count": len(results),
        "rows_written_total": sum(int(item.get("rows_written") or 0) for item in results),
        "rows_written_this_run_total": sum(
            int(item.get("rows_written_this_run") or 0) for item in results
        ),
        "all_complete_by_count": all(bool(item.get("complete_by_count")) for item in results),
        "any_partial": any(bool(item.get("partial_seen")) for item in results),
        "publication_approved": False,
        "training_approved": False,
        "results": results,
    }
    path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return path


def main() -> None:
    parser = argparse.ArgumentParser(description="Récolter les sous-ensembles texte SAN approuvés pour collecte locale")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--target", nargs="*", help="Clés ciblées; défaut = toutes les cibles activées")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    parser.add_argument(
        "--probe-only",
        action="store_true",
        help="Lire une seule ligne par split pour connaître le volume avant récolte complète.",
    )
    parser.add_argument(
        "--request-delay",
        type=float,
        default=DEFAULT_REQUEST_DELAY,
        help="Pause entre pages Dataset Viewer en secondes (défaut: 0.5).",
    )
    args = parser.parse_args()

    targets = select_targets(load_targets(args.config), args.target)

    if args.probe_only:
        results = probe_targets(targets)
        summary_path = write_probe_summary(results, args.output_root)
        print(f"Splits sondés : {len(results)}")
        for item in results:
            total = item.get("num_rows_total_reported")
            total_text = str(total) if total is not None else "?"
            print(
                f"- {item['family']}/{item['config']}/{item['split']}: "
                f"total={total_text}, partial={item['partial']}, première_ligne={item['first_row_available']}"
            )
        print("Mode probe-only : aucun corpus complet n'a été récolté.")
        print(f"Résumé : {summary_path}")
        return

    results = harvest_targets(
        targets,
        output_root=args.output_root,
        request_delay=max(0.0, args.request_delay),
    )
    summary_path = write_summary(results, args.output_root)

    print(f"Sous-ensembles récoltés : {len(results)}")
    for item in results:
        expected = item.get("num_rows_total_reported")
        expected_text = str(expected) if expected is not None else "?"
        resume_text = ""
        if item.get("resumed_from_existing_rows"):
            resume_text = f", repris={item['resumed_from_existing_rows']}"
        print(
            f"- {item['family']}/{item['config']}/{item['split']}: "
            f"{item['rows_written']} lignes (attendu={expected_text}, "
            f"nouvelles={item['rows_written_this_run']}{resume_text}, partial={item['partial_seen']})"
        )
    print(f"Résumé : {summary_path}")


if __name__ == "__main__":
    main()
