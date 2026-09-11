from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "inspect_huggingface_sources",
    ROOT / "processors" / "inspect_huggingface_sources.py",
)
inspect = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(inspect)


def test_should_inspect_skips_mirrors_and_metadata_only():
    assert inspect.should_inspect({"canonical": True, "decision": "inspect_target_subset"}) is True
    assert inspect.should_inspect({"canonical": False, "decision": "skip_duplicate"}) is False
    assert inspect.should_inspect({"canonical": True, "decision": "metadata_reference_only"}) is False


def test_config_names_and_splits():
    payload = {
        "splits": [
            {"config": "sbd_Latn", "split": "train"},
            {"config": "sbd_Latn", "split": "test"},
            {"config": "fra_Latn", "split": "train"},
        ]
    }
    assert inspect.config_names_from_splits(payload) == ["fra_Latn", "sbd_Latn"]
    assert inspect.split_names_for_config(payload, "sbd_Latn") == ["test", "train"]


def test_target_configs_prefers_expected_names():
    source = {
        "family": "fineweb2",
        "target_iso_codes": ["sbd"],
        "expected_configs": ["sbd_Latn"],
    }
    assert inspect.target_configs_for_source(source, ["fra_Latn", "sbd_Latn"]) == ["sbd_Latn"]


def test_target_configs_finds_chikhapo_pairs():
    source = {
        "family": "chikhapo",
        "target_iso_codes": ["stj"],
    }
    configs = ["eng_fra", "stj_eng", "eng_stj", "sym_eng", "all_eng"]
    assert inspect.target_configs_for_source(source, configs) == ["eng_stj", "stj_eng"]


def test_infer_chikhapo_configs_from_repo_files():
    files = [
        "data/stj_eng/train-00000.parquet",
        "data/eng_stj/train-00000.parquet",
        "data/fra_eng/train-00000.parquet",
        "README.md",
    ]
    assert inspect.infer_chikhapo_configs_from_repo_files(files, ["stj"]) == [
        "eng_stj",
        "stj_eng",
    ]


def test_config_sizes_extracts_num_rows():
    payload = {
        "size": {
            "configs": [
                {
                    "config": "sbd_Latn",
                    "num_rows": 87,
                    "num_bytes_original_files": 1000,
                    "num_bytes_parquet_files": 800,
                    "num_bytes_memory": 2000,
                }
            ]
        }
    }
    sizes = inspect.config_sizes(payload)
    assert sizes["sbd_Latn"]["num_rows"] == 87
    assert sizes["sbd_Latn"]["num_bytes_original_files"] == 1000


def test_build_summary_marks_no_download_and_no_training():
    all_sources = [
        {"repo_id": "a"},
        {"repo_id": "b"},
        {"repo_id": "c"},
    ]
    results = [
        {"status": "inspection_success_with_fallback"},
        {"status": "inspection_failed"},
    ]
    summary = inspect.build_summary(results, all_sources)
    assert summary["configured_repo_count"] == 3
    assert summary["canonical_sources_inspected"] == 2
    assert summary["inspection_success_count"] == 1
    assert summary["inspection_failed_count"] == 1
    assert summary["content_downloaded"] is False
    assert summary["training_approved"] is False
