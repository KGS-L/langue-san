# Berthelette 2001 — reconnaissance, récolte et inspection

Cette note documente la source Berthelette utilisée après la clôture technique de RefLex `stj/sym`.

## 1. Référence bibliographique

```text
Berthelette, John. 2001.
Sociolinguistic survey report for the San (Samo) language.
SIL Electronic Survey Reports (SILESR), 2002-005.
Dallas, Texas: SIL International.
75 pages annoncées dans le catalogue.
```

Identifiants :

```text
Glottolog reference id : 102181
SIL archive entry      : 8983
report id              : SILESR-2002-005
```

Notices :

```text
https://www.sil.org/resources/archives/8983
https://www.sil.org/resources/publications/entry/8983
```

PDF officiel identifié :

```text
https://www.sil.org/system/files/reapdata/82/40/67/82406717915460712209214978734638946211/SILESR2002_005.pdf
```

Le téléchargement automatisé SIL renvoie HTTP 403. Le PDF a donc été téléchargé manuellement puis ingéré localement.

## 2. Pourquoi cette source est importante

Le rapport est à la fois sociolinguistique et lexical. Il permet de conserver les données avec leur contexte géographique :

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
```

Il est particulièrement utile pour ne pas réduire une variété entière à une seule ville moderne.

## 3. Localités documentées par l'index bibliographique

Glottolog relie cette référence à :

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

Cette correspondance est conservée comme provenance bibliographique. Lors du parsing, on garde aussi les libellés réellement présents dans le PDF page par page.

## 4. Relation avec ASJP

ASJP cite Berthelette 2001 comme source de `MAYA_SAMO / sym`.

Donc :

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

Une comparaison de chevauchement sera faite après extraction.

## 5. Droits

La règle générale des SIL Language & Culture Archives est `CC-BY-NC-SA-4.0` sauf indication contraire de l'item/fichier.

Aucune mention explicite de droits n'a été retrouvée automatiquement dans le texte extrait du PDF. Le projet conserve donc un statut prudent :

```text
rights_status           = archive_default_noncommercial_pending_pdf_confirmation
publication_approved    = false
training_approved       = false
commercial_use_approved = false
```

## 6. Récolte PDF — réussie

Collecteur :

```text
collectors/berthelette.py
```

Résultat local :

```text
méthode   : manual_download_then_local_ingest
octets    : 4 015 872
PDF valide: True
SHA-256   : efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
```

Sorties :

```text
data/raw/berthelette/
├── SILESR2002_005.pdf
└── metadata.json
```

## 7. Inspection structurelle — réussie

Processor :

```text
processors/inspect_berthelette_pdf.py
```

Résultat réel :

```text
pages physiques PDF     : 73
pages catalogue          : 75
texte extractible        : 73 / 73 = 100 %
caractères extraits      : 176 986
SHA metadata             : OK
technical_ok             : True
OCR nécessaire           : non
pages droits détectées   : aucune par recherche textuelle automatique
```

L'écart `75 pages annoncées → 73 pages physiques` est conservé comme observation. Il n'est pas corrigé artificiellement.

Pages signalées par les mots-clés génériques `wordlist/lexical/appendix/...` :

```text
2, 3, 4, 8, 9, 10, 11, 15, 16, 19, 24, 25, 26, 32, 41
```

Rapport local :

```text
data/processed/berthelette/pdf_inventory.json
```

## 8. Signal fort d'un bloc comparatif multi-localités

L'inspection a montré que les huit localités réapparaissent ensemble sur un long bloc, notamment autour des pages `41–64`.

Cela est cohérent avec le fait que Glottolog classe le document comme `overview;wordlist;socling`, mais ce signal ne suffit pas encore à définir automatiquement la structure des lignes.

On ne parse donc pas encore le tableau à l'aveugle.

## 9. Étape actuelle — inspection ciblée du bloc wordlist

Processor ajouté :

```text
processors/inspect_berthelette_wordlist.py
```

Il :

```text
- extrait le texte PDF en mode layout
- compte les localités canoniques présentes page par page
- repère les blocs continus où plusieurs localités apparaissent ensemble
- conserve un aperçu du début et de la fin de chaque bloc
- écrit le texte complet candidat avec des marqueurs de page
- ne produit encore aucune entrée lexicale finale
```

Commande :

```bash
python processors/inspect_berthelette_wordlist.py
```

Sorties :

```text
data/processed/berthelette/wordlist_section_inventory.json
data/processed/berthelette/wordlist_candidate_text.txt
```

Le mode `layout` est important : il tente de conserver l'alignement des colonnes, ce qui nous permettra de savoir si le tableau peut être parsé proprement sans OCR ni reconstruction manuelle.

## 10. Schéma lexical prévu

Le schéma final sera figé après lecture du vrai bloc de tableau. Une occurrence devra conserver au minimum :

```text
source = Berthelette 2001
report_id = SILESR-2002-005
pdf_page
section / appendix / table
locality
variety_claimed_by_source
iso_639_3                    # seulement si justifié
concept / gloss source
original_form
notes
validation_status = external_unverified
rights_status
```

Toutes les occurrences sont conservées, même si plusieurs localités ou formes existent pour le même concept.

## 11. Critères de réussite lexicale

```text
PDF officiel vérifié                    ✅
provenance + SHA-256                    ✅
inspection structurelle                 ✅
texte extractible sans OCR              ✅
bloc wordlist confirmé                  en cours
structure des colonnes confirmée        à faire
extraction RAW lexicale                 à faire
QA technique lexical                    à faire
comparaison ASJP / RefLex               à faire
validation linguistique                 future
```

## 12. Statut actuel

```text
discovery               = confirmed
bibliographic_reference = confirmed
sil_archive_entry       = confirmed_8983
official_pdf_url        = identified
automated_web_access    = HTTP_403
pdf_harvest             = success_manual_ingest
pdf_bytes               = 4015872
pdf_sha256              = efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
pdf_pages               = 73
pdf_text_coverage       = 100_percent
pdf_inspection          = technical_ok
ocr_required            = false
wordlist_block_probe    = ready
lexical_extraction      = not_started
```
