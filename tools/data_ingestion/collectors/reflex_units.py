"""Récolte des unités lexicales RefLex via le vrai DataTable `/units`.

La page détail d'une langue RefLex expose un `sAjaxSource` du type :

    /units?filterLanguage=<internal_pk>&filterSource=&filterReference=

et propose explicitement un export CSV du DataTable (limite 10 000 lignes).
Ce collecteur découvre dynamiquement cette URL depuis la page langue, vérifie le
nombre de lignes annoncé par le XHR puis télécharge le CSV correspondant.

Le RAW est conservé tel quel. Aucune normalisation, déduplication ou validation
linguistique n'est réalisée ici.
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import io
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Any
from urllib.parse import urlsplit, urlunsplit

import requests

try:
    from collectors import reflex_clld as reflex
    from collectors import reflex_clld_xhr as xhr
    from collectors import reflex_clld_detail_probe as detail
except ImportError:
    import reflex_clld as reflex
    import reflex_clld_xhr as xhr
    import reflex_clld_detail_probe as detail


CONFIG_PATH = xhr.CONFIG_PATH
RAW_ROOT = xhr.RAW_ROOT
USER_AGENT = "langue-san-data-ingestion/1.0 (+reflex-units-csv)"
MAX_EXPORT_ROWS = 10_000


class RefLexUnitsError(RuntimeError):
    """Erreur contrôlée de la récolte RefLex `/units`."""


def _session(session: requests.Session | None = None) -> requests.Session:
    client = session or requests.Session()
    client.headers.update({"User-Agent": USER_AGENT})
    return client


def _is_units_url(url: str) -> bool:
    return urlsplit(url).path.rstrip("/").endswith("/units")


def _csv_url(ajax_source: str) -> str:
    parts = urlsplit(ajax_source)
    path = parts.path.rstrip("/")
    if not path.endswith("/units"):
        raise RefLexUnitsError(f"sAjaxSource inattendu pour les unités : {ajax_source}")
    return urlunsplit((parts.scheme, parts.netloc, path + ".csv", parts.query, parts.fragment))


def _decode_csv(content: bytes, fallback_text: str = "") -> str:
    try:
        return content.decode("utf-8-sig")
    except UnicodeDecodeError:
        if fallback_text:
            return fallback_text
        return content.decode("utf-8", errors="replace")


def _csv_shape(text: str) -> tuple[list[str], int]:
    reader = csv.reader(io.StringIO(text))
    rows = list(reader)
    if not rows:
        return [], 0
    return rows[0], max(0, len(rows) - 1)


def discover_units_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
) -> dict[str, Any]:
    diagnostic = detail.diagnose_target(source, target, session=session)
    candidates = [
        item
        for item in diagnostic.get("ajax_probes", [])
        if _is_units_url(str(item.get("ajax_source") or ""))
        and item.get("json") is True
    ]
    if len(candidates) != 1:
        raise RefLexUnitsError(
            f"{target.get('iso_639_3')}: impossible d'identifier un unique DataTable /units "
            f"(candidats={len(candidates)})."
        )

    units = candidates[0]
    reported = units.get("reported_count")
    if not isinstance(reported, int) or reported <= 0:
        raise RefLexUnitsError(
            f"{target.get('iso_639_3')}: le DataTable /units annonce un total invalide : {reported}."
        )
    if reported > MAX_EXPORT_ROWS:
        raise RefLexUnitsError(
            f"{target.get('iso_639_3')}: {reported} lignes dépassent la limite d'export "
            f"RefLex de {MAX_EXPORT_ROWS}."
        )

    ajax_source = str(units["ajax_source"])
    return {
        **target,
        "detail_url": diagnostic.get("detail_url"),
        "units_ajax_source": ajax_source,
        "units_csv_url": _csv_url(ajax_source),
        "units_reported_count": reported,
        "units_first_row": units.get("first_row"),
        "units_request_params": units.get("request_params"),
    }


def harvest_target(
    source: dict[str, Any],
    target: dict[str, Any],
    *,
    session: requests.Session,
    output_root: Path = RAW_ROOT,
) -> dict[str, Any]:
    discovered = discover_units_target(source, target, session=session)
    csv_url = str(discovered["units_csv_url"])
    response = session.get(
        csv_url,
        timeout=120,
        headers={"Accept": "text/csv,text/plain;q=0.9,*/*;q=0.1"},
    )
    response.raise_for_status()
    content = bytes(getattr(response, "content", b""))
    if not content:
        content = str(getattr(response, "text", "")).encode("utf-8")
    text = _decode_csv(content, str(getattr(response, "text", "")))
    headers, row_count = _csv_shape(text)

    expected = int(discovered["units_reported_count"])
    if row_count != expected:
        raise RefLexUnitsError(
            f"{target.get('iso_639_3')}: export CSV incomplet ou inattendu : "
            f"XHR={expected}, CSV={row_count}."
        )

    iso = str(target["iso_639_3"])
    output_dir = output_root / iso
    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / "units.csv"
    temp_path = output_dir / "units.csv.tmp"
    temp_path.write_bytes(content)
    temp_path.replace(output_path)

    digest = hashlib.sha256(content).hexdigest()
    metadata = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "homepage": source.get("homepage"),
        "license": source.get("license"),
        "validation_status": "external_unverified",
        "publication_approved": source.get("publication_approved", False),
        "training_approved": source.get("training_approved", False),
        "commercial_use_approved": source.get("commercial_use_approved", False),
        "iso_639_3": iso,
        "variety": target.get("variety"),
        "reflex_language_name": target.get("reflex_language_name"),
        "reflex_glottocode": target.get("reflex_glottocode"),
        "clld_language_id": target.get("clld_language_id"),
        "detail_url": discovered.get("detail_url"),
        "units_ajax_source": discovered.get("units_ajax_source"),
        "units_csv_url": csv_url,
        "xhr_reported_rows": expected,
        "csv_rows": row_count,
        "csv_headers": headers,
        "sha256": digest,
        "raw_path": str(output_path),
        "note": (
            "Le compteur Contribution peut différer du nombre d'unités exportables. "
            "Le contrôle de complétude compare ici le CSV au DataTable /units."
        ),
    }
    metadata_path = output_dir / "units_metadata.json"
    metadata_path.write_text(
        json.dumps(metadata, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    return metadata


def harvest(
    source: dict[str, Any],
    *,
    selected_iso: set[str],
    session: requests.Session | None = None,
    output_root: Path = RAW_ROOT,
) -> dict[str, Any]:
    client = _session(session)
    targets = xhr.resolve_selected_targets(source, selected_iso=selected_iso, session=client)
    results = [
        harvest_target(source, target, session=client, output_root=output_root)
        for target in targets
    ]
    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "source": source.get("name"),
        "license": source.get("license"),
        "transport": "reflex_units_csv_discovered_from_language_page",
        "results": results,
    }
    output_root.mkdir(parents=True, exist_ok=True)
    summary_path = output_root / "reflex_units_harvest_summary.json"
    summary_path.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    payload["summary_path"] = str(summary_path)
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Récolte RefLex depuis le DataTable /units et son export CSV officiel"
    )
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--iso", nargs="+", required=True, help="Ex. --iso stj ou --iso stj sym")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    args = parser.parse_args()

    source = reflex.load_source(args.config)
    configured = {str(item["iso_639_3"]) for item in source.get("targets", [])}
    selected_iso = {str(item).lower() for item in args.iso}
    unknown = sorted(selected_iso - configured)
    if unknown:
        raise RefLexUnitsError("Codes ISO non configurés : " + ", ".join(unknown))

    payload = harvest(source, selected_iso=selected_iso, output_root=args.output_root)
    print("Récolte RefLex /units CSV :")
    for item in payload["results"]:
        print(
            f"- {item['iso_639_3']} ({item['variety']}): "
            f"{item['csv_rows']} lignes, {len(item['csv_headers'])} colonnes "
            f"→ {item['raw_path']}"
        )
        print(f"  glottocode={item['reflex_glottocode']}, clld_id={item['clld_language_id']}")
        print(f"  ajax={item['units_ajax_source']}")
        print(f"  csv={item['units_csv_url']}")
        print(f"  headers={', '.join(item['csv_headers'])}")
    print(f"Résumé : {payload['summary_path']}")


if __name__ == "__main__":
    main()
