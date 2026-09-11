from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_chikhapo",
    ROOT / "collectors" / "huggingface_chikhapo.py",
)
collector = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(collector)


def test_path_matches_config_by_filename_stem():
    assert collector.path_matches_config("data/eng_stj.parquet", "eng_stj") is True
    assert collector.path_matches_config("data/stj_eng.jsonl", "stj_eng") is True
    assert collector.path_matches_config("README.md", "stj_eng") is False


def test_path_matches_config_by_directory_segment():
    assert collector.path_matches_config("data/eng_stj/train-00000.parquet", "eng_stj") is True


def test_find_target_files_groups_configs():
    files = [
        "data/eng_stj.parquet",
        "data/stj_eng/train.jsonl",
        "data/fra_eng.parquet",
    ]
    result = collector.find_target_files(files, ["eng_stj", "stj_eng"])
    assert result["eng_stj"] == ["data/eng_stj.parquet"]
    assert result["stj_eng"] == ["data/stj_eng/train.jsonl"]


def test_load_target_from_yaml(tmp_path):
    path = tmp_path / "harvest.yaml"
    path.write_text(
        """
lexical_subsets:
  - family: chikhapo
    repo_id: ec5ug/chikhapo
    configs: [eng_stj, stj_eng]
    enabled: true
""".strip(),
        encoding="utf-8",
    )
    target = collector.load_target(path)
    assert target["repo_id"] == "ec5ug/chikhapo"
    assert target["configs"] == ["eng_stj", "stj_eng"]
