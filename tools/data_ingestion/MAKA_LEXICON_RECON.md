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

## 2. Lexique historique 2003 — copie primaire désormais reçue

Référence de travail :

```text
SIL Burkina Faso. 2003.
Boo nɛn sɛwɛ san-fransi, fransi-san
[Lexique san–français, français–san].
Ouagadougou: SIL Burkina Faso.
```

ASJP `SOUTHERN_SAMO_SAN` cite explicitement `Burkina 2003` comme source.

Le **14 septembre 2026**, Urs Niggli / Burkina Langues a confirmé par courriel que l'application moderne `San dictionnaire` est bien issue du dictionnaire SIL 2003 et a transmis une copie numérique du lexique ainsi que deux index.

Fichiers reçus en pièces jointes :

```text
San-dic1-64 fev 2021.doc
  taille   : 1 853 440 octets
  sha256   : afa1713cee9efebd5655afad0da19c0c78d3acca01f156abdcf75ed162aa09c3

San du sud - Index (français).rtf
  taille   : 2 077 025 octets
  sha256   : dc17b9a3b3d5f02c88e1bab20e9d03f5debf4d0cfbe492beb00c6119a2b5933b

San du sud - Index (anglais).rtf
  taille   : 908 701 octets
  sha256   : f411862d04047245d50fd8278a0165a35d14430f00dde799cdd856f91a25120d
```

Ces fichiers doivent être conservés en RAW privé/local, sans modification et sans publication automatique.

### Informations confirmées directement par le document

Le document primaire indique notamment :

```text
édition                     : préliminaire
première impression         : premier trimestre 2003
copyright                   : TOUS DROITS RESERVES
ayant droit affiché         : Société Internationale de Linguistique (SIL)
volume lexical annoncé      : environ 2 200 mots
variété principale          : san du Sud / mà kaa
zone de référence           : Toma et Yaba
```

La préface distingue explicitement trois dialectes :

```text
mà kaa
mà tiaa
mà yaa
```

Le lexique 2003 est basé principalement sur des données recueillies par **M. Phillips** à Toma entre 1985 et 1997, complétées par des données de **Kathryn Woodham** en 2000–2001. Le document remercie également plusieurs collaborateurs sanphones.

Le champ `Author` des métadonnées du fichier Word reçu contient `Anne-Marie Gimenez`. Cette information est conservée comme **métadonnée du fichier**, sans la transformer automatiquement en attribution bibliographique définitive si le document imprimé ne l'affiche pas explicitement comme auteur.

Le fichier Word reçu contient 129 pages selon ses métadonnées Microsoft Word.

## 3. Relation entre 2003 et les ressources modernes — confirmée

Le lien généalogique qui était auparavant seulement supposé est maintenant confirmé par Urs Niggli :

```text
lexique SIL 2003
      ↓
base utilisée dans FieldWorks / FLEx
      ↓
application moderne San dictionnaire
      ↓
Webonary / distributions numériques associées
```

Urs Niggli précise qu'il a surtout facilité la distribution numérique du lexique afin de soutenir la langue san.

L'application comporte environ **2 220 entrées**. Cette valeur est cohérente avec le document 2003, qui annonce lui-même environ **2 200 mots**.

## 4. Audio — provenance précisée

Urs Niggli indique ne pas disposer des fichiers audio séparément.

Les mots ont été enregistrés directement dans **FieldWorks / FLEx** par **Mme Zan Awa**, étudiante à l'université à Ouagadougou.

Donc :

```text
speaker / recording contributor : Zan Awa
recording_environment            : FieldWorks / FLEx
audio_files_separate             : non disponibles auprès d'Urs Niggli
application_audio                : dérivé de la base FLEx / distribution numérique
```

Les audios restent potentiellement très utiles pour la phonétique et l'ASR lexical, mais leur extraction et leur utilisation ML restent soumises aux droits du corpus source et, selon le cas, aux droits liés aux enregistrements.

## 5. Webonary et application moderne

Ressources confirmées :

```text
Webonary : Dictionnaire San du sud
ISO      : sbd
variété  : Toma / Makaa
copyright visible : © 2021 SIL International®

Android  : San dictionnaire
package  : com.dict.toma.san
éditeur  : Burkina Langues
volume   : ~2 220 entrées
images   : >1 000 annoncées
audio    : >2 200 prononciations annoncées
```

Urs Niggli indique que l'application avait atteint **1 818 téléchargements Google Play** au moment de sa réponse du 14 septembre 2026. Ce chiffre est informatif seulement et n'entre pas dans les métriques linguistiques du corpus.

## 6. Droits — clarification importante

La réponse reçue est très utile pour la provenance, mais **elle ne constitue pas encore une autorisation juridique suffisante du détenteur des droits**.

Urs Niggli écrit explicitement qu'il ne pense pas disposer lui-même d'une autorisation sur le dictionnaire. À la question sur la redistribution publique d'un corpus open source, il répond que, **de son point de vue**, cela lui paraît acceptable.

Le document primaire reçu affiche cependant :

```text
TOUS DROITS RESERVES
© Société Internationale de Linguistique (SIL)
```

Décision du projet :

```text
response_received                = true
source_relationship_confirmed    = true
primary_digital_copy_received    = true
urs_personal_support             = true
urs_rights_authority             = not_confirmed
formal_rights_holder_permission  = still_required
publication_approved             = false
training_approved                = false
commercial_use_approved          = false
bulk_harvest_approved            = false
```

Le projet ne transforme donc pas la phrase « De mon point de vue: Oui » en licence ouverte ou en autorisation SIL.

## 7. Prochaine demande de droits

Le prochain contact doit viser **SIL Global / l'entité SIL détenant les droits de l'édition 2003** afin d'obtenir une autorisation écrite couvrant explicitement :

```text
- conservation locale du document primaire et des exports ;
- extraction structurée du lexique ;
- analyse et correction linguistique ;
- récupération/usage des prononciations audio ;
- constitution d'un corpus ;
- recherche ;
- entraînement et évaluation traduction / ASR / ML ;
- redistribution publique éventuelle du dataset dérivé ;
- cadre open source non commercial ;
- conditions d'attribution et licence à appliquer.
```

Tant que cette réponse n'est pas obtenue, la copie reçue peut être inventoriée et conservée, mais elle n'est pas marquée `training_approved` ni `publication_approved`.

## 8. Ressource média supplémentaire

Dans un second courriel, Urs Niggli signale également un **petit clip MP4 de l'alphabet**, disponible sur Mooré Burkina Faso dans la page `Alphabet en langues nationales`, **numéro 27**.

Cette ressource est inscrite dans l'inventaire média séparé et ne doit pas être fusionnée avec le corpus lexical.

## 9. Statut actuel

```text
discovery_2003                  = confirmed
variety                         = maka
iso_639_3                       = sbd
glottocode                      = sout2844
bibliographic_year              = 2003
historical_publisher            = SIL Burkina Faso
primary_2003_digital_copy       = received_2026_09_14
primary_document_rights         = all_rights_reserved_sil
primary_document_word_count     = about_2200_lexical_items
primary_document_pages_metadata = 129

modern_webonary                 = confirmed
modern_android_app              = confirmed
modern_android_package          = com.dict.toma.san
modern_source_relation_2003     = confirmed_by_urs_niggli
modern_volume_announced         = about_2220_entries
recording_contributor           = Zan_Awa
recording_tool                  = FieldWorks_FLEx
separate_audio_from_contact     = unavailable

contact_burkina_langues         = response_received_2026_09_14
rights_holder_permission        = still_required
rights_status                   = primary_copyright_confirmed_permission_pending
bulk_harvest                    = blocked_pending_rights_holder_permission
technical_ingestion_status      = source_recovered_rights_pending
```

## 10. Décision

La piste Maka a fortement progressé : la **source primaire 2003 est désormais disponible**, la relation avec l'application moderne est confirmée et la provenance des enregistrements est documentée.

Le blocage restant n'est plus l'accès aux données mais **l'autorisation formelle du détenteur des droits SIL**.

Pendant cette attente, `data_ingestion` peut continuer le dernier inventaire externe et la préparation de la phase média/audio.