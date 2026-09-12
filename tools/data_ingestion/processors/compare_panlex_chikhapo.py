"""Mesure le chevauchement lexical exact PanLex ↔ ChiKhaPo pour le Matya (stj).

La comparaison est analytique uniquement. Les formes sont normalisées en NFC,
casefold et espaces condensés pour mesurer les correspondances exactes de chaîne.
Aucune équivalence linguistique, étymologique ou de provenance n'est déduite.
"""

from __future__ import annotations

import argparse
import json
import unicodedata
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
PANLEX_PATH = REPO_ROOT / "data" / "raw" / "huggingface" / "panlex" / "stj" / "train.jsonl"
CHIKHAPO_STJ_ENG = REPO_ROOT / "data" / "raw" / "huggingface" / "chikhapo" / "stj_eng" / "stj_eng.jsonl"
CHIKHAPO_ENG_STJ = REPO_ROOT / "data" / "raw" / "huggingface" / "chikhapo" / "eng_stj" / "eng_stj.jsonl"
DEFAULT_OUTPUT = REPO_ROOT / "data" / "processed" / "huggingface" / "panlex_vs_chikhapo_stj.json"


class PanLexChiKhaPoCompareError(RuntimeError):
    """Erreur contrôlée pendant la comparaison PanLex ↔ ChiKhaPo."""


def normalize_form(value: Any) -> str:
    text = unicodedata.normalize("NFC", str(value or ""))
    return " ".join(text.split()).casefold()


def _read_jsonl(path: Path) -> list[dict[str, Any]]:
    if not path.exists():
        raise PanLexChiKhaPoCompareError(f"Fichier introuvable : {path}")
    rows: list[dict[str, Any]] = []
    with path.open("r", encoding="utf-8") as handle:
        for raw_line in handle:
            if not raw_line.strip():
                continue
            try:
                row = json.loads(raw_line)
            except json.JSONDecodeError as exc:
                raise PanLexChiKhaPoCompareError(f"JSON invalide dans {path}: {exc}") from exc
            if isinstance(row, dict):
                rows.append(row)
    return rows


def panlex_stj_forms(rows: list[dict[str, Any]]) -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}
    for row in rows:
        source_row = row.get("source_row")
        if not isinstance(source_row, dict):
            continue
        raw = source_row.get("vocab")
        normalized = normalize_form(raw)
        if not normalized:
            continue
        result.setdefault(normalized, set()).add(str(raw))
    return result


def chikhapo_source_stj_forms(rows: list[dict[str, Any]]) -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}
    for row in rows:
        raw = row.get("source_word")
        normalized = normalize_form(raw)
        if not normalized:
            continue
        result.setdefault(normalized, set()).add(str(raw))
    return result


def _target_values(value: Any) -> list[str]:
    if isinstance(value, list):
        return [str(item) for item in value if str(item).strip()]
    if value is None:
        return []
    text = str(value)
    return [text] if text.strip() else []


def chikhapo_target_stj_forms(rows: list[dict[str, Any]]) -> dict[str, set[str]]:
    result: dict[str, set[str]] = {}
    for row in rows:
        for raw in _target_values(row.get("target_translations")):
            normalized = normalize_form(raw)
            if not normalized:
                continue
            result.setdefault(normalized, set()).add(raw)
    return result


def _pct(part: int, whole: int) -> float:
    return round((part / whole * 100.0), 2) if whole else 0.0


def compare(
    panlex_path: Path = PANLEX_PATH,
    chikhapo_stj_eng: Path = CHIKHAPO_STJ_ENG,
    chikhapo_eng_stj: Path = CHIKHAPO_ENG_STJ,
    output: Path = DEFAULT_OUTPUT,
) -> dict[str, Any]:
    panlex_rows = _read_jsonl(panlex_path)
    stj_eng_rows = _read_jsonl(chikhapo_stj_eng)
    eng_stj_rows = _read_jsonl(chikhapo_eng_stj)

    panlex = panlex_stj_forms(panlex_rows)
    chi_sources = chikhapo_source_stj_forms(stj_eng_rows)
    chi_targets = chikhapo_target_stj_forms(eng_stj_rows)

    p = set(panlex)
    cs = set(chi_sources)
    ct = set(chi_targets)
    cu = cs | ct

    overlap_sources = p & cs
    overlap_targets = p & ct
    overlap_union = p & cu
    union_all = p | cu

    samples = []
    for normalized in sorted(overlap_union)[:100]:
        samples.append(
            {
                "normalized_form": normalized,
                "panlex_forms": sorted(panlex.get(normalized, set())),
                "chikhapo_source_forms": sorted(chi_sources.get(normalized, set())),
                "chikhapo_target_forms": sorted(chi_targets.get(normalized, set())),
            }
        )

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "iso_639_3": "stj",
        "variety": "matya",
        "comparison_scope": "exact_string_overlap_after_nfc_casefold_whitespace",
        "linguistic_equivalence_inferred": False,
        "provenance_equivalence_inferred": False,
        "inputs": {
            "panlex": str(panlex_path),
            "chikhapo_stj_eng": str(chikhapo_stj_eng),
            "chikhapo_eng_stj": str(chikhapo_eng_stj),
        },
        "summary": {
            "panlex_rows": len(panlex_rows),
            "panlex_unique_stj_forms": len(p),
            "chikhapo_stj_eng_rows": len(stj_eng_rows),
            "chikhapo_unique_stj_source_forms": len(cs),
            "chikhapo_eng_stj_rows": len(eng_stj_rows),
            "chikhapo_unique_stj_target_forms": len(ct),
            "chikhapo_unique_stj_forms_union": len(cu),
            "panlex_overlap_chikhapo_sources": len(overlap_sources),
            "panlex_overlap_chikhapo_targets": len(overlap_targets),
            "panlex_overlap_chikhapo_union": len(overlap_union),
            "panlex_coverage_by_chikhapo_union_pct": _pct(len(overlap_union), len(p)),
            "chikhapo_union_coverage_by_panlex_pct": _pct(len(overlap_union), len(cu)),
            "jaccard_panlex_chikhapo_union": round(len(overlap_union) / len(union_all), 6) if union_all else 0.0,
        },
        "overlap_sample": samples,
        "notes": [
            "Le chevauchement de chaînes n'établit pas que ChiKhaPo a copié PanLex, même si PanLex est déclaré comme une de ses sources amont.",
            "Les formes non identiques peuvent tout de même être linguistiquement liées; ce rapport ne fait aucune translittération ni fuzzy matching.",
            "Le RAW n'est pas modifié et aucun dédoublonnage automatique n'est appliqué.",
        ],
    }

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="Comparer les formes Matya PanLex et ChiKhaPo")
    parser.add_argument("--panlex", type=Path, default=PANLEX_PATH)
    parser.add_argument("--chikhapo-stj-eng", type=Path, default=CHIKHAPO_STJ_ENG)
    parser.add_argument("--chikhapo-eng-stj", type=Path, default=CHIKHAPO_ENG_STJ)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    payload = compare(args.panlex, args.chikhapo_stj_eng, args.chikhapo_eng_stj, args.output)
    s = payload["summary"]
    print("Comparaison PanLex ↔ ChiKhaPo (stj / Matya) :")
    print(f"- PanLex : {s['panlex_unique_stj_forms']} formes uniques")
    print(f"- ChiKhaPo stj_eng : {s['chikhapo_unique_stj_source_forms']} formes source uniques")
    print(f"- ChiKhaPo eng_stj : {s['chikhapo_unique_stj_target_forms']} formes cible uniques")
    print(f"- ChiKhaPo union : {s['chikhapo_unique_stj_forms_union']} formes uniques")
    print(f"- Chevauchement PanLex ↔ ChiKhaPo union : {s['panlex_overlap_chikhapo_union']} formes")
    print(f"- Couverture de PanLex par ChiKhaPo : {s['panlex_coverage_by_chikhapo_union_pct']}%")
    print(f"- Couverture de ChiKhaPo par PanLex : {s['chikhapo_union_coverage_by_panlex_pct']}%")
    print(f"- Jaccard : {s['jaccard_panlex_chikhapo_union']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
