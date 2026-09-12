# Berthelette 2001 — reconnaissance et préparation de la récolte

Cette note documente la source externe prioritaire après la clôture technique de RefLex `stj/sym`.

## 1. Référence bibliographique confirmée

```text
Berthelette, John. 2001.
Sociolinguistic survey report for the San (Samo) language.
SIL Electronic Survey Reports (SILESR), 2002-005.
Dallas, Texas: SIL International.
75 pages.
```

Identifiants / notices retrouvés :

```text
Glottolog reference id : 102181
SIL archive entry      : 8983
report id              : SILESR-2002-005
```

Notices SIL :

```text
https://www.sil.org/resources/archives/8983
https://www.sil.org/resources/publications/entry/8983
```

Ancienne notice historique :

```text
http://www.sil.org/silesr/abstract.asp?ref=2002-005
```

Le PDF officiel a également été identifié sur le stockage SIL :

```text
https://www.sil.org/system/files/reapdata/82/40/67/82406717915460712209214978734638946211/SILESR2002_005.pdf
```

Dans l'environnement de reconnaissance automatisée utilisé pendant cette phase, cette URL a répondu HTTP 403. Cela ne signifie pas que le document est absent : le collecteur prévoit un téléchargement direct si le serveur l'accepte depuis la machine locale, et un fallback par téléchargement manuel sinon.

## 2. Pourquoi cette source est importante

Berthelette n'est pas seulement un dictionnaire. Il s'agit d'une enquête sociolinguistique sur le San/Samo comprenant aussi des données lexicales.

Pour Langue SAN, cette source sert principalement à conserver :

```text
localité / village
    ↓
forme lexicale observée
    ↓
glose / concept source
    ↓
comparaison entre sites
    ↓
contexte sociolinguistique
    ↓
indice documenté sur les variétés
```

Elle est donc particulièrement utile pour éviter d'inférer une variété uniquement à partir d'une ville moderne ou d'une ressemblance graphique.

## 3. Localités explicitement reliées aux variétés dans l'index bibliographique

Glottolog associe cette référence aux localités suivantes :

```text
sbd / Maka
  Toma

stj / Matya
  Kassoum
  Kouy
  Toéni

sym / Maya
  Bounou
  Kiembara
  Bangassogo
  Lankoué
```

Cette information est plus forte qu'un simple `locality_hint`, car elle vient de l'index bibliographique de la référence Berthelette. Elle reste néanmoins une métadonnée de provenance : pendant l'extraction du PDF, on conservera ce que le rapport indique réellement page par page.

## 4. Relation avec les données déjà récoltées

ASJP cite `Berthelette 2001` comme source de sa wordlist :

```text
MAYA_SAMO / sym
```

Donc :

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

Une future comparaison devra identifier le chevauchement exact et préserver la chaîne de provenance.

## 5. Droits et statut de réutilisation

Les SIL Language & Culture Archives indiquent que, sauf mention contraire dans le fichier ou la notice d'un item, les éléments de l'archive sont mis à disposition sous :

```text
CC-BY-NC-SA-4.0
```

Cela permet notamment la copie et l'adaptation sous attribution, pour un usage non commercial, avec partage sous conditions compatibles.

Mais la règle projet reste conservatrice : la licence et les mentions du **PDF Berthelette lui-même** doivent être inspectées après téléchargement. Une mention spécifique dans l'item ou le fichier peut primer sur la règle générale de l'archive.

Statut actuel :

```text
rights_status          = archive_default_noncommercial_pending_pdf_confirmation
publication_approved   = false
training_approved      = false
commercial_use_approved = false
```

Aucune décision ML/commerciale n'est déduite du simple fait que le PDF est accessible.

## 6. Collecteur ajouté

Collecteur :

```text
collectors/berthelette.py
```

Il ne fait pour l'instant **que la récolte contrôlée du PDF original** :

```text
source SIL officielle
    ↓
téléchargement direct ou fichier téléchargé manuellement
    ↓
vérification signature %PDF-
    ↓
vérification taille minimale
    ↓
SHA-256
    ↓
PDF RAW + metadata
```

Il n'effectue encore :

```text
aucun OCR
aucune extraction de wordlist
aucune correction linguistique
aucune déduplication
aucune attribution inventée
```

Sorties prévues :

```text
data/raw/berthelette/
├── SILESR2002_005.pdf
└── metadata.json
```

## 7. Procédure locale

Après mise à jour de la branche :

```bash
cd ~/Bureau/langue-san
git pull origin feat/data-ingestion

cd tools/data_ingestion
source .venv/bin/activate
pytest tests
```

### Probe direct

```bash
python collectors/berthelette.py --probe-only
```

Si le téléchargement SIL fonctionne, on doit obtenir notamment :

```text
report_id = SILESR-2002-005
méthode   = direct_http
PDF valide = True
SHA-256   = ...
```

Le probe n'écrit rien dans `data/raw/`.

### Récolte directe

Après probe réussi :

```bash
python collectors/berthelette.py
```

### Si SIL répond HTTP 403

Télécharger manuellement le PDF officiel depuis la notice SIL ou l'URL PDF identifiée, puis lancer :

```bash
python collectors/berthelette.py --input-file ~/Téléchargements/SILESR2002_005.pdf
```

Le collecteur valide alors le fichier local et conserve dans les métadonnées :

```text
acquisition_method = manual_download_then_local_ingest
```

On ne remplace pas le document officiel par une copie secondaire si la source SIL peut être récupérée manuellement.

## 8. Étape suivante après téléchargement

Une fois le PDF RAW disponible :

```text
1. vérifier le nombre réel de pages
2. inspecter titre / auteur / report id / copyright / licence
3. vérifier si le PDF contient du texte extractible
4. inventorier sommaire, sections, annexes, tableaux et wordlists
5. identifier les pages contenant les données lexicales
6. identifier toutes les localités réellement présentes
7. définir le schéma RAW d'extraction
8. extraire les occurrences sans correction ni déduplication
9. produire un QA technique
10. comparer ensuite à ASJP / RefLex sans fusion automatique
```

L'OCR ne doit être utilisé qu'en dernier recours si le texte/tables ne sont pas extractibles normalement.

## 9. Schéma RAW envisagé pour les données lexicales

Le schéma exact sera défini après inspection du PDF. Une occurrence devra au minimum pouvoir conserver :

```text
source = Berthelette 2001
report_id = SILESR-2002-005
page
section / table / appendix
locality
variety_claimed_by_source
iso_639_3                    # seulement si justifié
concept / gloss source
original_form
notes
validation_status = external_unverified
rights_status
```

Si plusieurs sites ou locuteurs donnent une forme pour le même concept, toutes les occurrences doivent être conservées dans le RAW.

## 10. Critères de réussite

La source ne sera considérée techniquement récoltée qu'après :

```text
PDF officiel/localement vérifié
provenance + SHA-256 conservés
droits explicitement documentés
sections/wordlists inventoriées
localités/pages traçables
aucune variété inventée
aucune déduplication silencieuse
QA technique produit
```

## 11. Sources de reconnaissance

```text
Glottolog — Berthelette 2001, référence 102181
https://glottolog.org/resource/reference/id/102181

SIL Language & Culture Archives — entrée 8983
https://www.sil.org/resources/archives/8983

SIL Publications — entrée 8983
https://www.sil.org/resources/publications/entry/8983

ASJP — MAYA_SAMO
https://asjp.clld.org/languages/MAYA_SAMO
```

Les sources secondaires utilisées pour retrouver le lien PDF ne constituent pas la base du futur dataset. Le RAW doit provenir du document SIL original.

## Statut actuel

```text
discovery               = confirmed
bibliographic_reference = confirmed
sil_archive_entry       = confirmed_8983
official_pdf_url        = identified
automated_web_access    = HTTP_403_observed_in_research_environment
collector               = ready
item_license            = archive_default_pending_pdf_confirmation
ingestion               = download_probe_ready
lexical_extraction      = not_started
```
