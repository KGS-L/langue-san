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

## Sources déjà travaillées

### ASJP v21

```text
RAW : 341 occurrences
sbd : 37
stj : 129
sym : 175
licence : CC-BY-4.0
```

### Ainsi sois-je

```text
occurrences récupérées : 125
paires uniques         : 124
copyright               : All Rights Reserved
variété exacte          : inconnue
```

### Hugging Face

```text
Taxi1500 / sbd : 7 919
ChiKhaPo / stj : 872
PanLex          : 420
FinePDFs / sbd  : 7
GlotCC / sbd    : 2
FineWeb2 / sbd  : 4
```

### RefLex CLLD

```text
stj / Matya : 2 743
sym / Maya  : 2 378
TOTAL       : 5 121
QA          : technical_ok=True
```

### Berthelette 2001

```text
concepts                       : 220 (012–231)
occurrences                    : 1 814
sbd / Maka                     : 223
stj / Matya                    : 679
sym / Maya                     : 912
anomalies structurelles        : 0
formes source vides conservées : 8
technical_ok                   : True
```

Berthelette est techniquement clôturé. Les concepts `001–011` n'ont pas été retrouvés dans le PDF disponible et ne bloquent plus la récolte.

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

Ce total est un compteur de collecte contenant des chevauchements, licences différentes et données non validées linguistiquement.

## Maka `sbd`

Le lexique historique 2003 `Boo nɛn sɛwɛ san-fransi, fransi-san` est confirmé bibliographiquement, mais aucun exemplaire numérique officiel n'a été retrouvé rapidement.

La ressource moderne San du Sud/Webonary/app est confirmée, mais les droits de réutilisation massive restent à clarifier. On ne reste pas bloqué dessus.

## Matya `stj` — inspection Lexique Pro clôturée

Référence historique :

```text
Morris, Pamela; Sama, François; Sama, Jérémie; Drabo, Jean-Pierre. 2011.
Lexique San Matya avec guide d'orthographe.
Tougan, Burkina Faso: ANTBA.
```

Un installateur Windows Lexique Pro a été récupéré et extrait statiquement :

```text
San Matya - Lexique Pro Setup.exe
Inno Setup 5.3.10 Unicode
SHA-256: 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
```

Résultat final :

```text
734 fichiers
695 images
0 audio
lpLiftEnc / lpConfigEnc : binaires protégés/opaques
lift-ranges             : XML LIFT lisible, ranges uniquement
English.idx             : glosses anglais → ids
French.idx              : glosses français → ids
San Matya.idx           : 2 576 lignes = 2 576 ids numériques uniquement
```

L'index Matya ne contient aucune forme Matya lisible : `14 346` octets, `2 576` lignes, `0` tabulation, `0` octet NUL. Les encodages UTF-8/CP1252/Latin-1 donnent seulement les IDs. Il est donc impossible de reconstruire les paires Matya ↔ français/anglais depuis les `.idx` seuls sans accéder au corpus protégé `lpLiftEnc`.

La licence présente concerne **Lexique Pro**, pas les données lexicales. Aucun nouveau volume n'est ajouté au compteur RAW. Cette piste est fermée jusqu'à obtention d'un PDF/LIFT original ou d'une autorisation/export officielle.

Une recherche courte confirme via ASJP la référence Morris et al. 2011, mais n'a pas retrouvé de copie numérique primaire publique ni de licence explicite. citeturn301947search4

## Source active suivante — Maya `sym`

On passe maintenant à la source originale Maya. Objectifs :

```text
1. identifier la référence primaire utilisée par RefLex / autres agrégateurs
2. retrouver PDF/LIFT/catalogue ou ressource moderne fiable
3. vérifier droits/licence
4. récolter uniquement si accès et droits le permettent
5. comparer ensuite avec RefLex sym=2 378 et Berthelette sym=912
```

Glottolog confirme `Maya Samo`, ISO `sym`, Glottocode `maya1281`. citeturn350898search1

## Ordre des prochaines sources

```text
1. Source primaire Maya / sym
2. Morse 1967 — bibliographie et droits exacts
3. Maka moderne Webonary/app — reprendre dès clarification des droits
4. Matya Morris et al. 2011 — reprendre uniquement si source primaire/permission obtenue
5. Autres ressources Burkina Langues / ANTBA — droits vérifiés source par source
```

## Règle finale de cette branche

```text
accessible ≠ librement réutilisable
gratuit ≠ open data
présent dans un agrégateur ≠ source indépendante
récolté ≠ validé linguistiquement
récolté ≠ approuvé pour publication / entraînement / usage commercial
```
