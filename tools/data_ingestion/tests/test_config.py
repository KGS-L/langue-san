from pathlib import Path

import yaml


ROOT = Path(__file__).resolve().parents[1]


def test_language_iso_codes_are_distinct_and_expected():
    config = yaml.safe_load((ROOT / "config" / "languages.yaml").read_text(encoding="utf-8"))
    codes = {item["iso_639_3"] for item in config["varieties"].values()}

    assert codes == {"sbd", "stj", "sym"}


def test_expected_external_sources_are_enabled_for_current_ingestion_phase():
    config = yaml.safe_load((ROOT / "config" / "sources.yaml").read_text(encoding="utf-8"))
    enabled = {name for name, item in config["sources"].items() if item["enabled"]}

    assert enabled == {"asjp", "reflex"}
