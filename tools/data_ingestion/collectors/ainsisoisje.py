"""Collecteur du petit dictionnaire Français / Samo publié sur ainsisoisje.com.

Cette source est traitée comme une *source candidate* : la page publique affiche
un copyright « All Rights Reserved » et ne fournit pas, à ce stade, de licence
explicite autorisant publication ou entraînement ML. Les données récupérées sont
donc conservées uniquement dans ``data/raw/`` (ignoré par Git) pour inventaire,
comparaison de sources et étude de provenance.

Le site utilise le plugin WordPress « Name Directory ». Le collecteur découvre
les URLs par lettre depuis la page d'index puis extrait les couples terme /
description rendus dans ``div.name_directory_name_box``.
"""

from __future__ import annotations

import argparse
import json
import re
import time
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from typing import Iterable
from urllib.parse import urljoin

import requests


REPO_ROOT = Path(__file__).resolve().parents[3]
RAW_OUTPUT_DIR = REPO_ROOT / "data" / "raw" / "ainsisoisje"
DICTIONARY_URL = "https://ainsisoisje.com/dictionnaire-samo-francais/"
SOURCE_HOMEPAGE = "https://ainsisoisje.com/"
SOURCE_NAME = "Ainsi sois-je — Dictionnaire Français / Samo"
SOURCE_RIGHTS = "All Rights Reserved"
RIGHTS_STATUS = "rights_review_required"
USER_AGENT = "langue-san-data-ingestion/1.0 (+source-inventory)"


class AinsisoisjeCollectorError(RuntimeError):
    """Erreur contrôlée du collecteur Ainsi sois-je."""


class _DirectoryParser(HTMLParser):
    """Parse les entrées produites par le plugin WordPress Name Directory."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.box_depth = 0
        self.in_box = False
        self.term_depth = 0
        self.in_term = False
        self.ignore_depth = 0
        self.term_parts: list[str] = []
        self.description_parts: list[str] = []
        self.entries: list[tuple[str, str]] = []

    @staticmethod
    def _classes(attrs: list[tuple[str, str | None]]) -> set[str]:
        for key, value in attrs:
            if key == "class" and value:
                return set(value.split())
        return set()

    @staticmethod
    def _attr(attrs: list[tuple[str, str | None]], name: str) -> str | None:
        for key, value in attrs:
            if key == name:
                return value
        return None

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        classes = self._classes(attrs)

        if tag == "div" and "name_directory_name_box" in classes and not self.in_box:
            self.in_box = True
            self.box_depth = 1
            self.term_parts = []
            self.description_parts = []
            return

        if not self.in_box:
            return

        if tag == "div":
            self.box_depth += 1

        if tag in {"script", "style", "input", "button"}:
            self.ignore_depth += 1
            return

        if self._attr(attrs, "role") == "term":
            self.in_term = True
            self.term_depth = 1
            return

        if self.in_term:
            self.term_depth += 1

    def handle_endtag(self, tag: str) -> None:
        if not self.in_box:
            return

        if self.ignore_depth and tag in {"script", "style", "input", "button"}:
            self.ignore_depth -= 1
            return

        if self.in_term:
            self.term_depth -= 1
            if self.term_depth <= 0:
                self.in_term = False
                self.term_depth = 0

        if tag == "div":
            self.box_depth -= 1
            if self.box_depth <= 0:
                term = _clean_text(" ".join(self.term_parts))
                description = _clean_description(" ".join(self.description_parts))
                if term and description:
                    self.entries.append((term, description))
                self.in_box = False
                self.box_depth = 0

    def handle_data(self, data: str) -> None:
        if not self.in_box or self.ignore_depth:
            return
        text = data.strip()
        if not text:
            return
        if self.in_term:
            self.term_parts.append(text)
        else:
            self.description_parts.append(text)


def _clean_text(value: str) -> str:
    return " ".join(value.replace("\xa0", " ").split()).strip()


def _clean_description(value: str) -> str:
    text = _clean_text(value)
    # Libellés ajoutés par le plugin, pas par le dictionnaire lui-même.
    text = re.sub(r"(?:\.\.\.\s*)?(?:show|read)\s+more\b", "", text, flags=re.I)
    text = re.sub(r"(?:show|read)\s+less\b", "", text, flags=re.I)
    text = re.sub(r"(?:afficher|lire)\s+plus\b", "", text, flags=re.I)
    text = re.sub(r"(?:afficher|lire)\s+moins\b", "", text, flags=re.I)
    return _clean_text(text)


def parse_directory_entries(html: str) -> list[tuple[str, str]]:
    """Retourne les couples ``(français, samo)`` depuis une page par lettre."""

    parser = _DirectoryParser()
    parser.feed(html)
    parser.close()
    return parser.entries


def discover_letter_urls(html: str, base_url: str = DICTIONARY_URL) -> list[str]:
    """Découvre les liens du plugin contenant ``name_directory_startswith``."""

    hrefs = re.findall(r'href=["\']([^"\']*name_directory_startswith=[^"\']+)["\']', html, flags=re.I)
    urls: list[str] = []
    seen: set[str] = set()
    for href in hrefs:
        absolute = urljoin(base_url, href.replace("&amp;", "&"))
        if absolute not in seen:
            seen.add(absolute)
            urls.append(absolute)
    return urls


def extract_declared_count(html: str) -> int | None:
    """Extrait le compteur public, ex. « Il y a actuellement 126 noms ... »."""

    match = re.search(r"Il\s+y\s+a\s+actuellement\s+(\d+)\s+noms?", html, flags=re.I)
    return int(match.group(1)) if match else None


def _download_html(url: str, *, timeout: int = 60) -> str:
    response = requests.get(
        url,
        timeout=timeout,
        headers={"User-Agent": USER_AGENT, "Accept-Language": "fr,en;q=0.8"},
    )
    response.raise_for_status()
    return response.text


def collect(
    output_dir: Path = RAW_OUTPUT_DIR,
    *,
    delay_seconds: float = 0.25,
) -> Path:
    """Collecte localement le répertoire public sans promouvoir ses droits."""

    index_html = _download_html(DICTIONARY_URL)
    declared_count = extract_declared_count(index_html)
    letter_urls = discover_letter_urls(index_html)

    if not letter_urls:
        raise AinsisoisjeCollectorError(
            "Aucun lien par lettre Name Directory détecté. Le HTML du site a peut-être changé."
        )

    entries: list[dict[str, str]] = []
    seen: set[tuple[str, str]] = set()

    for index, page_url in enumerate(letter_urls):
        if index and delay_seconds > 0:
            time.sleep(delay_seconds)
        html = _download_html(page_url)
        for french, samo in parse_directory_entries(html):
            key = (french.casefold(), samo.casefold())
            if key in seen:
                continue
            seen.add(key)
            entries.append(
                {
                    "source": "ainsisoisje",
                    "french": french,
                    "samo": samo,
                    "source_page": page_url,
                    "variety": "unknown",
                    "iso_639_3": "unknown",
                    "validation_status": "external_unverified",
                }
            )

    if not entries:
        raise AinsisoisjeCollectorError(
            "Aucune entrée Français / Samo extraite. Vérifier le rendu Name Directory du site."
        )

    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / "dictionnaire_samo_francais.json"
    retrieved_at = datetime.now(timezone.utc).isoformat()

    payload = {
        "metadata": {
            "source": "ainsisoisje",
            "source_name": SOURCE_NAME,
            "homepage": SOURCE_HOMEPAGE,
            "dictionary_url": DICTIONARY_URL,
            "retrieved_at": retrieved_at,
            "entry_count": len(entries),
            "site_declared_entry_count": declared_count,
            "count_matches_site": declared_count is None or declared_count == len(entries),
            "rights": SOURCE_RIGHTS,
            "rights_status": RIGHTS_STATUS,
            "publication_approved": False,
            "training_approved": False,
            "provenance_status": "source_origin_not_documented_on_public_dictionary_page",
            "variety_status": "unknown",
            "intended_use": "local_source_inventory_and_comparison_only",
            "validation_status": "external_unverified",
            "notes": [
                "La page publique affiche un copyright All Rights Reserved.",
                "Aucune variété ISO n'est attribuée automatiquement au mot Samo du site.",
                "Le RAW doit rester local et ne doit pas être publié ou utilisé pour le ML sans clarification des droits.",
            ],
        },
        "entries": entries,
    }

    output_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return output_path


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Collecter le dictionnaire Français / Samo de ainsisoisje.com pour inventaire local"
    )
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=RAW_OUTPUT_DIR,
        help="Dossier RAW local (défaut: <repo>/data/raw/ainsisoisje)",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=0.25,
        help="Pause entre pages par lettre en secondes (défaut: 0.25)",
    )
    args = parser.parse_args()

    path = collect(args.output_dir, delay_seconds=max(0.0, args.delay))
    payload = json.loads(path.read_text(encoding="utf-8"))
    metadata = payload["metadata"]
    print(f"Entrées récupérées : {metadata['entry_count']}")
    if metadata.get("site_declared_entry_count") is not None:
        print(f"Compteur annoncé par le site : {metadata['site_declared_entry_count']}")
        print(f"Compteur cohérent : {metadata['count_matches_site']}")
    print(f"Droits : {metadata['rights']} ({metadata['rights_status']})")
    print(f"Variété : {metadata['variety_status']}")
    print(f"JSON : {path}")


if __name__ == "__main__":
    main()
