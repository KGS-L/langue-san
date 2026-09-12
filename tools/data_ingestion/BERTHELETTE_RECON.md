# Berthelette 2001 — reconnaissance, récolte et décodage lexical

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

## 2. Localités et variétés

Le PDF confirme explicitement à la page 64 :

```text
Toma       → variété maka  → sbd
Kouy       → variété matya → stj
Kassoum    → variété matya → stj
Toéni      → variété matya → stj
Bounou     → variété maya  → sym
Kiembara   → variété maya  → sym
Bangassogo → variété maya  → sym
Lankoué    → variété maya  → sym
```

Cette correspondance n'est donc plus un simple `locality_hint` pour Berthelette. Le futur RAW conservera malgré tout `locality + variety + iso + page` afin de préserver la provenance.

## 3. Relation avec ASJP

ASJP cite Berthelette 2001 comme source de `MAYA_SAMO / sym`.

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

Une comparaison de chevauchement sera faite après extraction.

## 4. Droits

La règle générale des SIL Language & Culture Archives est `CC-BY-NC-SA-4.0` sauf indication contraire de l'item/fichier.

Aucune mention explicite de droits n'a été retrouvée automatiquement dans le texte extrait du PDF. Le projet conserve donc un statut prudent :

```text
rights_status           = archive_default_noncommercial_pending_pdf_confirmation
publication_approved    = false
training_approved       = false
commercial_use_approved = false
```

## 5. Récolte PDF — réussie

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

## 6. Inspection structurelle — réussie

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

L'écart `75 pages annoncées → 73 pages physiques` est conservé comme observation et n'est pas corrigé artificiellement.

## 7. Bloc lexical confirmé

La section utile est :

```text
A Word List of Dialects in the San Region
```

Les pages PDF `41–63` contiennent les entrées numérotées visibles `012–231`. Les identifiants observés sont continus sur ce bloc. Les entrées `001–011` restent à localiser/récupérer avant de fixer le nombre total de concepts.

La page 64 documente ensuite les lieux, enquêteurs, dates et variétés. Les tableaux des pages 25–26 concernent les pourcentages de similarité lexicale et ne doivent pas être confondus avec la wordlist de formes.

## 8. Diagnostic de la police legacy — terminé

Processor :

```text
processors/diagnose_berthelette_fonts.py
```

Résultat réel sur les pages `41–64` :

```text
polices uniques              : 31
polices sans /ToUnicode      : 31
polices Type3 de wordlist    : /T9 à /T33
occurrences de tokens /G..   : 15 606
glyphes /G.. uniques         : 60
problème Unicode probable    : True
OCR utilisé                  : non
```

Les polices `/T9` à `/T33` sont des polices Type3. Leur `/Encoding /Differences` réencode les glyphes avec de petits codes internes (`1`, `2`, `3`, ...). Ces valeurs internes **ne sont pas** les codes IPA93 et ne doivent pas être utilisées directement comme caractères.

## 9. Découverte du mapping SIL IPA93

Le probe manuel a montré une relation stable entre le nom des glyphes `/Gxx` et les codes d'accès historiques SIL IPA93 :

```text
code_IPA93 = int(hex_du_nom_Gxx, 16) + 0x1E
```

Exemples sentinelles :

```text
/G3D → 0x3D + 0x1E = 91  → [
/G3F → 0x3F + 0x1E = 93  → ]
/G4F → 0x4F + 0x1E = 109 → m
/G51 → 0x51 + 0x1E = 111 → o
/G49 → 0x49 + 0x1E = 103 → g
/G57 → 0x57 + 0x1E = 117 → u
/G4E → 0x4E + 0x1E = 108 → l
/G30 → 0x30 + 0x1E = 78  → ŋ
/G23 → 0x23 + 0x1E = 65  → ɑ
/G06 → 0x06 + 0x1E = 36  → accent grave combinant
```

La première séquence observée :

```text
/G3D/G4F/G51/G05/G49/G57/G05/G4E/G51/G05/G3F
```

se décode donc techniquement en :

```text
[mōgūlō]
```

Ce résultat est une **conversion d'encodage**, pas une validation linguistique de la forme.

Le mapping Unicode utilisé pour le probe est fourni par le package open source `ipa2unicode` 1.3, qui implémente la table SIL IPA93 et est distribué sous licence MIT. Le projet conserve la provenance de cette dépendance et ne copie pas de fichier de police.

## 10. Étape actuelle — valider le décodage sur tout le bloc

Processor ajouté :

```text
processors/decode_berthelette_ipa93.py
```

Dépendance :

```text
ipa2unicode==1.3
```

Commande :

```bash
python processors/decode_berthelette_ipa93.py
```

Sortie locale :

```text
data/processed/berthelette/ipa93_decode_probe.json
```

Le processor :

```text
1. lit les pages 41–63
2. détecte toutes les séquences /Gxx
3. calcule le code IPA93 avec l'offset 0x1E
4. convertit chaque code en Unicode via la table IPA93
5. mesure la couverture de décodage
6. vérifie les glyphes sentinelles
7. montre des exemples Unicode
8. ne crée encore aucun dataset lexical final
9. ne lance aucun OCR
```

Critère de passage à l'extraction :

```text
sentinelles OK = True
couverture de décodage >= 99 %
technical_ok = True
```

Si ce probe passe, on construit directement le parseur RAW concept/localité/forme sans OCR.

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
raw_glyph_sequence
original_form_unicode
transcription_system = SIL_IPA93_to_Unicode_IPA
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
diagnostic Type3 /Gxx                   ✅
mapping candidat SIL IPA93 identifié    ✅
validation globale du décodage          à faire
entrées 001–011 localisées              à faire
extraction RAW lexicale                 à faire
QA technique lexical                    à faire
comparaison ASJP / RefLex               à faire
validation linguistique                 future
```

## 13. Statut actuel

```text
discovery                = confirmed
bibliographic_reference  = confirmed
sil_archive_entry        = confirmed_8983
official_pdf_url         = identified
automated_web_access     = HTTP_403
pdf_harvest              = success_manual_ingest
pdf_bytes                = 4015872
pdf_sha256               = efcd06c8e235df9e7334b141aaeb123064e227fab2c05d100c4a57bba7d9f196
pdf_pages                = 73
pdf_text_coverage        = 100_percent
pdf_inspection           = technical_ok
wordlist_section         = confirmed_pages_41_63_visible_ids_012_231
locality_variety_mapping = confirmed_in_pdf_page_64
legacy_font_issue        = confirmed_type3_without_tounicode
legacy_glyph_tokens      = 15606
legacy_unique_glyphs     = 60
ipa93_mapping_candidate  = hex_glyph_plus_0x1E
ipa93_decode_probe       = ready
ocr                      = deferred_last_resort
lexical_extraction       = pending_decode_probe
```
