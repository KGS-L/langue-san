from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("asjp_collector", ROOT / "collectors" / "asjp.py")
assert SPEC and SPEC.loader
asjp = module_from_spec(SPEC)
SPEC.loader.exec_module(asjp)


def test_extract_wordlists_filters_supported_iso_and_keeps_provenance():
    languages = [
        {"ID": "SOUTHERN_SAMO_SAN", "Name": "Southern Samo San", "ISO639P3code": "sbd", "Glottocode": "sout2844"},
        {"ID": "SAMO_MATYA_2", "Name": "Samo Matya 2", "ISO639P3code": "stj", "Glottocode": "maty1235"},
        {"ID": "OTHER", "Name": "Other", "ISO639P3code": "eng", "Glottocode": "stan1293"},
    ]
    parameters = [
        {"ID": "1", "Name": "I"},
        {"ID": "2", "Name": "YOU"},
    ]
    forms = [
        {"Language_ID": "SOUTHERN_SAMO_SAN", "Parameter_ID": "1", "Value": "ma"},
        {"Language_ID": "SAMO_MATYA_2", "Parameter_ID": "2", "Value": "i"},
        {"Language_ID": "OTHER", "Parameter_ID": "1", "Value": "I"},
    ]

    rows = asjp.extract_wordlists(languages, parameters, forms)

    assert len(rows) == 2
    assert {row["iso_639_3"] for row in rows} == {"sbd", "stj"}
    assert all(row["source"] == "ASJP" for row in rows)
    assert all(row["license"] == "CC-BY-4.0" for row in rows)
    assert {row["concept"] for row in rows} == {"I", "YOU"}


def test_extract_wordlists_accepts_iso_column_variants():
    languages = [
        {"ID": "MAYA_SAMO", "Name": "Maya Samo", "ISO_639_3": "sym"},
    ]
    parameters = [{"ID": "3", "Name": "WATER"}]
    forms = [{"Language_ID": "MAYA_SAMO", "Parameter_ID": "3", "Form": "ji"}]

    rows = asjp.extract_wordlists(languages, parameters, forms)

    assert len(rows) == 1
    assert rows[0]["iso_639_3"] == "sym"
    assert rows[0]["concept"] == "WATER"
    assert rows[0]["form"] == "ji"
