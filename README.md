# Langue SAN

> Initiative open source pour documenter, numériser et développer des ressources Français ↔ San au Burkina Faso.

**Langue SAN** est un projet communautaire et open source dont l'objectif est de constituer progressivement des ressources linguistiques fiables pour les variétés du San, puis de les utiliser pour créer des outils numériques utiles : corpus bilingues, audio, traduction, apprentissage et, à terme, modèles de traitement automatique du langage.

Le projet ne cherche pas à inventer une nouvelle langue ni à normaliser arbitrairement les usages. Il vise d'abord à **collecter, documenter, transcrire, structurer, valider et préserver** les formes réellement utilisées par les locuteurs.

## État actuel

Le collecteur web Laravel est maintenant un MVP avancé dans `apps/collector/`.

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
- demandes de correction, suppression d'audio, retrait de contribution et anonymisation ;
- purge automatique des audios arrivés à expiration ;
- exports dataset versionnés ;
- séparation déterministe train / validation / test ;
- tests automatisés et CI GitHub Actions.

Le prochain objectif principal est désormais le **pilote réel** avec des locuteurs et validateurs compétents.

## Deux formes complémentaires de collecte

### 1. Français → San : mots, expressions et phrases

Le collecteur possède **500 prompts de traduction**, organisés en **25 thèmes** :

```text
350 mots / expressions
150 phrases
----------------------
500 prompts Français → San
```

Chaque thème contient 14 mots/expressions et 6 phrases. Une session standard propose environ **10 éléments : 7 mots/expressions + 3 phrases**.

La sélection favorise les prompts les moins couverts, évite autant que possible de redemander au même contributeur un prompt déjà traité et vise plusieurs contributions indépendantes par élément.

```text
Prompt français
      ↓
Réponse San texte et/ou audio
      ↓
Transcription
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

Le projet collecte également de la parole produite directement en San afin de ne pas construire un corpus uniquement influencé par la structure du français.

Le catalogue contient actuellement **40 sujets de parole naturelle** autour notamment de la famille, du mariage, des traditions, de l'agriculture, du marché, des récits d'enfance, des proverbes, des contes, de la vie communautaire et de la transmission de la langue.

Exemple :

> « Racontez en San comment se déroule traditionnellement un mariage dans votre village ou votre communauté. »

La consigne française est seulement un déclencheur : elle n'est jamais considérée comme la traduction du récit.

```text
Sujet de discussion
      ↓
Audio San naturel
      ↓
Transcription San intégrale
      ↓
Segmentation
      ↓
Traduction française de chaque segment
      ↓
Validation linguistique
      ↓
Corpus naturel San ↔ Français
```

Tous les segments issus du même récit conservent le **même `source_id` et le même split** afin d'éviter les fuites entre train, validation et test.

## Variétés du San

Le projet suit séparément :

- **San Maka / San du Sud** — ISO 639-3 `sbd` ;
- **San Matya** — ISO 639-3 `stj` ;
- **San Maya** — ISO 639-3 `sym`.

### Aucune variété unique n'est ciblée à l'avance

Les trois variétés font partie du projet. La stratégie est **pilotée par les données réellement collectées et validées**.

Selon la couverture obtenue, les futures expériences pourront utiliser un corpus dédié à une variété, plusieurs corpus séparés ou éventuellement un modèle multi-variétés explicitement étiqueté. Une variété insuffisamment couverte ne sera pas artificiellement fusionnée avec les autres.

Le formulaire public ne demande pas au contributeur de connaître les termes techniques « Maka », « Matya » ou « Maya ». Il demande plutôt **où la personne a principalement appris ou parlé le San**.

Le référentiel interne peut conserver une suggestion documentée :

- Toma → suggestion `San Maka / sbd` ;
- Tougan → suggestion `San Matya / stj`.

Cette suggestion aide le validateur mais **ne devient jamais automatiquement la variété validée**.

Voir [`docs/DIALECTS.md`](docs/DIALECTS.md).

## Validation et qualité

Workflow principal :

```text
pending
  ↓
transcribed
  ↓
validated_once
  ↓
approved
```

En cas de désaccord :

```text
validated_twice → à départager
```

Une contribution peut également être `rejected`.

Une donnée n'entre dans un dataset que si elle satisfait les règles de validation, consentement et identification de variété applicables.

## Utilisateurs et rôles

### Contributeurs

Un contributeur peut commencer sans compte puis se connecter avec :

- Google OAuth ;
- email + code OTP à 8 chiffres.

Le même email vérifié par Google ou OTP correspond au même compte.

### Back-office

Rôles système :

- `admin` ;
- `moderator` ;
- `transcriber` ;
- `validator` ;
- `contributor`.

Les professions — linguiste, enseignant, développeur, chercheur, ML, communication, etc. — restent séparées des rôles d'autorisation.

## Consentement, retrait et conservation

Le consentement est versionné. La version courante prévoit notamment que :

- les audios restent privés ;
- une publication publique d'audio nécessite une autorisation spécifique ;
- les audios de contributions définitivement rejetées sont supprimés après **90 jours** ;
- les audios encore `pending` sans traitement depuis **12 mois** sont supprimés ;
- un contributeur connecté peut demander une correction, supprimer son audio, retirer une contribution ou demander l'anonymisation de son compte ;
- le retrait d'une contribution supprime immédiatement son contenu linguistique encore stocké et son audio et l'exclut des futurs exports ;
- les demandes nécessitant une intervention humaine ont un objectif opérationnel de traitement sous **30 jours**.

La page contributeur dédiée est `/mes-donnees`.

La purge automatique peut aussi être déclenchée manuellement :

```bash
php artisan data:purge-expired-audio
```

Voir [`docs/DATA_GOVERNANCE.md`](docs/DATA_GOVERNANCE.md).

## Exports dataset

### Corpus élicité Français → San

L'export contient notamment : version, `source_id`, split, direction `fr-san`, prompt, variété validée, français, San validé, catégorie et métadonnées de validation.

### Corpus naturel San → Français

Chaque ligne correspond à un segment issu d'un récit naturel et contient notamment : `source_id` du récit parent, position, split, direction `san-fr`, consigne d'élicitation, variété validée, segment San et traduction française.

Une contribution retirée est systématiquement exclue de tous les futurs exports.

## Architecture du dépôt

```text
langue-san/
├── apps/
│   └── collector/          # Laravel : collecte, comptes, modération, exports
├── data/
│   ├── schema/
│   └── samples/
├── docs/
│   ├── VISION.md
│   ├── ROADMAP.md
│   ├── DATA_GOVERNANCE.md
│   ├── DIALECTS.md
│   └── DATA_SCHEMA.md
├── ml/
│   ├── notebooks/
│   └── src/
├── CAHIER_DES_CHARGES.md
├── CONTRIBUTING.md
├── CODE_OF_CONDUCT.md
└── LICENSE
```

## Stack actuelle

### Collecteur

- PHP 8.3+ ;
- Laravel 13 ;
- Blade ;
- MySQL ;
- Bootstrap / NiceAdmin ;
- Spatie Laravel Permission ;
- Fortify ;
- Google OAuth + OTP email ;
- stockage privé Laravel ;
- Resend prévu pour les emails transactionnels ;
- PHPUnit + GitHub Actions.

### Machine Learning — phase suivante

Après constitution d'un corpus suffisamment validé :

- Python ;
- PyTorch ;
- Hugging Face Transformers / Datasets ;
- Google Colab ;
- baseline dictionnaire / mémoire de traduction ;
- modèles multilingues ou byte-level à comparer ;
- métriques automatiques + évaluation humaine.

## Installation du collecteur

Depuis `apps/collector/` :

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

En environnement non `production`, le seeding peut créer des comptes de démonstration pour tester les interfaces. Ils ne constituent jamais des données linguistiques réelles.

Tests :

```bash
php artisan test
```

## Roadmap

Voir [`docs/ROADMAP.md`](docs/ROADMAP.md) et [`CAHIER_DES_CHARGES.md`](CAHIER_DES_CHARGES.md).

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

## Prochains jalons

Avant la première expérimentation ML sérieuse :

- relire les 500 prompts et 40 sujets naturels ;
- lancer un pilote avec de vrais locuteurs ;
- constituer un groupe de validateurs ;
- mesurer séparément la couverture Maka / Matya / Maya ;
- tester audio et réseau sur de vrais téléphones ;
- configurer Resend et Google OAuth en production ;
- mettre en place et tester les sauvegardes privées ;
- définir la data card et la licence du futur dataset ;
- produire une première version de corpus réellement validé.

## Statut

🚧 **Projet expérimental — collecteur MVP avancé, préparation du pilote terrain.**

Les traductions, transcriptions et futurs modèles ne doivent pas être considérés comme des références officielles tant qu'ils n'ont pas été suffisamment validés par des locuteurs et spécialistes compétents.

## Licence

Le code source est publié sous **Apache License 2.0**.

Les datasets, enregistrements audio et ressources tierces peuvent être soumis à des licences ou autorisations différentes. La licence du code ne s'applique pas automatiquement aux données collectées.
