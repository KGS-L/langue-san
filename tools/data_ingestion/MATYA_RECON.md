# San Matya / `stj` — récupération, QA et analyse de chevauchement

Cette note documente la piste San Matya après récupération autorisée du corpus lexical Lexique Pro.

## 1. Source historique

Référence utilisée par ASJP pour `SAMO_MATYA_2` et reliée à la contribution Matya de RefLex :

```text
Morris, Pamela; Sama, François; Sama, Jérémie; Drabo, Jean-Pierre. 2011.
Lexique San Matya avec guide d'orthographe.
Tougan, Burkina Faso:
Association nationale pour la traduction de la Bible et alphabétisation (ANTBA).
```

Variété :

```text
San Matya / Samo Matya
ISO 639-3 : stj
Glottocode : maty1235
zone : Tougan et environs
```

RefLex expose une contribution `Morris et al. 2011 : Matya`. La forte proximité observée plus bas entre RefLex et le LIFT récupéré est compatible avec une origine documentaire commune ou très proche, mais l'analyse de chaînes seule ne suffit pas à démontrer l'identité complète des deux ressources.

## 2. Ressource moderne et installateur Lexique Pro

Ressource identifiée :

```text
nom : San Matya de A-Z
développeur : Burkina Langues
package : com.matya.san.lexique
volume annoncé : 2 576 entrées
images annoncées : 685
```

Installateur Windows analysé :

```text
fichier       : San Matya - Lexique Pro Setup.exe
format        : PE32 GUI Intel 80386 / Windows
installateur  : Inno Setup 5.3.10 Unicode
SHA-256       : 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
```

Extraction statique :

```text
fichiers totaux : 734
images           : 695
idx              : 3
db               : 3
lpLiftEnc         : 1
lpConfigEnc       : 1
lift-ranges       : 1
audio             : 0
```

Le RAW original est conservé. Les fichiers protégés d'origine ne sont pas remplacés par les fichiers récupérés.

## 3. Autorisation

Une autorisation écrite a été obtenue le 13 septembre 2026 auprès de la personne ayant déclaré disposer de l'autorité nécessaire sur les données.

Le périmètre explicitement confirmé couvre :

```text
analyse des fichiers de l'application
analyse des fichiers lpLiftEnc
récupération / reconstruction / sauvegarde
constitution d'un corpus linguistique
recherche
entraînement et évaluation de modèles de traduction
entraînement et évaluation d'autres modèles ML
```

Conditions confirmées :

```text
open source
non commercial
```

Le message d'autorisation original et ses métadonnées restent archivés localement et ne sont pas publiés dans le dépôt.

Cette autorisation ne doit pas être transformée automatiquement en licence standard de redistribution publique. Le projet conserve donc :

```text
publication_approved    = false
commercial_use_approved = false
```

jusqu'à obtention éventuelle d'une licence explicite de redistribution/publication.

## 4. Récupération du corpus

La récupération autorisée du corpus principal a réussi.

Résultat canonique :

```text
format LIFT        : XML valide
version LIFT       : 0.13
producer           : SIL.FLEx 9.1.24.1383
entrées lexicales  : 2 576
sens XML           : 2 790
entrées sans sens  : 19
lignes export sens : 2 809
audio              : 0
```

Le nombre de `2 809` lignes dans l'export de sens s'explique par :

```text
2 790 sens XML
+ 19 entrées sans élément <sense>
= 2 809 lignes exportées
```

SHA-256 du LIFT récupéré :

```text
8dda95167aa19699e878a2964e5b01e4ded0d1a0d82b304e0a28806db81ff279
```

Le nombre de 2 576 entrées correspond exactement au nombre d'identifiants de l'index Matya de la distribution.

La méthode technique détaillée de récupération reste dans la documentation privée locale ignorée par Git. La documentation publique conserve uniquement la provenance, les contrôles d'intégrité, les résultats et le périmètre d'autorisation.

## 5. Données lexicales observées

Analyse du LIFT récupéré :

```text
entrées                    : 2 576
formes lexicales uniques   : 2 188
références d'illustrations : 685
fichiers audio             : 0
```

Les 685 références d'illustrations correspondent au volume annoncé par l'application moderne. L'archive d'installation contient 695 fichiers image au total ; toutes les images du package ne sont donc pas nécessairement des illustrations lexicales référencées.

## 6. Comparaison avec les autres corpus Matya

Sources comparées :

```text
RefLex
ChiKhaPo
PanLex
ASJP
```

Comparaison stricte `NFC + casefold + espaces condensés` :

```text
Corpus récupéré                    : 2 188 formes uniques
RefLex                             : 2 503 formes uniques
ChiKhaPo                           :   406 formes uniques
PanLex                             :   408 formes uniques
ASJP                               :   111 formes uniques

Chevauchement avec l'union externe : 1 651
Absentes exactement de l'union     :   537
Part strictement absente           : 24,54 %
```

Détail principal :

```text
LIFT récupéré ↔ RefLex
chevauchement exact : 1 630
couverture du LIFT  : 74,50 %
couverture RefLex   : 65,12 %
```

RefLex explique donc l'essentiel du chevauchement. PanLex et ChiKhaPo se recouvrent eux-mêmes presque complètement sur les formes Matya déjà récoltées, ce qui confirme qu'il ne faut pas additionner naïvement les volumes de sources agrégées.

## 7. Analyse des quasi-doublons

Les 537 formes strictement absentes ont ensuite été comparées avec une normalisation technique plus souple, sans fusion automatique.

Résultat :

```text
candidats nouveaux stricts         : 537
variantes ponctuation / tirets      :   3
variantes tons / diacritiques       : 122
quasi-doublons techniques total     : 125
dont même gloss français            : 113
restantes après normalisation douce : 412
```

Les 412 restantes représentent donc :

```text
412 formes candidates réellement nouvelles
après déduplication technique conservatrice
```

Elles ne sont PAS déclarées comme 412 nouveaux mots linguistiquement confirmés. Une validation linguistique humaine reste nécessaire.

## 8. Compteur RAW

Le corpus récupéré ajoute ses 2 576 entrées au compteur de collecte RAW.

```text
ancien total RAW : 16 625
Matya récupéré   :  2 576
--------------------------
nouveau total    : 19 201
```

Ce compteur représente des occurrences/entrées de sources et conserve volontairement les chevauchements. Il ne représente ni le nombre de mots uniques, ni le nombre de paires d'entraînement finales, ni le nombre de données linguistiquement validées.

## 9. Fichiers locaux

RAW / récupération :

```text
data/raw/san_matya_lexique_pro/
├── San Matya - Lexique Pro Setup.exe
├── extracted/
├── recovered/
└── recovery_archive/
```

Résultats traités :

```text
data/processed/san_matya_lexique_pro/
├── recovery/
└── overlap/
```

Ces dossiers restent ignorés par Git.

Analyseurs reproductibles suivis dans Git :

```text
tools/data_ingestion/processors/compare_matya_recovered_sources.py
tools/data_ingestion/processors/analyze_matya_near_duplicates.py
```

## 10. Statut final

```text
discovery_historical              = confirmed
variety                           = matya
iso_639_3                         = stj
glottocode                        = maty1235
historical_year                   = 2011
historical_publisher              = ANTBA

modern_windows_installer          = inspected
static_extraction                 = completed
written_authorization             = obtained
authorization_scope               = open_source_noncommercial_research_and_ml

recovery_success                  = true
lift_valid                        = true
entry_count                       = 2576
sense_count                       = 2790
entries_without_sense             = 19
exported_sense_rows               = 2809
unique_lexical_forms              = 2188
illustration_references           = 685
audio_count                       = 0
technical_qa                      = passed

exact_external_overlap            = 1651
strict_new_candidates             = 537
near_duplicate_candidates         = 125
near_duplicate_same_french_gloss  = 113
remaining_new_candidates          = 412
overlap_analysis                  = completed

linguistic_validation             = pending
publication_approved              = false
training_approved                 = false
commercial_use_approved           = false

technical_ingestion_status        = closed
```

`training_approved=false` signifie que la qualité linguistique et le dataset final n'ont pas encore été validés par le pipeline du projet. Cela ne contredit pas l'autorisation reçue pour l'entraînement ML non commercial.

## 11. Décision

La piste technique Matya est clôturée pour la phase `data_ingestion`.

Aucune autre opération de récupération ou de comparaison technique n'est nécessaire pour continuer la collecte externe. Les 412 candidats restants seront repris plus tard lors de la validation linguistique et de la construction du dataset final.

Prochain bloc prioritaire :

```text
San Maya / sym
```
