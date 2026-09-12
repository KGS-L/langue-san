from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "reflex_clld",
    ROOT / "collectors" / "reflex_clld.py",
)
reflex = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(reflex)


# Reproduit la forme observée sur le vrai export RefLex en septembre 2026 :
# aucun ID interne ni code ISO n'est exporté, mais le Glottocode l'est.
LANGUAGES_CSV = """Name,Family,Glottocode,Macroarea,Number of records in biggest source,Number of sources,Latitude,Longitude
Southern Samo,Mande,sout2844,Africa,2200,2,12.0,-2.0
Matya Samo,Mande,maty1235,Africa,2576,2,13.0,-3.0
Maya Samo,Mande,maya1281,Africa,1500,1,13.1,-3.1
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
    def __init__(self):
        self.headers = {}
        self.calls = []

    def get(self, url, params=None, timeout=None, headers=None):
        self.calls.append((url, params, timeout, headers))
        if url.endswith("languages.csv"):
            return _FakeResponse(text=LANGUAGES_CSV)
        if url.endswith("/values"):
            language = str((params or {}).get("language"))
            totals = {"sout2844": 2200, "maty1235": 2576, "maya1281": 1500}
            return _FakeResponse(
                payload={
                    "iTotalDisplayRecords": totals[language],
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
            {"variety": "maka", "iso_639_3": "sbd", "glottocode": "sout2844"},
            {"variety": "matya", "iso_639_3": "stj", "glottocode": "maty1235"},
            {"variety": "maya", "iso_639_3": "sym", "glottocode": "maya1281"},
        ],
    }


def test_resolve_targets_uses_glottocodes_when_id_is_not_exported():
    reader = reflex.csv.DictReader(reflex.io.StringIO(LANGUAGES_CSV))
    headers = list(reader.fieldnames or [])
    rows = [dict(row) for row in reader]
    targets = reflex.resolve_targets(_source(), headers, rows)

    assert [item["reflex_language_id"] for item in targets] == [
        "sout2844",
        "maty1235",
        "maya1281",
    ]
    assert [item["iso_639_3"] for item in targets] == ["sbd", "stj", "sym"]
    assert all(
        str(item["reflex_language_id_resolution"]).startswith("glottocode_fallback:")
        for item in targets
    )


def test_resolve_targets_still_prefers_explicit_id_when_available():
    text = "id,name,glottocode\n10,Southern Samo,sout2844\n20,Matya Samo,maty1235\n30,Maya Samo,maya1281\n"
    reader = reflex.csv.DictReader(reflex.io.StringIO(text))
    headers = list(reader.fieldnames or [])
    rows = [dict(row) for row in reader]
    targets = reflex.resolve_targets(_source(), headers, rows)

    assert [item["reflex_language_id"] for item in targets] == ["10", "20", "30"]
    assert all(
        str(item["reflex_language_id_resolution"]).startswith("exported_column:")
        for item in targets
    )


def test_probe_reports_counts_without_full_csv_download():
    session = _FakeSession()
    result = reflex.probe(_source(), session=session)
    counts = {item["iso_639_3"]: item["num_records_reported"] for item in result["results"]}
    assert counts == {"sbd": 2200, "stj": 2576, "sym": 1500}
    assert result["commercial_use_approved"] is False
    assert not any(call[0].endswith("values.csv") for call in session.calls)


def test_harvest_language_preserves_csv_and_provenance(tmp_path):
    session = _FakeSession()
    target = {
        "iso_639_3": "sbd",
        "variety": "maka",
        "glottocode": "sout2844",
        "reflex_language_id": "sout2844",
        "reflex_language_id_resolution": "glottocode_fallback:Glottocode",
        "reflex_language_name": "Southern Samo",
        "language_row": {"Name": "Southern Samo", "Glottocode": "sout2844"},
    }
    result = reflex.harvest_language(
        _source(),
        target,
        session=session,
        output_root=tmp_path,
    )
    assert result["rows_written"] == 2
    assert result["reflex_language_id_resolution"] == "glottocode_fallback:Glottocode"
    output = Path(result["output"])
    assert output.read_text(encoding="utf-8") == VALUES_CSV
    assert (tmp_path / "sbd" / "language.json").exists()
