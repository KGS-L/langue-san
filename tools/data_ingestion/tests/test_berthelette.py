from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path

import pytest


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "berthelette_collector",
    ROOT / "collectors" / "berthelette.py",
)
berthelette = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(berthelette)


class _FakeResponse:
    def __init__(self, content: bytes, status_code: int = 200, url: str = "https://example.test/report.pdf"):
        self.content = content
        self.status_code = status_code
        self.url = url
        self.headers = {"Content-Type": "application/pdf"}

    def raise_for_status(self):
        if self.status_code >= 400:
            raise RuntimeError(f"HTTP {self.status_code}")


class _FakeSession:
    def __init__(self, response: _FakeResponse):
        self.headers = {}
        self.response = response
        self.calls = []

    def get(self, url, timeout=None, allow_redirects=None):
        self.calls.append((url, timeout, allow_redirects))
        return self.response


def _source():
    return {
        "name": "Berthelette 2001 — San/Samo sociolinguistic survey",
        "report_id": "SILESR-2002-005",
        "archive_entry": "https://www.sil.org/resources/archives/8983",
        "publication_entry": "https://www.sil.org/resources/publications/entry/8983",
        "direct_pdf": "https://example.test/report.pdf",
        "expected_pages": 75,
        "license": "CC-BY-NC-SA-4.0 (archive default; verify item/file)",
        "rights_status": "archive_default_noncommercial_pending_pdf_confirmation",
        "publication_approved": False,
        "training_approved": False,
        "commercial_use_approved": False,
    }


def _pdf_bytes():
    return b"%PDF-1.4\n" + (b"x" * 20_000)


def test_probe_direct_download_validates_pdf_and_preserves_provenance():
    session = _FakeSession(_FakeResponse(_pdf_bytes()))
    result = berthelette.probe(_source(), session=session)

    assert result["report_id"] == "SILESR-2002-005"
    assert result["acquisition_method"] == "direct_http"
    assert result["pdf_magic_ok"] is True
    assert result["bytes"] == len(_pdf_bytes())
    assert len(result["sha256"]) == 64
    assert session.calls[0][0] == "https://example.test/report.pdf"


def test_probe_rejects_non_pdf_payload():
    session = _FakeSession(_FakeResponse(b"<html>blocked</html>" + b"x" * 20_000))
    with pytest.raises(berthelette.BertheletteHarvestError, match="n'est pas un PDF"):
        berthelette.probe(_source(), session=session)


def test_download_reports_sil_403_with_manual_fallback():
    session = _FakeSession(_FakeResponse(b"forbidden", status_code=403))
    with pytest.raises(berthelette.BertheletteHarvestError, match="--input-file"):
        berthelette.probe(_source(), session=session)


def test_local_ingest_writes_raw_and_metadata(tmp_path):
    source_pdf = tmp_path / "downloaded.pdf"
    source_pdf.write_bytes(_pdf_bytes())
    output_root = tmp_path / "raw"

    result = berthelette.harvest(
        _source(),
        input_file=source_pdf,
        output_root=output_root,
    )

    raw = output_root / "SILESR2002_005.pdf"
    metadata = output_root / "metadata.json"
    assert raw.read_bytes() == _pdf_bytes()
    assert metadata.exists()
    assert result["acquisition_method"] == "manual_download_then_local_ingest"
    assert result["raw_path"] == str(raw)
    assert result["metadata_path"] == str(metadata)


def test_rejects_too_small_pdf(tmp_path):
    path = tmp_path / "small.pdf"
    path.write_bytes(b"%PDF-1.4\nsmall")
    with pytest.raises(berthelette.BertheletteHarvestError, match="anormalement petit"):
        berthelette.probe(_source(), input_file=path)
