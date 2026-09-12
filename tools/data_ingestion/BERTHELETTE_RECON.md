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

Le téléchargement automatisé SIL répond HTTP 403, mais le PDF a été récupéré manuellement puis ingéré localement avec le collecteur prévu à cet effet.

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

Cette information reste une métadonnée de provenance : pendant l'extraction du PDF, on conservera ce que le rapport indique réellement page par page.

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

Mais la règle projet reste conservatrice : les mentions du **PDF Berthelette lui-même** doivent encore être inspectées. Une mention spécifique dans l'item ou le fichier peut primer sur la règle générale de l'archive.

Statut actuel :

```text
rights_status           = archive_default_noncommercial_pending_pdf_confirmation
publication_approved    = false
training_approved       = false
commercial_use_approved = false
```

## 6. Récolte du PDF — terminée

Collecteur :

```text
collectors/berthelette.py
```

La tentative HTTP automatisée a confirmé le blocage `403`. Le fallback local a ensuite été utilisé :

```bash
python collectors/berthelette.py \
  --probe-only \
  --input-file ../../data/raw/berthelette/SILESR2002_005.pdf
```

Probe observé :

```text
report_id : SILESR-2002-005
méthode   : manual_download_then_local_ingest
octets    : 4015872
PDF valide: True
SHA-256   : efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
```

Puis ingestion locale :

```bash
python collectors/berthelette.py \
  --input-file ../../data/raw/berthelette/SILESR2002_005.pdf
```

Sorties présentes localement :

```text
data/raw/berthelette/
├── SILESR2002_005.pdf
└── metadata.json
```

Le PDF RAW est donc maintenant récolté et fingerprinté. Aucune extraction lexicale n'a encore été réalisée.

## 7. Étape suivante — inspection structurelle sans OCR

Processor :

```text
processors/inspect_berthelette_pdf.py
```

Dépendance ajoutée :

```text
pypdf
```

Objectif :

```text
PDF RAW
   ↓
lecture standard PDF
   ↓
nombre réel de pages
   ↓
mesure du texte extractible
   ↓
pages candidates droits/copyright
   ↓
pages candidates wordlist/appendix/lexical
   ↓
pages contenant les localités connues
   ↓
rapport d'inventaire JSON
```

Aucun OCR n'est lancé automatiquement. Les pages sans texte extractible sont simplement signalées afin de décider ensuite si une inspection visuelle ou un OCR ciblé est réellement nécessaire.

Commande :

```bash
python processors/inspect_berthelette_pdf.py
```

Sortie :

```text
data/processed/berthelette/pdf_inventory.json
```

Le script vérifie également que le SHA-256 du PDF correspond au `metadata.json` produit pendant la récolte.

## 8. Après l'inventaire PDF

Une fois le rapport produit :

```text
1. confirmer le nombre réel de pages
2. lire les pages droits/copyright détectées
3. identifier précisément les annexes et wordlists
4. vérifier les localités effectivement présentes page par page
5. comprendre la structure des tableaux
6. définir le schéma RAW lexical final
7. extraire les occurrences sans correction ni déduplication
8. produire un QA technique lexical
9. comparer ensuite à ASJP / RefLex sans fusion automatique
```

L'OCR reste un dernier recours, uniquement si des pages utiles ne sont pas extractibles par les outils PDF standards.

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

La source ne sera considérée techniquement récoltée au niveau lexical qu'après :

```text
PDF officiel/localement vérifié          ✅
provenance + SHA-256 conservés           ✅
inspection structurelle                  à faire
droits du fichier inspectés              à faire
sections/wordlists inventoriées          à faire
localités/pages traçables                à faire
aucune variété inventée                  règle active
aucune déduplication silencieuse         règle active
QA technique lexical                     à faire
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

## Statut actuel

```text
discovery               = confirmed
bibliographic_reference = confirmed
sil_archive_entry       = confirmed_8983
official_pdf_url        = identified
automated_web_access    = HTTP_403
pdf_harvest             = success_manual_ingest
pdf_bytes               = 4015872
pdf_sha256              = efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
item_license            = archive_default_pending_pdf_confirmation
pdf_inspection          = ready
lexical_extraction      = not_started
```
