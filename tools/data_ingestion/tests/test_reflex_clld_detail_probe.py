from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
import sys


ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

SPEC = spec_from_file_location(
    "reflex_clld_detail_probe",
    ROOT / "collectors" / "reflex_clld_detail_probe.py",
)
probe = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(probe)


class _FakeResponse:
    def __init__(self, *, text="", payload=None, status_code=200, headers=None):
        self.text = text
        self._payload = payload
        self.status_code = status_code
        self.headers = headers or {}

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
        self.calls.append((url, dict(params or {}), dict(headers or {})))
        if url.endswith("/languages/1472"):
            return _FakeResponse(
                text='''
                <html><script>
                CLLD.DataTable.init("Entries", null, {
                  "sAjaxSource": "/entries?language=1472&contribution=88"
                });
                </script>
                <a href="/downloads/matya.csv">CSV</a>
                </html>
                '''
            )
        if url.endswith("/entries"):
            return _FakeResponse(
                payload={
                    "sEcho": "1",
                    "iTotalRecords": 2764,
                    "iTotalDisplayRecords": 2764,
                    "aaData": [["foo", "bar"]],
                }
            )
        raise AssertionError(f"unexpected URL: {url}")


def _source():
    return {
        "name": "RefLex CLLD",
        "endpoints": {
            "languages": "https://reflex.example/languages",
        },
    }


def _target():
    return {
        "iso_639_3": "stj",
        "variety": "matya",
        "reflex_language_name": "Samo Matya",
        "reflex_glottocode": "maty1235",
        "clld_language_id": "1472",
    }


def test_extract_ajax_sources_and_download_links():
    page = '''
    <script>{"sAjaxSource":"/values?language=1472&amp;source=9"}</script>
    <a href="/export/matya.csv">CSV</a>
    '''
    ajax = probe.extract_ajax_sources(page, detail_url="https://reflex.example/languages/1472")
    downloads = probe.extract_download_links(page, detail_url="https://reflex.example/languages/1472")

    assert ajax == ["https://reflex.example/values?language=1472&source=9"]
    assert downloads == ["https://reflex.example/export/matya.csv"]


def test_probe_ajax_source_preserves_real_page_query_params():
    session = _FakeSession()
    result = probe.probe_ajax_source(
        "https://reflex.example/entries?language=1472&contribution=88",
        session=session,
    )

    assert result["reported_count"] == 2764
    assert result["rows_returned"] == 1
    assert result["request_params"]["language"] == "1472"
    assert result["request_params"]["contribution"] == "88"
    assert result["request_params"]["iDisplayLength"] == "1"


def test_diagnose_target_uses_detail_page_transport():
    session = _FakeSession()
    result = probe.diagnose_target(_source(), _target(), session=session)

    assert result["detail_url"] == "https://reflex.example/languages/1472"
    assert result["ajax_sources"] == [
        "https://reflex.example/entries?language=1472&contribution=88"
    ]
    assert result["ajax_probes"][0]["reported_count"] == 2764
    assert result["download_links"] == ["https://reflex.example/downloads/matya.csv"]
