from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "compare_ainsisoisje_asjp",
    ROOT / "processors" / "compare_ainsisoisje_asjp.py",
)
compare = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(compare)


def _asjp_entries():
    return [
        {
            "id": "1",
            "concept_fr": "eau",
            "source_form": "mu",
            "iso_639_3": "stj",
            "variety": "matya",
            "source_wordlist": "SAMO_MATYA_2",
        },
        {
            "id": "2",
            "concept_fr": "chien",
            "source_form": "jiri",
            "iso_639_3": "stj",
            "variety": "matya",
            "source_wordlist": "SAMO_MATYA_2",
        },
        {
            "id": "3",
            "concept_fr": "feu",
            "source_form": "kusi",
            "iso_639_3": "stj",
            "variety": "matya",
            "source_wordlist": "SAMO_MATYA_2",
        },
    ]


def test_exact_concept_and_form_match():
    comparisons = compare.compare_sources(
        [{"french": "Chien", "samo": "jiri"}],
        _asjp_entries(),
    )
    item = comparisons[0]
    assert item["concept_match_type"] == "exact_french_gloss"
    assert item["classification"] == "EXACT_FORM_MATCH"
    assert item["best_iso"] == "stj"


def test_site_only_when_no_concept_match():
    item = compare.compare_sources(
        [{"french": "ordinateur", "samo": "xyz"}],
        _asjp_entries(),
    )[0]
    assert item["classification"] == "SITE_ONLY_OR_UNRESOLVED"
    assert item["best_iso"] is None


def test_same_concept_different_form_is_not_validated():
    item = compare.compare_sources(
        [{"french": "feu", "samo": "pesa"}],
        _asjp_entries(),
    )[0]
    assert item["concept_match_type"] == "exact_french_gloss"
    assert item["classification"] in {
        "FORM_SIMILARITY_CANDIDATE",
        "CONCEPT_MATCH_FORM_DIFFERENT",
    }
    assert item["classification"] != "EXACT_FORM_MATCH"


def test_summary_marks_training_unapproved():
    items = compare.compare_sources(
        [{"french": "eau", "samo": "mu"}],
        _asjp_entries(),
    )
    summary = compare.build_summary(items)
    assert summary["training_approved"] is False
    assert summary["by_classification"]["EXACT_FORM_MATCH"] == 1


def test_deduplicate_site_entries_keeps_one_pair_for_comparison():
    entries = [
        {"french": "Noir", "samo": "Ti", "occurrence_id": "1"},
        {"french": "Noir", "samo": "Ti", "occurrence_id": "2"},
        {"french": "Eau", "samo": "Mu", "occurrence_id": "3"},
    ]
    unique = compare.deduplicate_site_entries(entries)
    assert len(unique) == 2
    assert unique[0]["occurrence_id"] == "1"
    assert unique[1]["occurrence_id"] == "3"


def test_summary_reports_raw_and_unique_site_counts():
    items = compare.compare_sources(
        [{"french": "eau", "samo": "mu"}],
        _asjp_entries(),
    )
    summary = compare.build_summary(items, raw_site_entry_count=2)
    assert summary["site_raw_occurrence_count"] == 2
    assert summary["site_unique_pair_count_compared"] == 1
    assert summary["duplicate_occurrences_excluded_from_comparison"] == 1
