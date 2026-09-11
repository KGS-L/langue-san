from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path
from types import SimpleNamespace


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_inventory",
    ROOT / "collectors" / "huggingface_inventory.py",
)
hf_inventory = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(hf_inventory)


class FakeApi:
    def __init__(self, by_filter):
        self.by_filter = by_filter
        self.calls = []

    def list_datasets(self, **kwargs):
        self.calls.append(kwargs)
        return self.by_filter.get(kwargs["filter"], [])


def test_normalize_dataset_info_extracts_metadata_from_tags():
    info = SimpleNamespace(
        id="example/dataset",
        tags=[
            "language:sbd",
            "license:cc-by-4.0",
            "modality:text",
            "format:parquet",
            "task_categories:translation",
            "size_categories:n<1K",
        ],
        gated=False,
        private=False,
        downloads=12,
        likes=3,
        last_modified=None,
        sha="abc123",
        card_data={},
    )

    item = hf_inventory.normalize_dataset_info(info, "sbd")

    assert item["repo_id"] == "example/dataset"
    assert item["matched_iso_codes"] == ["sbd"]
    assert item["matched_varieties"] == ["maka"]
    assert item["license"] == "cc-by-4.0"
    assert item["languages"] == ["sbd"]
    assert item["modalities"] == ["text"]
    assert item["formats"] == ["parquet"]
    assert item["tasks"] == ["translation"]
    assert item["content_downloaded"] is False
    assert item["training_approved"] is False


def test_inventory_merges_dataset_returned_for_multiple_iso_codes():
    shared = SimpleNamespace(
        id="org/shared",
        tags=["language:sbd", "language:stj", "license:apache-2.0"],
        gated=False,
        private=False,
        downloads=10,
        likes=1,
        card_data={},
    )
    maya = SimpleNamespace(
        id="org/maya",
        tags=["language:sym"],
        gated=False,
        private=False,
        downloads=2,
        likes=0,
        card_data={"license": "cc-by-4.0"},
    )
    api = FakeApi(
        {
            "language:sbd": [shared],
            "language:stj": [shared],
            "language:sym": [maya],
        }
    )

    datasets, counts = hf_inventory.inventory_datasets(api=api)

    assert counts == {"sbd": 1, "stj": 1, "sym": 1}
    assert len(datasets) == 2
    by_id = {item["repo_id"]: item for item in datasets}
    assert by_id["org/shared"]["matched_iso_codes"] == ["sbd", "stj"]
    assert by_id["org/shared"]["matched_varieties"] == ["maka", "matya"]
    assert by_id["org/maya"]["license"] == "cc-by-4.0"


def test_unknown_license_requires_rights_review():
    info = SimpleNamespace(
        id="example/no-license",
        tags=["language:sym"],
        gated=False,
        private=False,
        downloads=0,
        likes=0,
        card_data={},
    )
    item = hf_inventory.normalize_dataset_info(info, "sym")
    assert item["license"] is None
    assert item["review_status"] == "rights_review_required"


def test_summary_never_approves_content_automatically():
    summary = hf_inventory.build_summary(
        [
            {
                "license": "cc-by-4.0",
                "review_status": "resource_review_required",
                "gated": False,
            }
        ],
        {"sbd": 1, "stj": 0, "sym": 0},
    )
    assert summary["content_downloaded"] is False
    assert summary["publication_approved"] is False
    assert summary["training_approved"] is False
