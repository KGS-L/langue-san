"""QA technique des sous-ensembles PanLex SAN récoltés localement.

Cette étape ne normalise pas le vocabulaire et ne valide aucune entrée
linguistiquement. Elle vérifie uniquement l'intégrité JSONL, la cohérence ISO,
la présence du vocabulaire et les doublons exacts afin de documenter le RAW.
"""

from __future__ import annotations

import argparse
import json
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface" / "panlex"
DEFAULT_OUTPUT = REPO_ROOT / "data" / "processed" / "huggingface" / "panlex_qa.json"
EXPECTED_VARIETIES = {
    "sbd": "maka",
    "stj": "matya",
    "sym": "maya",
}


class PanLexQAError(RuntimeError):
    """Erreur contrôlée pendant le QA PanLex."""


def inspect_file(path: Path, iso: str, variety: str) -> dict[str, Any]:
    if not path.exists():
        raise PanLexQAError(f"Fichier PanLex introuvable : {path}")

    total_lines = 0
    valid_json = 0
    malformed_json = 0
    non_object_rows = 0
    missing_source_row = 0
    missing_vocab = 0
    wrapper_iso_mismatch = 0
    wrapper_variety_mismatch = 0
    source_iso_mismatch = 0
    exact_duplicate_rows = 0
    duplicate_vocab_rows = 0
    source_field_counter: Counter[str] = Counter()
    seen_records: set[str] = set()
    seen_vocab: set[str] = set()
    unique_vocab: set[str] = set()
    unique_var_codes: set[str] = set()

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

            if str(row.get("iso_639_3") or "").lower() != iso:
                wrapper_iso_mismatch += 1
            if str(row.get("variety") or "").lower() != variety:
                wrapper_variety_mismatch += 1

            source_row = row.get("source_row")
            if not isinstance(source_row, dict):
                missing_source_row += 1
                source_row = {}
            else:
                source_field_counter.update(str(key) for key in source_row)

            source_iso = str(source_row.get("639-3") or "").lower()
            if source_iso != iso:
                source_iso_mismatch += 1

            vocab = source_row.get("vocab")
            if vocab is None or not str(vocab).strip():
                missing_vocab += 1
            else:
                vocab_text = str(vocab)
                if vocab_text in seen_vocab:
                    duplicate_vocab_rows += 1
                else:
                    seen_vocab.add(vocab_text)
                unique_vocab.add(vocab_text)

            var_code = source_row.get("var_code")
            if var_code is not None and str(var_code).strip():
                unique_var_codes.add(str(var_code))

            canonical = json.dumps(row, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
            if canonical in seen_records:
                exact_duplicate_rows += 1
            else:
                seen_records.add(canonical)

    technical_ok = (
        malformed_json == 0
        and non_object_rows == 0
        and missing_source_row == 0
        and missing_vocab == 0
        and wrapper_iso_mismatch == 0
        and wrapper_variety_mismatch == 0
        and source_iso_mismatch == 0
    )

    return {
        "iso_639_3": iso,
        "variety": variety,
        "path": str(path),
        "total_nonempty_lines": total_lines,
        "valid_json_rows": valid_json,
        "malformed_json_rows": malformed_json,
        "non_object_rows": non_object_rows,
        "missing_source_row_rows": missing_source_row,
        "missing_vocab_rows": missing_vocab,
        "wrapper_iso_mismatch_rows": wrapper_iso_mismatch,
        "wrapper_variety_mismatch_rows": wrapper_variety_mismatch,
        "source_iso_mismatch_rows": source_iso_mismatch,
        "unique_vocab": len(unique_vocab),
        "unique_var_codes": len(unique_var_codes),
        "duplicate_vocab_rows": duplicate_vocab_rows,
        "exact_duplicate_rows": exact_duplicate_rows,
        "observed_source_fields": sorted(source_field_counter),
        "source_field_presence_counts": dict(sorted(source_field_counter.items())),
        "technical_ok": technical_ok,
    }


def run_qa(raw_root: Path = RAW_ROOT, output: Path = DEFAULT_OUTPUT) -> dict[str, Any]:
    results = []
    for iso, variety in EXPECTED_VARIETIES.items():
        results.append(inspect_file(raw_root / iso / "train.jsonl", iso, variety))

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "lbourdois/panlex",
        "validation_scope": "technical_only",
        "linguistic_validation": False,
        "publication_approved": False,
        "training_approved": False,
        "summary": {
            "iso_files_checked": len(results),
            "total_rows": sum(item["total_nonempty_lines"] for item in results),
            "total_valid_json_rows": sum(item["valid_json_rows"] for item in results),
            "total_exact_duplicate_rows": sum(item["exact_duplicate_rows"] for item in results),
            "all_technical_ok": all(item["technical_ok"] for item in results),
        },
        "iso_files": results,
        "notes": [
            "Le QA ne corrige, ne normalise et ne déduplique pas le RAW.",
            "duplicate_vocab_rows indique des répétitions de formes, pas nécessairement des doublons erronés : PanLex peut distinguer plusieurs variantes via var_code.",
            "Une ligne techniquement valide n'est pas une entrée linguistiquement validée du projet.",
        ],
    }

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="QA technique des sous-ensembles PanLex sbd/stj/sym")
    parser.add_argument("--raw-root", type=Path, default=RAW_ROOT)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    payload = run_qa(args.raw_root, args.output)
    print("QA PanLex :")
    for item in payload["iso_files"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}): {item['total_nonempty_lines']} lignes, "
            f"JSON valides={item['valid_json_rows']}, vocab uniques={item['unique_vocab']}, "
            f"var_codes={item['unique_var_codes']}, doublons exacts={item['exact_duplicate_rows']}, "
            f"technical_ok={item['technical_ok']}"
        )
    print(f"Total : {payload['summary']['total_rows']} lignes")
    print(f"Tous techniquement OK : {payload['summary']['all_technical_ok']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
