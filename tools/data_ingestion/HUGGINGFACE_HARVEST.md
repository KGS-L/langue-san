# Récolte Hugging Face — Langue SAN

Ce document suit l'inventaire, le triage, l'inspection et la récolte locale des ressources Hugging Face utiles au projet. Les données restent externes et non validées linguistiquement. Les RAW sont stockés sous `data/raw/`, ignoré par Git.

## État des sources ciblées

```text
Taxi1500 / sbd
  7 919 / 7 919 lignes récoltées
  domaine biblique
  droits amont à clarifier

ChiKhaPo / stj ↔ eng
  872 lignes RAW
  QA technique OK
  406 formes Matya uniques observées

PanLex / sbd stj sym
  sbd : 11 lignes
  stj : 408 lignes
  sym : 1 ligne
  QA technique OK

FinePDFs / sbd
  7 / 7 lignes récoltées

GlotCC / sbd
  2 / 2 lignes récoltées

FineWeb2 / sbd
  train : 4 lignes depuis 1 Parquet de 14 394 octets
  test : absent physiquement sur la révision récoltée
  QA technique OK

DCAD-2000 / sbd
  3 lignes détectées
  différé : licence `other`

MMS ulab
  différé à la future phase audio/ASR
```

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

Fichiers :

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

QA réel :

```text
eng_stj
  lignes          : 466
  sources uniques : 466
  traductions     : 528
  doublons exacts : 0
  technical_ok    : true

stj_eng
  lignes          : 406
  sources uniques : 406
  traductions     : 528
  doublons exacts : 0
  technical_ok    : true

Total lignes      : 872
```

Le repo ChiKhaPo est MIT, mais agrège notamment PanLex, GATITOS et IDS. Le RAW conserve donc le statut :

```text
upstream_source_provenance_review_required
```

## PanLex — récolté, QA validé et chevauchement mesuré

Récolte réelle :

```text
sbd / maka  : 11 / 11
stj / matya : 408 / 408
sym / maya  : 1 / 1
```

QA : 420 lignes JSON valides, 0 doublon exact, tous techniquement OK.

Comparaison des formes Matya PanLex ↔ ChiKhaPo :

```text
PanLex stj                       : 408 formes uniques
ChiKhaPo union Matya             : 406 formes uniques
Chevauchement                    : 406
Couverture PanLex par ChiKhaPo   : 99,51 %
Couverture ChiKhaPo par PanLex   : 100,00 %
Jaccard                          : 0,995098
```

Conséquence : ne pas additionner `408 + 406` comme 814 mots Matya indépendants. Les deux RAW restent utiles pour leur structure et leur provenance, mais leurs formes Matya se recouvrent presque complètement.

Rapports :

```text
data/processed/huggingface/panlex_qa.json
data/processed/huggingface/panlex_vs_chikhapo_stj.json
```

## FinePDFs et GlotCC sbd — récoltés

```text
FinePDFs / sbd_Latn / train : 7 / 7 lignes, partial=false
GlotCC / sbd-Latn / train   : 2 / 2 lignes, partial=false
```

Sorties :

```text
data/raw/huggingface/finepdfs/sbd_Latn/train.jsonl
data/raw/huggingface/glotcc/sbd-Latn/train.jsonl
```

Leur très faible volume en fait surtout des ressources de contrôle et de provenance.

## FineWeb2 sbd — récolté via fallback Parquet et QA validé

Dataset Viewer `/rows` renvoyait HTTP 500 pour `sbd_Latn`. Un collecteur dédié utilise donc les Parquet source du Hub.

Révision récoltée :

```text
af9c13333eb981300149d5ca60a8e9d659b276b9
```

Probe réel :

```text
train
  fichier : data/sbd_Latn/train/000_00000.parquet
  taille  : 14 394 octets

test
  absent sur cette révision
```

Récolte réelle :

```text
sbd_Latn/train : 4 lignes depuis 1 Parquet
```

Sorties :

```text
data/raw/huggingface/fineweb2/
├── sbd_Latn/train.jsonl
└── fineweb2_metadata.json
```

Le split `test` peut être déclaré dans les métadonnées générales de FineWeb2, mais `data/sbd_Latn/test` n'existe pas sur la révision récoltée. La configuration locale cible donc uniquement `train`.

QA technique réel :

```text
lignes                       : 4
JSON valides                 : 4
source_row manquants         : 0
métadonnées incohérentes     : 0
doublons exacts              : 0
lignes avec champ text       : 4
textes vides                 : 0
textes dupliqués             : 0
caractères texte             : 6651
technical_ok                 : true
```

Rapport :

```text
data/processed/huggingface/fineweb2_qa.json
```

Ce QA est purement technique : les 4 textes restent `external_unverified` et ne sont pas automatiquement considérés comme linguistiquement corrects, représentatifs du San Maka ou approuvés pour publication/entraînement.

## Statut du bloc Hugging Face texte/lexique

Le bloc Hugging Face texte/lexique peut être considéré comme **récolté pour les cibles actuellement approuvées** : Taxi1500, ChiKhaPo, PanLex, FinePDFs, GlotCC et FineWeb2 ont été récupérés localement avec provenance, et les QA techniques disponibles sont positifs.

Deux ressources restent volontairement hors de ce bloc :

```text
DCAD-2000
  3 lignes sbd
  licence `other`
  → droits à clarifier avant récolte automatique

MMS ulab
  audio non transcrit
  → à traiter plus tard dans la phase audio/ASR
```

La prochaine étape de cette branche n'est donc pas de transformer ces RAW en dataset final. Il faut continuer la **recherche/récolte d'autres sources externes** utiles, en particulier les ressources lexicales/documentaires originales ou plateformes non encore exploitées, tout en conservant la même discipline de provenance et de droits.

## Règle générale

```text
téléchargé
  ≠ validé linguistiquement
  ≠ orthographe standard
  ≠ approuvé pour publication
  ≠ approuvé pour entraînement
```
