from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from processors import diagnose_berthelette_fonts as diag


def test_glyph_regex_detects_legacy_tokens():
    text = "/G3D/G4F/G51 normal /G05"
    assert diag.GLYPH_RE.findall(text) == ["/G3D", "/G4F", "/G51", "/G05"]


def test_name_handles_none_and_values():
    assert diag._name(None) is None
    assert diag._name("/FontA") == "/FontA"


def test_encoding_info_handles_simple_name():
    result = diag._encoding_info({"/Encoding": "/WinAnsiEncoding"})
    assert result["type"] == "/WinAnsiEncoding"
    assert result["differences_count"] == 0


def test_encoding_info_extracts_differences():
    result = diag._encoding_info(
        {
            "/Encoding": {
                "/BaseEncoding": "/WinAnsiEncoding",
                "/Differences": [32, "/space", 65, "/G3D", "/G4F"],
            }
        }
    )
    assert result["base_encoding"] == "/WinAnsiEncoding"
    assert result["differences_count"] == 3
    assert result["differences_sample"] == ["/space", "/G3D", "/G4F"]


def test_font_descriptor_detects_embedded_font():
    result = diag._font_descriptor_info(
        {
            "/FontDescriptor": {
                "/FontName": "/LegacySAN",
                "/FontFile2": object(),
            }
        }
    )
    assert result["font_name"] == "/LegacySAN"
    assert result["embedded"] is True
    assert result["embedded_stream_keys"] == ["/FontFile2"]
