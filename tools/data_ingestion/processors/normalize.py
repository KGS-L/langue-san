"""Normalisation des données ASJP vers le schéma de travail Langue SAN.

Important : ce module ne transforme PAS la notation ASJP en orthographe San.
La forme source est conservée telle quelle dans ``source_form`` et le champ
``standard_san`` reste vide tant qu'une validation linguistique n'a pas eu lieu.
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
DEFAULT_INPUT = REPO_ROOT / "data" / "raw" / "asjp" / "asjp_v21_san_wordlists.json"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "asjp"

ISO_TO_VARIETY = {
    "sbd": "maka",
    "stj": "matya",
    "sym": "maya",
}

# Signes que l'on rencontre dans la notation ASJP de notre extraction.
# Ils sont comptés dans le rapport, mais jamais remplacés automatiquement.
ASJP_NOTATION_MARKERS = ("E", "C", "T", "N", "5", "3", "7", "*", "~")

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
    "source_form",
    "source_notation",
    "standard_san",
    "loan",
    "validation_status",
)


class NormalizationError(RuntimeError):
    """Erreur contrôlée de normalisation."""


def _as_bool(value: Any) -> bool | None:
    if isinstance(value, bool):
        return value
    if value is None:
        return None
    text = str(value).strip().lower()
    if text in {"true", "1", "yes", "y"}:
        return True
    if text in {"false", "0", "no", "n"}:
        return False
    return None


def _normalize_concept(value: Any) -> str | None:
    if value is None:
        return None
    text = str(value).strip()
    if not text:
        return None
    # Dans ASJP, certains concepts du noyau sont préfixés par '*'.
    # On garde la valeur originale dans concept_source et on retire uniquement
    # ce préfixe dans le champ pratique concept_normalized.
    return text[1:] if text.startswith("*") else text


def _stable_id(entry: dict[str, Any]) -> str:
    key = "|".join(
        str(entry.get(field) or "")
        for field in ("source_version", "iso_639_3", "language_id", "parameter_id", "value")
    )
    digest = hashlib.sha1(key.encode("utf-8")).hexdigest()[:12]
    return f"asjp-{digest}"


def normalize_entry(entry: dict[str, Any]) -> dict[str, Any]:
    """Normalise une entrée sans altérer sa forme linguistique source."""

    iso = str(entry.get("iso_639_3") or "").strip().lower()
    if iso not in ISO_TO_VARIETY:
        raise NormalizationError(f"Code ISO non pris en charge : {iso or '<vide>'}")

    source_form = entry.get("form") or entry.get("value")
    if isinstance(source_form, str):
        source_form = source_form.strip() or None

    return {
        "id": _stable_id(entry),
        "source": entry.get("source") or "ASJP",
        "source_version": entry.get("source_version"),
        "license": entry.get("license"),
        "variety": ISO_TO_VARIETY[iso],
        "iso_639_3": iso,
        "glottocode": entry.get("glottocode"),
        "source_wordlist": entry.get("language_id"),
        "parameter_id": entry.get("parameter_id"),
        "concept_source": entry.get("concept"),
        "concept_normalized": _normalize_concept(entry.get("concept")),
        # Pas de traduction automatique du concept à ce stade : ce champ sera
        # enrichi plus tard à partir d'un référentiel contrôlé.
        "concept_fr": None,
        # La notation ASJP est conservée telle quelle.
        "source_form": source_form,
        "source_notation": "asjp",
        # Ne jamais présenter automatiquement source_form comme orthographe San.
        "standard_san": None,
        "loan": _as_bool(entry.get("loan")),
        "validation_status": "external_unverified",
    }


def normalize_entries(entries: list[dict[str, Any]]) -> list[dict[str, Any]]:
    return [normalize_entry(entry) for entry in entries]


def _duplicate_summary(entries: list[dict[str, Any]]) -> dict[str, int]:
    keys = [
        (
            entry.get("iso_639_3"),
            entry.get("language_id"),
            entry.get("parameter_id"),
            entry.get("value") or entry.get("form"),
        )
        for entry in entries
    ]
    counts = Counter(keys)
    duplicate_groups = sum(1 for count in counts.values() if count > 1)
    duplicate_rows = sum(count - 1 for count in counts.values() if count > 1)
    return {
        "exact_duplicate_groups": duplicate_groups,
        "exact_duplicate_extra_rows": duplicate_rows,
    }


def build_report(
    raw_entries: list[dict[str, Any]],
    normalized_entries: list[dict[str, Any]],
    source_metadata: dict[str, Any] | None = None,
) -> dict[str, Any]:
    source_metadata = source_metadata or {}

    by_iso: dict[str, dict[str, Any]] = {}
    for iso in sorted(ISO_TO_VARIETY):
        rows = [row for row in normalized_entries if row["iso_639_3"] == iso]
        wordlists = Counter(row["source_wordlist"] for row in rows if row["source_wordlist"])
        by_iso[iso] = {
            "variety": ISO_TO_VARIETY[iso],
            "entries": len(rows),
            "unique_concepts": len({row["concept_normalized"] for row in rows if row["concept_normalized"]}),
            "unique_forms": len({row["source_form"] for row in rows if row["source_form"]}),
            "wordlists": dict(sorted(wordlists.items())),
        }

    multi_form_groups: dict[tuple[str, str], list[dict[str, Any]]] = defaultdict(list)
    for row in normalized_entries:
        concept = row.get("concept_normalized")
        if concept:
            multi_form_groups[(row["iso_639_3"], concept)].append(row)

    multi_form_concepts = []
    for (iso, concept), rows in sorted(multi_form_groups.items()):
        forms = sorted({row["source_form"] for row in rows if row.get("source_form")})
        if len(forms) > 1:
            multi_form_concepts.append(
                {
                    "iso_639_3": iso,
                    "variety": ISO_TO_VARIETY[iso],
                    "concept": concept,
                    "forms": forms,
                    "source_wordlists": sorted(
                        {row["source_wordlist"] for row in rows if row.get("source_wordlist")}
                    ),
                }
            )

    notation_marker_counts = {
        marker: sum(
            1
            for row in normalized_entries
            if marker in str(row.get("source_form") or "")
        )
        for marker in ASJP_NOTATION_MARKERS
    }

    missing_fields = {
        field: sum(1 for row in normalized_entries if not row.get(field))
        for field in ("source_wordlist", "concept_normalized", "source_form", "glottocode")
    }

    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": {
            "name": source_metadata.get("source", "ASJP"),
            "version": source_metadata.get("version"),
            "license": source_metadata.get("license"),
            "retrieved_at": source_metadata.get("retrieved_at"),
        },
        "status": {
            "collection": "success",
            "normalization": "success",
            "linguistic_validation": "required",
            "training_approved": False,
        },
        "totals": {
            "raw_entries": len(raw_entries),
            "normalized_entries": len(normalized_entries),
            "standard_san_populated": sum(1 for row in normalized_entries if row.get("standard_san")),
            "concept_fr_populated": sum(1 for row in normalized_entries if row.get("concept_fr")),
        },
        "by_iso": by_iso,
        "duplicates": _duplicate_summary(raw_entries),
        "missing_fields": missing_fields,
        "notation_markers": {
            "note": "Ces marqueurs appartiennent potentiellement à la notation ASJP et ne sont pas corrigés automatiquement.",
            "entries_containing_marker": notation_marker_counts,
        },
        "multi_form_concepts": {
            "count": len(multi_form_concepts),
            "items": multi_form_concepts,
        },
        "warnings": [
            "source_form conserve la notation ASJP et ne doit pas être affiché comme orthographe San validée.",
            "standard_san reste vide avant validation linguistique.",
            "Plusieurs formes d'un même concept ne sont pas supprimées automatiquement.",
            "Les listes lexicales d'une même variété restent identifiables via source_wordlist.",
        ],
    }


def process_file(input_path: Path = DEFAULT_INPUT, output_dir: Path = DEFAULT_OUTPUT_DIR) -> dict[str, Path]:
    if not input_path.exists():
        raise NormalizationError(
            f"Fichier brut introuvable : {input_path}\n"
            "Lancer d'abord le collecteur ASJP."
        )

    payload = json.loads(input_path.read_text(encoding="utf-8"))
    raw_entries = payload.get("entries")
    if not isinstance(raw_entries, list):
        raise NormalizationError("Le fichier brut ne contient pas de liste 'entries'.")

    normalized_entries = normalize_entries(raw_entries)
    report = build_report(raw_entries, normalized_entries, payload.get("metadata"))

    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "asjp_v21_san_normalized.json"
    csv_path = output_dir / "asjp_v21_san_normalized.csv"
    report_path = output_dir / "asjp_v21_report.json"

    normalized_payload = {
        "metadata": {
            "source": "ASJP",
            "source_version": payload.get("metadata", {}).get("version"),
            "source_retrieved_at": payload.get("metadata", {}).get("retrieved_at"),
            "processed_at": datetime.now(timezone.utc).isoformat(),
            "entry_count": len(normalized_entries),
            "validation_status": "external_unverified",
            "note": "source_form est une forme ASJP brute ; standard_san nécessite une validation linguistique.",
        },
        "entries": normalized_entries,
    }
    json_path.write_text(json.dumps(normalized_payload, ensure_ascii=False, indent=2), encoding="utf-8")

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        writer.writerows(normalized_entries)

    report_path.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")

    return {
        "json": json_path,
        "csv": csv_path,
        "report": report_path,
    }


def main() -> None:
    parser = argparse.ArgumentParser(description="Normaliser l'extraction ASJP du projet Langue SAN")
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT, help="Fichier JSON brut ASJP")
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR, help="Dossier de sortie")
    args = parser.parse_args()

    paths = process_file(args.input, args.output_dir)
    report = json.loads(paths["report"].read_text(encoding="utf-8"))

    print(f"Entrées normalisées : {report['totals']['normalized_entries']}")
    for iso, stats in report["by_iso"].items():
        print(
            f"- {iso} ({stats['variety']}): {stats['entries']} entrées, "
            f"{stats['unique_concepts']} concepts, {stats['unique_forms']} formes"
        )
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Rapport: {paths['report']}")


if __name__ == "__main__":
    main()
