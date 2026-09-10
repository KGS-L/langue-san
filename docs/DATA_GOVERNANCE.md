# Gouvernance des données

Ce document décrit les règles initiales de gestion des données du projet. Il sera affiné avant l'ouverture publique de la collecte.

## 1. Code open source ≠ données automatiquement ouvertes

Le code de ce dépôt est sous Apache-2.0. Cette licence ne s'applique pas automatiquement :

- aux enregistrements audio ;
- aux traductions collectées ;
- aux métadonnées des contributeurs ;
- aux ressources provenant de dictionnaires, livres, applications ou corpus tiers.

Toute publication d'un dataset doit avoir sa propre licence et une provenance claire.

## 2. Consentement

Avant tout enregistrement ou contribution destinée à être réutilisée, le site doit expliquer clairement les usages prévus.

Le consentement doit distinguer autant que possible :

- stockage de la contribution ;
- utilisation pour transcription et validation ;
- utilisation pour construire un corpus ;
- utilisation pour entraînement et évaluation de modèles ;
- publication éventuelle du texte ;
- publication éventuelle de l'audio.

La publication publique de l'audio ne doit jamais être supposée implicitement.

## 3. Minimisation des données personnelles

Le projet doit collecter seulement ce qui est nécessaire. Un identifiant pseudonyme peut être utilisé à la place d'un nom réel.

Les informations linguistiques utiles peuvent inclure la variété déclarée, la localité d'apprentissage ou d'usage et le niveau de maîtrise, sans exiger systématiquement des informations personnelles supplémentaires.

## 4. Stockage

Les audios bruts et datasets privés doivent rester hors du dépôt GitHub.

Exemples de zones privées :

```text
apps/collector/storage/app/private/audios/
data/private/
data/raw/
```

Des sauvegardes chiffrées et un contrôle d'accès devront être mis en place avant une collecte à grande échelle.

## 5. Cycle de validation

Statuts recommandés :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Une donnée utilisée pour l'entraînement doit être traçable jusqu'à sa provenance et à son statut de validation.

## 6. Variétés linguistiques

Conserver séparément au minimum :

- variété déclarée ;
- variété validée ;
- localité ou contexte, si utile ;
- validateur ayant confirmé la classification.

Une contribution `unknown` peut être stockée, mais ne doit pas être fusionnée silencieusement avec une variété connue.

## 7. Ressources tierces

Ne pas copier automatiquement le contenu d'une application, d'un livre ou d'un dictionnaire dans le dataset.

Avant réutilisation, vérifier explicitement le droit de :

- consulter ;
- numériser ;
- transformer ;
- entraîner un modèle ;
- republier ;
- exploiter commercialement, si cela devient pertinent.

## 8. Publication future

Avant toute publication d'un dataset :

- retirer ou pseudonymiser les données personnelles non nécessaires ;
- vérifier les consentements ;
- vérifier les licences de toutes les sources ;
- documenter la méthode de collecte ;
- documenter les variétés couvertes ;
- publier une data card ;
- versionner le dataset.

## 9. Suppression et correction

Le futur système doit permettre de corriger une donnée contestée et de retracer les changements. Une procédure de demande de retrait devra être définie avant la collecte publique à grande échelle.
