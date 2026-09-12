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

## 3. Localités et variétés

L'index bibliographique relie cette référence à :

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

Le PDF lui-même confirme explicitement cette correspondance à la page 64 :

```text
Toma       → variété maka
Kouy       → variété matya
Kassoum    → variété matya
Toéni      → variété matya
Bounou     → variété maya
Kiembara   → variété maya
Bangassogo → variété maya
Lankoué    → variété maya
```

Cette correspondance n'est donc plus un simple `locality_hint` pour cette source : elle est une attribution explicitement documentée par Berthelette. Le futur RAW conservera malgré tout `locality + variety + iso + page` afin de préserver la provenance.

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
pages droits détectées   : aucune par recherche textuelle automatique
```

L'écart `75 pages annoncées → 73 pages physiques` est conservé comme observation. Il n'est pas corrigé artificiellement.

Rapport local :

```text
data/processed/berthelette/pdf_inventory.json
```

## 8. Bloc lexical confirmé

L'inspection ciblée a confirmé la section :

```text
A Word List of Dialects in the San Region
```

Les pages PDF `41–63` contiennent les entrées numérotées visibles `012–231`. Les identifiants observés sont continus sur ce bloc. Cela représente 220 concepts directement repérés par extraction textuelle, mais les entrées `001–011` restent à localiser/récupérer avant de fixer le nombre total de concepts.

La page 64 ne contient plus la wordlist : elle documente les lieux, enquêteurs, dates et les variétés `maka/matya/maya`.

Les tableaux des pages 25–26 sur les pourcentages de similarité lexicale sont des statistiques de l'enquête et ne doivent pas être confondus avec la wordlist de formes.

## 9. Problème technique actuel — police phonétique legacy

Le texte français, les numéros de concepts et les noms de localités sont lisibles, mais les formes SAN phonétiques sont actuellement extraites sous forme de glyphes legacy :

```text
/G3D/G4F/G51/G05/...
```

Le mode `layout` de `pypdf` signale en plus :

```text
PDF contains an uninterpretable font. Output will be incomplete.
```

La page 64 précise que les transcriptions phonétiques suivent les standards IPA/AIP. Le problème n'est donc pas que les formes seraient absentes du PDF : le PDF contient une ancienne police/encodage dont la correspondance Unicode n'est pas directement interprétée par `pypdf`.

Règle actuelle :

```text
/Gxx tokens
    ≠ forme SAN exploitable
```

Aucun mapping ne doit être inventé à partir des codes hexadécimaux.

## 10. Diagnostic police avant tout OCR

Processor ajouté :

```text
processors/diagnose_berthelette_fonts.py
```

Il inspecte les pages `41–64` et documente :

```text
/BaseFont
/Subtype
/Encoding
/Differences
/ToUnicode présent ou absent
police embarquée ou non
volume de tokens /G..
```

Commande :

```bash
python processors/diagnose_berthelette_fonts.py
```

Sortie :

```text
data/processed/berthelette/font_diagnostic.json
```

Ce diagnostic ne convertit aucune forme et ne lance aucun OCR. Selon le résultat, on privilégiera dans cet ordre :

```text
1. mapping Unicode fourni/recouvrable depuis la police PDF
2. autre moteur PDF (Poppler, etc.) si mieux interprété
3. mapping documenté d'une police SIL legacy identifiée
4. OCR ciblé uniquement en dernier recours
```

## 11. Schéma lexical prévu

Une occurrence devra conserver au minimum :

```text
source = Berthelette 2001
report_id = SILESR-2002-005
pdf_page
concept_id
concept_gloss_fr
locality
variety_claimed_by_source
iso_639_3
original_form
transcription_system = IPA
notes
validation_status = external_unverified
rights_status
```

Toutes les occurrences sont conservées, même si plusieurs formes existent pour le même concept/localité.

## 12. Critères de réussite lexicale

```text
PDF officiel vérifié                    ✅
provenance + SHA-256                    ✅
inspection structurelle                 ✅
bloc wordlist confirmé                  ✅
variétés confirmées dans le PDF         ✅
formes SAN Unicode décodées             à faire
entrées 001–011 localisées              à faire
extraction RAW lexicale                 à faire
QA technique lexical                    à faire
comparaison ASJP / RefLex               à faire
validation linguistique                 future
```

## 13. Statut actuel

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
wordlist_section        = confirmed_pages_41_63_visible_ids_012_231
locality_variety_mapping = confirmed_in_pdf_page_64
legacy_font_issue       = confirmed_Gxx_tokens
font_diagnostic         = ready
ocr                     = deferred_last_resort
lexical_extraction      = blocked_until_font_decoding
```
