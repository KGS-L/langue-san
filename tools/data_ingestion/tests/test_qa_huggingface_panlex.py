from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "qa_huggingface_panlex",
    ROOT / "processors" / "qa_huggingface_panlex.py",
)
qa = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(qa)


def _write_row(path: Path, iso: str, variety: str, vocab: str, var_code: str = "x"):
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(
        (
            '{"iso_639_3":"%s","variety":"%s","source_row":'
            '{"vocab":"%s","639-3":"%s","var_code":"%s"}}\n'
        ) % (iso, variety, vocab, iso, var_code),
        encoding="utf-8",
    )


def test_inspect_file_reports_clean_panlex_row(tmp_path):
    path = tmp_path / "sbd" / "train.jsonl"
    _write_row(path, "sbd", "maka", "demo", "sbd-001")
    result = qa.inspect_file(path, "sbd", "maka")
    assert result["total_nonempty_lines"] == 1
    assert result["unique_vocab"] == 1
    assert result["unique_var_codes"] == 1
    assert result["technical_ok"] is True


def test_inspect_file_detects_source_iso_mismatch(tmp_path):
    path = tmp_path / "stj" / "train.jsonl"
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(
        '{"iso_639_3":"stj","variety":"matya","source_row":{"vocab":"demo","639-3":"sbd"}}\n',
        encoding="utf-8",
    )
    result = qa.inspect_file(path, "stj", "matya")
    assert result["source_iso_mismatch_rows"] == 1
    assert result["technical_ok"] is False


def test_inspect_file_counts_duplicate_vocab_separately_from_exact_duplicates(tmp_path):
    path = tmp_path / "sym" / "train.jsonl"
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(
        '\n'.join(
            [
                '{"iso_639_3":"sym","variety":"maya","source_row":{"vocab":"mu","639-3":"sym","var_code":"a"}}',
                '{"iso_639_3":"sym","variety":"maya","source_row":{"vocab":"mu","639-3":"sym","var_code":"b"}}',
            ]
        ) + '\n',
        encoding="utf-8",
    )
    result = qa.inspect_file(path, "sym", "maya")
    assert result["duplicate_vocab_rows"] == 1
    assert result["exact_duplicate_rows"] == 0
    assert result["technical_ok"] is True
