from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("review_sheet", ROOT / "processors" / "build_review_sheet.py")
assert SPEC and SPEC.loader
review = module_from_spec(SPEC)
SPEC.loader.exec_module(review)


def test_build_review_items_groups_by_iso_and_concept():
    entries = [
        {
            "iso_639_3": "stj",
            "variety": "matya",
            "concept_normalized": "fire",
            "concept_fr": "feu",
            "concept_fr_note": None,
            "source_wordlist": "SAMO_MATYA",
            "source_form": "pesa",
        },
        {
            "iso_639_3": "stj",
            "variety": "matya",
            "concept_normalized": "fire",
            "concept_fr": "feu",
            "concept_fr_note": None,
            "source_wordlist": "SAMO_MATYA_2",
            "source_form": "kusi",
        },
        {
            "iso_639_3": "sbd",
            "variety": "maka",
            "concept_normalized": "water",
            "concept_fr": "eau",
            "concept_fr_note": None,
            "source_wordlist": "SOUTHERN_SAMO",
            "source_form": "mu",
        },
    ]
    variants = {
        ("stj", "fire"): {
            "classification": "cross_wordlist_single_each",
        }
    }

    items = review.build_review_items(entries, variants)

    assert len(items) == 2
    fire = next(item for item in items if item["concept_en"] == "fire")
    water = next(item for item in items if item["concept_en"] == "water")

    assert fire["concept_fr"] == "feu"
    assert fire["source_forms"] == ["kusi", "pesa"]
    assert fire["variant_classification"] == "cross_wordlist_single_each"
    assert fire["review_priority"] == "medium"
    assert fire["standard_san"] is None
    assert fire["decision_status"] == "pending"
    assert fire["training_approved"] is False

    assert water["source_forms"] == ["mu"]
    assert water["variant_classification"] == "single_form"
    assert water["review_priority"] == "normal"


def test_high_priority_is_reserved_for_complex_cross_wordlist_variants():
    entries = [
        {
            "iso_639_3": "sbd",
            "variety": "maka",
            "concept_normalized": "die",
            "concept_fr": "mourir",
            "source_wordlist": "SOUTHERN_SAMO",
            "source_form": "ga",
        },
        {
            "iso_639_3": "sbd",
            "variety": "maka",
            "concept_normalized": "die",
            "concept_fr": "mourir",
            "source_wordlist": "SOUTHERN_SAMO_SAN",
            "source_form": "fo kuri",
        },
        {
            "iso_639_3": "sbd",
            "variety": "maka",
            "concept_normalized": "die",
            "concept_fr": "mourir",
            "source_wordlist": "SOUTHERN_SAMO_SAN",
            "source_form": "fo kuru",
        },
    ]
    variants = {
        ("sbd", "die"): {
            "classification": "cross_wordlist_with_internal_variants",
        }
    }

    item = review.build_review_items(entries, variants)[0]

    assert item["review_priority"] == "high"
    assert item["source_map"] == {
        "SOUTHERN_SAMO": ["ga"],
        "SOUTHERN_SAMO_SAN": ["fo kuri", "fo kuru"],
    }


def test_summary_counts_review_items():
    items = [
        {
            "iso_639_3": "sbd",
            "variant_classification": "single_form",
            "review_priority": "normal",
            "decision_status": "pending",
        },
        {
            "iso_639_3": "stj",
            "variant_classification": "cross_wordlist_single_each",
            "review_priority": "medium",
            "decision_status": "pending",
        },
    ]

    summary = review.build_summary(items)

    assert summary["review_items"] == 2
    assert summary["by_iso"] == {"sbd": 1, "stj": 1}
    assert summary["by_classification"] == {
        "cross_wordlist_single_each": 1,
        "single_form": 1,
    }
    assert summary["by_priority"] == {"medium": 1, "normal": 1}
    assert summary["pending_items"] == 2
    assert summary["training_approved"] is False
