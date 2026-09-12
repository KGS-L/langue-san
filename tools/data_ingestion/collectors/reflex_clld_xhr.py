"""Fallback XHR ciblé pour RefLex CLLD.

RefLex expose `languages.csv`, mais l'export `/values.csv` répond actuellement
HTTP 406 pour les valeurs lexicales. Les DataTables CLLD restent accessibles via
XHR. Attention : le paramètre `language=` des DataTables CLLD attend l'ID interne
de la ressource Language, pas nécessairement son glottocode.

Ce collecteur :
- résout d'abord la langue dans `languages.csv` par glottocode/nom ;
- interroge ensuite le DataTable `/languages` pour récupérer l'ID interne CLLD
  depuis le lien `/languages/<id>` de la ligne correspondante ;
- utilise cet ID interne pour sonder puis paginer `/values` ;
- vérifie le total attendu avant toute récolte complète ;
- conserve les lignes DataTable brutes sans normalisation ni déduplication.

Licence RefLex : CC-BY-NC-SA-4.0. Aucun usage commercial n'est déduit de cette
récolte.
"""

from __future__ import annotations

import argparse
import html
import json
import re
import unicodedata
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests

try:  # exécution depuis tools/data_ingestion/
    from collectors import reflex_clld as reflex
except ImportError:  # exécution directe depuis collectors/
    import reflex_clld as reflex


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "sources.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "reflex"
USER_AGENT = "langue-san-data-ingestion/1.0 (+reflex-clld-xhr-fallback)"
DEFAULT_PAGE_SIZE = 500
MAX_PAGE_SIZE = 1000
LANGUAGE_INDEX_PAGE_SIZE = 1000
LANGUAGE_HREF_RE = re.compile(r"/languages/([^\"'/?#<>]+)")
TAG_RE = re.compile(r"<[^>]+>")


class RefLexXHRError(RuntimeError):
    """Erreur contrôlée du fallback DataTable XHR."""


def _session(session: requests.Session | None = None) -> requests.Session:
    client = session or requests.Session()
    client.headers.update({"User-Agent": USER_AGENT})
    return client


def _parse_int(value: Any) -> int | None:
    if isinstance(value, int):
        return value
    text = str(value or "").strip().replace(" ", "").replace(",", "")
    return int(text) if text.isdigit() else None


def _reported_total(payload: dict[str, Any]) -> int | None:
    for key in ("iTotalDisplayRecords", "recordsFiltered", "iTotalRecords", "recordsTotal"):
        parsed = _parse_int(payload.get(key))
        if parsed is not None:
            return parsed
    return None


def _rows(payload: dict[str, Any]) -> list[Any]:
    rows = payload.get("aaData", payload.get("data", []))
    if not isinstance(rows, list):
        raise RefLexXHRError("La réponse XHR RefLex ne contient pas une liste de lignes.")
    return rows


def _expected_count(target: dict[str, Any]) -> int | None:
    if target.get("number_of_sources") == 1:
        value = target.get("records_biggest_source")
        return value if isinstance(value, int) else _parse_int(value)
    return None


def _xhr_headers() -> dict[str, str]:
    return {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json, text/javascript, */*; q=0.01",
    }


def _request_json(
    session: requests.Session,
    url: str,
    *,
    params: dict[str, str],
    timeout: int = 90,
) -> dict[str, Any]:
    response = session.get(url, params=params, timeout=timeout, headers=_xhr_headers())
    response.raise_for_status()
    try:
        payload = response.json()
    except ValueError as exc:
        preview = (response.text or "")[:300].replace("\n", " ")
        raise RefLexXHRError(
            "RefLex XHR n'a pas renvoyé du JSON. "
            f"Début de réponse : {preview!r}"
        ) from exc
    if not isinstance(payload, dict):
        raise RefLexXHRError("Forme JSON XHR RefLex invalide.")
    return payload


def _languages_url(source: dict[str, Any]) -> str:
    endpoints = source.get("endpoints", {})
    if endpoints.get("languages"):
        return str(endpoints["languages"])
    csv_url = str(endpoints.get("languages_csv") or "")
    if csv_url.endswith(".csv"):
        return csv_url[:-4]
    raise RefLexXHRError("Endpoint RefLex /languages introuvable dans la configuration.")


def _request_language_index(
    source: dict[str, Any],
    *,
    session: requests.Session,
) -> tuple[list[Any], int | None]:
    payload = _request_json(
        session,
        _languages_url(source),
        params={
            "sEcho": "1",
            "iDisplayStart": "0",
            "iDisplayLength": str(LANGUAGE_INDEX_PAGE_SIZE),
            "__eid__": "Languages",
        },
    )
    rows = _rows(payload)
    return rows, _reported_total(payload)


def _iter_strings(value: Any):
    if isinstance(value, str):
        yield value
    elif isinstance(value, dict):
        for key, item in value.items():
            yield str(key)
            yield from _iter_strings(item)
    elif isinstance(value, (list, tuple)):
        for item in value:
            yield from _iter_strings(item)
    elif value is not None:
        yield str(value)


def _row_blob(row: Any) -> str:
    return " ".join(_iter_strings(row))


def _normalise_text(value: str) -> str:
    value = TAG_RE.sub(" ", html.unescape(str(value or "")))
    ascii_text = (
        unicodedata.normalize("NFKD", value)
        .encode("ascii", "ignore")
        .decode("ascii")
        .casefold()
    )
    return " ".join(re.findall(r"[a-z0-9]+", ascii_text))


def _extract_language_ids(row: Any) -> list[str]:
    found: list[str] = []
    for text in _iter_strings(row):
        for match in LANGUAGE_HREF_RE.findall(html.unescape(text)):
            if match not in found:
                found.append(match)
    return found


def resolve_clld_language_id(target: dict[str, Any], language_rows: list[Any]) -> dict[str, Any]:
    """Résout l'ID interne CLLD depuis la ligne XHR de `/languages`."""

    glottocode = str(target.get("reflex_glottocode") or target.get("configured_glottocode") or "")
    name = str(target.get("reflex_language_name") or "")
    glotto_matches = [row for row in language_rows if glottocode and glottocode.casefold() in _row_blob(row).casefold()]

    matches = glotto_matches
    method = "languages_xhr_glottocode"
    if not matches and name:
        name_norm = _normalise_text(name)
        matches = [row for row in language_rows if name_norm and name_norm in _normalise_text(_row_blob(row))]
        method = "languages_xhr_name"

    if not matches:
        raise RefLexXHRError(
            f"{target.get('iso_639_3')}: aucune ligne du DataTable /languages ne correspond à "
            f"{name or '?'} / {glottocode or '?'}"
        )

    ids: list[str] = []
    for row in matches:
        for language_id in _extract_language_ids(row):
            if language_id not in ids:
                ids.append(language_id)

    if len(ids) != 1:
        preview = json.dumps(matches[:3], ensure_ascii=False)[:800]
        raise RefLexXHRError(
            f"{target.get('iso_639_3')}: ID interne CLLD ambigu ou absent dans /languages "
            f"(ids={ids}). Aperçu={preview}"
        )

    return {
        **target,
        "clld_language_id": ids[0],
        "clld_language_id_resolution": method,
    }


def _request_page(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
    start: int,
    length: int,
    echo: int,
) -> dict[str, Any]:
    language_id = target.get("clld_language_id")
    if not language_id:
        raise RefLexXHRError(
            f"{target.get('iso_639_3')}: ID interne CLLD non résolu avant appel /values."
        )
    return _request_json(
        session,
        str(source["endpoints"]["values"]),
        params={
            "language": str(language_id),
            "sEcho": str(echo),
            "iDisplayStart": str(start),
            "iDisplayLength": str(length),
            "__eid__": "Values",
        },
    )


def resolve_selected_targets(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session,
) -> list[dict[str, Any]]:
    headers, language_rows_csv = reflex.fetch_languages(source, session=session)
    targets = reflex.resolve_targets(source, headers, language_rows_csv)
    targets = [item for item in targets if item.get("iso_639_3") in selected_iso]
    if not targets:
        raise RefLexXHRError("Aucune cible RefLex résolue pour les codes ISO demandés.")

    problems = []
    for target in targets:
        if target.get("resolution_status") != "resolved":
            problems.append(f"{target.get('iso_639_3')}: mapping={target.get('resolution_status')}")
        elif target.get("mapping_review_required"):
            problems.append(f"{target.get('iso_639_3')}: mapping_review_required=true")
    if problems:
        raise RefLexXHRError(
            "Récolte XHR bloquée tant que le mapping n'est pas confirmé : " + "; ".join(problems)
        )

    language_rows_xhr, total = _request_language_index(source, session=session)
    if total is not None and total > LANGUAGE_INDEX_PAGE_SIZE:
        raise RefLexXHRError(
            f"Index /languages trop grand pour une résolution sûre : total={total}, "
            f"limite={LANGUAGE_INDEX_PAGE_SIZE}."
        )
    return [resolve_clld_language_id(target, language_rows_xhr) for target in targets]


def probe_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    payload = _request_page(source, target, session=session, start=0, length=1, echo=1)
    rows = _rows(payload)
    reported = _reported_total(payload)
    expected = _expected_count(target)
    filter_verified = expected is None or reported == expected
    preview = rows[0] if rows else None
    return {
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "reflex_language_name": target.get("reflex_language_name"),
        "reflex_glottocode": target.get("reflex_glottocode"),
        "clld_language_id": target.get("clld_language_id"),
        "clld_language_id_resolution": target.get("clld_language_id_resolution"),
        "expected_count": expected,
        "reported_count": reported,
        "filter_verified": filter_verified,
        "first_row_available": bool(rows),
        "first_row_type": type(preview).__name__ if preview is not None else None,
        "first_row_width": len(preview) if isinstance(preview, (list, tuple, dict)) else None,
        "first_row_preview": preview,
        "response_keys": sorted(str(key) for key in payload),
    }


def probe(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session | None = None,
) -> dict[str, Any]:
    client = _session(session)
    targets = resolve_selected_targets(source, selected_iso=selected_iso, session=client)
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "license": source.get("license"),
        "transport": "clld_datatable_xhr",
        "results": [probe_target(source, target, session=client) for target in targets],
    }


def harvest_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
    output_root: Path,
    page_size: int = DEFAULT_PAGE_SIZE,
) -> dict[str, Any]:
    if page_size < 1 or page_size > MAX_PAGE_SIZE:
        raise RefLexXHRError(f"page_size doit être compris entre 1 et {MAX_PAGE_SIZE}.")

    expected = _expected_count(target)
    output_dir = output_root / str(target["iso_639_3"])
    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / "values_datatable.jsonl"
    temp_path = output_dir / "values_datatable.jsonl.tmp"
    total_reported: int | None = None
    written = 0
    start = 0
    echo = 1

    try:
        with temp_path.open("w", encoding="utf-8") as handle:
            while True:
                payload = _request_page(
                    source,
                    target,
                    session=session,
                    start=start,
                    length=page_size,
                    echo=echo,
                )
                rows = _rows(payload)
                reported = _reported_total(payload)
                if reported is None:
                    raise RefLexXHRError(
                        f"{target['iso_639_3']}: total filtré absent de la réponse XHR."
                    )
                if total_reported is None:
                    total_reported = reported
                    if expected is not None and total_reported != expected:
                        raise RefLexXHRError(
                            f"{target['iso_639_3']}: filtre language non vérifié : "
                            f"attendu={expected}, XHR={total_reported}. Récolte annulée."
                        )
                elif reported != total_reported:
                    raise RefLexXHRError(
                        f"{target['iso_639_3']}: le total XHR a changé pendant la pagination "
                        f"({total_reported} -> {reported})."
                    )

                if not rows:
                    if written < total_reported:
                        raise RefLexXHRError(
                            f"{target['iso_639_3']}: pagination interrompue à {written}/{total_reported}."
                        )
                    break

                for row in rows:
                    record = {
                        "source": "RefLex CLLD",
                        "transport": "clld_datatable_xhr",
                        "iso_639_3": target.get("iso_639_3"),
                        "variety": target.get("variety"),
                        "reflex_language_name": target.get("reflex_language_name"),
                        "configured_glottocode": target.get("configured_glottocode"),
                        "reflex_glottocode": target.get("reflex_glottocode"),
                        "clld_language_id": target.get("clld_language_id"),
                        "license": source.get("license"),
                        "validation_status": "external_unverified",
                        "publication_approved": source.get("publication_approved", False),
                        "training_approved": source.get("training_approved", False),
                        "commercial_use_approved": source.get("commercial_use_approved", False),
                        "datatable_index": written,
                        "datatable_row": row,
                    }
                    handle.write(json.dumps(record, ensure_ascii=False) + "\n")
                    written += 1

                if written >= total_reported:
                    break
                start += len(rows)
                echo += 1
    except Exception:
        if temp_path.exists():
            temp_path.unlink()
        raise

    if total_reported is None:
        raise RefLexXHRError(f"{target['iso_639_3']}: aucune page XHR reçue.")
    if written != total_reported:
        if temp_path.exists():
            temp_path.unlink()
        raise RefLexXHRError(
            f"{target['iso_639_3']}: lignes écrites={written}, total XHR={total_reported}."
        )

    temp_path.replace(output_path)
    language_meta = {
        key: target.get(key)
        for key in (
            "iso_639_3",
            "variety",
            "configured_glottocode",
            "reflex_glottocode",
            "clld_language_id",
            "clld_language_id_resolution",
            "reflex_language_name",
            "records_biggest_source",
            "number_of_sources",
        )
    }
    (output_dir / "language_xhr.json").write_text(
        json.dumps(language_meta, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    return {
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "clld_language_id": target.get("clld_language_id"),
        "expected_count": expected,
        "reported_count": total_reported,
        "rows_written": written,
        "output": str(output_path),
    }


def harvest(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session | None = None,
    output_root: Path = RAW_ROOT,
    page_size: int = DEFAULT_PAGE_SIZE,
) -> dict[str, Any]:
    client = _session(session)
    targets = resolve_selected_targets(source, selected_iso=selected_iso, session=client)
    results = [
        harvest_target(
            source,
            target,
            session=client,
            output_root=output_root,
            page_size=page_size,
        )
        for target in targets
    ]
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "homepage": source.get("homepage"),
        "license": source.get("license"),
        "transport": "clld_datatable_xhr",
        "validation_status": "external_unverified",
        "publication_approved": source.get("publication_approved", False),
        "training_approved": source.get("training_approved", False),
        "commercial_use_approved": source.get("commercial_use_approved", False),
        "results": results,
    }
    output_root.mkdir(parents=True, exist_ok=True)
    summary = output_root / "reflex_xhr_harvest_summary.json"
    summary.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    payload["summary_path"] = str(summary)
    return payload


def _preview(value: Any, limit: int = 500) -> str:
    text = json.dumps(value, ensure_ascii=False)
    return text if len(text) <= limit else text[:limit] + "…"


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Probe/récolte RefLex via DataTable XHR avec résolution de l'ID interne CLLD"
    )
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--iso", nargs="+", required=True, help="Ex. --iso stj ou --iso stj sym")
    parser.add_argument("--probe-only", action="store_true")
    parser.add_argument("--page-size", type=int, default=DEFAULT_PAGE_SIZE)
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    args = parser.parse_args()

    source = reflex.load_source(args.config)
    configured = {str(item["iso_639_3"]) for item in source.get("targets", [])}
    selected_iso = {str(item).lower() for item in args.iso}
    unknown = sorted(selected_iso - configured)
    if unknown:
        raise RefLexXHRError("Codes ISO non configurés : " + ", ".join(unknown))

    if args.probe_only:
        payload = probe(source, selected_iso=selected_iso)
        print("Probe RefLex DataTable XHR :")
        for item in payload["results"]:
            print(
                f"- {item['iso_639_3']} ({item['variety']}): "
                f"glottocode={item['reflex_glottocode']}, clld_id={item['clld_language_id']}, "
                f"attendu={item['expected_count']}, XHR={item['reported_count']}, "
                f"filtre_vérifié={item['filter_verified']}, "
                f"première_ligne={item['first_row_available']}, "
                f"type={item['first_row_type']}, largeur={item['first_row_width']}"
            )
            if item["first_row_available"]:
                print(f"  aperçu={_preview(item['first_row_preview'])}")
        print("Mode probe-only : aucun corpus complet écrit.")
        return

    payload = harvest(
        source,
        selected_iso=selected_iso,
        output_root=args.output_root,
        page_size=args.page_size,
    )
    print("Récolte RefLex via DataTable XHR :")
    for item in payload["results"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}), clld_id={item['clld_language_id']}: "
            f"{item['rows_written']} / {item['reported_count']} lignes → {item['output']}"
        )
    print(f"Résumé : {payload['summary_path']}")


if __name__ == "__main__":
    main()
