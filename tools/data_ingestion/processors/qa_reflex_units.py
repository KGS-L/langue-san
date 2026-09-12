"""QA technique des exports CSV RefLex `/units` récoltés localement.

Cette étape ne corrige, ne normalise, ne déduplique et ne valide rien
linguistiquement. Elle vérifie uniquement l'intégrité technique du RAW :
- présence des colonnes attendues ;
- cohérence du nombre de lignes avec les métadonnées de récolte ;
- cohérence du glottocode par variété ;
- intégrité SHA-256 ;
- champs de provenance essentiels ;
- comptage des blancs, doublons et catégories observées.

Les traductions vides, POS vides et doublons sont signalés mais ne sont pas
supprimés : le RAW doit rester fidèle à RefLex.
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
RAW_ROOT = REPO_ROOT / "data" / "raw" / "reflex"
DEFAULT_OUTPUT = REPO_ROOT / "data" / "processed" / "reflex" / "reflex_units_qa.json"

EXPECTED_TARGETS = {
    "stj": {"variety": "matya", "glottocode": "maty1235"},
    "sym": {"variety": "maya", "glottocode": "maya1281"},
}

REQUIRED_HEADERS = [
    "Original Form",
    "Original Translation",
    "Comment",
    "Part of Speech",
    "Source",
    "Glottocode",
    "Family",
    "Latitude",
    "Longitude",
]


class RefLexUnitsQAError(RuntimeError):
    """Erreur contrôlée pendant le QA RefLex `/units`."""


def _load_metadata(path: Path) -> dict[str, Any]:
    if not path.exists():
        raise RefLexUnitsQAError(f"Métadonnées RefLex introuvables : {path}")
    try:
        payload = json.loads(path.read_text(encoding="utf-8"))
    except json.JSONDecodeError as exc:
        raise RefLexUnitsQAError(f"Métadonnées JSON invalides : {path}") from exc
    if not isinstance(payload, dict):
        raise RefLexUnitsQAError(f"Métadonnées RefLex invalides : {path}")
    return payload


def inspect_file(
    csv_path: Path,
    metadata_path: Path,
    *,
    iso: str,
    variety: str,
    expected_glottocode: str,
) -> dict[str, Any]:
    if not csv_path.exists():
        raise RefLexUnitsQAError(f"CSV RefLex introuvable : {csv_path}")

    raw_bytes = csv_path.read_bytes()
    try:
        text = raw_bytes.decode("utf-8-sig")
    except UnicodeDecodeError as exc:
        raise RefLexUnitsQAError(f"CSV RefLex non UTF-8 : {csv_path}") from exc

    metadata = _load_metadata(metadata_path)
    digest = hashlib.sha256(raw_bytes).hexdigest()

    reader = csv.DictReader(text.splitlines())
    headers = list(reader.fieldnames or [])
    missing_headers = [name for name in REQUIRED_HEADERS if name not in headers]
    extra_headers = [name for name in headers if name not in REQUIRED_HEADERS]

    total_rows = 0
    malformed_column_rows = 0
    blank_form_rows = 0
    blank_translation_rows = 0
    blank_source_rows = 0
    blank_pos_rows = 0
    glottocode_mismatch_rows = 0
    exact_duplicate_rows = 0
    duplicate_form_translation_rows = 0

    seen_exact: set[tuple[str, ...]] = set()
    seen_form_translation: set[tuple[str, str]] = set()
    source_counter: Counter[str] = Counter()
    pos_counter: Counter[str] = Counter()
    family_counter: Counter[str] = Counter()
    glottocode_counter: Counter[str] = Counter()

    for row in reader:
        total_rows += 1

        # DictReader place les colonnes surnuméraires sous la clé None.
        if None in row:
            malformed_column_rows += 1

        form = str(row.get("Original Form") or "").strip()
        translation = str(row.get("Original Translation") or "").strip()
        source = str(row.get("Source") or "").strip()
        pos = str(row.get("Part of Speech") or "").strip()
        glottocode = str(row.get("Glottocode") or "").strip()
        family = str(row.get("Family") or "").strip()

        if not form:
            blank_form_rows += 1
        if not translation:
            blank_translation_rows += 1
        if not source:
            blank_source_rows += 1
        else:
            source_counter[source] += 1
        if not pos:
            blank_pos_rows += 1
        else:
            pos_counter[pos] += 1

        if family:
            family_counter[family] += 1
        glottocode_counter[glottocode] += 1
        if glottocode != expected_glottocode:
            glottocode_mismatch_rows += 1

        exact_key = tuple(str(row.get(header) or "") for header in headers)
        if exact_key in seen_exact:
            exact_duplicate_rows += 1
        else:
            seen_exact.add(exact_key)

        pair_key = (form, translation)
        if pair_key in seen_form_translation:
            duplicate_form_translation_rows += 1
        else:
            seen_form_translation.add(pair_key)

    metadata_csv_rows = metadata.get("csv_rows")
    metadata_xhr_rows = metadata.get("xhr_reported_rows")
    metadata_iso = str(metadata.get("iso_639_3") or "").lower()
    metadata_variety = str(metadata.get("variety") or "").lower()
    metadata_glottocode = str(metadata.get("reflex_glottocode") or "")
    metadata_sha256 = str(metadata.get("sha256") or "")

    row_count_matches_metadata = (
        isinstance(metadata_csv_rows, int)
        and isinstance(metadata_xhr_rows, int)
        and total_rows == metadata_csv_rows == metadata_xhr_rows
    )
    metadata_identity_ok = (
        metadata_iso == iso
        and metadata_variety == variety
        and metadata_glottocode == expected_glottocode
    )
    sha256_matches_metadata = bool(metadata_sha256) and digest == metadata_sha256

    technical_ok = (
        total_rows > 0
        and not missing_headers
        and malformed_column_rows == 0
        and blank_form_rows == 0
        and blank_source_rows == 0
        and glottocode_mismatch_rows == 0
        and row_count_matches_metadata
        and metadata_identity_ok
        and sha256_matches_metadata
    )

    return {
        "iso_639_3": iso,
        "variety": variety,
        "expected_glottocode": expected_glottocode,
        "csv_path": str(csv_path),
        "metadata_path": str(metadata_path),
        "headers": headers,
        "missing_required_headers": missing_headers,
        "extra_headers": extra_headers,
        "total_rows": total_rows,
        "malformed_column_rows": malformed_column_rows,
        "blank_original_form_rows": blank_form_rows,
        "blank_original_translation_rows": blank_translation_rows,
        "blank_source_rows": blank_source_rows,
        "blank_part_of_speech_rows": blank_pos_rows,
        "glottocode_mismatch_rows": glottocode_mismatch_rows,
        "exact_duplicate_rows": exact_duplicate_rows,
        "duplicate_form_translation_rows": duplicate_form_translation_rows,
        "unique_sources": len(source_counter),
        "source_counts": dict(source_counter.most_common()),
        "part_of_speech_counts": dict(pos_counter.most_common()),
        "family_counts": dict(family_counter.most_common()),
        "glottocode_counts": dict(glottocode_counter.most_common()),
        "sha256": digest,
        "metadata_csv_rows": metadata_csv_rows,
        "metadata_xhr_reported_rows": metadata_xhr_rows,
        "row_count_matches_metadata": row_count_matches_metadata,
        "metadata_identity_ok": metadata_identity_ok,
        "sha256_matches_metadata": sha256_matches_metadata,
        "technical_ok": technical_ok,
    }


def run_qa(
    *,
    selected_iso: set[str] | None = None,
    raw_root: Path = RAW_ROOT,
    output: Path = DEFAULT_OUTPUT,
) -> dict[str, Any]:
    selected = selected_iso or set(EXPECTED_TARGETS)
    unknown = sorted(selected - set(EXPECTED_TARGETS))
    if unknown:
        raise RefLexUnitsQAError("Codes ISO non supportés par ce QA : " + ", ".join(unknown))

    results = []
    for iso in sorted(selected):
        target = EXPECTED_TARGETS[iso]
        results.append(
            inspect_file(
                raw_root / iso / "units.csv",
                raw_root / iso / "units_metadata.json",
                iso=iso,
                variety=str(target["variety"]),
                expected_glottocode=str(target["glottocode"]),
            )
        )

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "RefLex CLLD /units CSV",
        "license": "CC-BY-NC-SA-4.0",
        "validation_scope": "technical_only",
        "linguistic_validation": False,
        "publication_approved": False,
        "training_approved": False,
        "commercial_use_approved": False,
        "summary": {
            "iso_files_checked": len(results),
            "total_rows": sum(item["total_rows"] for item in results),
            "total_blank_translation_rows": sum(
                item["blank_original_translation_rows"] for item in results
            ),
            "total_exact_duplicate_rows": sum(item["exact_duplicate_rows"] for item in results),
            "total_glottocode_mismatch_rows": sum(
                item["glottocode_mismatch_rows"] for item in results
            ),
            "all_technical_ok": all(item["technical_ok"] for item in results),
        },
        "iso_files": results,
        "notes": [
            "Le QA ne modifie jamais units.csv.",
            "Les traductions vides, POS vides et doublons sont documentés mais ne sont pas supprimés automatiquement.",
            "Le nombre de lignes QA est comparé au compteur XHR /units enregistré au moment de la récolte, pas au compteur global de la contribution.",
            "technical_ok signifie uniquement intégrité technique; aucune forme n'est linguistiquement validée.",
        ],
    }

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="QA technique des exports RefLex /units CSV")
    parser.add_argument("--iso", nargs="+", choices=sorted(EXPECTED_TARGETS), default=None)
    parser.add_argument("--raw-root", type=Path, default=RAW_ROOT)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    selected = {str(value).lower() for value in args.iso} if args.iso else None
    payload = run_qa(selected_iso=selected, raw_root=args.raw_root, output=args.output)

    print("QA RefLex /units :")
    for item in payload["iso_files"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}): "
            f"lignes={item['total_rows']}, sources={item['unique_sources']}, "
            f"formes_vides={item['blank_original_form_rows']}, "
            f"traductions_vides={item['blank_original_translation_rows']}, "
            f"doublons_exacts={item['exact_duplicate_rows']}, "
            f"glottocode_mismatch={item['glottocode_mismatch_rows']}, "
            f"metadata_ok={item['row_count_matches_metadata'] and item['metadata_identity_ok'] and item['sha256_matches_metadata']}, "
            f"technical_ok={item['technical_ok']}"
        )
    print(f"Total : {payload['summary']['total_rows']} lignes")
    print(f"Tous techniquement OK : {payload['summary']['all_technical_ok']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
