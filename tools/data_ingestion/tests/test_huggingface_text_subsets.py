from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_text_subsets",
    ROOT / "collectors" / "huggingface_text_subsets.py",
)
collector = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(collector)


def test_select_targets_filters_by_key():
    targets = [
        {"key": "a", "enabled": True},
        {"key": "b", "enabled": True},
    ]
    assert collector.select_targets(targets, ["b"]) == [{"key": "b", "enabled": True}]


def test_select_targets_rejects_unknown_key():
    targets = [{"key": "a", "enabled": True}]
    try:
        collector.select_targets(targets, ["x"])
    except collector.HuggingFaceTextHarvestError as exc:
        assert "x" in str(exc)
    else:
        raise AssertionError("Une cible inconnue doit lever une erreur.")


def test_load_targets_from_yaml(tmp_path):
    path = tmp_path / "harvest.yaml"
    path.write_text(
        """
text_subsets:
  - key: keep
    enabled: true
  - key: skip
    enabled: false
""".strip(),
        encoding="utf-8",
    )
    assert collector.load_targets(path) == [{"key": "keep", "enabled": True}]
