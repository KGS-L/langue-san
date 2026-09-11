from importlib.util import module_from_spec, spec_from_file_location
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SPEC = spec_from_file_location("ainsisoisje", ROOT / "collectors" / "ainsisoisje.py")
ainsisoisje = module_from_spec(SPEC)
assert SPEC and SPEC.loader
SPEC.loader.exec_module(ainsisoisje)


def test_extract_declared_count():
    html = '<div class="name_directory_total">Il y a actuellement 126 noms dans ce répertoire</div>'
    assert ainsisoisje.extract_declared_count(html) == 126


def test_extract_letter_declared_count():
    html = "Il existe 5 noms dans ce répertoire qui commencent par la lettre N."
    assert ainsisoisje.extract_letter_declared_count(html) == 5


def test_discover_letter_urls_deduplicates_and_resolves_relative_links():
    html = '''
    <a class="name_directory_startswith" href="?name_directory_startswith=A">A</a>
    <a class="name_directory_startswith" href="?name_directory_startswith=B&amp;dir=1">B</a>
    <a href="?name_directory_startswith=A">A bis</a>
    '''
    urls = ainsisoisje.discover_letter_urls(html, "https://example.org/dico/")
    assert urls == [
        "https://example.org/dico/?name_directory_startswith=A",
        "https://example.org/dico/?name_directory_startswith=B&dir=1",
    ]


def test_parse_name_directory_entries_preserves_duplicates():
    html = '''
    <div class="name_directory_name_box">
      <h4 role="term">Noir</h4><br><p>Ti</p>
    </div>
    <div class="name_directory_name_box">
      <h4 role="term">Noir</h4><br><p>Ti</p>
    </div>
    '''
    assert ainsisoisje.parse_directory_entries(html) == [
        ("Noir", "Ti"),
        ("Noir", "Ti"),
    ]


def test_parse_name_directory_entries_preserves_term_with_empty_description():
    html = '''
    <div class="name_directory_name_box">
      <h4 role="term">Mot sans traduction</h4>
    </div>
    '''
    assert ainsisoisje.parse_directory_entries(html) == [
        ("Mot sans traduction", ""),
    ]


def test_parse_name_directory_entries():
    html = '''
    <div class="name_directory_name_box">
      <a name="namedirectory_eau"></a>
      <h4 role="term">Eau</h4><br>
      <p>mu</p>
    </div>
    <div class="name_directory_name_box">
      <h4 role="term">Chien</h4><br>
      jiri
    </div>
    '''
    assert ainsisoisje.parse_directory_entries(html) == [
        ("Eau", "mu"),
        ("Chien", "jiri"),
    ]


def test_duplicate_analysis_counts_occurrences_without_removing_them():
    entries = [
        {"french": "Noir", "samo": "Ti", "source_page": "N"},
        {"french": "Noir", "samo": "Ti", "source_page": "N"},
        {"french": "Eau", "samo": "Mu", "source_page": "E"},
        {"french": "EAU", "samo": "mu", "source_page": "E"},
        {"french": "Chien", "samo": "Jiri", "source_page": "C"},
    ]
    result = ainsisoisje.analyze_duplicate_occurrences(entries)
    assert result["unique_pair_count"] == 3
    assert result["duplicate_group_count"] == 2
    assert result["duplicate_extra_occurrences"] == 2
    assert sum(group["occurrences"] for group in result["duplicate_groups"]) == 4


def test_clean_description_removes_plugin_labels():
    assert ainsisoisje._clean_description("foo ... Show more") == "foo"
