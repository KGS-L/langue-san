from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "triage_huggingface_inventory",
    ROOT / "processors" / "triage_huggingface_inventory.py",
)
triage = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(triage)


def test_permissive_license_bucket():
    assert triage.license_bucket("cc-by-4.0") == "declared_reusable_review_required"
    assert triage.license_bucket("odc-by") == "declared_reusable_review_required"


def test_noncommercial_license_bucket():
    assert (
        triage.license_bucket("cc-by-nc-sa-4.0")
        == "restricted_noncommercial_review_required"
    )


def test_translation_repo_gets_high_priority_signal():
    item = {
        "repo_id": "example/san-parallel-translation",
        "matched_iso_codes": ["sbd", "stj", "sym"],
        "matched_varieties": ["maka", "matya", "maya"],
        "license": "cc-by-4.0",
        "gated": False,
        "private": False,
        "tasks": ["translation"],
        "modalities": ["text"],
        "tags": ["task_categories:translation", "modality:text"],
    }
    result = triage.triage_dataset(item)
    assert result["priority"] == "high"
    assert result["score"] >= 6
    assert "translation_task" in result["reasons"]
    assert result["training_approved"] is False


def test_frequency_dataset_is_deprioritized_but_not_rejected():
    item = {
        "repo_id": "lgi2p/finefreq",
        "matched_iso_codes": ["sbd"],
        "matched_varieties": ["maka"],
        "license": "cc-by-4.0",
        "gated": False,
        "private": False,
        "tasks": [],
        "modalities": [],
        "description": "character frequency statistics",
        "tags": [],
    }
    result = triage.triage_dataset(item)
    assert result["priority"] == "low"
    assert any(reason.startswith("low_value_signal:") for reason in result["reasons"])
    assert result["review_status"] == "manual_dataset_card_review_required"


def test_other_license_needs_manual_review():
    result = triage.triage_dataset(
        {
            "repo_id": "example/unknown-source",
            "matched_iso_codes": ["sym"],
            "license": "other",
            "gated": False,
            "private": False,
        }
    )
    assert result["license_bucket"] == "other_manual_review_required"
    assert "license_needs_manual_review" in result["reasons"]


def test_inventory_sorting_puts_high_before_low():
    rows = triage.triage_inventory(
        [
            {
                "repo_id": "example/frequencies",
                "matched_iso_codes": ["sbd"],
                "license": "other",
                "description": "character frequency statistics",
            },
            {
                "repo_id": "example/parallel-corpus",
                "matched_iso_codes": ["sbd", "stj", "sym"],
                "license": "cc0-1.0",
                "tasks": ["translation"],
                "modalities": ["text"],
            },
        ]
    )
    assert rows[0]["repo_id"] == "example/parallel-corpus"
    assert rows[-1]["repo_id"] == "example/frequencies"
