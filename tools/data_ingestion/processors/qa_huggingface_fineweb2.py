"""QA technique du sous-ensemble FineWeb2 sbd_Latn récolté localement.

Cette étape ne corrige, ne normalise et ne filtre aucune donnée. Elle vérifie
uniquement la structure JSONL produite par le collecteur, la provenance attendue
et quelques propriétés techniques du contenu source.
"""

from __future__ import annotations

import argparse
import json
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_INPUT = (
    REPO_ROOT
    / "data"
    / "raw"
    / "huggingface"
    / "fineweb2"
    / "sbd_Latn"
    / "train.jsonl"
)
DEFAULT_OUTPUT = (
    REPO_ROOT
    / "data"
    / "processed"
    / "huggingface"
    / "fineweb2_qa.json"
)

EXPECTED_METADATA = {
    "family": "fineweb2",
    "config": "sbd_Latn",
    "split": "train",
    "iso_639_3": "sbd",
    "variety": "maka",
    "validation_status": "external_unverified",
}


class FineWeb2QAError(RuntimeError):
    """Erreur contrôlée pendant le QA FineWeb2."""


def inspect_file(path: Path) -> dict[str, Any]:
    if not path.exists():
        raise FineWeb2QAError(f"Fichier FineWeb2 introuvable : {path}")

    total_lines = 0
    valid_json = 0
    malformed_json = 0
    non_object_rows = 0
    missing_source_row = 0
    metadata_mismatch_rows = 0
    exact_duplicate_rows = 0
    rows_with_text_field = 0
    rows_with_blank_text = 0
    duplicate_text_rows = 0
    total_text_chars = 0
    source_field_counter: Counter[str] = Counter()
    remote_files: set[str] = set()
    revisions: set[str] = set()
    seen_records: set[str] = set()
    seen_texts: set[str] = set()

    with path.open("r", encoding="utf-8") as handle:
        for raw_line in handle:
            if not raw_line.strip():
                continue
            total_lines += 1
            try:
                row = json.loads(raw_line)
            except json.JSONDecodeError:
                malformed_json += 1
                continue

            valid_json += 1
            if not isinstance(row, dict):
                non_object_rows += 1
                continue

            canonical = json.dumps(
                row,
                ensure_ascii=False,
                sort_keys=True,
                separators=(",", ":"),
            )
            if canonical in seen_records:
                exact_duplicate_rows += 1
            else:
                seen_records.add(canonical)

            if any(row.get(key) != expected for key, expected in EXPECTED_METADATA.items()):
                metadata_mismatch_rows += 1

            remote_file = row.get("remote_file")
            if isinstance(remote_file, str) and remote_file:
                remote_files.add(remote_file)

            revision = row.get("revision")
            if isinstance(revision, str) and revision:
                revisions.add(revision)

            source_row = row.get("source_row")
            if not isinstance(source_row, dict):
                missing_source_row += 1
                continue

            source_field_counter.update(str(key) for key in source_row)
            if "text" in source_row:
                rows_with_text_field += 1
                value = source_row.get("text")
                text = "" if value is None else str(value)
                if not text.strip():
                    rows_with_blank_text += 1
                else:
                    total_text_chars += len(text)
                    if text in seen_texts:
                        duplicate_text_rows += 1
                    else:
                        seen_texts.add(text)

    text_field_consistent = rows_with_text_field in {0, valid_json - non_object_rows - missing_source_row}
    technical_ok = (
        malformed_json == 0
        and non_object_rows == 0
        and missing_source_row == 0
        and metadata_mismatch_rows == 0
        and exact_duplicate_rows == 0
        and text_field_consistent
        and rows_with_blank_text == 0
    )

    return {
        "path": str(path),
        "total_nonempty_lines": total_lines,
        "valid_json_rows": valid_json,
        "malformed_json_rows": malformed_json,
        "non_object_rows": non_object_rows,
        "missing_source_row_rows": missing_source_row,
        "metadata_mismatch_rows": metadata_mismatch_rows,
        "exact_duplicate_rows": exact_duplicate_rows,
        "rows_with_text_field": rows_with_text_field,
        "rows_with_blank_text": rows_with_blank_text,
        "duplicate_text_rows": duplicate_text_rows,
        "unique_nonempty_texts": len(seen_texts),
        "total_text_chars": total_text_chars,
        "observed_source_fields": sorted(source_field_counter),
        "source_field_presence_counts": dict(sorted(source_field_counter.items())),
        "remote_files": sorted(remote_files),
        "revisions": sorted(revisions),
        "text_field_consistent": text_field_consistent,
        "technical_ok": technical_ok,
    }


def run_qa(input_path: Path = DEFAULT_INPUT, output: Path = DEFAULT_OUTPUT) -> dict[str, Any]:
    result = inspect_file(input_path)
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "HuggingFaceFW/fineweb-2",
        "config": "sbd_Latn",
        "split": "train",
        "iso_639_3": "sbd",
        "variety": "maka",
        "validation_scope": "technical_only",
        "linguistic_validation": False,
        "publication_approved": False,
        "training_approved": False,
        "result": result,
        "notes": [
            "Le QA ne modifie pas le RAW FineWeb2.",
            "Une ligne techniquement valide n'est pas une preuve de qualité linguistique ni d'identification correcte de langue.",
            "Les URLs et métadonnées de provenance présentes dans source_row doivent être conservées pour les vérifications futures.",
        ],
    }
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="QA technique du RAW FineWeb2 sbd_Latn/train")
    parser.add_argument("--input", type=Path, default=DEFAULT_INPUT)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    payload = run_qa(args.input, args.output)
    item = payload["result"]
    print("QA FineWeb2 :")
    print(f"- lignes : {item['total_nonempty_lines']}")
    print(f"- JSON valides : {item['valid_json_rows']}")
    print(f"- source_row manquants : {item['missing_source_row_rows']}")
    print(f"- métadonnées incohérentes : {item['metadata_mismatch_rows']}")
    print(f"- doublons exacts : {item['exact_duplicate_rows']}")
    print(f"- lignes avec champ text : {item['rows_with_text_field']}")
    print(f"- textes vides : {item['rows_with_blank_text']}")
    print(f"- textes dupliqués : {item['duplicate_text_rows']}")
    print(f"- caractères texte : {item['total_text_chars']}")
    print(f"- technical_ok : {item['technical_ok']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
