"""Inspection ciblée des sources Hugging Face après le triage automatique.

Cette étape ne télécharge toujours pas les corpus. Elle utilise l'API publique
Dataset Viewer de Hugging Face pour vérifier :

- les configs/splits réellement disponibles ;
- les sous-ensembles SAN attendus (ex. sbd_Latn) ;
- le nombre de lignes lorsque /size le fournit ;
- les paires lexicales ChiKhaPo impliquant sbd/stj/sym ;
- la présence d'au moins une ligne ciblée dans les datasets plats filtrables.

Les miroirs/duplicatas documentés dans config/huggingface_sources.yaml sont
ignorés. Le résultat est un rapport d'inspection, pas une validation linguistique
ni une autorisation de réutilisation.
"""

from __future__ import annotations

import argparse
import json
from datetime import datetime, timezone
from pathlib import Path
from typing import Any
from urllib.parse import quote

import requests
import yaml


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "huggingface_sources.yaml"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "huggingface"
VIEWER_BASE = "https://datasets-server.huggingface.co"
TARGET_ISO = {"sbd", "stj", "sym"}
USER_AGENT = "langue-san-data-ingestion/1.0 (+hf-source-inspection)"


class HuggingFaceSourceInspectionError(RuntimeError):
    """Erreur contrôlée pendant l'inspection Hugging Face."""


def load_source_plan(path: Path = CONFIG_PATH) -> list[dict[str, Any]]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    sources = payload.get("sources") if isinstance(payload, dict) else None
    if not isinstance(sources, list):
        raise HuggingFaceSourceInspectionError("config/huggingface_sources.yaml invalide : 'sources' absent.")
    return [item for item in sources if isinstance(item, dict)]


def should_inspect(source: dict[str, Any]) -> bool:
    if not source.get("canonical", False):
        return False
    return str(source.get("decision") or "") not in {
        "skip_duplicate",
        "skip_until_canonical_unavailable",
        "metadata_reference_only",
        "defer_derivative",
        "defer_orthography_analysis",
    }


def _get_json(session: requests.Session, endpoint: str, *, dataset: str, timeout: int = 90, **params: Any) -> dict[str, Any]:
    query = {"dataset": dataset, **params}
    response = session.get(
        f"{VIEWER_BASE}/{endpoint}",
        params=query,
        timeout=timeout,
        headers={"User-Agent": USER_AGENT},
    )
    response.raise_for_status()
    payload = response.json()
    return payload if isinstance(payload, dict) else {}


def config_names_from_splits(payload: dict[str, Any]) -> list[str]:
    configs = {
        str(item.get("config"))
        for item in payload.get("splits", [])
        if isinstance(item, dict) and item.get("config")
    }
    return sorted(configs)


def split_names_for_config(payload: dict[str, Any], config: str) -> list[str]:
    splits = {
        str(item.get("split"))
        for item in payload.get("splits", [])
        if isinstance(item, dict) and item.get("config") == config and item.get("split")
    }
    return sorted(splits)


def target_configs_for_source(source: dict[str, Any], available_configs: list[str]) -> list[str]:
    expected = [str(item) for item in source.get("expected_configs", []) if item]
    found = [item for item in expected if item in available_configs]
    if found:
        return sorted(set(found))

    family = str(source.get("family") or "")
    targets = {str(item).lower() for item in source.get("target_iso_codes", []) if item}

    if family == "chikhapo":
        result = []
        for config in available_configs:
            parts = {part.lower() for part in config.split("_")}
            if targets & parts:
                result.append(config)
        return sorted(set(result))

    # Certains repos utilisent sbd-Latn au lieu de sbd_Latn.
    result = []
    for config in available_configs:
        lowered = config.lower().replace("-", "_")
        if any(lowered == iso or lowered.startswith(f"{iso}_") for iso in targets):
            result.append(config)
    return sorted(set(result))


def config_sizes(size_payload: dict[str, Any]) -> dict[str, dict[str, Any]]:
    size = size_payload.get("size") if isinstance(size_payload, dict) else None
    configs = size.get("configs", []) if isinstance(size, dict) else []
    result: dict[str, dict[str, Any]] = {}
    for item in configs:
        if not isinstance(item, dict) or not item.get("config"):
            continue
        result[str(item["config"])] = {
            "num_rows": item.get("num_rows"),
            "num_bytes_original_files": item.get("num_bytes_original_files"),
            "num_bytes_parquet_files": item.get("num_bytes_parquet_files"),
            "num_bytes_memory": item.get("num_bytes_memory"),
        }
    return result


def _probe_filter_presence(
    session: requests.Session,
    *,
    dataset: str,
    config: str,
    split: str,
    column: str,
    value: str,
) -> dict[str, Any]:
    where = f'"{column}"=\'{value}\''
    try:
        payload = _get_json(
            session,
            "filter",
            dataset=dataset,
            config=config,
            split=split,
            where=where,
            offset=0,
            length=1,
        )
    except Exception as exc:  # réseau réel / viewer indisponible
        return {"value": value, "available": None, "error": str(exc)}

    rows = payload.get("rows", [])
    return {
        "value": value,
        "available": bool(rows),
        "partial": bool(payload.get("partial", False)),
    }


def inspect_source(source: dict[str, Any], session: requests.Session) -> dict[str, Any]:
    repo_id = str(source.get("repo_id") or "")
    result: dict[str, Any] = {
        "repo_id": repo_id,
        "family": source.get("family"),
        "source_type": source.get("source_type"),
        "decision": source.get("decision"),
        "priority": source.get("priority"),
        "license": source.get("license"),
        "target_iso_codes": source.get("target_iso_codes", []),
        "status": "inspection_started",
        "target_configs": [],
        "filter_probes": [],
    }

    try:
        splits_payload = _get_json(session, "splits", dataset=repo_id)
        available_configs = config_names_from_splits(splits_payload)
        result["available_config_count"] = len(available_configs)
        result["target_configs"] = target_configs_for_source(source, available_configs)

        try:
            size_payload = _get_json(session, "size", dataset=repo_id)
            sizes = config_sizes(size_payload)
        except Exception as exc:
            sizes = {}
            result["size_error"] = str(exc)

        target_config_details = []
        for config in result["target_configs"]:
            target_config_details.append(
                {
                    "config": config,
                    "splits": split_names_for_config(splits_payload, config),
                    **sizes.get(config, {}),
                }
            )
        result["target_config_details"] = target_config_details

        # Datasets plats où l'ISO est une colonne plutôt qu'une config.
        family = str(source.get("family") or "")
        if family in {"mms_ulab_v2", "panlex_snapshot"}:
            default_config = "default" if "default" in available_configs else (available_configs[0] if available_configs else None)
            if default_config:
                split_names = split_names_for_config(splits_payload, default_config)
                split = "train" if "train" in split_names else (split_names[0] if split_names else None)
                if split:
                    column = "iso3" if family == "mms_ulab_v2" else "639-3"
                    result["filter_config"] = default_config
                    result["filter_split"] = split
                    result["filter_column"] = column
                    for iso in source.get("target_iso_codes", []):
                        result["filter_probes"].append(
                            _probe_filter_presence(
                                session,
                                dataset=repo_id,
                                config=default_config,
                                split=split,
                                column=column,
                                value=str(iso),
                            )
                        )

        result["status"] = "inspection_success"
    except Exception as exc:
        result["status"] = "inspection_failed"
        result["error"] = str(exc)

    return result


def inspect_sources(
    sources: list[dict[str, Any]],
    *,
    session: requests.Session | None = None,
) -> list[dict[str, Any]]:
    client = session or requests.Session()
    return [inspect_source(source, client) for source in sources if should_inspect(source)]


def build_summary(results: list[dict[str, Any]], all_sources: list[dict[str, Any]]) -> dict[str, Any]:
    return {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "configured_repo_count": len(all_sources),
        "canonical_sources_inspected": len(results),
        "inspection_success_count": sum(item.get("status") == "inspection_success" for item in results),
        "inspection_failed_count": sum(item.get("status") == "inspection_failed" for item in results),
        "duplicate_or_deferred_repo_count": len(all_sources) - len(results),
        "content_downloaded": False,
        "training_approved": False,
        "notes": [
            "Cette étape inspecte configs, tailles et présence de codes ISO sans télécharger les corpus complets.",
            "Les miroirs connus sont exclus afin d'éviter de compter plusieurs fois la même source.",
            "Une présence de config ou de ligne ne valide pas linguistiquement le contenu.",
        ],
    }


def write_report(
    results: list[dict[str, Any]],
    all_sources: list[dict[str, Any]],
    output_dir: Path = DEFAULT_OUTPUT_DIR,
) -> Path:
    output_dir.mkdir(parents=True, exist_ok=True)
    path = output_dir / "huggingface_source_inspection.json"
    payload = {"summary": build_summary(results, all_sources), "sources": results}
    path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return path


def main() -> None:
    parser = argparse.ArgumentParser(description="Inspecter les sources Hugging Face canoniques SAN sans télécharger les corpus")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    args = parser.parse_args()

    all_sources = load_source_plan(args.config)
    results = inspect_sources(all_sources)
    report_path = write_report(results, all_sources, args.output_dir)

    print(f"Repos configurés : {len(all_sources)}")
    print(f"Sources canoniques inspectées : {len(results)}")
    print("Résultats :")
    for item in results:
        print(f"- {item['repo_id']} [{item['status']}]")
        details = item.get("target_config_details", [])
        if details:
            for detail in details:
                rows = detail.get("num_rows")
                rows_text = f"{rows} lignes" if rows is not None else "taille inconnue"
                print(f"    config {detail['config']}: {rows_text}; splits={','.join(detail.get('splits', []))}")
        elif item.get("target_configs") == []:
            print("    aucune config SAN dédiée détectée")
        for probe in item.get("filter_probes", []):
            print(
                f"    filtre ISO {probe['value']}: "
                f"présent={probe.get('available')} partial={probe.get('partial')}"
            )
        if item.get("error"):
            print(f"    erreur: {item['error']}")

    print("Aucun corpus complet n'a été téléchargé.")
    print(f"Rapport : {report_path}")


if __name__ == "__main__":
    main()
