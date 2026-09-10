# Machine Learning

La partie ML sera développée après la constitution d'un premier corpus validé.

## Principe

Nous ne prévoyons pas d'entraîner un grand modèle depuis zéro au démarrage. Les premières expériences utiliseront des modèles existants adaptés avec nos données.

## Environnement initial

- Google Colab ;
- Python ;
- PyTorch ;
- Hugging Face Transformers ;
- Hugging Face Datasets.

## Étapes prévues

```text
Export des données approuvées
        ↓
Nettoyage / normalisation
        ↓
Split train / validation / test
        ↓
Baseline
        ↓
Fine-tuning expérimental
        ↓
Évaluation automatique
        ↓
Évaluation par locuteurs
```

## Règle importante

Le jeu de test ne doit jamais être utilisé pour l'entraînement. Les deux directions d'une même paire bilingue doivent rester dans le même split afin d'éviter les fuites de données.

## Évaluation

Les métriques automatiques seront utiles, mais l'acceptation finale d'une traduction dépendra d'évaluations humaines : sens, naturel, orthographe et respect de la variété cible.
