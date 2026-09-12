from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_panlex",
    ROOT / "collectors" / "huggingface_panlex.py",
)
panlex = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(panlex)


class _FakeResponse:
    status_code = 200
    headers = {}

    def __init__(self, payload):
        self.payload = payload

    def raise_for_status(self):
        return None

    def json(self):
        return self.payload


class _FakeSession:
    def get(self, *args, **kwargs):
        return _FakeResponse(
            {
                "rows": [
                    {
                        "row_idx": 7,
                        "row": {
                            "vocab": "demo",
                            "639-3": "sbd",
                            "639-3_english_name": "Southern Samo",
                            "var_code": "sbd-000",
                            "english_name_var": "Southern Samo",
                        },
                    }
                ],
                "num_rows_total": 12,
                "partial": False,
            }
        )


def test_load_target_finds_panlex(tmp_path):
    path = tmp_path / "harvest.yaml"
    path.write_text(
        """
lexical_subsets:
  - family: chikhapo
    enabled: true
  - family: panlex_snapshot
    repo_id: lbourdois/panlex
    enabled: true
""".strip(),
        encoding="utf-8",
    )
    assert panlex.load_target(path)["repo_id"] == "lbourdois/panlex"


def test_probe_reports_target_count():
    target = {
        "repo_id": "lbourdois/panlex",
        "config": "default",
        "split": "train",
        "filter_column": "639-3",
        "iso_codes": {"sbd": "maka"},
    }
    result = panlex.probe_iso(target, "sbd", session=_FakeSession())
    assert result["num_rows_total_reported"] == 12
    assert result["partial"] is False
    assert result["first_row_available"] is True


def test_existing_state_reads_valid_jsonl(tmp_path):
    path = tmp_path / "rows.jsonl"
    path.write_text(
        '{"row_idx": 1}\n{"row_idx": 2}\nnot-json\n',
        encoding="utf-8",
    )
    count, indices = panlex._existing_state(path)
    assert count == 2
    assert indices == {1, 2}


def test_harvest_iso_writes_provenance(tmp_path):
    target = {
        "repo_id": "lbourdois/panlex",
        "config": "default",
        "split": "train",
        "filter_column": "639-3",
        "iso_codes": {"sbd": "maka"},
        "license": "cc0-1.0",
        "rights_status": "dataset_cc0_upstream_provenance_preserved",
        "snapshot_date": "2024-01-01",
    }
    result = panlex.harvest_iso(
        target,
        "sbd",
        session=_FakeSession(),
        output_root=tmp_path,
        request_delay=0,
    )
    assert result["rows_written"] == 1
    output = Path(result["output"])
    text = output.read_text(encoding="utf-8")
    assert '"iso_639_3": "sbd"' in text
    assert '"variety": "maka"' in text
    assert '"vocab": "demo"' in text
