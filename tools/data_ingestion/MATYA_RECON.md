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

RefLex référence également `Morris et al. 2011 : Matya` comme source amont de ses données Matya.

## 2. Pourquoi cette source est importante

RefLex a déjà fourni au projet 2 743 unités `stj`, mais l'objectif de cette phase est aussi de remonter aux sources primaires et de distinguer source originale, agrégateur et ressource moderne distribuée.

## 3. Ressources numériques actuelles identifiées

Une application Android actuelle existe :

```text
nom : San Matya de A-Z
développeur : Burkina Langues
package : com.matya.san.lexique
zone déclarée : autour de Tougan
volume annoncé : 2 576 entrées
images annoncées : 685
support : burkinalangues@gmail.com
```

Cette application reste traitée comme une ressource moderne distincte tant que sa relation exacte avec l'ouvrage Morris et al. 2011 n'est pas démontrée.

## 4. Installateur Windows Lexique Pro récupéré localement

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

Le fichier est Matya / `stj`, pas San du Sud / Maka / `sbd`.

## 5. Extraction statique de l'installateur — réussie

L'installateur a été extrait localement avec `innoextract`, sans exécuter le programme Windows.

Inventaire observé :

```text
fichiers totaux : 734
jpg             : 675
png             : 16
jpeg            : 3
jfif            : 1
images total     : 695
dll             : 13
ttf             : 10
idx             : 3
db              : 3
htm             : 2
txt             : 1
lpLiftEnc        : 1
lpConfigEnc      : 1
lift-ranges      : 1
audio            : 0
```

Les 3 fichiers `.db` identifiés dans le listing appartiennent aux domaines sémantiques génériques de Lexique Pro (`xxdict2.db`, `xxdict3.db`, `xxdict4.db`) et ne sont pas, à ce stade, considérés comme le corpus lexical Matya.

Fichiers structurants du dictionnaire :

```text
San du Nord Matya.lpLiftEnc
San du Nord Matya.lpConfigEnc
San du Nord Matya.lift-ranges
San du Nord Matya - San Matya.idx
San du Nord Matya - French.idx
San du Nord Matya - English.idx
```

Le package Windows ne contient donc **aucun fichier audio** détecté. Les données principales semblent être encapsulées dans `lpLiftEnc`/`lpConfigEnc`, accompagnées de ranges LIFT, de trois index et d'environ 695 images.

## 6. Interprétation du format Lexique Pro

Lexique Pro sait normalement importer/exporter du LIFT XML, mais une copie de distribution peut contenir un fichier protégé/chiffré `lpLiftEnc` au lieu du LIFT source. Une discussion du support SIL sur une copie de distribution chiffrée indique qu'il n'existe pas de méthode simple prévue pour récupérer le fichier source depuis cette copie.

Règle du projet :

```text
inspection statique        : autorisée localement
lecture des index/licence  : à faire
contournement chiffrement  : non
source LIFT originale      : à rechercher/privilégier
export officiel Lexique Pro: à privilégier si disponible
```

## 7. Étape technique immédiate

Faire une inspection courte et non destructive :

```text
1. `file`, `xxd` et `strings` sur lpLiftEnc/lpConfigEnc
2. lire le début de lift-ranges
3. lire licence.txt
4. inspecter le contenu des 3 fichiers idx
5. déterminer si les idx exposent seulement les clés de recherche ou suffisamment de données lexicales
6. si le corpus reste protégé, arrêter l'inspection et rechercher le LIFT/PDF/source 2011 ou demander une autorisation/export aux mainteneurs
```

## 8. Droits et accès

État actuel :

```text
référence historique 2011      : confirmée
PDF/LIFT original 2011         : non retrouvé à ce stade
droits ouvrage 2011            : à clarifier
application moderne            : confirmée
installateur Lexique Pro       : récupéré et extrait localement
contenu lexical embarqué       : confirmé
images                         : 695 fichiers dans le package
fichiers audio                 : 0 dans ce package
licence réutilisation données  : non confirmée
bulk harvest/publication       : non approuvés
```

La présence d'un exécutable public ou local ne vaut pas autorisation de republier, entraîner un modèle ou exploiter commercialement les données.

## 9. Règle de vitesse

```text
inspection entêtes + idx + licence
        ↓
si données ouvertes/exportables → parser/QA
sinon → documenter blocage
        ↓
recherche courte source originale Morris et al. 2011
        ↓
passer à la source Maya
```

On ne doit pas rester bloqué à essayer de casser `lpLiftEnc`.

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
modern_image_count_claimed = 685
modern_support_contact     = burkinalangues@gmail.com
modern_windows_installer   = extracted_locally
modern_windows_filename    = San Matya - Lexique Pro Setup.exe
installer_format           = inno_setup_5_3_10_unicode
installer_sha256           = 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
extracted_file_count       = 734
extracted_image_count      = 695
extracted_audio_count      = 0
embedded_main_data         = San du Nord Matya.lpLiftEnc
embedded_config            = San du Nord Matya.lpConfigEnc
embedded_ranges            = San du Nord Matya.lift-ranges
embedded_indexes           = san_matya_french_english_idx_present
static_inspection          = extracted_inventory_complete_header_probe_next
modern_license             = not_confirmed
bulk_harvest               = deferred_pending_rights_and_source_access
```
