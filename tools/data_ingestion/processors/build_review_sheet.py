"""Préparer une feuille de revue humaine pour les données ASJP enrichies.

Le fichier produit regroupe les formes par variété et concept. Il ne choisit
jamais automatiquement une forme correcte et laisse les champs de décision
linguistique vides afin qu'un validateur puisse les renseigner explicitement.
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
from collections import Counter, defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_INPUT = REPO_ROOT / "data" / "processed" / "asjp" / "asjp_v21_san_enriched.json"
DEFAULT_VARIANTS = REPO_ROOT / "data" / "processed" / "asjp" / "asjp_v21_variants_review.json"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "asjp" / "review"

CSV_FIELDS = (
    "review_id",
    "iso_639_3",
    "variety",
    "concept_en",
    "concept_fr",
    "concept_fr_note",
    "source_wordlists",
    "source_forms",
    "source_map",
    "variant_classification",
    "review_priority",
    "decision_status",
    "selected_source_form",
    "standard_san",
    "reviewer_notes",
    "linguistic_status",
    "training_approved",
)


class ReviewSheetError(RuntimeError):
    """Erreur contrôlée de préparation de la revue humaine."""


def _review_id(iso: str, concept: str) -> str:
    digest = hashlib.sha1(f"{iso}|{concept}".encode("utf-8")).hexdigest()[:12]
    return f"asjp-review-{digest}"


def _priority(classification: str) -> str:
    if classification == "cross_wordlist_with_internal_variants":
        return "high"
    if classification in {"cross_wordlist_single_each", "internal_variants_same_wordlist"}:
        return "medium"
    return "normal"


def _load_variant_index(path: Path) -> dict[tuple[str, str], dict[str, Any]]:
    if not path.exists():
        return {}
    payload = json.loads(path.read_text(encoding="utf-8"))
    groups = payload.get("groups") or []
    if not isinstance(groups, list):
        raise ReviewSheetError("Le fichier de variantes contient un champ 'groups' invalide.")
    return {
        (str(group.get("iso_639_3") or ""), str(group.get("concept") or "")): group
        for group in groups
        if group.get("iso_639_3") and group.get("concept")
    }


def build_review_items(
    entries: list[dict[str, Any]],
    variant_index: dict[tuple[str, str], dict[str, Any]] | None = None,
) -> list[dict[str, Any]]:
    """Regroupe les entrées par variété + concept pour revue humaine."""

    variant_index = variant_index or {}
    grouped: dict[tuple[str, str], list[dict[str, Any]]] = defaultdict(list)

    for entry in entries:
        iso = str(entry.get("iso_639_3") or "").strip()
        concept = str(entry.get("concept_normalized") or "").strip()
        if iso and concept:
            grouped[(iso, concept)].append(entry)

    items: list[dict[str, Any]] = []
    for (iso, concept), rows in sorted(grouped.items()):
        source_map_sets: dict[str, set[str]] = defaultdict(set)
        for row in rows:
            wordlist = str(row.get("source_wordlist") or "<unknown>").strip()
            form = str(row.get("source_form") or "").strip()
            if form:
                source_map_sets[wordlist].add(form)

        source_map = {
            wordlist: sorted(forms)
            for wordlist, forms in sorted(source_map_sets.items())
        }
        forms = sorted({form for values in source_map.values() for form in values})
        wordlists = sorted(source_map)

        variant = variant_index.get((iso, concept))
        classification = (
            str(variant.get("classification"))
            if variant
            else "single_form"
        )

        concept_fr = next((row.get("concept_fr") for row in rows if row.get("concept_fr")), None)
        concept_fr_note = next(
            (row.get("concept_fr_note") for row in rows if row.get("concept_fr_note")),
            None,
        )
        variety = next((row.get("variety") for row in rows if row.get("variety")), None)

        items.append(
            {
                "review_id": _review_id(iso, concept),
                "iso_639_3": iso,
                "variety": variety,
                "concept_en": concept,
                "concept_fr": concept_fr,
                "concept_fr_note": concept_fr_note,
                "source_wordlists": wordlists,
                "source_forms": forms,
                "source_map": source_map,
                "variant_classification": classification,
                "review_priority": _priority(classification),
                "decision_status": "pending",
                "selected_source_form": None,
                "standard_san": None,
                "reviewer_notes": None,
                "linguistic_status": "pending",
                "training_approved": False,
            }
        )

    return items


def build_summary(items: list[dict[str, Any]]) -> dict[str, Any]:
    by_iso = Counter(str(item["iso_639_3"]) for item in items)
    by_classification = Counter(str(item["variant_classification"]) for item in items)
    by_priority = Counter(str(item["review_priority"]) for item in items)

    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "ASJP",
        "status": "human_review_ready",
        "review_items": len(items),
        "by_iso": dict(sorted(by_iso.items())),
        "by_classification": dict(sorted(by_classification.items())),
        "by_priority": dict(sorted(by_priority.items())),
        "pending_items": sum(1 for item in items if item["decision_status"] == "pending"),
        "training_approved": False,
        "instructions": [
            "Ne pas supposer que source_form est déjà l'orthographe standard San.",
            "Pour un groupe multi-formes, comparer les formes et la variété avant de sélectionner une forme.",
            "Si aucune forme source n'est acceptable, renseigner standard_san avec une forme validée et l'expliquer dans reviewer_notes.",
            "Ne jamais passer training_approved à true sans décision explicite du workflow de gouvernance.",
        ],
    }


def process_file(
    input_path: Path = DEFAULT_INPUT,
    variants_path: Path = DEFAULT_VARIANTS,
    output_dir: Path = DEFAULT_OUTPUT_DIR,
) -> dict[str, Path]:
    if not input_path.exists():
        raise ReviewSheetError(
            f"Fichier enrichi introuvable : {input_path}\n"
            "Lancer d'abord processors/enrich_concepts.py."
        )

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    entries = payload.get("entries")
    if not isinstance(entries, list):
        raise ReviewSheetError("Le fichier enrichi ne contient pas de liste 'entries'.")

    variant_index = _load_variant_index(variants_path)
    items = build_review_items(entries, variant_index)
    summary = build_summary(items)

    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "asjp_v21_human_review.json"
    csv_path = output_dir / "asjp_v21_human_review.csv"
    summary_path = output_dir / "asjp_v21_human_review_summary.json"

    json_path.write_text(
        json.dumps({"summary": summary, "items": items}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for item in items:
            row = dict(item)
            row["source_wordlists"] = " | ".join(item["source_wordlists"])
            row["source_forms"] = " | ".join(item["source_forms"])
            row["source_map"] = json.dumps(item["source_map"], ensure_ascii=False, sort_keys=True)
            writer.writerow({field: row.get(field) for field in CSV_FIELDS})

    summary_path.write_text(
        json.dumps(summary, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    return {"json": json_path, "csv": csv_path, "summary": summary_path}


def main() -> None:
    parser = argparse.ArgumentParser(description="Préparer la feuille de revue humaine ASJP")
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT, help="Fichier ASJP enrichi")
    parser.add_argument("--variants", type=Path, default=DEFAULT_VARIANTS, help="Fichier QA des variantes")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR, help="Dossier de sortie")
    args = parser.parse_args()

    paths = process_file(args.input, args.variants, args.output_dir)
    summary = json.loads(paths["summary"].read_text(encoding="utf-8"))

    print(f"Éléments à revoir : {summary['review_items']}")
    print("Par variété :")
    for iso, count in summary["by_iso"].items():
        print(f"- {iso}: {count}")
    print("Par classification :")
    for name, count in summary["by_classification"].items():
        print(f"- {name}: {count}")
    print("Par priorité :")
    for name, count in summary["by_priority"].items():
        print(f"- {name}: {count}")
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Résumé : {paths['summary']}")


if __name__ == "__main__":
    main()
