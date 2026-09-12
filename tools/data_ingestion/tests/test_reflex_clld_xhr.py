from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
import sys

import pytest


ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

SPEC = spec_from_file_location(
    "reflex_clld_xhr",
    ROOT / "collectors" / "reflex_clld_xhr.py",
)
xhr = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(xhr)


class _FakeResponse:
    def __init__(self, payload, status_code=200):
        self._payload = payload
        self.status_code = status_code
        self.text = ""

    def raise_for_status(self):
        if self.status_code >= 400:
            raise RuntimeError(f"HTTP {self.status_code}")

    def json(self):
        return self._payload


class _FakeSession:
    def __init__(self, total=3, internal_id="L-42"):
        self.headers = {}
        self.calls = []
        self.total = total
        self.internal_id = internal_id
        self.rows = [
            ["form-1", "meaning-1"],
            ["form-2", "meaning-2"],
            ["form-3", "meaning-3"],
        ]

    def get(self, url, params=None, timeout=None, headers=None):
        params = dict(params or {})
        self.calls.append((url, params, timeout, dict(headers or {})))
        language = str(params.get("language") or "")
        if language != self.internal_id:
            return _FakeResponse(
                {
                    "sEcho": str(params.get("sEcho", "1")),
                    "iTotalRecords": 0,
                    "iTotalDisplayRecords": 0,
                    "aaData": [],
                }
            )
        start = int(params.get("iDisplayStart", 0))
        length = int(params.get("iDisplayLength", 1))
        page = self.rows[start : start + length]
        return _FakeResponse(
            {
                "sEcho": str(params.get("sEcho", "1")),
                "iTotalRecords": self.total,
                "iTotalDisplayRecords": self.total,
                "aaData": page,
            }
        )


def _source():
    return {
        "name": "RefLex CLLD",
        "homepage": "https://reflex.clld.huma-num.fr/",
        "license": "CC-BY-NC-SA-4.0",
        "publication_approved": False,
        "training_approved": False,
        "commercial_use_approved": False,
        "endpoints": {
            "languages": "https://reflex.clld.huma-num.fr/languages",
            "languages_csv": "https://reflex.clld.huma-num.fr/languages.csv",
            "values": "https://reflex.clld.huma-num.fr/values",
        },
    }


def _target(expected=3):
    return {
        "iso_639_3": "stj",
        "variety": "matya",
        "configured_glottocode": "maty1235",
        "reflex_glottocode": "maty1235",
        "reflex_language_id": "maty1235",
        "reflex_language_name": "Samo Matya",
        "clld_language_id": "L-42",
        "clld_language_id_resolution": "languages_xhr_glottocode",
        "resolution_status": "resolved",
        "mapping_review_required": False,
        "records_biggest_source": expected,
        "number_of_sources": 1,
    }


def test_resolve_clld_language_id_from_languages_xhr_anchor():
    rows = [
        [
            '<a href="/languages/L-42">Samo Matya</a>',
            "Mande",
            "maty1235",
            "Africa",
        ],
        [
            '<a href="/languages/L-77">Samo Maya</a>',
            "Mande",
            "maya1281",
            "Africa",
        ],
    ]
    target = _target()
    target.pop("clld_language_id")
    target.pop("clld_language_id_resolution")

    resolved = xhr.resolve_clld_language_id(target, rows)

    assert resolved["clld_language_id"] == "L-42"
    assert resolved["clld_language_id_resolution"] == "languages_xhr_glottocode"


def test_probe_target_uses_internal_clld_id_not_glottocode():
    session = _FakeSession(total=3, internal_id="L-42")
    result = xhr.probe_target(_source(), _target(), session=session)

    assert result["reported_count"] == 3
    assert result["expected_count"] == 3
    assert result["filter_verified"] is True
    assert result["first_row_preview"] == ["form-1", "meaning-1"]
    assert result["clld_language_id"] == "L-42"

    _, params, _, headers = session.calls[0]
    assert params["language"] == "L-42"
    assert params["language"] != "maty1235"
    assert params["iDisplayLength"] == "1"
    assert params["__eid__"] == "Values"
    assert headers["X-Requested-With"] == "XMLHttpRequest"


def test_harvest_target_pages_and_preserves_datatable_rows(tmp_path):
    session = _FakeSession(total=3)
    result = xhr.harvest_target(
        _source(),
        _target(),
        session=session,
        output_root=tmp_path,
        page_size=2,
    )

    assert result["rows_written"] == 3
    assert result["clld_language_id"] == "L-42"
    output = Path(result["output"])
    lines = [xhr.json.loads(line) for line in output.read_text(encoding="utf-8").splitlines()]
    assert [item["datatable_row"] for item in lines] == session.rows
    assert all(item["clld_language_id"] == "L-42" for item in lines)
    assert all(item["validation_status"] == "external_unverified" for item in lines)
    assert all(item["commercial_use_approved"] is False for item in lines)
    assert not (tmp_path / "stj" / "values_datatable.jsonl.tmp").exists()


def test_harvest_target_aborts_when_language_filter_count_does_not_match(tmp_path):
    session = _FakeSession(total=927845)

    with pytest.raises(xhr.RefLexXHRError, match="filtre language non vérifié"):
        xhr.harvest_target(
            _source(),
            _target(expected=2764),
            session=session,
            output_root=tmp_path,
            page_size=2,
        )

    assert not (tmp_path / "stj" / "values_datatable.jsonl.tmp").exists()


def test_page_size_is_bounded(tmp_path):
    with pytest.raises(xhr.RefLexXHRError, match="page_size"):
        xhr.harvest_target(
            _source(),
            _target(),
            session=_FakeSession(),
            output_root=tmp_path,
            page_size=1001,
        )
