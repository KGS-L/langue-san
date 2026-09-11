# Reconnaissance Hugging Face — Langue SAN

Date de référence : **11 septembre 2026**.

Ce document suit la phase d'inventaire des datasets Hugging Face susceptibles de contenir du San/Samo pour les trois codes ISO du projet :

```text
sbd → San Maka / Southern Samo
stj → San Matya
sym → San Maya
```

## 1. Ce que montre l'index des langues Hugging Face

La page publique `https://huggingface.co/languages` indique actuellement :

```text
sbd / Southern Samo : 16 datasets
stj / Matya Samo    :  7 datasets
sym / Maya Samo     :  6 datasets
```

Attention : ces nombres ne représentent pas nécessairement des sources indépendantes. Un même repo peut couvrir plusieurs codes ISO, et certains datasets sont eux-mêmes dérivés d'autres corpus.

## 2. Inventaire réel obtenu dans le projet

Commande :

```bash
python collectors/huggingface_inventory.py
```

Résultat réel obtenu le 11 septembre 2026 :

```text
sbd (maka)  : 16 repos
stj (matya) :  7 repos
sym (maya)  :  6 repos

17 datasets uniques
0 dataset gated
```

Répartition des licences déclarées :

```text
cc-by-4.0       : 2
cc-by-nc-sa-4.0 : 4
cc0-1.0         : 3
mit             : 1
odc-by          : 4
other           : 3
```

L'inventaire ne télécharge aucun contenu linguistique. Il conserve uniquement les métadonnées publiques du Hub dans :

```text
data/raw/huggingface/
├── huggingface_san_inventory.json
├── huggingface_san_inventory.csv
└── huggingface_san_inventory_summary.json
```

Les métadonnées comprennent notamment :

```text
repo_id
matched_iso_codes
matched_varieties
license
gated
downloads
likes
languages
modalities
formats
tasks
size_categories
url
```

Chaque repo reste :

```text
resource_review_required
```

ou :

```text
rights_review_required
```

si les droits sont absents ou incertains.

Aucun repo n'est automatiquement approuvé pour publication ou entraînement.

## 3. Triage automatique des 17 repos

Le script :

```bash
python processors/triage_huggingface_inventory.py
```

lit l'inventaire local et produit un ordre de priorité d'inspection :

```text
high
medium
low
```

Résultat réel :

```text
high   : 8
medium : 8
low    : 1
```

Le score prend en compte uniquement des signaux techniques :

```text
nombre de codes ISO couverts
licence déclarée
repo gated ou privé
tâches translation / ASR
modalité text / audio
mots-clés corpus / parallel / speech / etc.
signaux faible priorité : fréquence, benchmark, tokenizer, statistiques
```

Ce score ne décide jamais qu'un dataset est linguistiquement correct ou juridiquement autorisé.

Sorties :

```text
data/processed/huggingface/
├── huggingface_san_triage.json
├── huggingface_san_triage.csv
└── huggingface_san_triage_summary.json
```

## 4. Revue manuelle des familles de sources

La revue manuelle montre que le score automatique doit être corrigé par la provenance et la nature réelle des repos.

### MMS ulab v2

Les repos :

```text
espnet/mms_ulab_v2
amine-khelif/mms_ulab_v2
sundram1996/mms_ulab_v2
```

correspondent à la même famille de données. Le projet retient `espnet/mms_ulab_v2` comme référence canonique.

Le dataset annonce environ **8 900 heures de parole non étiquetée dans 4 023 langues**, cite Global Recordings Network comme source originale et est distribué sous `CC-BY-NC-SA-4.0`.

Les trois codes `sbd`, `stj`, `sym` sont déclarés. Comme l'audio est non transcrit, cette source est surtout intéressante pour de futurs travaux audio/ASR, pas comme corpus parallèle de traduction.

`coml/mmsulab` est un dérivé segmenté de `espnet/mms_ulab_v2` avec pyannote ; il ne doit pas être compté comme une source indépendante.

### `lbourdois/language_tags`

Ce dataset contient principalement des métadonnées de langue : noms, ISO, Glottocode, etc. Il est utile comme référence de nomenclature, mais pas comme corpus SAN à récolter. Son score HIGH automatique était donc trompeur.

### PanLex

`lbourdois/panlex` est un snapshot de PanLex avec environ 24,6 millions de lignes couvrant plus de 6 000 langues. La licence du snapshot est `CC0-1.0`.

Le repo expose notamment :

```text
vocab
639-3
639-3_english_name
var_code
english_name_var
```

Il est intéressant pour inventorier les formes `sbd`, `stj`, `sym`, mais ces lignes ne doivent pas être interprétées automatiquement comme des paires Français ↔ SAN.

### ChiKhaPo

`ec5ug/chikhapo` est un benchmark lexical plus directement utile. Ses lignes contiennent :

```text
source_word
target_translations
src_lang
tgt_lang
```

Les lexiques proviennent de PanLex, GATITOS et IDS. Le repo couvre environ 2 750 langues et notre inventaire a détecté `stj`.

Il faut maintenant vérifier quelles paires impliquant Matya existent réellement : par exemple `stj_eng`, `eng_stj`, ou une éventuelle paire avec `fra`.

### FineWeb2

`HuggingFaceFW/fineweb-2` contient explicitement un sous-ensemble :

```text
sbd_Latn
```

C'est un corpus web Common Crawl monolingue. Il peut fournir du texte continu en Southern Samo, mais la détection automatique de langue et le bruit du Web doivent être contrôlés.

### FinePDFs

`HuggingFaceFW/finepdfs` contient aussi :

```text
sbd_Latn
```

Il est distribué sous `ODC-By`. La fiche officielle signale que le code-switching est fréquent dans les PDF ; le contenu `sbd` devra donc être vérifié avant collecte massive.

`KefranAbg/finepdfs` est explicitement un duplicata de `HuggingFaceFW/finepdfs` et est exclu.

### GlotCC

`cis-lmu/GlotCC-V1` est retenu comme source canonique. `innadark/GlotCC-V1` est explicitement marqué comme duplicata et est exclu.

GlotCC contient du texte Common Crawl avec métadonnées de détection de langue et est distribué sous `CC0-1.0`.

### Taxi1500

`cis-lmu/Taxi1500-RawData` contient `sbd_Latn`. Sa fiche indique que les textes bruts sont issus d'un **corpus biblique**.

Cette ressource peut fournir du texte San, mais elle est fortement biaisée vers le domaine religieux et ses droits de contenu doivent être clarifiés avant réutilisation.

### FineFreq

`lgi2p/finefreq` contient des statistiques de fréquences de caractères dérivées de FineWeb2. Pour `sbd`, son manifeste indique 87 documents et 6 649 caractères dans sa table de statistiques.

Ce n'est pas un corpus de phrases. On le conserve pour une éventuelle analyse orthographique ultérieure.

### DCAD-2000

`openbmb/DCAD-2000` est un corpus web nettoyé couvrant plus de 2 000 langues. Son score automatique faible ne signifie pas qu'il est inutile : s'il possède réellement un sous-ensemble `sbd`, il peut être intéressant.

Sa licence étant déclarée de manière non standard dans l'inventaire, il reste à inspecter juridiquement avant acquisition.

## 5. Déduplication manuelle

La règle devient :

```text
1 repo Hugging Face ≠ 1 source indépendante
```

La revue manuelle est conservée dans :

```text
config/huggingface_sources.yaml
```

Ce fichier distingue :

```text
source canonique
miroir / duplicata
dérivé
métadonnées seulement
source à inspecter
```

Cela évite par exemple de télécharger trois fois MMS ulab v2 ou deux fois FinePDFs/GlotCC.

## 6. Inspection ciblée avant téléchargement

Commande :

```bash
python processors/inspect_huggingface_sources.py
```

Cette étape utilise l'API publique Dataset Viewer de Hugging Face (`/splits`, `/size`, `/filter`) sans télécharger les corpus complets.

Objectifs :

```text
FineWeb2  → détecter et mesurer sbd_Latn
FinePDFs  → détecter et mesurer sbd_Latn
GlotCC    → détecter et mesurer le sous-ensemble sbd
Taxi1500  → détecter et mesurer sbd_Latn
ChiKhaPo  → lister les paires de langues contenant stj
PanLex    → vérifier la présence de lignes sbd/stj/sym
MMS       → vérifier la présence filtrable de sbd/stj/sym
DCAD      → détecter un éventuel sous-ensemble sbd
```

Sortie :

```text
data/processed/huggingface/huggingface_source_inspection.json
```

Cette étape reste une inspection structurelle :

```text
config présente
    ≠
contenu correct en SAN
    ≠
validation linguistique
    ≠
autorisation ML
```

## 7. À ne pas confondre avec les modèles MMS

Des modèles comme `facebook/mms-1b-all` ou leurs conversions sont des **modèles ASR**, pas des datasets linguistiques à récolter dans cette phase.

Le support de `sbd` par MMS est intéressant pour la future feuille de route audio/ASR, mais il ne doit pas être compté comme une source de corpus Hugging Face.

## 8. Processus de sélection avant collector spécifique

Chaque source canonique est examinée selon :

```text
1. langue réellement présente
2. type : texte / traduction / audio / lexique / benchmark / métadonnées
3. volume réel pour sbd/stj/sym
4. source d'origine
5. licence du dataset
6. licence de la source originale
7. accès : public / gated
8. intérêt pour Langue SAN
9. duplication avec une source déjà collectée
```

Puis seulement les datasets intéressants auront un collector spécifique.

Règle :

```text
présent sur Hugging Face
    ≠
source indépendante
    ≠
licence claire
    ≠
donnée utile
    ≠
autorisée pour entraînement
```
