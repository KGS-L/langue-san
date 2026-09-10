# Variétés linguistiques

Le projet utilise « San » comme nom général du projet, mais les données ne doivent pas être traitées comme une seule variété homogène.

## Variétés de travail initiales

| Variété | Code ISO 639-3 utilisé dans la documentation du projet | Statut dans le projet |
| --- | --- | --- |
| San Maka / San du Sud | `sbd` | à documenter séparément |
| San Matya | `sym` | à documenter séparément |
| San Maya | `stj` | à documenter séparément |

Cette liste sert d'organisation initiale. La terminologie, les frontières linguistiques et les noms préférés devront être confirmés avec des locuteurs et spécialistes.

## Pourquoi les séparer ?

Des différences peuvent concerner :

- vocabulaire ;
- prononciation ;
- formes grammaticales ;
- orthographe ;
- expressions naturelles.

Mélanger des formes non étiquetées peut produire un dataset incohérent et un modèle qui retourne une forme d'une variété à un utilisateur d'une autre variété.

## Champs recommandés

```text
dialect_declared
dialect_validated
locality_declared
validation_status
validator_id
```

## Si le contributeur ne connaît pas le nom de sa variété

La réponse doit pouvoir rester :

```text
dialect_declared = unknown
```

Le contributeur peut indiquer la localité ou le contexte où il a appris/parlé le San.

La localité est une information utile, mais elle ne doit pas automatiquement être convertie en dialecte par le logiciel. Un validateur compétent peut ensuite confirmer ou corriger la classification.

## Règle pour le Machine Learning

Par défaut, une donnée dont la variété n'est pas suffisamment établie ne doit pas rejoindre automatiquement un dataset d'entraînement ciblant une variété précise.
