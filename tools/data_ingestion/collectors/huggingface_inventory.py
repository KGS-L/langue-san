"""Inventaire des datasets Hugging Face associés aux variétés SAN.

Cette étape ne télécharge AUCUN contenu linguistique. Elle interroge uniquement
les métadonnées publiques du Hugging Face Hub afin de repérer les datasets qui
se déclarent associés aux codes ISO du projet :

- sbd : San Maka / Southern Samo
- stj : San Matya
- sym : San Maya

Chaque dataset découvert reste ``resource_review_required`` jusqu'à examen de sa
fiche, de sa source d'origine, de sa licence réelle et de son contenu.
"""

from __future__ import annotations

import argparse
import csv
import json
from collections import Counter
from datetime import date, datetime, timezone
from pathlib import Path
from typing import Any, Iterable

from huggingface_hub import HfApi


REPO_ROOT = Path(__file__).resolve().parents[3]
RAW_OUTPUT_DIR = REPO_ROOT / "data" / "raw" / "huggingface"
TARGET_VARIETIES = {
    "sbd": "maka",
    "stj": "matya",
    "sym": "maya",
}

CSV_FIELDS = (
    "repo_id",
    "matched_iso_codes",
    "matched_varieties",
    "license",
    "review_status",
    "gated",
    "private",
    "downloads",
    "likes",
    "last_modified",
    "languages",
    "modalities",
    "formats",
    "tasks",
    "size_categories",
    "url",
)


class HuggingFaceInventoryError(RuntimeError):
    """Erreur contrôlée de l'inventaire Hugging Face."""


def _get(obj: Any, *names: str, default: Any = None) -> Any:
    for name in names:
        if isinstance(obj, dict) and name in obj:
            return obj[name]
        if hasattr(obj, name):
            return getattr(obj, name)
    return default


def _jsonable(value: Any) -> Any:
    if value is None or isinstance(value, (str, int, float, bool)):
        return value
    if isinstance(value, (datetime, date)):
        return value.isoformat()
    if isinstance(value, dict):
        return {str(key): _jsonable(item) for key, item in value.items()}
    if isinstance(value, (list, tuple, set)):
        return [_jsonable(item) for item in value]
    if hasattr(value, "to_dict"):
        try:
            return _jsonable(value.to_dict())
        except Exception:  # pragma: no cover - garde-fou pour objets tiers
            pass
    if hasattr(value, "__dict__"):
        return {
            str(key): _jsonable(item)
            for key, item in vars(value).items()
            if not str(key).startswith("_")
        }
    return str(value)


def _prefixed_values(tags: Iterable[str], prefix: str) -> list[str]:
    values = []
    marker = f"{prefix}:"
    for tag in tags:
        if tag.startswith(marker):
            value = tag[len(marker) :].strip()
            if value and value not in values:
                values.append(value)
    return sorted(values)


def _card_data_dict(info: Any) -> dict[str, Any]:
    raw = _jsonable(_get(info, "card_data", "cardData", default={}))
    return raw if isinstance(raw, dict) else {}


def _license_from_info(info: Any, tags: list[str], card_data: dict[str, Any]) -> str | None:
    tagged = _prefixed_values(tags, "license")
    if tagged:
        return tagged[0]
    card_license = card_data.get("license")
    if isinstance(card_license, list):
        return ",".join(str(item) for item in card_license if item) or None
    if card_license:
        return str(card_license)
    return None


def normalize_dataset_info(info: Any, matched_iso: str) -> dict[str, Any]:
    """Normalise les métadonnées d'un DatasetInfo sans juger le contenu."""

    repo_id = str(_get(info, "id", default="") or "").strip()
    if not repo_id:
        raise HuggingFaceInventoryError("DatasetInfo sans identifiant Hugging Face.")

    tags = sorted({str(tag) for tag in (_get(info, "tags", default=[]) or []) if tag})
    card_data = _card_data_dict(info)
    license_name = _license_from_info(info, tags, card_data)

    languages = _prefixed_values(tags, "language")
    if not languages:
        raw_languages = card_data.get("language") or card_data.get("languages") or []
        if isinstance(raw_languages, str):
            raw_languages = [raw_languages]
        if isinstance(raw_languages, list):
            languages = sorted({str(item) for item in raw_languages if item})

    modalities = _prefixed_values(tags, "modality")
    formats = _prefixed_values(tags, "format")
    tasks = sorted(
        set(_prefixed_values(tags, "task_categories"))
        | set(_prefixed_values(tags, "task_ids"))
    )
    size_categories = _prefixed_values(tags, "size_categories")

    return {
        "repo_id": repo_id,
        "url": f"https://huggingface.co/datasets/{repo_id}",
        "matched_iso_codes": [matched_iso],
        "matched_varieties": [TARGET_VARIETIES.get(matched_iso, "unknown")],
        "license": license_name,
        "review_status": "resource_review_required" if license_name else "rights_review_required",
        "gated": _jsonable(_get(info, "gated")),
        "private": bool(_get(info, "private", default=False)),
        "downloads": _get(info, "downloads"),
        "likes": _get(info, "likes"),
        "created_at": _jsonable(_get(info, "created_at", "createdAt")),
        "last_modified": _jsonable(_get(info, "last_modified", "lastModified")),
        "sha": _get(info, "sha"),
        "languages": languages,
        "modalities": modalities,
        "formats": formats,
        "tasks": tasks,
        "size_categories": size_categories,
        "tags": tags,
        "card_data": card_data,
        "description": _get(info, "description"),
        "citation": _get(info, "citation"),
        "inventory_only": True,
        "content_downloaded": False,
        "publication_approved": False,
        "training_approved": False,
    }


def _merge_match(existing: dict[str, Any], new_item: dict[str, Any]) -> None:
    existing["matched_iso_codes"] = sorted(
        set(existing.get("matched_iso_codes", [])) | set(new_item.get("matched_iso_codes", []))
    )
    existing["matched_varieties"] = sorted(
        set(existing.get("matched_varieties", [])) | set(new_item.get("matched_varieties", []))
    )


def inventory_datasets(
    *,
    api: HfApi | Any | None = None,
    iso_codes: Iterable[str] = TARGET_VARIETIES,
    limit_per_iso: int = 500,
) -> tuple[list[dict[str, Any]], dict[str, int]]:
    """Inventorie les repos tagués par ISO et fusionne les repos multi-ISO."""

    client = api or HfApi()
    requested = [str(code).lower() for code in iso_codes]
    unsupported = sorted(set(requested) - set(TARGET_VARIETIES))
    if unsupported:
        raise HuggingFaceInventoryError(
            "Codes ISO non configurés : " + ", ".join(unsupported)
        )

    by_repo: dict[str, dict[str, Any]] = {}
    counts: dict[str, int] = {}

    for iso in requested:
        try:
            results = list(
                client.list_datasets(
                    filter=f"language:{iso}",
                    full=True,
                    limit=limit_per_iso,
                )
            )
        except Exception as exc:  # pragma: no cover - réseau réel
            raise HuggingFaceInventoryError(
                f"Échec de l'inventaire Hugging Face pour {iso}: {exc}"
            ) from exc

        counts[iso] = len(results)
        for info in results:
            item = normalize_dataset_info(info, iso)
            repo_id = item["repo_id"]
            if repo_id in by_repo:
                _merge_match(by_repo[repo_id], item)
            else:
                by_repo[repo_id] = item

    datasets = sorted(by_repo.values(), key=lambda item: item["repo_id"].casefold())
    return datasets, counts


def build_summary(datasets: list[dict[str, Any]], query_counts: dict[str, int]) -> dict[str, Any]:
    licenses = Counter(str(item.get("license") or "UNKNOWN") for item in datasets)
    review_statuses = Counter(str(item.get("review_status") or "unknown") for item in datasets)
    gated = sum(1 for item in datasets if item.get("gated") not in {None, False, "false"})

    return {
        "queried_at": datetime.now(timezone.utc).isoformat(),
        "target_iso_codes": list(TARGET_VARIETIES),
        "query_result_count_by_iso": dict(sorted(query_counts.items())),
        "unique_dataset_count": len(datasets),
        "license_counts": dict(sorted(licenses.items())),
        "review_status_counts": dict(sorted(review_statuses.items())),
        "gated_dataset_count": gated,
        "inventory_status": "metadata_only_unreviewed",
        "content_downloaded": False,
        "publication_approved": False,
        "training_approved": False,
        "notes": [
            "L'inventaire utilise les tags de langue déclarés sur le Hugging Face Hub.",
            "Un tag ISO ne garantit pas que toutes les lignes du dataset appartiennent à cette langue.",
            "La licence affichée par le repo doit être vérifiée avec la provenance du contenu avant ingestion.",
            "Aucun contenu de dataset n'est téléchargé à cette étape.",
        ],
    }


def write_inventory(
    datasets: list[dict[str, Any]],
    query_counts: dict[str, int],
    output_dir: Path = RAW_OUTPUT_DIR,
) -> dict[str, Path]:
    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "huggingface_san_inventory.json"
    csv_path = output_dir / "huggingface_san_inventory.csv"
    summary_path = output_dir / "huggingface_san_inventory_summary.json"

    summary = build_summary(datasets, query_counts)
    json_path.write_text(
        json.dumps({"metadata": summary, "datasets": datasets}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    summary_path.write_text(
        json.dumps(summary, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for item in datasets:
            writer.writerow(
                {
                    "repo_id": item.get("repo_id"),
                    "matched_iso_codes": ";".join(item.get("matched_iso_codes", [])),
                    "matched_varieties": ";".join(item.get("matched_varieties", [])),
                    "license": item.get("license"),
                    "review_status": item.get("review_status"),
                    "gated": item.get("gated"),
                    "private": item.get("private"),
                    "downloads": item.get("downloads"),
                    "likes": item.get("likes"),
                    "last_modified": item.get("last_modified"),
                    "languages": ";".join(item.get("languages", [])),
                    "modalities": ";".join(item.get("modalities", [])),
                    "formats": ";".join(item.get("formats", [])),
                    "tasks": ";".join(item.get("tasks", [])),
                    "size_categories": ";".join(item.get("size_categories", [])),
                    "url": item.get("url"),
                }
            )

    return {"json": json_path, "csv": csv_path, "summary": summary_path}


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Inventorier les datasets Hugging Face associés à sbd, stj et sym"
    )
    parser.add_argument(
        "--iso",
        nargs="+",
        default=list(TARGET_VARIETIES),
        help="Codes ISO à inventorier (défaut: sbd stj sym)",
    )
    parser.add_argument(
        "--limit-per-iso",
        type=int,
        default=500,
        help="Limite de repos interrogés par code ISO (défaut: 500)",
    )
    parser.add_argument("--output-dir", type=Path, default=RAW_OUTPUT_DIR)
    args = parser.parse_args()

    datasets, counts = inventory_datasets(
        iso_codes=args.iso,
        limit_per_iso=max(1, args.limit_per_iso),
    )
    paths = write_inventory(datasets, counts, args.output_dir)
    summary = json.loads(paths["summary"].read_text(encoding="utf-8"))

    print("Résultats Hugging Face par ISO :")
    for iso in args.iso:
        iso = iso.lower()
        print(f"- {iso} ({TARGET_VARIETIES[iso]}): {counts.get(iso, 0)} repos")
    print(f"Datasets uniques : {summary['unique_dataset_count']}")
    print(f"Datasets gated : {summary['gated_dataset_count']}")
    print("Licences déclarées :")
    for license_name, count in summary["license_counts"].items():
        print(f"- {license_name}: {count}")
    print("Aucun contenu linguistique n'a été téléchargé : inventaire métadonnées uniquement.")
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Résumé : {paths['summary']}")


if __name__ == "__main__":
    main()
