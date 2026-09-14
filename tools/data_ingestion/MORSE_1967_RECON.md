# Morse 1967 — reconnaissance de la source historique

Cette note documente l’analyse de la source **Mary Lynn Morse (1967)** après l’envoi de la demande d’accès au lexique San Mayaa 2011 à ANTBA.

## 1. Référence bibliographique

Référence confirmée par ASJP et plusieurs travaux linguistiques ultérieurs :

```text
Morse, Mary Lynn. 1967.
The Question of 'Samogo'.
Journal of African Languages, vol. 6, pp. 61–80.
```

La graphie du titre varie parfois dans les bibliographies (`Samogo` / `Samogho`), mais la référence de travail du projet reste `The Question of 'Samogo'`, Journal of African Languages 6:61–80.

## 2. Pourquoi cette source intéresse Langue_SAN

ASJP utilise explicitement Morse 1967 comme source pour une wordlist **Samo Matya** :

```text
ASJP id      : SAMO_MATYA
ISO 639-3    : stj
Glottocode   : maty1235
source       : Morse 1967
```

Cette wordlist doit être distinguée de :

```text
ASJP id      : SAMO_MATYA_2
source       : Morris et al. 2011
```

Conséquence permanente pour le pipeline :

```text
ASJP / SAMO_MATYA    -> dérivé de Morse 1967
ASJP / SAMO_MATYA_2  -> dérivé de Morris et al. 2011
```

Les données `stj` d’ASJP ne doivent donc jamais être traitées comme une seule provenance documentaire.

## 3. Nature de l’article

Les travaux secondaires décrivent Morse 1967 comme une étude comparative destinée à clarifier le terme historique `Samogo` et la classification de plusieurs parlers ainsi désignés.

Une étude ultérieure de Robert Carlson décrit Morse 1967 comme fournissant une **liste comparative de 573 items** pour trois ensembles :

```text
Sembla
Samogho-Gouan
Tougan Samogo
```

Une autre enquête linguistique ultérieure utilise seulement **251 items tirés de Morse** pour certains calculs lexico-statistiques. Ces deux nombres ne désignent donc pas la même chose :

```text
573 items = taille de la liste comparative décrite pour Morse 1967
251 items = sous-ensemble de Morse utilisé dans une comparaison ultérieure
```

L’article ne doit pas être assimilé à un dictionnaire San Matya moderne. Son intérêt principal reste historique et comparatif.

## 4. Attention au terme « Samogo »

Le terme `Samogo` a historiquement recouvert plusieurs langues/groupes différents.

Règle du projet :

```text
une forme présente dans Morse 1967
    != automatiquement San Matya
    != automatiquement San Maya
    != automatiquement San Maka
```

Chaque liste/localité doit être attribuée uniquement à partir de la provenance explicitement fournie par la source.

La composante `Tougan Samogo` est la plus pertinente pour notre piste `stj`, mais cela ne permet pas d’attribuer les autres ensembles au San moderne.

## 5. Audit réel de provenance ASJP v21

Le processeur :

```text
tools/data_ingestion/processors/analyze_asjp_provenance.py
```

a été exécuté sur le RAW ASJP local.

Résultat global :

```text
Entrées ASJP SAN : 341
sbd              : 37
stj              : 129
sym              : 175
```

Répartition exacte par wordlist/provenance :

```text
SAMO_MATYA
  source            : Morse 1967 — The Question of 'Samogo'
  occurrences       : 34
  formes uniques    : 33
  concepts          : 32

SAMO_MATYA_2
  source            : Morris et al. 2011 — Lexique San Matya avec guide d'orthographe
  occurrences       : 95
  formes uniques    : 87
  concepts          : 73

SAMO_MAYA
  source            : Morris, Koussoubé & Seme 2011 — Lexique San Mayaa avec guide d'orthographe
  occurrences       : 102
  formes uniques    : 93
  concepts          : 87

MAYA_SAMO
  source            : Berthelette 2001 — Sociolinguistic survey report for the San language
  occurrences       : 73
  formes uniques    : 70
  concepts          : 25
```

Sorties locales :

```text
data/processed/asjp/provenance/asjp_san_provenance_summary.json
data/processed/asjp/provenance/asjp_san_provenance_by_language_id.csv
```

## 6. Ce que l’audit change pour Morse

Nous savons maintenant exactement ce que Morse apporte déjà via ASJP :

```text
34 occurrences
33 formes uniques
32 concepts
```

Ce sous-ensemble est faible par rapport aux **573 items comparatifs** signalés par la littérature secondaire. Il ne faut donc pas conclure que l’intégralité de Morse 1967 est déjà représentée dans ASJP.

Cependant, la source primaire complète n’est pas actuellement accessible sous une forme ouverte exploitable et ses droits de réutilisation ne sont pas clarifiés.

Décision de projet :

```text
conserver les 34 occurrences ASJP déjà récoltées
conserver explicitement la provenance Morse 1967
ne pas tenter d’inventer/reconstruire les centaines d’items absents
ne pas bloquer data_ingestion sur la recherche du document primaire
reprendre la piste uniquement si une copie légitime ou une autorisation devient disponible
```

## 7. Accès numérique et droits

État observé :

```text
référence bibliographique : confirmée
volume du journal          : catalogué / numérisé avec accès limité
article primaire ouvert    : non retrouvé
PDF primaire libre         : non confirmé
```

Les documents secondaires ne remplacent pas la source primaire.

Droits :

```text
article_publication_year   = 1967
rights_status              = rights_review_required
fulltext_redistribution    = not_approved
training_approved          = false
commercial_use_approved    = false
```

La licence CC-BY-4.0 d’ASJP couvre les données distribuées par ASJP sous ses conditions ; elle ne transforme pas l’article original de Morse en ressource CC-BY.

## 8. Statut final de la reconnaissance Morse

```text
source_reference             = confirmed
author                       = Mary Lynn Morse
year                         = 1967
title                        = The Question of 'Samogo'
journal                      = Journal of African Languages
volume                       = 6
pages                        = 61-80

relevance_to_stj             = confirmed_via_asjp
asjp_wordlist                = SAMO_MATYA
asjp_occurrences             = 34
asjp_unique_forms            = 33
asjp_concepts                = 32

comparative_wordlist_size    = 573_items_reported_by_secondary_source
later_subset_size            = 251_items_used_in_later_comparison
reported_groups              = Sembla, Samogho-Gouan, Tougan Samogo

primary_fulltext             = not_found_openly
rights_status                = rights_review_required
new_primary_ingestion        = deferred
technical_recon_status       = closed_for_now
```

## 9. Décision

La reconnaissance Morse 1967 est **clôturée pour cette phase**.

Aucun nouveau compteur RAW n’est ajouté : les 34 occurrences Morse sont déjà incluses dans les 341 occurrences ASJP existantes.

La piste ne sera rouverte que si l’un des événements suivants se produit :

```text
- copie primaire légitime obtenue ;
- accès bibliothèque / archive permettant inspection directe ;
- autorisation explicite de réutilisation ;
- nouvelle édition ou transcription fiable avec provenance démontrée.
```

Le projet passe donc à la prochaine piste sans rester bloqué sur Morse 1967.
