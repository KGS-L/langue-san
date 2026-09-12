"""Probe de décodage Unicode des glyphes legacy de la wordlist Berthelette.

Le PDF encode les transcriptions SAN dans des polices Type3 sans /ToUnicode.
`pypdf` expose alors les glyphes sous la forme `/G3D/G4F/...`.

Le diagnostic a montré que les codes internes /Differences (1, 2, 3, ...)
ne sont pas les codes IPA d'origine. En revanche, plusieurs glyphes sentinelles
sont cohérents avec l'encodage historique SIL IPA93 lorsque l'on applique :

    code_IPA93 = int(hex_du_nom_Gxx, 16) + 0x1E

Exemples :
- /G3D -> 0x3D + 0x1E = 91  -> '['
- /G3F -> 0x3F + 0x1E = 93  -> ']'
- /G4F -> 0x4F + 0x1E = 109 -> 'm'
- /G51 -> 0x51 + 0x1E = 111 -> 'o'
- /G49 -> 0x49 + 0x1E = 103 -> 'ɡ' (U+0261, IPA script g)
- /G30 -> 0x30 + 0x1E = 78  -> 'ŋ' dans SIL IPA93

Les noms Type3 observés dans ce PDF ont exactement deux chiffres hexadécimaux
après `/G`. Il faut donc impérativement borner le motif à deux chiffres : dans
une chaîne telle que `/G3FBangassogo`, le `B` et le `a` appartiennent au nom de
localité, pas au nom du glyphe.

Ce script valide cette hypothèse sur l'ensemble du bloc lexical avant tout
parsing final. Il ne crée aucun dataset lexical final et ne lance aucun OCR.
"""

from __future__ import annotations

import argparse
import json
import re
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from ipa2unicode import sil_to_unicode_dict
from pypdf import PdfReader


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_PDF = REPO_ROOT / "data" / "raw" / "berthelette" / "SILESR2002_005.pdf"
DEFAULT_OUTPUT = (
    REPO_ROOT / "data" / "processed" / "berthelette" / "ipa93_decode_probe.json"
)

# Important : les noms de glyphes Type3 de CE PDF sont /G + exactement 2
# chiffres hexadécimaux. Un motif {2,} avalerait par exemple le début de
# "Bangassogo" dans `/G3FBangassogo` puisque B et a sont aussi hexadécimaux.
GLYPH_RE = re.compile(r"/G[0-9A-Fa-f]{2}")
SEQUENCE_RE = re.compile(r"(?:/G[0-9A-Fa-f]{2})+")
IPA93_GLYPH_OFFSET = 0x1E

SENTINELS = {
    "/G3D": "[",
    "/G3F": "]",
    "/G4F": "m",
    "/G51": "o",
    "/G49": "ɡ",  # U+0261 LATIN SMALL LETTER SCRIPT G, caractère IPA
    "/G57": "u",
    "/G4E": "l",
    "/G30": "ŋ",
    "/G23": "ɑ",
    "/G06": "\u0300",  # combining grave accent
}


class BertheletteIPA93DecodeError(RuntimeError):
    """Erreur contrôlée pendant le probe de décodage IPA93."""


def glyph_token_to_access_code(token: str) -> int:
    if not GLYPH_RE.fullmatch(token or ""):
        raise BertheletteIPA93DecodeError(f"Token glyphe invalide : {token!r}")
    code = int(token[2:], 16) + IPA93_GLYPH_OFFSET
    if code < 0 or code > 255:
        raise BertheletteIPA93DecodeError(
            f"Code IPA93 hors plage pour {token}: {code}"
        )
    return code


def glyph_token_to_unicode(token: str) -> str | None:
    code = glyph_token_to_access_code(token)
    return sil_to_unicode_dict.get(code)


def decode_legacy_sequence(sequence: str) -> dict[str, Any]:
    tokens = GLYPH_RE.findall(sequence or "")
    if not tokens:
        raise BertheletteIPA93DecodeError(
            f"Aucun token /Gxx dans la séquence : {sequence!r}"
        )

    access_codes: list[int] = []
    decoded_parts: list[str] = []
    unresolved: list[dict[str, Any]] = []

    for token in tokens:
        code = glyph_token_to_access_code(token)
        access_codes.append(code)
        value = sil_to_unicode_dict.get(code)
        if value is None:
            unresolved.append({"token": token, "access_code": code})
            decoded_parts.append("�")
        else:
            decoded_parts.append(value)

    return {
        "raw_sequence": sequence,
        "tokens": tokens,
        "access_codes": access_codes,
        "decoded_unicode": "".join(decoded_parts),
        "unresolved": unresolved,
    }


def _sentinel_check() -> dict[str, Any]:
    rows: list[dict[str, Any]] = []
    all_ok = True
    for token, expected in SENTINELS.items():
        code = glyph_token_to_access_code(token)
        actual = sil_to_unicode_dict.get(code)
        ok = actual == expected
        all_ok = all_ok and ok
        rows.append(
            {
                "token": token,
                "access_code": code,
                "expected_unicode": expected,
                "actual_unicode": actual,
                "ok": ok,
            }
        )
    return {"all_ok": all_ok, "items": rows}


def probe_pdf(
    pdf_path: Path = DEFAULT_PDF,
    output_path: Path = DEFAULT_OUTPUT,
    start_page: int = 41,
    end_page: int = 63,
    sample_limit: int = 20,
) -> dict[str, Any]:
    pdf_path = pdf_path.expanduser().resolve()
    output_path = output_path.expanduser().resolve()

    if not pdf_path.exists():
        raise BertheletteIPA93DecodeError(f"PDF introuvable : {pdf_path}")

    reader = PdfReader(str(pdf_path))
    if start_page < 1 or end_page > len(reader.pages) or start_page > end_page:
        raise BertheletteIPA93DecodeError(
            f"Intervalle invalide {start_page}-{end_page} pour un PDF de {len(reader.pages)} pages."
        )

    glyph_counter: Counter[str] = Counter()
    unresolved_counter: Counter[str] = Counter()
    samples: list[dict[str, Any]] = []
    page_rows: list[dict[str, Any]] = []
    sequence_count = 0

    for page_no in range(start_page, end_page + 1):
        text = reader.pages[page_no - 1].extract_text() or ""
        sequences = SEQUENCE_RE.findall(text)
        page_tokens = GLYPH_RE.findall(text)
        glyph_counter.update(page_tokens)

        page_unresolved = 0
        for sequence in sequences:
            decoded = decode_legacy_sequence(sequence)
            sequence_count += 1
            for item in decoded["unresolved"]:
                unresolved_counter[item["token"]] += 1
                page_unresolved += 1
            if len(samples) < sample_limit:
                samples.append({"page": page_no, **decoded})

        page_rows.append(
            {
                "page": page_no,
                "sequence_count": len(sequences),
                "legacy_token_count": len(page_tokens),
                "unresolved_token_occurrences": page_unresolved,
            }
        )

    total_tokens = sum(glyph_counter.values())
    unresolved_total = sum(unresolved_counter.values())
    resolved_total = total_tokens - unresolved_total
    coverage = resolved_total / total_tokens if total_tokens else 0.0
    sentinel = _sentinel_check()

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": "Berthelette 2001 / SILESR-2002-005",
        "validation_scope": "legacy_glyph_to_sil_ipa93_unicode_probe_only",
        "pdf_path": str(pdf_path),
        "pages": {"start": start_page, "end": end_page},
        "decoder": {
            "glyph_name_pattern": "/G<2 hex digits>",
            "access_code_formula": "int(hex, 16) + 0x1E",
            "offset_decimal": IPA93_GLYPH_OFFSET,
            "mapping_source": "ipa2unicode.sil_to_unicode_dict (SIL IPA93)",
        },
        "sentinel_check": sentinel,
        "sequence_count": sequence_count,
        "legacy_token_total": total_tokens,
        "legacy_glyph_unique": len(glyph_counter),
        "resolved_token_total": resolved_total,
        "unresolved_token_total": unresolved_total,
        "unresolved_unique_tokens": sorted(unresolved_counter),
        "decode_coverage_ratio": round(coverage, 6),
        "top_glyphs": glyph_counter.most_common(40),
        "samples": samples,
        "page_results": page_rows,
        "technical_ok": bool(total_tokens) and sentinel["all_ok"] and coverage >= 0.99,
        "ocr_used": False,
        "notes": [
            "Le mapping /Differences du PDF contient des codes internes de sous-ensemble et n'est pas utilisé comme code IPA93.",
            "Les noms Type3 observés sont /G suivis d'exactement deux chiffres hexadécimaux; le parseur ne doit pas absorber le début d'un libellé adjacent comme Bangassogo.",
            "SIL IPA93 mappe le code 103 sur ɡ (U+0261, script g IPA), pas sur le g ASCII U+0067.",
            "Le décodage est une étape technique; il ne valide pas linguistiquement les formes SAN.",
            "Aucune entrée lexicale finale n'est écrite par ce probe.",
            "Les séquences brutes /Gxx et la provenance PDF doivent être conservées lors du futur parsing RAW.",
        ],
    }

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Teste le décodage SIL IPA93 des glyphes legacy Berthelette"
    )
    parser.add_argument("--pdf", type=Path, default=DEFAULT_PDF)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--start-page", type=int, default=41)
    parser.add_argument("--end-page", type=int, default=63)
    parser.add_argument("--sample-limit", type=int, default=20)
    args = parser.parse_args()

    payload = probe_pdf(
        pdf_path=args.pdf,
        output_path=args.output,
        start_page=args.start_page,
        end_page=args.end_page,
        sample_limit=args.sample_limit,
    )

    print("Probe décodage IPA93 Berthelette :")
    print(f"- pages : {payload['pages']['start']}-{payload['pages']['end']}")
    print(f"- sentinelles OK : {payload['sentinel_check']['all_ok']}")
    print(f"- séquences legacy : {payload['sequence_count']}")
    print(f"- tokens legacy : {payload['legacy_token_total']}")
    print(f"- glyphes uniques : {payload['legacy_glyph_unique']}")
    print(f"- tokens non résolus : {payload['unresolved_token_total']}")
    print(f"- couverture décodage : {payload['decode_coverage_ratio'] * 100:.2f} %")
    print(f"- technical_ok : {payload['technical_ok']}")
    print("- exemples :")
    for sample in payload["samples"][:10]:
        print(f"  p.{sample['page']} {sample['raw_sequence']} -> {sample['decoded_unicode']}")
    print(f"Rapport : {args.output}")
    print("Aucun OCR et aucune donnée lexicale finale n'ont été produits.")


if __name__ == "__main__":
    main()
