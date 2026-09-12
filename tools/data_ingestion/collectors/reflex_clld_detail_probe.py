"""Diagnostic ciblé de la page détail RefLex CLLD.

Ce module ne récolte aucun corpus. Il sert uniquement à découvrir le transport
réel utilisé par la page d'une langue RefLex après résolution sûre de son ID CLLD.

Pourquoi ?
- `languages.csv` confirme la présence et le volume de Samo Matya/Maya ;
- `/values.csv` répond 406 ;
- un appel XHR manuel à `/values?language=<id>` peut retourner 0 même quand la
  page langue annonce des milliers de fiches ;
- la page HTML de la langue est donc la meilleure source pour retrouver le
  `sAjaxSource` et les paramètres réellement générés par l'application CLLD.

Aucune donnée linguistique complète n'est écrite par ce script.
"""

from __future__ import annotations

import argparse
import html
import json
import re
from pathlib import Path
from typing import Any
from urllib.parse import parse_qsl, urlencode, urljoin, urlsplit, urlunsplit

import requests

try:
    from collectors import reflex_clld as reflex
    from collectors import reflex_clld_xhr as xhr
except ImportError:
    import reflex_clld as reflex
    import reflex_clld_xhr as xhr


CONFIG_PATH = xhr.CONFIG_PATH
USER_AGENT = "langue-san-data-ingestion/1.0 (+reflex-clld-detail-probe)"
S_AJAX_RE = re.compile(r"[\"']sAjaxSource[\"']\s*:\s*[\"']([^\"']+)[\"']")
HREF_RE = re.compile(r"href=[\"']([^\"']+)[\"']", re.IGNORECASE)


class RefLexDetailProbeError(RuntimeError):
    """Erreur contrôlée du diagnostic de page détail."""


def _session(session: requests.Session | None = None) -> requests.Session:
    client = session or requests.Session()
    client.headers.update({"User-Agent": USER_AGENT})
    return client


def _decode_js_url(value: str) -> str:
    return (
        html.unescape(str(value or ""))
        .replace("\\/", "/")
        .replace("\\u0026", "&")
        .replace("\\x26", "&")
    )


def extract_ajax_sources(page_html: str, *, detail_url: str) -> list[str]:
    """Extrait les `sAjaxSource` réellement embarqués dans la page langue."""

    found: list[str] = []
    for raw in S_AJAX_RE.findall(page_html or ""):
        absolute = urljoin(detail_url, _decode_js_url(raw))
        if absolute not in found:
            found.append(absolute)
    return found


def extract_download_links(page_html: str, *, detail_url: str) -> list[str]:
    """Conserve uniquement les liens qui ressemblent à un export/téléchargement."""

    found: list[str] = []
    for raw in HREF_RE.findall(page_html or ""):
        decoded = _decode_js_url(raw)
        lowered = decoded.casefold()
        if not any(token in lowered for token in (".csv", ".tsv", ".zip", "download", "export")):
            continue
        absolute = urljoin(detail_url, decoded)
        if absolute not in found:
            found.append(absolute)
    return found


def _interesting_snippets(page_html: str, *, limit: int = 8, radius: int = 240) -> list[str]:
    text = page_html or ""
    needles = ("sAjaxSource", "DataTable.init", "/values", "download", ".csv")
    snippets: list[str] = []
    occupied: list[tuple[int, int]] = []
    for needle in needles:
        for match in re.finditer(re.escape(needle), text, flags=re.IGNORECASE):
            start = max(0, match.start() - radius)
            end = min(len(text), match.end() + radius)
            if any(not (end < old_start or start > old_end) for old_start, old_end in occupied):
                continue
            compact = " ".join(html.unescape(text[start:end]).split())
            snippets.append(compact)
            occupied.append((start, end))
            if len(snippets) >= limit:
                return snippets
    return snippets


def _detail_url(source: dict[str, Any], target: dict[str, Any]) -> str:
    language_id = str(target.get("clld_language_id") or "").strip()
    if not language_id:
        raise RefLexDetailProbeError("ID CLLD absent avant diagnostic de la page langue.")
    base = xhr._languages_url(source).rstrip("/") + "/"
    return urljoin(base, language_id)


def _with_datatable_probe_params(url: str) -> tuple[str, dict[str, str]]:
    parts = urlsplit(url)
    params = {str(k): str(v) for k, v in parse_qsl(parts.query, keep_blank_values=True)}
    params.update({
        "sEcho": "1",
        "iDisplayStart": "0",
        "iDisplayLength": "1",
    })
    clean_url = urlunsplit((parts.scheme, parts.netloc, parts.path, "", parts.fragment))
    return clean_url, params


def probe_ajax_source(url: str, *, session: requests.Session) -> dict[str, Any]:
    request_url, params = _with_datatable_probe_params(url)
    response = session.get(
        request_url,
        params=params,
        timeout=90,
        headers=xhr._xhr_headers(),
    )
    status = getattr(response, "status_code", None)
    response.raise_for_status()
    try:
        payload = response.json()
    except ValueError:
        return {
            "ajax_source": url,
            "status_code": status,
            "json": False,
            "content_type": str(getattr(response, "headers", {}).get("Content-Type", "")),
            "preview": str(getattr(response, "text", ""))[:300],
        }
    if not isinstance(payload, dict):
        return {
            "ajax_source": url,
            "status_code": status,
            "json": True,
            "payload_type": type(payload).__name__,
        }
    rows = xhr._rows(payload)
    return {
        "ajax_source": url,
        "status_code": status,
        "json": True,
        "reported_count": xhr._reported_total(payload),
        "rows_returned": len(rows),
        "first_row": rows[0] if rows else None,
        "response_keys": sorted(str(key) for key in payload),
        "request_params": params,
    }


def diagnose_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    detail_url = _detail_url(source, target)
    response = session.get(
        detail_url,
        timeout=90,
        headers={"Accept": "text/html,application/xhtml+xml"},
    )
    response.raise_for_status()
    page_html = str(getattr(response, "text", ""))
    ajax_sources = extract_ajax_sources(page_html, detail_url=detail_url)
    return {
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "reflex_language_name": target.get("reflex_language_name"),
        "reflex_glottocode": target.get("reflex_glottocode"),
        "clld_language_id": target.get("clld_language_id"),
        "detail_url": detail_url,
        "status_code": getattr(response, "status_code", None),
        "html_chars": len(page_html),
        "ajax_sources": ajax_sources,
        "ajax_probes": [probe_ajax_source(url, session=session) for url in ajax_sources],
        "download_links": extract_download_links(page_html, detail_url=detail_url),
        "snippets": _interesting_snippets(page_html),
    }


def diagnose(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session | None = None,
) -> dict[str, Any]:
    client = _session(session)
    targets = xhr.resolve_selected_targets(source, selected_iso=selected_iso, session=client)
    return {
        "source": source.get("name"),
        "results": [diagnose_target(source, target, session=client) for target in targets],
    }


def _preview(value: Any, limit: int = 700) -> str:
    text = json.dumps(value, ensure_ascii=False)
    return text if len(text) <= limit else text[:limit] + "…"


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Inspecte la page détail RefLex et son vrai sAjaxSource sans récolter le corpus"
    )
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--iso", nargs="+", required=True, help="Ex. --iso stj")
    args = parser.parse_args()

    source = reflex.load_source(args.config)
    configured = {str(item["iso_639_3"]) for item in source.get("targets", [])}
    selected_iso = {str(item).lower() for item in args.iso}
    unknown = sorted(selected_iso - configured)
    if unknown:
        raise RefLexDetailProbeError("Codes ISO non configurés : " + ", ".join(unknown))

    payload = diagnose(source, selected_iso=selected_iso)
    print("Diagnostic page détail RefLex :")
    for item in payload["results"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}): "
            f"{item['reflex_language_name']} / {item['reflex_glottocode']}, "
            f"clld_id={item['clld_language_id']}"
        )
        print(
            f"  page={item['detail_url']} status={item['status_code']} "
            f"html_chars={item['html_chars']}"
        )
        print(f"  sAjaxSource trouvés={len(item['ajax_sources'])}")
        for probe in item["ajax_probes"]:
            print(
                f"    - {probe['ajax_source']} → status={probe.get('status_code')} "
                f"json={probe.get('json')} total={probe.get('reported_count')} "
                f"rows={probe.get('rows_returned')}"
            )
            if probe.get("request_params"):
                print(f"      params={_preview(probe['request_params'])}")
            if probe.get("first_row") is not None:
                print(f"      première_ligne={_preview(probe['first_row'])}")
            if probe.get("preview"):
                print(f"      aperçu_réponse={_preview(probe['preview'])}")
        print(f"  liens export/téléchargement={len(item['download_links'])}")
        for link in item["download_links"][:10]:
            print(f"    - {link}")
        if item["snippets"]:
            print("  extraits HTML/JS utiles :")
            for snippet in item["snippets"]:
                print(f"    - {_preview(snippet)}")
    print("Diagnostic uniquement : aucun corpus complet n'a été écrit.")


if __name__ == "__main__":
    main()
