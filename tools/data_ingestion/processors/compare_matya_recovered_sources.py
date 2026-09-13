#!/usr/bin/env python3
"""
Audit de chevauchement lexical Matya (stj).

Compare le corpus San Matya récupéré avec :
- RefLex
- ChiKhaPo
- PanLex
- ASJP

La comparaison est volontairement conservatrice :
NFC + casefold + espaces condensés uniquement.

Aucune équivalence linguistique ou orthographique n'est déduite.
Le RAW n'est jamais modifié.
"""

from __future__ import annotations

import csv
import json
import unicodedata
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

REPO = Path.home() / "Bureau" / "langue-san"

RECOVERED = (
    REPO
    / "data"
    / "processed"
    / "san_matya_lexique_pro"
    / "recovery"
    / "san_matya_entries.jsonl"
)

REFLEX = REPO / "data" / "raw" / "reflex" / "stj" / "units.csv"

CHIKHAPO_STJ_ENG = (
    REPO
    / "data"
    / "raw"
    / "huggingface"
    / "chikhapo"
    / "stj_eng"
    / "stj_eng.jsonl"
)

CHIKHAPO_ENG_STJ = (
    REPO
    / "data"
    / "raw"
    / "huggingface"
    / "chikhapo"
    / "eng_stj"
    / "eng_stj.jsonl"
)

PANLEX = (
    REPO
    / "data"
    / "raw"
    / "huggingface"
    / "panlex"
    / "stj"
    / "train.jsonl"
)

ASJP = REPO / "data" / "raw" / "asjp" / "asjp_v21_san_wordlists.json"

OUTPUT_DIR = (
    REPO
    / "data"
    / "processed"
    / "san_matya_lexique_pro"
    / "overlap"
)

SUMMARY_JSON = OUTPUT_DIR / "matya_overlap_summary.json"
SOURCE_COUNTS_CSV = OUTPUT_DIR / "matya_overlap_by_source.csv"
NEW_FORMS_CSV = OUTPUT_DIR / "matya_recovered_new_forms.csv"
OVERLAP_FORMS_CSV = OUTPUT_DIR / "matya_recovered_overlap_forms.csv"


class AuditError(RuntimeError):
    pass


def normalize(value: Any) -> str:
    text = unicodedata.normalize("NFC", str(value or ""))
    return " ".join(text.split()).casefold()


def require(path: Path) -> None:
    if not path.exists():
        raise AuditError(f"Fichier introuvable : {path}")


def read_jsonl(path: Path) -> list[dict[str, Any]]:
    require(path)
    rows: list[dict[str, Any]] = []
    with path.open("r", encoding="utf-8") as fh:
        for lineno, line in enumerate(fh, 1):
            if not line.strip():
                continue
            try:
                item = json.loads(line)
            except json.JSONDecodeError as exc:
                raise AuditError(f"JSON invalide {path}:{lineno}: {exc}") from exc
            if isinstance(item, dict):
                rows.append(item)
    return rows


def add_form(mapping: dict[str, set[str]], raw: Any) -> None:
    if raw is None:
        return
    if isinstance(raw, list):
        for item in raw:
            add_form(mapping, item)
        return
    text = str(raw).strip()
    key = normalize(text)
    if key:
        mapping.setdefault(key, set()).add(text)


def recovered_forms() -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}
    for row in read_jsonl(RECOVERED):
        add_form(result, row.get("lexical_unit_stj"))
    return result


def reflex_forms() -> dict[str, set[str]]:
    require(REFLEX)
    result: dict[str, set[str]] = {}
    with REFLEX.open("r", encoding="utf-8-sig", newline="") as fh:
        reader = csv.DictReader(fh)
        if not reader.fieldnames or "Original Form" not in reader.fieldnames:
            raise AuditError(
                f"Colonne 'Original Form' absente de {REFLEX}. "
                f"Colonnes={reader.fieldnames}"
            )
        for row in reader:
            add_form(result, row.get("Original Form"))
    return result


def chikhapo_forms() -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}

    for row in read_jsonl(CHIKHAPO_STJ_ENG):
        add_form(result, row.get("source_word"))

    for row in read_jsonl(CHIKHAPO_ENG_STJ):
        add_form(result, row.get("target_translations"))

    return result


def panlex_forms() -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}
    for row in read_jsonl(PANLEX):
        source_row = row.get("source_row")
        if isinstance(source_row, dict):
            add_form(result, source_row.get("vocab"))
    return result


def asjp_forms() -> dict[str, set[str]]:
    require(ASJP)
    payload = json.loads(ASJP.read_text(encoding="utf-8"))
    entries = payload.get("entries", [])
    result: dict[str, set[str]] = {}

    if not isinstance(entries, list):
        raise AuditError(f"Champ entries invalide dans {ASJP}")

    for row in entries:
        if not isinstance(row, dict):
            continue
        if str(row.get("iso_639_3") or "").lower() != "stj":
            continue
        add_form(result, row.get("form") or row.get("value"))

    return result


def pct(part: int, whole: int) -> float:
    return round(part / whole * 100.0, 2) if whole else 0.0


def jaccard(a: set[str], b: set[str]) -> float:
    union = a | b
    return round(len(a & b) / len(union), 6) if union else 0.0


def main() -> None:
    sources = {
        "recovered_lexique_pro": recovered_forms(),
        "reflex": reflex_forms(),
        "chikhapo": chikhapo_forms(),
        "panlex": panlex_forms(),
        "asjp": asjp_forms(),
    }

    sets = {name: set(forms) for name, forms in sources.items()}
    recovered = sets["recovered_lexique_pro"]

    external_names = ["reflex", "chikhapo", "panlex", "asjp"]
    external_union: set[str] = set()
    for name in external_names:
        external_union |= sets[name]

    recovered_overlap_union = recovered & external_union
    recovered_new = recovered - external_union

    comparisons = []
    for name in external_names:
        other = sets[name]
        overlap = recovered & other
        comparisons.append(
            {
                "source": name,
                "source_unique_forms": len(other),
                "recovered_unique_forms": len(recovered),
                "exact_overlap": len(overlap),
                "recovered_coverage_pct": pct(len(overlap), len(recovered)),
                "source_coverage_pct": pct(len(overlap), len(other)),
                "jaccard": jaccard(recovered, other),
            }
        )

    pairwise = {}
    names = list(sets)
    for i, left in enumerate(names):
        for right in names[i + 1 :]:
            a, b = sets[left], sets[right]
            pairwise[f"{left}__{right}"] = {
                "left_unique": len(a),
                "right_unique": len(b),
                "overlap": len(a & b),
                "jaccard": jaccard(a, b),
            }

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "iso_639_3": "stj",
        "variety": "matya",
        "comparison_scope": "exact_string_overlap_after_nfc_casefold_whitespace",
        "linguistic_equivalence_inferred": False,
        "provenance_equivalence_inferred": False,
        "inputs": {
            "recovered": str(RECOVERED),
            "reflex": str(REFLEX),
            "chikhapo_stj_eng": str(CHIKHAPO_STJ_ENG),
            "chikhapo_eng_stj": str(CHIKHAPO_ENG_STJ),
            "panlex": str(PANLEX),
            "asjp": str(ASJP),
        },
        "summary": {
            "recovered_unique_forms": len(recovered),
            "reflex_unique_forms": len(sets["reflex"]),
            "chikhapo_unique_forms": len(sets["chikhapo"]),
            "panlex_unique_forms": len(sets["panlex"]),
            "asjp_unique_forms": len(sets["asjp"]),
            "external_union_unique_forms": len(external_union),
            "recovered_exact_overlap_external_union": len(recovered_overlap_union),
            "recovered_exact_new_vs_external_union": len(recovered_new),
            "recovered_overlap_external_union_pct": pct(
                len(recovered_overlap_union), len(recovered)
            ),
            "recovered_new_vs_external_union_pct": pct(
                len(recovered_new), len(recovered)
            ),
        },
        "recovered_vs_each_source": comparisons,
        "pairwise": pairwise,
        "notes": [
            "Le RAW n'est pas modifié.",
            "Un chevauchement de chaînes ne prouve pas qu'une source a copié une autre.",
            "Les formes différentes peuvent être des variantes orthographiques ou tonales et nécessitent une analyse séparée.",
            "La comparaison conserve les diacritiques et les tons ; elle est donc volontairement stricte.",
        ],
    }

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    SUMMARY_JSON.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8"
    )

    with SOURCE_COUNTS_CSV.open("w", encoding="utf-8-sig", newline="") as fh:
        fields = [
            "source",
            "source_unique_forms",
            "recovered_unique_forms",
            "exact_overlap",
            "recovered_coverage_pct",
            "source_coverage_pct",
            "jaccard",
        ]
        writer = csv.DictWriter(fh, fieldnames=fields)
        writer.writeheader()
        writer.writerows(comparisons)

    provenance: dict[str, list[str]] = {}
    for name in external_names:
        for key in sets[name]:
            provenance.setdefault(key, []).append(name)

    with NEW_FORMS_CSV.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=["normalized_form", "recovered_forms"],
        )
        writer.writeheader()
        for key in sorted(recovered_new):
            writer.writerow(
                {
                    "normalized_form": key,
                    "recovered_forms": " | ".join(sorted(sources["recovered_lexique_pro"][key])),
                }
            )

    with OVERLAP_FORMS_CSV.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=["normalized_form", "recovered_forms", "also_in_sources"],
        )
        writer.writeheader()
        for key in sorted(recovered_overlap_union):
            writer.writerow(
                {
                    "normalized_form": key,
                    "recovered_forms": " | ".join(sorted(sources["recovered_lexique_pro"][key])),
                    "also_in_sources": " | ".join(provenance.get(key, [])),
                }
            )

    s = payload["summary"]

    print("=== Audit de chevauchement Matya / stj ===")
    print(f"Corpus récupéré          : {s['recovered_unique_forms']} formes uniques")
    print(f"RefLex                   : {s['reflex_unique_forms']} formes uniques")
    print(f"ChiKhaPo                 : {s['chikhapo_unique_forms']} formes uniques")
    print(f"PanLex                   : {s['panlex_unique_forms']} formes uniques")
    print(f"ASJP                     : {s['asjp_unique_forms']} formes uniques")
    print()
    print(
        "Chevauchement récupéré ↔ union externe : "
        f"{s['recovered_exact_overlap_external_union']}"
    )
    print(
        "Formes récupérées nouvelles (exactes)  : "
        f"{s['recovered_exact_new_vs_external_union']}"
    )
    print(
        "Part nouvelle                           : "
        f"{s['recovered_new_vs_external_union_pct']}%"
    )
    print()
    for item in comparisons:
        print(
            f"- {item['source']}: overlap={item['exact_overlap']} "
            f"| couverture récupéré={item['recovered_coverage_pct']}% "
            f"| couverture source={item['source_coverage_pct']}%"
        )
    print()
    print(f"Rapport JSON : {SUMMARY_JSON}")
    print(f"CSV résumé   : {SOURCE_COUNTS_CSV}")
    print(f"Nouvelles    : {NEW_FORMS_CSV}")
    print(f"Chevauchement: {OVERLAP_FORMS_CSV}")


if __name__ == "__main__":
    main()
