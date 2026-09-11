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
  → récolte locale activée, mais droits amont à clarifier et domaine biblique à isoler

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
```

Ces récoltes restent des données externes non validées. Elles sont stockées sous `data/raw/`, ignoré par Git.

Important pour Taxi1500 : le projet Langue SAN est actuellement non commercial, mais cela ne remplace pas une licence explicite du contenu source. Le RAW Taxi1500 est donc accepté pour **récolte locale, inventaire et recherche technique**, avec `rights_review_required_local_research_only`. Il ne doit pas être commité, republié ou redistribué tant que les droits amont ne sont pas clarifiés.

## Récolte Taxi1500 sbd

Sonde ciblée :

```bash
python collectors/huggingface_text_subsets.py --target taxi1500_sbd --probe-only
```

Récolte complète des 7 919 lignes :

```bash
python collectors/huggingface_text_subsets.py --target taxi1500_sbd
```

Sortie attendue :

```text
data/raw/huggingface/taxi1500/sbd_Latn/taxi1500.jsonl
```

Le corpus reste étiqueté :

```text
iso_639_3       = sbd
variety         = maka
rights_status   = rights_review_required_local_research_only
domain          = religious_bible_text
validation_status = external_unverified
```

Le domaine biblique doit rester séparé des futurs corpus généraux pour éviter qu'il ne domine les données du projet.

## Autres sous-ensembles texte sbd

Pour sonder/récolter individuellement :

```bash
python collectors/huggingface_text_subsets.py --target finepdfs_sbd --probe-only
python collectors/huggingface_text_subsets.py --target glotcc_sbd --probe-only
python collectors/huggingface_text_subsets.py --target fineweb2_sbd --probe-only
```

Le Dataset Viewer de FineWeb2 a renvoyé une erreur HTTP 500 sur `/rows` lors du premier probe. Cela n'invalide pas la config `sbd_Latn`; un fallback Parquet/Hub pourra être ajouté si l'erreur persiste.

Chaque ligne récoltée conserve :

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

## Récolte lexicale ChiKhaPo stj ↔ anglais

Les chemins ciblés ont été confirmés :

```text
data/eng_stj.jsonl
data/stj_eng.jsonl
```

Téléchargement local :

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

Le repo ChiKhaPo est sous licence MIT, mais ses lexiques agrègent notamment PanLex, GATITOS et IDS. Le RAW reste local avec le statut :

```text
upstream_source_provenance_review_required
```

## Sources volontairement bloquées ou différées

```text
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
