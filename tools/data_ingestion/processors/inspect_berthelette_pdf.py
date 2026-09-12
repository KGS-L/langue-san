"""Inspection structurelle du PDF Berthelette 2001 sans OCR.

Objectifs :
- vérifier que le PDF RAW est lisible par une bibliothèque PDF standard ;
- compter les pages et le texte extractible ;
- repérer les pages candidates pour les droits, annexes/wordlists et localités ;
- produire un inventaire JSON avant toute extraction lexicale.

Cette étape n'effectue aucun OCR, aucune correction linguistique et aucune
extraction de dataset final. Si certaines pages sont non extractibles, elles sont
simplement signalées pour inspection visuelle/OCR ultérieur en dernier recours.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import re
import unicodedata
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from pypdf import PdfReader


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_PDF = REPO_ROOT / "data" / "raw" / "berthelette" / "SILESR2002_005.pdf"
DEFAULT_METADATA = REPO_ROOT / "data" / "raw" / "berthelette" / "metadata.json"
DEFAULT_OUTPUT = REPO_ROOT / "data" / "processed" / "berthelette" / "pdf_inventory.json"

RIGHTS_TERMS = [
    "copyright",
    "creative commons",
    "cc by",
    "license",
    "licence",
    "noncommercial",
    "non-commercial",
    "all rights reserved",
]

LEXICAL_TERMS = [
    "wordlist",
    "word list",
    "wordlists",
    "lexical",
    "lexicostat",
    "vocabulary",
    "appendix",
    "appendices",
]

LOCALITY_TERMS = [
    "Toma",
    "Kassoum",
    "Kouy",
    "Toéni",
    "Toeni",
    "Bounou",
    "Kiembara",
    "Bangassogo",
    "Lankoué",
    "Lankoue",
]

VARIETY_TERMS = ["Maka", "Matya", "Maya", "Samo", "San"]


class BertheletteInspectError(RuntimeError):
    """Erreur contrôlée pendant l'inspection structurelle du PDF."""


def _sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def _normalise(text: str) -> str:
    return " ".join(
        unicodedata.normalize("NFKD", text or "")
        .encode("ascii", "ignore")
        .decode("ascii")
        .casefold()
        .split()
    )


def _find_terms(text: str, terms: list[str]) -> list[str]:
    normalized = _normalise(text)
    found: list[str] = []
    for term in terms:
        if _normalise(term) in normalized and term not in found:
            found.append(term)
    return found


def _snippet(text: str, term: str, radius: int = 180) -> str | None:
    if not text:
        return None
    normalized_term = _normalise(term)
    if not normalized_term:
        return None

    # Recherche tolérante aux accents/casse, puis extrait un contexte depuis le
    # texte original. Si le positionnement normalisé diverge trop, on renvoie le
    # début compacté de la page plutôt que d'inventer une position.
    compact = " ".join(text.split())
    normalized_compact = _normalise(compact)
    idx = normalized_compact.find(normalized_term)
    if idx < 0:
        return None
    if len(normalized_compact) != len(compact):
        # Les normalisations Unicode changent parfois les offsets ; un extrait
        # approximatif de la page reste plus sûr qu'un faux offset précis.
        return compact[: min(len(compact), radius * 2)]
    start = max(0, idx - radius)
    end = min(len(compact), idx + len(term) + radius)
    return compact[start:end]


def inspect_pdf(
    pdf_path: Path = DEFAULT_PDF,
    metadata_path: Path = DEFAULT_METADATA,
    output_path: Path = DEFAULT_OUTPUT,
) -> dict[str, Any]:
    pdf_path = pdf_path.expanduser().resolve()
    metadata_path = metadata_path.expanduser().resolve()
    output_path = output_path.expanduser().resolve()

    if not pdf_path.exists():
        raise BertheletteInspectError(f"PDF Berthelette introuvable : {pdf_path}")

    reader = PdfReader(str(pdf_path))
    page_count = len(reader.pages)
    if page_count == 0:
        raise BertheletteInspectError("Le PDF Berthelette ne contient aucune page.")

    local_metadata: dict[str, Any] = {}
    if metadata_path.exists():
        try:
            local_metadata = json.loads(metadata_path.read_text(encoding="utf-8"))
        except json.JSONDecodeError as exc:
            raise BertheletteInspectError(f"metadata.json invalide : {metadata_path}") from exc

    actual_sha = _sha256(pdf_path)
    expected_sha = str(local_metadata.get("sha256") or "")
    sha_matches_metadata = not expected_sha or expected_sha == actual_sha

    pages_with_text = 0
    pages_without_text: list[int] = []
    total_text_chars = 0
    page_summaries: list[dict[str, Any]] = []
    category_pages: dict[str, list[int]] = defaultdict(list)
    term_pages: dict[str, list[int]] = defaultdict(list)
    snippets: list[dict[str, Any]] = []

    for index, page in enumerate(reader.pages, start=1):
        try:
            text = page.extract_text() or ""
            extraction_error = None
        except Exception as exc:  # pypdf peut échouer sur une page isolée
            text = ""
            extraction_error = f"{type(exc).__name__}: {exc}"

        chars = len(text.strip())
        total_text_chars += chars
        if chars:
            pages_with_text += 1
        else:
            pages_without_text.append(index)

        rights_hits = _find_terms(text, RIGHTS_TERMS)
        lexical_hits = _find_terms(text, LEXICAL_TERMS)
        locality_hits = _find_terms(text, LOCALITY_TERMS)
        variety_hits = _find_terms(text, VARIETY_TERMS)

        for category, hits in (
            ("rights", rights_hits),
            ("lexical", lexical_hits),
            ("locality", locality_hits),
            ("variety", variety_hits),
        ):
            if hits:
                category_pages[category].append(index)
            for term in hits:
                term_pages[term].append(index)

        for term in (rights_hits + lexical_hits + locality_hits):
            excerpt = _snippet(text, term)
            if excerpt:
                snippets.append({"page": index, "term": term, "snippet": excerpt})

        page_summaries.append(
            {
                "page": index,
                "text_chars": chars,
                "text_extractable": bool(chars),
                "extraction_error": extraction_error,
                "rights_terms": rights_hits,
                "lexical_terms": lexical_hits,
                "locality_terms": locality_hits,
                "variety_terms": variety_hits,
            }
        )

    text_coverage = pages_with_text / page_count if page_count else 0.0
    metadata_obj = reader.metadata or {}
    pdf_metadata = {str(key): str(value) for key, value in metadata_obj.items() if value is not None}

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "Berthelette 2001 / SILESR-2002-005",
        "validation_scope": "pdf_structure_and_text_extractability_only",
        "ocr_used": False,
        "pdf_path": str(pdf_path),
        "metadata_path": str(metadata_path) if metadata_path.exists() else None,
        "sha256": actual_sha,
        "sha256_matches_harvest_metadata": sha_matches_metadata,
        "file_bytes": pdf_path.stat().st_size,
        "page_count": page_count,
        "expected_pages_from_catalog": local_metadata.get("expected_pages", 75),
        "pages_with_extractable_text": pages_with_text,
        "pages_without_extractable_text": pages_without_text,
        "text_coverage_ratio": round(text_coverage, 4),
        "total_extracted_text_chars": total_text_chars,
        "pdf_metadata": pdf_metadata,
        "candidate_pages": {
            "rights": sorted(set(category_pages.get("rights", []))),
            "lexical_or_appendix": sorted(set(category_pages.get("lexical", []))),
            "localities": sorted(set(category_pages.get("locality", []))),
            "varieties": sorted(set(category_pages.get("variety", []))),
        },
        "term_pages": {key: sorted(set(value)) for key, value in sorted(term_pages.items())},
        "snippets": snippets[:80],
        "pages": page_summaries,
        "technical_ok": sha_matches_metadata and page_count > 0 and text_coverage >= 0.8,
        "notes": [
            "technical_ok ne valide ni la langue ni les droits de réutilisation.",
            "Aucun OCR n'est lancé automatiquement. Les pages non extractibles sont seulement signalées.",
            "Les pages candidates servent à guider l'inspection suivante; elles ne constituent pas encore une extraction lexicale.",
        ],
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Inspecte la structure et le texte extractible du PDF Berthelette sans OCR"
    )
    parser.add_argument("--pdf", type=Path, default=DEFAULT_PDF)
    parser.add_argument("--metadata", type=Path, default=DEFAULT_METADATA)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    payload = inspect_pdf(args.pdf, args.metadata, args.output)
    print("Inspection PDF Berthelette :")
    print(f"- pages : {payload['page_count']}")
    print(
        f"- texte extractible : {payload['pages_with_extractable_text']}/{payload['page_count']} "
        f"({payload['text_coverage_ratio'] * 100:.1f} %)"
    )
    print(f"- caractères extraits : {payload['total_extracted_text_chars']}")
    print(f"- SHA metadata OK : {payload['sha256_matches_harvest_metadata']}")
    print(f"- pages droits : {payload['candidate_pages']['rights']}")
    print(f"- pages lexicales/annexes : {payload['candidate_pages']['lexical_or_appendix']}")
    print(f"- pages localités : {payload['candidate_pages']['localities']}")
    print("- localités détectées :")
    for locality in LOCALITY_TERMS:
        pages = payload["term_pages"].get(locality)
        if pages:
            print(f"  {locality}: {pages}")
    print(f"- technical_ok : {payload['technical_ok']}")
    print(f"Rapport : {args.output}")


if __name__ == "__main__":
    main()
