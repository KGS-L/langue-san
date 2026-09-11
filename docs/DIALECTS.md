# Variétés linguistiques

Le projet utilise « San » comme nom général, mais les données ne doivent pas être traitées comme une seule variété homogène.

## Variétés de travail

| Variété | Code ISO 639-3 utilisé dans la documentation du projet | Statut dans le projet |
| --- | --- | --- |
| San Maka / San du Sud | `sbd` | collectée et documentée séparément |
| San Matya | `stj` | collectée et documentée séparément |
| San Maya | `sym` | collectée et documentée séparément |

Cette liste sert d'organisation initiale. La terminologie, les frontières linguistiques et les noms préférés doivent continuer à être confirmés avec des locuteurs et spécialistes.

## Pas de variété unique ciblée

Le projet ne choisit pas une seule variété comme cible définitive du collecteur. **San Maka, San Matya et San Maya sont toutes les trois dans le périmètre.**

La stratégie est volontairement pilotée par les données :

```text
collecte dans plusieurs communautés
        ↓
classification + validation de la variété
        ↓
mesure de la couverture réelle de chaque variété
        ↓
constitution de corpus séparés / comparables
        ↓
expériences ML uniquement lorsque la quantité et la qualité le permettent
```

Une campagne terrain peut être organisée localement dans une zone donnée pour des raisons pratiques sans transformer cette zone en « variété officielle » du projet.

Le projet ne doit pas compenser artificiellement un manque de données d'une variété en la mélangeant silencieusement avec une autre. Si une variété dispose de trop peu de données pour un entraînement fiable, elle reste documentée dans le corpus jusqu'à ce que la couverture soit suffisante.

## Localité côté utilisateur, variété côté validation

Le formulaire public ne demande pas au contributeur de choisir « Maka », « Matya » ou « Maya ». Cette terminologie technique n'est pas forcément connue de tous les locuteurs.

Le contributeur indique plutôt la localité ou le contexte où il a principalement appris/parlé le San : par exemple Toma, Tougan, une autre localité, plusieurs localités ou « je ne sais pas ».

Le référentiel interne peut contenir une **suggestion de variété liée à une localité**, lorsque cette relation est appuyée par la documentation. Cette suggestion est uniquement une aide pour le validateur : elle ne doit jamais devenir automatiquement la variété validée d'une contribution.

Relations initiales documentées :

- Toma → suggestion interne `San Maka / Southern Samo [sbd]`, appuyée notamment par Berthelette (2001) et Platiel (1974) ;
- Tougan → suggestion interne `San Matya [stj]`, Tougan apparaissant comme nom associé à Matya Samo dans les référentiels linguistiques consultés.

Le système conserve la provenance de cette suggestion afin de pouvoir la corriger si les spécialistes du projet affinent le référentiel.

## Pourquoi séparer les variétés ?

Des différences peuvent concerner :

- vocabulaire ;
- prononciation ;
- formes grammaticales ;
- orthographe ;
- expressions naturelles.

Mélanger des formes non étiquetées peut produire un dataset incohérent et un modèle qui retourne une forme d'une variété à un utilisateur d'une autre variété.

## Champs recommandés

```text
locality_declared
suggested_variety_id
suggested_variety_status
suggested_variety_source
variety_validated
validation_status
validator_id
```

## Si le contributeur ne connaît pas sa variété

Ce n'est pas un problème. Le contributeur peut simplement indiquer la localité ou le contexte où il a appris/parlé le San.

La variété reste une donnée de validation linguistique. Un validateur compétent peut confirmer, modifier ou laisser la classification indéterminée.

## Règle pour le Machine Learning

Par défaut, une donnée dont la variété n'est pas suffisamment établie ne doit pas rejoindre automatiquement un dataset d'entraînement ciblant une variété précise.

Les décisions d'entraînement doivent être prises **après mesure du corpus validé**. Selon les volumes disponibles, nous pourrons entraîner ou évaluer :

- un modèle dédié à une variété suffisamment couverte ;
- plusieurs modèles séparés ;
- ou, plus tard, un modèle multi-variétés avec étiquettes explicites si les données permettent de le faire proprement.

Aucune de ces options ne doit être décidée en masquant l'identité réelle des données collectées.
