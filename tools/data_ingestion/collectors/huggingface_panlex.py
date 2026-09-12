"""Récolte ciblée des lignes PanLex pour sbd, stj et sym.

Le snapshot lbourdois/panlex contient ~24,6 millions de lignes. Ce collecteur
n'en télécharge pas le CSV complet : il utilise l'endpoint Dataset Viewer
`/filter` sur la colonne `639-3` et ne récupère que les lignes correspondant aux
codes ISO du projet.

Le RAW reste local sous data/raw/. Aucune donnée n'est validée linguistiquement
ni approuvée automatiquement pour publication ou entraînement.
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
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface" / "panlex"
VIEWER_BASE = "https://datasets-server.huggingface.co"
USER_AGENT = "langue-san-data-ingestion/1.0 (+panlex-target-harvest)"
PAGE_SIZE = 100
RETRY_STATUS = {429, 500, 502, 503, 504}


class PanLexHarvestError(RuntimeError):
    """Erreur contrôlée pendant la récolte ciblée PanLex."""


def load_target(path: Path = CONFIG_PATH) -> dict[str, Any]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    targets = payload.get("lexical_subsets") if isinstance(payload, dict) else None
    if not isinstance(targets, list):
        raise PanLexHarvestError("config/huggingface_harvest.yaml invalide : lexical_subsets absent.")
    for item in targets:
        if (
            isinstance(item, dict)
            and item.get("family") == "panlex_snapshot"
            and item.get("enabled", False)
        ):
            return item
    raise PanLexHarvestError("Aucune cible PanLex activée.")


def _request_filter(
    session: requests.Session,
    *,
    dataset: str,
    config: str,
    split: str,
    column: str,
    iso: str,
    offset: int,
    length: int = PAGE_SIZE,
    max_retries: int = 7,
) -> dict[str, Any]:
    where = f'"{column}"=\'{iso}\''
    delay = 1.0
    last_error: Exception | None = None

    for attempt in range(max_retries + 1):
        try:
            response = session.get(
                f"{VIEWER_BASE}/filter",
                params={
                    "dataset": dataset,
                    "config": config,
                    "split": split,
                    "where": where,
                    "offset": offset,
                    "length": min(PAGE_SIZE, max(1, length)),
                },
                timeout=90,
                headers={"User-Agent": USER_AGENT},
            )
            if response.status_code in RETRY_STATUS:
                retry_after = response.headers.get("Retry-After")
                if retry_after:
                    try:
                        delay = max(delay, float(retry_after))
                    except ValueError:
                        pass
                if attempt >= max_retries:
                    response.raise_for_status()
                print(
                    f"HTTP {response.status_code} PanLex/{iso} offset={offset}; "
                    f"nouvel essai dans {delay:.0f}s..."
                )
                time.sleep(min(delay, 60.0))
                delay = min(delay * 2, 60.0)
                continue

            response.raise_for_status()
            payload = response.json()
            if not isinstance(payload, dict):
                raise PanLexHarvestError("Réponse /filter invalide.")
            return payload
        except (requests.RequestException, ValueError) as exc:
            last_error = exc
            if attempt >= max_retries:
                break
            time.sleep(min(delay, 60.0))
            delay = min(delay * 2, 60.0)

    raise PanLexHarvestError(
        f"Échec /filter PanLex pour {iso} offset={offset}: {last_error}"
    )


def _existing_state(path: Path) -> tuple[int, set[int]]:
    if not path.exists():
        return 0, set()
    indices: set[int] = set()
    valid_lines = 0
    with path.open("r", encoding="utf-8") as handle:
        for line in handle:
            line = line.strip()
            if not line:
                continue
            try:
                item = json.loads(line)
            except json.JSONDecodeError:
                continue
            valid_lines += 1
            row_idx = item.get("row_idx")
            if isinstance(row_idx, int):
                indices.add(row_idx)
    return valid_lines, indices


def probe_iso(
    target: dict[str, Any],
    iso: str,
    *,
    session: requests.Session,
) -> dict[str, Any]:
    payload = _request_filter(
        session,
        dataset=str(target["repo_id"]),
        config=str(target["config"]),
        split=str(target["split"]),
        column=str(target["filter_column"]),
        iso=iso,
        offset=0,
        length=1,
    )
    rows = payload.get("rows", [])
    return {
        "iso_639_3": iso,
        "variety": target.get("iso_codes", {}).get(iso),
        "num_rows_total_reported": payload.get("num_rows_total"),
        "partial": bool(payload.get("partial", False)),
        "first_row_available": isinstance(rows, list) and bool(rows),
    }


def probe(
    target: dict[str, Any],
    iso_codes: list[str],
    *,
    session: requests.Session | None = None,
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    return [probe_iso(target, iso, session=client) for iso in iso_codes]


def harvest_iso(
    target: dict[str, Any],
    iso: str,
    *,
    session: requests.Session,
    output_root: Path = RAW_ROOT,
    request_delay: float = 0.5,
) -> dict[str, Any]:
    variety = str(target.get("iso_codes", {}).get(iso) or "unknown")
    output_dir = output_root / iso
    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / "train.jsonl"

    resumed_count, existing_indices = _existing_state(output_path)
    offset = resumed_count
    if resumed_count:
        print(
            f"Reprise détectée pour PanLex/{iso}: {resumed_count} lignes déjà locales, "
            f"reprise à offset={offset}."
        )

    total_expected: int | None = None
    partial_seen = False
    new_count = 0
    mode = "a" if resumed_count else "w"

    with output_path.open(mode, encoding="utf-8") as handle:
        while True:
            payload = _request_filter(
                session,
                dataset=str(target["repo_id"]),
                config=str(target["config"]),
                split=str(target["split"]),
                column=str(target["filter_column"]),
                iso=iso,
                offset=offset,
            )
            rows = payload.get("rows", [])
            if not isinstance(rows, list):
                raise PanLexHarvestError(f"Champ rows invalide pour PanLex/{iso}.")

            if total_expected is None and isinstance(payload.get("num_rows_total"), int):
                total_expected = int(payload["num_rows_total"])
            partial_seen = partial_seen or bool(payload.get("partial", False))

            if not rows:
                break

            for item in rows:
                if not isinstance(item, dict) or not isinstance(item.get("row"), dict):
                    continue
                row_idx = item.get("row_idx")
                if isinstance(row_idx, int) and row_idx in existing_indices:
                    continue
                record = {
                    "source": "huggingface",
                    "repo_id": target.get("repo_id"),
                    "family": "panlex_snapshot",
                    "config": target.get("config"),
                    "split": target.get("split"),
                    "row_idx": row_idx,
                    "iso_639_3": iso,
                    "variety": variety,
                    "license": target.get("license"),
                    "rights_status": target.get("rights_status"),
                    "snapshot_date": target.get("snapshot_date"),
                    "validation_status": "external_unverified",
                    "source_row": item["row"],
                }
                handle.write(json.dumps(record, ensure_ascii=False) + "\n")
                new_count += 1
                if isinstance(row_idx, int):
                    existing_indices.add(row_idx)

            offset += len(rows)
            if total_expected is not None and offset >= total_expected:
                break
            if len(rows) < PAGE_SIZE:
                break
            if request_delay > 0:
                time.sleep(request_delay)

    final_count = resumed_count + new_count
    return {
        "iso_639_3": iso,
        "variety": variety,
        "rows_written": final_count,
        "rows_new": new_count,
        "rows_resumed": resumed_count,
        "num_rows_total_reported": total_expected,
        "partial_seen": partial_seen,
        "complete_by_count": total_expected is None or final_count == total_expected,
        "output": str(output_path),
    }


def harvest(
    target: dict[str, Any],
    iso_codes: list[str],
    *,
    output_root: Path = RAW_ROOT,
    session: requests.Session | None = None,
    request_delay: float = 0.5,
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    return [
        harvest_iso(
            target,
            iso,
            session=client,
            output_root=output_root,
            request_delay=request_delay,
        )
        for iso in iso_codes
    ]


def write_summary(
    *,
    target: dict[str, Any],
    results: list[dict[str, Any]],
    probe_only: bool,
    output_root: Path = RAW_ROOT,
) -> Path:
    output_root.mkdir(parents=True, exist_ok=True)
    name = "panlex_probe_summary.json" if probe_only else "panlex_harvest_summary.json"
    path = output_root / name
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "repo_id": target.get("repo_id"),
        "snapshot_date": target.get("snapshot_date"),
        "license": target.get("license"),
        "probe_only": probe_only,
        "publication_approved": False,
        "training_approved": False,
        "results": results,
    }
    path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return path


def main() -> None:
    parser = argparse.ArgumentParser(description="Récolter les vocabulaires PanLex sbd/stj/sym sans télécharger 1,28 Go")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--iso", nargs="*", help="Codes ciblés; défaut = sbd stj sym")
    parser.add_argument("--probe-only", action="store_true")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    parser.add_argument("--request-delay", type=float, default=0.5)
    args = parser.parse_args()

    target = load_target(args.config)
    available = {str(key) for key in target.get("iso_codes", {})}
    iso_codes = [str(item).lower() for item in (args.iso or sorted(available))]
    unknown = sorted(set(iso_codes) - available)
    if unknown:
        raise PanLexHarvestError("Codes ISO non configurés : " + ", ".join(unknown))

    if args.probe_only:
        results = probe(target, iso_codes)
        summary = write_summary(target=target, results=results, probe_only=True, output_root=args.output_root)
        print("Sonde PanLex :")
        for item in results:
            total = item.get("num_rows_total_reported")
            print(
                f"- {item['iso_639_3']} ({item['variety']}): total={total if total is not None else '?'}, "
                f"partial={item['partial']}, première_ligne={item['first_row_available']}"
            )
        print("Mode probe-only : aucun vocabulaire complet n'a été récolté.")
        print(f"Résumé : {summary}")
        return

    results = harvest(
        target,
        iso_codes,
        output_root=args.output_root,
        request_delay=max(0.0, args.request_delay),
    )
    summary = write_summary(target=target, results=results, probe_only=False, output_root=args.output_root)
    print("Récolte PanLex :")
    for item in results:
        expected = item.get("num_rows_total_reported")
        print(
            f"- {item['iso_639_3']} ({item['variety']}): {item['rows_written']} lignes "
            f"(attendu={expected if expected is not None else '?'}, nouvelles={item['rows_new']}, "
            f"repris={item['rows_resumed']}, partial={item['partial_seen']})"
        )
    print(f"Résumé : {summary}")


if __name__ == "__main__":
    main()
