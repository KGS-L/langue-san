# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide complet : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)

## Périmètre de la branche `feat/data-ingestion`

Dans cette branche, le but principal est la **récolte technique des sources externes**.

```text
source externe
    ↓
vérification provenance / droits
    ↓
collector ou scraper
    ↓
data/raw/                        # local, non commité
    ↓
normalisation technique minimale
    ↓
QA / statistiques / comparaison
    ↓
SOURCE RÉCOLTÉE
```

La transformation ultérieure en source linguistique officielle du projet, la validation humaine, `standard_san`, l'import applicatif et l'autorisation ML seront traités séparément.

## Principes

1. Ne jamais mélanger automatiquement San Maka (`sbd`), San Matya (`stj`) et San Maya (`sym`).
2. Conserver provenance, URL, licence/droits et date d'acquisition.
3. Une donnée accessible publiquement n'est pas automatiquement réutilisable ou publiable.
4. Les ressources aux droits incertains restent désactivées dans `config/sources.yaml`.
5. `data/raw/` et `data/processed/` sont des données locales de travail et ne sont pas publiées automatiquement.
6. La notation source est conservée telle quelle.
7. Une similarité de chaînes de caractères n'est jamais une validation linguistique.

## Structure

```text
tools/data_ingestion/
├── config/
│   ├── concepts_fr.yaml
│   ├── languages.yaml
│   └── sources.yaml
├── collectors/
│   ├── __init__.py
│   ├── ainsisoisje.py
│   └── asjp.py
├── processors/
│   ├── __init__.py
│   ├── build_review_sheet.py
│   ├── compare_ainsisoisje_asjp.py
│   ├── enrich_concepts.py
│   ├── normalize.py
│   └── qa_variants.py
├── tests/
│   ├── test_ainsisoisje.py
│   ├── test_asjp.py
│   ├── test_build_review_sheet.py
│   ├── test_compare_ainsisoisje_asjp.py
│   ├── test_config.py
│   ├── test_enrich_concepts.py
│   ├── test_normalize.py
│   └── test_qa_variants.py
├── GUIDE_DATA_INGESTION.md
├── README.md
└── requirements.txt
```

## Installation locale

Depuis `tools/data_ingestion/` :

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Puis, à chaque session :

```bash
source .venv/bin/activate
pytest tests
```

# Source 1 — ASJP v21

ASJP est notre première source structurée.

```text
licence : CC-BY-4.0
statut  : approved_for_ingestion
```

Collecte :

```bash
python collectors/asjp.py
```

Résultat réel actuel :

```text
341 entrées
sbd : 37 entrées / 34 concepts / 36 formes
stj : 129 entrées / 78 concepts / 111 formes
sym : 175 entrées / 88 concepts / 150 formes
```

Normalisation :

```bash
python processors/normalize.py
```

QA variantes :

```bash
python processors/qa_variants.py
```

Résultat réel :

```text
68 groupes multi-formes
sbd : 2
stj : 35
sym : 31
```

Glosses françaises de travail :

```bash
python processors/enrich_concepts.py
```

Résultat réel :

```text
92 / 92 concepts glossés en français
341 / 341 entrées enrichies
```

La préparation de revue humaine déjà développée reste disponible, mais elle n'est pas la priorité de cette branche :

```bash
python processors/build_review_sheet.py
```

# Source 2 — Ainsi sois-je / Dictionnaire Français-Samo

Ressource publique :

```text
https://ainsisoisje.com/dictionnaire-samo-francais/
```

La page indique actuellement **126 noms** dans le répertoire.

Points importants :

```text
nom de langue affiché : Samo
variété ISO            : inconnue
copyright               : All Rights Reserved
statut droits           : rights_review_required
publication             : non approuvée
training ML             : non approuvé
```

Cette source reste donc une **source candidate locale pour inventaire et comparaison**. Son contenu brut ne doit pas être commité ou publié tant que les droits de réutilisation ne sont pas clarifiés.

## Collecte

```bash
python collectors/ainsisoisje.py
```

Le collecteur :

- charge la page principale ;
- découvre les liens par lettre du plugin WordPress Name Directory ;
- visite les pages avec une courte pause ;
- extrait les couples `français / samo` ;
- conserve dans le RAW les occurrences dupliquées visibles sur le site ;
- signale séparément les doublons et les écarts avec le compteur public ;
- ne lui attribue aucun code ISO ;
- écrit localement sous `data/raw/ainsisoisje/`.

Sortie :

```text
<repo>/data/raw/ainsisoisje/dictionnaire_samo_francais.json
```

Résultat réel actuel :

```text
compteur annoncé par le site      : 126
occurrences récupérées            : 125
paires Français/Samo uniques      : 124
groupes dupliqués                 : 1
occurrence dupliquée supplémentaire: 1

doublon observé : Noir → Ti (2 occurrences)
```

Une occurrence reste non récupérée par rapport au compteur global du site. Les compteurs par lettre analysés par le collecteur sont néanmoins cohérents avec les pages parcourues. L'écart est conservé comme anomalie de collecte documentée au lieu d'être masqué.

## Comparaison avec ASJP

Après la collecte :

```bash
python processors/compare_ainsisoisje_asjp.py
```

Le comparateur travaille sur les **124 paires Français/Samo uniques** afin que le doublon du site ne biaise pas les statistiques. Le RAW reste inchangé avec ses 125 occurrences.

Résultat réel actuel :

```text
Occurrences RAW du site : 125
Paires uniques comparées : 124
Doublons exclus des statistiques : 1

CONCEPT_MATCH_FORM_DIFFERENT : 15
FORM_SIMILARITY_CANDIDATE    : 9
SITE_ONLY_OR_UNRESOLVED      : 100
EXACT_FORM_MATCH             : 0
```

Signal heuristique de proximité des formes pour les concepts comparables :

```text
sbd : meilleur candidat 2 fois ; similarité moyenne 0.7494
stj : meilleur candidat 12 fois ; similarité moyenne 0.5153
sym : meilleur candidat 10 fois ; similarité moyenne 0.5463
```

Ce signal ne permet pas d'attribuer automatiquement le dictionnaire à `sbd`, `stj` ou `sym`. Il mesure seulement une ressemblance graphique entre chaînes pour les concepts que le comparateur a réussi à rapprocher.

Le comparateur classe les lignes selon :

```text
SITE_ONLY_OR_UNRESOLVED
EXACT_FORM_MATCH
FORM_SIMILARITY_CANDIDATE
CONCEPT_MATCH_FORM_DIFFERENT
```

Important :

```text
FORM_SIMILARITY_CANDIDATE
    !=
forme linguistiquement équivalente
```

Sorties :

```text
<repo>/data/processed/comparisons/
├── ainsisoisje_vs_asjp.json
├── ainsisoisje_vs_asjp.csv
└── ainsisoisje_vs_asjp_report.json
```

# État actuel des sources

```text
ASJP
├── découverte / provenance      ✅
├── droits                       ✅ CC-BY-4.0
├── collecte                     ✅ 341
├── normalisation / QA           ✅
└── état branche ingestion       ✅ SOURCE RÉCOLTÉE

Ainsi sois-je
├── découverte                   ✅
├── volume annoncé               ✅ 126
├── collecte locale              ✅ 125 occurrences / 124 paires uniques
├── écart compteur               ⚠️ 1 occurrence non récupérée, documentée
├── doublon connu                ⚠️ Noir → Ti
├── variété exacte               ? inconnue
├── droits                       ⚠️ All Rights Reserved
├── comparaison ASJP             ✅ 124 paires uniques comparées
└── état branche ingestion       ✅ SOURCE CANDIDATE RÉCOLTÉE

Hugging Face
└── inventaire datasets          ⏳ prochaine source
```
