# Berthelette 2001 — reconnaissance avant ingestion

Cette note documente la prochaine source externe prioritaire après la clôture technique de RefLex `stj/sym`.

## 1. Référence bibliographique confirmée

Référence de travail :

```text
Berthelette, John. 2001.
Sociolinguistic survey report for the San (Samo) language.
SIL Electronic Survey Reports (SILESR), 2002-005.
Dallas, Texas: SIL International.
75 pages.
```

Glottolog référence le document sous l'identifiant bibliographique `102181` et renvoie vers l'ancienne notice SIL :

```text
http://www.sil.org/silesr/abstract.asp?ref=2002-005
```

L'ancienne arborescence de ressources linguistiques mentionne également :

```text
www.sil.org/silesr/2002/005
```

Ces anciennes URL SIL peuvent ne plus correspondre à l'architecture actuelle du site ; elles servent de provenance historique et non de licence.

## 2. Pourquoi cette source est importante

Berthelette n'est pas seulement un lexique. Il s'agit d'une enquête sociolinguistique sur le San/Samo.

Pour le projet Langue SAN, son intérêt principal est de documenter :

```text
localités / villages
    ↓
formes lexicales observées
    ↓
comparaisons entre zones
    ↓
contexte sociolinguistique
    ↓
indices sur les frontières entre variétés
```

Cette source doit nous aider à éviter une attribution simpliste du type :

```text
Toma = automatiquement Maka
Tougan = automatiquement Matya
```

Une localité reste un indice tant que le document et/ou une source linguistique explicite ne confirme pas la variété.

## 3. Relation avec les sources déjà récoltées

ASJP cite `Berthelette 2001` comme source de sa wordlist `MAYA_SAMO` (`sym`).

Cela signifie que Berthelette est probablement une source amont d'au moins une partie des données comparatives déjà présentes dans notre RAW ASJP.

Conséquence :

```text
Berthelette + ASJP
    ≠ deux sources indépendantes à additionner naïvement
```

La provenance devra être conservée afin de mesurer les chevauchements plus tard.

## 4. Contexte SIL Burkina Faso

Le site actuel de SIL Burkina Faso distingue explicitement :

```text
Samo du sud
Samo, Matya
Samo, Maya
```

Le site indique également que les documents téléchargeables qu'il héberge sont protégés par le droit d'auteur. Ses conditions générales autorisent le téléchargement et une copie pour usage privé, recherche académique ou éducation, mais interdisent la réédition et l'utilisation commerciale sans consentement écrit.

Important : ces conditions concernent les documents publiés sur le site SIL Burkina Faso. Tant que l'item Berthelette exact n'a pas été retrouvé sur ce site avec sa notice actuelle, elles ne doivent pas être appliquées automatiquement à l'item comme licence spécifique.

Statut provisoire :

```text
rights_status = item_license_review_required
publication_approved = false
training_approved = false
commercial_use_approved = false
```

## 5. Ce que nous savons déjà sur le contenu

Les références secondaires décrivent le rapport comme une enquête de 75 pages sur le San/Samo. Des sources dérivées citent des données lexicales de plusieurs localités et ASJP utilise Berthelette comme source de `MAYA_SAMO`.

Nous ne devons toutefois pas reconstruire le dataset depuis Wikipedia, Wiktionary ou d'autres sources secondaires.

Règle :

```text
source secondaire = aide à la reconnaissance
source originale   = base de la récolte
```

## 6. Étapes de la phase Berthelette

```text
1. retrouver une copie officielle ou archivalement fiable du PDF
2. identifier la notice exacte et les droits de l'item
3. vérifier que le PDF correspond bien à SILESR 2002-005
4. inventorier les sections, annexes, tableaux et wordlists
5. identifier toutes les localités présentes
6. définir un schéma RAW qui conserve localité + page + source
7. extraire sans corriger ni normaliser les formes
8. produire un QA technique
9. comparer ensuite avec ASJP / RefLex sans fusion automatique
```

## 7. Schéma RAW envisagé

Le schéma final dépendra du PDF réel. Une entrée lexicale devrait au minimum conserver :

```text
source = Berthelette 2001
report_id = SILESR-2002-005
page
section / table / appendix
locality
variety_claimed_by_source   # seulement si explicitement indiquée
iso_639_3                   # seulement si mapping suffisamment justifié
concept / gloss source
original_form
notes
validation_status = external_unverified
rights_status
```

Si le rapport contient plusieurs locuteurs ou sites pour un même concept, toutes les occurrences doivent être conservées dans le RAW.

## 8. Critères de réussite

La source sera considérée techniquement récoltée seulement si :

```text
PDF/source originale identifiée
provenance conservée
localités conservées
pages/tableaux traçables
aucune variété inventée
aucune déduplication silencieuse
QA technique produit
statut des droits explicite
```

## 9. Sources de reconnaissance utilisées

```text
Glottolog — Berthelette, John 2001, référence 102181
https://glottolog.org/resource/reference/id/102181

SIL Burkina Faso — liste des langues
https://sil-burkina.org/fr/content/toutes-les-langues-du-burkina-faso

SIL Burkina Faso — conditions d'utilisation
https://sil-burkina.org/fr/content/conditions-dutilisation

ASJP — Wordlist Maya Samo
https://asjp.clld.org/languages/MAYA_SAMO
```

## Statut actuel

```text
discovery               = confirmed
bibliographic_reference = confirmed
official_pdf_current    = not_yet_confirmed
item_license            = not_yet_confirmed
ingestion               = not_started
```
