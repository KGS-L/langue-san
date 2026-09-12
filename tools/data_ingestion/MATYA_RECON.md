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

Les 3 fichiers `.db` identifiés appartiennent aux domaines sémantiques génériques de Lexique Pro (`xxdict2.db`, `xxdict3.db`, `xxdict4.db`) et ne sont pas considérés comme le corpus lexical Matya.

Fichiers structurants :

```text
San du Nord Matya.lpLiftEnc
San du Nord Matya.lpConfigEnc
San du Nord Matya.lift-ranges
San du Nord Matya - San Matya.idx
San du Nord Matya - French.idx
San du Nord Matya - English.idx
```

Le package Windows ne contient aucun audio détecté.

## 6. Probe final des formats

### `lpLiftEnc` et `lpConfigEnc`

Les deux fichiers sont détectés simplement comme `data`. Leur entête est binaire opaque, sans signature XML/LIFT ni texte structuré lisible au début. `strings` sur `lpLiftEnc` ne fournit que des fragments aléatoires non exploitables.

Conclusion :

```text
lpLiftEnc   = copie de distribution protégée/opaque
lpConfigEnc = configuration protégée/opaque
```

Le projet ne tente pas de contourner ou casser cette protection.

### `lift-ranges`

`San du Nord Matya.lift-ranges` est un document XML UTF-8 valide et lisible. Il contient les ranges LIFT génériques (étymologie, informations grammaticales, parties du discours, etc.).

Ce fichier est utile pour comprendre le schéma lexical, mais **ce n'est pas le corpus d'entrées du dictionnaire**.

### Index English / French / San Matya

`San du Nord Matya - English.idx` est du texte ASCII lisible. Il associe des termes anglais à un ou plusieurs identifiants d'entrées, par exemple :

```text
after            307
amulette          328
animal            136
celebration       61,115
honeycomb         2736
```

`San du Nord Matya - French.idx` est également un index textuel, avec encodage ancien/non-UTF-8 pour certains caractères français. Il associe des termes français à des identifiants, par exemple :

```text
abandonner   1318
abeille      2753
aboyer       966
acacia       1054
acheter      1894
```

`San du Nord Matya - San Matya.idx` est identifié comme texte ASCII mais `strings` ne laisse apparaître, dans le probe actuel, que des identifiants numériques. Il faut encore examiner les octets/lignes brutes pour déterminer si les formes Matya sont présentes dans un encodage ou une structure que `strings` ignore.

Les identifiants observés montent au moins jusqu'à ~2753. Ce nombre est intriguant car RefLex expose 2743 unités Matya, mais **un identifiant maximal n'est pas un nombre d'entrées** et cette proximité ne prouve pas encore que les deux ressources sont identiques.

## 7. Licence : logiciel ≠ données lexicales

`licence.txt` concerne **Lexique Pro**, copyright SIL International 2004–2010.

Le texte autorise l'utilisation et la distribution gratuite du logiciel avec un lexique uniquement si le distributeur :

```text
1. est propriétaire des données lexicales
   OU
2. a reçu l'autorisation de les distribuer
```

La licence interdit également le reverse engineering, la décompilation et le désassemblage du logiciel.

Conclusion importante :

```text
licence Lexique Pro ≠ licence des données San Matya
```

Le fichier `licence.txt` ne nous accorde pas de droit de republier, entraîner un modèle ou exploiter commercialement le contenu lexical Matya. Les droits des données restent à clarifier auprès de la source/éditeur/détenteur.

## 8. Décision technique

Le package Windows est utile pour :

```text
- confirmer l'existence et la structure d'un dictionnaire Matya conséquent
- récupérer un inventaire d'environ 695 images
- disposer des index français/anglais → identifiants
- comprendre le schéma LIFT via lift-ranges
- comparer ultérieurement les identifiants/glosses avec RefLex
```

Mais il ne fournit pas directement un LIFT source librement lisible. Le corpus principal reste protégé dans `lpLiftEnc`.

La dernière vérification locale autorisée est donc limitée à l'encodage/structure brute des trois `.idx`, notamment l'index San Matya. Après cela, si les formes Matya ne sont pas directement accessibles, on arrête l'inspection du package et on privilégie :

```text
1. LIFT/PDF original Morris et al. 2011
2. export officiel Lexique Pro si disponible
3. autorisation/source fournie par les mainteneurs
4. comparaison avec RefLex déjà récolté
```

## 9. Droits et accès

État actuel :

```text
référence historique 2011      : confirmée
PDF/LIFT original 2011         : non retrouvé à ce stade
droits ouvrage 2011            : à clarifier
application moderne            : confirmée
installateur Lexique Pro       : récupéré et extrait localement
contenu lexical embarqué       : confirmé mais corpus principal protégé
images                         : 695 fichiers dans le package
fichiers audio                 : 0 dans ce package
lift-ranges                    : XML lisible
index anglais/français         : lisibles comme lookup terme → ids
index San Matya                : probe brut encore nécessaire
licence logiciel               : Lexique Pro freeware sous conditions
licence données Matya          : non confirmée
bulk harvest/publication       : non approuvés
```

## 10. Règle de vitesse

```text
probe brut rapide de San Matya.idx
        ↓
si formes Matya lisibles → documenter structure / comparer avec RefLex
sinon → arrêter inspection package
        ↓
recherche courte Morris et al. 2011 / LIFT / PDF / droits
        ↓
passer à la source Maya
```

On ne doit pas rester bloqué à essayer de casser `lpLiftEnc`.

## 11. Statut actuel

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
modern_entry_count_claimed = 2576
modern_image_count_claimed = 685
modern_support_contact     = burkinalangues@gmail.com
modern_windows_installer   = extracted_locally
modern_windows_filename    = San Matya - Lexique Pro Setup.exe
installer_format           = inno_setup_5_3_10_unicode
installer_sha256           = 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
extracted_file_count       = 734
extracted_image_count      = 695
extracted_audio_count      = 0
embedded_main_data         = protected_opaque_lpLiftEnc
embedded_config            = protected_opaque_lpConfigEnc
embedded_ranges            = readable_lift_ranges_xml
english_index              = readable_term_to_entry_ids
french_index               = readable_legacy_encoded_term_to_entry_ids
san_matya_index            = raw_structure_probe_pending
software_license           = lexique_pro_only_not_data_license
modern_data_license        = not_confirmed
static_inspection          = almost_complete_one_idx_probe_remaining
bulk_harvest               = deferred_pending_rights_and_source_access
```
