"""Triage technique de l'inventaire Hugging Face SAN.

Ce script lit l'inventaire produit par ``collectors/huggingface_inventory.py`` et
classe les repos par priorité d'inspection. Il ne télécharge aucun contenu et
n'approuve aucune ressource pour publication ou entraînement.

Le triage est heuristique : il sert uniquement à décider quels repos examiner
en premier (fiche dataset, provenance, fichiers, licence réelle, volume par ISO).
"""

from __future__ import annotations

import argparse
import csv
import json
import re
from collections import Counter
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_INPUT = REPO_ROOT / "data" / "raw" / "huggingface" / "huggingface_san_inventory.json"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "huggingface"

CSV_FIELDS = (
    "repo_id",
    "priority",
    "score",
    "license",
    "license_bucket",
    "matched_iso_codes",
    "matched_varieties",
    "gated",
    "downloads",
    "tasks",
    "modalities",
    "formats",
    "size_categories",
    "reasons",
    "url",
)

PERMISSIVE_LICENSES = {
    "cc0-1.0",
    "cc-by-4.0",
    "mit",
    "apache-2.0",
    "odc-by",
}

RESTRICTIVE_LICENSES = {
    "cc-by-nc-sa-4.0",
    "cc-by-nc-4.0",
    "cc-by-nc-nd-4.0",
}

HIGH_VALUE_TERMS = {
    "translation",
    "parallel",
    "corpus",
    "speech",
    "audio",
    "asr",
    "text",
    "sentence",
    "sentences",
    "bible",
    "opus",
    "flores",
    "nllb",
}

LOW_VALUE_TERMS = {
    "finefreq",
    "frequency",
    "frequencies",
    "benchmark",
    "evaluation",
    "tokenizer",
    "character frequency",
    "statistics",
}


def _norm(value: Any) -> str:
    return str(value or "").strip().lower()


def _as_list(value: Any) -> list[str]:
    if value is None:
        return []
    if isinstance(value, list):
        return [str(item) for item in value if item is not None]
    if isinstance(value, (tuple, set)):
        return [str(item) for item in value if item is not None]
    return [str(value)]


def license_bucket(license_name: Any) -> str:
    name = _norm(license_name)
    if not name:
        return "unknown"
    if name in PERMISSIVE_LICENSES:
        return "declared_reusable_review_required"
    if name in RESTRICTIVE_LICENSES or "non-commercial" in name or "nc" in name:
        return "restricted_noncommercial_review_required"
    if name == "other":
        return "other_manual_review_required"
    return "manual_review_required"


def _search_blob(item: dict[str, Any]) -> str:
    parts: list[str] = [
        str(item.get("repo_id") or ""),
        str(item.get("description") or ""),
        " ".join(_as_list(item.get("tasks"))),
        " ".join(_as_list(item.get("modalities"))),
        " ".join(_as_list(item.get("formats"))),
        " ".join(_as_list(item.get("tags"))),
    ]
    card_data = item.get("card_data")
    if isinstance(card_data, dict):
        for key in ("pretty_name", "dataset_info", "task_categories", "task_ids", "language"):
            value = card_data.get(key)
            if value is not None:
                parts.append(str(value))
    return re.sub(r"\s+", " ", " ".join(parts)).lower()


def triage_dataset(item: dict[str, Any]) -> dict[str, Any]:
    score = 0
    reasons: list[str] = []

    iso_codes = sorted(set(_as_list(item.get("matched_iso_codes"))))
    if len(iso_codes) >= 3:
        score += 4
        reasons.append("covers_all_three_iso")
    elif len(iso_codes) == 2:
        score += 3
        reasons.append("covers_two_iso")
    elif len(iso_codes) == 1:
        score += 1
        reasons.append("covers_one_iso")

    bucket = license_bucket(item.get("license"))
    if bucket == "declared_reusable_review_required":
        score += 2
        reasons.append("declared_reusable_license")
    elif bucket == "restricted_noncommercial_review_required":
        score -= 1
        reasons.append("noncommercial_or_restrictive_license")
    elif bucket in {"unknown", "other_manual_review_required", "manual_review_required"}:
        score -= 2
        reasons.append("license_needs_manual_review")

    gated = item.get("gated") not in {None, False, "false", "False", 0}
    if gated:
        score -= 2
        reasons.append("gated")

    if item.get("private"):
        score -= 5
        reasons.append("private")

    blob = _search_blob(item)
    high_hits = sorted(term for term in HIGH_VALUE_TERMS if term in blob)
    low_hits = sorted(term for term in LOW_VALUE_TERMS if term in blob)

    if high_hits:
        bonus = min(4, len(high_hits))
        score += bonus
        reasons.append("high_value_signal:" + ",".join(high_hits[:5]))

    if low_hits:
        penalty = min(4, len(low_hits))
        score -= penalty
        reasons.append("low_value_signal:" + ",".join(low_hits[:5]))

    tasks = {_norm(x) for x in _as_list(item.get("tasks"))}
    modalities = {_norm(x) for x in _as_list(item.get("modalities"))}
    if any("translation" in task for task in tasks):
        score += 3
        reasons.append("translation_task")
    if any("automatic-speech-recognition" in task or task == "asr" for task in tasks):
        score += 3
        reasons.append("asr_task")
    if "audio" in modalities:
        score += 2
        reasons.append("audio_modality")
    if "text" in modalities:
        score += 1
        reasons.append("text_modality")

    if score >= 6:
        priority = "high"
    elif score >= 2:
        priority = "medium"
    else:
        priority = "low"

    return {
        "repo_id": item.get("repo_id"),
        "priority": priority,
        "score": score,
        "license": item.get("license"),
        "license_bucket": bucket,
        "matched_iso_codes": iso_codes,
        "matched_varieties": sorted(set(_as_list(item.get("matched_varieties")))),
        "gated": gated,
        "private": bool(item.get("private")),
        "downloads": item.get("downloads"),
        "likes": item.get("likes"),
        "tasks": _as_list(item.get("tasks")),
        "modalities": _as_list(item.get("modalities")),
        "formats": _as_list(item.get("formats")),
        "size_categories": _as_list(item.get("size_categories")),
        "reasons": reasons,
        "url": item.get("url"),
        "review_status": "manual_dataset_card_review_required",
        "content_downloaded": False,
        "publication_approved": False,
        "training_approved": False,
    }


def triage_inventory(datasets: list[dict[str, Any]]) -> list[dict[str, Any]]:
    rows = [triage_dataset(item) for item in datasets]
    priority_order = {"high": 0, "medium": 1, "low": 2}
    rows.sort(
        key=lambda item: (
            priority_order[item["priority"]],
            -int(item["score"]),
            str(item.get("repo_id") or "").casefold(),
        )
    )
    return rows


def build_summary(rows: list[dict[str, Any]]) -> dict[str, Any]:
    priorities = Counter(row["priority"] for row in rows)
    licenses = Counter(str(row.get("license") or "UNKNOWN") for row in rows)
    buckets = Counter(row["license_bucket"] for row in rows)
    return {
        "dataset_count": len(rows),
        "by_priority": dict(sorted(priorities.items())),
        "by_license": dict(sorted(licenses.items())),
        "by_license_bucket": dict(sorted(buckets.items())),
        "triage_status": "metadata_heuristic_only",
        "content_downloaded": False,
        "publication_approved": False,
        "training_approved": False,
        "notes": [
            "Le score sert seulement à ordonner la revue manuelle des fiches Hugging Face.",
            "Une licence déclarée sur Hugging Face ne suffit pas : la source d'origine doit être vérifiée.",
            "Un repo LOW peut rester utile pour orthographe, statistiques ou benchmark.",
            "Aucun dataset n'est approuvé automatiquement par ce script.",
        ],
    }


def process_file(input_path: Path = DEFAULT_INPUT, output_dir: Path = DEFAULT_OUTPUT_DIR) -> dict[str, Path]:
    if not input_path.exists():
        raise FileNotFoundError(f"Inventaire Hugging Face introuvable : {input_path}")

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    datasets = payload.get("datasets")
    if not isinstance(datasets, list):
        raise ValueError("Le fichier d'inventaire doit contenir une liste 'datasets'.")

    rows = triage_inventory(datasets)
    summary = build_summary(rows)
    output_dir.mkdir(parents=True, exist_ok=True)

    json_path = output_dir / "huggingface_san_triage.json"
    csv_path = output_dir / "huggingface_san_triage.csv"
    summary_path = output_dir / "huggingface_san_triage_summary.json"

    json_path.write_text(
        json.dumps({"summary": summary, "datasets": rows}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    summary_path.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for row in rows:
            writer.writerow(
                {
                    "repo_id": row.get("repo_id"),
                    "priority": row.get("priority"),
                    "score": row.get("score"),
                    "license": row.get("license"),
                    "license_bucket": row.get("license_bucket"),
                    "matched_iso_codes": ";".join(row.get("matched_iso_codes", [])),
                    "matched_varieties": ";".join(row.get("matched_varieties", [])),
                    "gated": row.get("gated"),
                    "downloads": row.get("downloads"),
                    "tasks": ";".join(row.get("tasks", [])),
                    "modalities": ";".join(row.get("modalities", [])),
                    "formats": ";".join(row.get("formats", [])),
                    "size_categories": ";".join(row.get("size_categories", [])),
                    "reasons": " | ".join(row.get("reasons", [])),
                    "url": row.get("url"),
                }
            )

    return {"json": json_path, "csv": csv_path, "summary": summary_path}


def main() -> None:
    parser = argparse.ArgumentParser(description="Trier les repos Hugging Face SAN par priorité d'inspection")
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT)
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    args = parser.parse_args()

    paths = process_file(args.input, args.output_dir)
    payload = json.loads(paths["json"].read_text(encoding="utf-8"))
    summary = payload["summary"]
    rows = payload["datasets"]

    print(f"Datasets triés : {summary['dataset_count']}")
    print("Par priorité :")
    for priority in ("high", "medium", "low"):
        print(f"- {priority}: {summary['by_priority'].get(priority, 0)}")

    print("\nRepos à inspecter, dans l'ordre :")
    for row in rows:
        iso = ",".join(row.get("matched_iso_codes", [])) or "?"
        print(
            f"- [{row['priority'].upper():6}] score={row['score']:>2} "
            f"{row['repo_id']} | ISO={iso} | licence={row.get('license') or 'UNKNOWN'}"
        )

    print("\nAucun contenu n'a été téléchargé ; triage métadonnées uniquement.")
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Résumé : {paths['summary']}")


if __name__ == "__main__":
    main()
