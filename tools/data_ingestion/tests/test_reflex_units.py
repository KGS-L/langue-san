from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
import sys

import pytest


ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

SPEC = spec_from_file_location(
    "reflex_units",
    ROOT / "collectors" / "reflex_units.py",
)
units = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(units)


class _FakeResponse:
    def __init__(self, *, text="", content=None, status_code=200):
        self.text = text
        self.content = content if content is not None else text.encode("utf-8")
        self.status_code = status_code

    def raise_for_status(self):
        if self.status_code >= 400:
            raise RuntimeError(f"HTTP {self.status_code}")


class _FakeSession:
    def __init__(self, csv_text="form,meaning\nfoo,bar\nbaz,qux\n"):
        self.headers = {}
        self.csv_text = csv_text
        self.calls = []

    def get(self, url, timeout=None, headers=None, params=None):
        self.calls.append((url, timeout, headers, params))
        return _FakeResponse(text=self.csv_text)


def _source():
    return {
        "name": "RefLex CLLD",
        "homepage": "https://reflex.clld.huma-num.fr/",
        "license": "CC-BY-NC-SA-4.0",
        "publication_approved": False,
        "training_approved": False,
        "commercial_use_approved": False,
    }


def _target():
    return {
        "iso_639_3": "stj",
        "variety": "matya",
        "reflex_language_name": "Samo Matya",
        "reflex_glottocode": "maty1235",
        "clld_language_id": "1472",
    }


def test_csv_url_preserves_filters():
    url = units._csv_url(
        "https://reflex.clld.huma-num.fr/units?filterLanguage=562&filterSource=&filterReference="
    )
    assert url == (
        "https://reflex.clld.huma-num.fr/units.csv?"
        "filterLanguage=562&filterSource=&filterReference="
    )


def test_discover_units_target_selects_units_probe(monkeypatch):
    monkeypatch.setattr(
        units.detail,
        "diagnose_target",
        lambda source, target, session: {
            "detail_url": "https://reflex.clld.huma-num.fr/languages/1472",
            "ajax_probes": [
                {
                    "ajax_source": "https://reflex.clld.huma-num.fr/contributions?filterLanguage=562&filterReference=",
                    "json": True,
                    "reported_count": 1,
                },
                {
                    "ajax_source": "https://reflex.clld.huma-num.fr/units?filterLanguage=562&filterSource=&filterReference=",
                    "json": True,
                    "reported_count": 2743,
                    "first_row": ["-bra₂", "NOM"],
                    "request_params": {"filterLanguage": "562"},
                },
            ],
        },
    )

    result = units.discover_units_target(_source(), _target(), session=_FakeSession())

    assert result["units_reported_count"] == 2743
    assert result["units_csv_url"].startswith("https://reflex.clld.huma-num.fr/units.csv?")
    assert "filterLanguage=562" in result["units_csv_url"]


def test_harvest_target_writes_exact_raw_and_metadata(monkeypatch, tmp_path):
    monkeypatch.setattr(
        units,
        "discover_units_target",
        lambda source, target, session: {
            **target,
            "detail_url": "https://reflex.clld.huma-num.fr/languages/1472",
            "units_ajax_source": "https://reflex.clld.huma-num.fr/units?filterLanguage=562&filterSource=&filterReference=",
            "units_csv_url": "https://reflex.clld.huma-num.fr/units.csv?filterLanguage=562&filterSource=&filterReference=",
            "units_reported_count": 2,
        },
    )
    csv_text = "form,meaning\nfoo,bar\nbaz,qux\n"
    session = _FakeSession(csv_text=csv_text)

    result = units.harvest_target(
        _source(),
        _target(),
        session=session,
        output_root=tmp_path,
    )

    raw = tmp_path / "stj" / "units.csv"
    assert raw.read_text(encoding="utf-8") == csv_text
    assert result["csv_rows"] == 2
    assert result["csv_headers"] == ["form", "meaning"]
    assert (tmp_path / "stj" / "units_metadata.json").exists()


def test_harvest_target_rejects_incomplete_csv(monkeypatch, tmp_path):
    monkeypatch.setattr(
        units,
        "discover_units_target",
        lambda source, target, session: {
            **target,
            "units_ajax_source": "https://reflex.clld.huma-num.fr/units?filterLanguage=562",
            "units_csv_url": "https://reflex.clld.huma-num.fr/units.csv?filterLanguage=562",
            "units_reported_count": 3,
        },
    )

    with pytest.raises(units.RefLexUnitsError, match="XHR=3, CSV=2"):
        units.harvest_target(
            _source(),
            _target(),
            session=_FakeSession(),
            output_root=tmp_path,
        )


def test_discover_units_target_rejects_over_export_limit(monkeypatch):
    monkeypatch.setattr(
        units.detail,
        "diagnose_target",
        lambda source, target, session: {
            "detail_url": "https://reflex.clld.huma-num.fr/languages/1472",
            "ajax_probes": [
                {
                    "ajax_source": "https://reflex.clld.huma-num.fr/units?filterLanguage=562",
                    "json": True,
                    "reported_count": 10001,
                }
            ],
        },
    )

    with pytest.raises(units.RefLexUnitsError, match="limite d'export"):
        units.discover_units_target(_source(), _target(), session=_FakeSession())
