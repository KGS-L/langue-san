# Roadmap

Cette roadmap décrit la direction actuelle du projet. Elle évoluera selon les retours des locuteurs, validateurs et contributeurs.

## Phase 0 — Cadrage

- [ ] choisir la première variété prioritaire ;
- [ ] définir la communauté pilote ;
- [ ] documenter le processus de consentement ;
- [ ] définir le schéma de données ;
- [ ] constituer un petit groupe de validateurs ;
- [ ] inventorier les ressources existantes et leurs licences.

## Phase 1 — Collecteur Laravel

- [ ] initialiser l'application Laravel dans `apps/collector/` ;
- [ ] créer les catégories et prompts ;
- [ ] formulaire de profil linguistique ;
- [ ] session de 10 contributions ;
- [ ] saisie texte optionnelle ;
- [ ] enregistrement audio navigateur ;
- [ ] stockage privé des audios ;
- [ ] attribution intelligente des prompts selon le nombre de contributions ;
- [ ] dashboard administrateur ;
- [ ] transcription des réponses audio ;
- [ ] validation / rejet / correction ;
- [ ] suivi du nombre de validations ;
- [ ] export CSV/JSON des données approuvées.

## Phase 2 — Corpus v0.x

### v0.1

- [ ] 100 concepts validés ;
- [ ] plusieurs contributeurs par concept lorsque possible ;
- [ ] vérifier le fonctionnement réel du workflow de validation.

### v0.2

- [ ] 500 concepts/mots courants ;
- [ ] premiers enregistrements audio validés ;
- [ ] couverture de plusieurs thèmes du quotidien.

### v0.3

- [ ] 500 concepts ;
- [ ] au moins 200 phrases simples validées ;
- [ ] séparation stricte train / validation / test ;
- [ ] premières statistiques de qualité du corpus.

## Phase 3 — Baseline Machine Learning

- [ ] préparer le pipeline Python ;
- [ ] notebook Google Colab reproductible ;
- [ ] baseline dictionnaire / mémoire de traduction ;
- [ ] tester un modèle multilingue ou byte-level adapté au contexte ;
- [ ] évaluer avec métriques automatiques ;
- [ ] organiser l'évaluation humaine ;
- [ ] documenter les erreurs par catégorie.

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

Nous n'avançons pas vers un modèle plus complexe simplement parce qu'un objectif de quantité est atteint. La qualité, la cohérence des variantes, la validation humaine et le droit d'usage des données restent des critères de passage.
