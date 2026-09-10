# Cahier des charges — Application de collecte Langue SAN

**Projet :** Langue SAN  
**Composant :** `apps/collector`  
**Type :** application web de collecte, transcription et validation  
**Stack principale :** Laravel + NiceAdmin pour le dashboard administrateur  
**Statut :** spécification MVP — v0.1  
**Date :** septembre 2026

---

## 1. Contexte

Langue SAN est une initiative open source visant à documenter et numériser progressivement les langues San du Burkina Faso afin de constituer des ressources linguistiques fiables pouvant servir à des outils numériques : dictionnaire, corpus texte/audio, traduction Français ↔ San, apprentissage et, à plus long terme, traitement automatique du langage.

La première contrainte du projet est l'absence d'un corpus numérique suffisamment grand, propre et validé. Avant de travailler sérieusement sur un modèle de traduction dans Google Colab, le projet doit donc disposer d'un outil simple permettant à des locuteurs de contribuer des traductions écrites ou audio.

L'application décrite dans ce document constitue cette première infrastructure.

---

## 2. Vision de l'application

Créer une application web mobile-first dans laquelle une personne parlant San peut contribuer en quelques minutes, même si elle ne sait pas écrire la langue.

L'expérience publique doit être extrêmement simple :

```text
Landing page
    ↓
Commencer
    ↓
Consentement
    ↓
Profil linguistique / localité
    ↓
Choix du thème
    ↓
Session de 10 mots / phrases
    ↓
Réponse écrite et/ou audio
    ↓
Merci
    ↓
Contribuer encore
```

Le travail complexe doit être déplacé côté administration : transcription des audios, identification/confirmation de la variété linguistique, comparaison des réponses, validation et préparation des exports de données.

---

## 3. Principe linguistique essentiel

### 3.1 Ne pas demander « Maka / Matya / Maya » au contributeur

Le public ne doit pas être obligé de connaître les noms techniques des variétés linguistiques.

L'interface publique ne doit donc pas afficher une question du type :

> Quel dialecte parlez-vous ? Maka / Matya / Maya ?

À la place, elle doit demander des informations concrètes que le locuteur connaît réellement :

> **Dans quelle localité ou zone avez-vous principalement appris ou parlé le San ?**

Exemples de choix initiaux :

- Toma ;
- Tougan ;
- autre localité ;
- je ne sais pas / plusieurs localités.

Si « autre localité » est sélectionné, un champ texte permet de préciser la ville ou le village.

### 3.2 La variété linguistique est une donnée de validation interne

Le système doit distinguer :

```text
localité déclarée par l'utilisateur
            ≠
variété linguistique validée
```

La base doit donc conserver au minimum :

- la localité déclarée ;
- une éventuelle variété suggérée par une table de correspondance interne ;
- la variété confirmée par un validateur compétent.

Une correspondance comme « Toma → Maka » ou « Tougan → Matya » ne doit pas être codée en dur dans l'interface publique. Les correspondances entre zones et variétés doivent être administrables et validées linguistiquement avant d'être considérées comme définitives.

Cela permet de corriger le projet sans perdre les déclarations originales des contributeurs.

---

## 4. Objectifs du MVP

Le MVP doit permettre de :

1. présenter clairement le projet et convaincre un locuteur de contribuer ;
2. recueillir un consentement explicite avant la collecte ;
3. recueillir un profil linguistique minimal sans demander de données personnelles inutiles ;
4. laisser le contributeur choisir le thème auquel il souhaite répondre ;
5. proposer une session courte d'environ 10 éléments ;
6. accepter une réponse écrite, une réponse audio ou les deux ;
7. permettre de passer une question ;
8. stocker les audios de façon privée ;
9. éviter que certains prompts reçoivent énormément de réponses tandis que d'autres restent sans contribution ;
10. fournir un dashboard administrateur basé sur NiceAdmin ;
11. écouter et transcrire les audios ;
12. valider ou rejeter les contributions ;
13. associer une contribution à une variété linguistique après vérification ;
14. distinguer clairement les données brutes des données approuvées ;
15. exporter uniquement des données explicitement éligibles à l'usage dataset/ML.

---

## 5. Objectifs de collecte initiaux

Le premier objectif du projet n'est pas encore de produire un traducteur généraliste.

### Milestone A — pilote

- 100 concepts/prompts français ;
- premières contributions texte/audio ;
- validation du parcours UX ;
- validation du workflow administrateur.

### Milestone B — lexique v0.1

- 500 concepts/mots courants ;
- plusieurs contributions indépendantes par concept lorsque possible ;
- audio associé à une partie significative des contributions ;
- localité connue ou documentée ;
- données validées.

### Milestone C — corpus de phrases v0.1

- 500 concepts/mots ;
- au moins 200 phrases simples ;
- plusieurs validations ;
- premiers exports destinés aux expérimentations NLP.

---

## 6. Publics cibles

### 6.1 Contributeur

Personne parlant San, sachant ou non l'écrire.

Aucun compte ne doit être obligatoire pour le MVP afin de réduire au maximum la friction.

### 6.2 Transcripteur / validateur

Personne autorisée à :

- écouter un audio ;
- saisir sa transcription ;
- corriger une proposition écrite ;
- confirmer une localité ;
- sélectionner une variété linguistique interne ;
- approuver ou rejeter une contribution.

### 6.3 Administrateur

Gère :

- catégories ;
- prompts ;
- localités ;
- correspondances localité/variété ;
- comptes du dashboard ;
- consentements ;
- contributions ;
- validation ;
- exports ;
- statistiques ;
- paramètres de collecte.

### 6.4 Super administrateur

Dispose de tous les droits et peut gérer les rôles des autres membres du projet.

---

# 7. Parcours public détaillé

## 7.1 Landing page

La landing page est une partie critique du produit. Elle doit répondre en quelques secondes à quatre questions :

1. Pourquoi ce projet existe-t-il ?
2. Pourquoi ma contribution est-elle utile ?
3. Combien de temps cela va-t-il me prendre ?
4. Est-ce que je peux participer même si je ne sais pas écrire le San ?

### Hero recommandé

**Titre proposé :**

> Aidez-nous à donner une place au San dans le numérique.

**Sous-titre proposé :**

> Vous parlez San ? En quelques minutes, aidez-nous à construire une ressource numérique de mots, phrases et prononciations. Vous pouvez écrire vos réponses ou simplement les enregistrer avec votre voix.

**CTA principal :**

> **Commencer**

Le bouton `Commencer` doit être visible immédiatement sur mobile et desktop.

### Sections de la landing page

#### A. Pourquoi participer ?

Expliquer de façon simple que les données contribueront à :

- documenter numériquement la langue ;
- construire un dictionnaire ;
- conserver des prononciations ;
- préparer de futurs outils de traduction ;
- préparer de futurs outils d'apprentissage.

Ne pas promettre qu'un modèle de traduction existe déjà.

#### B. Comment ça marche ?

Présenter 3 étapes :

```text
1. Choisissez un thème
2. Répondez à 10 mots ou phrases
3. Écrivez, parlez, ou faites les deux
```

#### C. Pas besoin de savoir écrire

Message à mettre clairement en avant :

> **Vous parlez San mais vous ne savez pas l'écrire ? Votre voix suffit.**

#### D. Progression du projet

Lorsque les premières données existent, afficher des statistiques compréhensibles :

```text
Prompts disponibles       120 / 500
Contributions reçues      842
Contributions validées    316
Audios enregistrés        529
```

Ne pas afficher de statistiques fictives en production.

#### E. Transparence

Une petite section doit expliquer que :

- les contributions sont relues avant intégration au corpus ;
- l'audio n'est pas automatiquement publié ;
- le projet est open source ;
- les règles de consentement et de données sont accessibles.

#### F. CTA final

Répéter le bouton :

> **Commencer**

---

## 7.2 Consentement

Avant tout enregistrement ou envoi de contribution, afficher le consentement actif.

Le contributeur doit comprendre au minimum :

- quelles données sont collectées ;
- pourquoi elles sont collectées ;
- que son audio peut être écouté et transcrit par des validateurs autorisés ;
- si les données peuvent servir à entraîner/évaluer des modèles ;
- que la publication éventuelle des audios doit être traitée séparément selon la politique définie ;
- que la politique peut être consultée avant acceptation.

Chaque acceptation doit être liée à une **version de consentement**.

Exemple :

```text
consent_version = v1.0
accepted_at = 2026-09-xx
```

Le bouton de poursuite reste désactivé tant que le consentement requis n'est pas accepté.

---

## 7.3 Profil linguistique

Le profil doit rester court.

Questions MVP recommandées :

### Q1 — Niveau de pratique

> Parlez-vous San ?

- Oui, couramment
- Oui, assez bien
- Un peu

### Q2 — Localité principale

> Dans quelle localité ou zone avez-vous principalement appris ou parlé le San ?

- Toma
- Tougan
- Autre
- Plusieurs localités / je ne sais pas

Si `Autre` :

```text
Précisez la ville ou le village : [________________]
```

### Q3 — Facultatif

> Y a-t-il une autre localité où vous utilisez souvent le San ?

Ce champ peut être reporté après le MVP si l'on souhaite un formulaire encore plus court.

### Données à ne pas rendre obligatoires dans le MVP

- nom complet ;
- numéro de téléphone ;
- adresse email ;
- adresse précise ;
- date de naissance.

Le but est la collecte linguistique, pas la constitution d'un fichier d'identité.

---

## 7.4 Choix du thème

Après le profil, demander :

> **Par quoi voulez-vous commencer ?**

Afficher les catégories sous forme de cartes facilement cliquables.

Ordre/catégories initiales :

1. Salutations
2. Présentation / identité
3. Famille
4. Nombres
5. Temps / jours
6. Nourriture
7. Maison
8. Marché / commerce
9. Déplacements
10. École
11. Travail
12. Autres thèmes activés plus tard

Ajouter une option :

> **Mélanger les thèmes**

Chaque carte peut afficher :

- nom ;
- petite description ;
- nombre approximatif de questions disponibles ;
- progression de collecte facultative.

Le contributeur choisit donc **ce qu'il souhaite traduire**, plutôt que de subir un ordre imposé.

---

# 8. Session de contribution

## 8.1 Taille de session

Par défaut : **10 prompts**.

Objectif recommandé lorsque les données le permettent :

```text
7 mots / concepts
+
3 phrases simples
```

Cette répartition doit être configurable depuis l'administration.

## 8.2 Écran d'une question

Exemple :

```text
3 / 10

Comment dites-vous :

        « MÈRE »

en San ?

Traduction écrite (facultatif)
[________________________________]

Vous préférez répondre avec votre voix ?

[ 🎤 Enregistrer ]

[ Passer ]                     [ Suivant ]
```

### Règles UX

- progression toujours visible (`3 / 10`) ;
- une seule question principale à l'écran ;
- taille de texte importante ;
- boutons utilisables au pouce sur smartphone ;
- aucune obligation d'écriture si un audio est fourni ;
- aucune obligation d'audio si un texte est fourni ;
- possibilité de fournir les deux ;
- possibilité de passer ;
- confirmation avant de quitter une session ayant des réponses non envoyées si nécessaire.

## 8.3 Réponse écrite

Le champ doit :

- accepter Unicode ;
- conserver les caractères diacritiques exactement comme saisis ;
- ne pas transformer automatiquement la casse ;
- ne pas « corriger » automatiquement l'orthographe San ;
- conserver la réponse brute originale même si elle est corrigée plus tard par un validateur.

## 8.4 Réponse audio

Le navigateur doit permettre :

```text
Enregistrer
    ↓
Arrêter
    ↓
Écouter
    ↓
Recommencer OU conserver
```

Le contributeur doit pouvoir écouter son propre enregistrement avant de continuer.

Durées recommandées pour le MVP :

- mot : maximum configurable, par défaut ~15 secondes ;
- phrase : maximum configurable, par défaut ~30 secondes.

Les valeurs exactes doivent rester configurables.

## 8.5 Fin de session

Afficher :

> **Merci pour votre contribution !**

Puis expliquer brièvement l'impact :

> Vos réponses seront vérifiées avant d'être intégrées aux ressources Langue SAN.

Afficher :

```text
Réponses envoyées : 8
Questions passées : 2
Audios : 6
```

CTA :

- `Contribuer avec 10 nouvelles questions`
- `Retour à l'accueil`

Pour une nouvelle session, l'utilisateur peut choisir un autre thème ou continuer le même.

---

# 9. Algorithme de sélection des prompts

Le système ne doit PAS utiliser uniquement :

```php
inRandomOrder()->limit(10)
```

L'objectif est d'obtenir une couverture équilibrée.

## 9.1 Principes

Pour la catégorie choisie :

1. sélectionner uniquement les prompts actifs ;
2. exclure les prompts déjà présents dans la session ;
3. si un identifiant anonyme de contributeur est disponible, éviter de reproposer immédiatement un prompt auquel cette personne a déjà répondu ;
4. privilégier les prompts ayant le moins de contributions indépendantes ;
5. privilégier ceux n'ayant pas encore atteint le nombre cible ;
6. ajouter de l'aléatoire à priorité équivalente ;
7. respecter si possible le ratio mots/phrases configuré.

## 9.2 Nombre cible

Chaque prompt possède :

```text
target_contributions
```

Valeur initiale recommandée : 3.

Exemple :

```text
Bonjour       12 contributions → faible priorité
Mère           3 contributions → objectif atteint
Boire          1 contribution  → haute priorité
Demain         0 contribution  → priorité maximale
```

## 9.3 Important

Le nombre de contributions n'est pas équivalent au nombre de validations.

Le dashboard doit afficher séparément :

```text
raw_contributions_count
validated_contributions_count
approved_contributions_count
```

---

# 10. Gestion de l'audio

## 10.1 Captation

Utiliser l'API navigateur `MediaRecorder` lorsque disponible.

Le frontend doit gérer les formats supportés selon navigateur plutôt que supposer un format unique.

Exemples possibles :

- WebM/Opus ;
- OGG/Opus ;
- formats compatibles Safari selon plateforme.

## 10.2 Stockage

L'audio brut doit être conservé dans un stockage **privé**.

Interdit :

```text
/public/audios
GitHub
repository public
URL permanente devinable
```

Prévoir :

```text
storage/app/private/audio/...
```

pour le développement/MVP, avec possibilité de passer plus tard à un stockage S3-compatible privé.

## 10.3 Accès dashboard

Un audio est servi uniquement via une route autorisée ou une URL temporaire/signée.

## 10.4 Métadonnées

Conserver lorsque disponible :

- MIME type ;
- extension ;
- taille ;
- durée ;
- checksum ;
- codec / sample rate / channels si analysés ;
- date de création.

## 10.5 Traitement ultérieur

Ne pas écraser l'audio original.

À terme, une version dérivée normalisée pourra être produite pour le ML, par exemple :

```text
original.*
    ↓
normalisation
    ↓
WAV mono / sample rate standardisé
```

Le format exact destiné au ML sera défini dans la partie `ml/` du projet.

Le MVP peut se contenter de conserver le fichier original tant que le workflow d'écoute/transcription fonctionne.

---

# 11. Identité anonyme du contributeur

Afin de proposer de nouveaux prompts sans imposer de compte, générer un UUID anonyme lors de la première contribution.

Exemple :

```text
contributor_uuid = 4b3e...
```

Il peut être conservé dans un cookie/local storage et dans la base.

Il ne doit pas être présenté comme une preuve forte d'identité : une personne changeant de navigateur peut recevoir un autre UUID.

Ce mécanisme sert uniquement à :

- regrouper les sessions d'un même navigateur ;
- réduire les répétitions ;
- mesurer approximativement les contributeurs distincts.

---

# 12. Dashboard administrateur — NiceAdmin

Le dashboard sera construit avec le template **NiceAdmin**.

Avant redistribution du code final, vérifier et respecter les conditions de licence applicables au template et aux assets utilisés.

## 12.1 Navigation recommandée

```text
Dashboard

Collecte
├── Catégories
├── Prompts
├── Sessions
├── Contributions
└── Audios

Validation
├── À transcrire
├── À valider
├── Divergences
└── Approuvées

Linguistique
├── Localités
├── Variétés
└── Correspondances localité / variété

Données
├── Dataset approuvé
├── Exports
└── Historique des exports

Administration
├── Utilisateurs
├── Rôles
├── Consentements
├── Paramètres
└── Journal d'activité
```

---

## 12.2 Dashboard principal

Cartes KPI :

- nombre de prompts actifs ;
- nombre de contributeurs anonymes ;
- sessions démarrées ;
- sessions terminées ;
- contributions reçues ;
- contributions avec audio ;
- contributions avec texte ;
- en attente de transcription ;
- en attente de validation ;
- approuvées ;
- rejetées.

Graphiques utiles :

- contributions par jour/semaine ;
- contributions par catégorie ;
- contributions par localité ;
- couverture des 500 concepts ;
- taux de validation ;
- répartition texte/audio/texte+audio.

Tables d'alerte :

- prompts avec 0 contribution ;
- prompts sous l'objectif cible ;
- audios en attente depuis longtemps ;
- désaccords de validation.

---

# 13. Gestion des catégories

CRUD administrateur.

Champs recommandés :

```text
id
name
slug
description
icon
sort_order
is_active
created_at
updated_at
```

L'ordre choisi dans `sort_order` contrôle l'affichage public mais l'utilisateur reste libre de sélectionner son thème.

---

# 14. Gestion des prompts

Un prompt est le texte français proposé au contributeur.

Champs :

```text
id
category_id
french_text
type              // word | sentence
difficulty        // optionnel : beginner, intermediate...
target_contributions
is_active
notes_internal
created_at
updated_at
```

### Règles

- aucun texte San inventé dans les seeds ;
- chaque prompt appartient à une catégorie ;
- un prompt peut être désactivé sans supprimer son historique ;
- éviter les doublons de sens inutiles ;
- les phrases doivent être courtes et compréhensibles sans contexte ambigu lorsque possible.

---

# 15. Workflow de contribution et validation

## 15.1 États recommandés

```text
pending
    ↓
needs_transcription     // si audio sans texte exploitable
    ↓
under_review
    ↓
validated_once
    ↓
validated_twice
    ↓
approved
```

État alternatif :

```text
rejected
```

Une contribution peut également nécessiter une adjudication en cas de divergence.

## 15.2 Original immuable

Toujours conserver :

```text
san_text_raw
```

La validation ne doit jamais effacer ce que le contributeur avait fourni.

Une correction devient par exemple :

```text
san_text_validated
```

## 15.3 Transcription

Si une personne fournit uniquement un audio :

```text
FR : Bonjour
Audio : recording_123
San brut : null
```

Un transcripteur écoute puis ajoute :

```text
transcription_text
transcribed_by
transcribed_at
```

## 15.4 Double validation

Lorsque possible, deux validateurs distincts doivent confirmer les données destinées au corpus.

Un même validateur ne doit pas compter deux fois pour le même niveau de validation.

Si les validations divergent :

```text
reviewer A → forme X
reviewer B → forme Y
```

la donnée passe dans `Divergences` et ne devient pas automatiquement `approved` par vote majoritaire.

Un validateur senior ou une discussion linguistique tranche et documente la décision.

---

# 16. Écran admin de validation

Un validateur doit pouvoir travailler sans naviguer entre plusieurs pages.

Maquette fonctionnelle :

```text
Contribution #182                         [pending]
──────────────────────────────────────────────────
Français
Je vais au marché.

Catégorie : Marché / commerce
Type : phrase
Localité déclarée : Toma
Niveau déclaré : courant

Réponse utilisateur
Texte : ____________________

Audio
[ ▶ écouter ]  00:04  [ vitesse 0.75x / 1x ]

Transcription
[________________________________________]

Texte validé
[________________________________________]

Variété interne
[ Sélectionner ▼ ]

Décision
○ approuver
○ corriger puis approuver
○ rejeter
○ demander arbitrage

Notes internes
[________________________________________]

[ Enregistrer et passer à la suivante ]
```

Filtres :

- catégorie ;
- localité ;
- variété validée ;
- statut ;
- avec audio ;
- sans audio ;
- avec texte ;
- type mot/phrase ;
- date ;
- validateur.

---

# 17. Gestion des localités et variétés

## 17.1 `localities`

Exemples initiaux :

```text
Toma
Tougan
Autre / à préciser
```

Les admins doivent pouvoir ajouter des villes/villages sans déployer du code.

## 17.2 `language_varieties`

Cette table utilise les noms linguistiques internes validés par le projet.

Exemple de structure :

```text
id
name
iso_code nullable
notes
is_active
```

## 17.3 `locality_variety_mappings`

Permet de documenter une relation sans la coder en dur :

```text
locality_id
variety_id
confidence
source
validated_by
validated_at
notes
```

La valeur peut être indicative, pas nécessairement définitive.

---

# 18. Modèle de données recommandé

Tables MVP :

```text
users
roles / permissions

categories
prompts

contributors
localities
language_varieties
locality_variety_mappings

contribution_sessions
session_prompts
contributions
recordings
transcriptions
validations

consent_versions
consent_acceptances

settings
activity_logs
```

## 18.1 `contributors`

```text
id
uuid
proficiency_level
primary_locality_id nullable
locality_other_text nullable
created_at
updated_at
```

## 18.2 `contribution_sessions`

```text
id
uuid
contributor_id
category_id nullable       // null = mixte
started_at
completed_at nullable
status                     // started | completed | abandoned
```

## 18.3 `session_prompts`

```text
id
session_id
prompt_id
position
shown_at nullable
answered_at nullable
skipped_at nullable
```

## 18.4 `contributions`

```text
id
uuid
session_id
prompt_id
contributor_id
san_text_raw nullable
status
submitted_at
created_at
updated_at
```

Contrainte fonctionnelle : au moins `san_text_raw` ou un `recording` doit être présent pour considérer la question comme répondue.

## 18.5 `recordings`

```text
id
contribution_id
storage_disk
storage_path
original_mime
extension nullable
size_bytes
duration_ms nullable
checksum nullable
processing_status
created_at
```

## 18.6 `transcriptions`

```text
id
contribution_id
text
transcribed_by
created_at
updated_at
```

Ne pas supprimer les anciennes versions si le projet met en place un historique ; utiliser une table/version ou journal d'activité.

## 18.7 `validations`

```text
id
contribution_id
validator_id
decision
validated_text nullable
language_variety_id nullable
confidence nullable
notes nullable
created_at
```

## 18.8 `consent_versions`

```text
id
version
content
is_active
published_at
created_at
```

## 18.9 `consent_acceptances`

```text
id
contributor_id
consent_version_id
accepted_at
```

---

# 19. Dataset approuvé

Ne jamais considérer la table `contributions` brute comme le dataset final.

Le pipeline logique est :

```text
contributions brutes
       ↓
transcriptions
       ↓
validations
       ↓
curation
       ↓
dataset approuvé
       ↓
export CSV / JSONL
       ↓
ml/
```

Une future table/vue `dataset_entries` pourra contenir les entrées curées :

```text
id
prompt_id
french_text
san_text
language_variety_id
locality_context
source_contribution_ids
validation_level
has_audio
license_status
created_at
```

Seules les données répondant aux critères de consentement et de validation doivent être exportables.

---

# 20. Exports

Formats souhaités à terme :

### CSV

```csv
id,french,san,type,category,variety,locality,audio,status
```

### JSONL

```json
{"id":"SAN-000001","french":"Bonjour","san":"...","type":"word","category":"salutations","variety":"..."}
```

Le dashboard doit permettre de filtrer avant export :

- variété ;
- catégorie ;
- mots/phrases ;
- niveau de validation ;
- avec/sans audio ;
- consentement compatible avec l'usage visé.

Chaque export important devrait être traçable afin de savoir quelles données ont servi à une expérimentation ML.

---

# 21. Authentification et autorisations admin

Les contributeurs publics n'ont pas besoin de compte.

Le dashboard est privé.

Rôles suggérés :

```text
super_admin
admin
validator
transcriber
viewer / researcher (plus tard)
```

Permissions séparées :

- gérer prompts ;
- écouter audio ;
- transcrire ;
- valider ;
- gérer localités/variétés ;
- exporter ;
- gérer utilisateurs ;
- gérer consentements ;
- voir statistiques.

---

# 22. Stack technique du MVP

## Backend

- Laravel ;
- PHP version compatible avec la version Laravel retenue ;
- Eloquent ORM ;
- validation Laravel ;
- jobs/queues Laravel si nécessaire.

## Base de données

Pour le MVP :

- MySQL convient parfaitement ;
- conserver un schéma portable autant que possible.

## Front public

- Blade ;
- Bootstrap compatible avec l'écosystème NiceAdmin, ou CSS public dédié ;
- JavaScript léger / Alpine.js ou Vanilla JS pour l'enregistreur audio.

Le public doit rester beaucoup plus simple visuellement que le dashboard.

## Dashboard

- NiceAdmin ;
- intégration dans les layouts Blade ;
- composants réutilisables pour tables, filtres, cards et formulaires.

## Stockage

MVP : Laravel private storage.

Évolution : S3-compatible privé.

## Tests

- tests feature Laravel ;
- tests unitaires sur la sélection des prompts ;
- tests d'autorisation dashboard ;
- tests d'upload audio ;
- tests du workflow de validation.

---

# 23. Pages et routes indicatives

Les noms sont indicatifs et pourront évoluer.

## Public

```text
GET  /
GET  /contribute/consent
POST /contribute/consent
GET  /contribute/profile
POST /contribute/profile
GET  /contribute/categories
POST /contribute/sessions
GET  /contribute/sessions/{uuid}
POST /contribute/sessions/{uuid}/answers
POST /contribute/sessions/{uuid}/skip
GET  /contribute/sessions/{uuid}/complete
```

Pour l'audio, un endpoint séparé peut être utilisé :

```text
POST /contribute/sessions/{uuid}/recordings
```

## Administration

```text
/admin
/admin/categories
/admin/prompts
/admin/sessions
/admin/contributions
/admin/transcriptions
/admin/validations
/admin/localities
/admin/language-varieties
/admin/datasets
/admin/exports
/admin/consents
/admin/users
/admin/settings
```

Utiliser UUID/signatures côté public plutôt que d'exposer des IDs incrémentaux lorsque pertinent.

---

# 24. Sécurité

Le MVP doit au minimum prévoir :

- CSRF ;
- validation stricte des uploads ;
- limitation de taille audio ;
- vérification MIME côté serveur ;
- noms de fichiers non contrôlés par l'utilisateur ;
- stockage privé ;
- rate limiting sur endpoints publics sensibles ;
- échappement des contenus affichés ;
- authentification admin ;
- autorisations par rôle ;
- journalisation des actions critiques ;
- protection des exports ;
- `.env` jamais versionné ;
- audios et datasets privés jamais commités dans GitHub.

Ne pas utiliser uniquement l'extension du fichier pour considérer un audio comme valide.

---

# 25. Vie privée et minimisation des données

Principe : collecter seulement ce qui est utile au projet linguistique.

Le MVP ne nécessite pas l'identité civile du locuteur.

Données minimales :

```text
UUID anonyme
niveau de pratique
localité linguistique
consentement
contributions
éventuels audios
```

La politique complète du projet reste documentée dans `docs/DATA_GOVERNANCE.md`.

Si le projet souhaite plus tard organiser spécifiquement une collecte auprès de mineurs, un flux de consentement approprié devra être défini avant cette campagne ; ce point n'est pas couvert par le MVP actuel.

---

# 26. Accessibilité et contraintes terrain

Le projet vise un usage réel au Burkina Faso et doit tenir compte des téléphones et connexions modestes.

Priorités :

- mobile-first ;
- pages légères ;
- peu de JavaScript inutile ;
- compression/contrôle de taille des audios ;
- feedback clair pendant l'upload ;
- gestion d'une connexion lente ;
- bouton de réessai si upload échoue ;
- ne pas perdre toute une session si une seule réponse échoue ;
- contraste lisible ;
- gros boutons ;
- interface compréhensible sans vocabulaire technique.

Évolution possible : mode PWA/offline partiel, non obligatoire pour le MVP.

---

# 27. Gestion des erreurs UX

Exemples :

### Microphone refusé

> L'accès au microphone n'a pas été autorisé. Vous pouvez modifier l'autorisation de votre navigateur ou répondre par écrit.

### Upload interrompu

> L'enregistrement n'a pas pu être envoyé. Votre réponse n'est pas perdue. Réessayez.

### Aucun prompt disponible dans le thème

> Toutes les questions disponibles dans ce thème ont déjà été suffisamment couvertes pour le moment. Choisissez un autre thème.

### Session expirée

Le système doit tenter de restaurer l'état lorsque cela est raisonnablement possible.

---

# 28. Paramètres administrables

Éviter de coder en dur les règles métier susceptibles d'évoluer.

Paramètres :

```text
session_size = 10
words_per_session = 7
sentences_per_session = 3
default_target_contributions = 3
max_word_audio_seconds = 15
max_sentence_audio_seconds = 30
collection_enabled = true
```

Également administrables :

- catégories actives ;
- localités proposées ;
- textes landing principaux à terme ;
- consentement actif ;
- messages de fin de session.

---

# 29. Indicateurs produit

Pour savoir si la collecte fonctionne :

```text
landing → clic Commencer
Commencer → consentement accepté
consentement → profil terminé
profil → session démarrée
session démarrée → session terminée
```

Mesures utiles :

- taux de conversion landing → contribution ;
- taux de complétion des sessions ;
- nombre moyen de réponses par session ;
- taux d'utilisation de l'audio ;
- taux de réponses passées ;
- contributions par catégorie ;
- prompts couverts / prompts totaux ;
- délai moyen avant transcription ;
- délai moyen avant validation ;
- taux de rejet ;
- taux de divergence entre validateurs.

Ne pas introduire de tracking publicitaire dans le MVP.

---

# 30. Back-office : filtres et actions en masse

Pour éviter un dashboard inutilisable après quelques milliers de contributions :

- pagination serveur ;
- recherche ;
- filtres combinables ;
- tri ;
- actions en masse limitées et sécurisées ;
- export filtré ;
- affichage des compteurs par statut.

Une action de validation linguistique ne doit pas être massivement appliquée sans contrôle si elle peut écraser ou standardiser des données différentes.

---

# 31. Seed initial

Préparer les catégories dès l'installation.

Le seed de développement peut contenir des prompts français comme :

```text
Bonjour
Merci
Père
Mère
Enfant
Un
Deux
Trois
Comment vas-tu ?
Je m'appelle ...
```

Ne jamais inventer de traductions San pour remplir le seed.

Les données San de test technique doivent être explicitement marquées comme fictives et ne jamais être exportées comme dataset réel.

---

# 32. Organisation du code dans `apps/collector`

Une fois Laravel initialisé, ce dossier deviendra l'application elle-même.

Structure logique indicative :

```text
apps/collector/
├── app/
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   │   ├── PromptSelectionService.php
│   │   ├── ContributionService.php
│   │   └── DatasetExportService.php
│   └── Policies/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── resources/
│   ├── views/
│   │   ├── public/
│   │   └── admin/
│   ├── css/
│   └── js/
│       └── audio-recorder.js
│
├── routes/
│   ├── web.php
│   └── admin.php
│
├── tests/
├── README.md
└── CAHIER_DES_CHARGES.md
```

Ne pas sur-architecturer avant d'avoir testé le pilote.

---

# 33. Roadmap de développement de l'application

## Sprint 0 — Initialisation

- [ ] installer Laravel dans `apps/collector` ;
- [ ] configurer `.env.example` ;
- [ ] connecter MySQL ;
- [ ] intégrer NiceAdmin ;
- [ ] mettre en place authentification admin ;
- [ ] préparer layout public + layout admin.

## Sprint 1 — Référentiels

- [ ] migrations catégories ;
- [ ] migrations prompts ;
- [ ] migrations localités ;
- [ ] migrations variétés ;
- [ ] CRUD NiceAdmin catégories ;
- [ ] CRUD prompts ;
- [ ] CRUD localités ;
- [ ] CRUD variétés ;
- [ ] seeds des catégories.

## Sprint 2 — Landing et démarrage de contribution

- [ ] landing convaincante ;
- [ ] CTA `Commencer` ;
- [ ] consentement versionné ;
- [ ] profil linguistique ;
- [ ] choix du thème ;
- [ ] création de session anonyme.

## Sprint 3 — Session de 10 prompts

- [ ] algorithme de priorité ;
- [ ] ratio mots/phrases ;
- [ ] écran question ;
- [ ] réponse texte ;
- [ ] skip ;
- [ ] progression ;
- [ ] reprise de session basique ;
- [ ] page de remerciement.

## Sprint 4 — Audio

- [ ] MediaRecorder ;
- [ ] démarrer/arrêter ;
- [ ] préécoute ;
- [ ] réenregistrement ;
- [ ] upload ;
- [ ] stockage privé ;
- [ ] contrôles MIME/taille ;
- [ ] lecteur sécurisé côté admin.

## Sprint 5 — Validation

- [ ] file « à transcrire » ;
- [ ] transcription ;
- [ ] file « à valider » ;
- [ ] correction ;
- [ ] sélection variété ;
- [ ] double validation ;
- [ ] divergences ;
- [ ] rejet ;
- [ ] historique.

## Sprint 6 — Dashboard et exports

- [ ] KPIs ;
- [ ] graphiques ;
- [ ] couverture des prompts ;
- [ ] filtres ;
- [ ] dataset approuvé ;
- [ ] export CSV ;
- [ ] export JSONL ;
- [ ] historique d'export.

## Sprint 7 — Pilote terrain

- [ ] charger ~100 prompts ;
- [ ] faire tester par un petit groupe ;
- [ ] mesurer les abandons ;
- [ ] vérifier la qualité audio ;
- [ ] vérifier la compréhension des questions ;
- [ ] recueillir les retours ;
- [ ] corriger avant passage aux 500 concepts.

---

# 34. Critères d'acceptation du MVP

Le MVP est considéré opérationnel lorsque :

- [ ] un visiteur comprend le projet depuis la landing page ;
- [ ] il peut cliquer sur `Commencer` ;
- [ ] il accepte un consentement versionné ;
- [ ] il indique sa localité sans devoir connaître « Maka / Matya / Maya » ;
- [ ] il choisit un thème ;
- [ ] il reçoit jusqu'à 10 prompts pertinents ;
- [ ] il peut répondre par texte ;
- [ ] il peut répondre par audio ;
- [ ] il peut écouter et refaire son audio ;
- [ ] il peut passer une question ;
- [ ] sa progression est visible ;
- [ ] les audios sont privés ;
- [ ] un admin peut créer/modifier les prompts ;
- [ ] un admin peut écouter une contribution ;
- [ ] un transcripteur peut transcrire un audio ;
- [ ] un validateur peut corriger et valider ;
- [ ] la variété interne peut être attribuée après validation ;
- [ ] deux validations distinctes sont traçables ;
- [ ] une divergence ne devient pas automatiquement une donnée approuvée ;
- [ ] seules les données approuvées et compatibles avec le consentement peuvent être exportées ;
- [ ] les données privées ne sont jamais versionnées dans GitHub.

---

# 35. Hors périmètre du MVP

Ne pas développer maintenant :

- modèle de traduction en production ;
- speech-to-text automatique San ;
- text-to-speech San ;
- application mobile native ;
- gamification complexe ;
- classement public des contributeurs ;
- paiement ;
- réseau social ;
- chat ;
- entraînement GPU depuis le site ;
- publication automatique des datasets ;
- publication automatique des audios ;
- PWA offline complète.

Ces fonctionnalités pourront être étudiées lorsque la collecte et la validation fonctionnent réellement.

---

# 36. Évolution après le MVP

Lorsque la base sera suffisamment propre :

```text
Collector Laravel
      ↓
Dataset approuvé
      ↓
Export versionné
      ↓
Google Colab / Python
      ↓
Baseline de traduction
      ↓
Évaluation humaine
      ↓
Corrections
      ↓
Nouveau dataset
```

Le collecteur devient donc une infrastructure permanente du projet, même si le modèle ML change plus tard.

Évolutions possibles :

- campagnes de collecte par thème ;
- PWA / offline ;
- import de listes de prompts ;
- validation communautaire contrôlée ;
- API dataset interne ;
- statistiques linguistiques ;
- gestion de plusieurs variétés ;
- annotation grammaticale ;
- alignement audio/texte ;
- publication de versions de datasets autorisées ;
- intégration future avec Hugging Face.

---

# 37. Principe directeur

Le succès de cette application ne se mesure pas au nombre d'écrans ni à la sophistication technique.

Il se mesure à sa capacité à produire :

> **des données San authentiques, traçables, consenties, bien contextualisées et validées.**

La priorité doit donc rester :

```text
simplicité pour le contributeur
+
rigueur dans le back-office
+
qualité du dataset
```

avant toute sophistication liée à l'intelligence artificielle.
