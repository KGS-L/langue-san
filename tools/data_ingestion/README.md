# Data ingestion

Ce dossier regroupe les outils utilisés pour acquérir des ressources linguistiques externes destinées au projet Langue SAN.

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

## Structure

```text
tools/data_ingestion/
├── config/
│   ├── languages.yaml
│   └── sources.yaml
├── collectors/
│   ├── __init__.py
│   └── asjp.py
├── processors/
│   ├── __init__.py
│   └── normalize.py
├── tests/
│   ├── test_asjp.py
│   ├── test_config.py
│   └── test_normalize.py
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
data/raw/               # non commité
      ↓
normalisation
      ↓
data/processed/         # données de travail
      ↓
rapport QA
      ↓
contrôle provenance / licence
      ↓
validation linguistique si nécessaire
      ↓
dataset exploitable
```

## ASJP — première source active

L'intégration initiale utilise **ASJP v21 (2025)** sous forme CLDF. Au lieu de scraper les pages HTML, le collecteur télécharge les tables structurées publiées par le projet ASJP, puis extrait uniquement les entrées correspondant aux codes ISO du projet :

- `sbd` — San Maka / Southern Samo San ;
- `stj` — San Matya ;
- `sym` — San Maya.

La ressource est conservée comme **référence lexicale externe** et reste `external_unverified` tant qu'elle n'a pas été contrôlée par notre processus linguistique.

### Installation

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

### Tests

```bash
pytest tests
```

Les tests unitaires n'ont pas besoin d'Internet : ils valident la sélection des codes ISO, la conservation de la provenance, le parsing du schéma CLDF attendu et le comportement du normaliseur.

### Collecte ASJP

```bash
python collectors/asjp.py
```

Pour ne récupérer qu'une ou plusieurs variétés :

```bash
python collectors/asjp.py --iso sbd stj
```

La sortie est créée sous :

```text
<racine-du-repo>/data/raw/asjp/asjp_v21_san_wordlists.json
```

Ce fichier ne doit pas être commité.

### Normalisation + rapport QA

Après la collecte :

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

## Résultat ASJP actuel

Première collecte réelle validée techniquement :

```text
341 entrées au total
sbd : 37 entrées / 34 concepts
stj : 129 entrées / 78 concepts
sym : 175 entrées / 88 concepts
```

Ces chiffres valident la collecte technique, pas la qualité linguistique finale des formes.
