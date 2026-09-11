# Cahier des charges — Langue SAN

**Projet :** Langue SAN  
**Version :** 0.3  
**Date de mise à jour :** 11 septembre 2026  
**Statut :** collecteur MVP avancé — préparation du pilote terrain  
**Dépôt :** `KGS-L/langue-san`

---

## 1. Objet du document

Ce document définit le périmètre fonctionnel, technique, linguistique et de gouvernance du projet **Langue SAN**.

Il sert de référence commune pour :

- le collecteur web ;
- le back-office de transcription et validation ;
- la constitution progressive des corpus ;
- la future partie Machine Learning ;
- le futur traducteur Français ↔ San ;
- la future application d'apprentissage.

Le cahier des charges doit évoluer avec les retours des locuteurs, validateurs, linguistes et membres du projet.

---

## 2. Contexte et vision

Les variétés du San sont encore faiblement représentées dans les outils numériques modernes. Le projet vise à construire des ressources de qualité à partir de données réellement produites ou validées par les locuteurs.

Le projet ne doit pas :

- inventer des formes San pour compléter artificiellement le corpus ;
- présenter comme officielles des traductions non validées ;
- mélanger silencieusement différentes variétés ;
- considérer une localité comme une preuve automatique de variété ;
- publier des audios ou données privées sans autorisation ;
- intégrer automatiquement des ressources tierces sans vérifier leurs droits d'utilisation.

Le principe directeur est :

> **Documenter la langue existante, préserver sa diversité, valider humainement les données et seulement ensuite construire des outils IA.**

---

## 3. Objectifs du projet

### 3.1 Objectif principal

Construire un corpus Français ↔ San propre, traçable, validé et exploitable pour :

- la documentation linguistique ;
- la traduction ;
- la recherche ;
- l'apprentissage ;
- le traitement automatique du langage ;
- la valorisation numérique du San.

### 3.2 Objectifs intermédiaires

Le projet doit permettre de :

- collecter du vocabulaire et des phrases Français → San ;
- collecter de la parole naturelle San → San ;
- stocker des audios privés ;
- transcrire les audios ;
- segmenter les récits longs ;
- traduire en français les segments de parole naturelle ;
- identifier et valider la variété linguistique ;
- obtenir au moins deux validations indépendantes lorsque possible ;
- produire des datasets versionnés ;
- conserver des splits train / validation / test stables ;
- préparer une baseline ML reproductible sur Google Colab ;
- construire à terme un traducteur puis une application d'apprentissage.

---

## 4. Périmètre fonctionnel actuel

Le périmètre MVP couvre quatre espaces principaux :

1. site public ;
2. espace contributeur ;
3. collecte linguistique ;
4. back-office de modération et préparation du corpus.

La partie ML et l'application pédagogique constituent les phases suivantes.

---

## 5. Variétés linguistiques

Le projet utilise « San » comme nom général, mais ne traite pas les données comme une seule variété homogène.

Les références de travail actuelles sont :

| Variété | ISO 639-3 | Usage dans le projet |
| --- | --- | --- |
| San Maka / San du Sud | `sbd` | variété distincte |
| San Matya | `stj` | variété distincte |
| San Maya | `sym` | variété distincte |

### 5.1 Stratégie côté utilisateur

Le public ne doit pas être obligé de connaître les termes « Maka », « Matya » ou « Maya ».

Le formulaire demande plutôt :

> **Dans quelle ville ou localité avez-vous principalement appris ou parlé le San ?**

Exemples :

- Toma ;
- Tougan ;
- autre localité.

### 5.2 Suggestions internes

Le référentiel peut conserver des suggestions documentées :

- Toma → San Maka / `sbd` ;
- Tougan → San Matya / `stj`.

Ces suggestions doivent contenir leur provenance documentaire et sont seulement une aide au travail du validateur.

La règle est :

```text
localité déclarée
       ↓
suggestion interne éventuelle
       ↓
validation humaine
       ↓
variété validée
```

La variété finale n'est jamais déduite automatiquement par le logiciel.

### 5.3 Pilote

L'infrastructure supporte plusieurs variétés, mais la première campagne terrain peut se concentrer sur **une variété / communauté pilote** afin de maximiser la cohérence des premiers jeux de données.

Cette décision reste à confirmer avec les personnes de terrain et les validateurs.

---

## 6. Acteurs et rôles

### 6.1 Visiteur

Peut :

- consulter la landing page ;
- consulter les pages de confidentialité, politique de contribution et gouvernance ;
- découvrir la communauté ;
- commencer à contribuer sans compte ;
- consulter la page « Rejoindre le projet ».

### 6.2 Contributeur

Peut :

- contribuer sans mot de passe ;
- se connecter avec Google ;
- se connecter par code OTP envoyé par email ;
- compléter son profil ;
- voir son tableau de bord ;
- consulter l'historique de ses contributions ;
- reprendre les futures campagnes ;
- rendre son profil public uniquement par opt-in ;
- candidater pour rejoindre l'équipe projet.

### 6.3 Transcripteur

Peut :

- accéder au back-office avec les permissions nécessaires ;
- écouter les audios privés ;
- saisir ou corriger la transcription San ;
- préparer les récits naturels pour segmentation.

Le rôle `transcriber` ne doit pas donner automatiquement les droits d'administration.

### 6.4 Validateur

Peut :

- relire les transcriptions ;
- confirmer ou corriger le texte San ;
- confirmer la variété ;
- approuver ou rejeter une contribution ;
- participer à la double validation.

Un même utilisateur ne peut pas effectuer les deux validations de la même contribution.

### 6.5 Modérateur

Peut gérer les opérations linguistiques et les éléments de référence qui lui sont autorisés, mais ne doit pas disposer automatiquement des droits réservés à l'administrateur, notamment la gestion des modérateurs et l'export final du dataset.

### 6.6 Administrateur

Il n'y a qu'un administrateur principal.

Il peut notamment :

- gérer les modérateurs ;
- gérer les permissions projet ;
- gérer les référentiels ;
- consulter les statistiques globales ;
- examiner les candidatures projet ;
- gérer les prompts ;
- télécharger les exports dataset.

---

## 7. Authentification

### 7.1 Contributeurs

Les contributeurs sont passwordless.

Méthodes :

- Google OAuth ;
- email + OTP de 8 chiffres.

Contraintes :

- l'email est normalisé en minuscules ;
- Google et OTP doivent retrouver le même compte lorsque l'adresse email vérifiée est identique ;
- un OTP expire après une durée limitée ;
- le code est stocké sous forme hashée ;
- le nombre d'essais est limité ;
- les demandes répétées sont soumises à rate limiting.

### 7.2 Staff

Admin et modérateurs utilisent email + mot de passe.

Les comptes staff ne doivent pas utiliser le parcours public OTP / Google comme mécanisme principal d'administration.

---

## 8. Profil contributeur

### 8.1 Contexte linguistique

Le profil de collecte doit contenir au minimum :

- localité principale d'apprentissage / usage ;
- autre localité si nécessaire ;
- niveau de pratique ;
- capacité à écrire le San.

Le niveau est exprimé de manière compréhensible par le public :

- langue maternelle / depuis l'enfance ;
- courant ;
- intermédiaire ;
- notions de base.

### 8.2 Profil utilisateur

Après authentification, le contributeur complète :

- nom ;
- pays de résidence ;
- tranche d'âge ;
- profession ;
- organisation facultative.

### 8.3 Profil public

Le profil public est désactivé par défaut.

Seules des données sûres peuvent être affichées publiquement :

- nom public ;
- profession ;
- pays ;
- bio ;
- liens GitHub / LinkedIn facultatifs ;
- badge membre du projet.

Ne doivent pas être exposés publiquement :

- email ;
- tranche d'âge ;
- localité linguistique exacte ;
- niveau de maîtrise ;
- audio ;
- consentements internes ;
- identifiants techniques.

---

## 9. Mode de collecte A — Français → San

### 9.1 Objectif

Construire des correspondances ciblées entre français et San pour le vocabulaire, les expressions et les phrases courantes.

### 9.2 Catalogue

Le catalogue actuel contient exactement :

```text
25 thèmes
× 20 prompts par thème
= 500 prompts
```

Répartition :

```text
350 mots / expressions
150 phrases
```

Chaque thème contient :

- 14 mots ou expressions ;
- 6 phrases.

### 9.3 Session standard

Une session standard contient environ :

```text
7 mots / expressions
+
3 phrases
=
10 prompts
```

### 9.4 Sélection intelligente

Le moteur doit :

- choisir uniquement des prompts actifs ;
- éviter les prompts déjà traités par le même contributeur ;
- favoriser les prompts ayant le moins de contributions utilisables ;
- prendre en compte la priorité ;
- randomiser les ex æquo.

La cible initiale par prompt est de **3 contributions indépendantes** lorsque cela est possible.

### 9.5 Réponse

Pour chaque prompt, le contributeur peut :

- écrire la réponse San ;
- enregistrer sa voix ;
- faire les deux ;
- passer la question.

L'audio est recommandé mais n'est pas obligatoire si une réponse écrite est fournie.

---

## 10. Mode de collecte B — Parole naturelle San → San

### 10.1 Objectif

Éviter de constituer un corpus où le San est systématiquement produit comme traduction d'une structure française.

Le projet collecte donc également des récits, descriptions, histoires, proverbes et explications produites directement en San.

### 10.2 Banque de sujets

Le catalogue contient actuellement **40 sujets** de parole naturelle couvrant notamment :

- famille ;
- mariage ;
- cérémonies ;
- funérailles ;
- marché ;
- nourriture ;
- agriculture ;
- élevage ;
- saisons ;
- déplacements ;
- école ;
- travail ;
- santé comme récit de pratique sociale, sans conseil médical ;
- histoire du village ;
- entraide ;
- danse et traditions ;
- proverbes ;
- contes ;
- conseils des anciens ;
- réconciliation ;
- hospitalité ;
- environnement ;
- transmission du San ;
- sujet libre.

### 10.3 Consigne

La consigne française est uniquement un déclencheur.

Le site doit afficher clairement :

> **Ne traduisez pas la consigne française mot à mot. Parlez naturellement en San avec vos propres mots.**

### 10.4 Audio

Pour les récits naturels :

- l'audio est obligatoire ;
- durée recommandée : 2 à 5 minutes ;
- limite fonctionnelle prévue : jusqu'à 10 minutes ;
- taille serveur autorisée : jusqu'à environ 50 Mo côté validation Laravel ;
- l'utilisateur doit pouvoir se réécouter ;
- il doit pouvoir recommencer avant l'envoi.

Le serveur de production doit être configuré avec des limites PHP / proxy supérieures à cette taille.

### 10.5 Pipeline

```text
Sujet de discussion
      ↓
Audio naturel San
      ↓
Transcription San complète
      ↓
Segmentation
      ↓
Traduction française de chaque segment
      ↓
Validation de la contribution
      ↓
Corpus naturel San ↔ Français
```

### 10.6 Segments

Chaque segment peut contenir :

- position ;
- `start_ms` ;
- `end_ms` ;
- texte San ;
- traduction française ;
- variété éventuelle ;
- auteur de la création / dernière modification.

Les timestamps permettent une future exploitation alignée avec l'audio.

### 10.7 Règle de validation

Un récit naturel ne doit pas être considéré validable pour le dataset tant que :

- sa transcription San n'existe pas ;
- au moins un segment n'a pas été créé ;
- les segments utiles n'ont pas de texte San ;
- les segments utiles n'ont pas de traduction française.

---

## 11. Statuts des contributions

Le workflow utilise :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Signification :

- `pending` : contribution reçue, à transcrire ;
- `transcribed` : transcription disponible, à valider ;
- `validated_once` : une première validation existe ;
- `validated_twice` : deux validations ne concordent pas totalement, arbitrage nécessaire ;
- `approved` : contribution approuvée ;
- `rejected` : contribution rejetée.

Côté contributeur, des libellés simplifiés sont utilisés :

- Reçue ;
- En vérification ;
- Validée ;
- Non retenue.

Les notes internes des validateurs ne sont jamais affichées au contributeur.

---

## 12. Double validation

Une contribution est automatiquement approuvable lorsque deux validations indépendantes concordent sur les éléments nécessaires, notamment :

- décision ;
- variété ;
- transcription / correction effective.

Deux rejets indépendants peuvent mener au statut `rejected`.

En cas de désaccord :

```text
validated_twice
```

Le projet doit définir avant la grande collecte :

- qui peut arbitrer ;
- comment documenter l'arbitrage ;
- comment traiter un désaccord durable ;
- comment gérer une erreur découverte après approbation.

---

## 13. Back-office

Le back-office doit couvrir :

- dashboard statistiques ;
- catégories ;
- prompts ;
- localités ;
- variétés ;
- contributions ;
- file « À transcrire » ;
- file « À valider » ;
- lecture sécurisée des audios ;
- segmentation des récits naturels ;
- candidatures projet ;
- utilisateurs staff ;
- exports dataset.

### 13.1 Dashboard

Les indicateurs utiles comprennent notamment :

- contributeurs ;
- nombre total de contributions ;
- contributions récentes ;
- éléments à transcrire ;
- éléments à valider ;
- approuvées ;
- rejetées ;
- taux d'approbation ;
- candidatures projet ;
- couverture des prompts ;
- prompts sous-couverts ;
- répartition par localité ;
- état de configuration Google / email.

---

## 14. Candidature et communauté projet

Le site permet à un contributeur authentifié de candidater pour rejoindre le projet.

La candidature peut contenir :

- domaines de contribution ;
- expérience ;
- motivation ;
- disponibilité ;
- portfolio ;
- lien éventuel avec la langue / communauté.

L'approbation d'une candidature :

- crée une adhésion projet ;
- ne donne pas automatiquement les permissions `admin`, `moderator`, `transcriber` ou `validator`.

L'administrateur attribue ensuite les fonctions nécessaires séparément.

---

## 15. Consentement

Le consentement doit être versionné.

Il doit expliquer au minimum :

- stockage des réponses ;
- transcription ;
- validation ;
- constitution d'un corpus ;
- éventuelle utilisation pour entraîner / évaluer des modèles ;
- politique de publication des audios.

La publication publique de l'audio n'est jamais implicite.

Une procédure explicite de correction, retrait ou suppression doit être finalisée avant une collecte publique à grande échelle.

---

## 16. Gouvernance des données

### 16.1 Séparation code / données

Le dépôt de code est sous Apache-2.0.

Cela ne signifie pas que :

- les audios ;
- les traductions ;
- les datasets ;
- les métadonnées ;
- les ressources tierces

sont automatiquement sous la même licence.

### 16.2 Données privées

Ne jamais committer sur GitHub :

- audios bruts privés ;
- données personnelles ;
- datasets privés complets ;
- secrets ;
- clés API.

### 16.3 Ressources tierces

Avant réutilisation d'un dictionnaire, livre, application ou corpus existant, vérifier explicitement le droit de :

- consulter ;
- numériser ;
- transformer ;
- entraîner ;
- republier ;
- exploiter commercialement le cas échéant.

### 16.4 Sauvegardes

Avant collecte à grande échelle, mettre en place :

- sauvegarde DB ;
- sauvegarde privée des audios ;
- chiffrement ou stockage protégé ;
- contrôle d'accès ;
- test de restauration.

---

## 17. Exports datasets

### 17.1 Export Français → San

Seules les contributions éligibles sont exportées.

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

### 17.2 Export parole naturelle San → Français

Chaque segment devient une ligne :

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

### 17.3 Source ID

Le `source_id` est pseudonymisé à partir d'un secret stable de production.

Le secret `DATASET_SOURCE_SALT` ne doit pas être changé arbitrairement après publication d'un corpus versionné.

### 17.4 Splits

Le split est déterministe :

```text
train
validation
test
```

Toutes les données dérivées d'une même source doivent conserver le même split.

Exemple : les 40 segments d'un même récit naturel doivent tous rester dans `train`, ou tous dans `validation`, ou tous dans `test`.

Cette règle protège l'évaluation contre les fuites de contenu.

---

## 18. Architecture applicative

Architecture applicative cible :

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

Éléments transverses :

- Policies ;
- Enums ;
- rôles / permissions Spatie ;
- services métier ;
- tests Feature.

Les règles métier ne doivent pas être dispersées dans les contrôleurs.

---

## 19. Stack technique

### 19.1 Collecteur actuel

- PHP 8.3+ ;
- Laravel 13 ;
- Blade ;
- MySQL ;
- Bootstrap ;
- NiceAdmin pour l'administration ;
- Spatie Laravel Permission ;
- Laravel Fortify ;
- Google OAuth ;
- OTP email ;
- Resend pour la production email ;
- stockage privé Laravel pour les audios ;
- PHPUnit ;
- GitHub Actions.

### 19.2 Machine Learning futur

- Python ;
- PyTorch ;
- Hugging Face Transformers ;
- Hugging Face Datasets ;
- Google Colab ;
- notebooks reproductibles ;
- métriques automatiques ;
- évaluation humaine.

Le modèle final n'est pas figé. Le projet doit comparer une baseline simple et des modèles multilingues / byte-level adaptés au faible volume de données.

---

## 20. Machine Learning — stratégie

Le projet ne doit pas commencer directement par un gros modèle.

Ordre recommandé :

```text
Corpus validé
   ↓
normalisation
   ↓
baseline dictionnaire / mémoire de traduction
   ↓
modèle simple ou byte-level
   ↓
évaluation automatique
   ↓
évaluation humaine
   ↓
analyse des erreurs
   ↓
itération
```

### 20.1 Première direction

La première direction expérimentale est :

```text
Français → San
```

sur un domaine limité et une variété clairement identifiée.

### 20.2 Bidirectionnel

La direction :

```text
San → Français
```

sera ajoutée progressivement, notamment grâce aux segments de parole naturelle.

### 20.3 Protection contre la contamination

Les variantes inversées ou augmentées d'une même paire doivent conserver :

- le même `source_id` ;
- le même split.

---

## 21. Future application d'apprentissage

Après une base linguistique suffisamment validée, le projet pourra construire une application mobile / web inspirée des principes pédagogiques de Duolingo, mais adaptée aux réalités du San.

Fonctionnalités envisagées :

- vocabulaire ;
- phrases du quotidien ;
- écoute ;
- répétition ;
- association mot-image ;
- remise en ordre ;
- traduction guidée ;
- exercices audio ;
- progression par niveau ;
- leçons regroupées par thème ;
- affichage clair de la variété ciblée.

Un premier MVP pédagogique pourra commencer avec environ 20 à 40 leçons, après validation du corpus correspondant.

---

## 22. Exigences UX

Le site public doit :

- être mobile-first ;
- rester simple pour des utilisateurs non techniques ;
- ne pas exposer le vocabulaire linguistique interne lorsque cela n'aide pas le contributeur ;
- utiliser la palette visuelle de la landing page ;
- éviter les boutons Bootstrap bleus par défaut ;
- conserver une cohérence entre landing, contribution, espace contributeur et pages légales ;
- être utilisable sur connexion lente autant que possible ;
- permettre de réessayer après un échec réseau ;
- prévenir clairement avant l'utilisation du microphone ;
- proposer une alternative texte pour la collecte ciblée.

---

## 23. Exigences non fonctionnelles

### 23.1 Sécurité

En production :

- HTTPS obligatoire ;
- `APP_DEBUG=false` ;
- secrets hors du dépôt ;
- cookies sécurisés ;
- validation stricte des fichiers audio ;
- rate limiting ;
- autorisations contrôlées par Policies / permissions ;
- audio servi uniquement via routes autorisées ;
- aucun accès public direct au stockage privé.

### 23.2 Performance

Le système doit rester utilisable avec :

- plusieurs centaines puis milliers de contributions ;
- pagination sur les listes admin ;
- exports chunkés ;
- requêtes optimisées pour les files de modération.

### 23.3 Compatibilité

Tester au minimum :

- Chrome desktop ;
- Chrome Android ;
- Safari iOS ;
- autorisation refusée du microphone ;
- réseau lent ;
- envoi audio interrompu ;
- reprise / nouvel enregistrement.

---

## 24. Emails transactionnels

Le projet prévoit Resend en production.

Emails principaux :

- code OTP ;
- invitation modérateur ;
- décision de candidature projet.

Les emails sont en français tant qu'une traduction San n'a pas été validée par des personnes compétentes.

---

## 25. Tests et CI

Les fonctionnalités critiques doivent être couvertes par tests Feature.

Couverture existante / attendue :

- permissions back-office ;
- OTP ;
- Google et OTP vers le même compte ;
- confidentialité des audios ;
- rôles spécialisés ;
- double validation ;
- export dataset ;
- protection des splits ;
- catalogue de prompts ;
- parole naturelle ;
- segmentation ;
- autorisations de validation.

GitHub Actions doit lancer automatiquement PHPUnit sur les changements du collecteur.

---

## 26. Indicateurs de progression

### 26.1 Collecte ciblée

Suivre :

- prompts actifs ;
- prompts ayant 0, 1, 2, 3+ contributions ;
- taux d'approbation ;
- taux de rejet ;
- répartition par thème ;
- répartition par localité ;
- répartition par variété validée ;
- contributeurs uniques.

### 26.2 Parole naturelle

Suivre :

- nombre de récits ;
- durée totale audio ;
- récits transcrits ;
- récits segmentés ;
- segments traduits ;
- segments validés ;
- durée moyenne par récit ;
- couverture des sujets.

### 26.3 Qualité

Suivre :

- taux de désaccord entre validateurs ;
- corrections après première validation ;
- contributions sans variété confirmée ;
- erreurs fréquentes de transcription ;
- prompts français jugés ambigus.

---

## 27. Jalons

### Jalon A — Collecteur technique

**Statut : largement réalisé.**

Comprend :

- collecte ciblée ;
- comptes ;
- audio ;
- back-office ;
- transcription ;
- validation ;
- export ;
- rôles ;
- CI.

### Jalon B — Parole naturelle

**Statut : implémenté techniquement, à tester sur le terrain.**

Comprend :

- sujets naturels ;
- enregistrement long ;
- transcription ;
- segmentation ;
- traduction ;
- export San → Français.

### Jalon C — Pilote réel

Objectif :

- 10 à 20 contributeurs réels ;
- 2 à 4 validateurs ;
- plusieurs localités / contexte maîtrisé ;
- premiers prompts couverts par plusieurs locuteurs ;
- premiers récits naturels complets ;
- évaluation du workflow de désaccord.

### Jalon D — Corpus v0.x

Objectif :

- couverture suffisante des 500 prompts ;
- au moins 200 phrases simples validées ;
- volume significatif de segments naturels ;
- data card ;
- licence dataset ;
- statistiques qualité ;
- splits figés.

### Jalon E — Baseline ML

Objectif :

- notebook Google Colab ;
- baseline dictionnaire ;
- premier modèle ;
- métriques ;
- évaluation humaine ;
- rapport d'erreurs.

### Jalon F — Traducteur expérimental

Objectif :

- Français → San ;
- domaine limité ;
- variété affichée ;
- avertissements ;
- corrections utilisateur ;
- version du modèle.

### Jalon G — Apprentissage

Objectif :

- premières leçons ;
- audio ;
- exercices ;
- progression ;
- test avec communauté pilote.

---

## 28. Décisions encore ouvertes

Avant l'ouverture publique à grande échelle, il faut encore décider ou finaliser :

- variété prioritaire du premier pilote ;
- communauté / localité pilote ;
- liste des validateurs réels ;
- règles d'arbitrage linguistique ;
- conventions pratiques de segmentation de parole naturelle ;
- durée de conservation des audios ;
- procédure formelle de retrait et correction ;
- politique de sauvegarde ;
- licence du futur dataset ;
- inventaire complet des ressources tierces et de leurs droits ;
- seuil de qualité minimum avant entraînement ML.

---

## 29. Critères de sortie avant pilote public

Le système peut être considéré prêt pour un petit pilote lorsque :

- les migrations s'exécutent sur une base vierge ;
- les seeders sont idempotents ;
- les tests passent ;
- l'audio fonctionne sur plusieurs téléphones ;
- les pages publiques sont cohérentes visuellement ;
- Google OAuth et Resend sont configurés en production ;
- les sauvegardes sont en place ;
- les audios restent privés ;
- les 500 prompts ont été relus ;
- les sujets de parole naturelle ont été relus ;
- les validateurs réels sont identifiés ;
- le texte de consentement est validé ;
- la procédure de retrait est documentée ;
- la variété / zone pilote est choisie.

---

## 30. Critères de sortie avant première expérimentation ML

Ne pas démarrer une expérimentation présentée comme sérieuse tant que :

- un corpus validé n'existe pas ;
- les variétés sont identifiées ;
- le dataset est versionné ;
- les splits sont stables ;
- les consentements autorisent l'usage prévu ;
- les données test sont séparées avant toute augmentation inverse ;
- un jeu de test humain est conservé ;
- la qualité de la transcription est jugée acceptable ;
- les données naturelles et élicitées sont distinguées.

---

## 31. Documentation associée

- [`README.md`](README.md) — présentation générale ;
- [`docs/VISION.md`](docs/VISION.md) — vision ;
- [`docs/ROADMAP.md`](docs/ROADMAP.md) — feuille de route ;
- [`docs/DIALECTS.md`](docs/DIALECTS.md) — variétés ;
- [`docs/DATA_GOVERNANCE.md`](docs/DATA_GOVERNANCE.md) — gouvernance ;
- [`docs/DATA_SCHEMA.md`](docs/DATA_SCHEMA.md) — schéma de données ;
- [`CONTRIBUTING.md`](CONTRIBUTING.md) — contribution au projet ;
- [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md) — règles communautaires.

---

## 32. Principe final

La réussite de Langue SAN ne sera pas mesurée uniquement par le nombre de phrases collectées ou par un score automatique de traduction.

Le projet doit préserver simultanément :

- la fidélité linguistique ;
- la diversité des variétés ;
- la naturalité des usages ;
- la traçabilité ;
- le consentement ;
- la qualité de validation ;
- l'utilité pour les communautés.

Le corpus doit rester la fondation du projet : **collecter mieux avant d'entraîner plus gros**.
