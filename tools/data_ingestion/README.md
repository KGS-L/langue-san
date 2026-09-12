# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide général : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)  
> État Hugging Face : [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md)  
> Reconnaissance RefLex et prochaines sources : [`REFLEX_RECON.md`](REFLEX_RECON.md)

## Périmètre de `feat/data-ingestion`

Cette branche sert à la **récolte technique des sources externes**.

```text
source externe
    ↓
vérification provenance / droits
    ↓
collector / scraper / export ciblé
    ↓
data/raw/                        # local, non commité
    ↓
QA technique / statistiques / comparaison
    ↓
SOURCE RÉCOLTÉE
```

La transformation ultérieure en source linguistique officielle du projet, la validation humaine, `standard_san`, l'import applicatif et l'autorisation ML seront traités séparément.

## Variétés — règle absolue

```text
San Maka / San du Sud : sbd
San Matya             : stj
San Maya              : sym
```

Toma et Tougan sont uniquement des indices géographiques. Une localité ne valide jamais automatiquement une variété.

## Principes

1. Ne jamais fusionner automatiquement `sbd`, `stj` et `sym`.
2. Conserver provenance, URL, licence/droits, identifiants source et date d'acquisition.
3. Une donnée accessible publiquement n'est pas automatiquement réutilisable ou publiable.
4. Le RAW reproduit la source : pas de déduplication silencieuse, pas de translittération, pas de correction linguistique.
5. Les doublons, blancs et anomalies sont signalés séparément par le QA.
6. `data/raw/` et `data/processed/` restent locaux et ne sont pas publiés automatiquement.
7. Une similarité graphique n'est jamais une validation linguistique.
8. `récolté` ≠ `validé linguistiquement` ≠ `approuvé pour publication` ≠ `approuvé pour entraînement`.

## Installation locale

Depuis `tools/data_ingestion/` :

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

À chaque session :

```bash
source .venv/bin/activate
pytest tests
```

## État des sources déjà travaillées

### ASJP v21 — récolté

```text
licence : CC-BY-4.0
RAW     : 341 entrées
sbd     : 37
stj     : 129
sym     : 175
```

La notation ASJP est conservée avec la provenance de chaque wordlist. Les processors de normalisation technique, QA des variantes et enrichissement des concepts sont disponibles.

### Ainsi sois-je — récolte locale candidate

```text
copyright site              : All Rights Reserved
variété exacte              : inconnue
compteur annoncé            : 126
occurrences récupérées      : 125
paires uniques              : 124
doublon observé             : Noir → Ti (2 occurrences)
publication / entraînement  : non approuvés
```

Le RAW reste local. La comparaison avec ASJP est heuristique et ne permet aucune attribution automatique vers `sbd`, `stj` ou `sym`.

### Hugging Face — bloc texte/lexique ciblé terminé

Détails complets dans [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md).

```text
Taxi1500 / sbd
  7 919 / 7 919 lignes
  domaine biblique
  droits amont à clarifier

ChiKhaPo / stj ↔ eng
  872 lignes RAW
  QA technique OK
  406 formes Matya uniques observées

PanLex / sbd stj sym
  sbd : 11
  stj : 408
  sym : 1
  QA technique OK

PanLex ↔ ChiKhaPo stj
  chevauchement : 406 formes
  couverture ChiKhaPo par PanLex : 100 %
  → ne pas additionner naïvement les volumes

FinePDFs / sbd
  7 / 7 lignes

GlotCC / sbd
  2 / 2 lignes

FineWeb2 / sbd
  4 lignes depuis 1 Parquet
  6 651 caractères de texte
  QA technique OK
```

FineWeb2 a nécessité un fallback direct vers le Parquet du Hub car Dataset Viewer `/rows` renvoyait HTTP 500. Sur la révision récoltée, `train` existe mais le `test` annoncé dans les métadonnées n'est pas présent physiquement.

Restent volontairement différés :

```text
DCAD-2000 : licence `other`, rights review requis
MMS ulab  : audio non transcrit, future phase audio/ASR
```

## Source actuelle — RefLex CLLD

RefLex CLLD est la prochaine source structurée prioritaire. La base est téléchargeable et conserve les formes originales ainsi que les références de sources. Elle est sous :

```text
CC-BY-NC-SA-4.0
```

Cette licence autorise notre inventaire/recherche locale non commerciale sous ses conditions, mais **ne doit pas être interprétée comme une autorisation pour un futur usage commercial ou un entraînement commercial**.

Cibles :

```text
sbd / maka  / sout2844
stj / matya / maty1235
sym / maya  / maya1281
```

Le collecteur est :

```text
collectors/reflex_clld.py
```

### Étape 1 — probe uniquement

Après mise à jour de la branche :

```bash
cd ~/Bureau/langue-san
git pull origin feat/data-ingestion

cd tools/data_ingestion
source .venv/bin/activate
pytest tests

python collectors/reflex_clld.py --probe-only
```

Le probe doit seulement :

```text
languages.csv
    ↓
résolution des IDs internes RefLex par glottocode / ISO
    ↓
1 ligne de probe par variété
    ↓
volume exact sbd / stj / sym
```

Il ne doit pas télécharger les lexiques complets.

Résumé local attendu :

```text
data/raw/reflex/reflex_probe_summary.json
```

### Étape 2 — récolte complète

**Ne pas la lancer avant validation du probe.**

Lorsque les identifiants et volumes auront été vérifiés :

```bash
python collectors/reflex_clld.py
```

Sorties prévues :

```text
data/raw/reflex/
├── sbd/
│   ├── language.json
│   └── values.csv
├── stj/
│   ├── language.json
│   └── values.csv
├── sym/
│   ├── language.json
│   └── values.csv
└── reflex_harvest_summary.json
```

Le CSV RefLex est conservé brut. Les comparaisons avec ASJP, PanLex ou ChiKhaPo viendront après la récolte et ne modifieront pas le RAW.

## Prochaines sources après RefLex

La reconnaissance détaillée se trouve dans [`REFLEX_RECON.md`](REFLEX_RECON.md). Ordre actuel :

```text
1. RefLex CLLD
2. Berthelette 2001 — enquête sociolinguistique + wordlists
3. Lexiques originaux SIL / ANTBA
4. Dictionnaires Burkina Langues — seulement après clarification des droits
5. Textes / audio bibliques ANTBA — droits à clarifier et domaine religieux séparé
```

Les applications de dictionnaire sont potentiellement très riches, notamment en audio, mais une application gratuite n'est pas une licence de réutilisation. Aucun scraping APK massif ne doit être lancé sans autorisation claire.

## Règle finale de cette branche

```text
accessible
  ≠ librement réutilisable

gratuit
  ≠ open data

présent dans un agrégateur
  ≠ source indépendante

récolté
  ≠ validé linguistiquement
  ≠ orthographe standard
  ≠ approuvé pour publication
  ≠ approuvé pour entraînement
  ≠ approuvé pour usage commercial
```
