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


LANGUAGES_CSV = """id,name,family,glottocode,iso_639_3\n10,Southern Samo,Mande,sout2844,sbd\n20,Matya Samo,Mande,maty1235,stj\n30,Maya Samo,Mande,maya1281,sym\n"""
VALUES_CSV = """id,name,description,language_pk\n1,foo,bar,10\n2,baz,qux,10\n"""


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
            totals = {"10": 2200, "20": 2576, "30": 1500}
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


def test_resolve_targets_uses_glottocodes():
    reader = reflex.csv.DictReader(reflex.io.StringIO(LANGUAGES_CSV))
    headers = list(reader.fieldnames or [])
    rows = [dict(row) for row in reader]
    targets = reflex.resolve_targets(_source(), headers, rows)
    assert [item["reflex_language_id"] for item in targets] == ["10", "20", "30"]
    assert [item["iso_639_3"] for item in targets] == ["sbd", "stj", "sym"]


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
        "reflex_language_id": "10",
        "reflex_language_name": "Southern Samo",
        "language_row": {"id": "10", "name": "Southern Samo"},
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
