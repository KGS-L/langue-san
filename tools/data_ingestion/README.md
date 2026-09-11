# Data ingestion

Ce dossier regroupe les outils utilisés pour acquérir des ressources linguistiques externes destinées au projet Langue SAN.

Il est volontairement séparé de :

- `apps/collector/`, qui collecte les contributions terrain et communautaires ;
- `data/`, qui contient les schémas, exemples et futurs jeux de données publiables ;
- `ml/`, qui servira au prétraitement ML, aux expériences, à l'entraînement et à l'évaluation.

## Principes

1. Ne jamais mélanger automatiquement les variétés San Maka (`sbd`), San Matya (`stj`) et San Maya (`sym`).
2. Conserver pour chaque ressource sa provenance, son URL, sa licence et sa date d'acquisition.
3. Une donnée récupérée sur Internet n'est pas automatiquement une donnée validée linguistiquement.
4. Les ressources dont les droits sont incertains restent désactivées jusqu'à vérification.
5. Les données brutes doivent être écrites sous `data/raw/`, déjà exclu de Git.

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
│   └── test_config.py
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

Depuis la racine du dépôt :

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r tools/data_ingestion/requirements.txt
```

### Collecte ASJP

```bash
python tools/data_ingestion/collectors/asjp.py
```

Pour ne récupérer qu'une ou plusieurs variétés :

```bash
python tools/data_ingestion/collectors/asjp.py --iso sbd stj
```

La sortie est créée sous :

```text
data/raw/asjp/asjp_v21_san_wordlists.json
```

Ce fichier ne doit pas être commité.

### Tests

```bash
pytest tools/data_ingestion/tests
```

Les tests unitaires n'ont pas besoin d'Internet : ils valident la sélection des codes ISO, la conservation de la provenance et le parsing du schéma CLDF attendu.
