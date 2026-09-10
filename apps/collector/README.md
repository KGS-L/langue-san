# Collector — Laravel

Ce dossier accueillera le site de collecte et son dashboard administrateur.

## Objectif

Permettre à un contributeur de répondre rapidement à une petite session de mots et phrases françaises en fournissant :

- une réponse écrite en San ;
- un enregistrement audio ;
- ou les deux.

## MVP prévu

Parcours public :

```text
Accueil / explication
        ↓
Consentement
        ↓
Profil linguistique
        ↓
Session d'environ 10 prompts
        ↓
Merci / contribuer encore
```

Dashboard :

```text
Statistiques
Prompts
Contributions
Audio
Transcription
Validation
Export
```

## Attribution des prompts

Éviter un tirage purement aléatoire. Le système doit favoriser les prompts ayant le moins de contributions/validations, avec une part d'aléatoire pour garder les sessions variées.

## Audio

Les audios doivent être stockés dans un espace privé et ne jamais être commités dans GitHub.

Au début, aucun système automatique de speech-to-text San n'est supposé fiable. Les audios peuvent être transcrits manuellement par des validateurs compétents.

## Stack envisagée

- Laravel ;
- Blade + Livewire ou frontend Laravel simple ;
- dashboard admin Laravel ;
- base relationnelle ;
- stockage privé des fichiers audio.

Le choix exact des packages sera documenté lors de l'initialisation du MVP.
