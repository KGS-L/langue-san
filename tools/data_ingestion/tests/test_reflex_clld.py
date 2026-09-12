from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path

import pytest


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "reflex_clld",
    ROOT / "collectors" / "reflex_clld.py",
)
reflex = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(reflex)


LANGUAGES_CSV = """Name,Family,Glottocode,Macroarea,Number of records in biggest source,Number of sources,Latitude,Longitude
Southern Samo,Mande,sout2844,Africa,2200,2,12.0,-2.0
Matya Samo,Mande,maty1235,Africa,2576,2,13.0,-3.0
Maya Samo,Mande,maya1281,Africa,1500,1,13.1,-3.1
"""

# Le nom contient volontairement une virgule sur la première ligne. Comme dans
# tout CSV valide, la valeur est entourée de guillemets afin que DictReader ne
# décale pas Family/Glottocode/Macroarea d'une colonne.
LANGUAGES_CSV_CHANGED_GLOTTO = """Name,Family,Glottocode,Macroarea,Number of records in biggest source,Number of sources,Latitude,Longitude
"Samo, Southern",Mande,olds1111,Africa,2200,2,12.0,-2.0
Samo Matya,Mande,oldm2222,Africa,2576,2,13.0,-3.0
Samo Mayaa,Mande,oldy3333,Africa,1500,1,13.1,-3.1
"""

LANGUAGES_CSV_UNRESOLVED = """Name,Family,Glottocode,Macroarea,Number of records in biggest source,Number of sources,Latitude,Longitude
Samo North,Mande,nort0001,Africa,100,1,12.0,-2.0
Other Language,Mande,othe0001,Africa,50,1,13.0,-3.0
"""

VALUES_CSV = """id,name,description,language_pk
1,foo,bar,sout2844
2,baz,qux,sout2844
"""


class _FakeResponse:
    def __init__(self, *, text="", payload=None, status_code=200):
        self.text = text
        self.content = text.encode("utf-8")
        self._payload = payload
        self.status_code = status_code

    def raise_for_status(self):
        if self.status_code >= 400:
            raise RuntimeError(f"HTTP {self.status_code}")

    def json(self):
        if self._payload is None:
            raise ValueError("not json")
        return self._payload


class _FakeSession:
    def __init__(self, languages_csv=LANGUAGES_CSV, totals=None):
        self.headers = {}
        self.calls = []
        self.languages_csv = languages_csv
        self.totals = totals or {"sout2844": 2200, "maty1235": 2576, "maya1281": 1500}

    def get(self, url, params=None, timeout=None, headers=None):
        self.calls.append((url, params, timeout, headers))
        if url.endswith("languages.csv"):
            return _FakeResponse(text=self.languages_csv)
        if url.endswith("/values"):
            language = str((params or {}).get("language"))
            return _FakeResponse(
                payload={
                    "iTotalDisplayRecords": self.totals[language],
                    "aaData": [["demo"]],
                }
            )
        if url.endswith("values.csv"):
            return _FakeResponse(text=VALUES_CSV)
        raise AssertionError(url)


def _source():
    return {
        "name": "RefLex CLLD",
        "homepage": "https://reflex.clld.huma-num.fr/",
        "version": "1.0",
        "doi": "10.34847/nkl.e3cbkjzm",
        "license": "CC-BY-NC-SA-4.0",
        "status": "approved_for_noncommercial_research_ingestion",
        "commercial_use_approved": False,
        "publication_approved": False,
        "training_approved": False,
        "endpoints": {
            "languages_csv": "https://reflex.clld.huma-num.fr/languages.csv",
            "values": "https://reflex.clld.huma-num.fr/values",
            "values_csv": "https://reflex.clld.huma-num.fr/values.csv",
        },
        "targets": [
            {
                "variety": "maka",
                "iso_639_3": "sbd",
                "glottocode": "sout2844",
                "name_aliases": ["Southern Samo", "Samo Southern", "San Maka"],
            },
            {
                "variety": "matya",
                "iso_639_3": "stj",
                "glottocode": "maty1235",
                "name_aliases": ["Matya Samo", "Samo Matya", "San Matya"],
            },
            {
                "variety": "maya",
                "iso_639_3": "sym",
                "glottocode": "maya1281",
                "name_aliases": ["Maya Samo", "Samo Maya", "Mayaa Samo", "Samo Mayaa"],
            },
        ],
    }


def _read_languages(text):
    reader = reflex.csv.DictReader(reflex.io.StringIO(text))
    headers = list(reader.fieldnames or [])
    rows = [dict(row) for row in reader]
    return headers, rows


def test_resolve_targets_uses_configured_glottocodes_when_present():
    headers, rows = _read_languages(LANGUAGES_CSV)
    targets = reflex.resolve_targets(_source(), headers, rows)

    assert [item["reflex_language_id"] for item in targets] == [
        "sout2844",
        "maty1235",
        "maya1281",
    ]
    assert all(item["resolution_status"] == "resolved" for item in targets)
    assert all(item["mapping_review_required"] is False for item in targets)


def test_resolve_targets_can_probe_by_exact_name_alias_when_reflex_glottocode_differs():
    headers, rows = _read_languages(LANGUAGES_CSV_CHANGED_GLOTTO)
    targets = reflex.resolve_targets(_source(), headers, rows)

    assert [item["reflex_language_id"] for item in targets] == [
        "olds1111",
        "oldm2222",
        "oldy3333",
    ]
    assert all(item["resolution_method"] == "name_alias_probe_fallback" for item in targets)
    assert all(item["mapping_review_required"] is True for item in targets)
    assert targets[0]["configured_glottocode"] == "sout2844"
    assert targets[0]["reflex_glottocode"] == "olds1111"


def test_unresolved_targets_return_diagnostic_candidates_instead_of_aborting():
    headers, rows = _read_languages(LANGUAGES_CSV_UNRESOLVED)
    targets = reflex.resolve_targets(_source(), headers, rows)

    assert all(item["resolution_status"] == "unresolved" for item in targets)
    sbd = targets[0]
    assert sbd["diagnostic_candidates"]
    assert sbd["diagnostic_candidates"][0]["name"] == "Samo North"


def test_probe_reports_counts_without_full_csv_download():
    session = _FakeSession()
    result = reflex.probe(_source(), session=session)
    counts = {item["iso_639_3"]: item["num_records_reported"] for item in result["results"]}

    assert counts == {"sbd": 2200, "stj": 2576, "sym": 1500}
    assert all(item["probe_status"] == "ok" for item in result["results"])
    assert result["commercial_use_approved"] is False
    assert not any(call[0].endswith("values.csv") for call in session.calls)


def test_probe_uses_observed_reflex_glottocode_for_alias_fallback():
    totals = {"olds1111": 2200, "oldm2222": 2576, "oldy3333": 1500}
    session = _FakeSession(LANGUAGES_CSV_CHANGED_GLOTTO, totals=totals)
    result = reflex.probe(_source(), session=session)

    assert [item["probe_status"] for item in result["results"]] == ["ok", "ok", "ok"]
    assert result["results"][0]["reflex_glottocode"] == "olds1111"
    assert result["results"][0]["mapping_review_required"] is True


def test_harvest_is_blocked_until_alias_mapping_is_confirmed():
    totals = {"olds1111": 2200, "oldm2222": 2576, "oldy3333": 1500}
    session = _FakeSession(LANGUAGES_CSV_CHANGED_GLOTTO, totals=totals)

    with pytest.raises(reflex.RefLexHarvestError, match="mapping langue"):
        reflex.harvest(_source(), session=session)


def test_harvest_language_preserves_csv_and_provenance(tmp_path):
    session = _FakeSession()
    target = {
        "iso_639_3": "sbd",
        "variety": "maka",
        "configured_glottocode": "sout2844",
        "reflex_glottocode": "sout2844",
        "glottocode": "sout2844",
        "reflex_language_id": "sout2844",
        "reflex_language_id_resolution": "glottocode_fallback:Glottocode",
        "reflex_language_name": "Southern Samo",
        "resolution_method": "configured_glottocode_or_iso",
        "resolution_status": "resolved",
        "mapping_review_required": False,
        "language_row": {"Name": "Southern Samo", "Glottocode": "sout2844"},
    }
    result = reflex.harvest_language(
        _source(),
        target,
        session=session,
        output_root=tmp_path,
    )

    assert result["rows_written"] == 2
    output = Path(result["output"])
    assert output.read_text(encoding="utf-8") == VALUES_CSV
    assert (tmp_path / "sbd" / "language.json").exists()
