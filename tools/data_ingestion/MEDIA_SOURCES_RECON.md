# Sources média / audio SAN — reconnaissance initiale

Cette note inventorie les ressources audio/vidéo/textes parlés déjà repérées pour les variétés SAN. Elle ne lance pas encore de collecte massive.

Objectif : préparer une future phase dédiée à la parole naturelle, à l'ASR et aux corpus multimodaux, sans mélanger cette étape avec la collecte lexicale actuellement en cours.

## 1. Principes

Les ressources média sont séparées en deux grandes catégories :

```text
A. parole naturelle / semi-naturelle
   reportages
   interviews
   émissions radio
   conversations
   témoignages
   contes
   récits

B. parole spécialisée
   lectures bibliques
   films doublés
   chants
   poésie chantée
   prononciations lexicales isolées
```

Ces catégories ne doivent pas être mélangées sans étiquette de domaine.

Une ressource publiquement accessible n'est pas automatiquement autorisée pour :

```text
bulk download
redistribution
entraînement ML
usage commercial
```

## 2. Southern Samo / San Maka — `sbd`

Plusieurs ressources structurées ont été repérées.

### Scripture Earth

Scripture Earth indexe explicitement `San` avec le code `sbd` et expose des liens vers plusieurs types de ressources :

```text
texte
 audio
 vidéo
 application
 Bible.is
 film de Marc
 film Jésus
 YouVersion
 Global Recordings Network
```

Statut :

```text
source_directory = confirmed
media_available   = confirmed
rights_review     = required_per_provider
harvest           = deferred
```

### Réseau Faso Bibles

Le site Réseau Faso Bibles possède une section `Samo Sud` avec texte, audio et vidéo.

Crédits observés :

```text
texte : Bible Text in San © Alliance Biblique du Burkina Faso, 1995
audio : Audio recording of the Bible Text ℗ 2010 Hosanna
vidéo : Courtesy of LUMO Project Films
```

Des fichiers vidéo sont proposés au téléchargement sur le site, mais la disponibilité au téléchargement ne constitue pas à elle seule une autorisation de constitution/redistribution d'un dataset ML.

Cette source doit donc rester séparée avec ses détenteurs de droits propres.

### Find.Bible / Bible audio

Find.Bible référence notamment :

```text
Southern Samo Bible
San Catholic Edition Bible
JESUS film
God's Story
The Living Christ
Look, Listen & Live 1–8
Mark film
San Bible app
```

Cette plateforme est surtout utile comme annuaire de provenance vers les fournisseurs réels.

## 3. San Matya — `stj`

Scripture Earth possède une page dédiée à `Samo, Matya` (`stj`) avec des liens vers :

```text
Bible.is / ANTBA
ressources audio
Global Recordings Network
SIL.org
```

Ces médias n'ont pas été ajoutés au RAW pendant la phase lexicale.

Le corpus Lexique Pro Matya récupéré ne contient aucun fichier audio :

```text
audio_count = 0
```

Les futures ressources `stj` audio devront donc venir d'autres fournisseurs ou de collecte terrain.

## 4. San Maya — `sym`

Scripture Earth indexe `Samo, Maya` (`sym`) parmi les langues du Burkina Faso.

Le site Leburu, déjà identifié pendant la reconnaissance Maya, contient également des textes et contenus vidéo San Mayaa.

État :

```text
media_candidates = confirmed
exact_inventory  = pending
rights_review    = pending
harvest          = deferred
```

La demande envoyée à ANTBA pour le lexique Maya pourra également aider à clarifier les ressources texte/audio associées à cette variété.

## 5. Prononciations lexicales Maka

L'application moderne `San dictionnaire` / Maka annonce :

```text
>2 200 fichiers audio
~2 220 mots
>1 000 images
```

Ces audios sont des prononciations lexicales et doivent être classés séparément de la parole naturelle.

Catégorie prévue :

```text
speech_type = isolated_lexical_pronunciation
```

Ils pourraient être utiles pour phonétique, lexique audio, adaptation ASR et évaluation de prononciation si les droits sont obtenus.

## 6. Collecte future de parole naturelle

La future phase devra également rechercher activement des contenus non religieux et plus proches de la parole spontanée :

```text
reportages TV
interviews d'artistes et personnalités SAN
radios locales
émissions communautaires
archives audiovisuelles
contes et récits traditionnels
conférences / témoignages
vidéos YouTube / Facebook / médias locaux
```

Pour chaque contenu, les métadonnées minimales seront :

```text
source_url
provider
publication_date
variety
iso_639_3
locality_if_explicit
speaker_id_pseudonymous
speech_type
rights_status
start_time
end_time
transcription_status
translation_status
validation_status
```

## 7. Pipeline cible média

```text
source audio/vidéo
    ↓
provenance + droits
    ↓
identification variété
    ↓
extraction audio
    ↓
segmentation
    ↓
transcription San
    ↓
traduction française
    ↓
validation humaine
    ↓
dataset speech
```

Sorties futures possibles :

```text
San audio → San texte       ASR
San texte → français        traduction
français → San              traduction
San audio → français        speech translation
```

## 8. Décision actuelle

```text
media_discovery_started      = true
bulk_media_harvest           = false
media_training_approved      = false
natural_speech_collection    = future_phase
lexical_ingestion_separation = required
```

La phase actuelle se limite à l'inventaire et à la provenance. La collecte média massive commencera après clôture du bloc lexical externe et définition des règles de droits, stockage, segmentation et validation.