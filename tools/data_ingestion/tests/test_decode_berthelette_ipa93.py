from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from processors import decode_berthelette_ipa93 as dec


def test_glyph_token_to_access_code_uses_confirmed_offset():
    assert dec.glyph_token_to_access_code("/G3D") == 91
    assert dec.glyph_token_to_access_code("/G3F") == 93
    assert dec.glyph_token_to_access_code("/G4F") == 109
    assert dec.glyph_token_to_access_code("/G51") == 111
    assert dec.glyph_token_to_access_code("/G30") == 78


def test_sentinel_unicode_values_match_sil_ipa93():
    assert dec.glyph_token_to_unicode("/G3D") == "["
    assert dec.glyph_token_to_unicode("/G3F") == "]"
    assert dec.glyph_token_to_unicode("/G4F") == "m"
    assert dec.glyph_token_to_unicode("/G51") == "o"
    assert dec.glyph_token_to_unicode("/G49") == "ɡ"
    assert dec.glyph_token_to_unicode("/G30") == "ŋ"
    assert dec.glyph_token_to_unicode("/G23") == "ɑ"


def test_decode_known_berthelette_sequence():
    sequence = "/G3D/G4F/G51/G05/G49/G57/G05/G4E/G51/G05/G3F"
    result = dec.decode_legacy_sequence(sequence)

    assert result["access_codes"] == [91, 109, 111, 35, 103, 117, 35, 108, 111, 35, 93]
    assert result["decoded_unicode"] == "[mo\u0304ɡu\u0304lo\u0304]"
    assert result["unresolved"] == []


def test_decode_sequence_with_eng_alpha_and_grave():
    result = dec.decode_legacy_sequence("/G30/G23/G06")
    assert result["decoded_unicode"] == "ŋɑ\u0300"


def test_regex_stops_before_adjacent_locality_name():
    text = "/G3D/G4C/G47/GD6/G49/G57/G50/G46/G23/G50/G23/G3FBangassogo"
    sequences = dec.SEQUENCE_RE.findall(text)
    assert sequences == [
        "/G3D/G4C/G47/GD6/G49/G57/G50/G46/G23/G50/G23/G3F"
    ]
    assert dec.GLYPH_RE.findall(text)[-1] == "/G3F"


def test_invalid_glyph_token_is_rejected():
    try:
        dec.glyph_token_to_access_code("G3D")
    except dec.BertheletteIPA93DecodeError:
        return
    raise AssertionError("Un token sans préfixe /G aurait dû être rejeté")
