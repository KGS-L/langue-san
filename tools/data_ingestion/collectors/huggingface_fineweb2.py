"""Récolte FineWeb2 sbd_Latn via les fichiers Parquet du Hub.

Le Dataset Viewer `/rows` renvoie actuellement HTTP 500 pour la config
`sbd_Latn`. Ce collecteur contourne uniquement cette limite technique en allant
sur les fichiers source Parquet déjà publiés dans le repo
`HuggingFaceFW/fineweb-2`.

`--probe-only` ne télécharge aucun corpus : il liste les fichiers Parquet ciblés
et leur taille. Le mode de récolte télécharge uniquement les splits réellement
présents sous `data/sbd_Latn/` et convertit les lignes en JSONL sous data/raw/
en conservant la provenance.

Le README/config d'un dataset peut déclarer un split qui n'existe pas comme
dossier source sur la révision courante. Ce cas est documenté comme
`missing_on_revision` au lieu de faire échouer toute la sonde.

Aucune ligne n'est validée linguistiquement et aucune approbation de publication
ou d'entraînement n'est déduite de cette récolte.
"""

from __future__ import annotations

import argparse
import json
from datetime import date, datetime, timezone
from pathlib import Path
from typing import Any

import pyarrow.parquet as pq
import yaml
from huggingface_hub import HfApi, hf_hub_download
from huggingface_hub.errors import RemoteEntryNotFoundError


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "huggingface_harvest.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface" / "fineweb2"


class FineWeb2HarvestError(RuntimeError):
    """Erreur contrôlée pendant la récolte FineWeb2."""


def load_target(path: Path = CONFIG_PATH) -> dict[str, Any]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    targets = payload.get("text_subsets") if isinstance(payload, dict) else None
    if not isinstance(targets, list):
        raise FineWeb2HarvestError("config/huggingface_harvest.yaml invalide : text_subsets absent.")
    for item in targets:
        if (
            isinstance(item, dict)
            and item.get("family") == "fineweb2"
            and item.get("enabled", False)
        ):
            return item
    raise FineWeb2HarvestError("Aucune cible FineWeb2 activée.")


def _json_safe(value: Any) -> Any:
    if value is None or isinstance(value, (str, int, float, bool)):
        return value
    if isinstance(value, (datetime, date)):
        return value.isoformat()
    if isinstance(value, bytes):
        return {"__bytes_hex__": value.hex()}
    if isinstance(value, dict):
        return {str(key): _json_safe(item) for key, item in value.items()}
    if isinstance(value, (list, tuple)):
        return [_json_safe(item) for item in value]
    return str(value)


def list_split_files(
    target: dict[str, Any],
    split: str,
    *,
    api: HfApi | None = None,
    revision: str | None = None,
) -> list[dict[str, Any]]:
    client = api or HfApi()
    repo_id = str(target["repo_id"])
    config = str(target["config"])
    path_in_repo = f"data/{config}/{split}"
    try:
        items = client.list_repo_tree(
            repo_id,
            path_in_repo=path_in_repo,
            recursive=True,
            revision=revision,
            repo_type="dataset",
        )
        files: list[dict[str, Any]] = []
        for item in items:
            remote_path = getattr(item, "path", None)
            if not remote_path or not str(remote_path).endswith(".parquet"):
                continue
            files.append(
                {
                    "path": str(remote_path),
                    "size_bytes": getattr(item, "size", None),
                    "blob_id": getattr(item, "blob_id", None),
                }
            )
        return sorted(files, key=lambda item: item["path"])
    except RemoteEntryNotFoundError:
        return []


def resolve_revision(repo_id: str, *, api: HfApi | None = None) -> str | None:
    try:
        return (api or HfApi()).dataset_info(repo_id).sha
    except Exception:
        return None


def probe(
    target: dict[str, Any],
    splits: list[str],
    *,
    api: HfApi | None = None,
) -> dict[str, Any]:
    client = api or HfApi()
    revision = resolve_revision(str(target["repo_id"]), api=client)
    results = []
    for split in splits:
        files = list_split_files(target, split, api=client, revision=revision)
        known_sizes = [item["size_bytes"] for item in files if isinstance(item.get("size_bytes"), int)]
        results.append(
            {
                "split": split,
                "status": "available" if files else "missing_on_revision",
                "available": bool(files),
                "file_count": len(files),
                "total_size_bytes": (
                    sum(known_sizes) if files and len(known_sizes) == len(files) else None
                ),
                "files": files,
            }
        )

    if not any(item["available"] for item in results):
        raise FineWeb2HarvestError(
            f"Aucun Parquet trouvé pour {target['repo_id']}/{target['config']} "
            f"sur la révision {revision or '?'} pour les splits demandés."
        )

    return {
        "repo_id": target.get("repo_id"),
        "revision": revision,
        "config": target.get("config"),
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "probe_only": True,
        "content_downloaded": False,
        "splits": results,
    }


def _read_parquet_rows(path: Path):
    parquet_file = pq.ParquetFile(path)
    row_idx = 0
    for batch in parquet_file.iter_batches(batch_size=1000):
        for row in batch.to_pylist():
            yield row_idx, _json_safe(row)
            row_idx += 1


def harvest_split(
    target: dict[str, Any],
    split: str,
    *,
    revision: str | None,
    api: HfApi | None = None,
    output_root: Path = RAW_ROOT,
) -> dict[str, Any]:
    client = api or HfApi()
    files = list_split_files(target, split, api=client, revision=revision)
    if not files:
        return {
            "split": split,
            "status": "missing_on_revision",
            "rows_written": 0,
            "file_count": 0,
            "files": [],
            "output": None,
        }

    config = str(target["config"])
    output_dir = output_root / config
    output_dir.mkdir(parents=True, exist_ok=True)
    output_path = output_dir / f"{split}.jsonl"
    temp_path = output_path.with_suffix(".jsonl.tmp")
    if temp_path.exists():
        temp_path.unlink()

    global_row_idx = 0
    file_stats = []
    try:
        with temp_path.open("w", encoding="utf-8") as handle:
            for file_info in files:
                remote_path = str(file_info["path"])
                local_cache = Path(
                    hf_hub_download(
                        repo_id=str(target["repo_id"]),
                        filename=remote_path,
                        repo_type="dataset",
                        revision=revision,
                    )
                )
                file_rows = 0
                for row_idx_in_file, row in _read_parquet_rows(local_cache):
                    record = {
                        "source": "huggingface",
                        "repo_id": target.get("repo_id"),
                        "family": "fineweb2",
                        "config": config,
                        "split": split,
                        "revision": revision,
                        "remote_file": remote_path,
                        "row_idx": global_row_idx,
                        "row_idx_in_file": row_idx_in_file,
                        "iso_639_3": target.get("iso_639_3"),
                        "variety": target.get("variety"),
                        "license": target.get("license"),
                        "rights_status": target.get("rights_status"),
                        "validation_status": "external_unverified",
                        "source_row": row,
                    }
                    handle.write(json.dumps(record, ensure_ascii=False) + "\n")
                    global_row_idx += 1
                    file_rows += 1
                file_stats.append(
                    {
                        **file_info,
                        "rows": file_rows,
                    }
                )
        temp_path.replace(output_path)
    except Exception:
        if temp_path.exists():
            temp_path.unlink()
        raise

    return {
        "split": split,
        "status": "harvested",
        "rows_written": global_row_idx,
        "file_count": len(files),
        "files": file_stats,
        "output": str(output_path),
    }


def harvest(
    target: dict[str, Any],
    splits: list[str],
    *,
    api: HfApi | None = None,
    output_root: Path = RAW_ROOT,
) -> dict[str, Any]:
    client = api or HfApi()
    revision = resolve_revision(str(target["repo_id"]), api=client)
    results = [
        harvest_split(
            target,
            split,
            revision=revision,
            api=client,
            output_root=output_root,
        )
        for split in splits
    ]
    if not any(item.get("status") == "harvested" for item in results):
        raise FineWeb2HarvestError(
            f"Aucun split FineWeb2 disponible pour {target['config']} sur la révision {revision or '?'}"
        )

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "repo_id": target.get("repo_id"),
        "revision": revision,
        "config": target.get("config"),
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "license": target.get("license"),
        "rights_status": target.get("rights_status"),
        "harvest_method": "huggingface_hub_original_parquet",
        "dataset_viewer_rows_bypassed": True,
        "validation_status": "external_unverified",
        "publication_approved": False,
        "training_approved": False,
        "results": results,
    }
    output_root.mkdir(parents=True, exist_ok=True)
    metadata_path = output_root / "fineweb2_metadata.json"
    metadata_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    payload["metadata_path"] = str(metadata_path)
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="Récolter FineWeb2 sbd_Latn via les Parquet source du Hub")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--split", nargs="*", help="Splits ciblés; défaut = ceux de la config")
    parser.add_argument("--probe-only", action="store_true")
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    args = parser.parse_args()

    target = load_target(args.config)
    configured_splits = [str(item) for item in target.get("splits", [])]
    splits = [str(item) for item in (args.split or configured_splits)]
    unknown = sorted(set(splits) - set(configured_splits))
    if unknown:
        raise FineWeb2HarvestError("Splits non configurés : " + ", ".join(unknown))

    if args.probe_only:
        result = probe(target, splits)
        print(f"FineWeb2 : {result['repo_id']} @ {result.get('revision') or '?'}")
        print(f"Config : {result['config']}")
        for item in result["splits"]:
            if not item["available"]:
                print(f"- {item['split']}: absent sur cette révision (aucun Parquet source)")
                continue
            size = item.get("total_size_bytes")
            size_text = f"{size} octets" if size is not None else "taille inconnue"
            print(f"- {item['split']}: {item['file_count']} parquet(s), {size_text}")
            for file_info in item["files"]:
                print(f"    {file_info['path']} ({file_info.get('size_bytes') or '?'} octets)")
        print("Mode probe-only : aucun fichier Parquet n'a été téléchargé.")
        return

    result = harvest(target, splits, output_root=args.output_root)
    print(f"Récolte FineWeb2 : {result['repo_id']} @ {result.get('revision') or '?'}")
    for item in result["results"]:
        if item.get("status") == "missing_on_revision":
            print(f"- {result['config']}/{item['split']}: absent sur cette révision, ignoré")
            continue
        print(
            f"- {result['config']}/{item['split']}: {item['rows_written']} lignes "
            f"depuis {item['file_count']} parquet(s)"
        )
    print(f"Métadonnées : {result['metadata_path']}")


if __name__ == "__main__":
    main()
