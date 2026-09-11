from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("enrich_concepts", ROOT / "processors" / "enrich_concepts.py")
assert SPEC and SPEC.loader
enrich = module_from_spec(SPEC)
SPEC.loader.exec_module(enrich)


def test_controlled_mapping_contains_expected_glosses_and_notes():
    mapping, notes, metadata = enrich.load_mapping(ROOT / "config" / "concepts_fr.yaml")

    assert metadata["version"] == 1
    assert mapping["water"] == "eau"
    assert mapping["fire"] == "feu"
    assert mapping["lie"] == "être couché / s'allonger"
    assert mapping["you"] == "tu / vous"
    assert "mentir" in notes["lie"]


def test_enrichment_does_not_promote_source_form_to_standard_san():
    mapping = {"water": "eau"}
    entry = {
        "id": "asjp-test",
        "concept_normalized": "water",
        "concept_fr": None,
        "source_form": "mu",
        "standard_san": None,
        "validation_status": "external_unverified",
    }

    enriched = enrich.enrich_entry(entry, mapping)

    assert enriched["concept_fr"] == "eau"
    assert enriched["concept_fr_source"] == "project_controlled_gloss_v1"
    assert enriched["concept_fr_status"] == "project_controlled_gloss"
    assert enriched["source_form"] == "mu"
    assert enriched["standard_san"] is None
    assert enriched["validation_status"] == "external_unverified"


def test_missing_gloss_is_explicit_and_report_is_partial():
    entries = [
        {"concept_normalized": "water", "source_form": "mu"},
        {"concept_normalized": "unknown-concept", "source_form": "x"},
    ]
    mapping = {"water": "eau"}

    enriched_entries = enrich.enrich_entries(entries, mapping)
    report = enrich.build_report(entries, enriched_entries, mapping, {})

    assert enriched_entries[1]["concept_fr"] is None
    assert enriched_entries[1]["concept_fr_status"] == "missing_gloss"
    assert report["status"] == "partial"
    assert report["totals"]["mapped_concepts"] == 1
    assert report["totals"]["unmapped_concepts"] == 1
    assert report["missing_concepts"] == ["unknown-concept"]
    assert report["training_approved"] is False


def test_current_mapping_has_92_controlled_concepts():
    mapping, _, _ = enrich.load_mapping(ROOT / "config" / "concepts_fr.yaml")

    assert len(mapping) == 92
