"""Récolte contrôlée du rapport Berthelette 2001 / SILESR 2002-005.

Source officielle identifiée : SIL Language & Culture Archives, entrée 8983.
Le lien PDF historique/officiel actuel est conservé dans `config/sources.yaml`.

Le collecteur sait fonctionner de deux façons :

1. téléchargement direct depuis SIL ;
2. ingestion d'un fichier téléchargé manuellement avec `--input-file` si SIL
   refuse les clients automatisés (HTTP 403, protection anti-bot, etc.).

Dans les deux cas, le fichier est validé comme PDF, copié dans `data/raw/`,
son SHA-256 est calculé et un fichier de métadonnées de provenance est écrit.
Aucune extraction lexicale ni normalisation linguistique n'est effectuée ici.
"""

from __future__ import annotations

import argparse
import hashlib
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
import yaml


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "sources.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "berthelette"
DEFAULT_FILENAME = "SILESR2002_005.pdf"
USER_AGENT = (
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/152.0 Safari/537.36 "
    "langue-san-data-ingestion/1.0"
)


class BertheletteHarvestError(RuntimeError):
    """Erreur contrôlée de la récolte Berthelette."""


def load_source(config_path: Path = CONFIG_PATH) -> dict[str, Any]:
    config = yaml.safe_load(config_path.read_text(encoding="utf-8"))
    source = (config or {}).get("sources", {}).get("berthelette")
    if not isinstance(source, dict):
        raise BertheletteHarvestError(
            f"Source `berthelette` absente de la configuration : {config_path}"
        )
    return source


def _session(session: requests.Session | None = None) -> requests.Session:
    client = session or requests.Session()
    client.headers.update(
        {
            "User-Agent": USER_AGENT,
            "Accept": "application/pdf,application/octet-stream;q=0.9,*/*;q=0.1",
            "Referer": "https://www.sil.org/resources/archives/8983",
        }
    )
    return client


def _validate_pdf(content: bytes, *, source_label: str) -> None:
    if not content:
        raise BertheletteHarvestError(f"{source_label}: contenu vide.")
    if not content.startswith(b"%PDF-"):
        preview = content[:80]
        raise BertheletteHarvestError(
            f"{source_label}: le contenu reçu n'est pas un PDF (début={preview!r})."
        )
    if len(content) < 10_000:
        raise BertheletteHarvestError(
            f"{source_label}: PDF anormalement petit ({len(content)} octets)."
        )


def _download(
    source: dict[str, Any], *, session: requests.Session
) -> tuple[bytes, dict[str, Any]]:
    url = str(source.get("direct_pdf") or "").strip()
    if not url:
        raise BertheletteHarvestError("URL `direct_pdf` absente de la configuration Berthelette.")

    response = session.get(url, timeout=120, allow_redirects=True)
    status = getattr(response, "status_code", None)
    if status == 403:
        raise BertheletteHarvestError(
            "SIL répond HTTP 403 au téléchargement automatisé. Le fichier officiel est bien "
            "identifié, mais il faut le télécharger manuellement puis relancer avec "
            "`--input-file /chemin/SILESR2002_005.pdf`."
        )
    response.raise_for_status()
    content = bytes(getattr(response, "content", b""))
    _validate_pdf(content, source_label=url)
    return content, {
        "acquisition_method": "direct_http",
        "requested_url": url,
        "final_url": str(getattr(response, "url", url)),
        "http_status": status,
        "content_type": str(getattr(response, "headers", {}).get("Content-Type", "")),
    }


def _read_local(path: Path) -> tuple[bytes, dict[str, Any]]:
    path = path.expanduser().resolve()
    if not path.exists() or not path.is_file():
        raise BertheletteHarvestError(f"Fichier local introuvable : {path}")
    content = path.read_bytes()
    _validate_pdf(content, source_label=str(path))
    return content, {
        "acquisition_method": "manual_download_then_local_ingest",
        "input_file": str(path),
    }


def inspect_payload(content: bytes, source: dict[str, Any], acquisition: dict[str, Any]) -> dict[str, Any]:
    digest = hashlib.sha256(content).hexdigest()
    return {
        "source": source.get("name"),
        "report_id": source.get("report_id"),
        "archive_entry": source.get("archive_entry"),
        "publication_entry": source.get("publication_entry"),
        "direct_pdf": source.get("direct_pdf"),
        "expected_pages": source.get("expected_pages"),
        "rights_status": source.get("rights_status"),
        "license": source.get("license"),
        "publication_approved": source.get("publication_approved", False),
        "training_approved": source.get("training_approved", False),
        "commercial_use_approved": source.get("commercial_use_approved", False),
        "validation_status": "external_unverified",
        "bytes": len(content),
        "sha256": digest,
        "pdf_magic_ok": content.startswith(b"%PDF-"),
        **acquisition,
    }


def harvest(
    source: dict[str, Any],
    *,
    input_file: Path | None = None,
    output_root: Path = RAW_ROOT,
    session: requests.Session | None = None,
) -> dict[str, Any]:
    if input_file is not None:
        content, acquisition = _read_local(input_file)
    else:
        content, acquisition = _download(source, session=_session(session))

    metadata = inspect_payload(content, source, acquisition)
    output_root.mkdir(parents=True, exist_ok=True)
    output_path = output_root / DEFAULT_FILENAME
    temp_path = output_root / f"{DEFAULT_FILENAME}.tmp"
    temp_path.write_bytes(content)
    temp_path.replace(output_path)

    metadata.update(
        {
            "generated_at": datetime.now(timezone.utc).isoformat(),
            "raw_path": str(output_path),
        }
    )
    metadata_path = output_root / "metadata.json"
    metadata_path.write_text(
        json.dumps(metadata, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    metadata["metadata_path"] = str(metadata_path)
    return metadata


def probe(
    source: dict[str, Any],
    *,
    input_file: Path | None = None,
    session: requests.Session | None = None,
) -> dict[str, Any]:
    if input_file is not None:
        content, acquisition = _read_local(input_file)
    else:
        content, acquisition = _download(source, session=_session(session))
    return inspect_payload(content, source, acquisition)


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Récolte le rapport Berthelette 2001 / SILESR 2002-005 depuis SIL"
    )
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--input-file", type=Path, help="PDF téléchargé manuellement si SIL bloque HTTP")
    parser.add_argument("--probe-only", action="store_true", help="Valide le PDF sans écrire dans data/raw")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    args = parser.parse_args()

    source = load_source(args.config)

    if args.probe_only:
        result = probe(source, input_file=args.input_file)
        print("Probe Berthelette 2001 :")
        print(f"- report_id : {result['report_id']}")
        print(f"- méthode   : {result['acquisition_method']}")
        print(f"- octets    : {result['bytes']}")
        print(f"- PDF valide: {result['pdf_magic_ok']}")
        print(f"- SHA-256   : {result['sha256']}")
        print(f"- droits    : {result['rights_status']}")
        print("Mode probe-only : aucun fichier RAW écrit.")
        return

    result = harvest(
        source,
        input_file=args.input_file,
        output_root=args.output_root,
    )
    print("Récolte Berthelette 2001 :")
    print(f"- report_id : {result['report_id']}")
    print(f"- méthode   : {result['acquisition_method']}")
    print(f"- octets    : {result['bytes']}")
    print(f"- SHA-256   : {result['sha256']}")
    print(f"- RAW       : {result['raw_path']}")
    print(f"- metadata  : {result['metadata_path']}")
    print(f"- droits    : {result['rights_status']}")


if __name__ == "__main__":
    main()
