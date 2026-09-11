"""Récolte ciblée des configs Matya (`stj`) de ChiKhaPo.

Dataset Viewer ne peut pas servir ChiKhaPo car il possède plus de 5 000 configs.
On utilise donc le Hub pour télécharger uniquement les fichiers dont le chemin
correspond aux configs `eng_stj` et `stj_eng` configurées dans
`config/huggingface_harvest.yaml`.

Les fichiers originaux sont conservés tels quels sous data/raw/. Aucune paire
lexicale n'est promue vers un dataset validé du projet à cette étape.
"""

from __future__ import annotations

import argparse
import json
import shutil
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import yaml
from huggingface_hub import HfApi, hf_hub_download


REPO_ROOT = Path(__file__).resolve().parents[3]
CONFIG_PATH = REPO_ROOT / "tools" / "data_ingestion" / "config" / "huggingface_harvest.yaml"
RAW_ROOT = REPO_ROOT / "data" / "raw" / "huggingface" / "chikhapo"


class ChiKhaPoHarvestError(RuntimeError):
    """Erreur contrôlée pendant la récolte ciblée ChiKhaPo."""


def load_target(path: Path = CONFIG_PATH) -> dict[str, Any]:
    payload = yaml.safe_load(path.read_text(encoding="utf-8"))
    targets = payload.get("lexical_subsets") if isinstance(payload, dict) else None
    if not isinstance(targets, list):
        raise ChiKhaPoHarvestError("config/huggingface_harvest.yaml invalide : lexical_subsets absent.")
    for item in targets:
        if isinstance(item, dict) and item.get("family") == "chikhapo" and item.get("enabled", False):
            return item
    raise ChiKhaPoHarvestError("Aucune cible ChiKhaPo activée.")


def path_matches_config(path: str, config: str) -> bool:
    normalized = str(path).replace("\\", "/")
    segments = normalized.split("/")
    for segment in segments:
        stem = segment.rsplit(".", 1)[0]
        if stem == config:
            return True
    return f"/{config}/" in f"/{normalized}/"


def find_target_files(files: list[str], configs: list[str]) -> dict[str, list[str]]:
    result: dict[str, list[str]] = {config: [] for config in configs}
    for path in files:
        for config in configs:
            if path_matches_config(path, config):
                result[config].append(path)
    return {config: sorted(set(paths)) for config, paths in result.items()}


def collect(
    *,
    config_path: Path = CONFIG_PATH,
    output_root: Path = RAW_ROOT,
    list_only: bool = False,
) -> dict[str, Any]:
    target = load_target(config_path)
    repo_id = str(target["repo_id"])
    configs = [str(item) for item in target.get("configs", []) if item]
    if not configs:
        raise ChiKhaPoHarvestError("Aucune config ChiKhaPo configurée.")

    api = HfApi()
    files = [str(item) for item in api.list_repo_files(repo_id, repo_type="dataset")]
    matched = find_target_files(files, configs)
    missing = [config for config, paths in matched.items() if not paths]
    if missing:
        raise ChiKhaPoHarvestError(
            "Aucun fichier trouvé pour les configs : " + ", ".join(missing)
        )

    revision = None
    try:
        revision = api.dataset_info(repo_id).sha
    except Exception:
        revision = None

    downloaded: list[dict[str, Any]] = []
    if not list_only:
        output_root.mkdir(parents=True, exist_ok=True)
        for config, paths in matched.items():
            config_dir = output_root / config
            config_dir.mkdir(parents=True, exist_ok=True)
            for remote_path in paths:
                local_cache = hf_hub_download(
                    repo_id=repo_id,
                    filename=remote_path,
                    repo_type="dataset",
                    revision=revision,
                )
                destination = config_dir / Path(remote_path).name
                shutil.copy2(local_cache, destination)
                downloaded.append(
                    {
                        "config": config,
                        "remote_path": remote_path,
                        "local_path": str(destination),
                        "size_bytes": destination.stat().st_size,
                    }
                )

    payload = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "repo_id": repo_id,
        "revision": revision,
        "configs": configs,
        "matched_files": matched,
        "downloaded_files": downloaded,
        "list_only": list_only,
        "iso_639_3": target.get("iso_639_3"),
        "variety": target.get("variety"),
        "license": target.get("license"),
        "upstream_sources": target.get("upstream_sources", []),
        "rights_status": target.get("rights_status"),
        "local_only": bool(target.get("local_only", True)),
        "validation_status": "external_unverified",
        "publication_approved": False,
        "training_approved": False,
    }

    output_root.mkdir(parents=True, exist_ok=True)
    metadata_path = output_root / "metadata.json"
    metadata_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    payload["metadata_path"] = str(metadata_path)
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="Récolter eng_stj et stj_eng depuis ec5ug/chikhapo")
    parser.add_argument("--config", type=Path, default=CONFIG_PATH)
    parser.add_argument("--output-root", type=Path, default=RAW_ROOT)
    parser.add_argument(
        "--list-only",
        action="store_true",
        help="Lister les fichiers ciblés sans télécharger leur contenu.",
    )
    args = parser.parse_args()

    result = collect(
        config_path=args.config,
        output_root=args.output_root,
        list_only=args.list_only,
    )
    print(f"Repo : {result['repo_id']}")
    print(f"Révision : {result.get('revision') or '?'}")
    for config, paths in result["matched_files"].items():
        print(f"- {config}: {len(paths)} fichier(s)")
        for path in paths:
            print(f"    {path}")
    if result["list_only"]:
        print("Mode list-only : aucun contenu téléchargé.")
    else:
        total = sum(int(item["size_bytes"]) for item in result["downloaded_files"])
        print(f"Fichiers téléchargés : {len(result['downloaded_files'])}")
        print(f"Volume téléchargé : {total} octets")
    print(f"Métadonnées : {result['metadata_path']}")


if __name__ == "__main__":
    main()
