"""Diagnostic des polices legacy utilisées dans la wordlist Berthelette.

Le texte courant du rapport est extractible, mais les formes SAN des pages de
wordlist apparaissent comme des glyphes du type `/G3D/G4F/...` et le mode layout
signale une police ininterprétable. Ce script inspecte uniquement les ressources
Font des pages concernées afin de déterminer :

- les noms de police (/BaseFont) réellement utilisés ;
- leur subtype ;
- la présence ou non d'une table /ToUnicode ;
- l'encodage et les éventuelles /Differences ;
- si une police est embarquée dans le PDF ;
- combien de glyphes legacy `/G..` apparaissent dans le texte extrait.

Aucune forme SAN n'est convertie ici et aucun OCR n'est lancé.
"""

from __future__ import annotations

import argparse
import json
import re
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from pypdf import PdfReader


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_PDF = REPO_ROOT / "data" / "raw" / "berthelette" / "SILESR2002_005.pdf"
DEFAULT_OUTPUT = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "font_diagnostic.json"
)
GLYPH_RE = re.compile(r"/G[0-9A-Fa-f]{2,}")


class BertheletteFontDiagnosticError(RuntimeError):
    """Erreur contrôlée pendant le diagnostic des polices."""


def _resolve(value: Any) -> Any:
    try:
        return value.get_object()
    except AttributeError:
        return value


def _name(value: Any) -> str | None:
    if value is None:
        return None
    return str(value)


def _encoding_info(font: Any) -> dict[str, Any]:
    encoding = _resolve(font.get("/Encoding")) if hasattr(font, "get") else None
    if encoding is None:
        return {"type": None, "base_encoding": None, "differences_count": 0, "differences_sample": []}
    if not hasattr(encoding, "get"):
        return {
            "type": _name(encoding),
            "base_encoding": None,
            "differences_count": 0,
            "differences_sample": [],
        }

    differences = list(encoding.get("/Differences") or [])
    names = [str(item) for item in differences if str(item).startswith("/")]
    return {
        "type": "dictionary",
        "base_encoding": _name(encoding.get("/BaseEncoding")),
        "differences_count": len(names),
        "differences_sample": names[:30],
    }


def _font_descriptor_info(font: Any) -> dict[str, Any]:
    descriptor = _resolve(font.get("/FontDescriptor")) if hasattr(font, "get") else None
    if descriptor is None or not hasattr(descriptor, "get"):
        return {
            "font_name": None,
            "embedded": False,
            "embedded_stream_keys": [],
        }
    stream_keys = [key for key in ("/FontFile", "/FontFile2", "/FontFile3") if descriptor.get(key)]
    return {
        "font_name": _name(descriptor.get("/FontName")),
        "embedded": bool(stream_keys),
        "embedded_stream_keys": stream_keys,
    }


def inspect_font(resource_name: str, font_ref: Any) -> dict[str, Any]:
    font = _resolve(font_ref)
    if not hasattr(font, "get"):
        return {"resource_name": resource_name, "unreadable": True}

    descendants = _resolve(font.get("/DescendantFonts"))
    descendant_info: list[dict[str, Any]] = []
    if isinstance(descendants, list):
        for child_ref in descendants:
            child = _resolve(child_ref)
            if hasattr(child, "get"):
                descendant_info.append(
                    {
                        "base_font": _name(child.get("/BaseFont")),
                        "subtype": _name(child.get("/Subtype")),
                        "font_descriptor": _font_descriptor_info(child),
                    }
                )

    return {
        "resource_name": resource_name,
        "base_font": _name(font.get("/BaseFont")),
        "subtype": _name(font.get("/Subtype")),
        "to_unicode": bool(font.get("/ToUnicode")),
        "encoding": _encoding_info(font),
        "font_descriptor": _font_descriptor_info(font),
        "descendant_fonts": descendant_info,
    }


def diagnose_fonts(
    pdf_path: Path = DEFAULT_PDF,
    output_path: Path = DEFAULT_OUTPUT,
    start_page: int = 41,
    end_page: int = 64,
) -> dict[str, Any]:
    pdf_path = pdf_path.expanduser().resolve()
    output_path = output_path.expanduser().resolve()
    if not pdf_path.exists():
        raise BertheletteFontDiagnosticError(f"PDF introuvable : {pdf_path}")

    reader = PdfReader(str(pdf_path))
    if start_page < 1 or end_page > len(reader.pages) or start_page > end_page:
        raise BertheletteFontDiagnosticError(
            f"Intervalle invalide {start_page}-{end_page} pour un PDF de {len(reader.pages)} pages."
        )

    font_by_key: dict[str, dict[str, Any]] = {}
    page_results: list[dict[str, Any]] = []
    glyph_counter: Counter[str] = Counter()

    for page_no in range(start_page, end_page + 1):
        page = reader.pages[page_no - 1]
        normal_text = page.extract_text() or ""
        glyphs = GLYPH_RE.findall(normal_text)
        glyph_counter.update(glyphs)

        resources = _resolve(page.get("/Resources")) or {}
        fonts = _resolve(resources.get("/Font")) if hasattr(resources, "get") else None
        resource_names: list[str] = []
        if hasattr(fonts, "items"):
            for resource_name, font_ref in fonts.items():
                resource = str(resource_name)
                resource_names.append(resource)
                info = inspect_font(resource, font_ref)
                signature = json.dumps(info, ensure_ascii=False, sort_keys=True)
                font_by_key.setdefault(signature, info)

        page_results.append(
            {
                "page": page_no,
                "legacy_glyph_tokens": len(glyphs),
                "unique_legacy_glyph_tokens": len(set(glyphs)),
                "font_resources": resource_names,
            }
        )

    unique_fonts = sorted(
        font_by_key.values(),
        key=lambda item: (str(item.get("base_font")), str(item.get("resource_name"))),
    )
    missing_tounicode = [
        item for item in unique_fonts if item.get("to_unicode") is False
    ]

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "Berthelette 2001 / SILESR-2002-005",
        "validation_scope": "legacy_font_diagnostic_only",
        "pdf_path": str(pdf_path),
        "pages": {"start": start_page, "end": end_page},
        "unique_fonts": unique_fonts,
        "unique_font_count": len(unique_fonts),
        "fonts_without_tounicode": len(missing_tounicode),
        "legacy_glyph_token_total": sum(glyph_counter.values()),
        "legacy_glyph_unique": len(glyph_counter),
        "top_legacy_glyphs": glyph_counter.most_common(40),
        "page_results": page_results,
        "likely_unicode_mapping_problem": bool(glyph_counter) and bool(missing_tounicode),
        "ocr_used": False,
        "notes": [
            "Les tokens /G.. sont des sorties d'extraction, pas des formes SAN utilisables.",
            "Une police sans /ToUnicode peut nécessiter une table de mapping legacy ou un autre moteur PDF.",
            "Ce diagnostic ne convertit aucun glyphe et ne lance aucun OCR.",
        ],
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="Diagnostique les polices legacy de la wordlist Berthelette")
    parser.add_argument("--pdf", type=Path, default=DEFAULT_PDF)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--start-page", type=int, default=41)
    parser.add_argument("--end-page", type=int, default=64)
    args = parser.parse_args()

    payload = diagnose_fonts(args.pdf, args.output, args.start_page, args.end_page)
    print("Diagnostic polices Berthelette :")
    print(f"- pages : {payload['pages']['start']}-{payload['pages']['end']}")
    print(f"- polices uniques : {payload['unique_font_count']}")
    print(f"- polices sans ToUnicode : {payload['fonts_without_tounicode']}")
    print(f"- tokens legacy /G.. : {payload['legacy_glyph_token_total']}")
    print(f"- glyphes legacy uniques : {payload['legacy_glyph_unique']}")
    print(f"- problème Unicode probable : {payload['likely_unicode_mapping_problem']}")
    print("- polices :")
    for font in payload["unique_fonts"]:
        print(
            f"  {font.get('resource_name')}: base={font.get('base_font')} "
            f"subtype={font.get('subtype')} ToUnicode={font.get('to_unicode')} "
            f"embedded={font.get('font_descriptor', {}).get('embedded')}"
        )
        enc = font.get("encoding") or {}
        if enc.get("base_encoding") or enc.get("differences_count"):
            print(
                f"    encoding={enc.get('base_encoding')} differences={enc.get('differences_count')} "
                f"sample={enc.get('differences_sample', [])[:10]}"
            )
    print(f"Rapport : {args.output}")
    print("Aucun OCR et aucune conversion de forme SAN n'ont été effectués.")


if __name__ == "__main__":
    main()
