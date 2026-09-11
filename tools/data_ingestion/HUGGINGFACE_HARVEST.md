# Récolte Hugging Face — Langue SAN

Ce document prend le relais après l'inventaire, le triage et l'inspection des repos Hugging Face.

## Résultat de l'inspection ciblée

Inspection locale réussie après 51 tests :

```text
espnet/mms_ulab_v2
  aucune config SAN dédiée détectée via Dataset Viewer
  filtres ISO sbd/stj/sym non résolus
  → différé à la phase audio/ASR

lbourdois/panlex
  aucune config SAN dédiée
  filtres ISO sbd/stj/sym non résolus via Dataset Viewer
  → extraction ciblée à traiter séparément

ec5ug/chikhapo
  fallback Hub réussi
  eng_stj détecté
  stj_eng détecté
  → première cible lexicale Matya

HuggingFaceFW/fineweb-2
  sbd_Latn détecté
  splits train + test
  taille non renvoyée par /size
  → cible texte Maka

HuggingFaceFW/finepdfs
  sbd_Latn : 7 lignes
  → petite cible texte Maka

cis-lmu/GlotCC-V1
  sbd-Latn : 2 lignes
  → petite cible texte Maka

cis-lmu/Taxi1500-RawData
  sbd_Latn : 7 919 lignes
  → gros volume, mais texte biblique et droits à clarifier : pas de récolte automatique pour le moment

openbmb/DCAD-2000
  sbd_Latn : 3 lignes
  → droits déclarés comme `other` : pas de récolte automatique pour le moment
```

## Cibles activées maintenant

Le fichier :

```text
config/huggingface_harvest.yaml
```

active uniquement :

```text
FineWeb2    → sbd_Latn
FinePDFs    → sbd_Latn
GlotCC-V1   → sbd-Latn
ChiKhaPo    → eng_stj + stj_eng
```

Ces récoltes restent des données externes non validées. Elles sont stockées sous `data/raw/`, ignoré par Git.

## Sonde avant récolte des sous-ensembles texte sbd

Comme `/size` n'a pas fourni le volume de FineWeb2, commencer par :

```bash
python collectors/huggingface_text_subsets.py --probe-only
```

Cette commande récupère seulement **une ligne par split** et affiche le `num_rows_total` renvoyé par Dataset Viewer. Elle ne télécharge pas le corpus complet.

Exemple de sortie attendue :

```text
fineweb2/sbd_Latn/train: total=...
fineweb2/sbd_Latn/test: total=...
finepdfs/sbd_Latn/train: total=7
glotcc/sbd-Latn/train: total=2
```

Le rapport est écrit dans :

```text
data/raw/huggingface/huggingface_text_probe_summary.json
```

## Récolte des sous-ensembles texte sbd

Après vérification des volumes :

```bash
python collectors/huggingface_text_subsets.py
```

Le collecteur utilise l'API Dataset Viewer `/rows`, 100 lignes maximum par requête, et parcourt chaque split jusqu'à la fin.

Sorties attendues :

```text
data/raw/huggingface/
├── fineweb2/sbd_Latn/train.jsonl
├── fineweb2/sbd_Latn/test.jsonl
├── finepdfs/sbd_Latn/train.jsonl
├── glotcc/sbd-Latn/train.jsonl
└── huggingface_text_harvest_summary.json
```

Chaque ligne conserve :

```text
repo_id
family
config
split
row_idx
iso_639_3
variety
license
rights_status
validation_status
source_row
```

Pour une seule cible :

```bash
python collectors/huggingface_text_subsets.py --target fineweb2_sbd
```

## Récolte lexicale ChiKhaPo stj ↔ anglais

Avant téléchargement, vérifier les chemins ciblés :

```bash
python collectors/huggingface_chikhapo.py --list-only
```

Puis télécharger uniquement les fichiers concernés :

```bash
python collectors/huggingface_chikhapo.py
```

Sortie :

```text
data/raw/huggingface/chikhapo/
├── eng_stj/
├── stj_eng/
└── metadata.json
```

Le repo ChiKhaPo est sous licence MIT, mais ses lexiques agrègent notamment PanLex, GATITOS et IDS. Pour cette raison, le RAW reste local et conserve le statut :

```text
upstream_source_provenance_review_required
```

## Sources volontairement bloquées ou différées

```text
Taxi1500
  7 919 lignes sbd détectées
  mais corpus biblique + droits du contenu à clarifier

DCAD-2000
  3 lignes sbd
  licence `other`

PanLex
  snapshot CC0 intéressant
  extraction ciblée sbd/stj/sym à développer séparément

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
