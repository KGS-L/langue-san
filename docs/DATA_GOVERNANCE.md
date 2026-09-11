# Gouvernance des données

Ce document décrit les règles initiales de gestion des données du projet. Il sera affiné avant l'ouverture publique de la collecte.

## 1. Code open source ≠ données automatiquement ouvertes

Le code de ce dépôt est sous Apache-2.0. Cette licence ne s'applique pas automatiquement :

- aux enregistrements audio ;
- aux traductions collectées ;
- aux récits naturels et leurs transcriptions ;
- aux métadonnées des contributeurs ;
- aux ressources provenant de dictionnaires, livres, applications ou corpus tiers.

Toute publication d'un dataset doit avoir sa propre licence et une provenance claire.

## 2. Consentement

Avant tout enregistrement ou contribution destinée à être réutilisée, le site doit expliquer clairement les usages prévus.

Le consentement doit distinguer autant que possible :

- stockage de la contribution ;
- utilisation pour transcription et validation ;
- segmentation et traduction des récits naturels ;
- utilisation pour construire un corpus ;
- utilisation pour entraînement et évaluation de modèles ;
- publication éventuelle du texte ;
- publication éventuelle de l'audio.

La publication publique de l'audio ne doit jamais être supposée implicitement.

## 3. Minimisation des données personnelles

Le projet doit collecter seulement ce qui est nécessaire. Un identifiant pseudonyme peut être utilisé à la place d'un nom réel.

Les informations linguistiques utiles peuvent inclure la localité d'apprentissage ou d'usage, le niveau de maîtrise et, après validation, la variété linguistique. Le formulaire public ne doit pas exiger du contributeur qu'il connaisse les labels techniques Maka, Matya ou Maya.

## 4. Stockage

Les audios bruts et datasets privés doivent rester hors du dépôt GitHub.

Exemples de zones privées :

```text
apps/collector/storage/app/private/recordings/
data/private/
data/raw/
```

Des sauvegardes chiffrées et un contrôle d'accès devront être mis en place avant une collecte à grande échelle.

## 5. Deux formes de collecte complémentaires

### 5.1 Français → San élicité

Le contributeur traduit des mots, expressions et phrases françaises ciblées. Cette forme produit des paires parallèles contrôlées et facilite la mesure de couverture de concepts précis.

### 5.2 Parole naturelle San → San

Une consigne thématique sert uniquement à déclencher un récit naturel en San : histoire familiale, mariage, marché, agriculture, conte, proverbe, souvenir, etc.

Pipeline :

```text
consigne thématique
      ↓
audio San naturel
      ↓
transcription San complète
      ↓
segmentation en phrases / unités utiles
      ↓
traduction française de chaque segment
      ↓
validation linguistique
```

La consigne française n'est jamais la traduction du récit. Elle reste uniquement une métadonnée d'élicitation.

Cette deuxième source est importante pour limiter le biais structurel qu'un corpus composé uniquement de traductions du français pourrait introduire dans le San collecté.

## 6. Cycle de validation

Statuts :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Pour une contribution de parole naturelle, `transcribed` ne suffit pas à autoriser la validation : le récit doit aussi avoir été segmenté et chaque segment doit comporter une transcription San et une traduction française.

Une donnée utilisée pour l'entraînement doit être traçable jusqu'à sa provenance et à son statut de validation.

## 7. Variétés linguistiques et localités

Conserver séparément au minimum :

- localité déclarée par le contributeur ;
- éventuelle variété suggérée par le référentiel ;
- statut et source de cette suggestion ;
- variété validée par un humain ;
- validateur ayant confirmé la classification.

Une correspondance documentée comme Toma → Maka ou Tougan → Matya peut servir d'aide interne au validateur, mais ne doit jamais remplir automatiquement la variété validée d'une contribution.

Une contribution dont la variété reste indéterminée peut être conservée, mais ne doit pas être fusionnée silencieusement avec une variété connue.

## 8. Segmentation et prévention des fuites ML

Un récit naturel est une source unique même s'il produit plusieurs dizaines de segments.

Tous les segments provenant du même récit doivent :

- conserver le même identifiant de source pseudonymisé ;
- rester ensemble dans le même split `train`, `validation` ou `test` ;
- ne jamais être répartis aléatoirement indépendamment les uns des autres.

La même règle s'applique aux futures paires inversées : une paire dérivée d'une source existante doit conserver le split de cette source.

## 9. Ressources tierces

Ne pas copier automatiquement le contenu d'une application, d'un livre ou d'un dictionnaire dans le dataset.

Avant réutilisation, vérifier explicitement le droit de :

- consulter ;
- numériser ;
- transformer ;
- entraîner un modèle ;
- republier ;
- exploiter commercialement, si cela devient pertinent.

## 10. Publication future

Avant toute publication d'un dataset :

- retirer ou pseudonymiser les données personnelles non nécessaires ;
- vérifier les consentements ;
- vérifier les licences de toutes les sources ;
- documenter la méthode de collecte ;
- distinguer données élicitées et parole naturelle ;
- documenter les variétés couvertes ;
- publier une data card ;
- versionner le dataset.

## 11. Suppression et correction

Le système doit permettre de corriger une donnée contestée et de retracer les changements. Une procédure de demande de retrait et une durée de conservation des audios devront être définies avant la collecte publique à grande échelle.
