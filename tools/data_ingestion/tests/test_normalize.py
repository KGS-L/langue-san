import json
from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("normalize", ROOT / "processors" / "normalize.py")
assert SPEC and SPEC.loader
normalize = module_from_spec(SPEC)
SPEC.loader.exec_module(normalize)


def _sample_entries():
    return [
        {
            "source": "ASJP",
            "source_version": "v21",
            "license": "CC-BY-4.0",
            "iso_639_3": "sbd",
            "language_id": "SOUTHERN_SAMO",
            "glottocode": "sout2844",
            "parameter_id": "75",
            "concept": "*water",
            "value": "mu",
            "form": "mu",
            "loan": "false",
        },
        {
            "source": "ASJP",
            "source_version": "v21",
            "license": "CC-BY-4.0",
            "iso_639_3": "stj",
            "language_id": "SAMO_MATYA_2",
            "glottocode": "maty1235",
            "parameter_id": "41",
            "concept": "*nose",
            "value": "5ini",
            "form": "5ini",
            "loan": "false",
        },
        {
            "source": "ASJP",
            "source_version": "v21",
            "license": "CC-BY-4.0",
            "iso_639_3": "sym",
            "language_id": "SAMO_MAYA",
            "glottocode": "maya1281",
            "parameter_id": "66",
            "concept": "*come",
            "value": "da",
            "form": "da",
            "loan": "false",
        },
        {
            "source": "ASJP",
            "source_version": "v21",
            "license": "CC-BY-4.0",
            "iso_639_3": "sym",
            "language_id": "SAMO_MAYA",
            "glottocode": "maya1281",
            "parameter_id": "66",
            "concept": "*come",
            "value": "gono",
            "form": "gono",
            "loan": "false",
        },
    ]


def test_normalization_preserves_asjp_form_and_does_not_claim_standard_spelling():
    row = normalize.normalize_entry(_sample_entries()[1])

    assert row["variety"] == "matya"
    assert row["concept_source"] == "*nose"
    assert row["concept_normalized"] == "nose"
    assert row["source_form"] == "5ini"
    assert row["source_notation"] == "asjp"
    assert row["standard_san"] is None
    assert row["concept_fr"] is None
    assert row["validation_status"] == "external_unverified"


def test_report_counts_varieties_and_multi_form_concepts():
    raw = _sample_entries()
    normalized = normalize.normalize_entries(raw)
    report = normalize.build_report(raw, normalized, {"source": "ASJP", "version": "v21"})

    assert report["totals"]["normalized_entries"] == 4
    assert report["by_iso"]["sbd"]["entries"] == 1
    assert report["by_iso"]["stj"]["entries"] == 1
    assert report["by_iso"]["sym"]["entries"] == 2
    assert report["by_iso"]["sym"]["unique_concepts"] == 1
    assert report["multi_form_concepts"]["count"] == 1
    assert report["status"]["training_approved"] is False


def test_process_file_writes_json_csv_and_report(tmp_path):
    input_path = tmp_path / "raw.json"
    output_dir = tmp_path / "processed"
    payload = {
        "metadata": {
            "source": "ASJP",
            "version": "v21",
            "license": "CC-BY-4.0",
            "retrieved_at": "2026-09-11T20:37:00+00:00",
        },
        "entries": _sample_entries(),
    }
    input_path.write_text(json.dumps(payload), encoding="utf-8")

    paths = normalize.process_file(input_path, output_dir)

    assert paths["json"].exists()
    assert paths["csv"].exists()
    assert paths["report"].exists()

    report = json.loads(paths["report"].read_text(encoding="utf-8"))
    assert report["totals"]["raw_entries"] == 4
    assert report["duplicates"]["exact_duplicate_extra_rows"] == 0
