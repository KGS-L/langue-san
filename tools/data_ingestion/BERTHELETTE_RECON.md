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

PDF officiel identifié :

```text
https://www.sil.org/system/files/reapdata/82/40/67/82406717915460712209214978734638946211/SILESR2002_005.pdf
```

Le téléchargement automatisé SIL renvoie HTTP 403. Le PDF a donc été téléchargé manuellement puis ingéré localement.

## 2. Localités et variétés confirmées dans le PDF

La page 64 confirme explicitement :

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

Cette correspondance n'est donc pas une simple inférence géographique pour cette source. L'extraction conserve néanmoins `locality + variety + iso + page` afin de préserver la provenance.

## 3. Relation avec ASJP

ASJP cite Berthelette 2001 comme source de `MAYA_SAMO / sym`.

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

Une comparaison de chevauchement sera faite après extraction technique.

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

```text
pages physiques PDF  : 73
pages catalogue       : 75
texte extractible     : 73 / 73 = 100 %
caractères extraits   : 176 986
SHA metadata          : OK
technical_ok          : True
```

L'écart `75 → 73` est conservé comme observation et n'est pas corrigé artificiellement.

## 7. Bloc lexical confirmé

La section utile est :

```text
A Word List of Dialects in the San Region
```

Les pages PDF `41–63` contiennent les concepts visibles `012–231`, continus. La page 64 ne contient plus la wordlist mais les métadonnées de terrain.

Les concepts `001–011` ne sont pas visibles dans le bloc textuellement repéré du PDF actuel. Ils ne doivent pas être inventés. Le catalogue annonce 75 pages alors que le fichier en contient 73 ; ce décalage est conservé comme observation mais ne suffit pas, à lui seul, à expliquer l'absence des concepts `001–011`.

## 8. Police Type3 legacy — diagnostic terminé

Les formes SAN sont encodées avec des polices Type3 sans `/ToUnicode` :

```text
/T9 à /T33 : Type3
31 polices uniques sur pages 41–64
31 sans /ToUnicode
15 606 occurrences de tokens /G..
60 glyphes /G.. différents sur pages 41–64
```

Les valeurs internes de `/Encoding /Differences` (`1`, `2`, `3`, ...) sont des codes de sous-ensemble PDF et ne sont pas les codes IPA historiques.

## 9. Mapping SIL IPA93 — confirmé techniquement

Les noms de glyphes `/Gxx` suivent le mapping :

```text
code_IPA93 = int(hex_du_nom_Gxx, 16) + 0x1E
```

Le décodage utilise `ipa2unicode==1.3`, qui fournit la table SIL IPA93 → Unicode.

Important : le caractère IPA `ɡ` (U+0261) est distinct du `g` ASCII. Le décodeur conserve le caractère IPA et ne le remplace pas par une approximation graphique.

Exemple réel :

```text
/G3D/G4F/G51/G05/G49/G57/G05/G4E/G51/G05/G3F
→ [mōɡūlō]
```

## 10. Probe global de décodage — réussi

Processor :

```text
processors/decode_berthelette_ipa93.py
```

Résultat réel sur les pages `41–63` :

```text
sentinelles OK          : True
séquences legacy        : 1 661
tokens legacy           : 15 606
glyphes uniques         : 58
tokens non résolus      : 0
couverture décodage     : 100.00 %
technical_ok            : True
OCR utilisé             : non
```

Le diagnostic précédent comptait 60 glyphes uniques sur `41–64`, tandis que le probe lexical en compte 58 sur `41–63`. Cette différence de périmètre est attendue et n'est pas une anomalie.

Le problème de police est donc **clos techniquement** pour le bloc lexical. Aucun OCR n'est nécessaire.

## 11. Étape actuelle — extraction des occurrences 012–231

Processor :

```text
processors/extract_berthelette_wordlist.py
```

Commande :

```bash
python processors/extract_berthelette_wordlist.py
```

Sorties locales :

```text
data/processed/berthelette/wordlist_occurrences_012_231.csv
data/processed/berthelette/wordlist_occurrences_012_231_summary.json
```

Le parseur :

```text
1. lit les pages PDF 41–63
2. repère les concepts 012–231
3. accumule les glyphes /Gxx même lorsqu'une transcription est coupée sur plusieurs fragments
4. convertit les glyphes SIL IPA93 vers Unicode
5. associe chaque forme aux localités indiquées dans la source
6. réutilise une même forme quand la source la donne pour plusieurs localités sans répéter les glyphes
7. conserve plusieurs formes pour un même concept/localité
8. attribue variété et ISO selon la table explicitement donnée page 64
9. conserve la séquence legacy originale et la transcription Unicode
10. ne déduplique pas et ne valide pas linguistiquement
```

Schéma principal :

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

Le `summary.json` contrôle notamment :

```text
concept_count
missing_concept_ids
occurrence_count
counts_by_locality
counts_by_iso
multi_form_concept_locality_groups
anomalies
technical_ok
```

## 12. Règles de qualité

```text
PDF officiel vérifié                    ✅
provenance + SHA-256                    ✅
inspection structurelle                 ✅
bloc wordlist confirmé                  ✅
variétés confirmées dans le PDF         ✅
diagnostic Type3 /Gxx                   ✅
mapping SIL IPA93                       ✅
décodage global 100 %                   ✅
OCR                                     non nécessaire
extraction 012–231                      prête à exécuter
concepts 001–011                        non retrouvés / non inventés
QA technique lexical complet            après extraction
comparaison ASJP / RefLex               après QA
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
legacy_font_issue        = solved_without_ocr
legacy_glyph_tokens      = 15606
ipa93_decode_probe       = technical_ok_100_percent
lexical_extractor        = ready_for_012_231
ocr                      = not_required
lexical_extraction       = ready_to_run
```
