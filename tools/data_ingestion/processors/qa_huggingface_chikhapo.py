"""QA technique des fichiers ChiKhaPo Matya récoltés localement.

Cette étape ne normalise pas les mots et ne valide aucune traduction. Elle
vérifie uniquement la structure JSONL, la direction de langue attendue, les
champs minimaux et les doublons exacts afin de documenter ce qui a réellement
été téléchargé.
"""

from __future__ import annotations

import argparse
import json
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface" / "chikhapo"
DEFAULT_OUTPUT = REPO_ROOT / "data" / "processed" / "huggingface" / "chikhapo_qa.json"
EXPECTED_DIRECTIONS = {
    "eng_stj": ("eng", "stj"),
    "stj_eng": ("stj", "eng"),
}


class ChiKhaPoQAError(RuntimeError):
    """Erreur contrôlée pendant le QA ChiKhaPo."""


def _target_values(value: Any) -> list[str]:
    """Retourne les traductions non vides sans modifier le contenu source."""

    if isinstance(value, list):
        return [str(item) for item in value if str(item).strip()]
    if value is None:
        return []
    text = str(value)
    return [text] if text.strip() else []


def inspect_file(path: Path, config: str) -> dict[str, Any]:
    if not path.exists():
        raise ChiKhaPoQAError(f"Fichier ChiKhaPo introuvable : {path}")

    expected_src, expected_tgt = EXPECTED_DIRECTIONS[config]
    total_lines = 0
    valid_json = 0
    malformed_json = 0
    non_object_rows = 0
    missing_source_word = 0
    missing_target_translations = 0
    source_language_mismatch = 0
    target_language_mismatch = 0
    rows_with_language_fields = 0
    total_target_translations = 0
    rows_with_multiple_targets = 0
    exact_duplicate_rows = 0
    field_counter: Counter[str] = Counter()
    seen_records: set[str] = set()
    unique_source_words: set[str] = set()
    unique_target_values: set[str] = set()

    with path.open("r", encoding="utf-8") as handle:
        for line_number, raw_line in enumerate(handle, start=1):
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

            field_counter.update(str(key) for key in row)
            source_word = row.get("source_word")
            target_translations = _target_values(row.get("target_translations"))
            src_lang = row.get("src_lang")
            tgt_lang = row.get("tgt_lang")

            if source_word is None or not str(source_word).strip():
                missing_source_word += 1
            else:
                unique_source_words.add(str(source_word))

            if not target_translations:
                missing_target_translations += 1
            else:
                total_target_translations += len(target_translations)
                unique_target_values.update(target_translations)
                if len(target_translations) > 1:
                    rows_with_multiple_targets += 1

            if src_lang is not None or tgt_lang is not None:
                rows_with_language_fields += 1
            if src_lang is not None and str(src_lang).lower() != expected_src:
                source_language_mismatch += 1
            if tgt_lang is not None and str(tgt_lang).lower() != expected_tgt:
                target_language_mismatch += 1

            canonical = json.dumps(row, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
            if canonical in seen_records:
                exact_duplicate_rows += 1
            else:
                seen_records.add(canonical)

    return {
        "config": config,
        "path": str(path),
        "expected_src_lang": expected_src,
        "expected_tgt_lang": expected_tgt,
        "total_nonempty_lines": total_lines,
        "valid_json_rows": valid_json,
        "malformed_json_rows": malformed_json,
        "non_object_rows": non_object_rows,
        "missing_source_word_rows": missing_source_word,
        "missing_target_translations_rows": missing_target_translations,
        "rows_with_language_fields": rows_with_language_fields,
        "source_language_mismatch_rows": source_language_mismatch,
        "target_language_mismatch_rows": target_language_mismatch,
        "total_target_translations": total_target_translations,
        "rows_with_multiple_targets": rows_with_multiple_targets,
        "unique_source_words": len(unique_source_words),
        "unique_target_values": len(unique_target_values),
        "exact_duplicate_rows": exact_duplicate_rows,
        "observed_fields": sorted(field_counter),
        "field_presence_counts": dict(sorted(field_counter.items())),
        "technical_ok": (
            malformed_json == 0
            and non_object_rows == 0
            and missing_source_word == 0
            and missing_target_translations == 0
            and source_language_mismatch == 0
            and target_language_mismatch == 0
        ),
    }


def run_qa(raw_root: Path = RAW_ROOT, output: Path = DEFAULT_OUTPUT) -> dict[str, Any]:
    results = []
    for config in EXPECTED_DIRECTIONS:
        path = raw_root / config / f"{config}.jsonl"
        results.append(inspect_file(path, config))

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "ec5ug/chikhapo",
        "iso_639_3": "stj",
        "variety": "matya",
        "validation_scope": "technical_only",
        "linguistic_validation": False,
        "publication_approved": False,
        "training_approved": False,
        "summary": {
            "configs_checked": len(results),
            "total_rows": sum(item["total_nonempty_lines"] for item in results),
            "total_valid_json_rows": sum(item["valid_json_rows"] for item in results),
            "total_exact_duplicate_rows": sum(item["exact_duplicate_rows"] for item in results),
            "all_technical_ok": all(item["technical_ok"] for item in results),
        },
        "configs": results,
        "notes": [
            "Le QA ne corrige, ne normalise et ne déduplique pas le RAW.",
            "Une ligne techniquement valide n'est pas une traduction linguistiquement validée.",
            "La provenance amont PanLex/GATITOS/IDS doit rester attachée à cette source.",
        ],
    }

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="QA technique des fichiers ChiKhaPo eng_stj et stj_eng")
    parser.add_argument("--raw-root", type=Path, default=RAW_ROOT)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    payload = run_qa(args.raw_root, args.output)
    print("QA ChiKhaPo :")
    for item in payload["configs"]:
        print(
            f"- {item['config']}: {item['total_nonempty_lines']} lignes, "
            f"JSON valides={item['valid_json_rows']}, "
            f"sources uniques={item['unique_source_words']}, "
            f"traductions={item['total_target_translations']}, "
            f"doublons exacts={item['exact_duplicate_rows']}, "
            f"technical_ok={item['technical_ok']}"
        )
    print(f"Total : {payload['summary']['total_rows']} lignes")
    print(f"Tous techniquement OK : {payload['summary']['all_technical_ok']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
