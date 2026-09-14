#!/usr/bin/env python3
"""Audit de provenance des wordlists ASJP SAN.

Objectifs :
- séparer les entrées par `language_id` ASJP au lieu de raisonner seulement par ISO ;
- mesurer la part provenant de `SAMO_MATYA` (Morse 1967) ;
- distinguer `SAMO_MATYA_2` (Morris et al. 2011) ;
- distinguer les wordlists Maya `SAMO_MAYA` et `MAYA_SAMO` ;
- conserver le RAW inchangé.

Le script lit le JSON généré par `collectors/asjp.py` et écrit uniquement des
résultats d'audit dans `data/processed/asjp/provenance/`.
"""

from __future__ import annotations

import csv
import json
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

REPO = Path.home() / "Bureau" / "langue-san"
RAW = REPO / "data/raw/asjp/asjp_v21_san_wordlists.json"
OUT_DIR = REPO / "data/processed/asjp/provenance"
OUT_JSON = OUT_DIR / "asjp_san_provenance_summary.json"
OUT_CSV = OUT_DIR / "asjp_san_provenance_by_language_id.csv"

KNOWN_PROVENANCE = {
    "SAMO_MATYA": {
        "variety": "matya",
        "iso_639_3": "stj",
        "upstream_reference": "Morse 1967 — The Question of 'Samogo'",
    },
    "SAMO_MATYA_2": {
        "variety": "matya",
        "iso_639_3": "stj",
        "upstream_reference": "Morris et al. 2011 — Lexique San Matya avec guide d'orthographe",
    },
    "SAMO_MAYA": {
        "variety": "maya",
        "iso_639_3": "sym",
        "upstream_reference": "Morris, Koussoubé & Seme 2011 — Lexique San Mayaa avec guide d'orthographe",
    },
    "MAYA_SAMO": {
        "variety": "maya",
        "iso_639_3": "sym",
        "upstream_reference": "Berthelette 2001 — Sociolinguistic survey report for the San language",
    },
}


def load_entries() -> list[dict[str, Any]]:
    if not RAW.exists():
        raise SystemExit(f"Fichier ASJP introuvable : {RAW}")

    payload = json.loads(RAW.read_text(encoding="utf-8"))
    entries = payload.get("entries")
    if not isinstance(entries, list):
        raise SystemExit("Champ `entries` absent ou invalide dans le RAW ASJP")
    return [row for row in entries if isinstance(row, dict)]


def main() -> None:
    entries = load_entries()

    grouped: dict[str, list[dict[str, Any]]] = defaultdict(list)
    iso_counts: Counter[str] = Counter()

    for row in entries:
        language_id = str(row.get("language_id") or "").strip() or "UNKNOWN"
        grouped[language_id].append(row)
        iso_counts[str(row.get("iso_639_3") or "").strip()] += 1

    rows: list[dict[str, Any]] = []
    for language_id in sorted(grouped):
        items = grouped[language_id]
        first = items[0]
        known = KNOWN_PROVENANCE.get(language_id, {})
        forms = {
            str(item.get("form") or item.get("value") or "").strip()
            for item in items
            if str(item.get("form") or item.get("value") or "").strip()
        }
        concepts = {
            str(item.get("concept") or "").strip()
            for item in items
            if str(item.get("concept") or "").strip()
        }

        rows.append(
            {
                "language_id": language_id,
                "language_name": first.get("language_name"),
                "iso_639_3": first.get("iso_639_3"),
                "glottocode": first.get("glottocode"),
                "variety": known.get("variety"),
                "occurrence_count": len(items),
                "unique_form_count": len(forms),
                "concept_count": len(concepts),
                "upstream_reference": known.get("upstream_reference"),
                "known_provenance": language_id in KNOWN_PROVENANCE,
            }
        )

    target_ids = ["SAMO_MATYA", "SAMO_MATYA_2", "SAMO_MAYA", "MAYA_SAMO"]
    by_id = {row["language_id"]: row for row in rows}

    summary = {
        "source": str(RAW),
        "total_entries": len(entries),
        "counts_by_iso": dict(sorted(iso_counts.items())),
        "language_id_count": len(grouped),
        "targets": {key: by_id.get(key) for key in target_ids},
        "all_language_ids": rows,
        "warnings": [
            "Un même code ISO peut regrouper plusieurs wordlists de provenance différente.",
            "Les volumes ASJP ne doivent pas être additionnés aux sources primaires comme s'il s'agissait de données indépendantes.",
            "MAYA_SAMO est dérivé de Berthelette 2001 et ne constitue pas une source indépendante de Berthelette.",
            "SAMO_MATYA et SAMO_MATYA_2 ont deux provenances historiques distinctes malgré le même ISO stj.",
        ],
    }

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    OUT_JSON.write_text(
        json.dumps(summary, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    fields = [
        "language_id",
        "language_name",
        "iso_639_3",
        "glottocode",
        "variety",
        "occurrence_count",
        "unique_form_count",
        "concept_count",
        "upstream_reference",
        "known_provenance",
    ]
    with OUT_CSV.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=fields)
        writer.writeheader()
        writer.writerows(rows)

    print("=== ASJP SAN — audit de provenance ===")
    print(f"Entrées totales : {len(entries)}")
    print(f"Par ISO         : {dict(sorted(iso_counts.items()))}")
    print()

    for key in target_ids:
        row = by_id.get(key)
        if not row:
            print(f"{key:15} : ABSENT")
            continue
        print(
            f"{key:15} : {row['occurrence_count']} occurrences | "
            f"{row['unique_form_count']} formes uniques | "
            f"{row['concept_count']} concepts"
        )
        print(f"  ↳ {row['upstream_reference']}")

    print()
    print(f"Résumé JSON : {OUT_JSON}")
    print(f"Détail CSV  : {OUT_CSV}")


if __name__ == "__main__":
    main()
