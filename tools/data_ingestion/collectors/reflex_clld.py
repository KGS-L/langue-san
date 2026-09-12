"""Récolte ciblée RefLex CLLD pour les trois variétés SAN.

RefLex CLLD expose des index CSV via le framework CLLD. Le collecteur procède en
deux temps :

1. télécharger le petit index `languages.csv` pour résoudre les identifiants
   internes RefLex à partir des glottocodes du projet ;
2. sonder le DataTable `values` avec une seule ligne afin d'obtenir le nombre de
   fiches lexicales avant toute récolte complète.

En mode récolte, le CSV `values.csv?language=<id>` est conservé tel quel sous
`data/raw/reflex/<iso>/values.csv`. Aucune normalisation, déduplication ou
validation linguistique n'est appliquée au RAW.

La base RefLex CLLD est sous CC-BY-NC-SA-4.0. Le projet marque donc cette source
comme non commerciale : aucune approbation implicite pour publication commerciale
ou entraînement d'un produit commercial n'est déduite de la récolte.
"""

from __future__ import annotations

import argparse
import csv
import io
import json
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


def _pick_column(headers: list[str], candidates: tuple[str, ...]) -> str | None:
    normalised = {_normalise_header(header): header for header in headers}
    for candidate in candidates:
        found = normalised.get(_normalise_header(candidate))
        if found is not None:
            return found
    return None


def fetch_languages(source: dict[str, Any], *, session: requests.Session | None = None) -> tuple[list[str], list[dict[str, str]]]:
    client = _session(session)
    url = str(source["endpoints"]["languages_csv"])
    response = client.get(url, timeout=90)
    response.raise_for_status()
    text = response.content.decode("utf-8-sig") if getattr(response, "content", None) is not None else response.text
    reader = csv.DictReader(io.StringIO(text))
    headers = list(reader.fieldnames or [])
    if not headers:
        raise RefLexHarvestError("languages.csv RefLex ne contient aucun en-tête CSV.")
    rows = [dict(row) for row in reader]
    if not rows:
        raise RefLexHarvestError("languages.csv RefLex ne contient aucune langue.")
    return headers, rows


def _row_has_exact_value(row: dict[str, str], expected: str) -> bool:
    needle = expected.strip().lower()
    return any(str(value or "").strip().lower() == needle for value in row.values())


def resolve_targets(
    source: dict[str, Any],
    headers: list[str],
    rows: list[dict[str, str]],
) -> list[dict[str, Any]]:
    id_col = _pick_column(headers, ("id", "language_id", "languageid"))
    glottocode_col = _pick_column(headers, ("glottocode", "glotto_code", "glottocode_id"))
    iso_col = _pick_column(headers, ("iso_639_3", "iso639p3code", "iso6393", "iso"))
    name_col = _pick_column(headers, ("name", "language", "language_name"))
    if id_col is None:
        raise RefLexHarvestError(
            "Impossible d'identifier la colonne ID de languages.csv. "
            f"Colonnes observées : {', '.join(headers)}"
        )

    resolved = []
    for target in source.get("targets", []):
        iso = str(target["iso_639_3"])
        glottocode = str(target["glottocode"])
        matches = []
        for row in rows:
            glotto_match = (
                glottocode_col is not None
                and str(row.get(glottocode_col) or "").strip().lower() == glottocode.lower()
            )
            iso_match = (
                iso_col is not None
                and str(row.get(iso_col) or "").strip().lower() == iso.lower()
            )
            # Fallback prudent pour les exports dont le nom exact des colonnes change.
            exact_fallback = _row_has_exact_value(row, glottocode) or _row_has_exact_value(row, iso)
            if glotto_match or iso_match or exact_fallback:
                matches.append(row)
        if len(matches) != 1:
            raise RefLexHarvestError(
                f"Résolution RefLex ambiguë pour {iso}/{glottocode}: {len(matches)} ligne(s) trouvée(s)."
            )
        row = matches[0]
        language_id = str(row.get(id_col) or "").strip()
        if not language_id:
            raise RefLexHarvestError(f"ID RefLex vide pour {iso}/{glottocode}.")
        resolved.append(
            {
                "iso_639_3": iso,
                "variety": target.get("variety"),
                "glottocode": glottocode,
                "reflex_language_id": language_id,
                "reflex_language_name": row.get(name_col) if name_col else None,
                "language_row": row,
            }
        )
    return resolved


def _probe_count(payload: dict[str, Any]) -> int | None:
    for key in ("iTotalDisplayRecords", "recordsFiltered", "iTotalRecords", "recordsTotal"):
        value = payload.get(key)
        if isinstance(value, int):
            return value
        if isinstance(value, str) and value.isdigit():
            return int(value)
    return None


def probe_language(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    url = str(source["endpoints"]["values"])
    response = session.get(
        url,
        params={
            "language": target["reflex_language_id"],
            "sEcho": "1",
            "iDisplayStart": "0",
            "iDisplayLength": "1",
        },
        timeout=90,
        headers={"Accept": "application/json"},
    )
    response.raise_for_status()
    try:
        payload = response.json()
    except ValueError as exc:
        preview = (response.text or "")[:200].replace("\n", " ")
        raise RefLexHarvestError(
            "Le probe DataTable RefLex n'a pas renvoyé du JSON. "
            f"Début de réponse : {preview!r}"
        ) from exc
    if not isinstance(payload, dict):
        raise RefLexHarvestError("Réponse DataTable RefLex invalide.")
    total = _probe_count(payload)
    if total is None:
        raise RefLexHarvestError(
            "Le nombre de lignes n'est pas présent dans la réponse RefLex. "
            f"Clés reçues : {', '.join(sorted(str(k) for k in payload))}"
        )
    data = payload.get("aaData", payload.get("data", []))
    first_row_available = isinstance(data, list) and bool(data)
    return {
        **{key: target.get(key) for key in (
            "iso_639_3",
            "variety",
            "glottocode",
            "reflex_language_id",
            "reflex_language_name",
        )},
        "num_records_reported": total,
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
    text = response.content.decode("utf-8-sig") if getattr(response, "content", None) is not None else response.text
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
    return {
        "iso_639_3": target["iso_639_3"],
        "variety": target.get("variety"),
        "glottocode": target.get("glottocode"),
        "reflex_language_id": target.get("reflex_language_id"),
        "rows_written": row_count,
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
        for item in payload["results"]:
            print(
                f"- {item['iso_639_3']} ({item['variety']}): "
                f"RefLex id={item['reflex_language_id']}, "
                f"nom={item.get('reflex_language_name') or '?'}, "
                f"fiches={item['num_records_reported']}, "
                f"première_ligne={item['first_row_available']}"
            )
        print("Mode probe-only : aucun export lexical complet n'a été téléchargé.")
        print(f"Résumé : {summary}")
        return

    payload = harvest(source, output_root=args.output_root, selected_iso=selected_iso)
    print("Récolte RefLex CLLD :")
    for item in payload["results"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}): {item['rows_written']} lignes "
            f"→ {item['output']}"
        )
    print(f"Métadonnées : {payload['metadata_path']}")


if __name__ == "__main__":
    main()
