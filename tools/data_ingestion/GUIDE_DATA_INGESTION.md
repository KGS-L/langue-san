# Guide Data Ingestion — Langue SAN

Ce document est le guide de référence pour comprendre, exécuter, contrôler et faire évoluer toute la partie **data ingestion** du projet Langue SAN.

Il est volontairement pédagogique : l'objectif est qu'une personne revenant sur le projet plusieurs semaines ou plusieurs mois plus tard puisse comprendre :

- ce que fait le pipeline ;
- pourquoi chaque étape existe ;
- quelles commandes lancer ;
- quels fichiers sont produits ;
- ce qui est considéré comme réussi ou échoué ;
- ce qui est techniquement validé ;
- ce qui nécessite encore une validation linguistique humaine ;
- quand une donnée peut, ou non, être utilisée pour le Machine Learning.

---

## 1. Objectif de `data_ingestion`

`tools/data_ingestion/` sert à acquérir et préparer des **ressources linguistiques externes** trouvées sur Internet ou publiées sous forme de datasets.

Exemples :

- ASJP ;
- datasets Hugging Face ;
- corpus publics ;
- dictionnaires ou lexiques lorsque leur licence le permet ;
- ressources téléchargées via API ou format structuré.

Ce dossier ne remplace pas le collecteur terrain Laravel.

```text
apps/collector/
    = contributions de vrais locuteurs

 tools/data_ingestion/
    = ressources externes déjà existantes

 data/
    = données de travail, schémas et futurs datasets publiables

 ml/
    = expériences, entraînement et évaluation ML
```

---

## 2. Modèle mental du pipeline

Le pipeline complet doit toujours être compris comme une succession d'étapes contrôlées :

```text
Découverte d'une source
        ↓
Vérification licence / droits / accès
        ↓
Collecte technique
        ↓
Donnée brute RAW
        ↓
Normalisation
        ↓
Contrôle qualité technique
        ↓
Validation linguistique humaine si nécessaire
        ↓
Donnée approuvée
        ↓
Dataset versionné
        ↓
Machine Learning / traduction / apprentissage
```

Une erreur fréquente serait de penser :

```text
"J'ai téléchargé un mot San depuis Internet"
        =
"J'ai une traduction San validée"
```

C'est faux.

La collecte technique et la validation linguistique sont deux choses différentes.

---

## 3. Les niveaux de données

### 3.1 RAW — donnée brute

La donnée est stockée telle qu'elle provient de la source.

Exemple :

```text
data/raw/asjp/asjp_v21_san_wordlists.json
```

Règles :

- ne pas modifier manuellement ;
- conserver la provenance ;
- conserver la licence ;
- conserver les identifiants de la source ;
- ne pas considérer la forme comme validée ;
- ne pas commiter les données RAW dans Git.

### 3.2 PROCESSED — donnée normalisée de travail

La structure est convertie dans un format commun au projet.

Exemple :

```text
data/processed/asjp/
├── asjp_v21_san_normalized.json
├── asjp_v21_san_normalized.csv
└── asjp_v21_report.json
```

La normalisation signifie :

- noms de champs cohérents ;
- variété explicitement identifiée ;
- source et licence préservées ;
- concepts structurés ;
- métadonnées prêtes à être inspectées.

Elle ne signifie PAS :

- orthographe San validée ;
- traduction française validée ;
- donnée prête pour l'entraînement.

### 3.3 VALIDATED — donnée linguistiquement validée

Une donnée devient linguistiquement validée après contrôle par le workflow défini par le projet : locuteurs, transcripteurs, validateurs ou spécialistes compétents.

### 3.4 TRAINING APPROVED — donnée autorisée pour le ML

Même une donnée linguistiquement correcte ne doit être intégrée à un entraînement que si :

- la licence le permet ;
- sa variété est connue ;
- sa provenance est conservée ;
- son statut de validation le permet ;
- elle respecte les règles du futur dataset et de gouvernance.

---

## 4. Statuts utilisés

Pour suivre une source, utiliser les statuts suivants :

| Statut | Signification |
|---|---|
| `discovered` | source trouvée mais non étudiée |
| `rights_review_required` | licence / droits à vérifier |
| `approved_for_ingestion` | droits suffisants pour commencer l'acquisition |
| `collection_success` | récupération technique réussie |
| `collection_failed` | acquisition échouée |
| `normalization_success` | conversion vers le schéma de travail réussie |
| `qa_passed` | contrôles techniques satisfaisants |
| `external_unverified` | donnée externe, non validée linguistiquement par Langue SAN |
| `linguistic_validation_required` | validation humaine nécessaire |
| `training_approved` | donnée explicitement autorisée pour usage ML |
| `blocked` | source volontairement bloquée |
| `rejected` | source ou donnée rejetée |

Important :

```text
collection_success != linguistic_validation

normalization_success != training_approved
```

---

## 5. Structure du dossier

```text
tools/data_ingestion/
├── config/
│   ├── languages.yaml
│   └── sources.yaml
│
├── collectors/
│   ├── __init__.py
│   └── asjp.py
│
├── processors/
│   ├── __init__.py
│   └── normalize.py
│
├── tests/
│   ├── test_asjp.py
│   ├── test_config.py
│   └── test_normalize.py
│
├── GUIDE_DATA_INGESTION.md
├── README.md
└── requirements.txt
```

### `config/`

Contient ce que le pipeline doit connaître sans le coder en dur partout.

`languages.yaml` définit notamment :

```text
sbd → San Maka / San du Sud
stj → San Matya
sym → San Maya
```

`sources.yaml` indique quelles sources sont autorisées ou bloquées.

### `collectors/`

Un collector récupère une source externe et produit du RAW.

Exemple :

```text
collectors/asjp.py
```

### `processors/`

Les processors transforment la donnée RAW sans prétendre la corriger linguistiquement.

### `tests/`

Les tests vérifient que nos scripts respectent les règles définies.

---

## 6. Installation locale

Depuis :

```text
~/Bureau/langue-san/tools/data_ingestion
```

Créer l'environnement virtuel une seule fois :

```bash
python3 -m venv .venv
```

Activer l'environnement :

```bash
source .venv/bin/activate
```

Le terminal doit commencer par :

```text
(.venv)
```

Installer les dépendances :

```bash
pip install -r requirements.txt
```

Lors des prochaines sessions, il suffit généralement de faire :

```bash
cd ~/Bureau/langue-san/tools/data_ingestion
source .venv/bin/activate
```

---

## 7. Toujours lancer les tests avant une ingestion importante

Commande :

```bash
pytest tests
```

Résultat attendu :

```text
... passed
```

Un test en échec signifie qu'il faut arrêter le pipeline et comprendre le problème avant de produire de nouvelles données.

---

# 8. Source ASJP — procédure complète

ASJP est la première source externe active du projet.

## 8.1 Pourquoi ASJP ?

ASJP fournit des listes lexicales structurées et permet d'identifier les trois codes ISO utilisés par notre projet :

```text
sbd → San Maka / Southern Samo
stj → San Matya
sym → San Maya
```

Le pipeline utilise la version ASJP v21 sous forme CLDF structurée.

Licence enregistrée :

```text
CC-BY-4.0
```

Statut de la source :

```text
approved_for_ingestion
```

---

## 8.2 Étape A — collecte

Commande :

```bash
python collectors/asjp.py
```

Résultat attendu :

```text
/home/.../langue-san/data/raw/asjp/asjp_v21_san_wordlists.json
```

Un résultat affichant ce chemin signifie que le téléchargement et l'extraction se sont terminés correctement.

### Résultat réellement obtenu le 11 septembre 2026

```text
341 entrées
```

Répartition :

| ISO | Variété | Entrées | Concepts uniques | Formes uniques |
|---|---|---:|---:|---:|
| `sbd` | Maka / Southern Samo | 37 | 34 | 36 |
| `stj` | Matya | 129 | 78 | 111 |
| `sym` | Maya | 175 | 88 | 150 |
| **Total** | | **341** | | |

Listes lexicales rencontrées :

```text
sbd
├── SOUTHERN_SAMO       33 entrées
└── SOUTHERN_SAMO_SAN    4 entrées

stj
├── SAMO_MATYA          34 entrées
└── SAMO_MATYA_2        95 entrées

sym
├── MAYA_SAMO           73 entrées
└── SAMO_MAYA           102 entrées
```

### Statut

```text
Collecte ASJP : VALIDÉE TECHNIQUEMENT
```

Cela signifie que la récupération fonctionne et que les trois variétés attendues sont présentes.

Cela ne signifie pas que les 341 formes sont des orthographes San validées.

---

## 8.3 Comprendre les formes ASJP

Exemples trouvés :

```text
mu
5ini
yErE
Ci
ky~E
jE*
m3
```

Il ne faut PAS faire des remplacements automatiques comme :

```text
5 → ?
E → é
C → c
* → rien
```

sans règle linguistique documentée.

Ces caractères appartiennent potentiellement au système de notation utilisé par la source.

Le pipeline doit donc conserver :

```text
source_form = valeur ASJP originale
source_notation = asjp
standard_san = null
```

---

## 8.4 Étape B — normalisation

Après la collecte :

```bash
python processors/normalize.py
```

Résultat attendu :

```text
Entrées normalisées : 341
- sbd (maka): 37 entrées, 34 concepts, 36 formes
- stj (matya): 129 entrées, 78 concepts, 111 formes
- sym (maya): 175 entrées, 88 concepts, 150 formes
```

Puis trois fichiers :

```text
data/processed/asjp/
├── asjp_v21_san_normalized.json
├── asjp_v21_san_normalized.csv
└── asjp_v21_report.json
```

### JSON normalisé

Exemple conceptuel :

```json
{
  "source": "ASJP",
  "source_version": "v21",
  "variety": "matya",
  "iso_639_3": "stj",
  "source_wordlist": "SAMO_MATYA_2",
  "concept_source": "*water",
  "concept_normalized": "water",
  "concept_fr": null,
  "source_form": "mu",
  "source_notation": "asjp",
  "standard_san": null,
  "validation_status": "external_unverified"
}
```

Pourquoi `concept_fr` est `null` ?

Parce que le pipeline n'invente pas automatiquement une traduction française de contrôle.

Pourquoi `standard_san` est `null` ?

Parce que la forme ASJP n'est pas automatiquement considérée comme orthographe San validée.

---

## 8.5 Étape C — lire le rapport qualité

Fichier :

```text
data/processed/asjp/asjp_v21_report.json
```

Le rapport vérifie notamment :

- nombre total d'entrées ;
- nombre d'entrées par variété ;
- nombre de concepts distincts ;
- nombre de formes distinctes ;
- listes lexicales sources ;
- doublons exacts ;
- champs manquants ;
- concepts possédant plusieurs formes ;
- présence de marqueurs de notation ASJP ;
- statut d'approbation ML.

Valeur importante attendue :

```json
"training_approved": false
```

C'est volontaire.

---

## 8.6 Plusieurs formes pour un même concept

Exemple : le concept `one` peut apparaître avec plusieurs formes.

Ce n'est pas automatiquement une erreur.

Raisons possibles :

- plusieurs listes lexicales ;
- variantes locales ;
- variantes de transcription ;
- sources différentes ;
- contexte différent ;
- erreur historique dans une source.

Donc :

```text
NE PAS dédupliquer uniquement sur concept.
```

On conserve :

```text
iso_639_3
source_wordlist
concept
source_form
```

jusqu'à investigation.

---

# 9. Qu'est-ce qu'un échec ?

## Échec de collecte

Exemples :

```text
HTTP 404
HTTP 403
connexion impossible
format source changé
aucune entrée trouvée
```

Statut :

```text
collection_failed
```

Action : ne pas créer artificiellement de données pour compenser.

## Échec de structure

Exemple :

```text
le JSON ne contient plus "entries"
```

Action : vérifier si la source a changé de format.

## Échec de licence

Exemple : une ressource techniquement téléchargeable mais sans droit clair de réutilisation.

Action :

```text
rights_review_required
```

ou :

```text
blocked
```

Le fait qu'un fichier soit accessible publiquement ne signifie pas automatiquement qu'il peut être utilisé pour entraîner un modèle.

## Échec linguistique

Exemple : une entrée est techniquement correcte mais un validateur San indique qu'elle est incorrecte pour la variété concernée.

Action : conserver la provenance et marquer la donnée comme rejetée ou nécessitant révision. Ne jamais masquer silencieusement le problème.

---

# 10. Qu'est-ce qui est considéré comme validé ?

Nous avons plusieurs niveaux de validation.

## Validation 1 — technique

Questions :

- le téléchargement fonctionne-t-il ?
- le fichier est-il lisible ?
- les champs attendus existent-ils ?
- les codes ISO attendus sont-ils présents ?
- le nombre d'entrées est-il cohérent ?

ASJP : **oui pour la collecte actuelle**.

## Validation 2 — provenance et droits

Questions :

- source connue ?
- URL enregistrée ?
- version connue ?
- licence connue ?
- réutilisation compatible avec notre objectif ?

ASJP : autorisé pour ingestion selon la configuration actuelle.

## Validation 3 — qualité structurelle

Questions :

- pas de mélange silencieux Maka / Matya / Maya ?
- source_wordlist conservé ?
- doublons analysables ?
- valeurs manquantes identifiables ?

Cette étape est matérialisée par le normaliseur et le rapport QA.

## Validation 4 — linguistique

Questions :

- le mot correspond-il réellement au concept ?
- la variété est-elle correcte ?
- l'orthographe est-elle acceptable ?
- la forme est-elle naturelle ?

Cette validation ne peut pas être déduite uniquement par le script.

## Validation 5 — ML

Questions :

- données autorisées pour training ?
- qualité suffisante ?
- source trop biaisée ?
- split correct ?
- aucune fuite train/test ?

Une source peut donc être techniquement excellente mais toujours non autorisée pour l'entraînement.

---

# 11. Procédure pour ajouter une nouvelle source

Chaque nouvelle source doit suivre exactement ce processus.

### Étape 1 — découverte

Documenter :

```text
nom
URL
organisation
langues / variétés
format
volume approximatif
```

### Étape 2 — droits

Vérifier :

```text
licence
réutilisation
modification
publication
usage commercial éventuel
usage ML
attribution requise
```

### Étape 3 — configuration

Ajouter la source dans :

```text
config/sources.yaml
```

Ne pas l'activer tant que les droits ne sont pas suffisamment clairs.

### Étape 4 — collector

Créer :

```text
collectors/<source>.py
```

Il doit écrire sous :

```text
data/raw/<source>/
```

### Étape 5 — tests

Ajouter des tests sans dépendre d'Internet autant que possible.

### Étape 6 — première collecte réelle

Mesurer :

```text
nombre d'entrées
variétés trouvées
formats
champs manquants
erreurs
```

### Étape 7 — normalisation

Transformer vers un schéma commun en préservant la valeur source.

### Étape 8 — rapport qualité

Produire les statistiques et anomalies.

### Étape 9 — décision

Choisir :

```text
conserver comme référence
soumettre à validation humaine
utiliser pour enrichissement
utiliser pour ML
rejeter
bloquer
```

---

# 12. Ce qu'il ne faut jamais faire

Ne jamais :

- mélanger automatiquement `sbd`, `stj` et `sym` ;
- supprimer une forme seulement parce qu'une autre ressemble davantage à ce que l'on attend ;
- convertir une notation phonétique en orthographe standard sans règle validée ;
- inventer une traduction manquante et la marquer comme source originale ;
- effacer la provenance ;
- remplacer un fichier RAW manuellement ;
- entraîner un modèle avec une source dont les droits ne sont pas clairs ;
- considérer un dataset Internet comme vérité linguistique absolue.

---

# 13. Tableau d'état actuel

| Source / étape | Statut | Résultat |
|---|---|---|
| ASJP — découverte | `done` | source identifiée |
| ASJP — licence | `approved_for_ingestion` | CC-BY-4.0 enregistrée |
| ASJP — collector | `done` | `collectors/asjp.py` |
| ASJP — collecte réelle | `collection_success` | 341 entrées |
| ASJP — séparation variétés | `qa_passed` | sbd / stj / sym séparés |
| ASJP — doublons exacts | `qa_passed` | aucun doublon exact détecté dans l'extraction analysée |
| ASJP — normaliseur | `implemented` | `processors/normalize.py` |
| ASJP — exécution normaliseur locale | `to_run` | lancer après `git pull` |
| ASJP — validation linguistique | `not_started` | nécessaire avant normalisation orthographique |
| ASJP — training | `not_approved` | ne pas utiliser comme corpus d'entraînement validé pour l'instant |
| Hugging Face | `rights_review_required` | analyser dataset par dataset |
| Webonary | `blocked_for_now` | droits / accès à vérifier |

---

# 14. Prochaine étape immédiate

Après récupération du dernier code :

```bash
git pull origin feat/data-ingestion
```

Depuis :

```text
~/Bureau/langue-san/tools/data_ingestion
```

activer le venv :

```bash
source .venv/bin/activate
```

lancer les tests :

```bash
pytest tests
```

puis :

```bash
python processors/normalize.py
```

Ensuite inspecter :

```text
data/processed/asjp/asjp_v21_report.json
```

La prochaine décision sera basée sur ce rapport, pas uniquement sur le fait que le script s'est exécuté.

---

# 15. Règle finale à retenir

Le pipeline Langue SAN suit cette règle :

```text
Téléchargé
    ≠
Correct linguistiquement
    ≠
Validé
    ≠
Autorisé pour entraînement
```

Chaque passage d'un niveau au suivant doit être explicite, documenté et traçable.
