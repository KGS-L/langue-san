# Récolte Hugging Face — Langue SAN

Ce document prend le relais après l'inventaire, le triage et l'inspection des repos Hugging Face.

## Résultat de l'inspection ciblée

```text
espnet/mms_ulab_v2
  aucune config SAN dédiée détectée via Dataset Viewer
  filtres ISO sbd/stj/sym non résolus
  → différé à la phase audio/ASR

lbourdois/panlex
  snapshot lexical CC0
  ~24,6M lignes / 6152 langues
  → récolté de façon ciblée pour sbd/stj/sym

ec5ug/chikhapo
  fallback Hub réussi
  eng_stj détecté
  stj_eng détecté
  → récolté et QA technique validé

HuggingFaceFW/fineweb-2
  sbd_Latn détecté
  splits train + test
  /rows renvoie HTTP 500 sur cette config
  → fallback dédié via les Parquet source du Hub

HuggingFaceFW/finepdfs
  sbd_Latn : 7 lignes
  → récolté

cis-lmu/GlotCC-V1
  sbd-Latn : 2 lignes
  → récolté

cis-lmu/Taxi1500-RawData
  sbd_Latn : 7 919 lignes
  → récolte locale complète, droits amont à clarifier et domaine biblique à isoler

openbmb/DCAD-2000
  sbd_Latn : 3 lignes
  → droits déclarés comme `other` : pas de récolte automatique pour le moment
```

## Cibles activées maintenant

Le fichier :

```text
config/huggingface_harvest.yaml
```

active :

```text
FineWeb2    → sbd_Latn
FinePDFs    → sbd_Latn
GlotCC-V1   → sbd-Latn
Taxi1500    → sbd_Latn / split taxi1500
ChiKhaPo    → eng_stj + stj_eng
PanLex      → lignes 639-3=sbd/stj/sym
```

Ces récoltes restent des données externes non validées. Elles sont stockées sous `data/raw/`, ignoré par Git.

## Taxi1500 sbd — récolté

Résultat réel :

```text
7919 / 7919 lignes
partial = false
```

La première tentative a subi un HTTP 429 après 6100 lignes. Le collecteur a ensuite repris automatiquement au bon offset et récupéré les 1819 lignes restantes.

Sortie :

```text
data/raw/huggingface/taxi1500/sbd_Latn/taxi1500.jsonl
```

Le corpus reste étiqueté :

```text
iso_639_3         = sbd
variety           = maka
rights_status     = rights_review_required_local_research_only
domain            = religious_bible_text
validation_status = external_unverified
```

Le domaine biblique doit rester séparé des futurs corpus généraux pour éviter qu'il ne domine les données du projet.

## ChiKhaPo stj ↔ anglais — récolté et QA technique validé

Fichiers récoltés :

```text
data/raw/huggingface/chikhapo/
├── eng_stj/eng_stj.jsonl
├── stj_eng/stj_eng.jsonl
└── metadata.json
```

Révision Hugging Face :

```text
f9b2a2b609a0bc4613a9d599d5195e82ccb0e658
```

Résultat QA :

```text
eng_stj
  lignes            : 466
  JSON valides      : 466
  sources uniques   : 466
  traductions       : 528
  doublons exacts   : 0
  technical_ok      : true

stj_eng
  lignes            : 406
  JSON valides      : 406
  sources uniques   : 406
  traductions       : 528
  doublons exacts   : 0
  technical_ok      : true

Total lignes        : 872
Tous techniquement OK : true
```

Le nombre de traductions est identique dans les deux sens (528), mais le nombre de mots sources diffère car plusieurs traductions peuvent être regroupées sur une même entrée. Cela n'est pas une anomalie technique.

Le repo ChiKhaPo est sous licence MIT, mais ses lexiques agrègent notamment PanLex, GATITOS et IDS. Le RAW reste local avec le statut :

```text
upstream_source_provenance_review_required
```

## PanLex — récolté, QA validé et chevauchement mesuré

Récolte réelle :

```text
sbd / maka  : 11 / 11 lignes, partial=false
stj / matya : 408 / 408 lignes, partial=false
sym / maya  : 1 / 1 ligne, partial=false
```

Sorties :

```text
data/raw/huggingface/panlex/
├── sbd/train.jsonl
├── stj/train.jsonl
├── sym/train.jsonl
├── panlex_probe_summary.json
└── panlex_harvest_summary.json
```

Les colonnes source conservées comprennent notamment :

```text
vocab
639-3
639-3_english_name
var_code
english_name_var
```

`var_code` est conservé tel quel : il s'agit d'un identifiant de variante PanLex, pas d'un code ISO international.

### QA technique PanLex — résultat réel

```text
sbd / maka
  lignes          : 11
  JSON valides    : 11
  vocab uniques   : 11
  var_codes       : 1
  doublons exacts : 0
  technical_ok    : true

stj / matya
  lignes          : 408
  JSON valides    : 408
  vocab uniques   : 408
  var_codes       : 2
  doublons exacts : 0
  technical_ok    : true

sym / maya
  lignes          : 1
  JSON valides    : 1
  vocab uniques   : 1
  var_codes       : 1
  doublons exacts : 0
  technical_ok    : true

Total             : 420 lignes
Tous techniquement OK : true
```

Rapport :

```text
data/processed/huggingface/panlex_qa.json
```

### PanLex ↔ ChiKhaPo pour stj — résultat réel

La comparaison des formes Matya, après uniquement NFC + casefold + espaces condensés, donne :

```text
PanLex stj                          : 408 formes uniques
ChiKhaPo stj_eng                   : 406 formes source uniques
ChiKhaPo eng_stj                   : 406 formes cible uniques
Union des formes Matya ChiKhaPo    : 406
Chevauchement PanLex ∩ ChiKhaPo    : 406
Couverture de PanLex par ChiKhaPo  : 99,51 %
Couverture de ChiKhaPo par PanLex  : 100,00 %
Jaccard                             : 0,995098
```

Interprétation technique : ChiKhaPo et PanLex ne doivent pas être comptés comme deux apports lexicaux indépendants pour `stj`. Toutes les 406 formes Matya observées dans ChiKhaPo sont déjà présentes dans PanLex ; PanLex ne possède que 2 formes supplémentaires hors de l'union ChiKhaPo observée.

Cela est cohérent avec la provenance déclarée de ChiKhaPo, qui agrège notamment PanLex. Cette conclusion porte uniquement sur le chevauchement des formes, pas sur l'identité exacte de toutes les entrées, traductions, sens ou sources amont.

Rapport :

```text
data/processed/huggingface/panlex_vs_chikhapo_stj.json
```

Conséquence pour les futurs bilans de volume : conserver les deux RAW et leurs provenances, mais éviter d'additionner naïvement `408 + 406` comme 814 mots Matya indépendants.

## FinePDFs et GlotCC sbd — récoltés

Probes :

```text
FinePDFs / sbd_Latn / train : 7 lignes, partial=false
GlotCC / sbd-Latn / train   : 2 lignes, partial=false
```

Récolte réelle :

```text
FinePDFs : 7 / 7 lignes
GlotCC   : 2 / 2 lignes
```

Sorties :

```text
data/raw/huggingface/
├── finepdfs/sbd_Latn/train.jsonl
└── glotcc/sbd-Latn/train.jsonl
```

Ces 9 lignes restent `external_unverified`. Leur très faible volume en fait surtout des ressources de contrôle/provenance, pas un corpus principal.

## FineWeb2 sbd — fallback Parquet du Hub

La config `sbd_Latn` est bien déclarée avec les splits `train` et `test`, mais Dataset Viewer `/rows` a renvoyé HTTP 500. Le projet ne force donc plus cet endpoint.

Le collecteur dédié :

```text
collectors/huggingface_fineweb2.py
```

utilise `HfApi.list_repo_tree()` uniquement sur :

```text
data/sbd_Latn/train/
data/sbd_Latn/test/
```

puis, pour la récolte complète, télécharge seulement les Parquet trouvés via `hf_hub_download` et les convertit en JSONL avec `pyarrow`.

Avant toute récolte, sonder uniquement la liste et la taille des fichiers :

```bash
python collectors/huggingface_fineweb2.py --probe-only
```

Cette sonde ne télécharge aucun Parquet. Elle doit être utilisée pour vérifier le volume exact avant la récolte complète.

Si le volume est acceptable :

```bash
python collectors/huggingface_fineweb2.py
```

Sorties prévues :

```text
data/raw/huggingface/fineweb2/
├── sbd_Latn/train.jsonl
├── sbd_Latn/test.jsonl
└── fineweb2_metadata.json
```

Chaque ligne conserve notamment la révision du repo, le chemin Parquet d'origine, l'index dans le fichier, les métadonnées `sbd/maka` et la ligne source complète.

Dépendance supplémentaire :

```text
pyarrow>=17,<22
```

## Sources volontairement différées

```text
DCAD-2000
  3 lignes sbd
  licence `other`

MMS ulab
  audio non transcrit
  utile plus tard pour ASR, pas prioritaire dans la collecte texte/lexique
```

## Règle

```text
téléchargé
  ≠ validé linguistiquement
  ≠ orthographe standard
  ≠ approuvé pour publication
  ≠ approuvé pour entraînement
```
