# Récolte Hugging Face — Langue SAN

Ce document prend le relais après l'inventaire, le triage et l'inspection des repos Hugging Face.

## Résultat de l'inspection ciblée

Inspection locale réussie après 59 tests :

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
  taille non renvoyée par /size
  /rows renvoie actuellement HTTP 500 sur le probe local
  → cible texte Maka conservée, fallback à ajouter si nécessaire

HuggingFaceFW/finepdfs
  sbd_Latn : 7 lignes
  → petite cible texte Maka

cis-lmu/GlotCC-V1
  sbd-Latn : 2 lignes
  → petite cible texte Maka

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

## PanLex — récolté

Le probe ciblé a trouvé :

```text
sbd / Maka  : 11 lignes
stj / Matya : 408 lignes
sym / Maya  : 1 ligne
partial = false pour les trois codes
```

La première requête `sbd` a subi plusieurs HTTP 500 temporaires du Dataset Viewer, mais le retry/backoff a fini par réussir.

Récolte réelle :

```text
sbd / maka  : 11 / 11 lignes, nouvelles=11, repris=0, partial=false
stj / matya : 408 / 408 lignes, nouvelles=408, repris=0, partial=false
sym / maya  : 1 / 1 ligne, nouvelles=1, repris=0, partial=false
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

### QA technique PanLex

```bash
python processors/qa_huggingface_panlex.py
```

Le QA vérifie l'intégrité JSONL, les codes ISO, la variété, `source_row.vocab`, les `var_code`, les répétitions de vocabulaire et les doublons exacts. Il ne modifie pas le RAW.

### Comparaison PanLex ↔ ChiKhaPo pour stj

Les volumes `408 lignes PanLex stj` et `406 mots sources ChiKhaPo stj_eng` sont très proches, mais cela ne suffit pas à conclure qu'il s'agit des mêmes données. Comme ChiKhaPo déclare PanLex parmi ses sources amont, le chevauchement exact est mesuré avec :

```bash
python processors/compare_panlex_chikhapo.py
```

La comparaison applique uniquement NFC + casefold + espaces condensés à des fins analytiques. Elle mesure les formes Matya identiques et ne déduit ni équivalence linguistique ni identité de provenance.

## Autres sous-ensembles texte sbd

Pour sonder/récolter individuellement :

```bash
python collectors/huggingface_text_subsets.py --target finepdfs_sbd --probe-only
python collectors/huggingface_text_subsets.py --target glotcc_sbd --probe-only
python collectors/huggingface_text_subsets.py --target fineweb2_sbd --probe-only
```

Le Dataset Viewer de FineWeb2 a renvoyé une erreur HTTP 500 sur `/rows` lors du premier probe. Cela n'invalide pas la config `sbd_Latn`; un fallback Parquet/Hub pourra être ajouté si l'erreur persiste.

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
