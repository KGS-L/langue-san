# Morse 1967 — reconnaissance de la source historique

Cette note ouvre l’analyse de la source **Mary Lynn Morse (1967)** après l’envoi de la demande d’accès au lexique San Mayaa 2011 à ANTBA.

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

Donc les données ASJP `stj` ne représentent pas nécessairement une seule provenance historique.

Conséquence pour notre pipeline :

```text
ASJP / SAMO_MATYA    -> dérivé de Morse 1967
ASJP / SAMO_MATYA_2  -> dérivé de Morris et al. 2011
```

Il faudra conserver ce niveau de provenance pendant les comparaisons et la construction du dataset final.

## 3. Nature de l’article

Les travaux secondaires décrivent Morse 1967 comme une étude comparative destinée à clarifier le terme historique `Samogo` et la classification de plusieurs parlers ainsi désignés.

Une enquête linguistique ultérieure sur le samogo indique que Morse travaillait avec des listes lexicales d’environ :

```text
251 items
```

et compare notamment plusieurs parlers appelés historiquement Samogo/Samo.

L’article ne doit donc pas être considéré automatiquement comme un dictionnaire San Matya moderne. Son intérêt principal est :

```text
- vocabulaire historique comparatif ;
- provenance ancienne pour une partie du Matya ;
- classification des groupes appelés Samogo/Samo ;
- comparaison avec des parlers qui ne correspondent pas tous à sbd/stj/sym ;
- contrôle diachronique des formes lexicales.
```

## 4. Attention au terme « Samogo »

Morse 1967 est justement important parce que le terme `Samogo` a historiquement recouvert plusieurs langues/groupes différents.

Des travaux ultérieurs soulignent notamment que le Samo parlé autour de Tougan n’appartient pas au même groupe que le dzùùngoo / samogho de l’ouest du Burkina Faso, malgré l’usage historique de noms proches.

Règle du projet :

```text
une forme présente dans Morse 1967
    != automatiquement San Matya
    != automatiquement San Maya
    != automatiquement San Maka
```

Chaque liste/localité doit être attribuée uniquement à partir de la provenance explicitement fournie par la source.

## 5. Relation avec nos données déjà récoltées

Nous possédons déjà indirectement une petite partie de Morse 1967 via ASJP.

Le corpus ASJP v21 récolté contient au total :

```text
stj : 129 occurrences
```

mais ce volume peut agréger plusieurs wordlists `stj` de provenance différente. Il ne faut donc pas utiliser le simple compteur ISO pour conclure que les 129 lignes proviennent toutes de Morse 1967.

Avant toute ingestion supplémentaire de Morse, il faudra séparer les entrées ASJP par `language_id` et source bibliographique.

## 6. Accès numérique actuel

Recherche initiale :

```text
référence bibliographique : confirmée
volume du journal          : référencé dans Google Books
article complet ouvert     : non retrouvé lors de la première recherche
PDF primaire libre         : non confirmé
```

Le volume 6 du `Journal of African Languages` est référencé dans Google Books, mais la présence d’une notice ou d’un aperçu ne constitue pas un droit de réutilisation du contenu intégral.

Des PDF secondaires citent et analysent Morse 1967, mais ils ne remplacent pas le document primaire.

## 7. Droits

À ce stade :

```text
article_publication_year   = 1967
rights_status              = rights_review_required
fulltext_redistribution    = not_approved
training_approved          = false
commercial_use_approved    = false
```

La licence CC-BY-4.0 d’ASJP couvre les données distribuées par ASJP sous ses conditions ; elle ne doit pas être interprétée comme une licence CC-BY sur l’article original de Morse.

## 8. Statut actuel

```text
source_reference           = confirmed
author                     = Mary Lynn Morse
year                       = 1967
title                      = The Question of 'Samogo'
journal                    = Journal of African Languages
volume                     = 6
pages                      = 61-80

relevance_to_stj           = confirmed_via_asjp
asjp_wordlist              = SAMO_MATYA
historical_wordlist_size   = approximately_251_items_from_secondary_report

primary_fulltext           = not_found_yet
rights_status              = rights_review_required
bulk_harvest               = blocked_pending_primary_access_and_rights_review
technical_ingestion_status = reconnaissance_in_progress
```

## 9. Prochaine action

```text
1. retrouver une copie primaire légitime du Journal of African Languages 6 (1967), pages 61–80 ;
2. vérifier si l’article contient effectivement les listes lexicales complètes ou seulement les résultats comparatifs ;
3. identifier chaque localité / parler présent dans les tableaux ;
4. vérifier les droits de reproduction et de réutilisation ;
5. séparer dans ASJP les données SAMO_MATYA (Morse 1967) des autres wordlists stj ;
6. seulement ensuite décider si une nouvelle ingestion apporte des données non déjà présentes dans ASJP.
```

Le but n’est pas de dupliquer ASJP, mais de récupérer la provenance primaire et, si le document le permet, des formes ou métadonnées que l’agrégateur n’a pas conservées.
