"""Récolte ciblée RefLex CLLD pour les variétés SAN.

Le collecteur reste prudent :

- `languages.csv` sert d'index d'autorité pour savoir quelles langues sont
  réellement présentes dans la version CLLD ;
- le glottocode configuré est essayé en premier ;
- les alias de nom ne servent qu'au diagnostic/probe lorsque le glottocode
  diffère ;
- quand l'index indique exactement une source, `Number of records in biggest
  source` est aussi le nombre total de fiches de la langue et permet un probe
  sans télécharger le lexique ;
- si plusieurs sources existent, un probe DataTable XHR est tenté ;
- une récolte complète reste bloquée pour tout mapping non résolu ou nécessitant
  une revue explicite.

L'export RefLex observé en septembre 2026 ne contient ni ID interne ni code ISO.
Il expose notamment `Name`, `Glottocode`, `Number of records in biggest source`
et `Number of sources`.

La base RefLex CLLD est sous CC-BY-NC-SA-4.0. Les données restent donc séparées
d'un futur corpus commercial tant qu'aucune autorisation supplémentaire n'est
obtenue.
"""

from __future__ import annotations

import argparse
import csv
import io
import json
import re
import unicodedata
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
import yaml


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "sources.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "reflex"
USER_AGENT = "langue-san-data-ingestion/1.0 (+reflex-clld-target-harvest)"


class RefLexHarvestError(RuntimeError):
    """Erreur contrôlée pendant le probe ou la récolte RefLex."""


def load_source(path: Path = CONFIG_PATH) -> dict[str, Any]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    sources = payload.get("sources") if isinstance(payload, dict) else None
    source = sources.get("reflex") if isinstance(sources, dict) else None
    if not isinstance(source, dict) or not source.get("enabled", False):
        raise RefLexHarvestError("Source RefLex absente ou désactivée dans config/sources.yaml.")
    return source


def _session(session: requests.Session | None = None) -> requests.Session:
    client = session or requests.Session()
    client.headers.update({"User-Agent": USER_AGENT})
    return client


def _normalise_header(value: str) -> str:
    return "".join(ch.lower() for ch in value if ch.isalnum())


def _normalise_text(value: str) -> str:
    ascii_text = (
        unicodedata.normalize("NFKD", str(value or ""))
        .encode("ascii", "ignore")
        .decode("ascii")
        .casefold()
    )
    return " ".join(re.findall(r"[a-z0-9]+", ascii_text))


def _token_signature(value: str) -> tuple[str, ...]:
    return tuple(sorted(set(_normalise_text(value).split())))


def _pick_column(headers: list[str], candidates: tuple[str, ...]) -> str | None:
    normalised = {_normalise_header(header): header for header in headers}
    for candidate in candidates:
        found = normalised.get(_normalise_header(candidate))
        if found is not None:
            return found
    return None


def _parse_int(value: Any) -> int | None:
    text = str(value or "").strip().replace(" ", "").replace(",", "")
    return int(text) if text.isdigit() else None


def fetch_languages(
    source: dict[str, Any],
    *,
    session: requests.Session | None = None,
) -> tuple[list[str], list[dict[str, str]]]:
    client = _session(session)
    url = str(source["endpoints"]["languages_csv"])
    response = client.get(url, timeout=90)
    response.raise_for_status()
    text = (
        response.content.decode("utf-8-sig")
        if getattr(response, "content", None) is not None
        else response.text
    )
    reader = csv.DictReader(io.StringIO(text))
    headers = list(reader.fieldnames or [])
    if not headers:
        raise RefLexHarvestError("languages.csv RefLex ne contient aucun en-tête CSV.")
    rows = [dict(row) for row in reader]
    if not rows:
        raise RefLexHarvestError("languages.csv RefLex ne contient aucune langue.")
    return headers, rows


def _row_has_exact_value(row: dict[str, str], expected: str) -> bool:
    needle = str(expected or "").strip().casefold()
    return any(str(value or "").strip().casefold() == needle for value in row.values())


def _target_aliases(target: dict[str, Any]) -> list[str]:
    return [str(item) for item in target.get("name_aliases", []) if str(item).strip()]


def _diagnostic_candidates(
    target: dict[str, Any],
    *,
    headers: list[str],
    rows: list[dict[str, str]],
    limit: int = 12,
) -> list[dict[str, str]]:
    """Retourne des lignes proches sans les considérer comme un mapping validé."""

    name_col = _pick_column(headers, ("name", "language", "language_name"))
    glottocode_col = _pick_column(headers, ("glottocode", "glotto_code", "glottocode_id"))
    family_col = _pick_column(headers, ("family",))
    biggest_col = _pick_column(headers, ("number of records in biggest source",))
    sources_col = _pick_column(headers, ("number of sources",))

    aliases = _target_aliases(target)
    keywords: set[str] = set()
    for alias in aliases:
        keywords.update(_normalise_text(alias).split())
    keywords.discard("san")

    scored: list[tuple[int, dict[str, str]]] = []
    for row in rows:
        name = str(row.get(name_col) or "") if name_col else ""
        tokens = set(_normalise_text(name).split())
        score = 0
        for alias in aliases:
            if _normalise_text(name) == _normalise_text(alias):
                score = max(score, 100)
            elif _token_signature(name) == _token_signature(alias):
                score = max(score, 95)

        distinctive_hits = len((keywords - {"samo"}) & tokens)
        if distinctive_hits:
            score = max(score, 60 + distinctive_hits * 10)
            if "samo" in tokens:
                score += 10
        elif "samo" in tokens:
            score = max(score, 20)

        if score:
            scored.append((score, row))

    scored.sort(key=lambda item: (-item[0], _normalise_text(str(item[1].get(name_col) or ""))))
    return [
        {
            "score": str(score),
            "name": str(row.get(name_col) or "") if name_col else "",
            "glottocode": str(row.get(glottocode_col) or "") if glottocode_col else "",
            "family": str(row.get(family_col) or "") if family_col else "",
            "records_biggest_source": str(row.get(biggest_col) or "") if biggest_col else "",
            "number_of_sources": str(row.get(sources_col) or "") if sources_col else "",
        }
        for score, row in scored[:limit]
    ]


def _resolve_one_target(
    target: dict[str, Any],
    *,
    headers: list[str],
    rows: list[dict[str, str]],
) -> dict[str, Any]:
    id_col = _pick_column(headers, ("id", "language_id", "languageid"))
    glottocode_col = _pick_column(headers, ("glottocode", "glotto_code", "glottocode_id"))
    iso_col = _pick_column(headers, ("iso_639_3", "iso639p3code", "iso6393", "iso"))
    name_col = _pick_column(headers, ("name", "language", "language_name"))
    biggest_col = _pick_column(headers, ("number of records in biggest source",))
    sources_col = _pick_column(headers, ("number of sources",))

    iso = str(target["iso_639_3"])
    configured_glottocode = str(target["glottocode"])
    aliases = _target_aliases(target)

    exact_matches: list[dict[str, str]] = []
    for row in rows:
        glotto_match = (
            glottocode_col is not None
            and str(row.get(glottocode_col) or "").strip().casefold()
            == configured_glottocode.casefold()
        )
        iso_match = (
            iso_col is not None
            and str(row.get(iso_col) or "").strip().casefold() == iso.casefold()
        )
        exact_fallback = _row_has_exact_value(row, configured_glottocode) or _row_has_exact_value(row, iso)
        if glotto_match or iso_match or exact_fallback:
            exact_matches.append(row)

    resolution_method: str | None = None
    mapping_review_required = False
    matches = exact_matches

    if len(matches) == 1:
        resolution_method = "configured_glottocode_or_iso"
    elif len(matches) > 1:
        return {
            "iso_639_3": iso,
            "variety": target.get("variety"),
            "configured_glottocode": configured_glottocode,
            "resolution_status": "ambiguous_exact_mapping",
            "mapping_review_required": True,
            "diagnostic_candidates": _diagnostic_candidates(target, headers=headers, rows=rows),
        }
    else:
        alias_matches: list[dict[str, str]] = []
        if name_col and aliases:
            alias_norms = {_normalise_text(alias) for alias in aliases}
            alias_signatures = {_token_signature(alias) for alias in aliases}
            for row in rows:
                name = str(row.get(name_col) or "")
                if _normalise_text(name) in alias_norms or _token_signature(name) in alias_signatures:
                    alias_matches.append(row)

        if len(alias_matches) == 1:
            matches = alias_matches
            resolution_method = "name_alias_probe_fallback"
            mapping_review_required = True
        elif len(alias_matches) > 1:
            return {
                "iso_639_3": iso,
                "variety": target.get("variety"),
                "configured_glottocode": configured_glottocode,
                "resolution_status": "ambiguous_name_alias",
                "mapping_review_required": True,
                "diagnostic_candidates": _diagnostic_candidates(target, headers=headers, rows=rows),
            }
        else:
            return {
                "iso_639_3": iso,
                "variety": target.get("variety"),
                "configured_glottocode": configured_glottocode,
                "resolution_status": "not_found_in_languages_index",
                "mapping_review_required": True,
                "diagnostic_candidates": _diagnostic_candidates(target, headers=headers, rows=rows),
            }

    row = matches[0]
    observed_glottocode = str(row.get(glottocode_col) or "").strip() if glottocode_col else ""
    if id_col is not None:
        language_id = str(row.get(id_col) or "").strip()
        id_resolution_method = f"exported_column:{id_col}"
    elif observed_glottocode:
        language_id = observed_glottocode
        id_resolution_method = f"glottocode_fallback:{glottocode_col}"
    else:
        return {
            "iso_639_3": iso,
            "variety": target.get("variety"),
            "configured_glottocode": configured_glottocode,
            "resolution_status": "resolved_row_without_usable_id",
            "mapping_review_required": True,
            "language_row": row,
        }

    if observed_glottocode.casefold() != configured_glottocode.casefold():
        mapping_review_required = True

    records_biggest_source = _parse_int(row.get(biggest_col)) if biggest_col else None
    number_of_sources = _parse_int(row.get(sources_col)) if sources_col else None

    return {
        "iso_639_3": iso,
        "variety": target.get("variety"),
        "configured_glottocode": configured_glottocode,
        "reflex_glottocode": observed_glottocode or None,
        "glottocode": configured_glottocode,
        "reflex_language_id": language_id,
        "reflex_language_id_resolution": id_resolution_method,
        "reflex_language_name": row.get(name_col) if name_col else None,
        "resolution_method": resolution_method,
        "resolution_status": "resolved",
        "mapping_review_required": mapping_review_required,
        "records_biggest_source": records_biggest_source,
        "number_of_sources": number_of_sources,
        "language_row": row,
    }


def resolve_targets(
    source: dict[str, Any],
    headers: list[str],
    rows: list[dict[str, str]],
) -> list[dict[str, Any]]:
    if _pick_column(headers, ("glottocode", "glotto_code", "glottocode_id")) is None:
        raise RefLexHarvestError(
            "Impossible d'identifier la colonne Glottocode dans languages.csv. "
            f"Colonnes observées : {', '.join(headers)}"
        )
    return [_resolve_one_target(target, headers=headers, rows=rows) for target in source.get("targets", [])]


def _probe_count(payload: dict[str, Any]) -> int | None:
    for key in ("iTotalDisplayRecords", "recordsFiltered", "iTotalRecords", "recordsTotal"):
        value = payload.get(key)
        parsed = _parse_int(value)
        if parsed is not None:
            return parsed
    return None


def probe_language(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    base = {
        key: target.get(key)
        for key in (
            "iso_639_3",
            "variety",
            "configured_glottocode",
            "reflex_glottocode",
            "glottocode",
            "reflex_language_id",
            "reflex_language_id_resolution",
            "reflex_language_name",
            "resolution_method",
            "resolution_status",
            "mapping_review_required",
            "records_biggest_source",
            "number_of_sources",
        )
    }

    if target.get("resolution_status") != "resolved":
        return {
            **base,
            "probe_status": "not_probed_unresolved_mapping",
            "num_records_reported": None,
            "first_row_available": False,
            "diagnostic_candidates": target.get("diagnostic_candidates", []),
        }

    # RefLex fournit déjà un compte exact quand une langue n'a qu'une seule
    # source : le nombre de fiches de la plus grosse source est alors le total.
    source_count = target.get("number_of_sources")
    biggest = target.get("records_biggest_source")
    if source_count == 1 and isinstance(biggest, int):
        return {
            **base,
            "probe_status": "ok_from_languages_index",
            "num_records_reported": biggest,
            "count_source": "languages.csv_single_source",
            "first_row_available": biggest > 0,
        }

    # Pour les langues multi-sources, CLLD sert le DataTable via une requête XHR.
    # Ne pas forcer Accept: application/json : RefLex répond 406 à cette
    # négociation alors que l'endpoint HTML/XHR reste le mécanisme attendu.
    url = str(source["endpoints"]["values"])
    try:
        response = session.get(
            url,
            params={
                "language": target["reflex_language_id"],
                "sEcho": "1",
                "iDisplayStart": "0",
                "iDisplayLength": "1",
            },
            timeout=90,
            headers={"X-Requested-With": "XMLHttpRequest"},
        )
        response.raise_for_status()
        payload = response.json()
    except Exception as exc:
        return {
            **base,
            "probe_status": "probe_request_failed",
            "probe_error": f"{type(exc).__name__}: {exc}",
            "num_records_reported": None,
            "first_row_available": False,
        }

    if not isinstance(payload, dict):
        return {
            **base,
            "probe_status": "invalid_json_shape",
            "num_records_reported": None,
            "first_row_available": False,
        }

    total = _probe_count(payload)
    data = payload.get("aaData", payload.get("data", []))
    first_row_available = isinstance(data, list) and bool(data)
    if total is None:
        return {
            **base,
            "probe_status": "count_missing",
            "num_records_reported": None,
            "first_row_available": first_row_available,
            "response_keys": sorted(str(key) for key in payload),
        }

    return {
        **base,
        "probe_status": "ok_from_datatable_xhr",
        "num_records_reported": total,
        "count_source": "values_datatable_xhr",
        "first_row_available": first_row_available,
    }


def probe(
    source: dict[str, Any],
    *,
    session: requests.Session | None = None,
    selected_iso: set[str] | None = None,
) -> dict[str, Any]:
    client = _session(session)
    headers, language_rows = fetch_languages(source, session=client)
    targets = resolve_targets(source, headers, language_rows)
    if selected_iso:
        targets = [item for item in targets if item["iso_639_3"] in selected_iso]
    results = [probe_language(source, target, session=client) for target in targets]
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "homepage": source.get("homepage"),
        "version": source.get("version"),
        "license": source.get("license"),
        "status": source.get("status"),
        "commercial_use_approved": source.get("commercial_use_approved", False),
        "languages_index_rows": len(language_rows),
        "languages_index_columns": headers,
        "results": results,
    }


def _count_csv_rows(text: str) -> tuple[list[str], int]:
    reader = csv.reader(io.StringIO(text))
    try:
        headers = next(reader)
    except StopIteration:
        return [], 0
    return headers, sum(1 for row in reader if any(str(cell).strip() for cell in row))


def _assert_harvest_mapping_is_confirmed(targets: list[dict[str, Any]]) -> None:
    problems = []
    for target in targets:
        if target.get("resolution_status") != "resolved":
            problems.append(
                f"{target.get('iso_639_3')}: mapping non résolu ({target.get('resolution_status')})"
            )
        elif target.get("mapping_review_required"):
            problems.append(
                f"{target.get('iso_639_3')}: RefLex={target.get('reflex_glottocode')} "
                f"vs config={target.get('configured_glottocode')}"
            )
    if problems:
        raise RefLexHarvestError(
            "Récolte RefLex bloquée tant que le mapping langue n'est pas confirmé dans la config : "
            + "; ".join(problems)
        )


def harvest_language(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
    output_root: Path,
) -> dict[str, Any]:
    url = str(source["endpoints"]["values_csv"])
    response = session.get(
        url,
        params={"language": target["reflex_language_id"]},
        timeout=180,
    )
    response.raise_for_status()
    text = (
        response.content.decode("utf-8-sig")
        if getattr(response, "content", None) is not None
        else response.text
    )
    headers, row_count = _count_csv_rows(text)
    if not headers:
        raise RefLexHarvestError(f"Export values.csv vide pour {target['iso_639_3']}.")

    output_dir = output_root / str(target["iso_639_3"])
    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / "values.csv"
    output_path.write_text(text, encoding="utf-8")
    (output_dir / "language.json").write_text(
        json.dumps(target, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    expected = (
        target.get("records_biggest_source")
        if target.get("number_of_sources") == 1
        else None
    )
    return {
        "iso_639_3": target["iso_639_3"],
        "variety": target.get("variety"),
        "configured_glottocode": target.get("configured_glottocode"),
        "reflex_glottocode": target.get("reflex_glottocode"),
        "reflex_language_id": target.get("reflex_language_id"),
        "reflex_language_id_resolution": target.get("reflex_language_id_resolution"),
        "rows_written": row_count,
        "expected_rows_from_index": expected,
        "count_matches_index": expected is None or row_count == expected,
        "columns": headers,
        "output": str(output_path),
    }


def harvest(
    source: dict[str, Any],
    *,
    session: requests.Session | None = None,
    output_root: Path = RAW_ROOT,
    selected_iso: set[str] | None = None,
) -> dict[str, Any]:
    client = _session(session)
    headers, language_rows = fetch_languages(source, session=client)
    targets = resolve_targets(source, headers, language_rows)
    if selected_iso:
        targets = [item for item in targets if item["iso_639_3"] in selected_iso]
    _assert_harvest_mapping_is_confirmed(targets)

    results = [
        harvest_language(source, target, session=client, output_root=output_root)
        for target in targets
    ]
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "homepage": source.get("homepage"),
        "version": source.get("version"),
        "doi": source.get("doi"),
        "license": source.get("license"),
        "status": source.get("status"),
        "validation_status": "external_unverified",
        "publication_approved": source.get("publication_approved", False),
        "training_approved": source.get("training_approved", False),
        "commercial_use_approved": source.get("commercial_use_approved", False),
        "harvest_method": "clld_filtered_values_csv",
        "results": results,
    }
    output_root.mkdir(parents=True, exist_ok=True)
    metadata = output_root / "reflex_harvest_summary.json"
    metadata.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    payload["metadata_path"] = str(metadata)
    return payload


def _print_probe_item(item: dict[str, Any]) -> None:
    iso = item.get("iso_639_3")
    variety = item.get("variety")
    if item.get("resolution_status") != "resolved":
        print(
            f"- {iso} ({variety}): mapping={item.get('resolution_status')}, "
            f"glottocode_config={item.get('configured_glottocode')}"
        )
        candidates = item.get("diagnostic_candidates") or []
        if candidates:
            print("  candidats RefLex :")
            for candidate in candidates:
                print(
                    "    - "
                    f"nom={candidate.get('name') or '?'}, "
                    f"glottocode={candidate.get('glottocode') or '?'}, "
                    f"famille={candidate.get('family') or '?'}, "
                    f"plus_grosse_source={candidate.get('records_biggest_source') or '?'}, "
                    f"sources={candidate.get('number_of_sources') or '?'}"
                )
        return

    print(
        f"- {iso} ({variety}): "
        f"nom={item.get('reflex_language_name') or '?'}, "
        f"glottocode_config={item.get('configured_glottocode') or '?'}, "
        f"glottocode_reflex={item.get('reflex_glottocode') or '?'}, "
        f"id={item.get('reflex_language_id') or '?'}, "
        f"résolution={item.get('resolution_method') or '?'}, "
        f"probe={item.get('probe_status') or '?'}"
    )
    if str(item.get("probe_status") or "").startswith("ok_"):
        print(
            f"  fiches={item.get('num_records_reported')}, "
            f"source_compte={item.get('count_source')}, "
            f"première_ligne={item.get('first_row_available')}, "
            f"sources={item.get('number_of_sources')}, "
            f"mapping_review_required={item.get('mapping_review_required')}"
        )
    elif item.get("probe_error"):
        print(f"  erreur_probe={item['probe_error']}")


def main() -> None:
    parser = argparse.ArgumentParser(description="Sonder/récolter RefLex CLLD pour sbd, stj et sym")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--iso", nargs="*", help="Limiter à sbd, stj et/ou sym")
    parser.add_argument("--probe-only", action="store_true")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    args = parser.parse_args()

    source = load_source(args.config)
    configured = {str(item["iso_639_3"]) for item in source.get("targets", [])}
    selected = {str(item).lower() for item in (args.iso or [])}
    unknown = sorted(selected - configured)
    if unknown:
        raise RefLexHarvestError("Codes ISO non configurés : " + ", ".join(unknown))
    selected_iso = selected or None

    if args.probe_only:
        payload = probe(source, selected_iso=selected_iso)
        args.output_root.mkdir(parents=True, exist_ok=True)
        summary = args.output_root / "reflex_probe_summary.json"
        summary.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
        print("Sonde RefLex CLLD :")
        print(f"- index langues : {payload['languages_index_rows']} lignes")
        print(f"- colonnes : {', '.join(payload['languages_index_columns'])}")
        for item in payload["results"]:
            _print_probe_item(item)
        print("Mode probe-only : aucun export lexical complet n'a été téléchargé.")
        print(f"Résumé : {summary}")
        return

    payload = harvest(source, output_root=args.output_root, selected_iso=selected_iso)
    print("Récolte RefLex CLLD :")
    for item in payload["results"]:
        expected = item.get("expected_rows_from_index")
        expected_text = f" / attendu={expected}" if expected is not None else ""
        print(
            f"- {item['iso_639_3']} ({item['variety']}): {item['rows_written']} lignes"
            f"{expected_text} → {item['output']}"
        )
    print(f"Métadonnées : {payload['metadata_path']}")


if __name__ == "__main__":
    main()
