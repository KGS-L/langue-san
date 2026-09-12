# Berthelette 2001 — reconnaissance, récolte et extraction lexicale

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

PDF officiel :

```text
https://www.sil.org/system/files/reapdata/82/40/67/82406717915460712209214978734638946211/SILESR2002_005.pdf
```

Le téléchargement automatisé SIL renvoie HTTP 403. Le PDF a été téléchargé manuellement puis ingéré localement.

## 2. Localités et variétés confirmées par le PDF

La page 64 documente explicitement :

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

Le RAW conserve néanmoins `locality + variety + iso + page` afin de préserver la provenance.

## 3. Droits

La règle générale des SIL Language & Culture Archives est `CC-BY-NC-SA-4.0` sauf indication contraire de l'item/fichier. Aucune mention explicite supplémentaire n'a été retrouvée automatiquement dans le texte du PDF.

Le projet conserve donc le statut prudent suivant :

```text
rights_status           = archive_default_noncommercial_pending_pdf_confirmation
publication_approved    = false
training_approved       = false
commercial_use_approved = false
```

## 4. Récolte et inspection PDF — terminées

```text
méthode                 : manual_download_then_local_ingest
octets                  : 4 015 872
SHA-256                 : efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
pages physiques         : 73
pages catalogue         : 75
texte extractible       : 73 / 73
a caractères extraits   : 176 986
inspection technique    : OK
OCR                     : non requis
```

L'écart `75 → 73` est conservé comme observation et n'est pas corrigé artificiellement.

## 5. Bloc lexical

La section utile est :

```text
A Word List of Dialects in the San Region
```

Dans le PDF disponible, les pages `41–63` contiennent les concepts visibles `012–231`, continus, soit 220 concepts. La page 64 contient ensuite les métadonnées de terrain.

Les concepts `001–011` n'ont pas été retrouvés dans le fichier disponible. Ils restent documentés comme `not_found_in_available_pdf` et ne sont pas inventés. Cette lacune ne bloque plus la récolte.

## 6. Police Type3 legacy et décodage — problème résolu

Les formes SAN sont encodées dans des polices Type3 sans `/ToUnicode`, ce qui produit initialement des glyphes `/Gxx`.

Diagnostic :

```text
31 polices uniques sur pages 41–64
31 sans /ToUnicode
/T9 à /T33 : Type3
15 606 tokens /G.. sur le bloc lexical
glyphes uniques pages 41–63 : 58
```

Le mapping validé est :

```text
code_IPA93 = int(hex_du_nom_Gxx, 16) + 0x1E
```

Le processor `decode_berthelette_ipa93.py` utilise `ipa2unicode==1.3` pour la conversion SIL IPA93 → Unicode.

Résultat du probe global :

```text
sentinelles OK      : True
séquences legacy    : 1 661
tokens legacy       : 15 606
tokens non résolus  : 0
couverture           : 100.00 %
technical_ok         : True
OCR                  : non
```

Le problème de police est donc clos techniquement.

## 7. Extraction lexicale 012–231 — terminée et QA passée

Processor :

```text
processors/extract_berthelette_wordlist.py
```

Sorties locales :

```text
data/processed/berthelette/wordlist_occurrences_012_231.csv
data/processed/berthelette/wordlist_occurrences_012_231_summary.json
```

Résultat final :

```text
concepts                         : 220 (012–231)
concepts manquants dans 012–231  : 0
occurrences                      : 1 814
sbd / Maka                       : 223
stj / Matya                      : 679
sym / Maya                       : 912
anomalies structurelles          : 0
formes source vides conservées   : 8
groupes multi-formes             : 53
technical_ok                     : True
```

Par localité :

```text
Bangassogo : 226
Bounou     : 228
Kassoum    : 224
Kiembara   : 229
Kouy       : 228
Lankoué    : 229
Toma       : 223
Toéni      : 227
```

Les 8 formes vides correspondent à des formes explicitement vides dans la représentation source et sont conservées comme occurrences RAW. Elles ne sont pas supprimées silencieusement.

## 8. Relation avec ASJP

ASJP cite Berthelette 2001 comme source de `MAYA_SAMO / sym`. Donc :

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

Les deux peuvent rester dans le compteur brut de collecte, mais les analyses de couverture indépendante, déduplication ou valeur probante doivent tenir compte de cette dépendance de provenance.

Une comparaison ASJP/Berthelette reste utile comme contrôle de provenance, mais n'est plus bloquante pour fermer la récolte technique Berthelette.

## 9. Schéma conservé

Chaque occurrence contient notamment :

```text
source
report_id
pdf_page
concept_source_page
concept_id
concept_gloss_fr
locality
variety_claimed_by_source
iso_639_3
raw_legacy_glyphs
decoded_transcription_source
original_form_ipa
transcription_system
validation_status
rights_status
source_order
```

Aucune validation linguistique ni orthographique n'est implicite dans ce décodage technique.

## 10. Statut final de cette source dans la branche ingestion

```text
discovery                = confirmed
bibliographic_reference  = confirmed
sil_archive_entry        = confirmed_8983
official_pdf_url         = identified
pdf_harvest              = success_manual_ingest
pdf_inspection           = technical_ok
wordlist_section         = confirmed_pages_41_63_visible_ids_012_231
concepts_001_011         = not_found_in_available_pdf_not_invented
locality_variety_mapping = confirmed_in_pdf_page_64
legacy_font_issue        = solved_without_ocr
ipa93_decode_probe       = technical_ok_100_percent
lexical_occurrences      = 1814
lexical_counts_by_iso    = sbd:223 stj:679 sym:912
source_blank_forms       = 8
structural_anomalies     = 0
lexical_extraction_qa    = technical_ok
publication_approved     = false
training_approved        = false
commercial_use_approved  = false
```

**Berthelette est techniquement clôturé pour `feat/data-ingestion`.** La prochaine priorité est le lexique original San Maka / Southern Samo `sbd` de SIL Burkina Faso (2003).
