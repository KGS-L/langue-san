# Collector — Laravel

Ce dossier accueille l'application web de collecte, transcription et validation du projet **Langue SAN**.

> 📘 Le cahier des charges complet est disponible dans [`CAHIER_DES_CHARGES.md`](./CAHIER_DES_CHARGES.md).

## Objectif

Permettre à un contributeur de répondre rapidement à une petite session de mots et phrases françaises en fournissant :

- une réponse écrite en San ;
- un enregistrement audio ;
- ou les deux.

Le contributeur ne doit pas avoir besoin de connaître les appellations linguistiques techniques comme Maka, Matya ou Maya. Le parcours public recueille plutôt la **localité ou zone où la personne a appris/parlé le San** (par exemple Toma, Tougan ou une autre localité). La variété linguistique est ensuite confirmée dans le workflow de validation interne.

## Parcours public MVP

```text
Landing page convaincante
        ↓
[ Commencer ]
        ↓
Consentement
        ↓
Profil linguistique / localité
        ↓
Choix du thème
        ↓
Session d'environ 10 prompts
        ↓
Réponse texte et/ou audio
        ↓
Merci / contribuer encore
```

Les premières catégories prévues sont notamment :

- Salutations ;
- Présentation / identité ;
- Famille ;
- Nombres ;
- Temps / jours ;
- Nourriture ;
- Maison ;
- Marché / commerce ;
- Déplacements ;
- École ;
- Travail.

## Dashboard

Le dashboard administrateur utilisera le template **NiceAdmin**.

Il doit couvrir notamment :

```text
Statistiques
Catégories
Prompts
Sessions
Contributions
Audio
Transcription
Validation
Localités / variétés
Dataset approuvé
Exports
Consentements
Utilisateurs / rôles
```

## Attribution des prompts

Éviter un tirage purement aléatoire. Le système doit favoriser les prompts ayant le moins de contributions/validations, avec une part d'aléatoire pour garder les sessions variées.

La session cible est configurable ; l'objectif initial recommandé est d'environ **7 mots + 3 phrases** par session lorsque le contenu disponible le permet.

## Audio

Les audios doivent être stockés dans un espace privé et ne jamais être commités dans GitHub.

Au début, aucun système automatique de speech-to-text San n'est supposé fiable. Les audios sont transcrits manuellement par des validateurs compétents.

## Stack envisagée

- Laravel ;
- Blade ;
- Bootstrap / JavaScript léger pour le parcours public ;
- NiceAdmin pour le dashboard ;
- MySQL pour le MVP ;
- stockage privé Laravel, puis éventuellement S3-compatible ;
- Google Colab / Python seulement plus tard, après constitution de données validées.

## Prochaine étape

Initialiser Laravel directement dans ce dossier, intégrer NiceAdmin, puis suivre les sprints décrits dans le [cahier des charges](./CAHIER_DES_CHARGES.md).
