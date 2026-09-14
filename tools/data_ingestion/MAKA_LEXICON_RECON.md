# San Maka / Southern Samo `sbd` — reconnaissance

Cette note suit la piste Maka / San du Sud dans `data_ingestion`.

## 1. Identité de la variété

```text
nom principal : San Maka / San du Sud / Southern Samo
ISO 639-3     : sbd
Glottocode    : sout2844
dialecte      : Toma / Makaa / Nyaana
```

La variété reste strictement séparée de `stj` (Matya) et `sym` (Maya).

## 2. Lexique historique 2003

Référence bibliographique confirmée par ASJP :

```text
SIL Burkina Faso. 2003.
Boo nɛn sɛwɛ san-fransi, fransi-san
[Lexique san–français, français–san].
Ouagadougou: SIL Burkina Faso.
```

ASJP `SOUTHERN_SAMO_SAN` cite explicitement `Burkina 2003` comme source.

Des bibliographies secondaires décrivent une édition préliminaire d'environ 120 pages. Une attribution secondaire à Anne-Marie Giménez existe, mais elle n'est pas considérée comme auteur confirmé tant qu'une notice primaire n'a pas été retrouvée.

État :

```text
référence bibliographique : confirmée
copie numérique primaire  : non retrouvée
licence                    : non confirmée
```

## 3. Webonary moderne — source importante

Une ressource moderne directement dédiée à `sbd` existe :

```text
nom       : Dictionnaire San du sud
plateforme: Webonary
URL       : https://www.webonary.work/san-sud/
ISO       : sbd
variété   : Toma / Makaa
copyright : © 2021 SIL International®
```

L'introduction Webonary confirme explicitement le code ISO `sbd` et le dialecte Toma/Makaa. Le site permet de parcourir les entrées en Southern San, français et anglais.

Le copyright visible ne fournit pas à lui seul une autorisation de bulk harvest, redistribution ou entraînement ML.

## 4. Application Android moderne

Application actuelle :

```text
nom        : San dictionnaire
éditeur    : Burkina Langues
package    : com.dict.toma.san
support    : burkinalangues@gmail.com
développeur: Urs Niggli
```

La fiche Google Play décrit explicitement le lexique comme basé sur le san du Sud `mà kaa` et annonce :

```text
environ 2 220 mots
plus de 1 000 images
plus de 2 200 fichiers audio
français + anglais
prononciation audio
édition préliminaire
```

Cette ressource est particulièrement intéressante pour Langue_SAN parce qu'elle pourrait fournir à la fois des données lexicales et un corpus de prononciations isolées.

## 5. Version Windows Lexique Pro

Le site Mooré Burkina Faso propose publiquement :

```text
San du sud - Lexique Pro Setup.exe
volume affiché : 36.65 MB
contenu annoncé : dictionnaire San–français–anglais avec audio et images
```

Cette distribution est donc une candidate forte pour une inspection technique locale comparable à Matya.

Cependant, contrairement au cas Matya, aucune autorisation explicite n'a encore été obtenue pour analyser/récupérer les fichiers de cette distribution au-delà d'un inventaire de surface.

Décision actuelle :

```text
download_publicly_offered       = confirmed
static_inventory_candidate      = yes
protected_data_recovery         = not_authorized_yet
bulk_lexical_extraction         = not_authorized_yet
bulk_audio_extraction           = not_authorized_yet
```

## 6. Relation entre 2003 et la ressource moderne

Le lien exact entre :

```text
lexique SIL Burkina Faso 2003
Webonary 2021
application Burkina Langues
Lexique Pro Windows
```

n'est pas encore démontré.

Le volume moderne d'environ 2 220 mots et sa présentation comme lexique de référence rendent une continuité documentaire plausible, mais le projet ne doit pas l'affirmer sans source primaire ou confirmation du détenteur des données.

## 7. Droits et demande envoyée

État actuel :

```text
Webonary copyright             = SIL International 2021
licence explicite corpus       = not_confirmed
publication_approved           = false
training_approved              = false
commercial_use_approved        = false
bulk_harvest_approved          = false
```

Le contact technique/public identifié est :

```text
burkinalangues@gmail.com
```

Une demande écrite a été envoyée le **14 septembre 2026** afin de clarifier :

```text
- relation avec le lexique 2003 ;
- autorité sur les données lexicales et audio ;
- analyse des fichiers Lexique Pro ;
- récupération du lexique et des fichiers audio ;
- constitution d'un corpus open source non commercial ;
- recherche ;
- entraînement/évaluation traduction ;
- entraînement/évaluation ASR et autres modèles ML ;
- droit ou non de redistribution publique du corpus récupéré.
```

Statut :

```text
contact_status = mail_sent
response       = pending
```

Si Burkina Langues n'est pas détenteur des droits, le projet demandera le contact SIL/ayant droit approprié.

## 8. Valeur potentielle pour le futur modèle

Si les droits sont obtenus, cette source pourrait être l'une des plus utiles du projet :

```text
~2 220 entrées lexicales
>2 200 fichiers audio de prononciation
>1 000 images
FR / EN / sbd
```

Les fichiers audio ne remplacent pas la parole naturelle, mais ils sont potentiellement très utiles pour :

```text
prononciation lexicale
alignement mot ↔ audio
phonétique / lexique audio
pré-entraînement ou adaptation ASR
évaluation de prononciation
```

Ils devront rester séparés des futurs corpus de conversations, reportages et récits naturels.

## 9. Statut actuel

```text
discovery_2003                  = confirmed
variety                         = maka
iso_639_3                       = sbd
glottocode                      = sout2844
bibliographic_year              = 2003
historical_publisher            = SIL Burkina Faso
primary_2003_digital_copy       = not_found_yet

modern_webonary                 = confirmed
modern_webonary_copyright       = SIL_International_2021
modern_android_app              = confirmed
modern_android_package          = com.dict.toma.san
modern_android_developer        = Burkina_Langues
modern_windows_lexique_pro      = confirmed
modern_windows_size_mb          = 36.65
modern_volume_announced         = about_2220_words
modern_image_count_announced    = more_than_1000
modern_audio_count_announced    = more_than_2200

modern_explicit_license         = not_confirmed
rights_status                   = authorization_requested
contact_status                  = mail_sent_2026_09_14
response_status                 = pending
bulk_harvest                    = blocked_pending_authorization
technical_ingestion_status      = waiting_for_rights
```

## 10. Prochaine action

La reconnaissance Maka est maintenant suspendue proprement dans l'attente de la réponse du détenteur ou du contact technique.

Aucune extraction massive du Lexique Pro, du Webonary ou des fichiers audio ne sera effectuée avant clarification des droits.

Pendant cette attente, `data_ingestion` poursuit l'inventaire des autres sources externes et prépare la future phase média/audio.
