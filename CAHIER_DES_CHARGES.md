# Cahier des charges — Langue SAN

**Projet :** Langue SAN  
**Version :** 0.4  
**Date de mise à jour :** 11 septembre 2026  
**Statut :** collecteur MVP avancé — préparation du pilote terrain  
**Dépôt :** `KGS-L/langue-san`

---

## 1. Objet

Ce document définit le périmètre fonctionnel, technique, linguistique et de gouvernance du projet **Langue SAN**. Il sert de référence commune pour le collecteur web, le back-office, la constitution des corpus, la future partie Machine Learning, le futur traducteur Français ↔ San et la future application d'apprentissage.

Principe directeur :

> **Documenter la langue existante, préserver sa diversité, valider humainement les données et seulement ensuite construire des outils IA.**

Le projet ne doit pas inventer de traductions San, mélanger silencieusement les variétés, déduire automatiquement une variété d'une localité, publier des audios sans consentement spécifique ni intégrer des ressources tierces sans vérifier leurs droits.

---

## 2. Objectifs

Le projet doit permettre de :

- collecter du vocabulaire et des phrases Français → San ;
- collecter de la parole naturelle produite directement en San ;
- stocker les audios de manière privée ;
- transcrire et segmenter les données ;
- traduire en français les segments de parole naturelle ;
- identifier et valider la variété linguistique ;
- obtenir deux validations indépendantes lorsque possible ;
- produire des datasets versionnés et traçables ;
- conserver des splits train / validation / test stables ;
- permettre la correction et le retrait des données ;
- préparer une baseline ML reproductible ;
- construire ensuite un traducteur puis des fonctionnalités d'apprentissage.

---

## 3. Variétés linguistiques

Le projet suit séparément :

| Variété | ISO 639-3 | Politique |
| --- | --- | --- |
| San Maka / San du Sud | `sbd` | collectée séparément |
| San Matya | `stj` | collectée séparément |
| San Maya | `sym` | collectée séparément |

### 3.1 Aucune variété unique ciblée

**Les trois variétés font partie du périmètre.** Le projet ne fixe pas à l'avance une variété prioritaire définitive.

La stratégie est pilotée par les données :

```text
collecte multi-communautés
       ↓
validation de la variété
       ↓
mesure de la couverture réelle
       ↓
corpus séparés / comparables
       ↓
choix des expériences ML selon quantité + qualité
```

Une campagne terrain peut être concentrée géographiquement pour des raisons pratiques sans exclure les autres variétés du projet.

Une variété insuffisamment couverte reste documentée ; elle ne doit pas être fusionnée artificiellement avec une autre pour augmenter le volume.

### 3.2 Localité côté utilisateur

Le public n'est pas obligé de connaître les noms « Maka », « Matya » ou « Maya ». Le formulaire demande plutôt :

> Dans quelle ville ou localité avez-vous principalement appris ou parlé le San ?

Le référentiel peut contenir une suggestion interne documentée, notamment :

- Toma → San Maka / `sbd` ;
- Tougan → San Matya / `stj`.

La suggestion ne devient jamais automatiquement la variété validée. Le validateur peut confirmer, modifier ou laisser indéterminé.

---

## 4. Acteurs et permissions

### Visiteur

Peut consulter le site public, les pages légales et commencer une contribution sans compte.

### Contributeur

Peut :

- contribuer sans mot de passe ;
- se connecter avec Google ou OTP email ;
- compléter son profil ;
- retrouver ses contributions ;
- gérer son profil public opt-in ;
- candidater pour rejoindre le projet ;
- gérer ses demandes de correction, suppression ou retrait.

### Transcripteur

Peut écouter les audios privés autorisés et saisir/corriger les transcriptions. Il ne reçoit pas automatiquement les droits d'administration.

### Validateur

Peut confirmer/corriger une transcription, confirmer une variété, approuver/rejeter une contribution et participer à la double validation. Un même compte ne peut pas effectuer les deux validations d'une même contribution.

### Modérateur

Peut gérer les opérations linguistiques et référentiels autorisés, sans obtenir automatiquement les droits sensibles réservés à l'administrateur.

### Administrateur

Peut notamment gérer les modérateurs, permissions, référentiels, candidatures, prompts, demandes de données et exports dataset.

Rôles système :

```text
admin
moderator
transcriber
validator
contributor
```

---

## 5. Authentification

### Contributeurs

Authentification passwordless :

- Google OAuth ;
- email + OTP à 8 chiffres.

Le même email vérifié doit correspondre au même compte, quel que soit le canal utilisé.

### Staff

Admin et modérateurs utilisent email + mot de passe. Le parcours staff est séparé du parcours public.

---

## 6. Profil contributeur

Contexte linguistique minimal :

- localité principale ;
- autre localité si nécessaire ;
- niveau de pratique ;
- capacité à écrire le San.

Après authentification :

- nom ;
- pays ;
- tranche d'âge ;
- profession ;
- organisation facultative.

Le profil public est désactivé par défaut. Ne jamais exposer publiquement email, tranche d'âge, localité linguistique exacte, niveau de maîtrise, audio, consentement ou identifiants techniques.

---

## 7. Collecte A — Français → San

Catalogue actuel :

```text
25 thèmes
× 20 prompts
= 500 prompts
```

Répartition :

```text
350 mots / expressions
150 phrases
```

Chaque thème contient 14 mots/expressions et 6 phrases.

Session standard :

```text
7 mots / expressions
+
3 phrases
=
10 prompts
```

Le moteur sélectionne des prompts actifs, évite ceux déjà traités par le même contributeur, favorise les moins couverts, prend en compte la priorité et randomise les ex æquo.

Pour chaque prompt, le contributeur peut écrire, enregistrer sa voix, faire les deux ou passer la question. La cible initiale est d'obtenir environ 3 contributions indépendantes par prompt lorsque possible.

---

## 8. Collecte B — Parole naturelle San → San

Objectif : éviter un corpus où le San est systématiquement produit comme calque de structures françaises.

Le catalogue contient **40 sujets de parole naturelle** autour notamment de la famille, du mariage, des cérémonies, de l'agriculture, du marché, des récits d'enfance, des proverbes, des contes, de la vie communautaire et de la transmission du San.

La consigne doit préciser :

> Ne traduisez pas la consigne française mot à mot. Parlez naturellement en San avec vos propres mots.

### Audio

- obligatoire pour la parole naturelle ;
- durée recommandée : 2 à 5 minutes ;
- limite fonctionnelle : environ 10 minutes ;
- réécoute avant envoi ;
- possibilité de recommencer.

### Pipeline

```text
Sujet de discussion
      ↓
Audio San naturel
      ↓
Transcription San complète
      ↓
Segmentation
      ↓
Traduction française de chaque segment
      ↓
Validation linguistique
      ↓
Corpus naturel San ↔ Français
```

La consigne française reste une métadonnée d'élicitation et ne constitue jamais la traduction du récit.

Chaque segment peut conserver position, `start_ms`, `end_ms`, texte San, traduction française, variété et auteur des modifications.

Tous les segments d'un récit gardent le même `source_id` et le même split train/validation/test.

---

## 9. Workflow de validation

Statuts :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

`validated_twice` indique notamment un désaccord nécessitant arbitrage.

Une parole naturelle ne peut pas rejoindre le dataset tant que sa transcription, sa segmentation et les traductions françaises des segments utiles ne sont pas prêtes.

Côté contributeur, les statuts sont simplifiés en : Reçue, En vérification, Validée, Non retenue et Retirée.

---

## 10. Consentement et confidentialité

Le consentement est versionné. La version actuelle est **1.1**.

Elle couvre explicitement :

- stockage des réponses ;
- transcription ;
- validation ;
- constitution du corpus ;
- utilisation pour entraînement/évaluation lorsque autorisée ;
- caractère privé des audios ;
- politique de conservation ;
- droit de correction et retrait.

L'audio brut n'est jamais publié publiquement par défaut. Une publication future nécessite une autorisation spécifique distincte.

---

## 11. Conservation des données

Politique opérationnelle :

- audio d'une contribution définitivement rejetée : suppression après **90 jours** ;
- audio d'une contribution `pending` sans traitement depuis **12 mois** : suppression ;
- audio retenu : conservation privée tant qu'il est utile et couvert par le consentement, sauf demande de suppression ;
- données de compte : conservation jusqu'à demande d'anonymisation/suppression, sous réserve d'une trace technique minimale.

Configuration : `config/data_retention.php`.

Commande :

```bash
php artisan data:purge-expired-audio
```

Cette commande est planifiée quotidiennement via le scheduler Laravel.

---

## 12. Correction, suppression et retrait

Le contributeur authentifié dispose de `/mes-donnees`.

Types de demandes :

- correction ;
- retrait d'une contribution ;
- suppression d'un audio ;
- anonymisation/suppression de données de compte ;
- autre demande.

### Retrait d'une contribution

Le retrait est immédiat :

```text
withdrawn_at renseigné
+ audio supprimé
+ texte source supprimé
+ transcription supprimée
+ segments supprimés
+ validations liées supprimées
+ exclusion de tous les futurs exports
```

La ligne technique peut rester sans contenu linguistique afin de conserver une trace minimale de retrait.

### Suppression audio

Le fichier et son enregistrement en base sont supprimés immédiatement sans obligatoirement retirer la contribution textuelle.

### Correction / anonymisation

Les demandes nécessitant une intervention humaine sont visibles dans la file admin **Demandes de données**. Objectif de traitement : **30 jours**.

### Dataset déjà publié

Le projet garantit l'exclusion des données retirées de ses futures versions. Il ne peut pas garantir la suppression de copies qu'un tiers aurait déjà téléchargées avant le retrait.

---

## 13. Back-office

Le back-office couvre :

- dashboard statistiques ;
- catégories ;
- prompts ;
- localités ;
- variétés ;
- contributions ;
- file À transcrire ;
- file À valider ;
- lecture sécurisée des audios ;
- segmentation des récits ;
- candidatures projet ;
- utilisateurs et permissions ;
- demandes de données ;
- exports dataset.

---

## 14. Exports datasets

### Français → San

Colonnes principales :

```text
dataset_version
source_id
split
direction
prompt_code
variety
variety_iso
french
context
san
type
category
locality
validation_count
submitted_at
approved_at
```

### Parole naturelle San → Français

```text
dataset_version
source_id
segment_position
split
direction
elicitation_prompt_code
elicitation_prompt
variety
variety_iso
san
french
category
locality
submitted_at
approved_at
```

Toute contribution avec `withdrawn_at` est exclue des deux exports.

Le secret `DATASET_SOURCE_SALT` doit rester stable après publication de corpus versionnés.

---

## 15. Architecture applicative

```text
Blade / UI
    ↓
FormRequest
    ↓
Controller
    ↓
Service
    ↓
RepositoryInterface
    ↓
EloquentRepository
    ↓
Model
    ↓
Database
```

Éléments transverses : Policies, Enums, Spatie Permission et tests Feature.

---

## 16. Stack technique

### Collecteur

- PHP 8.3+ ;
- Laravel 13 ;
- Blade ;
- MySQL ;
- Bootstrap / NiceAdmin ;
- Spatie Laravel Permission ;
- Laravel Fortify ;
- Google OAuth ;
- OTP email ;
- Resend en production ;
- stockage privé Laravel ;
- PHPUnit ;
- GitHub Actions.

### Machine Learning futur

- Python ;
- PyTorch ;
- Hugging Face Transformers / Datasets ;
- Google Colab ;
- baseline dictionnaire / mémoire de traduction ;
- modèles multilingues ou byte-level à comparer ;
- métriques automatiques + évaluation humaine.

Le choix d'un modèle par variété ou multi-variétés dépendra des volumes réellement validés.

---

## 17. Sécurité et exploitation

En production :

- HTTPS obligatoire ;
- `APP_DEBUG=false` ;
- secrets hors du dépôt ;
- cookies sécurisés ;
- validation stricte des uploads ;
- rate limiting ;
- autorisations via Policies/permissions ;
- aucun accès public direct aux audios ;
- sauvegarde DB + audios privés ;
- test de restauration ;
- scheduler Laravel actif pour la politique de rétention.

---

## 18. UX

Le site doit rester mobile-first, compréhensible par un public non technique et cohérent avec la palette de la landing page.

À tester :

- Chrome Android ;
- Safari iOS ;
- refus d'accès micro ;
- réseau lent ;
- interruption d'upload ;
- récit de plusieurs minutes ;
- réenregistrement.

---

## 19. Tests et CI

Les tests critiques doivent couvrir notamment :

- OTP et Google ;
- permissions ;
- confidentialité audio ;
- double validation ;
- segmentation ;
- exports et splits ;
- retrait d'une contribution ;
- suppression d'audio ;
- interdiction de gérer la contribution d'un autre utilisateur ;
- purge de rétention ;
- catalogue des prompts ;
- parole naturelle.

GitHub Actions exécute PHPUnit sur le collecteur.

---

## 20. Jalons

### A — Collecteur technique

**Largement réalisé.**

### B — Parole naturelle

**Implémentée techniquement, à tester sur le terrain.**

### C — Pilote réel

Objectif :

- 10 à 20 contributeurs réels ;
- 2 à 4 validateurs ;
- premiers prompts couverts par plusieurs locuteurs ;
- premiers récits naturels complets ;
- mesure séparée de la couverture Maka / Matya / Maya ;
- test réel du workflow de désaccord.

### D — Corpus v0.x

Objectif :

- couverture suffisante des 500 prompts ;
- au moins 200 phrases validées ;
- volume significatif de segments naturels ;
- statistiques qualité ;
- data card ;
- licence dataset ;
- splits figés.

### E — Baseline ML

Objectif : notebook Colab reproductible, baseline dictionnaire/mémoire de traduction, premier modèle, métriques, évaluation humaine et rapport d'erreurs.

### F — Traducteur expérimental

Français → San sur domaine limité avec variété clairement affichée lorsque connue, signalement/correction et version du modèle.

### G — Apprentissage

Vocabulaire, phrases, écoute, répétition, association mot-image, remise en ordre, traduction guidée et progression.

---

## 21. Décisions encore ouvertes

Les décisions encore réellement ouvertes concernent surtout :

- recrutement de validateurs réels ;
- organisation des relais / zones de collecte afin de couvrir les trois variétés ;
- conventions détaillées de segmentation de parole naturelle ;
- arbitrage final en cas de désaccord durable entre validateurs ;
- sauvegardes de production et procédure de restauration ;
- licence du futur dataset ;
- inventaire complet des ressources tierces et de leurs droits ;
- seuil de qualité minimum avant chaque expérimentation ML.

**Le choix d'une variété unique prioritaire n'est plus une décision ouverte : Maka, Matya et Maya restent toutes les trois dans le projet.**

---

## 22. Critères avant pilote public

- migrations et seeders fonctionnels ;
- tests verts ;
- audio testé sur plusieurs téléphones ;
- Google OAuth et Resend configurés ;
- sauvegardes en place ;
- scheduler de rétention actif ;
- 500 prompts et 40 sujets naturels relus ;
- validateurs identifiés ;
- consentement 1.1 publié ;
- procédure de retrait fonctionnelle ;
- organisation pratique de la collecte définie.

---

## 23. Critères avant expérimentation ML sérieuse

Ne pas considérer un entraînement comme une baseline sérieuse tant que :

- un corpus validé n'existe pas ;
- les variétés sont étiquetées ;
- la couverture de chaque variété est mesurée ;
- le dataset est versionné ;
- les splits sont stables ;
- les consentements autorisent l'usage ;
- les données retirées sont exclues ;
- les données test sont séparées avant augmentation ;
- un jeu d'évaluation humaine est conservé ;
- données naturelles et élicitées restent distinguées.

---

## 24. Documentation associée

- [`README.md`](README.md) ;
- [`docs/VISION.md`](docs/VISION.md) ;
- [`docs/ROADMAP.md`](docs/ROADMAP.md) ;
- [`docs/DIALECTS.md`](docs/DIALECTS.md) ;
- [`docs/DATA_GOVERNANCE.md`](docs/DATA_GOVERNANCE.md) ;
- [`docs/DATA_SCHEMA.md`](docs/DATA_SCHEMA.md) ;
- [`CONTRIBUTING.md`](CONTRIBUTING.md) ;
- [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md).

---

## 25. Principe final

La réussite de Langue SAN ne sera pas mesurée uniquement par le nombre de phrases collectées ou par un score automatique.

Le projet doit préserver simultanément :

- fidélité linguistique ;
- diversité des variétés ;
- naturalité des usages ;
- traçabilité ;
- consentement et possibilité de retrait ;
- qualité de validation ;
- utilité pour les communautés.

**Collecter mieux avant d'entraîner plus gros.**
