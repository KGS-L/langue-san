# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide général : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)  
> État Hugging Face : [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md)  
> Reconnaissance RefLex : [`REFLEX_RECON.md`](REFLEX_RECON.md)  
> Reconnaissance Berthelette : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md)

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

Toma et Tougan sont uniquement des indices géographiques. Une localité ne valide jamais automatiquement une variété, sauf lorsqu'une source linguistique/bibliographique explicite documente elle-même cette correspondance et que cette provenance est conservée.

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

## RefLex CLLD — récolte + QA terminés pour Matya et Maya

RefLex CLLD est sous :

```text
CC-BY-NC-SA-4.0
```

Les données récoltées restent donc destinées à l'inventaire/recherche locale sous les conditions de cette licence. Elles ne sont **pas** automatiquement approuvées pour publication, entraînement ou usage commercial.

### Résolution réelle

```text
stj / Matya / Samo Matya / maty1235
sym / Maya  / Samo Maya  / maya1281
```

`San Maka / sbd / sout2844` n'a pas été trouvé dans l'index RefLex actuel et ne doit pas être remplacé par un candidat approximatif.

La récolte réelle utilise le DataTable `/units` découvert depuis la page de la langue puis son export CSV officiel.

### Résultats

```text
stj / Matya
  languages.csv : 2 764 fiches dans la plus grosse/unique source
  /units export : 2 743 lignes
  glottocode     : maty1235
  clld page id   : 1472

sym / Maya
  languages.csv : 2 384 fiches dans la plus grosse/unique source
  /units export : 2 378 lignes
  glottocode     : maya1281
  clld page id   : 1473

TOTAL RefLex RAW : 5 121 unités lexicales
```

Colonnes RAW :

```text
Original Form
Original Translation
Comment
Part of Speech
Source
Glottocode
Family
Latitude
Longitude
```

QA exécuté :

```text
stj : technical_ok=True
sym : technical_ok=True
all_technical_ok=True
```

**Statut RefLex dans cette phase : `collection_success + qa_passed` pour `stj` et `sym`.**

## Volume RAW opérationnel à ce stade

```text
ASJP           341
Ainsi sois-je  125
Taxi1500      7919
ChiKhaPo       872
PanLex         420
FinePDFs         7
GlotCC           2
FineWeb2          4
RefLex         5121
------------------
TOTAL         14811 occurrences/lignes RAW
```

Ce total est un **compteur de collecte**, pas un corpus final : il contient des chevauchements, des domaines spécialisés, des licences/droits différents et des données non validées linguistiquement.

## Source active — Berthelette 2001

Reconnaissance détaillée : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md).

```text
John Berthelette
Sociolinguistic survey report for the San (Samo) language
SILESR 2002-005
75 pages
SIL archive entry 8983
```

La notice SIL officielle et l'URL du PDF original ont été identifiées. Le document est intéressant à la fois pour le contexte sociolinguistique et pour ses wordlists/localités.

Glottolog associe explicitement cette référence à :

```text
sbd / Maka  : Toma
stj / Matya : Kassoum, Kouy, Toéni
sym / Maya  : Bounou, Kiembara, Bangassogo, Lankoué
```

ASJP `MAYA_SAMO` utilise Berthelette comme source amont : les futures lignes Berthelette ne devront donc pas être additionnées naïvement aux lignes ASJP.

### Collecteur

```text
collectors/berthelette.py
```

Il télécharge/ingère uniquement le **PDF original** et conserve son SHA-256 et sa provenance. L'extraction des wordlists viendra après inspection du PDF.

Après `git pull` et `pytest tests` :

```bash
python collectors/berthelette.py --probe-only
```

Si le serveur SIL autorise le téléchargement depuis la machine locale, le probe valide le PDF sans écrire de RAW. On pourra ensuite lancer :

```bash
python collectors/berthelette.py
```

Si SIL répond HTTP 403, télécharger manuellement le fichier officiel `SILESR2002_005.pdf` depuis l'entrée SIL 8983 / le lien PDF, puis :

```bash
python collectors/berthelette.py --input-file ~/Téléchargements/SILESR2002_005.pdf
```

Sorties de récolte :

```text
data/raw/berthelette/
├── SILESR2002_005.pdf
└── metadata.json
```

Droits de travail actuels : la règle par défaut des SIL Language & Culture Archives est `CC-BY-NC-SA-4.0` sauf indication contraire de l'item/fichier. Le PDF doit donc encore être inspecté avant de figer son statut juridique précis dans le projet.

## Ordre des prochaines sources

```text
1. Berthelette 2001 — PDF, inspection, wordlists/localités, QA
2. Lexiques originaux SIL / ANTBA
3. Dictionnaires Burkina Langues — seulement après clarification des droits
4. Textes / audio bibliques ANTBA — droits à clarifier et domaine religieux séparé
5. RefLex sbd — seulement si une présence/source fiable est retrouvée ultérieurement
```

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
