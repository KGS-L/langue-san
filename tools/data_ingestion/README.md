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
│   └── test_config.py
├── README.md
└── requirements.txt
```

## Pipeline prévu

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

## Première source

La première intégration cible ASJP, qui fournit des listes lexicales structurées. Le collecteur ASJP sera développé avant d'ajouter d'autres sources.
