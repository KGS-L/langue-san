"""Fallback XHR ciblé pour RefLex CLLD.

Pourquoi ce collecteur séparé ?
--------------------------------
L'index RefLex expose bien `languages.csv`, mais l'URL
`/values.csv?language=<glottocode>` répond actuellement HTTP 406 pour les valeurs
lexicales. Le framework CLLD sert toutefois les DataTables via des requêtes XHR
(`X-Requested-With: XMLHttpRequest` + `sEcho`). Ce module utilise ce transport
sans prétendre qu'il s'agit d'un export DB canonique.

Règles de sécurité :
- uniquement les mappings RefLex déjà résolus sans revue requise ;
- vérification du nombre total annoncé avant récolte complète ;
- si le filtre `language=` n'est pas appliqué (total inattendu), arrêt immédiat ;
- RAW conservé tel que renvoyé par le DataTable sous `datatable_row` ;
- aucune normalisation, déduplication ou validation linguistique ;
- licence RefLex : CC-BY-NC-SA-4.0, usage commercial non approuvé.
"""

from __future__ import annotations

import argparse
import json
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
    # Dans l'index RefLex, si Number of sources == 1, le nombre de fiches de la
    # plus grosse source est nécessairement le total de la langue.
    if target.get("number_of_sources") == 1:
        value = target.get("records_biggest_source")
        return value if isinstance(value, int) else _parse_int(value)
    return None


def _request_page(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
    start: int,
    length: int,
    echo: int,
) -> dict[str, Any]:
    url = str(source["endpoints"]["values"])
    response = session.get(
        url,
        params={
            "language": target["reflex_language_id"],
            "sEcho": str(echo),
            "iDisplayStart": str(start),
            "iDisplayLength": str(length),
        },
        timeout=90,
        headers={
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json, text/javascript, */*; q=0.01",
        },
    )
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


def resolve_selected_targets(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session,
) -> list[dict[str, Any]]:
    headers, language_rows = reflex.fetch_languages(source, session=session)
    targets = reflex.resolve_targets(source, headers, language_rows)
    targets = [item for item in targets if item.get("iso_639_3") in selected_iso]
    if not targets:
        raise RefLexXHRError("Aucune cible RefLex résolue pour les codes ISO demandés.")

    problems = []
    for target in targets:
        if target.get("resolution_status") != "resolved":
            problems.append(
                f"{target.get('iso_639_3')}: mapping={target.get('resolution_status')}"
            )
        elif target.get("mapping_review_required"):
            problems.append(
                f"{target.get('iso_639_3')}: mapping_review_required=true"
            )
    if problems:
        raise RefLexXHRError(
            "Récolte XHR bloquée tant que le mapping n'est pas confirmé : "
            + "; ".join(problems)
        )
    return targets


def probe_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    payload = _request_page(
        source,
        target,
        session=session,
        start=0,
        length=1,
        echo=1,
    )
    rows = _rows(payload)
    reported = _reported_total(payload)
    expected = _expected_count(target)
    filter_verified = expected is None or reported == expected

    preview = rows[0] if rows else None
    return {
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "reflex_language_name": target.get("reflex_language_name"),
        "reflex_language_id": target.get("reflex_language_id"),
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
    results = [probe_target(source, target, session=client) for target in targets]
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "license": source.get("license"),
        "transport": "clld_datatable_xhr",
        "results": results,
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
                    "reflex_language_id": target.get("reflex_language_id"),
                    "configured_glottocode": target.get("configured_glottocode"),
                    "reflex_glottocode": target.get("reflex_glottocode"),
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
            if len(rows) == 0:
                raise RefLexXHRError(
                    f"{target['iso_639_3']}: aucune progression pendant la pagination."
                )
            start += len(rows)
            echo += 1

    if total_reported is None:
        raise RefLexXHRError(f"{target['iso_639_3']}: aucune page XHR reçue.")
    if written != total_reported:
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
            "reflex_language_id",
            "reflex_language_name",
            "records_biggest_source",
            "number_of_sources",
        )
    }
    (output_dir / "language_xhr.json").write_text(
        json.dumps(language_meta, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    return {
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
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
        description="Probe/récolte RefLex via DataTable XHR lorsque values.csv répond 406"
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
                f"attendu={item['expected_count']}, XHR={item['reported_count']}, "
                f"filtre_vérifié={item['filter_verified']}, "
                f"première_ligne={item['first_row_available']}, "
                f"type={item['first_row_type']}, largeur={item['first_row_width']}"
            )
            if item["first_row_available"]:
                print(f"  aperçu={_preview(item['first_row_preview'])}")
        print("Mode probe-only : une seule ligne demandée par langue, aucun corpus complet écrit.")
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
            f"- {item['iso_639_3']} ({item['variety']}): "
            f"{item['rows_written']} / {item['reported_count']} lignes "
            f"→ {item['output']}"
        )
    print(f"Résumé : {payload['summary_path']}")


if __name__ == "__main__":
    main()
