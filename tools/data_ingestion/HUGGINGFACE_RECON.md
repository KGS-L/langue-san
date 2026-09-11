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

Attention : Hugging Face précise que ce tableau compte les datasets **monolingues ou peu multilingues** (5 langues ou moins). Les grands datasets multilingues peuvent donc ne pas apparaître dans ces nombres.

Ces nombres sont seulement un signal de découverte et ne doivent pas être interprétés comme 29 sources indépendantes et directement exploitables.

## 2. Méthode d'inventaire du projet

Le script :

```bash
python collectors/huggingface_inventory.py
```

interroge les métadonnées du Hub avec les filtres :

```text
language:sbd
language:stj
language:sym
```

Il ne télécharge aucun contenu linguistique.

Il produit :

```text
data/raw/huggingface/
├── huggingface_san_inventory.json
├── huggingface_san_inventory.csv
└── huggingface_san_inventory_summary.json
```

Les métadonnées conservées incluent notamment :

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

si aucune licence n'est déclarée.

Aucun repo n'est automatiquement approuvé pour publication ou entraînement.

## 3. Candidats déjà repérés pendant la reconnaissance web

### `lgi2p/finefreq`

URL : `https://huggingface.co/datasets/lgi2p/finefreq`

Licence déclarée : `cc-by-4.0`.

FineFreq contient des statistiques de fréquences de caractères dérivées de FineWeb/FineWeb2. Son manifeste contient explicitement :

```text
sbd,sbd_Latn,Latn,Southern Samo,87,6649,"FineWeb2, v2.1.0",2,2013,2014,DATA/sbd_Latn
```

Ce dataset est intéressant pour étudier :

```text
alphabet / caractères observés
fréquences de caractères
signaux d'orthographie web
```

mais il ne fournit pas directement un corpus de phrases ou un dictionnaire Français ↔ San. Il est donc **faible priorité pour la traduction**, mais potentiellement utile plus tard pour l'analyse orthographique.

Aucune entrée `stj` ou `sym` n'a été retrouvée dans le manifeste consulté lors de cette reconnaissance.

### `CohereLabs/xP3x`

URL : `https://huggingface.co/datasets/CohereLabs/xP3x`

La collection xP3x annonce 277 langues et plusieurs tâches NLP. La collection est publiée sous Apache-2.0, mais sa fiche précise que **les datasets individuels intégrés peuvent avoir des licences différentes**.

Un fichier de langues présent dans le repo reconnaît les trois codes :

```text
sbd → Southern Samo
stj → Matya Samo
sym → Maya Samo
```

Cela suffit pour le conserver comme **candidat à inspecter**, mais pas pour conclure que les trois variétés possèdent un volume utile de données dans xP3x. Il faudra vérifier les configs/sous-ensembles réellement présents et remonter à leurs sources d'origine.

## 4. À ne pas confondre avec les modèles MMS

Des modèles comme `facebook/mms-1b-all` ou leurs conversions sont des **modèles ASR**, pas des datasets linguistiques à récolter dans cette phase.

Le support de `sbd` par MMS est intéressant pour la future feuille de route audio/ASR, mais il ne doit pas être compté comme une source de corpus Hugging Face.

## 5. Processus de sélection après inventaire

Après exécution du script d'inventaire, chaque repo sera classé manuellement selon :

```text
1. langue réellement présente
2. type : texte / traduction / audio / lexique / benchmark / métadonnées
3. volume réel pour sbd/stj/sym
4. source d'origine
5. licence du dataset
6. licence de la source originale
7. accès : public / gated
8. intérêt pour Langue SAN
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
