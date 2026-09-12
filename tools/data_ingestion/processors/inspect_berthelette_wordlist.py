"""Inspection ciblée des pages de wordlist du rapport Berthelette 2001.

Cette étape intervient après `inspect_berthelette_pdf.py`. Le PDF est déjà
récolté et son texte est extractible sur toutes les pages. Ici on cherche le ou
les blocs continus où plusieurs localités SAN apparaissent ensemble, puis on
exporte le texte en mode `layout` afin de préserver au mieux les colonnes du
PDF avant de construire un parseur lexical.

Important : ce script ne crée pas encore de dataset lexical. Il ne corrige,
ne déduplique et ne normalise aucune forme SAN.
"""

from __future__ import annotations

import argparse
import json
import unicodedata
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from pypdf import PdfReader


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_PDF = REPO_ROOT / "data" / "raw" / "berthelette" / "SILESR2002_005.pdf"
DEFAULT_OUTPUT = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "wordlist_section_inventory.json"
)
DEFAULT_TEXT_OUTPUT = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "wordlist_candidate_text.txt"
)

LOCALITY_ALIASES: dict[str, tuple[str, ...]] = {
    "Toma": ("Toma",),
    "Kassoum": ("Kassoum",),
    "Kouy": ("Kouy",),
    "Toéni": ("Toéni", "Toeni"),
    "Bounou": ("Bounou",),
    "Kiembara": ("Kiembara",),
    "Bangassogo": ("Bangassogo",),
    "Lankoué": ("Lankoué", "Lankoue"),
}

STRUCTURE_TERMS = (
    "appendix",
    "appendices",
    "wordlist",
    "word list",
    "lexicostat",
    "vocabulary",
    "comparative",
    "gloss",
    "french",
    "english",
)


class BertheletteWordlistInspectError(RuntimeError):
    """Erreur contrôlée pendant l'inspection ciblée des wordlists."""


def _normalise(text: str) -> str:
    return " ".join(
        unicodedata.normalize("NFKD", text or "")
        .encode("ascii", "ignore")
        .decode("ascii")
        .casefold()
        .split()
    )


def localities_in_text(text: str) -> list[str]:
    normalized = _normalise(text)
    found: list[str] = []
    for canonical, aliases in LOCALITY_ALIASES.items():
        if any(_normalise(alias) in normalized for alias in aliases):
            found.append(canonical)
    return found


def contiguous_ranges(pages: list[int]) -> list[tuple[int, int]]:
    if not pages:
        return []
    ordered = sorted(set(pages))
    ranges: list[tuple[int, int]] = []
    start = previous = ordered[0]
    for page in ordered[1:]:
        if page == previous + 1:
            previous = page
            continue
        ranges.append((start, previous))
        start = previous = page
    ranges.append((start, previous))
    return ranges


def _extract_layout(page: Any) -> str:
    try:
        return page.extract_text(extraction_mode="layout") or ""
    except TypeError:
        # Compatibilité défensive si une version de pypdf ne reconnaît pas
        # extraction_mode, même si requirements.txt impose une version récente.
        return page.extract_text() or ""


def _preview(text: str, limit: int = 1200) -> str:
    compact_lines = [line.rstrip() for line in (text or "").splitlines() if line.strip()]
    value = "\n".join(compact_lines[:30])
    return value if len(value) <= limit else value[:limit] + "…"


def inspect_wordlist_blocks(
    pdf_path: Path = DEFAULT_PDF,
    output_path: Path = DEFAULT_OUTPUT,
    text_output_path: Path = DEFAULT_TEXT_OUTPUT,
    min_localities: int = 6,
) -> dict[str, Any]:
    pdf_path = pdf_path.expanduser().resolve()
    output_path = output_path.expanduser().resolve()
    text_output_path = text_output_path.expanduser().resolve()

    if not pdf_path.exists():
        raise BertheletteWordlistInspectError(f"PDF Berthelette introuvable : {pdf_path}")
    if min_localities < 2 or min_localities > len(LOCALITY_ALIASES):
        raise BertheletteWordlistInspectError(
            f"min_localities doit être compris entre 2 et {len(LOCALITY_ALIASES)}."
        )

    reader = PdfReader(str(pdf_path))
    page_info: list[dict[str, Any]] = []
    candidate_pages: list[int] = []
    page_text: dict[int, str] = {}

    for page_number, page in enumerate(reader.pages, start=1):
        text = _extract_layout(page)
        page_text[page_number] = text
        localities = localities_in_text(text)
        normalized = _normalise(text)
        structure_terms = [term for term in STRUCTURE_TERMS if _normalise(term) in normalized]
        is_candidate = len(localities) >= min_localities
        if is_candidate:
            candidate_pages.append(page_number)
        page_info.append(
            {
                "page": page_number,
                "text_chars": len(text.strip()),
                "localities": localities,
                "locality_count": len(localities),
                "structure_terms": structure_terms,
                "candidate": is_candidate,
            }
        )

    ranges = contiguous_ranges(candidate_pages)
    range_info: list[dict[str, Any]] = []
    for start, end in ranges:
        pages = list(range(start, end + 1))
        all_localities = sorted(
            {loc for p in pages for loc in localities_in_text(page_text.get(p, ""))}
        )
        range_info.append(
            {
                "start_page": start,
                "end_page": end,
                "page_count": end - start + 1,
                "localities": all_localities,
                "first_page_preview": _preview(page_text.get(start, "")),
                "last_page_preview": _preview(page_text.get(end, "")),
            }
        )

    # Conserver le texte complet des pages candidates, avec marqueurs de page.
    text_output_path.parent.mkdir(parents=True, exist_ok=True)
    blocks: list[str] = []
    for page_number in candidate_pages:
        blocks.append(
            f"===== PDF PAGE {page_number} =====\n{page_text.get(page_number, '').rstrip()}\n"
        )
    text_output_path.write_text("\n".join(blocks), encoding="utf-8")

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "Berthelette 2001 / SILESR-2002-005",
        "validation_scope": "wordlist_block_detection_only",
        "pdf_path": str(pdf_path),
        "page_count": len(reader.pages),
        "min_localities_per_candidate_page": min_localities,
        "candidate_pages": candidate_pages,
        "candidate_ranges": range_info,
        "canonical_localities": list(LOCALITY_ALIASES),
        "text_output": str(text_output_path),
        "pages": page_info,
        "notes": [
            "Une page candidate contient plusieurs localités SAN; cela ne suffit pas à prouver qu'elle est lexicale.",
            "Les aperçus servent à confirmer manuellement le début et la fin du bloc avant parsing.",
            "Le texte complet candidat est exporté en mode layout pour préserver les colonnes autant que possible.",
            "Aucune ligne lexicale n'est encore créée à cette étape.",
        ],
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Repère les blocs comparatifs multi-localités du PDF Berthelette"
    )
    parser.add_argument("--pdf", type=Path, default=DEFAULT_PDF)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--text-output", type=Path, default=DEFAULT_TEXT_OUTPUT)
    parser.add_argument("--min-localities", type=int, default=6)
    args = parser.parse_args()

    payload = inspect_wordlist_blocks(
        pdf_path=args.pdf,
        output_path=args.output,
        text_output_path=args.text_output,
        min_localities=args.min_localities,
    )

    print("Inspection ciblée wordlist Berthelette :")
    print(f"- pages candidates : {payload['candidate_pages']}")
    print("- blocs continus :")
    for item in payload["candidate_ranges"]:
        print(
            f"  pages {item['start_page']}-{item['end_page']} "
            f"({item['page_count']} pages), localités={', '.join(item['localities'])}"
        )
        print("  aperçu début :")
        print(item["first_page_preview"])
        if item["end_page"] != item["start_page"]:
            print("  aperçu fin :")
            print(item["last_page_preview"])
    print(f"- texte candidat : {payload['text_output']}")
    print(f"- rapport : {args.output}")
    print("Aucune donnée lexicale finale n'a été écrite.")


if __name__ == "__main__":
    main()
