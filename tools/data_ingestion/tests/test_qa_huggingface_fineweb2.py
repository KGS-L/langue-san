from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "qa_huggingface_fineweb2",
    ROOT / "processors" / "qa_huggingface_fineweb2.py",
)
qa = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(qa)


def _record(text: str, row_idx: int = 0):
    return {
        "source": "huggingface",
        "repo_id": "HuggingFaceFW/fineweb-2",
        "family": "fineweb2",
        "config": "sbd_Latn",
        "split": "train",
        "revision": "abc123",
        "remote_file": "data/sbd_Latn/train/000_00000.parquet",
        "row_idx": row_idx,
        "row_idx_in_file": row_idx,
        "iso_639_3": "sbd",
        "variety": "maka",
        "license": "odc-by",
        "rights_status": "dataset_license_declared_upstream_provenance_preserved",
        "validation_status": "external_unverified",
        "source_row": {
            "text": text,
            "url": f"https://example.test/{row_idx}",
        },
    }


def test_inspect_file_reports_clean_rows(tmp_path):
    import json

    path = tmp_path / "train.jsonl"
    rows = [_record("premier texte", 0), _record("deuxième texte", 1)]
    path.write_text(
        "\n".join(json.dumps(row, ensure_ascii=False) for row in rows) + "\n",
        encoding="utf-8",
    )

    result = qa.inspect_file(path)
    assert result["total_nonempty_lines"] == 2
    assert result["valid_json_rows"] == 2
    assert result["rows_with_text_field"] == 2
    assert result["rows_with_blank_text"] == 0
    assert result["duplicate_text_rows"] == 0
    assert result["metadata_mismatch_rows"] == 0
    assert result["technical_ok"] is True


def test_inspect_file_detects_blank_text_and_metadata_mismatch(tmp_path):
    import json

    path = tmp_path / "train.jsonl"
    row = _record("", 0)
    row["iso_639_3"] = "stj"
    path.write_text(json.dumps(row, ensure_ascii=False) + "\n", encoding="utf-8")

    result = qa.inspect_file(path)
    assert result["rows_with_blank_text"] == 1
    assert result["metadata_mismatch_rows"] == 1
    assert result["technical_ok"] is False


def test_run_qa_writes_report(tmp_path):
    import json

    input_path = tmp_path / "train.jsonl"
    output_path = tmp_path / "report.json"
    input_path.write_text(json.dumps(_record("texte", 0), ensure_ascii=False) + "\n", encoding="utf-8")

    payload = qa.run_qa(input_path, output_path)
    assert output_path.exists()
    assert payload["result"]["technical_ok"] is True
    assert payload["linguistic_validation"] is False
