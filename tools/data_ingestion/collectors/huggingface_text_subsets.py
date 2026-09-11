"""Récolte locale de petits sous-ensembles texte Hugging Face déjà inspectés.

Le collecteur utilise l'API Dataset Viewer `/rows` afin de récupérer uniquement
les configs/splits SAN ciblés, par pages de 100 lignes maximum. Il conserve les
lignes source avec leur index et leurs métadonnées de provenance.

Cette étape est technique : aucune donnée n'est validée linguistiquement et
aucune autorisation ML n'est accordée automatiquement.
"""

from __future__ import annotations

import argparse
import json
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


def _get_rows(
    session: requests.Session,
    *,
    dataset: str,
    config: str,
    split: str,
    offset: int,
    length: int = PAGE_SIZE,
    timeout: int = 90,
) -> dict[str, Any]:
    response = session.get(
        f"{VIEWER_BASE}/rows",
        params={
            "dataset": dataset,
            "config": config,
            "split": split,
            "offset": offset,
            "length": min(PAGE_SIZE, max(1, length)),
        },
        timeout=timeout,
        headers={"User-Agent": USER_AGENT},
    )
    response.raise_for_status()
    payload = response.json()
    if not isinstance(payload, dict):
        raise HuggingFaceTextHarvestError(f"Réponse /rows invalide pour {dataset}/{config}/{split}.")
    return payload


def harvest_split(
    target: dict[str, Any],
    split: str,
    *,
    session: requests.Session,
    output_root: Path = RAW_ROOT,
) -> dict[str, Any]:
    repo_id = str(target["repo_id"])
    config = str(target["config"])
    family = str(target["family"])
    target_dir = output_root / family / config
    target_dir.mkdir(parents=True, exist_ok=True)
    output_path = target_dir / f"{split}.jsonl"

    offset = 0
    total_expected: int | None = None
    partial_seen = False
    written = 0

    with output_path.open("w", encoding="utf-8") as handle:
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

            for item in rows:
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
                    "validation_status": "external_unverified",
                    "source_row": row,
                }
                handle.write(json.dumps(record, ensure_ascii=False) + "\n")
                written += 1

            offset += len(rows)
            if total_expected is not None and offset >= total_expected:
                break
            if len(rows) < PAGE_SIZE:
                break

    return {
        "repo_id": repo_id,
        "family": family,
        "config": config,
        "split": split,
        "rows_written": written,
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
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    results: list[dict[str, Any]] = []
    for target in targets:
        for split in target.get("splits", []):
            results.append(
                harvest_split(target, str(split), session=client, output_root=output_root)
            )
    return results


def write_summary(results: list[dict[str, Any]], output_root: Path = RAW_ROOT) -> Path:
    output_root.mkdir(parents=True, exist_ok=True)
    path = output_root / "huggingface_text_harvest_summary.json"
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "result_count": len(results),
        "rows_written_total": sum(int(item.get("rows_written") or 0) for item in results),
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
    args = parser.parse_args()

    targets = select_targets(load_targets(args.config), args.target)
    results = harvest_targets(targets, output_root=args.output_root)
    summary_path = write_summary(results, args.output_root)

    print(f"Sous-ensembles récoltés : {len(results)}")
    for item in results:
        expected = item.get("num_rows_total_reported")
        expected_text = str(expected) if expected is not None else "?"
        print(
            f"- {item['family']}/{item['config']}/{item['split']}: "
            f"{item['rows_written']} lignes (attendu={expected_text}, partial={item['partial_seen']})"
        )
    print(f"Résumé : {summary_path}")


if __name__ == "__main__":
    main()
