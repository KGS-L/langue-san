from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path

import httpx
from huggingface_hub.errors import RemoteEntryNotFoundError


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "huggingface_fineweb2",
    ROOT / "collectors" / "huggingface_fineweb2.py",
)
fineweb2 = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(fineweb2)


class _Info:
    sha = "abc123"


class _RepoFile:
    def __init__(self, path, size, blob_id="blob"):
        self.path = path
        self.size = size
        self.blob_id = blob_id


class _RepoFolder:
    def __init__(self, path):
        self.path = path


class _FakeApi:
    def dataset_info(self, repo_id):
        assert repo_id == "HuggingFaceFW/fineweb-2"
        return _Info()

    def list_repo_tree(self, repo_id, path_in_repo=None, recursive=False, revision=None, repo_type=None):
        assert repo_id == "HuggingFaceFW/fineweb-2"
        assert path_in_repo == "data/sbd_Latn/train"
        assert recursive is True
        assert revision == "abc123"
        assert repo_type == "dataset"
        return [
            _RepoFile("data/sbd_Latn/train/0000.parquet", 120),
            _RepoFile("data/sbd_Latn/train/0001.parquet", 80),
            _RepoFile("data/sbd_Latn/train/README.txt", 5),
            _RepoFolder("data/sbd_Latn/train/subdir"),
        ]


class _FakeApiMissingTest(_FakeApi):
    def list_repo_tree(self, repo_id, path_in_repo=None, recursive=False, revision=None, repo_type=None):
        if path_in_repo == "data/sbd_Latn/test":
            request = httpx.Request(
                "GET",
                "https://huggingface.co/api/datasets/HuggingFaceFW/fineweb-2/tree/abc123/data%2Fsbd_Latn%2Ftest",
            )
            response = httpx.Response(404, request=request)
            raise RemoteEntryNotFoundError("missing test split", response=response)
        return super().list_repo_tree(
            repo_id,
            path_in_repo=path_in_repo,
            recursive=recursive,
            revision=revision,
            repo_type=repo_type,
        )


def test_load_target_finds_fineweb2(tmp_path):
    path = tmp_path / "harvest.yaml"
    path.write_text(
        """
text_subsets:
  - family: finepdfs
    enabled: true
  - family: fineweb2
    repo_id: HuggingFaceFW/fineweb-2
    config: sbd_Latn
    enabled: true
""".strip(),
        encoding="utf-8",
    )
    assert fineweb2.load_target(path)["config"] == "sbd_Latn"


def test_list_split_files_keeps_only_parquet():
    target = {
        "repo_id": "HuggingFaceFW/fineweb-2",
        "config": "sbd_Latn",
    }
    files = fineweb2.list_split_files(target, "train", api=_FakeApi(), revision="abc123")
    assert [item["path"] for item in files] == [
        "data/sbd_Latn/train/0000.parquet",
        "data/sbd_Latn/train/0001.parquet",
    ]
    assert sum(item["size_bytes"] for item in files) == 200


def test_probe_reports_revision_files_and_size():
    target = {
        "repo_id": "HuggingFaceFW/fineweb-2",
        "config": "sbd_Latn",
        "iso_639_3": "sbd",
        "variety": "maka",
    }
    result = fineweb2.probe(target, ["train"], api=_FakeApi())
    assert result["revision"] == "abc123"
    assert result["content_downloaded"] is False
    assert result["splits"][0]["file_count"] == 2
    assert result["splits"][0]["total_size_bytes"] == 200


def test_probe_marks_missing_split_instead_of_aborting():
    target = {
        "repo_id": "HuggingFaceFW/fineweb-2",
        "config": "sbd_Latn",
        "iso_639_3": "sbd",
        "variety": "maka",
    }
    result = fineweb2.probe(target, ["train", "test"], api=_FakeApiMissingTest())
    assert result["splits"][0]["status"] == "available"
    assert result["splits"][1]["status"] == "missing_on_revision"
    assert result["splits"][1]["available"] is False
