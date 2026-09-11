"""Collecteur ASJP v21 (CLDF).

Le collecteur télécharge les tables CLDF publiques de la version v21 d'ASJP,
puis extrait uniquement les listes lexicales associées aux codes ISO 639-3
utilisés par le projet Langue SAN : ``sbd``, ``stj`` et ``sym``.

Les sorties sont écrites sous ``data/raw/asjp/``. Ce dossier est ignoré par
Git et les données récupérées restent marquées comme ressources externes non
validées linguistiquement par le projet.
"""

from __future__ import annotations

import argparse
import csv
import io
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Iterable

import requests


RAW_OUTPUT_DIR = Path("data/raw/asjp")
SUPPORTED_ISO_CODES = ("sbd", "stj", "sym")
ASJP_VERSION = "v21"
BASE_RAW_URL = f"https://raw.githubusercontent.com/lexibank/asjp/{ASJP_VERSION}/cldf"
TABLE_URLS = {
    "languages": f"{BASE_RAW_URL}/languages.csv",
    "parameters": f"{BASE_RAW_URL}/parameters.csv",
    "forms": f"{BASE_RAW_URL}/forms.csv",
}
SOURCE_HOMEPAGE = "https://asjp.clld.org/"
SOURCE_RELEASE = "https://zenodo.org/records/16736409"
SOURCE_LICENSE = "CC-BY-4.0"


class ASJPCollectorError(RuntimeError):
    """Erreur contrôlée du collecteur ASJP."""


def _download_csv(url: str, *, timeout: int = 60) -> list[dict[str, str]]:
    response = requests.get(
        url,
        timeout=timeout,
        headers={"User-Agent": "langue-san-data-ingestion/1.0"},
    )
    response.raise_for_status()
    return list(csv.DictReader(io.StringIO(response.text)))


def _first_existing_field(row: dict[str, str], candidates: Iterable[str]) -> str | None:
    for key in candidates:
        value = row.get(key)
        if value:
            return value.strip()
    return None


def _iso_code(row: dict[str, str]) -> str | None:
    return _first_existing_field(
        row,
        (
            "ISO639P3code",
            "ISO639P3Code",
            "ISO639P3",
            "ISO_639_3",
            "iso639P3code",
            "iso",
        ),
    )


def extract_wordlists(
    languages: list[dict[str, str]],
    parameters: list[dict[str, str]],
    forms: list[dict[str, str]],
    iso_codes: Iterable[str] = SUPPORTED_ISO_CODES,
) -> list[dict[str, str | None]]:
    """Extrait les formes ASJP correspondant aux codes ISO demandés."""

    wanted = {code.lower() for code in iso_codes}
    selected_languages: dict[str, dict[str, str]] = {}

    for language in languages:
        iso = (_iso_code(language) or "").lower()
        language_id = language.get("ID", "").strip()
        if iso in wanted and language_id:
            selected_languages[language_id] = language

    parameter_names = {
        row.get("ID", "").strip(): _first_existing_field(row, ("Name", "Concepticon_Gloss", "Description"))
        for row in parameters
        if row.get("ID")
    }

    extracted: list[dict[str, str | None]] = []
    for form in forms:
        language_id = form.get("Language_ID", "").strip()
        language = selected_languages.get(language_id)
        if language is None:
            continue

        parameter_id = form.get("Parameter_ID", "").strip()
        extracted.append(
            {
                "source": "ASJP",
                "source_version": ASJP_VERSION,
                "license": SOURCE_LICENSE,
                "iso_639_3": (_iso_code(language) or "").lower(),
                "language_id": language_id,
                "language_name": _first_existing_field(language, ("Name", "name")),
                "glottocode": _first_existing_field(language, ("Glottocode", "Glottolog_ID")),
                "parameter_id": parameter_id or None,
                "concept": parameter_names.get(parameter_id),
                "value": _first_existing_field(form, ("Value", "Form")),
                "form": _first_existing_field(form, ("Form", "Value")),
                "loan": _first_existing_field(form, ("Loan", "Borrowed")),
            }
        )

    return extracted


def collect(
    output_dir: Path = RAW_OUTPUT_DIR,
    iso_codes: Iterable[str] = SUPPORTED_ISO_CODES,
) -> Path:
    output_dir.mkdir(parents=True, exist_ok=True)

    languages = _download_csv(TABLE_URLS["languages"])
    parameters = _download_csv(TABLE_URLS["parameters"])
    forms = _download_csv(TABLE_URLS["forms"])

    rows = extract_wordlists(languages, parameters, forms, iso_codes)
    if not rows:
        raise ASJPCollectorError(
            "Aucune entrée ASJP trouvée pour les codes ISO demandés. "
            "Vérifier la structure CLDF ou la version configurée."
        )

    retrieved_at = datetime.now(timezone.utc).isoformat()
    payload = {
        "metadata": {
            "source": "ASJP",
            "version": ASJP_VERSION,
            "homepage": SOURCE_HOMEPAGE,
            "release": SOURCE_RELEASE,
            "license": SOURCE_LICENSE,
            "retrieved_at": retrieved_at,
            "iso_codes": sorted({str(row["iso_639_3"]) for row in rows}),
            "entry_count": len(rows),
            "validation_status": "external_unverified",
        },
        "entries": rows,
    }

    output_path = output_dir / f"asjp_{ASJP_VERSION}_san_wordlists.json"
    output_path.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    return output_path


def main() -> None:
    parser = argparse.ArgumentParser(description="Collecter les listes lexicales ASJP du projet Langue SAN")
    parser.add_argument(
        "--iso",
        nargs="+",
        default=list(SUPPORTED_ISO_CODES),
        help="Codes ISO 639-3 à extraire (défaut: sbd stj sym)",
    )
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=RAW_OUTPUT_DIR,
        help="Dossier de sortie brute",
    )
    args = parser.parse_args()

    unsupported = sorted(set(args.iso) - set(SUPPORTED_ISO_CODES))
    if unsupported:
        parser.error(f"Codes ISO non configurés pour ce projet: {', '.join(unsupported)}")

    path = collect(args.output_dir, args.iso)
    print(path)


if __name__ == "__main__":
    main()
