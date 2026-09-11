# Roadmap

Cette roadmap décrit la direction actuelle du projet. Elle évoluera selon les retours des locuteurs, validateurs et contributeurs.

## Phase 0 — Cadrage

- [ ] choisir la première variété prioritaire pour le pilote terrain ;
- [ ] définir la communauté pilote ;
- [x] documenter le processus initial de consentement ;
- [x] définir le schéma de données du collecteur ;
- [ ] constituer un petit groupe de validateurs réels ;
- [ ] inventorier complètement les ressources existantes et leurs licences.

Le formulaire public demande la localité plutôt qu'un nom technique de variété. Le référentiel peut proposer une variété interne (par exemple Toma → Maka, Tougan → Matya) avec sa provenance, mais seul un validateur humain confirme la variété finale.

## Phase 1 — Collecteur Laravel

- [x] initialiser l'application Laravel dans `apps/collector/` ;
- [x] créer les catégories et prompts ;
- [x] préparer un catalogue de 500 prompts Français → San ;
- [x] formulaire de profil linguistique basé sur la localité ;
- [x] session de 10 contributions (7 mots/expressions + 3 phrases) ;
- [x] saisie texte optionnelle ;
- [x] enregistrement audio navigateur ;
- [x] stockage privé des audios ;
- [x] attribution intelligente des prompts selon le nombre de contributions ;
- [x] dashboard administrateur ;
- [x] transcription des réponses audio ;
- [x] validation / rejet / correction ;
- [x] double validation humaine ;
- [x] export CSV versionné des données approuvées ;
- [x] rôles spécialisés transcripteur / validateur ;
- [x] espace contributeur et historique ;
- [x] CI PHPUnit sur GitHub Actions.

## Phase 1B — Parole naturelle San → San

Cette collecte complète les phrases élicitées Français → San afin d'éviter de construire un corpus composé uniquement de structures influencées par le français.

- [x] banque initiale de sujets de récits naturels ;
- [x] enregistrement long (recommandé 2 à 5 minutes) ;
- [x] transcription San intégrale ;
- [x] segmentation manuelle en phrases ;
- [x] traduction française de chaque segment ;
- [x] rattachement de chaque segment au récit source ;
- [x] validation linguistique après segmentation ;
- [x] export San → Français séparé du corpus élicité ;
- [x] split train / validation / test commun à tous les segments d'un même récit ;
- [ ] tester le workflow sur de vrais récits avec plusieurs locuteurs ;
- [ ] documenter des règles pratiques de segmentation (ponctuation, hésitations, répétitions, discours rapporté, etc.).

Pipeline :

```text
Sujet en français (simple déclencheur)
        ↓
parole naturelle en San
        ↓
audio San privé
        ↓
transcription San complète
        ↓
segmentation en phrases / unités utiles
        ↓
traduction française par segment
        ↓
validation de la variété et du contenu
        ↓
corpus San ↔ Français
```

La consigne française qui déclenche le récit est une métadonnée. Elle ne doit jamais être utilisée comme si elle était la traduction du récit San.

## Phase 2 — Corpus v0.x

### v0.1 — Pilote réel

- [ ] 10 à 20 contributeurs réels ;
- [ ] 2 à 4 validateurs compétents ;
- [ ] premiers concepts couverts par au moins 3 locuteurs lorsque possible ;
- [ ] premiers récits naturels transcrits, segmentés et traduits ;
- [ ] vérifier le fonctionnement réel du workflow de validation et des désaccords.

### v0.2 — Couverture 500 prompts

- [x] catalogue de collecte de 500 prompts Français → San prêt ;
- [ ] obtenir une couverture suffisante des 500 prompts ;
- [ ] premiers enregistrements audio validés ;
- [ ] couverture de plusieurs thèmes du quotidien ;
- [ ] augmenter progressivement le volume de parole naturelle.

### v0.3 — Corpus entraînable

- [ ] 500 prompts suffisamment couverts ;
- [ ] au moins 200 phrases simples validées ;
- [ ] volume significatif de segments provenant de récits naturels ;
- [x] séparation stricte train / validation / test dans l'export ;
- [ ] premières statistiques de qualité du corpus ;
- [ ] data card et licence du dataset définies avant publication.

## Phase 3 — Baseline Machine Learning

- [ ] préparer le pipeline Python ;
- [ ] notebook Google Colab reproductible ;
- [ ] baseline dictionnaire / mémoire de traduction ;
- [ ] tester un modèle multilingue ou byte-level adapté au contexte ;
- [ ] évaluer avec métriques automatiques ;
- [ ] organiser l'évaluation humaine ;
- [ ] documenter les erreurs par catégorie et variété.

## Phase 4 — Premier traducteur expérimental

- [ ] Français → San sur un domaine limité ;
- [ ] affichage clair de la variété cible ;
- [ ] niveau de confiance ou avertissement ;
- [ ] fonction de signalement / correction ;
- [ ] historique des versions du modèle ;
- [ ] API d'inférence.

## Phase 5 — Bidirectionnel

- [ ] San → Français ;
- [ ] entraînement ou routage bidirectionnel ;
- [ ] intégrer correctement les segments de parole naturelle ;
- [ ] tests de contamination entre variétés ;
- [ ] amélioration du corpus grâce aux corrections validées.

## Phase 6 — Apprentissage

- [ ] vocabulaire ;
- [ ] phrases du quotidien ;
- [ ] écoute et répétition ;
- [ ] association mot-image ;
- [ ] remise en ordre ;
- [ ] traduction guidée ;
- [ ] progression par niveau.

## Principe de passage entre phases

Nous n'avançons pas vers un modèle plus complexe simplement parce qu'un objectif de quantité est atteint. La qualité, la naturalité du San, la cohérence des variétés, la validation humaine et le droit d'usage des données restent des critères de passage.
