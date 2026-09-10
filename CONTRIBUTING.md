# Contribuer à Langue SAN

Merci de votre intérêt pour Langue SAN. Le projet est ouvert aux contributions techniques, linguistiques, documentaires et communautaires.

## Avant de contribuer

Merci de respecter trois principes :

1. ne pas inventer de formes linguistiques ;
2. ne pas mélanger les variétés du San sans les identifier ;
3. ne pas publier de données personnelles, audio ou contenus tiers sans autorisation claire.

## Comment contribuer

### Locuteurs San

Vous pouvez aider à :

- proposer la traduction de mots et phrases ;
- enregistrer des prononciations ;
- corriger une transcription ;
- signaler une variante ;
- confirmer ou contester une traduction ;
- expliquer le contexte d'usage d'une expression.

Les contributions linguistiques destinées au dataset passeront par le système de collecte et de validation prévu par le projet. Évitez de publier directement des audios personnels dans une issue GitHub.

### Linguistes, enseignants et chercheurs

Vous pouvez aider à :

- documenter l'orthographe ;
- distinguer les variétés ;
- définir les champs du corpus ;
- améliorer les procédures de validation ;
- proposer des jeux de test ;
- documenter les phénomènes grammaticaux utiles à la traduction.

### Développeurs

Le premier composant logiciel sera le collecteur Laravel dans `apps/collector/`.

Avant de coder :

1. consultez les issues ouvertes ;
2. commentez l'issue si vous souhaitez la prendre ;
3. créez une branche dédiée ;
4. faites des changements ciblés ;
5. ajoutez ou mettez à jour les tests lorsque pertinent ;
6. ouvrez une Pull Request claire.

Exemples de branches :

```text
feature/audio-recorder
feature/admin-validation
fix/contribution-assignment
docs/data-governance
```

### Data / Machine Learning

La partie ML vivra dans `ml/`. Au démarrage, nous privilégions les expériences reproductibles et petites : notebooks Colab, preprocessing transparent et évaluation séparée du jeu d'entraînement.

Ne publiez pas de dataset privé ou non autorisé dans le dépôt.

## Règles pour les Pull Requests

Une PR doit idéalement :

- résoudre un problème clair ;
- rester limitée à un objectif ;
- expliquer ce qui change ;
- indiquer comment tester ;
- signaler tout impact sur les données, la confidentialité ou les licences.

## Proposer une nouvelle ressource linguistique

Si vous connaissez un dictionnaire, guide d'orthographe, mémoire, thèse, application ou corpus utile, ouvrez une issue avec :

- le titre de la ressource ;
- l'auteur ou l'institution ;
- un lien si disponible ;
- la variété concernée ;
- ce que la ressource pourrait apporter ;
- la licence ou le statut des droits si connu.

Une ressource accessible sur Internet n'est pas automatiquement libre de réutilisation.

## Qualité linguistique

Une contribution ne devient pas automatiquement une vérité de référence. Le projet prévoit des statuts de validation et, lorsque possible, plusieurs validations indépendantes.

## Respect

Toutes les contributions doivent respecter le [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md).
