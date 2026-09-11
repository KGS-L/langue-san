from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("qa_variants", ROOT / "processors" / "qa_variants.py")
assert SPEC and SPEC.loader
qa = module_from_spec(SPEC)
SPEC.loader.exec_module(qa)


def _entry(iso, variety, concept, form, wordlist):
    return {
        "iso_639_3": iso,
        "variety": variety,
        "concept_normalized": concept,
        "source_form": form,
        "source_wordlist": wordlist,
    }


def test_internal_variants_same_wordlist():
    entries = [
        _entry("sbd", "maka", "walk", "to wo", "SOUTHERN_SAMO_SAN"),
        _entry("sbd", "maka", "walk", "to we", "SOUTHERN_SAMO_SAN"),
    ]

    groups = qa.build_variant_groups(entries)

    assert len(groups) == 1
    assert groups[0]["classification"] == "internal_variants_same_wordlist"
    assert groups[0]["forms"] == ["to we", "to wo"]
    assert groups[0]["needs_human_review"] is True
    assert groups[0]["resolution_status"] == "pending"


def test_cross_wordlist_single_each():
    entries = [
        _entry("stj", "matya", "fire", "pesa", "SAMO_MATYA"),
        _entry("stj", "matya", "fire", "kusi", "SAMO_MATYA_2"),
    ]

    groups = qa.build_variant_groups(entries)

    assert len(groups) == 1
    assert groups[0]["classification"] == "cross_wordlist_single_each"


def test_cross_wordlist_with_internal_variants():
    entries = [
        _entry("sym", "maya", "one", "dEnE", "MAYA_SAMO"),
        _entry("sym", "maya", "one", "dEnEnE", "MAYA_SAMO"),
        _entry("sym", "maya", "one", "dununi", "SAMO_MAYA"),
    ]

    groups = qa.build_variant_groups(entries)

    assert len(groups) == 1
    assert groups[0]["classification"] == "cross_wordlist_with_internal_variants"


def test_identical_forms_are_not_flagged_as_variant_group():
    entries = [
        _entry("stj", "matya", "water", "mu", "SAMO_MATYA"),
        _entry("stj", "matya", "water", "mu", "SAMO_MATYA_2"),
    ]

    assert qa.build_variant_groups(entries) == []


def test_summary_counts_iso_and_classification():
    entries = [
        _entry("sbd", "maka", "walk", "to wo", "A"),
        _entry("sbd", "maka", "walk", "to we", "A"),
        _entry("stj", "matya", "fire", "pesa", "A"),
        _entry("stj", "matya", "fire", "kusi", "B"),
    ]

    groups = qa.build_variant_groups(entries)
    summary = qa.build_summary(groups)

    assert summary["variant_group_count"] == 2
    assert summary["by_iso"] == {"sbd": 1, "stj": 1}
    assert summary["by_classification"] == {
        "cross_wordlist_single_each": 1,
        "internal_variants_same_wordlist": 1,
    }
    assert summary["status"] == "human_review_required"
