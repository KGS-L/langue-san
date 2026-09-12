from importlib.util import module_from_spec, spec_from_file_location
import csv
import hashlib
import json
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location(
    "qa_reflex_units",
    ROOT / "processors" / "qa_reflex_units.py",
)
qa = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(qa)


HEADERS = [
    "Original Form",
    "Original Translation",
    "Comment",
    "Part of Speech",
    "Source",
    "Glottocode",
    "Family",
    "Latitude",
    "Longitude",
]


def _write_fixture(root: Path, iso: str, variety: str, glottocode: str, rows):
    folder = root / iso
    folder.mkdir(parents=True, exist_ok=True)
    csv_path = folder / "units.csv"
    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=HEADERS)
        writer.writeheader()
        writer.writerows(rows)
    content = csv_path.read_bytes()
    metadata = {
        "iso_639_3": iso,
        "variety": variety,
        "reflex_glottocode": glottocode,
        "csv_rows": len(rows),
        "xhr_reported_rows": len(rows),
        "sha256": hashlib.sha256(content).hexdigest(),
    }
    (folder / "units_metadata.json").write_text(
        json.dumps(metadata), encoding="utf-8"
    )
    return csv_path, folder / "units_metadata.json"


def _row(form="ka", translation="eau", glottocode="maty1235"):
    return {
        "Original Form": form,
        "Original Translation": translation,
        "Comment": "",
        "Part of Speech": "NOM",
        "Source": "Morris et al. 2011 : Matya",
        "Glottocode": glottocode,
        "Family": "Mande",
        "Latitude": "13.2",
        "Longitude": "-3.15",
    }


def test_inspect_file_validates_metadata_and_glottocode(tmp_path):
    csv_path, meta_path = _write_fixture(
        tmp_path,
        "stj",
        "matya",
        "maty1235",
        [_row("ka", "eau"), _row("so", "maison")],
    )

    result = qa.inspect_file(
        csv_path,
        meta_path,
        iso="stj",
        variety="matya",
        expected_glottocode="maty1235",
    )

    assert result["total_rows"] == 2
    assert result["glottocode_mismatch_rows"] == 0
    assert result["row_count_matches_metadata"] is True
    assert result["sha256_matches_metadata"] is True
    assert result["technical_ok"] is True


def test_duplicates_and_blank_translation_are_reported_without_modifying_raw(tmp_path):
    duplicate = _row("ka", "")
    csv_path, meta_path = _write_fixture(
        tmp_path,
        "stj",
        "matya",
        "maty1235",
        [duplicate, duplicate],
    )
    before = csv_path.read_bytes()

    result = qa.inspect_file(
        csv_path,
        meta_path,
        iso="stj",
        variety="matya",
        expected_glottocode="maty1235",
    )

    assert result["blank_original_translation_rows"] == 2
    assert result["exact_duplicate_rows"] == 1
    assert result["duplicate_form_translation_rows"] == 1
    assert result["technical_ok"] is True
    assert csv_path.read_bytes() == before


def test_wrong_glottocode_fails_technical_qa(tmp_path):
    csv_path, meta_path = _write_fixture(
        tmp_path,
        "stj",
        "matya",
        "maty1235",
        [_row(glottocode="maya1281")],
    )

    result = qa.inspect_file(
        csv_path,
        meta_path,
        iso="stj",
        variety="matya",
        expected_glottocode="maty1235",
    )

    assert result["glottocode_mismatch_rows"] == 1
    assert result["technical_ok"] is False


def test_run_qa_aggregates_matya_and_maya(tmp_path):
    _write_fixture(tmp_path, "stj", "matya", "maty1235", [_row()])
    maya_row = _row(form="mi", translation="feu", glottocode="maya1281")
    maya_row["Source"] = "Morris et al. 2011 : Maya"
    _write_fixture(tmp_path, "sym", "maya", "maya1281", [maya_row])
    output = tmp_path / "processed" / "qa.json"

    payload = qa.run_qa(raw_root=tmp_path, output=output)

    assert payload["summary"]["iso_files_checked"] == 2
    assert payload["summary"]["total_rows"] == 2
    assert payload["summary"]["all_technical_ok"] is True
    assert output.exists()
