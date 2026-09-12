# Data ingestion

Ce dossier regroupe les outils utilisés pour **découvrir, récupérer, inventorier et comparer des ressources linguistiques externes** destinées au projet Langue SAN.

> Guide général : [`GUIDE_DATA_INGESTION.md`](GUIDE_DATA_INGESTION.md)  
> État Hugging Face : [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md)  
> Reconnaissance RefLex : [`REFLEX_RECON.md`](REFLEX_RECON.md)  
> Reconnaissance Berthelette : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md)

## Périmètre de `feat/data-ingestion`

Cette branche sert à la **récolte technique des sources externes**.

```text
source externe
    ↓
vérification provenance / droits
    ↓
collector / scraper / export ciblé
    ↓
data/raw/                        # local, non commité
    ↓
QA technique / statistiques / comparaison
    ↓
SOURCE RÉCOLTÉE
```

La transformation ultérieure en source linguistique officielle du projet, la validation humaine, `standard_san`, l'import applicatif et l'autorisation ML seront traités séparément.

## Variétés — règle absolue

```text
San Maka / San du Sud : sbd
San Matya             : stj
San Maya              : sym
```

Toma et Tougan sont uniquement des indices géographiques. Une localité ne valide jamais automatiquement une variété, sauf lorsqu'une source linguistique/bibliographique explicite documente elle-même cette correspondance et que cette provenance est conservée.

## Principes

1. Ne jamais fusionner automatiquement `sbd`, `stj` et `sym`.
2. Conserver provenance, URL, licence/droits, identifiants source et date d'acquisition.
3. Une donnée accessible publiquement n'est pas automatiquement réutilisable ou publiable.
4. Le RAW reproduit la source : pas de déduplication silencieuse, pas de translittération, pas de correction linguistique.
5. Les doublons, blancs et anomalies sont signalés séparément par le QA.
6. `data/raw/` et `data/processed/` restent locaux et ne sont pas publiés automatiquement.
7. Une similarité graphique n'est jamais une validation linguistique.
8. `récolté` ≠ `validé linguistiquement` ≠ `approuvé pour publication` ≠ `approuvé pour entraînement`.

## Installation locale

Depuis `tools/data_ingestion/` :

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

À chaque session :

```bash
source .venv/bin/activate
pytest tests
```

## État des sources déjà travaillées

### ASJP v21 — récolté

```text
licence : CC-BY-4.0
RAW     : 341 entrées
sbd     : 37
stj     : 129
sym     : 175
```

### Ainsi sois-je — récolte locale candidate

```text
copyright site              : All Rights Reserved
variété exacte              : inconnue
compteur annoncé            : 126
occurrences récupérées      : 125
paires uniques              : 124
doublon observé             : Noir → Ti (2 occurrences)
publication / entraînement  : non approuvés
```

### Hugging Face — bloc texte/lexique ciblé terminé

Détails complets dans [`HUGGINGFACE_HARVEST.md`](HUGGINGFACE_HARVEST.md).

```text
Taxi1500 / sbd  : 7 919 lignes
ChiKhaPo / stj  : 872 lignes RAW
PanLex           : 420 lignes RAW
FinePDFs / sbd  : 7 lignes
GlotCC / sbd    : 2 lignes
FineWeb2 / sbd  : 4 lignes
```

PanLex et ChiKhaPo se chevauchent fortement sur `stj`; leurs volumes ne doivent pas être additionnés comme s'il s'agissait de sources indépendantes.

### RefLex CLLD — récolte + QA terminés pour Matya et Maya

```text
stj / Matya : 2 743 lignes /units, glottocode maty1235
sym / Maya  : 2 378 lignes /units, glottocode maya1281
TOTAL       : 5 121 unités lexicales RAW
```

QA :

```text
stj : technical_ok=True
sym : technical_ok=True
all_technical_ok=True
```

`San Maka / sbd / sout2844` n'a pas été trouvé dans l'index RefLex actuel.

## Volume RAW opérationnel à ce stade

```text
ASJP           341
Ainsi sois-je  125
Taxi1500      7919
ChiKhaPo       872
PanLex         420
FinePDFs         7
GlotCC           2
FineWeb2          4
RefLex         5121
------------------
TOTAL         14811 occurrences/lignes RAW
```

Ce total est un **compteur de collecte**, pas un corpus final. À ces `14 811` occurrences s'ajoute **1 document source Berthelette**, mais le PDF n'est pas encore compté comme lignes lexicales tant que ses wordlists ne sont pas parsées.

## Source active — Berthelette 2001

Détails : [`BERTHELETTE_RECON.md`](BERTHELETTE_RECON.md).

```text
John Berthelette
Sociolinguistic survey report for the San (Samo) language
SILESR 2002-005
SIL archive entry 8983
```

### PDF récolté et inspecté

```text
fichier                : data/raw/berthelette/SILESR2002_005.pdf
octets                 : 4 015 872
SHA-256                : efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
pages physiques        : 73
texte extractible      : 73 / 73
caractères extraits    : 176 986
technical_ok           : True
```

La page 64 confirme explicitement :

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

### Wordlist confirmée

La section `A Word List of Dialects in the San Region` est localisée aux pages PDF `41–63`. Les identifiants visibles vont actuellement de `012` à `231`. Les entrées `001–011` restent à localiser.

Les formes phonétiques ne sont pas absentes : elles sont encodées dans des polices Type3 legacy sans `/ToUnicode`, ce qui donne avec `pypdf` des séquences comme :

```text
/G3D/G4F/G51/G05/...
```

Diagnostic réel :

```text
31 polices uniques
31 sans /ToUnicode
/T9 à /T33 : Type3
15 606 occurrences de tokens /G..
60 glyphes /G.. différents
```

### Décodage SIL IPA93 — probe actuel

Le mapping candidat est :

```text
code_IPA93 = int(hex_du_nom_Gxx, 16) + 0x1E
```

Des glyphes sentinelles donnent correctement `[` `]` `m` `o` `g` `u` `l` `ŋ` `ɑ` et des diacritiques IPA93.

Exemple :

```text
/G3D/G4F/G51/G05/G49/G57/G05/G4E/G51/G05/G3F
→ [mōgūlō]
```

Le processor suivant valide maintenant le mapping sur tout le bloc lexical :

```bash
pip install -r requirements.txt
pytest tests
python processors/decode_berthelette_ipa93.py
```

Sortie :

```text
data/processed/berthelette/ipa93_decode_probe.json
```

On attend :

```text
sentinelles OK = True
couverture décodage >= 99 %
technical_ok = True
```

Si ce probe passe, on construit directement le parseur RAW Berthelette avec `concept + page + localité + variété + ISO + séquence legacy + forme Unicode + provenance`, puis le QA technique. Aucun OCR ne sera nécessaire si la couverture est suffisante.

## Ordre des prochaines sources

```text
1. Berthelette 2001 — valider IPA93, parser, QA
2. Lexiques originaux SIL / ANTBA
3. Dictionnaires Burkina Langues — seulement après clarification des droits
4. Textes / audio bibliques ANTBA — droits à clarifier et domaine religieux séparé
5. RefLex sbd — seulement si une présence/source fiable est retrouvée ultérieurement
```

## Règle finale de cette branche

```text
accessible
  ≠ librement réutilisable

gratuit
  ≠ open data

présent dans un agrégateur
  ≠ source indépendante

récolté
  ≠ validé linguistiquement
  ≠ orthographe standard
  ≠ approuvé pour publication
  ≠ approuvé pour entraînement
  ≠ approuvé pour usage commercial
```
