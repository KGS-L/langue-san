# San Matya / `stj` — reconnaissance de la source primaire

Cette note suit la source Matya après la clôture technique de Berthelette et documente séparément l'ouvrage historique 2011 et les ressources Lexique Pro modernes.

## 1. Source historique cible

Référence utilisée par ASJP pour `SAMO_MATYA_2` :

```text
Morris, Pamela; Sama, François; Sama, Jérémie; Drabo, Jean-Pierre. 2011.
Lexique San Matya avec guide d'orthographe.
Tougan, Burkina Faso:
Association nationale pour la traduction de la Bible et alphabétisation (ANTBA).
```

Variété :

```text
San Matya / San Ma Caa / Samo Matya
ISO 639-3 : stj
Glottocode : maty1235
zone : Tougan et environs
```

ASJP confirme explicitement cette référence comme source de `SAMO_MATYA_2`. RefLex référence également `Morris et al. 2011 : Matya` comme source amont de ses données Matya.

## 2. Ressource moderne identifiée

Une application Android actuelle existe :

```text
nom : San Matya de A-Z
développeur : Burkina Langues
package : com.matya.san.lexique
volume annoncé : 2 576 entrées
images annoncées : 685
support : burkinalangues@gmail.com
```

Cette application reste traitée comme une ressource moderne distincte tant que sa relation exacte avec l'ouvrage Morris et al. 2011 n'est pas démontrée.

## 3. Installateur Windows Lexique Pro

Fichier local :

```text
data/raw/san_matya_lexique_pro/
└── San Matya - Lexique Pro Setup.exe
```

Identification :

```text
format       : PE32 GUI Intel 80386 / Windows
installateur : Inno Setup 5.3.10 Unicode
SHA-256      : 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
```

Extraction statique avec `innoextract` réussie, sans exécuter le programme Windows.

Inventaire :

```text
fichiers totaux : 734
images           : 695
idx              : 3
db               : 3
lpLiftEnc         : 1
lpConfigEnc       : 1
lift-ranges       : 1
audio             : 0
```

Les trois `.db` sont les bases génériques de domaines sémantiques Lexique Pro et non le corpus Matya.

## 4. Structure des données

Fichiers principaux :

```text
San du Nord Matya.lpLiftEnc
San du Nord Matya.lpConfigEnc
San du Nord Matya.lift-ranges
San du Nord Matya - San Matya.idx
San du Nord Matya - French.idx
San du Nord Matya - English.idx
```

### Corpus principal

`lpLiftEnc` et `lpConfigEnc` sont des binaires opaques/protégés. Les entêtes ne correspondent pas à du XML/LIFT lisible et `strings` ne fournit que des fragments aléatoires.

Le projet ne tente pas de contourner cette protection.

### `lift-ranges`

Le fichier `.lift-ranges` est un XML LIFT UTF-8 valide et lisible. Il contient les ranges génériques : parties du discours, informations grammaticales, étymologie, etc. Il ne contient pas le corpus lexical principal.

### Index anglais et français

Les index English/French sont des tables de recherche `terme → identifiant(s) d'entrée`.

Exemples :

```text
English.idx
animal       136
celebration  61,115
honeycomb    2736

French.idx
abeille      2753
aboyer       966
acacia       1054
acheter      1894
```

L'index français utilise un encodage legacy pour certains caractères.

### Index San Matya — probe final

Le probe brut final tranche définitivement :

```text
taille : 14 346 octets
lignes : 2 576
tabs   : 0
NUL    : 0
```

Les `2 576` lignes sont uniquement des identifiants numériques, par exemple :

```text
1139
1095
470
2516
1060
1320
...
```

Le décodage UTF-8, CP1252 et Latin-1 produit exactement ces identifiants ; les essais UTF-16 ne donnent que du texte incohérent.

Conclusion :

```text
San Matya.idx = ordre/index numérique de 2 576 entrées
                sans formes lexicales Matya en clair
```

Il n'est donc pas possible de reconstruire `forme Matya ↔ français/anglais` à partir des seuls fichiers `.idx`, car la partie Matya manque et le corpus source reste dans `lpLiftEnc` protégé.

Le nombre exact de 2 576 lignes correspond au volume annoncé par l'application moderne, ce qui confirme fortement que cet installateur représente la même base lexicale moderne ou une exportation directe de celle-ci. Cela ne démontre toutefois pas que cette base est identique à l'ouvrage Morris et al. 2011.

## 5. Licence : logiciel ≠ données lexicales

`licence.txt` concerne Lexique Pro, copyright SIL International 2004–2010.

Elle autorise l'utilisation/distribution du logiciel avec un lexique uniquement lorsque le distributeur possède les données lexicales ou a reçu l'autorisation de les distribuer.

```text
licence Lexique Pro ≠ licence du corpus San Matya
```

Les droits de publication, réutilisation ML et usage commercial des données Matya restent non confirmés.

## 6. Décision finale sur le package Lexique Pro

L'inspection technique locale est terminée.

```text
installateur              : inspecté
extraction statique       : réussie
corpus principal          : protégé/opaque
lift-ranges               : lisible mais non lexical
index anglais/français    : glosses → ids
index San Matya           : 2576 ids numériques uniquement
images                    : 695
fichiers audio            : 0
reconstruction lexicale   : impossible via idx seuls
contournement protection  : non entrepris
```

Aucun nouveau volume lexical n'est ajouté au compteur RAW à partir de ce package.

## 7. Recherche historique Morris et al. 2011

Une recherche web courte confirme la citation via ASJP, mais n'a pas retrouvé de PDF/LIFT original public ni de licence explicite pour l'ouvrage 2011.

État :

```text
référence bibliographique : confirmée
PDF/LIFT original          : non retrouvé
licence données            : non confirmée
bulk harvest               : bloqué
```

La piste pourra être reprise si ANTBA, Burkina Langues, SIL ou les auteurs fournissent un export LIFT/PDF ou une autorisation explicite.

## 8. Statut final de la piste Matya moderne

```text
discovery_historical       = confirmed
variety                    = matya
iso_639_3                  = stj
glottocode                 = maty1235
historical_year            = 2011
historical_publisher       = ANTBA
primary_digital_copy       = not_found
rights_status              = rights_review_required
modern_entry_count_claimed = 2576
modern_image_count_claimed = 685
modern_windows_installer   = inspected_and_closed
installer_sha256           = 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
extracted_file_count       = 734
extracted_image_count      = 695
extracted_audio_count      = 0
embedded_main_data         = protected_opaque_lpLiftEnc
embedded_ranges            = readable_lift_ranges_xml
english_index              = readable_term_to_entry_ids
french_index               = readable_term_to_entry_ids_legacy_encoding
san_matya_index            = 2576_numeric_ids_only
static_inspection          = completed
lexical_reconstruction     = not_possible_from_distribution_files_without_protected_corpus
modern_data_license        = not_confirmed
bulk_harvest               = blocked_pending_source_or_permission
```

## 9. Suite

La piste Lexique Pro Matya est fermée pour l'instant. La source suivante à investiguer est **San Maya / `sym`**, puis Morse 1967. La piste Morris et al. 2011 restera ouverte uniquement pour un accès primaire autorisé.
