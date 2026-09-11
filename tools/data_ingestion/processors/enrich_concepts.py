"""Enrichissement contrôlé des concepts ASJP avec des glosses françaises.

Ce module ajoute un libellé français de travail aux concepts anglais ASJP sans
modifier les formes San provenant de la source. Une glose française contrôlée
n'est pas une validation linguistique de la forme San et ne rend pas une entrée
apte automatiquement à l'entraînement.
"""

from __future__ import annotations

import argparse
import csv
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import yaml


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_INPUT = REPO_ROOT / "data" / "processed" / "asjp" / "asjp_v21_san_normalized.json"
DEFAULT_MAPPING = REPO_ROOT / "tools" / "data_ingestion" / "config" / "concepts_fr.yaml"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "asjp"

GLOSS_SOURCE = "project_controlled_gloss_v1"
GLOSS_STATUS = "project_controlled_gloss"

CSV_FIELDS = (
    "id",
    "source",
    "source_version",
    "license",
    "variety",
    "iso_639_3",
    "glottocode",
    "source_wordlist",
    "parameter_id",
    "concept_source",
    "concept_normalized",
    "concept_fr",
    "concept_fr_source",
    "concept_fr_status",
    "concept_fr_note",
    "source_form",
    "source_notation",
    "standard_san",
    "loan",
    "validation_status",
)


class ConceptEnrichmentError(RuntimeError):
    """Erreur contrôlée de l'enrichissement des concepts."""


def load_mapping(path: Path = DEFAULT_MAPPING) -> tuple[dict[str, str], dict[str, str], dict[str, Any]]:
    if not path.exists():
        raise ConceptEnrichmentError(f"Fichier de glosses introuvable : {path}")

    payload = yaml.safe_load(path.read_text(encoding="utf-8")) or {}
    mapping = payload.get("mapping")
    if not isinstance(mapping, dict):
        raise ConceptEnrichmentError("Le fichier de glosses doit contenir un objet 'mapping'.")

    notes = payload.get("notes") or {}
    if not isinstance(notes, dict):
        raise ConceptEnrichmentError("Le champ 'notes' doit être un objet lorsqu'il est présent.")

    normalized_mapping = {
        str(key).strip(): str(value).strip()
        for key, value in mapping.items()
        if str(key).strip() and value is not None and str(value).strip()
    }
    normalized_notes = {
        str(key).strip(): str(value).strip()
        for key, value in notes.items()
        if str(key).strip() and value is not None and str(value).strip()
    }

    return normalized_mapping, normalized_notes, payload


def enrich_entry(
    entry: dict[str, Any],
    mapping: dict[str, str],
    notes: dict[str, str] | None = None,
) -> dict[str, Any]:
    notes = notes or {}
    concept = str(entry.get("concept_normalized") or "").strip()
    concept_fr = mapping.get(concept)

    enriched = dict(entry)
    enriched["concept_fr"] = concept_fr
    enriched["concept_fr_source"] = GLOSS_SOURCE if concept_fr else None
    enriched["concept_fr_status"] = GLOSS_STATUS if concept_fr else "missing_gloss"
    enriched["concept_fr_note"] = notes.get(concept)
    return enriched


def enrich_entries(
    entries: list[dict[str, Any]],
    mapping: dict[str, str],
    notes: dict[str, str] | None = None,
) -> list[dict[str, Any]]:
    return [enrich_entry(entry, mapping, notes) for entry in entries]


def build_report(
    entries: list[dict[str, Any]],
    enriched_entries: list[dict[str, Any]],
    mapping: dict[str, str],
    notes: dict[str, str],
) -> dict[str, Any]:
    concepts = sorted(
        {
            str(entry.get("concept_normalized") or "").strip()
            for entry in entries
            if str(entry.get("concept_normalized") or "").strip()
        }
    )
    missing_concepts = [concept for concept in concepts if concept not in mapping]
    mapped_concepts = [concept for concept in concepts if concept in mapping]

    used_notes = {
        concept: notes[concept]
        for concept in mapped_concepts
        if concept in notes
    }

    populated_entries = sum(1 for entry in enriched_entries if entry.get("concept_fr"))
    status = "success" if not missing_concepts else "partial"

    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "ASJP",
        "status": status,
        "gloss_source": GLOSS_SOURCE,
        "gloss_status": GLOSS_STATUS,
        "totals": {
            "entries": len(entries),
            "entries_with_french_gloss": populated_entries,
            "entries_without_french_gloss": len(entries) - populated_entries,
            "unique_concepts": len(concepts),
            "mapped_concepts": len(mapped_concepts),
            "unmapped_concepts": len(missing_concepts),
        },
        "missing_concepts": missing_concepts,
        "documented_ambiguities": used_notes,
        "training_approved": False,
        "warnings": [
            "concept_fr est une glose française contrôlée du concept ASJP, pas une validation de source_form.",
            "standard_san reste inchangé et doit rester vide tant qu'aucune validation linguistique n'a eu lieu.",
            "Une paire concept_fr/source_form ne doit pas être promue automatiquement comme paire d'entraînement.",
        ],
    }


def process_file(
    input_path: Path = DEFAULT_INPUT,
    mapping_path: Path = DEFAULT_MAPPING,
    output_dir: Path = DEFAULT_OUTPUT_DIR,
) -> dict[str, Path]:
    if not input_path.exists():
        raise ConceptEnrichmentError(
            f"Fichier normalisé introuvable : {input_path}\n"
            "Lancer d'abord processors/normalize.py."
        )

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    entries = payload.get("entries")
    if not isinstance(entries, list):
        raise ConceptEnrichmentError("Le fichier normalisé ne contient pas de liste 'entries'.")

    mapping, notes, mapping_metadata = load_mapping(mapping_path)
    enriched_entries = enrich_entries(entries, mapping, notes)
    report = build_report(entries, enriched_entries, mapping, notes)

    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "asjp_v21_san_enriched.json"
    csv_path = output_dir / "asjp_v21_san_enriched.csv"
    report_path = output_dir / "asjp_v21_concepts_fr_report.json"

    enriched_payload = {
        "metadata": {
            **(payload.get("metadata") or {}),
            "enriched_at": datetime.now(timezone.utc).isoformat(),
            "concept_fr_source": GLOSS_SOURCE,
            "concept_fr_mapping_version": mapping_metadata.get("version"),
            "concept_fr_coverage_status": report["status"],
            "validation_status": "external_unverified",
            "training_approved": False,
        },
        "entries": enriched_entries,
    }

    json_path.write_text(
        json.dumps(enriched_payload, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for entry in enriched_entries:
            writer.writerow({field: entry.get(field) for field in CSV_FIELDS})

    report_path.write_text(
        json.dumps(report, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    return {"json": json_path, "csv": csv_path, "report": report_path}


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Ajouter des glosses françaises contrôlées aux concepts ASJP"
    )
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT, help="Fichier ASJP normalisé")
    parser.add_argument("--mapping", type=Path, default=DEFAULT_MAPPING, help="Mapping concepts anglais → français")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR, help="Dossier de sortie")
    args = parser.parse_args()

    paths = process_file(args.input, args.mapping, args.output_dir)
    report = json.loads(paths["report"].read_text(encoding="utf-8"))
    totals = report["totals"]

    print(f"Concepts ASJP uniques : {totals['unique_concepts']}")
    print(f"Concepts avec glose FR : {totals['mapped_concepts']}")
    print(f"Concepts sans glose FR : {totals['unmapped_concepts']}")
    print(f"Entrées enrichies : {totals['entries_with_french_gloss']} / {totals['entries']}")
    if report["missing_concepts"]:
        print("Concepts manquants : " + ", ".join(report["missing_concepts"]))
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Rapport: {paths['report']}")


if __name__ == "__main__":
    main()
