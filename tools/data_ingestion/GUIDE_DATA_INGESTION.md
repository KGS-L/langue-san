# Guide Data Ingestion — Langue SAN

Ce document est le **manuel de travail principal** de `tools/data_ingestion/`.

Son objectif est simple : permettre de revenir sur le projet après plusieurs semaines ou plusieurs mois et comprendre immédiatement :

- ce que fait le pipeline ;
- pourquoi chaque étape existe ;
- quelles commandes lancer ;
- quels fichiers sont générés ;
- quel résultat est attendu ;
- comment reconnaître un échec ;
- ce qui est techniquement validé ;
- ce qui doit encore être validé par des humains ;
- à quel moment une donnée peut ou non être utilisée pour le ML.

---

# 1. Rôle de `data_ingestion`

`tools/data_ingestion/` sert à récupérer et préparer des **ressources linguistiques externes** déjà publiées : datasets, lexiques, corpus, API, exports structurés, etc.

Il ne faut pas confondre cette partie avec le collecteur Laravel.

```text
apps/collector/
    contributions terrain et communautaires

 tools/data_ingestion/
    ressources externes déjà existantes

 data/raw/
    copie brute des sources récupérées

 data/processed/
    données transformées et rapports de travail

 ml/
    expériences, entraînement et évaluation
```

---

# 2. Principe central

Une donnée récupérée sur Internet n'est pas automatiquement correcte pour notre application.

Toujours garder cette règle en tête :

```text
Téléchargé
    ≠
Correct linguistiquement
    ≠
Validé humainement
    ≠
Autorisé pour entraînement
```

Les étapes sont donc volontairement séparées.

---

# 3. Pipeline général

```text
1. Découvrir une source
        ↓
2. Vérifier droits / licence / accès
        ↓
3. Collecter techniquement
        ↓
4. Stocker le RAW
        ↓
5. Normaliser la structure
        ↓
6. Produire les rapports QA
        ↓
7. Analyser les variantes
        ↓
8. Ajouter des métadonnées contrôlées si utile
        ↓
9. Préparer la revue humaine
        ↓
10. Validation linguistique
        ↓
11. Décision dataset
        ↓
12. Autorisation ML éventuelle
```

Aucune étape ne doit être sautée silencieusement.

---

# 4. Variétés SAN suivies

Les trois variétés doivent rester séparées.

| Variété | ISO 639-3 | Nom de travail |
|---|---|---|
| San Maka / San du Sud | `sbd` | `maka` |
| San Matya | `stj` | `matya` |
| San Maya | `sym` | `maya` |

Règle :

```text
sbd != stj != sym
```

Une ressemblance entre deux formes ne permet jamais de fusionner automatiquement les variétés.

---

# 5. Niveaux de données

## 5.1 RAW

Donnée récupérée telle que fournie par la source.

Exemple :

```text
data/raw/asjp/asjp_v21_san_wordlists.json
```

Règles :

- ne pas modifier manuellement ;
- conserver provenance, version et licence ;
- conserver la notation originale ;
- ne pas considérer la donnée comme validée ;
- ne pas commiter le RAW dans Git.

## 5.2 PROCESSED

Donnée structurée selon le schéma de travail du projet.

Exemple :

```text
data/processed/asjp/
```

Une normalisation peut modifier les **noms de champs et la structure**, mais pas inventer une correction linguistique.

## 5.3 HUMAN REVIEW

Donnée préparée pour qu'un locuteur, validateur ou linguiste prenne une décision explicite.

## 5.4 VALIDATED

Donnée linguistiquement approuvée selon le workflow du projet.

## 5.5 TRAINING APPROVED

Donnée explicitement autorisée pour le ML après contrôle qualité, gouvernance, droits et split dataset.

---

# 6. Statuts utilisés

| Statut | Sens |
|---|---|
| `discovered` | source trouvée |
| `rights_review_required` | droits à vérifier |
| `approved_for_ingestion` | ingestion autorisée |
| `collection_success` | récupération réussie |
| `collection_failed` | récupération échouée |
| `normalization_success` | structure normalisée |
| `qa_passed` | contrôle technique satisfaisant |
| `external_unverified` | donnée externe non validée linguistiquement |
| `human_review_required` | décision humaine nécessaire |
| `linguistic_validation_required` | validation linguistique requise |
| `training_approved` | autorisé explicitement pour ML |
| `blocked` | ressource bloquée |
| `rejected` | ressource ou entrée rejetée |

Important :

```text
collection_success != linguistic_validation
normalization_success != training_approved
```

---

# 7. Structure actuelle

```text
tools/data_ingestion/
├── config/
│   ├── concepts_fr.yaml
│   ├── languages.yaml
│   └── sources.yaml
│
├── collectors/
│   ├── __init__.py
│   └── asjp.py
│
├── processors/
│   ├── __init__.py
│   ├── build_review_sheet.py
│   ├── enrich_concepts.py
│   ├── normalize.py
│   └── qa_variants.py
│
├── tests/
│   ├── test_asjp.py
│   ├── test_build_review_sheet.py
│   ├── test_config.py
│   ├── test_enrich_concepts.py
│   ├── test_normalize.py
│   └── test_qa_variants.py
│
├── GUIDE_DATA_INGESTION.md
├── README.md
└── requirements.txt
```

---

# 8. Installation locale

Depuis :

```text
~/Bureau/langue-san/tools/data_ingestion
```

Créer le venv une seule fois :

```bash
python3 -m venv .venv
```

Activer :

```bash
source .venv/bin/activate
```

Installer les dépendances :

```bash
pip install -r requirements.txt
```

Lors des prochaines sessions :

```bash
cd ~/Bureau/langue-san/tools/data_ingestion
source .venv/bin/activate
```

Le terminal doit afficher `(.venv)`.

---

# 9. Tests

Après chaque `git pull` qui modifie `data_ingestion`, lancer :

```bash
pytest tests
```

Résultat attendu :

```text
... passed
```

Si un test échoue :

```text
STOP
→ lire l'erreur
→ corriger avant de régénérer les données
```

Ne jamais utiliser `--break-system-packages` pour installer les dépendances du projet.

---

# 10. Source ASJP v21

ASJP est la première source externe réellement intégrée.

Configuration principale :

```text
version : v21
licence : CC-BY-4.0
usage   : lexical_reference
statut  : approved_for_ingestion
```

ASJP sert ici de **référence lexicale externe**. Ce n'est pas encore notre dictionnaire San officiel.

---

# 11. Étape ASJP 1 — collecte

Commande :

```bash
python collectors/asjp.py
```

Fichier produit :

```text
data/raw/asjp/asjp_v21_san_wordlists.json
```

## Résultat réel obtenu le 11 septembre 2026

```text
341 entrées
```

| ISO | Variété | Entrées | Concepts uniques | Formes uniques |
|---|---|---:|---:|---:|
| `sbd` | Maka | 37 | 34 | 36 |
| `stj` | Matya | 129 | 78 | 111 |
| `sym` | Maya | 175 | 88 | 150 |
| **Total** | | **341** | | |

Listes lexicales rencontrées :

```text
sbd
├── SOUTHERN_SAMO       33
└── SOUTHERN_SAMO_SAN    4

stj
├── SAMO_MATYA          34
└── SAMO_MATYA_2        95

sym
├── MAYA_SAMO           73
└── SAMO_MAYA           102
```

Statut :

```text
collection_success ✅
```

---

# 12. Étape ASJP 2 — normalisation

Commande :

```bash
python processors/normalize.py
```

Résultat réel :

```text
Entrées normalisées : 341
- sbd (maka): 37 entrées, 34 concepts, 36 formes
- stj (matya): 129 entrées, 78 concepts, 111 formes
- sym (maya): 175 entrées, 88 concepts, 150 formes
```

Sorties :

```text
data/processed/asjp/
├── asjp_v21_san_normalized.json
├── asjp_v21_san_normalized.csv
└── asjp_v21_report.json
```

Le normaliseur conserve :

```text
source_form
source_wordlist
iso_639_3
provenance
licence
```

Il laisse :

```text
standard_san = null
```

jusqu'à validation linguistique.

---

# 13. Comprendre la notation ASJP

Exemples réels :

```text
mu
5ini
yErE
Ci
ky~E
jE*
m3
```

Ne jamais faire automatiquement :

```text
5 → ?
E → é
C → c
* → suppression
```

sans règle linguistique documentée et approuvée.

La forme originale doit rester disponible dans :

```text
source_form
```

---

# 14. Étape ASJP 3 — QA des variantes

Commande :

```bash
python processors/qa_variants.py
```

Résultat réel :

```text
68 groupes multi-formes
```

Par variété :

```text
sbd : 2
stj : 35
sym : 31
```

Par type :

```text
cross_wordlist_single_each            : 16
cross_wordlist_with_internal_variants : 27
internal_variants_same_wordlist       : 25
```

Sorties :

```text
data/processed/asjp/
├── asjp_v21_variants_review.json
├── asjp_v21_variants_review.csv
└── asjp_v21_variants_summary.json
```

## Interprétation

### `internal_variants_same_wordlist`

Plusieurs formes pour un concept dans une même liste.

### `cross_wordlist_single_each`

Plusieurs listes donnent chacune une forme différente.

### `cross_wordlist_with_internal_variants`

Plusieurs listes divergent et au moins une contient plusieurs formes.

C'est la catégorie la plus prioritaire pour la revue humaine.

Règle :

```text
plusieurs formes != erreur automatique
```

---

# 15. Étape ASJP 4 — glosses françaises contrôlées

Configuration :

```text
config/concepts_fr.yaml
```

Commande :

```bash
python processors/enrich_concepts.py
```

Résultat réel obtenu :

```text
Concepts ASJP uniques : 92
Concepts avec glose FR : 92
Concepts sans glose FR : 0
Entrées enrichies : 341 / 341
```

Sorties :

```text
data/processed/asjp/
├── asjp_v21_san_enriched.json
├── asjp_v21_san_enriched.csv
└── asjp_v21_concepts_fr_report.json
```

Exemple :

```text
concept_normalized = water
concept_fr         = eau
source_form        = mu
standard_san       = null
```

Cela signifie :

```text
water → eau
```

est une glose de concept contrôlée par le projet.

Cela ne signifie PAS encore :

```text
eau → mu
```

est une traduction San validée.

---

# 16. Étape ASJP 5 — préparer la revue humaine

Commande :

```bash
python processors/build_review_sheet.py
```

Objectif : regrouper les 341 lignes par :

```text
variété + concept
```

Les statistiques actuelles donnent un résultat attendu de :

```text
sbd : 34 éléments
stj : 78 éléments
sym : 88 éléments
Total : 200 éléments de revue
```

Parmi eux :

```text
68 multi-formes
132 forme unique observée
```

Sorties attendues :

```text
data/processed/asjp/review/
├── asjp_v21_human_review.json
├── asjp_v21_human_review.csv
└── asjp_v21_human_review_summary.json
```

## Champs importants pour le validateur

Le CSV rassemble :

```text
iso_639_3
variety
concept_en
concept_fr
source_wordlists
source_forms
variant_classification
review_priority
```

et laisse à compléter :

```text
decision_status
selected_source_form
standard_san
reviewer_notes
linguistic_status
```

Aucun de ces champs de décision n'est rempli automatiquement.

## Priorités

```text
high
    cross_wordlist_with_internal_variants

medium
    cross_wordlist_single_each
    internal_variants_same_wordlist

normal
    une seule forme observée
```

Même une ligne `normal` doit être validée : `normal` signifie seulement qu'ASJP ne présente qu'une forme dans notre extraction.

---

# 17. Comment effectuer la revue humaine

Pour chaque élément :

1. vérifier que le concept français est compris ;
2. vérifier que la variété correspond au locuteur / validateur ;
3. examiner les formes ASJP proposées ;
4. décider si une forme source est acceptable ;
5. si nécessaire, proposer une orthographe San correcte ;
6. ajouter une note lorsqu'une décision est ambiguë ;
7. ne jamais valider une variété différente par simple ressemblance.

Décisions possibles de travail :

```text
confirm_source_form
enter_standard_form
keep_multiple
needs_second_review
reject
```

Le workflow final de validation linguistique sera défini avant import de ces décisions dans un dataset validé.

---

# 18. Ce qui constitue un échec

## Collecte

```text
HTTP 403 / 404
connexion impossible
aucune entrée trouvée
structure distante modifiée
```

→ `collection_failed`

## Licence

Ressource accessible mais droits non clairs.

→ `rights_review_required` ou `blocked`

## Normalisation

```text
JSON invalide
entries absent
code ISO non supporté
```

→ ne pas continuer.

## QA

Des variantes nombreuses ne sont pas un échec en elles-mêmes.

L'échec serait de les supprimer ou fusionner sans justification.

## Linguistique

Une forme peut être techniquement bien importée mais incorrecte pour une variété.

→ conserver la provenance et marquer la décision humaine.

---

# 19. Ordre recommandé pour régénérer ASJP

Après modification du code :

```bash
pytest tests
python collectors/asjp.py
python processors/normalize.py
python processors/qa_variants.py
python processors/enrich_concepts.py
python processors/build_review_sheet.py
```

Chaque commande dépend de la précédente.

Pipeline fichiers :

```text
ASJP distant
    ↓
data/raw/asjp/asjp_v21_san_wordlists.json
    ↓
asjp_v21_san_normalized.json
    ↓
asjp_v21_variants_review.json
    ↓
asjp_v21_san_enriched.json
    ↓
review/asjp_v21_human_review.csv
```

---

# 20. Ajouter une nouvelle source

Pour Hugging Face, Webonary ou toute autre source :

## A. Découverte

Documenter :

```text
nom
URL
organisation
langues
format
volume
```

## B. Droits

Vérifier :

```text
licence
réutilisation
redistribution
modification
usage commercial
usage ML
attribution
```

## C. Configuration

Ajouter dans :

```text
config/sources.yaml
```

## D. Collector

Créer :

```text
collectors/<source>.py
```

## E. RAW

Écrire sous :

```text
data/raw/<source>/
```

## F. Normalisation

Préserver la source et la notation originale.

## G. QA

Mesurer :

```text
volume
variétés
champs manquants
doublons
variantes
anomalies
```

## H. Revue humaine / décision

Choisir explicitement :

```text
reference_only
human_review
validated_dataset
rejected
blocked
training_approved
```

---

# 21. Ce qu'il ne faut jamais faire

Ne jamais :

- mélanger `sbd`, `stj`, `sym` automatiquement ;
- convertir la notation ASJP arbitrairement ;
- supprimer une variante uniquement parce qu'elle paraît étrange ;
- présenter `source_form` comme `standard_san` sans validation ;
- inventer une traduction manquante en prétendant qu'elle vient de la source ;
- perdre la provenance ;
- commiter des RAW non destinés à Git ;
- utiliser une ressource aux droits incertains pour le ML ;
- prendre une liste Internet comme vérité linguistique absolue ;
- activer `training_approved` par défaut.

---

# 22. État réel du projet — 11 septembre 2026

| Étape | Statut | Résultat |
|---|---|---|
| ASJP découverte | ✅ | source identifiée |
| ASJP droits | ✅ ingestion | CC-BY-4.0 enregistrée |
| ASJP collector | ✅ | fonctionnel |
| ASJP collecte réelle | ✅ | 341 entrées |
| Séparation variétés | ✅ | sbd / stj / sym |
| Normalisation | ✅ | 341 / 341 |
| QA variantes | ✅ | 68 groupes |
| Glosses FR | ✅ | 92 / 92 concepts, 341 / 341 entrées |
| Préparation revue humaine | ⏳ | script prêt, exécution locale suivante |
| Validation orthographique San | ⏳ | non commencée |
| Validation humaine | ⏳ | non commencée |
| Dataset validé ASJP | ❌ | pas encore |
| Training ASJP | ❌ | non approuvé |
| Hugging Face | ⏳ | revue ressource par ressource |
| Webonary | ⛔ | accès / droits à clarifier |

---

# 23. Prochaine action

Depuis :

```text
~/Bureau/langue-san/tools/data_ingestion
```

Après avoir récupéré les derniers commits :

```bash
pytest tests
python processors/build_review_sheet.py
```

Résultat attendu : environ **200 éléments de revue humaine**.

Une fois cette étape confirmée, la phase technique ASJP de préparation sera quasiment terminée. La prochaine grande phase ne sera plus du scraping : ce sera l'organisation de la **validation linguistique humaine**.
