from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
import json


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "qa_huggingface_chikhapo",
    ROOT / "processors" / "qa_huggingface_chikhapo.py",
)
qa = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(qa)


def test_target_values_handles_list_scalar_and_empty():
    assert qa._target_values(["a", "", "b"]) == ["a", "b"]
    assert qa._target_values("x") == ["x"]
    assert qa._target_values(None) == []


def test_inspect_file_counts_valid_rows_and_duplicates(tmp_path):
    path = tmp_path / "eng_stj.jsonl"
    rows = [
        {
            "source_word": "water",
            "target_translations": ["mu"],
            "src_lang": "eng",
            "tgt_lang": "stj",
        },
        {
            "source_word": "fire",
            "target_translations": ["x", "y"],
            "src_lang": "eng",
            "tgt_lang": "stj",
        },
    ]
    path.write_text(
        "\n".join(json.dumps(row) for row in [rows[0], rows[1], rows[0]]) + "\n",
        encoding="utf-8",
    )

    result = qa.inspect_file(path, "eng_stj")
    assert result["total_nonempty_lines"] == 3
    assert result["valid_json_rows"] == 3
    assert result["total_target_translations"] == 4
    assert result["rows_with_multiple_targets"] == 1
    assert result["unique_source_words"] == 2
    assert result["exact_duplicate_rows"] == 1
    assert result["technical_ok"] is True


def test_inspect_file_flags_bad_direction_and_missing_fields(tmp_path):
    path = tmp_path / "stj_eng.jsonl"
    path.write_text(
        json.dumps(
            {
                "source_word": "",
                "target_translations": [],
                "src_lang": "eng",
                "tgt_lang": "stj",
            }
        )
        + "\n",
        encoding="utf-8",
    )

    result = qa.inspect_file(path, "stj_eng")
    assert result["missing_source_word_rows"] == 1
    assert result["missing_target_translations_rows"] == 1
    assert result["source_language_mismatch_rows"] == 1
    assert result["target_language_mismatch_rows"] == 1
    assert result["technical_ok"] is False


def test_run_qa_aggregates_two_configs(tmp_path):
    raw_root = tmp_path / "raw"
    for config, src, tgt in [("eng_stj", "eng", "stj"), ("stj_eng", "stj", "eng")]:
        folder = raw_root / config
        folder.mkdir(parents=True)
        (folder / f"{config}.jsonl").write_text(
            json.dumps(
                {
                    "source_word": "a",
                    "target_translations": ["b"],
                    "src_lang": src,
                    "tgt_lang": tgt,
                }
            )
            + "\n",
            encoding="utf-8",
        )

    output = tmp_path / "qa.json"
    payload = qa.run_qa(raw_root, output)
    assert payload["summary"]["total_rows"] == 2
    assert payload["summary"]["all_technical_ok"] is True
    assert output.exists()
