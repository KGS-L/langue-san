# Variétés linguistiques

Le projet utilise « San » comme nom général, mais les données ne doivent pas être traitées comme une seule variété homogène.

## Variétés de travail initiales

| Variété | Code ISO 639-3 utilisé dans la documentation du projet | Statut dans le projet |
| --- | --- | --- |
| San Maka / San du Sud | `sbd` | à documenter séparément |
| San Matya | `stj` | à documenter séparément |
| San Maya | `sym` | à documenter séparément |

Cette liste sert d'organisation initiale. La terminologie, les frontières linguistiques et les noms préférés doivent continuer à être confirmés avec des locuteurs et spécialistes.

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
