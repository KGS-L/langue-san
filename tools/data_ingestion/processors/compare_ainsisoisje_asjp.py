"""Comparaison heuristique entre Ainsi sois-je et ASJP.

Cette comparaison sert à mesurer le recouvrement potentiel entre sources. Elle
ne valide aucune équivalence linguistique et n'attribue pas automatiquement la
variété du dictionnaire « Samo » de ainsisoisje.com à sbd, stj ou sym.

Le RAW Ainsi sois-je conserve toutes les occurrences visibles sur le site, y
compris ses doublons. Pour éviter de biaiser les statistiques inter-sources, la
comparaison travaille en revanche sur les paires Français/Samo uniques.
"""

from __future__ import annotations

import argparse
import csv
import json
import re
import unicodedata
from collections import Counter, defaultdict
from difflib import SequenceMatcher
from pathlib import Path
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[3]
DEFAULT_SITE_INPUT = REPO_ROOT / "data" / "raw" / "ainsisoisje" / "dictionnaire_samo_francais.json"
DEFAULT_ASJP_INPUT = REPO_ROOT / "data" / "processed" / "asjp" / "asjp_v21_san_enriched.json"
DEFAULT_OUTPUT_DIR = REPO_ROOT / "data" / "processed" / "comparisons"

CSV_FIELDS = (
    "site_french",
    "site_samo",
    "concept_match_type",
    "matched_concept_fr",
    "concept_similarity",
    "classification",
    "best_form_similarity",
    "best_iso",
    "best_variety",
    "best_asjp_form",
    "best_source_wordlist",
    "top_matches_json",
)


class SourceComparisonError(RuntimeError):
    """Erreur contrôlée de comparaison inter-sources."""


def _text_key(value: Any) -> str:
    text = unicodedata.normalize("NFKD", str(value or "").casefold())
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    text = text.replace("’", "'")
    text = re.sub(r"[^\w]+", " ", text, flags=re.UNICODE)
    return " ".join(text.split())


def _exact_form_key(value: Any) -> str:
    """Normalisation minimale pour une égalité textuelle stricte."""

    text = unicodedata.normalize("NFC", str(value or "")).casefold()
    return " ".join(text.replace("\xa0", " ").split())


def _form_key(value: Any) -> str:
    # Clé uniquement destinée à la similarité technique. La valeur originale
    # reste toujours conservée et n'est jamais réécrite.
    return _text_key(value).replace(" ", "")


def similarity(a: Any, b: Any) -> float:
    left = _form_key(a)
    right = _form_key(b)
    if not left or not right:
        return 0.0
    return SequenceMatcher(None, left, right).ratio()


def _site_pair_key(entry: dict[str, Any]) -> tuple[str, str]:
    """Clé stable utilisée uniquement pour éviter le double comptage."""

    return (
        _exact_form_key(entry.get("french")),
        _exact_form_key(entry.get("samo")),
    )


def deduplicate_site_entries(entries: list[dict[str, Any]]) -> list[dict[str, Any]]:
    """Retourne une occurrence par paire Français/Samo, sans modifier le RAW."""

    unique: list[dict[str, Any]] = []
    seen: set[tuple[str, str]] = set()
    for entry in entries:
        key = _site_pair_key(entry)
        if key in seen:
            continue
        seen.add(key)
        unique.append(entry)
    return unique


def _concept_variants(value: str) -> set[str]:
    """Crée des clés de travail pour une glose FR sans en changer le sens."""

    variants = {_text_key(value)}
    for part in re.split(r"\s*(?:/|;)\s*", value):
        key = _text_key(part)
        if key:
            variants.add(key)
    return {item for item in variants if item}


def _build_concept_index(asjp_entries: list[dict[str, Any]]) -> tuple[dict[str, list[dict[str, Any]]], list[str]]:
    index: dict[str, list[dict[str, Any]]] = defaultdict(list)
    labels: set[str] = set()
    for entry in asjp_entries:
        concept_fr = str(entry.get("concept_fr") or "").strip()
        if not concept_fr:
            continue
        labels.add(concept_fr)
        for key in _concept_variants(concept_fr):
            index[key].append(entry)
    return dict(index), sorted(labels)


def _match_concept(
    french: str,
    concept_index: dict[str, list[dict[str, Any]]],
    concept_labels: list[str],
    *,
    fuzzy_threshold: float = 0.90,
) -> tuple[str, str | None, float, list[dict[str, Any]]]:
    site_key = _text_key(french)
    if site_key in concept_index:
        rows = concept_index[site_key]
        label = str(rows[0].get("concept_fr") or french)
        return "exact_french_gloss", label, 1.0, rows

    scored = sorted(
        (
            SequenceMatcher(None, site_key, _text_key(label)).ratio(),
            label,
        )
        for label in concept_labels
        if site_key and _text_key(label)
    )
    if not scored:
        return "none", None, 0.0, []

    score, label = scored[-1]
    if score < fuzzy_threshold:
        return "none", None, score, []

    rows: list[dict[str, Any]] = []
    for key in _concept_variants(label):
        rows.extend(concept_index.get(key, []))
    unique = {str(row.get("id") or id(row)): row for row in rows}
    return "fuzzy_french_gloss_candidate", label, score, list(unique.values())


def compare_entry(
    site_entry: dict[str, Any],
    concept_index: dict[str, list[dict[str, Any]]],
    concept_labels: list[str],
) -> dict[str, Any]:
    french = str(site_entry.get("french") or "").strip()
    samo = str(site_entry.get("samo") or "").strip()
    match_type, matched_label, concept_score, rows = _match_concept(
        french,
        concept_index,
        concept_labels,
    )

    if not rows:
        return {
            "site_french": french,
            "site_samo": samo,
            "concept_match_type": match_type,
            "matched_concept_fr": matched_label,
            "concept_similarity": round(concept_score, 4),
            "classification": "SITE_ONLY_OR_UNRESOLVED",
            "best_form_similarity": None,
            "best_iso": None,
            "best_variety": None,
            "best_asjp_form": None,
            "best_source_wordlist": None,
            "top_matches": [],
        }

    candidates = []
    for row in rows:
        score = similarity(samo, row.get("source_form"))
        candidates.append(
            {
                "iso_639_3": row.get("iso_639_3"),
                "variety": row.get("variety"),
                "source_wordlist": row.get("source_wordlist"),
                "source_form": row.get("source_form"),
                "form_similarity": round(score, 4),
            }
        )

    candidates.sort(key=lambda item: item["form_similarity"], reverse=True)
    top = candidates[:5]
    best = top[0]

    exact_textual = any(
        _exact_form_key(samo) == _exact_form_key(item["source_form"])
        for item in candidates
        if item.get("source_form")
    )
    if exact_textual:
        classification = "EXACT_FORM_MATCH"
    elif best["form_similarity"] >= 0.80:
        classification = "FORM_SIMILARITY_CANDIDATE"
    else:
        classification = "CONCEPT_MATCH_FORM_DIFFERENT"

    return {
        "site_french": french,
        "site_samo": samo,
        "concept_match_type": match_type,
        "matched_concept_fr": matched_label,
        "concept_similarity": round(concept_score, 4),
        "classification": classification,
        "best_form_similarity": best["form_similarity"],
        "best_iso": best.get("iso_639_3"),
        "best_variety": best.get("variety"),
        "best_asjp_form": best.get("source_form"),
        "best_source_wordlist": best.get("source_wordlist"),
        "top_matches": top,
    }


def compare_sources(site_entries: list[dict[str, Any]], asjp_entries: list[dict[str, Any]]) -> list[dict[str, Any]]:
    concept_index, labels = _build_concept_index(asjp_entries)
    return [compare_entry(entry, concept_index, labels) for entry in site_entries]


def build_summary(
    comparisons: list[dict[str, Any]],
    *,
    raw_site_entry_count: int | None = None,
) -> dict[str, Any]:
    classifications = Counter(item["classification"] for item in comparisons)
    concept_matches = Counter(item["concept_match_type"] for item in comparisons)

    by_iso_scores: dict[str, list[float]] = defaultdict(list)
    by_iso_top_count: Counter[str] = Counter()
    for item in comparisons:
        for candidate in item.get("top_matches", []):
            iso = str(candidate.get("iso_639_3") or "")
            score = float(candidate.get("form_similarity") or 0.0)
            if iso:
                by_iso_scores[iso].append(score)
        if item.get("best_iso"):
            by_iso_top_count[str(item["best_iso"])] += 1

    variety_signal = {}
    for iso in sorted(set(by_iso_scores) | set(by_iso_top_count)):
        scores = by_iso_scores.get(iso, [])
        variety_signal[iso] = {
            "times_best_candidate": by_iso_top_count.get(iso, 0),
            "mean_candidate_similarity": round(sum(scores) / len(scores), 4) if scores else None,
            "note": "Signal heuristique de chaînes de caractères, pas attribution linguistique de variété.",
        }

    raw_count = raw_site_entry_count if raw_site_entry_count is not None else len(comparisons)
    return {
        "site_raw_occurrence_count": raw_count,
        "site_unique_pair_count_compared": len(comparisons),
        "duplicate_occurrences_excluded_from_comparison": max(0, raw_count - len(comparisons)),
        "by_classification": dict(sorted(classifications.items())),
        "by_concept_match_type": dict(sorted(concept_matches.items())),
        "variety_similarity_signal": variety_signal,
        "rights_note": "Ainsi sois-je reste rights_review_required / All Rights Reserved.",
        "linguistic_note": "Les scores mesurent une similarité graphique, jamais une équivalence linguistique.",
        "training_approved": False,
    }


def process_files(
    site_input: Path = DEFAULT_SITE_INPUT,
    asjp_input: Path = DEFAULT_ASJP_INPUT,
    output_dir: Path = DEFAULT_OUTPUT_DIR,
) -> dict[str, Path]:
    if not site_input.exists():
        raise SourceComparisonError(f"Source Ainsi sois-je introuvable : {site_input}")
    if not asjp_input.exists():
        raise SourceComparisonError(f"Source ASJP enrichie introuvable : {asjp_input}")

    site_payload = json.loads(site_input.read_text(encoding="utf-8"))
    asjp_payload = json.loads(asjp_input.read_text(encoding="utf-8"))
    site_entries = site_payload.get("entries")
    asjp_entries = asjp_payload.get("entries")
    if not isinstance(site_entries, list) or not isinstance(asjp_entries, list):
        raise SourceComparisonError("Les deux fichiers doivent contenir une liste 'entries'.")

    unique_site_entries = deduplicate_site_entries(site_entries)
    comparisons = compare_sources(unique_site_entries, asjp_entries)
    summary = build_summary(comparisons, raw_site_entry_count=len(site_entries))

    output_dir.mkdir(parents=True, exist_ok=True)
    json_path = output_dir / "ainsisoisje_vs_asjp.json"
    csv_path = output_dir / "ainsisoisje_vs_asjp.csv"
    report_path = output_dir / "ainsisoisje_vs_asjp_report.json"

    json_path.write_text(
        json.dumps({"summary": summary, "comparisons": comparisons}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    with csv_path.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=CSV_FIELDS)
        writer.writeheader()
        for item in comparisons:
            writer.writerow(
                {
                    "site_french": item["site_french"],
                    "site_samo": item["site_samo"],
                    "concept_match_type": item["concept_match_type"],
                    "matched_concept_fr": item["matched_concept_fr"],
                    "concept_similarity": item["concept_similarity"],
                    "classification": item["classification"],
                    "best_form_similarity": item["best_form_similarity"],
                    "best_iso": item["best_iso"],
                    "best_variety": item["best_variety"],
                    "best_asjp_form": item["best_asjp_form"],
                    "best_source_wordlist": item["best_source_wordlist"],
                    "top_matches_json": json.dumps(item["top_matches"], ensure_ascii=False),
                }
            )

    report_path.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")
    return {"json": json_path, "csv": csv_path, "report": report_path}


def main() -> None:
    parser = argparse.ArgumentParser(description="Comparer Ainsi sois-je avec ASJP sans validation linguistique automatique")
    parser.add_argument("--site-input", type=Path, default=DEFAULT_SITE_INPUT)
    parser.add_argument("--asjp-input", type=Path, default=DEFAULT_ASJP_INPUT)
    parser.add_argument("--output-dir", type=Path, default=DEFAULT_OUTPUT_DIR)
    args = parser.parse_args()

    paths = process_files(args.site_input, args.asjp_input, args.output_dir)
    report = json.loads(paths["report"].read_text(encoding="utf-8"))
    print(f"Occurrences RAW du site : {report['site_raw_occurrence_count']}")
    print(f"Paires uniques comparées : {report['site_unique_pair_count_compared']}")
    print(
        "Doublons exclus des statistiques : "
        f"{report['duplicate_occurrences_excluded_from_comparison']}"
    )
    print("Par classification :")
    for name, count in report["by_classification"].items():
        print(f"- {name}: {count}")
    print("Signal de proximité par ISO (heuristique) :")
    for iso, stats in report["variety_similarity_signal"].items():
        print(
            f"- {iso}: meilleur candidat {stats['times_best_candidate']} fois, "
            f"similarité moyenne {stats['mean_candidate_similarity']}"
        )
    print(f"JSON   : {paths['json']}")
    print(f"CSV    : {paths['csv']}")
    print(f"Rapport: {paths['report']}")


if __name__ == "__main__":
    main()
