# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide général : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)  
> État Hugging Face : [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md)  
> Reconnaissance RefLex : [`REFLEX_RECON.md`](REFLEX_RECON.md)  
> Berthelette : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md)  
> Maka : [`MAKA_LEXICON_RECON.md`](MAKA_LEXICON_RECON.md)  
> Matya : [`MATYA_RECON.md`](MATYA_RECON.md)

## Périmètre de `feat/data-ingestion`

```text
source externe
    ↓
vérification provenance / droits
    ↓
collector / export / extraction ciblée
    ↓
data/raw/ ou data/processed/ local
    ↓
QA technique / statistiques / comparaison
    ↓
SOURCE RÉCOLTÉE
```

La validation humaine, `standard_san`, l'import applicatif et les décisions finales de publication/ML sont traités séparément.

## Variétés — règle absolue

```text
San Maka / San du Sud : sbd
San Matya             : stj
San Maya              : sym
```

Une localité n'est jamais utilisée seule pour attribuer une variété, sauf lorsqu'une source explicite documente elle-même la correspondance et que cette provenance est conservée.

## Principes

1. Ne jamais fusionner automatiquement `sbd`, `stj` et `sym`.
2. Conserver provenance, URL, licence/droits, identifiants source et date d'acquisition.
3. Une ressource publique n'est pas automatiquement réutilisable ou publiable.
4. Le RAW reproduit la source : pas de déduplication silencieuse, pas de correction linguistique implicite.
5. Les doublons, blancs et anomalies sont signalés séparément par le QA.
6. `data/raw/` et `data/processed/` restent locaux et ne sont pas publiés automatiquement.
7. `récolté` ≠ `validé linguistiquement` ≠ `approuvé pour publication` ≠ `approuvé pour entraînement`.

## Installation locale

Depuis `tools/data_ingestion/` :

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
pytest tests
```

## Sources déjà travaillées

### ASJP v21

```text
licence : CC-BY-4.0
RAW     : 341 occurrences
sbd     : 37
stj     : 129
sym     : 175
```

### Ainsi sois-je

```text
copyright site         : All Rights Reserved
variété exacte         : inconnue
occurrences récupérées : 125
paires uniques         : 124
publication / ML       : non approuvés
```

### Hugging Face — bloc texte/lexique ciblé terminé

```text
Taxi1500 / sbd : 7 919
ChiKhaPo / stj : 872
PanLex          : 420
FinePDFs / sbd  : 7
GlotCC / sbd    : 2
FineWeb2 / sbd  : 4
```

PanLex et ChiKhaPo se chevauchent fortement sur `stj`; leurs volumes ne représentent pas des sources indépendantes.

### RefLex CLLD — Matya et Maya

```text
stj / Matya : 2 743
sym / Maya  : 2 378
TOTAL       : 5 121
QA          : technical_ok=True
```

`San Maka / sbd / sout2844` n'a pas été trouvé dans l'index RefLex actuel.

### Berthelette 2001 — récolte lexicale techniquement clôturée

```text
concepts                       : 220 (012–231)
occurrences                    : 1 814
sbd / Maka                     : 223
stj / Matya                    : 679
sym / Maya                     : 912
anomalies structurelles        : 0
formes source vides conservées : 8
groupes multi-formes           : 53
technical_ok                   : True
```

Les concepts `001–011` n'ont pas été retrouvés dans le PDF disponible et ne bloquent plus la récolte. Le décodage des polices Type3 SIL IPA93 a atteint 100 % sans OCR.

## Volume RAW opérationnel

```text
ASJP            341
Ainsi sois-je   125
Taxi1500       7919
ChiKhaPo        872
PanLex          420
FinePDFs          7
GlotCC            2
FineWeb2           4
RefLex          5121
Berthelette     1814
-------------------
TOTAL          16625 occurrences/lignes RAW
```

Ce total est un **compteur de collecte** : il contient des chevauchements, des licences différentes, des domaines spécialisés et des données non validées linguistiquement.

## Maka `sbd` — reconnaissance mise en attente sur les droits

Le lexique historique 2003 `Boo nɛn sɛwɛ san-fransi, fransi-san` est confirmé bibliographiquement, mais aucun exemplaire numérique officiel de cette édition n'a été retrouvé rapidement.

Une ressource moderne Southern San existe : Webonary `Dictionnaire San du sud`, une application `San dictionnaire` et une version Windows. L'application annonce environ **2 220 mots**, plus de **1 000 images** et plus de **2 200 fichiers audio**. Le Webonary affiche `© 2021 SIL International®`, sans licence de réutilisation explicite retrouvée à ce stade.

Donc :

```text
Maka moderne = ressource confirmée
bulk harvest = différé
raison        = droits/licence non clarifiés
```

On ne reste pas bloqué dessus.

## Source active — Matya `stj`

Référence primaire cible :

```text
Morris, Pamela; Sama, François; Sama, Jérémie; Drabo, Jean-Pierre. 2011.
Lexique San Matya avec guide d'orthographe.
Tougan, Burkina Faso: ANTBA.
```

Cette source est citée par ASJP `SAMO_MATYA_2` et correspond à la source amont Matya documentée par RefLex.

Une application moderne `San Matya de A-Z` de Burkina Langues est aussi confirmée : **2 576 entrées**, **685 images**, zone de Tougan. Sa licence de réutilisation n'est pas encore confirmée ; elle est traitée séparément de l'ouvrage 2011.

Un installateur Windows local a été identifié comme **Inno Setup 5.3.10 Unicode** :

```text
San Matya - Lexique Pro Setup.exe
SHA-256: 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
```

Le listing `innoextract -l` confirme que l'installateur embarque réellement un jeu de données Lexique Pro :

```text
San du Nord Matya.lpLiftEnc       ~1.8 MiB
San du Nord Matya.lpConfigEnc     ~42.1 KiB
San du Nord Matya.lift-ranges     ~1.15 MiB
index San Matya / French / English
nombreuses images lexicales
```

Le corpus principal semble donc encapsulé/protégé au format `lpLiftEnc`. On ne tente pas de contourner une protection. Étape immédiate : extraction statique avec `innoextract`, inventaire des fichiers, inspection des entêtes/formats, de `licence.txt`, et vérification de la présence éventuelle d'audio. Si une exportation standard Lexique Pro ou une source originale non protégée existe, elle sera privilégiée.

## Ordre des prochaines sources

```text
1. Extraire statiquement San Matya - Lexique Pro Setup.exe et inventorier son contenu
2. Morris et al. 2011 — source primaire Matya / droits
3. Source Maya originale citée par RefLex
4. Morse 1967 — bibliographie et droits exacts
5. Maka moderne Webonary/app — reprendre dès clarification des droits
6. Autres ressources Burkina Langues / ANTBA — droits vérifiés source par source
```

## Règle finale de cette branche

```text
accessible ≠ librement réutilisable
gratuit ≠ open data
présent dans un agrégateur ≠ source indépendante
récolté ≠ validé linguistiquement
récolté ≠ approuvé pour publication / entraînement / usage commercial
```
