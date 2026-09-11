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


def test_clean_description_removes_plugin_labels():
    assert ainsisoisje._clean_description("foo ... Show more") == "foo"
