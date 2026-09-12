# RefLex CLLD — reconnaissance et résultat final de récolte

Cette note documente ce qui a réellement été observé et récolté depuis RefLex CLLD dans la branche `feat/data-ingestion`.

## 1. Source

RefLex CLLD (`https://reflex.clld.huma-num.fr/`) est une base lexicale africaine structurée et téléchargeable.

Version observée :

```text
RefLex CLLD 1.0
1 435 sources lexicales
927 845 enregistrements lexicaux
815 languoïdes
```

Citation :

```text
Segerer G., Flavier S., Zerner J., Doan-Rabier U., 2025.
RefLex: Reference Lexicon of Africa (CLLD), Version 1.0.
DOI: 10.34847/nkl.e3cbkjzm
```

Licence :

```text
CC-BY-NC-SA-4.0
```

Conséquence : les données récoltées peuvent servir à notre inventaire/recherche locale non commerciale sous les conditions de cette licence, mais elles ne sont pas automatiquement approuvées pour publication commerciale, corpus commercial ou entraînement commercial.

## 2. Cibles SAN

```text
sbd / maka  / sout2844
stj / matya / maty1235
sym / maya  / maya1281
```

Règle permanente : les trois variétés restent séparées.

## 3. Ce que `languages.csv` a réellement montré

Colonnes observées :

```text
Name
Family
Glottocode
Macroarea
Number of records in biggest source
Number of sources
Latitude
Longitude
```

Aucune colonne ID interne CLLD et aucun code ISO ne sont exportés.

Résolution :

```text
sbd / Maka
  glottocode configuré : sout2844
  résultat             : non trouvé dans l'index actuel
  décision             : ne pas attribuer un candidat approximatif

stj / Matya
  nom RefLex            : Samo Matya
  glottocode            : maty1235
  index                 : 2 764 fiches
  nombre de sources     : 1

sym / Maya
  nom RefLex            : Samo Maya
  glottocode            : maya1281
  index                 : 2 384 fiches
  nombre de sources     : 1
```

## 4. Pourquoi les premiers essais `/values` ont échoué

Les premiers probes supposaient que les données lexicales seraient directement disponibles via :

```text
/values
/values.csv
```

Observations réelles :

```text
/values.csv?... → HTTP 406
/values?language=<glottocode> → pas le bon filtre
/values?language=<id-page-clld> → 0 ligne
```

Le problème n'était pas l'absence des données Matya/Maya. Le transport réel de la page de langue était différent.

## 5. Découverte du vrai DataTable lexical

Le diagnostic de la page détail Matya :

```text
/languages/1472
```

a montré deux DataTables réels :

```text
/contributions?filterLanguage=562&filterReference=
/units?filterLanguage=562&filterSource=&filterReference=
```

Pour Matya :

```text
Contribution : Morris et al. 2011 : Matya
compteur contribution : 2 764
DataTable /units      : 2 743
filtre interne        : filterLanguage=562
```

Première unité observée pendant le probe :

```text
Original Form : -bra₂
POS           : NOM
Source        : Morris et al. 2011 : Matya
Glottocode    : maty1235
```

La page indique explicitement un téléchargement CSV des DataTables, limité à 10 000 lignes. Les volumes Matya/Maya restent sous cette limite.

## 6. Collecteur final utilisé

Collecteur :

```text
collectors/reflex_units.py
```

Principe :

```text
languages.csv
    ↓
résolution sûre par glottocode/nom
    ↓
page langue CLLD
    ↓
découverte dynamique du vrai sAjaxSource /units
    ↓
probe du compteur /units
    ↓
construction de /units.csv avec les mêmes filtres
    ↓
téléchargement RAW
    ↓
comparaison CSV ↔ compteur XHR
    ↓
metadata + SHA-256
```

Le collecteur ne normalise, ne corrige et ne déduplique aucune forme.

## 7. Résultats de récolte

### Matya — `stj`

Commande :

```bash
python collectors/reflex_units.py --iso stj
```

Résultat :

```text
2 743 lignes
9 colonnes
glottocode = maty1235
clld page id = 1472
filterLanguage = 562
```

### Maya — `sym`

Commande :

```bash
python collectors/reflex_units.py --iso sym
```

Résultat :

```text
2 378 lignes
9 colonnes
glottocode = maya1281
clld page id = 1473
filterLanguage = 563
```

### Total RefLex récolté

```text
stj : 2 743
sym : 2 378
-----------
TOTAL : 5 121 unités lexicales RAW
```

Colonnes :

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

## 8. Écarts de compteurs

RefLex présente deux types de compteurs qui ne sont pas identiques :

```text
Matya : index langue 2 764 → /units 2 743 → écart 21
Maya  : index langue 2 384 → /units 2 378 → écart 6
```

Décision du projet :

- ne pas inventer d'explication ;
- conserver l'écart dans les métadonnées ;
- considérer la récolte complète lorsque le CSV correspond exactement au DataTable `/units` réellement exporté.

## 9. QA technique

Processor :

```text
processors/qa_reflex_units.py
```

Commande :

```bash
python processors/qa_reflex_units.py --iso stj sym
```

Résultat utilisateur confirmé :

```text
stj : technical_ok=True
sym : technical_ok=True
all_technical_ok=True
```

Le QA vérifie notamment :

```text
présence des 9 colonnes attendues
volume CSV ↔ compteur XHR sauvegardé
SHA-256 ↔ metadata
ISO / variété / glottocode
formes et traductions vides
doublons exacts
formes+traductions répétées
sources observées
parties du discours observées
```

Il ne modifie jamais le RAW.

## 10. Statut final de RefLex pour cette phase

```text
sbd / Maka  : non récolté depuis RefLex actuel — absent de l'index
stj / Matya : collection_success + qa_passed
sym / Maya  : collection_success + qa_passed
```

Cela signifie uniquement : **récolte technique réussie**.

Cela ne signifie pas :

```text
validation linguistique
orthographe standard
publication approuvée
entraînement approuvé
usage commercial approuvé
```

## 11. Sources lexicales originales reliées au SAN

### Southern Samo / Maka — `sbd`

```text
Boo nεn sέwε san-fransi, fransi-san
[Lexique san–français, français–san]
SIL Burkina Faso, Ouagadougou, 2003, ~120 p.
```

Référence utilisée par ASJP pour `SOUTHERN_SAMO_SAN`.

### San Matya — `stj`

```text
Morris, P., Koussoubé, M., Seme, P. (2011)
Lexique San Matya avec guide d’orthographe
ANTBA, Tougan, Burkina Faso
```

RefLex expose la contribution `Morris et al. 2011 : Matya`.

### San Mayaa — `sym`

```text
Morris, P., Koussoubé, M., Seme, P. (2011)
Lexique San Mayaa avec guide d’orthographe
ANTBA, Tougan, Burkina Faso
```

## 12. Prochaine source : Berthelette 2001

Référence de travail :

```text
John Berthelette
Sociolinguistic survey report for the San (Samo) language
travail daté 2001
SIL Electronic Survey Reports 2002-005
≈ 75 pages
```

Intérêt :

```text
localités documentées
comparaison entre zones San
wordlists / données lexicales potentielles
contexte sociolinguistique
vérification des attributions de variété
```

Étapes avant ingestion :

```text
1. retrouver la notice et le fichier officiels SIL
2. vérifier la licence exacte de l'item
3. inspecter les annexes, tableaux et wordlists réels
4. définir le schéma RAW avec provenance par localité
5. ne jamais attribuer automatiquement Toma/Tougan à un ISO sans preuve
6. récolter
7. QA technique
```

## 13. Ordre après Berthelette

```text
1. Berthelette 2001
2. lexiques originaux SIL / ANTBA
3. applications Burkina Langues après clarification des droits
4. textes/audio ANTBA après clarification des droits
5. revenir à RefLex sbd seulement si une présence fiable est retrouvée
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
