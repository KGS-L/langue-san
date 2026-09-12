# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide général : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)  
> État Hugging Face : [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md)  
> Reconnaissance RefLex : [`REFLEX_RECON.md`](REFLEX_RECON.md)  
> Berthelette : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md)

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

PDF officiel local :

```text
data/raw/berthelette/SILESR2002_005.pdf
SHA-256 : efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
```

Le PDF contient 73 pages physiques, avec texte extractible sur 73/73. Les formes SAN de la wordlist sont encodées en polices Type3 legacy ; le mapping SIL IPA93 → Unicode a été résolu sans OCR avec une couverture de 100 %.

La page 64 confirme :

```text
Toma       → maka  → sbd
Kouy       → matya → stj
Kassoum    → matya → stj
Toéni      → matya → stj
Bounou     → maya  → sym
Kiembara   → maya  → sym
Bangassogo → maya  → sym
Lankoué    → maya  → sym
```

Extraction finale des concepts visibles `012–231` :

```text
concepts                       : 220
occurrences                    : 1 814
sbd / Maka                     : 223
stj / Matya                    : 679
sym / Maya                     : 912
anomalies structurelles        : 0
formes source vides conservées : 8
groupes multi-formes           : 53
technical_ok                   : True
```

Les concepts `001–011` n'ont pas été retrouvés dans le PDF disponible. Ils sont documentés comme absents et ne bloquent plus la récolte.

ASJP `MAYA_SAMO / sym` cite Berthelette 2001 comme source : les occurrences sont conservées dans le compteur brut, mais ne doivent pas être interprétées comme deux sources indépendantes.

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

Ce total est un **compteur de collecte**. Il contient des chevauchements, des licences différentes, des domaines spécialisés et des données non validées linguistiquement.

## Source active suivante — lexique San Maka / Southern Samo `sbd`

Priorité :

```text
Boo nɛn sɛwɛ san-fransi, fransi-san
[Lexique san–français, français–san]
SIL Burkina Faso, 2003
Southern Samo / San Maka / sbd
```

ASJP `SOUTHERN_SAMO_SAN` cite cette ressource comme source. Elle est prioritaire car RefLex ne fournit actuellement pas de récolte `sbd` équivalente.

Étape immédiate : retrouver une notice/source originale ou un exemplaire numérique fiable, clarifier les droits et l'accès, puis récolter si autorisé. Si l'accès numérique original n'est pas retrouvé rapidement, documenter le blocage et passer à la source originale suivante au lieu de rester bloqué.

## Ordre des prochaines sources

```text
1. Lexique San Maka / Southern Samo 2003 — accès + droits + récolte si disponible
2. Source Matya originale citée par RefLex (Morris et al. 2011)
3. Source Maya originale citée par RefLex
4. Morse 1967 — bibliographie et droits exacts
5. Burkina Langues / ANTBA — seulement après clarification des droits
```

## Règle finale de cette branche

```text
accessible ≠ librement réutilisable
gratuit ≠ open data
présent dans un agrégateur ≠ source indépendante
récolté ≠ validé linguistiquement
récolté ≠ approuvé pour publication / entraînement / usage commercial
```
