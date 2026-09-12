from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "inspect_berthelette_pdf",
    ROOT / "processors" / "inspect_berthelette_pdf.py",
)
mod = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(mod)


def test_normalise_handles_accents_and_case():
    assert mod._normalise("Toéni MAYA") == "toeni maya"


def test_find_terms_detects_localities_and_lexical_terms():
    text = "Appendix B contains the word list collected in Toéni and Toma."
    lexical = mod._find_terms(text, mod.LEXICAL_TERMS)
    localities = mod._find_terms(text, mod.LOCALITY_TERMS)

    assert "appendix" in [item.casefold() for item in lexical]
    assert "word list" in [item.casefold() for item in lexical]
    assert "Toéni" in localities or "Toeni" in localities
    assert "Toma" in localities


def test_find_terms_does_not_invent_absent_places():
    text = "This section describes survey methodology in Burkina Faso."
    localities = mod._find_terms(text, mod.LOCALITY_TERMS)
    assert localities == []
