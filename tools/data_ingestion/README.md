# Data ingestion

Ce dossier regroupe les outils utilisés pour acquérir, contrôler et préparer des ressources linguistiques externes destinées au projet Langue SAN.

Il est volontairement séparé de :

- `apps/collector/`, qui collecte les contributions terrain et communautaires ;
- `data/`, qui contient les schémas, exemples et futurs jeux de données publiables ;
- `ml/`, qui servira au prétraitement ML, aux expériences, à l'entraînement et à l'évaluation.

> Guide complet : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)
>
> Le guide détaille les étapes, statuts, commandes, résultats attendus, échecs possibles, niveaux de validation et procédure d'ajout d'une nouvelle source.

## Principes

1. Ne jamais mélanger automatiquement les variétés San Maka (`sbd`), San Matya (`stj`) et San Maya (`sym`).
2. Conserver pour chaque ressource sa provenance, son URL, sa licence et sa date d'acquisition.
3. Une donnée récupérée sur Internet n'est pas automatiquement une donnée validée linguistiquement.
4. Les ressources dont les droits sont incertains restent désactivées jusqu'à vérification.
5. Les données brutes doivent être écrites sous `data/raw/`, déjà exclu de Git.
6. La notation source doit être conservée avant toute éventuelle normalisation linguistique.
7. Une glose française ajoutée par le projet n'est pas une validation de la forme San correspondante.
8. Aucun script ne doit promouvoir automatiquement une forme externe vers `standard_san`.

## Structure

```text
tools/data_ingestion/
├── config/
│   ├── concepts_fr.yaml
│   ├── languages.yaml
│   └── sources.yaml
├── collectors/
│   ├── __init__.py
│   └── asjp.py
├── processors/
│   ├── __init__.py
│   ├── enrich_concepts.py
│   ├── normalize.py
│   └── qa_variants.py
├── tests/
│   ├── test_asjp.py
│   ├── test_config.py
│   ├── test_enrich_concepts.py
│   ├── test_normalize.py
│   └── test_qa_variants.py
├── GUIDE_DATA_INGESTION.md
├── README.md
└── requirements.txt
```

## Pipeline

```text
Source externe
      ↓
collector
      ↓
data/raw/                       # non commité
      ↓
normalisation
      ↓
data/processed/                 # données de travail
      ↓
rapport QA général
      ↓
analyse des variantes
      ↓
enrichissement contrôlé des concepts FR
      ↓
validation linguistique humaine
      ↓
dataset approuvé explicitement
      ↓
ML / traduction / apprentissage
```

## Installation locale

Depuis `tools/data_ingestion/` :

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Lors des sessions suivantes :

```bash
source .venv/bin/activate
```

## Tests

```bash
pytest tests
```

Les tests unitaires n'ont pas besoin d'Internet pour les transformations locales. Ils vérifient notamment la sélection des codes ISO, la conservation de la provenance, le parsing du schéma CLDF, la normalisation, l'analyse des variantes et l'enrichissement contrôlé des concepts français.

# ASJP — première source active

L'intégration initiale utilise **ASJP v21 (2025)** sous forme CLDF. Le collecteur utilise les tables structurées publiées par le projet ASJP, puis extrait uniquement les entrées correspondant aux codes ISO du projet :

- `sbd` — San Maka / Southern Samo San ;
- `stj` — San Matya ;
- `sym` — San Maya.

La ressource reste `external_unverified` tant qu'elle n'a pas été contrôlée par notre processus linguistique.

## 1. Collecte

```bash
python collectors/asjp.py
```

Sortie :

```text
<racine-du-repo>/data/raw/asjp/asjp_v21_san_wordlists.json
```

Résultat réel actuel :

```text
341 entrées au total
sbd : 37 entrées / 34 concepts / 36 formes
stj : 129 entrées / 78 concepts / 111 formes
sym : 175 entrées / 88 concepts / 150 formes
```

## 2. Normalisation + rapport QA général

```bash
python processors/normalize.py
```

Sorties :

```text
<racine-du-repo>/data/processed/asjp/
├── asjp_v21_san_normalized.json
├── asjp_v21_san_normalized.csv
└── asjp_v21_report.json
```

Le normaliseur conserve la forme ASJP originale dans `source_form`. Il ne la transforme jamais automatiquement en orthographe San validée : `standard_san` reste `null` tant qu'une validation linguistique n'a pas eu lieu.

## 3. Analyse QA des variantes

```bash
python processors/qa_variants.py
```

Résultat réel actuel :

```text
68 groupes multi-formes

Par variété :
sbd : 2
stj : 35
sym : 31

Par type :
cross_wordlist_single_each            : 16
cross_wordlist_with_internal_variants : 27
internal_variants_same_wordlist       : 25
```

Sorties :

```text
<racine-du-repo>/data/processed/asjp/
├── asjp_v21_variants_review.json
├── asjp_v21_variants_review.csv
└── asjp_v21_variants_summary.json
```

Une variante détectée n'est pas automatiquement une erreur. Le pipeline conserve les formes et demande une revue humaine au lieu d'en sélectionner une arbitrairement.

## 4. Enrichissement contrôlé des concepts en français

Le fichier :

```text
config/concepts_fr.yaml
```

contient des glosses françaises de travail pour les concepts ASJP observés dans notre extraction.

Commande :

```bash
python processors/enrich_concepts.py
```

Sorties prévues :

```text
<racine-du-repo>/data/processed/asjp/
├── asjp_v21_san_enriched.json
├── asjp_v21_san_enriched.csv
└── asjp_v21_concepts_fr_report.json
```

Le processeur ajoute notamment :

```text
concept_fr
concept_fr_source = project_controlled_gloss_v1
concept_fr_status = project_controlled_gloss
concept_fr_note
```

Exemple :

```text
concept_normalized = water
concept_fr         = eau
source_form        = mu
standard_san       = null
```

Cela signifie uniquement : « le concept ASJP `water` est glossé `eau` en français ». Cela ne signifie pas encore : « `eau → mu` est une paire de traduction San validée ».

# Statut actuel

```text
ASJP — licence / provenance             ✅
ASJP — collecte                         ✅ 341 entrées
ASJP — séparation sbd/stj/sym           ✅
ASJP — normalisation                    ✅
ASJP — QA variantes                     ✅ 68 groupes classifiés
ASJP — glosses françaises contrôlées    ⏳ prochaine étape locale
ASJP — validation orthographique San    ⏳ non commencée
ASJP — validation humaine               ⏳ non commencée
ASJP — training                         ❌ non approuvé
```

La règle centrale reste :

```text
Téléchargé ≠ correct linguistiquement ≠ validé ≠ autorisé pour entraînement
```
