from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
import json


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_text_subsets",
    ROOT / "collectors" / "huggingface_text_subsets.py",
)
collector = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(collector)


class _FakeResponse:
    def __init__(self, payload, status_code=200, headers=None):
        self.payload = payload
        self.status_code = status_code
        self.headers = headers or {}

    def raise_for_status(self):
        return None

    def json(self):
        return self.payload


class _FakeSession:
    def get(self, *args, **kwargs):
        return _FakeResponse(
            {
                "rows": [{"row_idx": 0, "row": {"text": "abc"}}],
                "num_rows_total": 87,
                "partial": False,
            }
        )


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


def test_probe_split_reports_total_without_harvest():
    target = {
        "repo_id": "example/repo",
        "family": "fineweb2",
        "config": "sbd_Latn",
    }
    result = collector.probe_split(target, "train", session=_FakeSession())
    assert result["num_rows_total_reported"] == 87
    assert result["partial"] is False
    assert result["first_row_available"] is True
    assert result["first_row_idx"] == 0


def test_resume_state_uses_existing_row_indices(tmp_path):
    path = tmp_path / "partial.jsonl"
    records = [
        {"row_idx": 0, "source_row": {"text": "a"}},
        {"row_idx": 1, "source_row": {"text": "b"}},
        {"row_idx": 2, "source_row": {"text": "c"}},
    ]
    path.write_text(
        "".join(json.dumps(item) + "\n" for item in records),
        encoding="utf-8",
    )
    assert collector._resume_state(path) == (3, 3)


def test_resume_state_empty_file_starts_at_zero(tmp_path):
    path = tmp_path / "empty.jsonl"
    path.write_text("", encoding="utf-8")
    assert collector._resume_state(path) == (0, 0)
