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

Un installateur Windows a maintenant été téléchargé manuellement et placé localement dans le projet :

```text
San Matya - Lexique Pro Setup.exe
taille observée : ~21 MiB
```

Important : ce fichier est **Matya / stj**, et non San du Sud / Maka / sbd. Le dossier local ne doit donc pas rester nommé `san_sud_lexique_pro`, afin d'éviter tout mélange de variété.

Statut actuel de cet installateur :

```text
fichier local           : présent
exécution               : non requise
inspection statique     : à faire
contenu embarqué        : inconnu
relation avec 2011      : non démontrée
licence réutilisation   : non confirmée
bulk harvest            : non approuvé avant clarification des droits
```

L'inspection autorisée à ce stade est uniquement technique et locale : type d'installateur, listing des fichiers embarqués, formats Lexique Pro/LIFT/XML/DB et inventaire éventuel des médias. Aucun exécutable ne doit être lancé pour cette inspection.

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
installateur Lexique Pro local       : récupéré manuellement (~21 MiB)
licence de réutilisation moderne     : non confirmée
bulk harvest                          : non approuvé
```

La présence locale d'un installateur ne vaut pas autorisation de republier, d'entraîner un modèle ou d'exploiter commercialement son contenu.

## 6. Prochaine inspection technique

Ordre de travail local :

```text
1. renommer le dossier local en san_matya_lexique_pro
2. calculer SHA-256 et identifier le type d'installateur
3. lister le contenu sans exécuter le .exe
4. rechercher .lift/.xml/.db/.sqlite/.txt/.html + audio/images
5. si données structurées trouvées : documenter structure et provenance
6. ne pas effectuer de bulk harvest publié tant que les droits ne sont pas clarifiés
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
modern_windows_installer   = local_file_present_approx_21MiB
modern_windows_filename    = San Matya - Lexique Pro Setup.exe
modern_license             = not_confirmed
static_inspection          = pending
bulk_harvest               = deferred_pending_rights
```
