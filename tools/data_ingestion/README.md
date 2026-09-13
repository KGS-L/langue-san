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
8. Le compteur RAW mesure la collecte par source, pas le nombre de formes lexicales uniques.

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

### Lexique Pro Matya récupéré

```text
entrées LIFT                  : 2 576
sens XML                      : 2 790
formes lexicales uniques      : 2 188
références d'illustrations    : 685
fichiers audio                : 0
technical_ok                  : True
```

## Volume RAW opérationnel

```text
ASJP               341
Ainsi sois-je      125
Taxi1500          7919
ChiKhaPo           872
PanLex             420
FinePDFs             7
GlotCC               2
FineWeb2              4
RefLex             5121
Berthelette        1814
Lexique Pro Matya  2576
----------------------
TOTAL             19201 occurrences/lignes/entrées RAW
```

Ce total est un compteur de collecte contenant des chevauchements, licences différentes et données non validées linguistiquement.

## Maka `sbd`

Le lexique historique 2003 `Boo nɛn sɛwɛ san-fransi, fransi-san` est confirmé bibliographiquement, mais aucun exemplaire numérique officiel n'a été retrouvé rapidement.

La ressource moderne San du Sud/Webonary/app est confirmée, mais les droits de réutilisation massive restent à clarifier. On ne reste pas bloqué dessus.

## Matya `stj` — récupération et analyse clôturées

Référence historique :

```text
Morris, Pamela; Sama, François; Sama, Jérémie; Drabo, Jean-Pierre. 2011.
Lexique San Matya avec guide d'orthographe.
Tougan, Burkina Faso: ANTBA.
```

La piste Lexique Pro Matya a été rouverte après obtention d'une autorisation écrite permettant l'analyse et la récupération du corpus ainsi que son utilisation dans un cadre open source non commercial pour la recherche et l'entraînement/évaluation de modèles ML.

Récupération validée :

```text
LIFT valide                    : oui
version                        : 0.13
producer                       : SIL.FLEx 9.1.24.1383
entrées                        : 2 576
sens XML                       : 2 790
entrées sans sens              : 19
lignes export sens             : 2 809
formes lexicales uniques       : 2 188
références illustrations       : 685
audio                          : 0
```

Analyse du chevauchement avec RefLex / ChiKhaPo / PanLex / ASJP :

```text
chevauchement exact externe    : 1 651
formes strictement absentes    :   537
quasi-doublons techniques      :   125
dont même gloss français       :   113
candidats restants             :   412
```

Les `412` restantes sont des **candidats techniques** et non des nouveaux mots linguistiquement confirmés.

Le RAW conserve les 2 576 entrées complètes et porte le compteur opérationnel global à `19 201`.

Droits et statut :

```text
autorisation écrite            : oui
cadre                          : open source / non commercial
recherche + ML                 : explicitement autorisés
publication publique dataset   : non approuvée automatiquement
usage commercial               : non approuvé
training_approved pipeline     : false
validation linguistique        : pending
technical_ingestion_status     : closed
```

La récupération technique, le QA et l'analyse de chevauchement sont terminés. La validation linguistique et la construction du dataset d'entraînement seront traitées plus tard.

## Source active suivante — Maya `sym`

On passe maintenant à la source originale Maya. Objectifs :

```text
1. identifier la référence primaire utilisée par RefLex / autres agrégateurs
2. retrouver PDF/LIFT/catalogue ou ressource moderne fiable
3. vérifier droits/licence
4. récolter uniquement si accès et droits le permettent
5. comparer ensuite avec RefLex sym=2 378 et Berthelette sym=912
```

Glottocode de travail : `maya1281`, ISO 639-3 : `sym`.

## Ordre des prochaines sources

```text
1. Source primaire Maya / sym
2. Morse 1967 — bibliographie et droits exacts
3. Maka moderne Webonary/app — reprendre dès clarification des droits
4. Autres ressources Burkina Langues / ANTBA — droits vérifiés source par source
```

Matya n'est plus une piste de collecte active : il ne reviendra que lors de la validation linguistique et de la construction du dataset final.

## Règle finale de cette branche

```text
accessible ≠ librement réutilisable
gratuit ≠ open data
présent dans un agrégateur ≠ source indépendante
récolté ≠ validé linguistiquement
récolté ≠ approuvé pour publication / entraînement / usage commercial
```
