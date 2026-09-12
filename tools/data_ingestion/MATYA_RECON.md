# San Matya / `stj` — reconnaissance de la source primaire

Cette note ouvre la source prioritaire suivante après la clôture technique de Berthelette et la mise en attente des droits/access sur le lexique Maka moderne.

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

RefLex référence également `Morris et al. 2011 : Matya` comme source amont de ses données Matya.

## 2. Pourquoi cette source est importante

RefLex a déjà fourni au projet 2 743 unités `stj`, mais l'objectif de cette phase est aussi de remonter aux **sources primaires**.

Cette ressource permettrait de :

```text
- confirmer la provenance des données RefLex Matya
- obtenir le lexique/guide d'orthographe original
- distinguer source primaire et agrégateur
- améliorer la documentation des droits
```

## 3. Ressources numériques actuelles identifiées

Une application Android actuelle existe sur Google Play :

```text
nom : San Matya de A-Z
développeur : Burkina Langues
package : com.matya.san.lexique
zone déclarée : autour de Tougan
volume annoncé : 2 576 entrées
images annoncées : 685
mise à jour observée : 27 juillet 2026
support : burkinalangues@gmail.com
```

Le descriptif la présente comme `Lexique San Matya - Français`.

Cette application est traitée comme une **ressource moderne distincte** tant que sa relation exacte avec l'ouvrage Morris et al. 2011 n'est pas démontrée.

### Installateur Windows Lexique Pro récupéré localement

Un installateur Windows a été téléchargé manuellement et placé localement dans :

```text
data/raw/san_matya_lexique_pro/
└── San Matya - Lexique Pro Setup.exe
```

Identification technique :

```text
format       : PE32 GUI Intel 80386 / Windows
installateur : Inno Setup 5.3.10 Unicode
SHA-256      : 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
```

Important : ce fichier est **Matya / stj**, et non San du Sud / Maka / sbd.

## 4. Listing statique Inno Setup — contenu lexical confirmé

`innoextract -l` révèle que l'installateur embarque bien un jeu de données Lexique Pro San Matya.

Fichiers structurants identifiés :

```text
San du Nord Matya.lpLiftEnc        ~1.8 MiB
San du Nord Matya.lpConfigEnc      ~42.1 KiB
San du Nord Matya.lift-ranges      ~1.15 MiB
San du Nord Matya - San Matya.idx  ~14 KiB
San du Nord Matya - French.idx     ~44.7 KiB
San du Nord Matya - English.idx    ~2.21 KiB
```

Autres éléments observés :

```text
pictures/                         nombreuses images lexicales
Display/homebanner1.jpg
Display/homebanner2.jpg
Semantic Domains/xxdict2.db
Semantic Domains/xxdict3.db
Semantic Domains/xxdict4.db
licence.txt                       licence du logiciel à inspecter séparément
```

La présence de `lpLiftEnc` indique très probablement un corpus LIFT encapsulé/protégé par Lexique Pro. Ce fichier ne doit pas être traité comme un LIFT XML ordinaire tant que son format réel n'a pas été inspecté. Aucun contournement de protection n'est entrepris.

Le fichier `.lift-ranges` est distinct du corpus lexical principal ; il contient normalement des listes/ranges LIFT et ne doit pas être confondu avec les entrées du dictionnaire.

Le premier listing montre de très nombreuses images. La présence d'audio n'est **pas encore confirmée** par ce listing partiel.

## 5. Pourquoi le grep d'extensions n'a rien affiché

La commande utilisée cherchait des extensions en fin de ligne (`$`), alors que `innoextract -l` ajoute après chaque chemin des guillemets, des destinations et/ou la taille du fichier. L'absence de sortie du `grep` ne signifie donc pas absence de fichiers correspondants.

Le listing brut constitue la preuve de présence de `.lpLiftEnc`, `.lpConfigEnc`, `.lift-ranges`, `.idx`, `.jpg`, `.png` et `.db`.

## 6. Séparation des domaines

Une application biblique ANTBA en San Matya existe également. Elle relève du domaine religieux et ne doit pas être confondue avec le lexique général.

```text
lexique général ≠ texte biblique
```

## 7. Droits et accès

État actuel :

```text
référence bibliographique historique : confirmée
PDF officiel 2011                    : non retrouvé à ce stade
notice primaire ANTBA/SIL            : à retrouver
droits de l'ouvrage 2011             : à clarifier
application moderne                  : confirmée
installateur Lexique Pro local       : récupéré
contenu lexical embarqué             : confirmé techniquement
licence de réutilisation moderne     : non confirmée
bulk harvest                          : non approuvé
```

La présence locale et l'extraction technique d'un installateur ne valent pas autorisation de republier, d'entraîner un modèle ou d'exploiter commercialement son contenu.

## 8. Prochaine inspection technique

Ordre de travail local :

```text
1. extraire l'installateur avec innoextract dans un dossier local `extracted/`
2. inventorier les types et nombres de fichiers réellement extraits
3. inspecter `licence.txt`
4. inspecter sans modification les signatures/entêtes de lpLiftEnc, lpConfigEnc, lift-ranges et idx
5. vérifier si des fichiers audio sont effectivement présents
6. documenter structure et relation éventuelle avec Morris et al. 2011
7. ne pas contourner un chiffrement/protection ; si le corpus principal est protégé, privilégier une exportation prévue par Lexique Pro ou une autorisation/source originale
```

## 9. Règle de vitesse

```text
1. inspection statique courte de l'installateur local
2. recherche courte du PDF / catalogue / notice primaire 2011
3. si structure exploitable + droits suffisants : collecteur/extraction
4. sinon : documenter le blocage
5. passer à la source primaire Maya suivante
```

L'objectif est d'éviter de rester bloqué sur une source difficile d'accès.

## 10. Statut actuel

```text
discovery_historical       = confirmed
variety                    = matya
iso_639_3                  = stj
glottocode                 = maty1235
historical_year            = 2011
historical_publisher       = ANTBA
historical_place           = Tougan
primary_digital_copy       = not_found_yet
rights_status              = rights_review_required
modern_android_app         = confirmed
modern_entry_count         = 2576
modern_image_count         = 685
modern_support_contact     = burkinalangues@gmail.com
modern_windows_installer   = local_file_present
modern_windows_filename    = San Matya - Lexique Pro Setup.exe
installer_format           = inno_setup_5_3_10_unicode
installer_sha256           = 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
embedded_lexique_pro_data  = confirmed
embedded_main_data         = San du Nord Matya.lpLiftEnc
embedded_ranges            = San du Nord Matya.lift-ranges
embedded_indexes           = san_matya_french_english_idx_present
embedded_pictures          = confirmed_many
embedded_audio             = not_confirmed_yet
modern_license             = not_confirmed
static_inspection          = listing_completed_extraction_next
bulk_harvest               = deferred_pending_rights
```
