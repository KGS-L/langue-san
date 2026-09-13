#!/usr/bin/env python3
"""
Phase 2 — quasi-doublons Matya (stj)

Objectif :
- partir des formes du LIFT récupéré ;
- exclure les doublons exacts déjà mesurés ;
- détecter rapidement les variantes de ponctuation / tons / diacritiques ;
- utiliser la traduction française RefLex comme indice supplémentaire ;
- NE JAMAIS fusionner automatiquement les formes.

Aucune donnée RAW n'est modifiée.
"""

from __future__ import annotations

import csv
import json
import re
import unicodedata
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

REPO = Path.home() / "Bureau" / "langue-san"

RECOVERED = (
    REPO / "data/processed/san_matya_lexique_pro/recovery/san_matya_entries.jsonl"
)
REFLEX = REPO / "data/raw/reflex/stj/units.csv"
CHIKHAPO_STJ_ENG = REPO / "data/raw/huggingface/chikhapo/stj_eng/stj_eng.jsonl"
CHIKHAPO_ENG_STJ = REPO / "data/raw/huggingface/chikhapo/eng_stj/eng_stj.jsonl"
PANLEX = REPO / "data/raw/huggingface/panlex/stj/train.jsonl"
ASJP = REPO / "data/raw/asjp/asjp_v21_san_wordlists.json"

OUT_DIR = REPO / "data/processed/san_matya_lexique_pro/overlap"
OUT_JSON = OUT_DIR / "matya_near_duplicate_summary.json"
OUT_CSV = OUT_DIR / "matya_near_duplicate_candidates.csv"
OUT_REMAINING = OUT_DIR / "matya_remaining_new_candidates.csv"

APOSTROPHES = {
    "’": "'",
    "‘": "'",
    "ʼ": "'",
    "ʹ": "'",
    "ꞌ": "'",
    "`": "'",
    "´": "'",
}
DASHES = {
    "‐": "-",
    "‑": "-",
    "‒": "-",
    "–": "-",
    "—": "-",
    "−": "-",
}
SUPERSUB_DIGITS = str.maketrans("", "", "₀₁₂₃₄₅₆₇₈₉⁰¹²³⁴⁵⁶⁷⁸⁹")


class AuditError(RuntimeError):
    pass


def require(path: Path) -> None:
    if not path.exists():
        raise AuditError(f"Fichier introuvable : {path}")


def nfc(value: Any) -> str:
    return unicodedata.normalize("NFC", str(value or ""))


def strict_key(value: Any) -> str:
    return " ".join(nfc(value).split()).casefold()


def punctuation_key(value: Any) -> str:
    text = nfc(value)
    for src, dst in APOSTROPHES.items():
        text = text.replace(src, dst)
    for src, dst in DASHES.items():
        text = text.replace(src, dst)

    text = " ".join(text.split()).casefold()

    # Les tirets morphologiques de bord sont ignorés uniquement pour l'audit.
    text = text.strip("- ")
    return text


def toneless_key(value: Any) -> str:
    text = punctuation_key(value)

    # Décomposition Unicode puis retrait des marques combinatoires
    # (tons/accents encodés comme combining marks).
    text = unicodedata.normalize("NFD", text)
    text = "".join(ch for ch in text if unicodedata.category(ch) != "Mn")
    text = unicodedata.normalize("NFC", text)

    # RefLex peut employer des chiffres en indice pour distinguer des entrées/homonymes.
    text = text.translate(SUPERSUB_DIGITS)

    # Espaces autour des apostrophes/tirets.
    text = re.sub(r"\s*'\s*", "'", text)
    text = re.sub(r"\s*-\s*", "-", text)
    text = text.strip("- ")
    text = " ".join(text.split())
    return text.casefold()


def gloss_key(value: Any) -> str:
    text = unicodedata.normalize("NFD", str(value or "").casefold())
    text = "".join(ch for ch in text if unicodedata.category(ch) != "Mn")
    text = re.sub(r"[^\w\s'-]+", " ", text, flags=re.UNICODE)
    return " ".join(text.split())


def read_jsonl(path: Path) -> list[dict[str, Any]]:
    require(path)
    rows = []
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


def values(value: Any) -> list[str]:
    if value is None:
        return []
    if isinstance(value, list):
        out = []
        for item in value:
            out.extend(values(item))
        return out
    text = str(value).strip()
    return [text] if text else []


def recovered_records() -> dict[str, dict[str, Any]]:
    result: dict[str, dict[str, Any]] = {}

    for entry in read_jsonl(RECOVERED):
        forms = values(entry.get("lexical_unit_stj"))
        glosses_fr = []

        senses = entry.get("senses")
        if isinstance(senses, list):
            for sense in senses:
                if isinstance(sense, dict):
                    glosses_fr.extend(values(sense.get("gloss_fr")))
                    glosses_fr.extend(values(sense.get("definition_fr")))

        for form in forms:
            key = strict_key(form)
            if not key:
                continue
            rec = result.setdefault(
                key,
                {
                    "forms": set(),
                    "entry_ids": set(),
                    "glosses_fr": set(),
                },
            )
            rec["forms"].add(form)
            if entry.get("entry_id"):
                rec["entry_ids"].add(str(entry["entry_id"]))
            rec["glosses_fr"].update(g for g in glosses_fr if g)

    return result


def external_add(
    store: dict[str, dict[str, Any]],
    *,
    source: str,
    form: Any,
    glosses: list[str] | None = None,
) -> None:
    for raw in values(form):
        key = strict_key(raw)
        if not key:
            continue
        rec = store.setdefault(
            key,
            {"forms": set(), "sources": set(), "glosses_fr": set()},
        )
        rec["forms"].add(raw)
        rec["sources"].add(source)
        if glosses:
            rec["glosses_fr"].update(g for g in glosses if g)


def load_external() -> dict[str, dict[str, Any]]:
    out: dict[str, dict[str, Any]] = {}

    # RefLex
    require(REFLEX)
    with REFLEX.open("r", encoding="utf-8-sig", newline="") as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            external_add(
                out,
                source="reflex",
                form=row.get("Original Form"),
                glosses=values(row.get("Original Translation")),
            )

    # ChiKhaPo stj -> eng
    for row in read_jsonl(CHIKHAPO_STJ_ENG):
        external_add(out, source="chikhapo", form=row.get("source_word"))

    # ChiKhaPo eng -> stj
    for row in read_jsonl(CHIKHAPO_ENG_STJ):
        external_add(out, source="chikhapo", form=row.get("target_translations"))

    # PanLex
    for row in read_jsonl(PANLEX):
        src = row.get("source_row")
        if isinstance(src, dict):
            external_add(out, source="panlex", form=src.get("vocab"))

    # ASJP stj
    require(ASJP)
    payload = json.loads(ASJP.read_text(encoding="utf-8"))
    for row in payload.get("entries", []):
        if (
            isinstance(row, dict)
            and str(row.get("iso_639_3") or "").lower() == "stj"
        ):
            external_add(
                out,
                source="asjp",
                form=row.get("form") or row.get("value"),
            )

    return out


def build_index(
    records: dict[str, dict[str, Any]],
    key_fn,
) -> dict[str, set[str]]:
    idx: dict[str, set[str]] = defaultdict(set)
    for strict, rec in records.items():
        for form in rec["forms"]:
            key = key_fn(form)
            if key:
                idx[key].add(strict)
    return idx


def gloss_overlap(a: set[str], b: set[str]) -> list[str]:
    ka = {gloss_key(x): x for x in a if gloss_key(x)}
    kb = {gloss_key(x): x for x in b if gloss_key(x)}
    common = sorted(set(ka) & set(kb))
    return [ka[k] for k in common]


def main() -> None:
    recovered = recovered_records()
    external = load_external()

    recovered_strict = set(recovered)
    external_strict = set(external)

    strict_new = recovered_strict - external_strict

    ext_punct = build_index(external, punctuation_key)
    ext_toneless = build_index(external, toneless_key)

    candidates = []
    remaining = []

    counts = {
        "strict_new_input": len(strict_new),
        "punctuation_variants": 0,
        "tone_or_diacritic_variants": 0,
        "soft_matches_with_same_french_gloss": 0,
        "remaining_after_soft_normalization": 0,
    }

    for rkey in sorted(strict_new):
        rrec = recovered[rkey]
        rforms = sorted(rrec["forms"])
        representative = rforms[0]

        pkey = punctuation_key(representative)
        tkey = toneless_key(representative)

        matched_stricts: set[str] = set()
        match_type = None

        punct_matches = ext_punct.get(pkey, set())
        if punct_matches:
            matched_stricts |= punct_matches
            match_type = "punctuation_or_boundary_marker"
            counts["punctuation_variants"] += 1
        else:
            tone_matches = ext_toneless.get(tkey, set())
            if tone_matches:
                matched_stricts |= tone_matches
                match_type = "tone_diacritic_or_homonym_marker"
                counts["tone_or_diacritic_variants"] += 1

        if not matched_stricts:
            remaining.append(
                {
                    "recovered_form": representative,
                    "all_recovered_forms": " | ".join(rforms),
                    "entry_ids": " | ".join(sorted(rrec["entry_ids"])),
                    "french_glosses": " | ".join(sorted(rrec["glosses_fr"])),
                }
            )
            continue

        external_forms = set()
        external_sources = set()
        external_glosses = set()

        for ext_strict in matched_stricts:
            erec = external[ext_strict]
            external_forms |= erec["forms"]
            external_sources |= erec["sources"]
            external_glosses |= erec["glosses_fr"]

        same_gloss = gloss_overlap(
            rrec["glosses_fr"],
            external_glosses,
        )

        if same_gloss:
            counts["soft_matches_with_same_french_gloss"] += 1

        candidates.append(
            {
                "recovered_form": representative,
                "all_recovered_forms": " | ".join(rforms),
                "entry_ids": " | ".join(sorted(rrec["entry_ids"])),
                "match_type": match_type,
                "external_forms": " | ".join(sorted(external_forms)),
                "external_sources": " | ".join(sorted(external_sources)),
                "recovered_french_glosses": " | ".join(
                    sorted(rrec["glosses_fr"])
                ),
                "external_french_glosses": " | ".join(
                    sorted(external_glosses)
                ),
                "same_french_gloss": " | ".join(same_gloss),
            }
        )

    counts["remaining_after_soft_normalization"] = len(remaining)
    counts["soft_variant_total"] = len(candidates)
    counts["soft_variant_pct_of_537"] = round(
        len(candidates) / len(strict_new) * 100, 2
    ) if strict_new else 0.0
    counts["remaining_pct_of_537"] = round(
        len(remaining) / len(strict_new) * 100, 2
    ) if strict_new else 0.0

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "iso_639_3": "stj",
        "variety": "matya",
        "method": {
            "strict": "NFC + casefold + whitespace",
            "punctuation": (
                "apostrophes/dashes harmonisés + marqueurs morphologiques "
                "de bord ignorés"
            ),
            "toneless": (
                "punctuation_key + retrait des combining marks + "
                "chiffres exposant/indice"
            ),
            "automatic_merge": False,
        },
        "summary": counts,
        "warning": (
            "Les formes restantes ne sont pas encore déclarées linguistiquement "
            "nouvelles. Elles sont seulement absentes après ces normalisations "
            "techniques conservatrices."
        ),
    }

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    OUT_JSON.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    candidate_fields = [
        "recovered_form",
        "all_recovered_forms",
        "entry_ids",
        "match_type",
        "external_forms",
        "external_sources",
        "recovered_french_glosses",
        "external_french_glosses",
        "same_french_gloss",
    ]

    with OUT_CSV.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=candidate_fields)
        writer.writeheader()
        writer.writerows(candidates)

    remaining_fields = [
        "recovered_form",
        "all_recovered_forms",
        "entry_ids",
        "french_glosses",
    ]

    with OUT_REMAINING.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=remaining_fields)
        writer.writeheader()
        writer.writerows(remaining)

    print("=== Matya / stj — Phase 2 quasi-doublons ===")
    print(f"Candidats nouveaux stricts au départ : {counts['strict_new_input']}")
    print(
        "Variantes ponctuation / tirets        : "
        f"{counts['punctuation_variants']}"
    )
    print(
        "Variantes tons/diacritiques           : "
        f"{counts['tone_or_diacritic_variants']}"
    )
    print(
        "Quasi-doublons techniques total       : "
        f"{counts['soft_variant_total']} "
        f"({counts['soft_variant_pct_of_537']}%)"
    )
    print(
        "Dont même gloss français              : "
        f"{counts['soft_matches_with_same_french_gloss']}"
    )
    print(
        "Restants après normalisation douce     : "
        f"{counts['remaining_after_soft_normalization']} "
        f"({counts['remaining_pct_of_537']}%)"
    )
    print()
    print(f"Rapport : {OUT_JSON}")
    print(f"Quasi-doublons : {OUT_CSV}")
    print(f"Restants : {OUT_REMAINING}")


if __name__ == "__main__":
    main()
