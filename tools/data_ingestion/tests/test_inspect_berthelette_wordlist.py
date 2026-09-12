from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from processors.inspect_berthelette_wordlist import contiguous_ranges, localities_in_text


def test_localities_in_text_collapses_accent_aliases_to_canonical_names():
    text = "Toma Kassoum Kouy Toeni Bounou Kiembara Bangassogo Lankoue"
    assert localities_in_text(text) == [
        "Toma",
        "Kassoum",
        "Kouy",
        "Toéni",
        "Bounou",
        "Kiembara",
        "Bangassogo",
        "Lankoué",
    ]


def test_contiguous_ranges_groups_separate_candidate_blocks():
    assert contiguous_ranges([2, 3, 4, 8, 9, 10, 41, 42, 43, 44]) == [
        (2, 4),
        (8, 10),
        (41, 44),
    ]


def test_contiguous_ranges_empty_input():
    assert contiguous_ranges([]) == []
