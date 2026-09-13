# San Maya / `sym` — reconnaissance de la source primaire

Cette note ouvre la piste San Maya après la clôture technique de Matya.

## 1. Identité de la variété

```text
nom principal : San Maya / San Mayaa / Maya Samo
ISO 639-3     : sym
Glottocode    : maya1281
zone          : nord-ouest du Burkina Faso, notamment Kiembara et plusieurs localités du Sourou
```

Règle permanente du projet : `sym` reste séparé de `stj` (Matya) et `sbd` (Maka).

## 2. Source lexicale primaire confirmée

La référence lexicale la plus importante retrouvée pour Maya est :

```text
Morris, P., Koussoubé, M., Seme, P. 2011.
Lexique San Mayaa avec guide d’orthographe.
Tougan, Burkina Faso:
Association nationale pour la traduction de la Bible et alphabétisation (ANTBA).
```

Cette référence est explicitement utilisée par la wordlist ASJP `SAMO_MAYA` :

```text
https://asjp.clld.org/languages/SAMO_MAYA
```

Elle est également référencée comme source amont du corpus lexical Maya exposé par RefLex.

Une publication académique secondaire cite également :

```text
Guide d’orthographe san mayaa.
Édition préliminaire – juin 2011.
SIL.
```

Le projet traite pour l’instant ces références comme fortement liées, mais ne fusionne pas bibliographiquement le guide SIL et le lexique ANTBA sans preuve primaire supplémentaire.

## 3. Attention : deux wordlists ASJP `sym`

ASJP expose au moins deux wordlists associées à `sym` / `maya1281` :

```text
SAMO_MAYA
  source : Morris et al. 2011
  nature : source lexicale primaire / dérivée du lexique San Mayaa

MAYA_SAMO
  source : Berthelette 2001
  nature : wordlist du rapport sociolinguistique
```

La seconde n’est pas une source indépendante de Berthelette pour notre inventaire, puisque Berthelette a déjà été récolté directement et traité séparément.

Il faudra donc éviter de compter naïvement les deux ASJP comme deux apports lexicaux indépendants.

## 4. RefLex déjà récolté

La récolte RefLex actuelle contient :

```text
ISO             : sym
nom RefLex      : Samo Maya
glottocode      : maya1281
page CLLD       : 1473
filterLanguage  : 563
unités RAW      : 2 378
QA technique    : passed
```

Ces 2 378 unités sont déjà stockées localement sous :

```text
data/raw/reflex/sym/units.csv
```

Elles restent une source agrégée ; la priorité actuelle est de retrouver ou d’obtenir l’accès à la source primaire 2011.

## 5. Berthelette déjà récolté

Le rapport Berthelette apporte pour Maya :

```text
sym / Maya : 912 occurrences
```

Localités explicitement Maya dans cette source :

```text
Bounou      : 228
Kiembara    : 229
Bangassogo  : 226
Lankoué     : 229
```

Ces données sont déjà techniquement clôturées et ne doivent pas être recollectées comme une nouvelle source.

## 6. PanLex et autres agrégateurs

Le snapshot PanLex déjà récolté contient actuellement :

```text
sym : 1 ligne
```

Ce volume est négligeable pour Maya et ne constitue pas une source primaire.

Les plateformes de type Glosbe peuvent être utilisées pour reconnaissance/provenance, mais ne sont pas considérées comme sources indépendantes tant que leurs données amont ne sont pas identifiées.

## 7. Ressource web San Mayaa identifiée

Le site :

```text
https://www.leburusan.com/
```

possède une section `sym` et son plan de site annonce explicitement :

```text
Lexique San Mayaa en ligne
```

Le même site contient aussi des textes et contenus vidéo San Mayaa, ce qui pourra être utile plus tard pour la phase média/audio.

Pour l’instant :

```text
ressource                 = confirmed_online
lexique_en_ligne          = confirmed_by_site_index
relation_exacte_avec_2011 = not_yet_proven
bulk_harvest              = blocked_pending_rights_and_structure_review
publication_approved      = false
training_approved         = false
```

Nous ne lançons pas de scraping massif tant que la structure exacte du lexique et les droits de réutilisation ne sont pas clarifiés.

## 8. Application Android ANTBA

Une application Android actuelle existe :

```text
nom     : San Mayaa
package : com.mayaa.san.burkina
éditeur : ANTBA
```

La fiche publique la présente principalement comme une application de textes bibliques San Mayaa avec affichage parallèle français/anglais.

Décision :

```text
ce n’est pas actuellement traité comme le lexique primaire 2011
catégorie future : text/media religious corpus
```

Elle pourra être inventoriée lors de la phase texte/audio, avec les mêmes règles de droits et de provenance que les autres ressources religieuses.

## 9. Contexte institutionnel actuel

ANTBA reste active sur San Mayaa. Un bulletin ANTBA de 2024 indique notamment la dédicace des Nouveaux Testaments San Mayaa et Matyaa le 4 mai 2024.

Cette continuité institutionnelle rend ANTBA prioritaire pour :

```text
1. demander une copie numérique du Lexique San Mayaa 2011 ;
2. demander si un export LIFT / Lexique Pro existe ;
3. clarifier les droits de recherche, corpus et entraînement ML ;
4. demander si le lexique en ligne Leburu correspond directement au corpus 2011.
```

## 10. Statut actuel

```text
discovery_historical       = confirmed
variety                    = maya
iso_639_3                  = sym
glottocode                 = maya1281
primary_lexicon_year       = 2011
primary_lexicon_publisher  = ANTBA
primary_lexicon_reference  = confirmed
primary_pdf_or_lift        = not_found_yet
rights_status              = rights_review_required

reflex_units_harvested     = 2378
berthelette_occurrences    = 912
panlex_rows                = 1

online_lexicon_candidate   = leburusan.com/sym
android_text_app_candidate = com.mayaa.san.burkina

bulk_harvest               = blocked_pending_primary_access_or_permission
technical_ingestion_status = in_progress
```

## 11. Prochaine action

Ordre immédiat :

```text
1. retrouver un PDF/LIFT/export du Lexique San Mayaa 2011 ;
2. inspecter la structure réelle du lexique en ligne Leburu ;
3. identifier clairement le détenteur des droits / contact ANTBA ;
4. demander une autorisation comparable à celle obtenue pour Matya si nécessaire ;
5. seulement ensuite récolter la source primaire ;
6. QA technique ;
7. comparaison avec RefLex, Berthelette, ASJP et PanLex ;
8. clôture Maya.
```

L’objectif n’est pas d’accumuler les mêmes données à travers plusieurs agrégateurs, mais d’identifier l’origine primaire, préserver la provenance et mesurer l’apport réellement nouveau.