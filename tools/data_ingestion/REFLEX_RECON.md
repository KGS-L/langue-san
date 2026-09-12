# RefLex CLLD et prochaines sources externes SAN

État de la reconnaissance après la phase Hugging Face. Cette note reste dans la branche `feat/data-ingestion` : elle sert à décider quoi récolter, sous quelles conditions et dans quel ordre.

## 1. RefLex CLLD — priorité actuelle

RefLex CLLD (`https://reflex.clld.huma-num.fr/`) est une base lexicale africaine structurée et téléchargeable. La version 1.0 annonce :

```text
1 435 sources lexicales
927 845 enregistrements lexicaux
815 languoïdes
```

Citation officielle :

```text
Segerer G., Flavier S., Zerner J., Doan-Rabier U., 2025.
RefLex: Reference Lexicon of Africa (CLLD), Version 1.0.
DOI: 10.34847/nkl.e3cbkjzm
```

Licence :

```text
CC-BY-NC-SA-4.0
```

Conséquence importante : la récolte est acceptable pour notre inventaire/recherche locale non commerciale, mais les données RefLex ne doivent pas être versées automatiquement dans un futur corpus commercial, dans un modèle commercial ou dans une distribution incompatible avec `NC`/`SA`.

Le framework CLLD fournit des exports CSV des tables et permet de conserver les données filtrées. Le collecteur cible :

```text
sbd / maka  / glottocode sout2844
stj / matya / glottocode maty1235
sym / maya  / glottocode maya1281
```

### Probe

```bash
python collectors/reflex_clld.py --probe-only
```

Le probe :

1. télécharge uniquement le petit index `languages.csv` ;
2. résout les identifiants internes RefLex par glottocode/ISO ;
3. demande une seule ligne au DataTable `values` afin de récupérer le nombre total de fiches pour chaque variété ;
4. ne télécharge pas les exports lexicaux complets.

Résumé local :

```text
data/raw/reflex/reflex_probe_summary.json
```

### Récolte complète, seulement après validation du probe

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

Le CSV est conservé brut. Pas de normalisation, translittération, déduplication ou fusion avec ASJP à cette étape.

## 2. Sources lexicales originales déjà reliées au SAN

### Southern Samo / Maka — `sbd`

Référence connue :

```text
Boo nεn sέwε san-fransi, fransi-san
[Lexique san–français, français–san]
SIL Burkina Faso, Ouagadougou, 2003, ~120 p.
```

Cette référence est utilisée par ASJP pour `SOUTHERN_SAMO_SAN` et est également signalée comme source RefLex dans la littérature. RefLex peut donc potentiellement nous donner une couverture beaucoup plus large que la petite liste comparative ASJP.

### San Matya — `stj`

Référence connue :

```text
Morris, P., Koussoubé, M., Seme, P. (2011)
Lexique San Matya avec guide d’orthographe
ANTBA, Tougan, Burkina Faso
```

ASJP l'utilise pour `SAMO_MATYA_2`. Le probe RefLex doit confirmer la présence et le volume exact dans RefLex avant toute conclusion.

### San Mayaa — `sym`

Référence connue :

```text
Morris, P., Koussoubé, M., Seme, P. (2011)
Lexique San Mayaa avec guide d’orthographe
ANTBA, Tougan, Burkina Faso
```

Cette ressource est explicitement signalée comme présente dans RefLex dans la littérature lexicographique consultée.

## 3. Berthelette — enquête sociolinguistique SAN

Référence :

```text
John Berthelette (2001)
Sociolinguistic survey report for the San (Samo) language
SIL Electronic Survey Reports 2002-005
75 pages
```

Le document est décrit comme contenant notamment une vue d'ensemble, des données sociolinguistiques et des listes lexicales pour plusieurs localités. Il distingue notamment Toma/Maka, plusieurs zones Matya et plusieurs zones Maya.

Cette source est très utile pour :

- vérifier l'attribution des variétés ;
- conserver les formes par localité ;
- comparer les formes anciennes avec les lexiques de 2003/2011 ;
- éviter de réduire une variété entière à une seule localité.

Avant ingestion, vérifier la notice SIL exacte et la licence attachée au fichier lui-même. Les archives SIL indiquent généralement CC-BY-NC-SA-4.0 sauf indication contraire, mais cette règle générale ne remplace pas la vérification de l'item.

## 4. Dictionnaires Android — très riches mais droits à clarifier

### San du Sud / Maka

Application : `San dictionnaire` — Burkina Langues.

La fiche publique annonce environ :

```text
2 220 mots
> 1 000 images
> 2 200 fichiers audio
San – Français – English
```

C'est potentiellement une source majeure, surtout pour l'audio lexical. Aucune licence de réutilisation des contenus n'a été identifiée dans la fiche publique. Donc :

```text
status = rights_review_required
pas d'extraction APK automatique
pas de redistribution
pas d'entraînement
```

### San Matya

Application : `San Matya de A-Z` — Burkina Langues.

La fiche publique annonce :

```text
2 576 entrées
685 images
Lexique San Matya – Français
```

Même règle : inventorier et contacter le détenteur/développeur avant extraction massive ou réutilisation.

## 5. ANTBA — textes bibliques Matya/Mayaa

ANTBA confirme ses projets de traduction San Maya et San Matya autour de Tougan. Les Nouveaux Testaments San Mayaa et Matyaa ont été dédiés le 4 mai 2024 et des applications mobiles existent.

Ces textes seraient précieux pour l'alignement de phrases et l'audio, mais ils sont fortement biaisés par le domaine religieux et les droits de traduction biblique doivent être clarifiés avant copie/redistribution/entraînement.

Donc :

```text
inventaire : oui
scraping automatique : non pour l'instant
corpus principal généraliste : non
```

## 6. Ordre recommandé après RefLex

```text
1. RefLex CLLD
   → probe sbd/stj/sym
   → récolte ciblée si le probe est bon
   → QA + comparaison avec ASJP/PanLex/ChiKhaPo

2. Berthelette 2001
   → récupérer la notice/fichier officiel SIL
   → vérifier la licence de l'item
   → extraire les wordlists avec provenance par localité

3. Lexiques originaux SIL/ANTBA
   → chercher les PDF/exports officiels ou obtenir l'autorisation
   → ne pas supposer qu'une application gratuite autorise l'extraction

4. Applications Burkina Langues
   → contacter pour autorisation de réutilisation des entrées et surtout des audios

5. Textes/audio bibliques ANTBA
   → seulement après clarification des droits
   → conserver comme domaine religieux séparé
```

## Règle permanente

```text
accessible publiquement
  ≠ librement réutilisable

gratuit
  ≠ open data

dans RefLex/ASJP/Hugging Face
  ≠ source indépendante

récolté
  ≠ validé linguistiquement
  ≠ standard orthographique
  ≠ approuvé pour usage commercial
  ≠ approuvé pour entraînement
```
