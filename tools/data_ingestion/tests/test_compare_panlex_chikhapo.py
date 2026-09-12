from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "compare_panlex_chikhapo",
    ROOT / "processors" / "compare_panlex_chikhapo.py",
)
compare = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(compare)


def test_normalize_form_uses_nfc_casefold_and_whitespace():
    assert compare.normalize_form("  TÉ  ") == "té"
    assert compare.normalize_form("A   B") == "a b"


def test_form_extractors_collect_expected_stj_values():
    panlex_rows = [{"source_row": {"vocab": "Ti"}}]
    stj_eng_rows = [{"source_word": "ti", "target_translations": ["black"]}]
    eng_stj_rows = [{"source_word": "black", "target_translations": ["TI", "foo"]}]
    assert set(compare.panlex_stj_forms(panlex_rows)) == {"ti"}
    assert set(compare.chikhapo_source_stj_forms(stj_eng_rows)) == {"ti"}
    assert set(compare.chikhapo_target_stj_forms(eng_stj_rows)) == {"ti", "foo"}


def test_compare_reports_exact_overlap(tmp_path):
    panlex_path = tmp_path / "panlex.jsonl"
    stj_eng_path = tmp_path / "stj_eng.jsonl"
    eng_stj_path = tmp_path / "eng_stj.jsonl"
    output = tmp_path / "report.json"

    panlex_path.write_text(
        '\n'.join(
            [
                '{"source_row":{"vocab":"Ti"}}',
                '{"source_row":{"vocab":"Mu"}}',
            ]
        ) + '\n',
        encoding="utf-8",
    )
    stj_eng_path.write_text(
        '{"source_word":"ti","target_translations":["black"]}\n',
        encoding="utf-8",
    )
    eng_stj_path.write_text(
        '{"source_word":"water","target_translations":["mu","other"]}\n',
        encoding="utf-8",
    )

    payload = compare.compare(panlex_path, stj_eng_path, eng_stj_path, output)
    summary = payload["summary"]
    assert summary["panlex_unique_stj_forms"] == 2
    assert summary["chikhapo_unique_stj_forms_union"] == 3
    assert summary["panlex_overlap_chikhapo_union"] == 2
    assert summary["panlex_coverage_by_chikhapo_union_pct"] == 100.0
    assert output.exists()


def test_compare_does_not_infer_provenance(tmp_path):
    panlex_path = tmp_path / "panlex.jsonl"
    stj_eng_path = tmp_path / "stj_eng.jsonl"
    eng_stj_path = tmp_path / "eng_stj.jsonl"
    output = tmp_path / "report.json"
    panlex_path.write_text('{"source_row":{"vocab":"a"}}\n', encoding="utf-8")
    stj_eng_path.write_text('{"source_word":"b","target_translations":["x"]}\n', encoding="utf-8")
    eng_stj_path.write_text('{"source_word":"x","target_translations":["c"]}\n', encoding="utf-8")
    payload = compare.compare(panlex_path, stj_eng_path, eng_stj_path, output)
    assert payload["provenance_equivalence_inferred"] is False
    assert payload["linguistic_equivalence_inferred"] is False
