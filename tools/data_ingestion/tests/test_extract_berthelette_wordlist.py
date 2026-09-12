from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from processors import extract_berthelette_wordlist as ext


def test_localities_are_returned_in_source_order_with_aliases():
    line = "Kassoum Toeni Bounou Lankoue"
    assert ext._localities_in_order(line) == ["Kassoum", "Toéni", "Bounou", "Lankoué"]


def test_locality_attached_to_closing_type3_glyph_is_detected():
    assert ext._localities_in_order("/G3FBangassogo") == ["Bangassogo"]
    assert ext._localities_in_order("/G3FToéni") == ["Toéni"]
    assert ext._localities_in_order("/G3FKouy") == ["Kouy"]


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


def test_parse_attached_localities_do_not_leave_dangling_glyphs():
    text = """
012 grand frère
/G3D/G4C/G47/GD6/G49/G57/G50/G46/G23/G50/G23/G3FBangassogo
/G3D/G4C/G47/GD6/G49/G57/G4E/G27/GD6/G50/G23/G3FKouy
/G3D/G4C/G47/GD6/G49/G57/GD6/G4E/G23/G3FKassoum
013 petite soeur
/G3D/G46/G23/G05/G4E/G51/G05/GD6/G50/G23/G06/G3FBounou
"""
    result = ext.parse_page_texts([(41, text)], expected_start_id=12, expected_end_id=13)

    assert result["summary"]["anomaly_count"] == 0
    assert result["summary"]["technical_ok"] is True
    assert [row["locality"] for row in result["rows"]] == [
        "Bangassogo", "Kouy", "Kassoum", "Bounou"
    ]


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


def test_explicit_empty_source_form_is_preserved_without_failing_parser():
    text = """
020 exemple
/G3D/G3F Toma
"""
    result = ext.parse_page_texts([(42, text)], expected_start_id=20, expected_end_id=20)

    assert len(result["rows"]) == 1
    assert result["rows"][0]["raw_legacy_glyphs"] == "/G3D/G3F"
    assert result["rows"][0]["original_form_ipa"] == ""
    assert result["summary"]["source_blank_form_count"] == 1
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
