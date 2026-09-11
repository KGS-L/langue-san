# Langue SAN

> Initiative open source pour documenter, numériser et développer des ressources Français ↔ San au Burkina Faso.

**Langue SAN** est un projet communautaire et open source dont l'objectif est de constituer progressivement des ressources linguistiques fiables pour les variétés du San, puis de les utiliser pour créer des outils numériques utiles : corpus bilingues, audio, traduction, apprentissage et, à terme, modèles de traitement automatique du langage.

Le projet ne cherche pas à inventer une nouvelle langue ni à normaliser arbitrairement les usages. Il vise d'abord à **collecter, documenter, transcrire, structurer, valider et préserver** les formes réellement utilisées par les locuteurs.

## État actuel

Le premier collecteur web est maintenant opérationnel dans `apps/collector/`.

Il couvre notamment :

- contribution avec ou sans compte ;
- connexion contributeur par Google ou code OTP reçu par email ;
- profil linguistique et localité déclarée ;
- collecte Français → San par mots et phrases ;
- collecte de parole naturelle en San ;
- enregistrement audio privé ;
- rattachement des contributions anonymes au compte après connexion ;
- espace contributeur et historique ;
- communauté publique sur consentement explicite ;
- candidature pour rejoindre le projet ;
- back-office administrateur / modérateur / transcripteur / validateur ;
- transcription ;
- segmentation des récits naturels ;
- double validation linguistique ;
- exports dataset versionnés ;
- séparation déterministe train / validation / test ;
- tests automatisés et CI GitHub Actions.

Le prochain objectif n'est plus seulement de coder : il est de faire fonctionner le système avec un **petit pilote réel**, des locuteurs et plusieurs validateurs compétents.

## Deux formes complémentaires de collecte

Le projet utilise deux pipelines afin de ne pas construire un corpus uniquement influencé par la structure du français.

### 1. Français → San : mots, expressions et phrases

Le collecteur possède actuellement **500 prompts de traduction**, organisés en **25 thèmes**.

Chaque thème contient :

- 14 mots ou expressions ;
- 6 phrases ;
- soit 20 prompts par thème.

Cela représente au total :

```text
350 mots / expressions
150 phrases
----------------------
500 prompts Français → San
```

Une session standard propose environ **10 éléments**, généralement **7 mots + 3 phrases**.

La sélection favorise les prompts les moins couverts et évite autant que possible de redemander au même contributeur un prompt auquel il a déjà répondu. La cible initiale est de disposer de plusieurs contributions indépendantes par prompt, avec une valeur de référence de **3 contributions par élément**.

Workflow :

```text
Prompt français
      ↓
Réponse San texte et/ou audio
      ↓
Transcription de travail
      ↓
Validation linguistique 1
      ↓
Validation linguistique 2
      ↓
Donnée approuvée
      ↓
Corpus Français ↔ San
```

### 2. San → San : parole naturelle

Un second mode de collecte demande au locuteur de **parler librement en San**, sans traduire une phrase française mot à mot.

Le catalogue contient actuellement **40 sujets de parole naturelle**, par exemple autour de la famille, du mariage, des traditions, de l'agriculture, des marchés, des récits d'enfance, des proverbes, des contes, de la vie communautaire et de la transmission de la langue.

Exemple :

> « Racontez en San comment se déroule traditionnellement un mariage dans votre village ou votre communauté. »

Le but est de récupérer des structures spontanées et naturelles :

```text
Sujet de discussion
      ↓
Audio San naturel
      ↓
Transcription San intégrale
      ↓
Segmentation en phrases
      ↓
Traduction française de chaque segment
      ↓
Validation linguistique
      ↓
Corpus naturel San ↔ Français
```

La consigne française reste une **métadonnée d'élicitation**. Elle ne doit jamais être confondue avec la traduction du récit.

Tous les segments issus d'un même récit conservent le **même `source_id` et le même split** afin d'éviter les fuites entre train, validation et test.

## Variétés du San

Le projet distingue les variétés au lieu de les mélanger.

Les références de travail actuelles sont :

- **San Maka / San du Sud** — ISO 639-3 `sbd` ;
- **San Matya** — ISO 639-3 `stj` ;
- **San Maya** — ISO 639-3 `sym`.

Le formulaire public ne demande pas au contributeur de connaître les termes techniques « Maka », « Matya » ou « Maya ». Il demande plutôt **où la personne a principalement appris ou parlé le San**.

Le référentiel interne peut conserver une suggestion documentée :

- Toma → suggestion `San Maka / sbd` ;
- Tougan → suggestion `San Matya / stj`.

Cette suggestion sert uniquement d'aide au validateur. **La localité ne valide jamais automatiquement la variété linguistique.** Le validateur peut confirmer, modifier ou laisser la variété indéterminée.

Voir [`docs/DIALECTS.md`](docs/DIALECTS.md).

## Validation et qualité du corpus

Le cycle principal des contributions est :

```text
pending
  ↓
transcribed
  ↓
validated_once
  ↓
approved
```

En cas de désaccord entre validateurs :

```text
validated_twice → à départager
```

Une contribution peut aussi devenir :

```text
rejected
```

Une donnée n'entre pas automatiquement dans un dataset d'entraînement simplement parce qu'elle a été collectée. Elle doit respecter les règles de consentement, de validation et de variété linguistique du corpus concerné.

## Utilisateurs et rôles

### Public / contributeurs

Un contributeur peut commencer sans compte. Il peut ensuite se connecter par :

- Google OAuth ;
- email + code OTP à 8 chiffres.

Le même email Google / OTP correspond au même compte.

### Back-office

Les rôles système actuels sont :

- `admin` ;
- `moderator` ;
- `transcriber` ;
- `validator` ;
- `contributor`.

Les professions ou domaines d'expertise — linguiste, enseignant, développeur, chercheur, ML, communication, etc. — sont séparés des rôles d'autorisation.

## Exports dataset

Deux exports sont séparés afin de préserver la nature des données.

### Corpus élicité Français → San

L'export contient notamment :

- version du dataset ;
- `source_id` pseudonymisé ;
- split `train`, `validation` ou `test` ;
- direction `fr-san` ;
- code du prompt ;
- variété validée et code ISO ;
- texte français ;
- contexte ;
- texte San validé ;
- catégorie ;
- localité ;
- nombre de validations ;
- dates de soumission et validation.

### Corpus naturel San → Français

Chaque ligne correspond à un segment issu d'un récit naturel validé et contient notamment :

- `source_id` du récit parent ;
- position du segment ;
- direction `san-fr` ;
- consigne d'élicitation ;
- variété validée ;
- segment San ;
- traduction française ;
- catégorie et localité ;
- dates de soumission et validation.

Le projet doit conserver le même split pour toutes les données dérivées d'une même source.

## Gouvernance des données

Le fait que le **code** soit open source ne signifie pas que les **données** collectées sont automatiquement publiques.

Les enregistrements audio, traductions, métadonnées et profils sont soumis à des règles spécifiques concernant :

- consentement ;
- minimisation des données personnelles ;
- pseudonymisation ;
- provenance ;
- validation ;
- retrait et correction ;
- entraînement de modèles ;
- publication éventuelle d'un dataset ;
- licence du dataset.

Les audios bruts et données privées ne doivent jamais être ajoutés directement à GitHub.

Voir [`docs/DATA_GOVERNANCE.md`](docs/DATA_GOVERNANCE.md).

## Architecture du dépôt

```text
langue-san/
├── apps/
│   └── collector/          # Laravel : collecte, comptes, modération, exports
├── data/
│   ├── schema/             # schémas et exemples publics
│   └── samples/            # données fictives ou explicitement publiables
├── docs/
│   ├── VISION.md
│   ├── ROADMAP.md
│   ├── DATA_GOVERNANCE.md
│   ├── DIALECTS.md
│   └── DATA_SCHEMA.md
├── ml/
│   ├── notebooks/          # futurs notebooks Google Colab
│   └── src/                # preprocessing, entraînement, évaluation
├── CAHIER_DES_CHARGES.md
├── CONTRIBUTING.md
├── CODE_OF_CONDUCT.md
└── LICENSE
```

Le projet reste volontairement dans un **monorepo** au démarrage.

## Stack actuelle

### Collecteur

- PHP 8.3+ ;
- Laravel 13 ;
- Blade ;
- MySQL en développement principal ;
- Bootstrap / NiceAdmin pour le back-office ;
- Spatie Laravel Permission pour les rôles et permissions ;
- Fortify pour l'authentification staff ;
- Google OAuth + OTP email pour les contributeurs ;
- stockage privé Laravel pour les audios ;
- Resend prévu pour les emails transactionnels ;
- PHPUnit + GitHub Actions pour les tests.

### Machine Learning — phase suivante

La partie ML sera ajoutée après constitution d'un corpus suffisamment validé. Elle utilisera principalement :

- Python ;
- PyTorch ;
- Hugging Face Transformers / Datasets ;
- Google Colab pour les premières expérimentations ;
- baseline dictionnaire / mémoire de traduction ;
- modèles multilingues ou byte-level à évaluer selon la qualité du corpus ;
- métriques automatiques complétées par une évaluation humaine.

## Installation du collecteur

Depuis `apps/collector/` :

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

En environnement non `production`, le seeding peut également créer des comptes de démonstration afin de tester les interfaces. Ces comptes ne doivent pas être utilisés comme données linguistiques réelles.

Pour lancer les tests :

```bash
php artisan test
```

## Roadmap

La feuille de route détaillée est disponible dans [`docs/ROADMAP.md`](docs/ROADMAP.md).

Le chemin général est maintenant :

```text
Collecte ciblée + parole naturelle
            ↓
Transcription + segmentation
            ↓
Double validation humaine
            ↓
Corpus versionné
            ↓
Baseline ML sur Google Colab
            ↓
Traducteur expérimental
            ↓
Traduction bidirectionnelle
            ↓
Application d'apprentissage
```

Le cahier des charges consolidé est disponible dans [`CAHIER_DES_CHARGES.md`](CAHIER_DES_CHARGES.md).

## Prochains jalons

Avant d'entraîner un modèle plus ambitieux, le projet doit notamment :

- faire relire les 500 prompts français ;
- lancer un pilote avec de vrais locuteurs ;
- constituer un petit groupe de validateurs ;
- confirmer la stratégie de variété pilote ;
- tester l'enregistrement sur téléphones et réseaux instables ;
- finaliser la procédure de retrait / correction des données ;
- configurer Resend et Google OAuth en production ;
- mettre en place les sauvegardes privées ;
- produire une première version de corpus réellement validé.

## Contribuer

Les contributions sont ouvertes à différents profils :

- **locuteurs San** : voix, vocabulaire, récits, variantes ;
- **linguistes / enseignants** : orthographe, transcription, grammaire, validation ;
- **développeurs** : Laravel, UI/UX, API, audio, tests, DevOps ;
- **data / ML** : préparation du corpus, évaluation, entraînement ;
- **chercheurs** : méthodologie, ressources, documentation ;
- **associations / communautés** : mobilisation et gouvernance.

Consultez [`CONTRIBUTING.md`](CONTRIBUTING.md) avant de commencer.

## Statut

🚧 **Projet expérimental — collecteur MVP avancé, préparation du pilote terrain.**

Les traductions, transcriptions et futurs modèles ne doivent pas être considérés comme des références officielles tant qu'ils n'ont pas été suffisamment validés par des locuteurs et spécialistes compétents.

## Licence

Le code source de ce dépôt est publié sous **Apache License 2.0**.

Les datasets, enregistrements audio et ressources tierces peuvent être soumis à des licences ou autorisations différentes. La licence du code ne s'applique pas automatiquement aux données collectées.
