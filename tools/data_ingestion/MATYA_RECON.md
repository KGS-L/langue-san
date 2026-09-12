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

Un installateur Windows a été téléchargé manuellement puis déplacé dans :

```text
data/raw/san_matya_lexique_pro/San Matya - Lexique Pro Setup.exe
```

Identification statique réelle :

```text
file      : PE32 executable (GUI) Intel 80386, for MS Windows, 9 sections
installer : Inno Setup Setup Data 5.3.10 (Unicode)
messages  : Inno Setup Messages 5.1.11 (Unicode)
SHA-256   : 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
7z        : non installé au moment du probe
```

Important : ce fichier est **Matya / stj**, et non San du Sud / Maka / sbd.

Le fait qu'il s'agisse d'un installateur Inno Setup est utile : son contenu peut normalement être inspecté/extrait sans exécuter le programme Windows, avec un outil adapté tel que `innoextract`.

Statut actuel :

```text
fichier local           : présent
exécution               : non requise
inspection PE/Inno      : terminée
listing contenu         : à faire avec innoextract
contenu lexical embarqué: inconnu
relation avec 2011      : non démontrée
licence réutilisation   : non confirmée
bulk harvest            : non approuvé avant clarification des droits
```

## 4. Séparation des domaines

Une application biblique ANTBA en San Matya existe également. Elle relève du domaine religieux et ne doit pas être confondue avec le lexique général.

```text
lexique général ≠ texte biblique
```

## 5. Droits et accès

État actuel :

```text
référence bibliographique historique : confirmée
PDF officiel 2011                    : non retrouvé à ce stade
notice primaire ANTBA/SIL            : à retrouver
droits de l'ouvrage 2011             : à clarifier
application moderne                  : confirmée
installateur Lexique Pro local       : récupéré et fingerprinté
licence de réutilisation moderne     : non confirmée
bulk harvest                          : non approuvé
```

La présence locale d'un installateur ne vaut pas autorisation de republier, d'entraîner un modèle ou d'exploiter commercialement son contenu.

## 6. Prochaine inspection technique

Ordre de travail local :

```text
1. installer innoextract sur Ubuntu
2. lister le contenu sans exécuter le .exe
3. extraire dans un sous-dossier local isolé si le listing est exploitable
4. rechercher .lift/.xml/.db/.sqlite/.txt/.html + audio/images
5. identifier le format source Lexique Pro et les relations média ↔ entrées
6. documenter structure/provenance
7. ne pas republier les données tant que les droits ne sont pas clarifiés
```

## 7. Règle de vitesse

```text
1. inspection statique courte de l'installateur local
2. recherche courte du PDF / catalogue / notice primaire 2011
3. si structure exploitable + droits suffisants : collecteur/extraction
4. sinon : documenter le blocage
5. passer à la source primaire Maya suivante
```

L'objectif est d'éviter de rester bloqué sur une source difficile d'accès.

## 8. Statut actuel

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
modern_windows_sha256      = 1c1ece4f0ad78e8b634c9ebb8c7ae8a97870fa1d6015cde12ba335584c9c467c
modern_windows_format      = PE32_Inno_Setup_5.3.10_unicode
modern_license             = not_confirmed
static_inspection          = installer_identified_listing_pending
bulk_harvest               = deferred_pending_rights
```
