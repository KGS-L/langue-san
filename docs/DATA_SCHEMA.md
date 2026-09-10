# Schéma de données — collecteur Langue SAN

Le schéma sépare clairement le compte utilisateur, l’identité de collecte pseudonymisée, les prompts français, les contributions, les enregistrements et les validations.

## Principe : contribuer sans compte

La création d’un compte n’est pas obligatoire pour répondre au questionnaire.

Un visiteur reçoit un `contributor_profile` pseudonymisé identifié par un `public_code` et un jeton navigateur dont seul le hash est conservé en base. Ce profil peut ensuite être lié à un compte `user` si la personne souhaite retrouver ses statistiques, son historique ou poursuivre ses contributions plus tard.

Ainsi :

```text
Visiteur anonyme
      ↓
contributor_profiles
      ↓
collection_sessions / contributions / consents

Compte facultatif
      ↓
users ← contributor_profiles.user_id
```

## Entités principales

### `users`

Comptes authentifiés uniquement.

```text
id
name
email
password
role              # admin | moderator | contributor
status            # active | suspended
created_at
updated_at
```

Un contributeur peut exister sans ligne dans `users`.

### `contributor_profiles`

Identité pseudonymisée utilisée pour toute collecte.

```text
id
public_code        # identifiant stable, ex. SAN-01ABCDEF12
user_id            # nullable, compte facultatif
guest_token_hash   # nullable, jamais stocker le jeton brut
locality_id
locality_other
fluency_level
can_write_san
last_seen_at
created_at
updated_at
```

Le public ne choisit pas directement une variété technique comme Maka / Matya / Maya. La localité déclarée sert de contexte ; la variété finale est déterminée lors de la validation linguistique.

### `categories`

```text
id
name
slug
description
icon
display_order
is_active
```

### `prompts`

Un mot, concept ou une phrase française présenté au contributeur.

```text
id
code                    # identifiant métier stable, ex. SAL-W-001
category_id
french_text
context                 # nullable, uniquement pour lever une ambiguïté
type                    # word | sentence
difficulty              # 1..5
priority                # 0..100
target_contributions
is_active
created_at
updated_at
```

Aucune traduction San ne doit être stockée dans `prompts`.

### `collection_sessions`

Une session de collecte, typiquement 10 questions dans un thème.

```text
id
contributor_profile_id
category_id
status
started_at
completed_at
created_at
updated_at
```

### `session_prompts`

```text
id
collection_session_id
prompt_id
position
status                  # pending | answered | skipped
```

### `contributions`

Une réponse à un prompt.

```text
id
collection_session_id
prompt_id
contributor_profile_id
locality_id
san_text                # nullable si audio uniquement
status
submitted_at
created_at
updated_at
```

La localité est également copiée sur la contribution afin de conserver le contexte au moment précis de la collecte, même si le profil change plus tard.

### `recordings`

```text
id
contribution_id
disk
path
mime_type
duration_ms
size_bytes
quality_status
created_at
updated_at
```

Les fichiers audio bruts restent privés.

### `validations`

```text
id
contribution_id
validator_id
decision                # approve | correct | reject
san_text_corrected
variety_id
notes
created_at
updated_at
```

La réponse originale n’est pas écrasée par une correction.

### `consent_versions`

Versionne le texte et les permissions proposées au contributeur.

### `contributor_consents`

Le consentement appartient au profil de collecte et non au compte utilisateur, ce qui permet le consentement d’un participant anonyme.

```text
id
contributor_profile_id
consent_version_id
accepted_at
ip_hash
```

## Statut agrégé d’une contribution

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Seules les données `approved`, rattachées à une variété validée et à un consentement compatible avec l’entraînement, peuvent entrer dans un export ML.

## Sélection des prompts

Une session privilégie les prompts sous-représentés, évite de reproposer au même `contributor_profile` un prompt déjà traité et tient compte de la priorité métier. Le format initial visé est :

```text
7 mots / concepts
+
3 phrases
=
10 questions
```

## Export ML

Exemple de champs :

```text
id
prompt_code
variety
french
context
san
type
category
locality
validation_level
```

Aucun identifiant personnel ou jeton navigateur ne doit apparaître dans le dataset d’entraînement.

## Séparation train / validation / test

La séparation doit se faire au niveau des paires sémantiques avant de générer éventuellement les deux directions de traduction. Une même paire ne doit pas apparaître dans `train` en Français → San et dans `test` sous forme inversée San → Français.

Voir également `data/schema/contribution.example.json`.
