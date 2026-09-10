# Schéma de données — proposition initiale

Le schéma définit comment séparer les prompts français, les réponses des contributeurs, les enregistrements et les validations.

## Entités principales

### `prompts`

Un élément présenté au contributeur.

```text
id
french_text
type              # word | sentence
category
level
is_active
created_at
updated_at
```

### `contributors`

Profil pseudonymisé du contributeur.

```text
id
public_code        # ex. USR-000123
dialect_declared
locality_declared
fluency_level
consent_version
created_at
```

Éviter de rendre obligatoires des données personnelles non nécessaires.

### `contributions`

Une réponse à un prompt.

```text
id
prompt_id
contributor_id
san_text           # nullable si audio uniquement
dialect_declared
locality_declared
status
created_at
updated_at
```

### `recordings`

```text
id
contribution_id
path
mime_type
duration_ms
size_bytes
consent_to_training
consent_to_publication
quality_status
created_at
```

### `validations`

```text
id
contribution_id
validator_id
decision           # approve | correct | reject
corrected_text
dialect_validated
notes
created_at
```

## Statut agrégé d'une contribution

Valeurs initiales possibles :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Le statut `approved` doit être attribué selon une règle explicite, pas seulement parce qu'une réponse existe.

## Export ML

Le fichier exporté pour le Machine Learning doit contenir uniquement les champs nécessaires, par exemple :

```text
id
dialect
french
san
type
category
source
validation_level
```

Le dataset d'entraînement ne doit pas contenir directement les identifiants personnels des contributeurs.

## Séparation train / validation / test

La séparation doit se faire au niveau des paires sémantiques avant de générer éventuellement les deux directions de traduction. Une même paire ne doit pas apparaître dans `train` en Français → San et dans `test` sous forme inversée San → Français.

Voir également `data/schema/contribution.example.json`.
