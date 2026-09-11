"""Analyse QA des concepts ASJP ayant plusieurs formes.

Ce module ne choisit jamais automatiquement une forme correcte. Il regroupe les
variantes par variété et concept, distingue les divergences internes à une même
liste lexicale de celles observées entre plusieurs listes, puis produit des
fichiers destinés à la revue humaine.
"""

from __future__ import annotations

import argparse
import csv
import json
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_INPUT = REPO_ROOT / "data" / "processed" / "asjp" / "asjp_v21_san_normalized.json"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "asjp"

CSV_FIELDS = (
    "variant_group_id",
    "iso_639_3",
    "variety",
    "concept",
    "classification",
    "source_wordlists",
    "forms",
    "source_map",
    "needs_human_review",
    "resolution_status",
)


class VariantQAError(RuntimeError):
    """Erreur contrôlée de l'analyse des variantes."""


def classify_group(source_map: dict[str, list[str]]) -> str:
    """Classe un groupe de variantes sans interprétation linguistique."""

    if len(source_map) == 1:
        return "internal_variants_same_wordlist"

    if all(len(forms) == 1 for forms in source_map.values()):
        return "cross_wordlist_single_each"

    return "cross_wordlist_with_internal_variants"


def build_variant_groups(entries: list[dict[str, Any]]) -> list[dict[str, Any]]:
    grouped: dict[tuple[str, str], list[dict[str, Any]]] = defaultdict(list)

    for entry in entries:
        iso = str(entry.get("iso_639_3") or "").strip()
        concept = str(entry.get("concept_normalized") or "").strip()
        if not iso or not concept:
            continue
        grouped[(iso, concept)].append(entry)

    groups: list[dict[str, Any]] = []
    for index, ((iso, concept), rows) in enumerate(sorted(grouped.items()), start=1):
        forms = sorted({str(row.get("source_form") or "").strip() for row in rows if row.get("source_form")})
        if len(forms) <= 1:
            continue

        source_map_sets: dict[str, set[str]] = defaultdict(set)
        for row in rows:
            wordlist = str(row.get("source_wordlist") or "<unknown>")
            form = str(row.get("source_form") or "").strip()
            if form:
                source_map_sets[wordlist].add(form)

        source_map = {name: sorted(values) for name, values in sorted(source_map_sets.items())}
        variety = next((str(row.get("variety")) for row in rows if row.get("variety")), "")

        groups.append(
            {
                "variant_group_id": f"asjp-variant-{index:03d}",
                "iso_639_3": iso,
                "variety": variety,
                "concept": concept,
                "classification": classify_group(source_map),
                "source_wordlists": sorted(source_map),
                "forms": forms,
                "source_map": source_map,
                "needs_human_review": True,
                "resolution_status": "pending",
            }
        )

    return groups


def build_summary(groups: list[dict[str, Any]]) -> dict[str, Any]:
    by_iso: dict[str, int] = defaultdict(int)
    by_classification: dict[str, int] = defaultdict(int)

    for group in groups:
        by_iso[group["iso_639_3"]] += 1
        by_classification[group["classification"]] += 1

    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "ASJP",
        "status": "human_review_required",
        "variant_group_count": len(groups),
        "by_iso": dict(sorted(by_iso.items())),
        "by_classification": dict(sorted(by_classification.items())),
        "interpretation": {
            "internal_variants_same_wordlist": "Plusieurs formes sont présentes dans une seule liste lexicale. Ne pas dédupliquer automatiquement.",
            "cross_wordlist_single_each": "Chaque liste fournit une forme différente pour le même concept. Une comparaison de sources est nécessaire.",
            "cross_wordlist_with_internal_variants": "Le concept varie entre listes et au moins une liste contient elle-même plusieurs formes. Revue prioritaire recommandée.",
        },
        "decision_rule": "Aucune forme n'est promue vers standard_san sans validation linguistique explicite.",
    }


def process_file(input_path: Path = DEFAULT_INPUT, output_dir: Path = DEFAULT_OUTPUT_DIR) -> dict[str, Path]:
    if not input_path.exists():
        raise VariantQAError(f"Fichier normalisé introuvable : {input_path}\nLancer d'abord processors/normalize.py.")

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    entries = payload.get("entries")
    if not isinstance(entries, list):
        raise VariantQAError("Le fichier normalisé ne contient pas de liste 'entries'.")

    groups = build_variant_groups(entries)
    summary = build_summary(groups)

    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "asjp_v21_variants_review.json"
    csv_path = output_dir / "asjp_v21_variants_review.csv"
    summary_path = output_dir / "asjp_v21_variants_summary.json"

    json_path.write_text(
        json.dumps({"summary": summary, "groups": groups}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for group in groups:
            row = dict(group)
            row["source_wordlists"] = " | ".join(group["source_wordlists"])
            row["forms"] = " | ".join(group["forms"])
            row["source_map"] = json.dumps(group["source_map"], ensure_ascii=False, sort_keys=True)
            writer.writerow(row)

    summary_path.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")

    return {"json": json_path, "csv": csv_path, "summary": summary_path}


def main() -> None:
    parser = argparse.ArgumentParser(description="Analyser les variantes ASJP nécessitant une revue humaine")
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT, help="Fichier ASJP normalisé")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR, help="Dossier de sortie")
    args = parser.parse_args()

    paths = process_file(args.input, args.output_dir)
    summary = json.loads(paths["summary"].read_text(encoding="utf-8"))

    print(f"Groupes multi-formes : {summary['variant_group_count']}")
    print("Par variété :")
    for iso, count in summary["by_iso"].items():
        print(f"- {iso}: {count}")
    print("Par type :")
    for name, count in summary["by_classification"].items():
        print(f"- {name}: {count}")
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Résumé : {paths['summary']}")


if __name__ == "__main__":
    main()
