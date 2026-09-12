from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from processors import extract_berthelette_wordlist as ext


def test_localities_are_returned_in_source_order_with_aliases():
    line = "Kassoum Toeni Bounou Lankoue"
    assert ext._localities_in_order(line) == ["Kassoum", "Toéni", "Bounou", "Lankoué"]


def test_strip_source_brackets_preserves_inner_ipa():
    assert ext._strip_source_brackets("[mōɡūlō]") == "mōɡūlō"
    assert ext._strip_source_brackets("mō") == "mō"


def test_parse_assigns_one_form_to_multiple_localities():
    text = """
012 grand frère
/G3D/G4F/G51/G05/G49/G57/G05/G4E/G51/G05/G3F Lankoué Kiembara
Kassoum Kouy
"""
    result = ext.parse_page_texts([(41, text)], expected_start_id=12, expected_end_id=12)

    rows = result["rows"]
    assert [row["locality"] for row in rows] == ["Lankoué", "Kiembara", "Kassoum", "Kouy"]
    assert all(row["original_form_ipa"] == "mōɡūlō" for row in rows)
    assert result["summary"]["technical_ok"] is True


def test_parse_preserves_multiple_forms_for_same_locality():
    text = """
016 ancien
/G3D/G4F/G51/G3F Lankoué
/G3D/G4F/G57/G3F Lankoué
"""
    result = ext.parse_page_texts([(41, text)], expected_start_id=16, expected_end_id=16)

    rows = result["rows"]
    assert len(rows) == 2
    assert rows[0]["locality"] == rows[1]["locality"] == "Lankoué"
    assert rows[0]["raw_legacy_glyphs"] != rows[1]["raw_legacy_glyphs"]
    assert result["summary"]["multi_form_concept_locality_groups"] == [
        {"concept_id": "016", "locality": "Lankoué", "occurrences": 2}
    ]


def test_parse_accumulates_split_glyph_sequence_until_locality():
    text = """
017 guérisseur
/G3D/G4C/G47
/G05/G3F Bangassogo
"""
    result = ext.parse_page_texts([(41, text)], expected_start_id=17, expected_end_id=17)

    assert len(result["rows"]) == 1
    assert result["rows"][0]["raw_legacy_glyphs"] == "/G3D/G4C/G47/G05/G3F"
    assert result["rows"][0]["locality"] == "Bangassogo"
    assert result["summary"]["technical_ok"] is True


def test_missing_concept_is_reported_and_fails_technical_ok():
    text = """
012 grand frère
/G3D/G4F/G51/G3F Toma
014 petit frère
/G3D/G4F/G57/G3F Toma
"""
    result = ext.parse_page_texts([(41, text)], expected_start_id=12, expected_end_id=14)

    assert result["summary"]["missing_concept_ids"] == ["013"]
    assert result["summary"]["technical_ok"] is False
