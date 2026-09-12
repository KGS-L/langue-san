"""Extrait les occurrences lexicales de la wordlist Berthelette 2001.

Préconditions techniques déjà validées :
- PDF officiel récolté localement ;
- bloc lexical confirmé sur les pages PDF 41–63 ;
- glyphes Type3 `/Gxx` décodables à 100 % via SIL IPA93 -> Unicode ;
- mapping localité -> variété confirmé explicitement dans le PDF, page 64.

Le script extrait uniquement le bloc actuellement visible `012–231`.
Il conserve la représentation legacy `/Gxx`, la transcription Unicode source,
la localité et la provenance. Il ne corrige pas la langue, ne déduplique pas
les variantes et ne fabrique pas les concepts `001–011`, absents du bloc
textuellement repéré dans le PDF actuel.
"""

from __future__ import annotations

import argparse
import csv
import json
import re
from collections import Counter, defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from pypdf import PdfReader

try:
    from processors.decode_berthelette_ipa93 import (
        GLYPH_RE,
        BertheletteIPA93DecodeError,
        decode_legacy_sequence,
    )
except ModuleNotFoundError:  # exécution directe depuis processors/
    from decode_berthelette_ipa93 import (  # type: ignore
        GLYPH_RE,
        BertheletteIPA93DecodeError,
        decode_legacy_sequence,
    )


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_PDF = REPO_ROOT / "data" / "raw" / "berthelette" / "SILESR2002_005.pdf"
DEFAULT_CSV = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "wordlist_occurrences_012_231.csv"
)
DEFAULT_SUMMARY = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "wordlist_occurrences_012_231_summary.json"
)

SOURCE_NAME = "Berthelette 2001"
REPORT_ID = "SILESR-2002-005"
RIGHTS_STATUS = "archive_default_noncommercial_pending_pdf_confirmation"
TRANSCRIPTION_SYSTEM = "IPA (legacy SIL IPA93 decoded to Unicode)"
VALIDATION_STATUS = "external_unverified"

CONCEPT_RE = re.compile(r"^\s*(\d{3})\s+(.+?)\s*$")

LOCALITY_INFO: dict[str, dict[str, str | tuple[str, ...]]] = {
    "Toma": {"aliases": ("Toma",), "variety": "maka", "iso": "sbd"},
    "Kouy": {"aliases": ("Kouy",), "variety": "matya", "iso": "stj"},
    "Kassoum": {"aliases": ("Kassoum",), "variety": "matya", "iso": "stj"},
    "Toéni": {"aliases": ("Toéni", "Toeni", "Toé ni"), "variety": "matya", "iso": "stj"},
    "Bounou": {"aliases": ("Bounou",), "variety": "maya", "iso": "sym"},
    "Kiembara": {"aliases": ("Kiembara",), "variety": "maya", "iso": "sym"},
    "Bangassogo": {"aliases": ("Bangassogo",), "variety": "maya", "iso": "sym"},
    "Lankoué": {"aliases": ("Lankoué", "Lankoue", "Lankoé"), "variety": "maya", "iso": "sym"},
}

CSV_FIELDS = [
    "source",
    "report_id",
    "pdf_page",
    "concept_source_page",
    "concept_id",
    "concept_gloss_fr",
    "locality",
    "variety_claimed_by_source",
    "iso_639_3",
    "raw_legacy_glyphs",
    "decoded_transcription_source",
    "original_form_ipa",
    "transcription_system",
    "validation_status",
    "rights_status",
    "source_order",
]


class BertheletteWordlistExtractError(RuntimeError):
    """Erreur contrôlée pendant l'extraction lexicale Berthelette."""


def _localities_in_order(line: str) -> list[str]:
    """Retourne les localités reconnues dans leur ordre d'apparition."""
    hits: list[tuple[int, str]] = []
    for canonical, info in LOCALITY_INFO.items():
        aliases = info["aliases"]
        assert isinstance(aliases, tuple)
        best: int | None = None
        for alias in aliases:
            # Tolérer un ou plusieurs espaces dans les alias comme `Toé ni`.
            pattern = re.escape(alias).replace(r"\ ", r"\s+")
            match = re.search(rf"(?<!\w){pattern}(?!\w)", line, flags=re.IGNORECASE)
            if match and (best is None or match.start() < best):
                best = match.start()
        if best is not None:
            hits.append((best, canonical))

    # Une localité n'est ajoutée qu'une fois par ligne, même si un alias se répète.
    return [canonical for _, canonical in sorted(hits, key=lambda item: item[0])]


def _strip_source_brackets(decoded: str) -> str:
    value = (decoded or "").strip()
    if len(value) >= 2 and value.startswith("[") and value.endswith("]"):
        return value[1:-1]
    return value


def _form_from_tokens(tokens: list[str]) -> dict[str, Any]:
    raw = "".join(tokens)
    decoded = decode_legacy_sequence(raw)
    return {
        "raw_legacy_glyphs": raw,
        "decoded_transcription_source": decoded["decoded_unicode"],
        "original_form_ipa": _strip_source_brackets(decoded["decoded_unicode"]),
        "unresolved": decoded["unresolved"],
    }


def parse_page_texts(
    page_texts: list[tuple[int, str]],
    expected_start_id: int = 12,
    expected_end_id: int = 231,
) -> dict[str, Any]:
    """Parse des textes déjà extraits ; séparé du PDF pour faciliter les tests."""
    rows: list[dict[str, Any]] = []
    anomalies: list[dict[str, Any]] = []
    concepts: dict[int, dict[str, Any]] = {}

    current_id: int | None = None
    current_gloss: str | None = None
    current_concept_page: int | None = None
    pending_tokens: list[str] = []
    last_form: dict[str, Any] | None = None
    source_order = 0

    def record_dangling(reason: str, page: int, line_no: int) -> None:
        nonlocal pending_tokens
        if pending_tokens:
            anomalies.append(
                {
                    "type": "dangling_glyphs",
                    "reason": reason,
                    "page": page,
                    "line": line_no,
                    "concept_id": current_id,
                    "raw_legacy_glyphs": "".join(pending_tokens),
                }
            )
            pending_tokens = []

    for page_no, text in page_texts:
        for line_no, line in enumerate((text or "").splitlines(), start=1):
            concept_match = CONCEPT_RE.match(line)
            if concept_match:
                concept_id = int(concept_match.group(1))
                if expected_start_id <= concept_id <= expected_end_id:
                    record_dangling("new_concept", page_no, line_no)
                    current_id = concept_id
                    current_gloss = concept_match.group(2).strip()
                    current_concept_page = page_no
                    last_form = None
                    concepts.setdefault(
                        concept_id,
                        {"gloss": current_gloss, "first_page": page_no},
                    )
                    continue

            if current_id is None or current_gloss is None:
                continue

            line_tokens = GLYPH_RE.findall(line)
            if line_tokens:
                pending_tokens.extend(line_tokens)

            localities = _localities_in_order(line)
            if not localities:
                continue

            form: dict[str, Any] | None = None
            if pending_tokens:
                try:
                    form = _form_from_tokens(pending_tokens)
                except BertheletteIPA93DecodeError as exc:
                    anomalies.append(
                        {
                            "type": "decode_error",
                            "page": page_no,
                            "line": line_no,
                            "concept_id": current_id,
                            "message": str(exc),
                            "raw_legacy_glyphs": "".join(pending_tokens),
                        }
                    )
                pending_tokens = []
                if form is not None:
                    last_form = form
            elif last_form is not None:
                # Le PDF regroupe parfois plusieurs localités sous une même forme :
                # les localités suivantes n'ont alors aucun glyphe répété.
                form = last_form

            if form is None:
                anomalies.append(
                    {
                        "type": "locality_without_form",
                        "page": page_no,
                        "line": line_no,
                        "concept_id": current_id,
                        "localities": localities,
                        "text": line.strip(),
                    }
                )
                continue

            for locality in localities:
                info = LOCALITY_INFO[locality]
                source_order += 1
                rows.append(
                    {
                        "source": SOURCE_NAME,
                        "report_id": REPORT_ID,
                        "pdf_page": page_no,
                        "concept_source_page": current_concept_page,
                        "concept_id": f"{current_id:03d}",
                        "concept_gloss_fr": current_gloss,
                        "locality": locality,
                        "variety_claimed_by_source": info["variety"],
                        "iso_639_3": info["iso"],
                        "raw_legacy_glyphs": form["raw_legacy_glyphs"],
                        "decoded_transcription_source": form["decoded_transcription_source"],
                        "original_form_ipa": form["original_form_ipa"],
                        "transcription_system": TRANSCRIPTION_SYSTEM,
                        "validation_status": VALIDATION_STATUS,
                        "rights_status": RIGHTS_STATUS,
                        "source_order": source_order,
                    }
                )

    if pending_tokens:
        last_page = page_texts[-1][0] if page_texts else 0
        record_dangling("end_of_input", last_page, 0)

    observed_ids = sorted(concepts)
    expected_ids = list(range(expected_start_id, expected_end_id + 1))
    missing_ids = [value for value in expected_ids if value not in concepts]

    by_locality = Counter(row["locality"] for row in rows)
    by_iso = Counter(row["iso_639_3"] for row in rows)
    pair_counts: dict[tuple[str, str], int] = Counter(
        (row["concept_id"], row["locality"]) for row in rows
    )
    multi_form_pairs = [
        {"concept_id": concept_id, "locality": locality, "occurrences": count}
        for (concept_id, locality), count in sorted(pair_counts.items())
        if count > 1
    ]

    decode_errors = [item for item in anomalies if item["type"] == "decode_error"]
    rows_without_form = [row for row in rows if not str(row["original_form_ipa"]).strip()]

    technical_ok = (
        bool(rows)
        and not missing_ids
        and not decode_errors
        and not rows_without_form
        and observed_ids == expected_ids
    )

    return {
        "rows": rows,
        "summary": {
            "validation_scope": "technical_extraction_of_visible_wordlist_012_231_only",
            "expected_concept_range": [expected_start_id, expected_end_id],
            "concept_count": len(observed_ids),
            "concept_min": min(observed_ids) if observed_ids else None,
            "concept_max": max(observed_ids) if observed_ids else None,
            "missing_concept_ids": [f"{value:03d}" for value in missing_ids],
            "occurrence_count": len(rows),
            "counts_by_locality": dict(sorted(by_locality.items())),
            "counts_by_iso": dict(sorted(by_iso.items())),
            "multi_form_concept_locality_groups": multi_form_pairs,
            "anomaly_count": len(anomalies),
            "anomalies": anomalies,
            "rows_without_form": len(rows_without_form),
            "technical_ok": technical_ok,
            "notes": [
                "Les concepts 001–011 ne sont pas fabriqués : ils ne font pas partie du bloc 012–231 textuellement confirmé dans le PDF actuel.",
                "Plusieurs occurrences pour un même concept/localité sont conservées comme variantes source, sans déduplication.",
                "La transcription Unicode est un décodage technique SIL IPA93 ; elle n'est pas une validation linguistique.",
            ],
        },
    }


def extract_pdf(
    pdf_path: Path = DEFAULT_PDF,
    csv_path: Path = DEFAULT_CSV,
    summary_path: Path = DEFAULT_SUMMARY,
    start_page: int = 41,
    end_page: int = 63,
) -> dict[str, Any]:
    pdf_path = pdf_path.expanduser().resolve()
    csv_path = csv_path.expanduser().resolve()
    summary_path = summary_path.expanduser().resolve()

    if not pdf_path.exists():
        raise BertheletteWordlistExtractError(f"PDF introuvable : {pdf_path}")

    reader = PdfReader(str(pdf_path))
    if start_page < 1 or end_page > len(reader.pages) or start_page > end_page:
        raise BertheletteWordlistExtractError(
            f"Intervalle invalide {start_page}-{end_page} pour un PDF de {len(reader.pages)} pages."
        )

    page_texts = [
        (page_no, reader.pages[page_no - 1].extract_text() or "")
        for page_no in range(start_page, end_page + 1)
    ]
    parsed = parse_page_texts(page_texts)

    csv_path.parent.mkdir(parents=True, exist_ok=True)
    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        writer.writerows(parsed["rows"])

    summary = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": f"{SOURCE_NAME} / {REPORT_ID}",
        "pdf_path": str(pdf_path),
        "pages": {"start": start_page, "end": end_page},
        "csv_output": str(csv_path),
        **parsed["summary"],
    }
    summary_path.parent.mkdir(parents=True, exist_ok=True)
    summary_path.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")

    return {"rows": parsed["rows"], "summary": summary}


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Extrait les occurrences 012–231 de la wordlist Berthelette en Unicode IPA"
    )
    parser.add_argument("--pdf", type=Path, default=DEFAULT_PDF)
    parser.add_argument("--csv", type=Path, default=DEFAULT_CSV)
    parser.add_argument("--summary", type=Path, default=DEFAULT_SUMMARY)
    parser.add_argument("--start-page", type=int, default=41)
    parser.add_argument("--end-page", type=int, default=63)
    args = parser.parse_args()

    result = extract_pdf(args.pdf, args.csv, args.summary, args.start_page, args.end_page)
    summary = result["summary"]

    print("Extraction wordlist Berthelette :")
    print(f"- pages : {summary['pages']['start']}-{summary['pages']['end']}")
    print(f"- concepts : {summary['concept_count']} ({summary['concept_min']:03d}-{summary['concept_max']:03d})")
    print(f"- concepts manquants dans 012-231 : {summary['missing_concept_ids']}")
    print(f"- occurrences : {summary['occurrence_count']}")
    print(f"- par ISO : {summary['counts_by_iso']}")
    print(f"- par localité : {summary['counts_by_locality']}")
    print(f"- anomalies : {summary['anomaly_count']}")
    print(f"- groupes multi-formes : {len(summary['multi_form_concept_locality_groups'])}")
    print(f"- technical_ok : {summary['technical_ok']}")
    print(f"CSV : {summary['csv_output']}")
    print(f"Résumé : {args.summary}")
    print("Les concepts 001–011 restent hors de cette extraction tant qu'ils ne sont pas retrouvés dans la source.")


if __name__ == "__main__":
    main()
