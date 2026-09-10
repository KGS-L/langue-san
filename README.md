# Langue SAN

> Initiative open source pour documenter, numériser et développer des ressources Français ↔ San au Burkina Faso.

**Langue SAN** est un projet communautaire et open source. Son objectif est de construire progressivement des ressources linguistiques fiables pour les variétés du San, puis de les utiliser pour créer des outils numériques utiles : dictionnaire, traduction, audio, apprentissage et, à terme, modèles de traitement automatique du langage.

Le projet est encore au démarrage. Toute personne peut contribuer : locuteurs San, linguistes, enseignants, développeurs, designers, data/ML engineers, associations et personnes intéressées par la préservation numérique des langues nationales.

## Vision

Nous voulons rendre les langues San mieux représentées dans le numérique, tout en respectant les locuteurs, les variantes linguistiques, les règles d'écriture, la provenance des données et le consentement des contributeurs.

Le projet n'a pas pour objectif d'inventer ou de normaliser arbitrairement la langue. Il cherche d'abord à **collecter, documenter, structurer, faire valider et préserver** les formes réellement utilisées par les communautés.

## Objectifs

La progression prévue est simple :

1. construire une base lexicale Français ↔ San ;
2. collecter des phrases courtes et naturelles ;
3. collecter des enregistrements audio lorsque possible ;
4. faire transcrire et valider les contributions ;
5. construire un corpus bilingue propre ;
6. tester des modèles de traduction sur Google Colab ;
7. publier progressivement des outils de traduction et d'apprentissage.

### Premier jalon

- 100 concepts validés ;
- puis 500 concepts/mots courants ;
- premières phrases validées ;
- plusieurs contributions par élément ;
- audio lorsque possible ;
- identification claire de la variété linguistique ;
- aucune donnée brute sensible publiée automatiquement.

Les premiers thèmes de collecte incluent : salutations, présentation/identité, famille, nombres, temps/jours, nourriture, maison, marché, déplacements, école et travail.

## Variétés du San

Le projet distingue les variétés au lieu de les mélanger. Les catégories de travail initiales sont notamment :

- **San Maka / San du Sud** (`sbd`) ;
- **San Matya** (`sym`) ;
- **San Maya** (`stj`).

Une localité ne doit pas être utilisée seule pour déduire automatiquement une variété. Le système pourra conserver à la fois la variété déclarée par le contributeur et celle confirmée par un validateur.

Voir [`docs/DIALECTS.md`](docs/DIALECTS.md).

## Comment la collecte fonctionnera

Une session de contribution pourra proposer environ 10 éléments, par exemple **7 mots + 3 phrases**. Pour chaque élément, le contributeur pourra :

- écrire l'équivalent en San ;
- enregistrer sa réponse en audio ;
- ou fournir les deux.

Un exemple de cycle :

```text
Contribution
    ↓
En attente
    ↓
Transcription si nécessaire
    ↓
Validation linguistique
    ↓
Deuxième validation lorsque possible
    ↓
Donnée approuvée
    ↓
Dataset exploitable
```

Une contribution non validée ou dont la variété reste inconnue peut être conservée pour révision, mais ne doit pas être intégrée automatiquement au corpus d'entraînement.

## Architecture du dépôt

```text
langue-san/
├── apps/
│   └── collector/       # futur site Laravel de collecte + dashboard admin
├── data/
│   ├── schema/          # schémas et exemples publics
│   └── samples/         # données fictives ou explicitement publiables
├── docs/
│   ├── VISION.md
│   ├── ROADMAP.md
│   ├── DATA_GOVERNANCE.md
│   ├── DIALECTS.md
│   └── DATA_SCHEMA.md
├── ml/
│   ├── notebooks/       # futurs notebooks Google Colab
│   └── src/             # preprocessing, entraînement, évaluation
├── CONTRIBUTING.md
├── CODE_OF_CONDUCT.md
└── LICENSE
```

Le projet reste volontairement dans un **monorepo** au démarrage. Si certaines briques deviennent autonomes plus tard, elles pourront être séparées dans d'autres dépôts.

## Technologies prévues

La première application de collecte sera développée avec **Laravel**, avec un dashboard administrateur pour la transcription, la validation, la gestion des contributions et les exports.

La partie Machine Learning sera ajoutée plus tard avec principalement :

- Python ;
- PyTorch ;
- Hugging Face Transformers / Datasets ;
- Google Colab pour les premières expérimentations ;
- métriques automatiques + validation humaine.

## Données et audio

Le fait que le **code** soit open source ne signifie pas que toutes les **données** collectées sont automatiquement publiques.

Les enregistrements audio, traductions, métadonnées et éventuelles données de contributeurs sont soumis à des règles spécifiques concernant :

- consentement ;
- anonymisation ;
- provenance ;
- validation ;
- réutilisation ;
- entraînement de modèles ;
- publication éventuelle d'un dataset.

Les audios bruts et données privées ne doivent jamais être ajoutés directement à GitHub.

Voir [`docs/DATA_GOVERNANCE.md`](docs/DATA_GOVERNANCE.md).

## Contribuer

Tout le monde peut participer.

- **Locuteurs San** : traductions, audio, variantes, validation.
- **Linguistes / enseignants** : orthographe, grammaire, transcription, méthodologie.
- **Développeurs** : Laravel, UI/UX, API, audio, tests, DevOps.
- **Data / ML** : nettoyage, préparation du corpus, entraînement, évaluation.
- **Design / produit** : expérience de contribution, accessibilité, documentation.

Consultez [`CONTRIBUTING.md`](CONTRIBUTING.md) avant de commencer.

## Roadmap

La feuille de route détaillée est disponible dans [`docs/ROADMAP.md`](docs/ROADMAP.md).

En résumé :

```text
Collecte → Validation → Dataset → Google Colab → Baseline ML → Traduction → Apprentissage
```

## Statut

🚧 **Projet expérimental — phase de démarrage.**

Les traductions, transcriptions et futurs modèles ne doivent pas être considérés comme des références officielles tant qu'ils n'ont pas été suffisamment validés par des locuteurs et spécialistes compétents.

## Licence

Le code source de ce dépôt est publié sous **Apache License 2.0**.

Les datasets, enregistrements audio et ressources tierces peuvent être soumis à des licences ou autorisations différentes. La licence du code ne s'applique pas automatiquement aux données collectées.

## Rejoindre le projet

Vous pouvez commencer par :

- ouvrir une issue ;
- proposer une amélioration ;
- corriger la documentation ;
- contribuer au futur site Laravel ;
- aider à définir les règles de collecte et de validation ;
- aider comme locuteur ou validateur linguistique.

**Chaque contribution utile compte.**
