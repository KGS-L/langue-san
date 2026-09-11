# Gouvernance des données

Ce document décrit les règles de gestion des données du projet Langue SAN.

## 1. Code open source ≠ données automatiquement ouvertes

Le code de ce dépôt est sous Apache-2.0. Cette licence ne s'applique pas automatiquement :

- aux enregistrements audio ;
- aux traductions collectées ;
- aux métadonnées des contributeurs ;
- aux datasets produits ;
- aux ressources provenant de dictionnaires, livres, applications ou corpus tiers.

Toute publication d'un dataset doit avoir sa propre licence et une provenance claire.

## 2. Consentement versionné

Avant toute contribution réutilisable, le site explique les usages prévus. Le consentement est enregistré avec une version identifiable.

La version 1.1 distingue notamment :

- stockage de la contribution ;
- transcription et validation ;
- constitution d'un corpus ;
- utilisation pour entraînement et évaluation lorsque `allow_training` est autorisé ;
- publication éventuelle du texte ;
- publication éventuelle de l'audio.

**La publication publique de l'audio n'est jamais implicite.** Le consentement courant conserve `allow_audio_publication = false`. Une publication future d'un enregistrement brut nécessitera une autorisation spécifique distincte.

## 3. Minimisation des données personnelles

Le projet collecte seulement ce qui est utile au fonctionnement du collecteur et à l'analyse linguistique.

Un identifiant pseudonyme est utilisé pour le profil de contribution. Les informations de connexion et données de compte ne doivent pas être copiées dans les exports ML.

Les informations linguistiques utiles peuvent inclure :

- localité d'apprentissage ou d'usage ;
- niveau de pratique ;
- capacité à écrire le San ;
- variété validée ;
- contexte de collecte.

## 4. Stockage privé

Les audios bruts et datasets privés restent hors du dépôt GitHub.

Exemples :

```text
apps/collector/storage/app/private/
data/private/
data/raw/
```

Les audios sont servis uniquement par des routes autorisées de l'application.

Avant une collecte à grande échelle, les sauvegardes de base de données et d'audios doivent être protégées et un test de restauration doit être réalisé.

## 5. Durées de conservation

La politique opérationnelle courante est :

- **audio d'une contribution définitivement rejetée : 90 jours** après le dernier changement de statut, puis suppression automatique ;
- **audio d'une contribution `pending` sans aucun traitement depuis 12 mois : suppression automatique** ;
- **audio d'une contribution retenue :** conservation privée tant qu'il reste utile à la traçabilité/validation et couvert par le consentement, sauf demande de suppression ;
- **données de compte :** conservation tant que le compte reste actif ou jusqu'à une demande d'anonymisation/suppression, sous réserve de traces techniques minimales ne contenant plus le contenu linguistique retiré.

Ces durées sont configurables dans `config/data_retention.php`.

La commande :

```bash
php artisan data:purge-expired-audio
```

applique la purge des audios expirés. Elle est planifiée quotidiennement par le scheduler Laravel.

## 6. Cycle de validation

Statuts :

```text
pending
transcribed
validated_once
validated_twice
approved
rejected
```

Une donnée utilisée pour l'entraînement doit être traçable jusqu'à sa provenance, sa version de consentement et son statut de validation.

`validated_twice` représente notamment les cas où deux validations ne concordent pas complètement et où un arbitrage reste nécessaire.

## 7. Variétés linguistiques

Le projet couvre simultanément :

- San Maka / San du Sud (`sbd`) ;
- San Matya (`stj`) ;
- San Maya (`sym`).

Aucune variété unique n'est imposée à l'avance comme cible définitive. La couverture réellement collectée et validée déterminera les datasets et expériences ML raisonnables.

Conserver séparément :

- localité déclarée ;
- suggestion de variété et sa source lorsqu'elle existe ;
- variété validée ;
- validateur ayant confirmé la classification.

Une contribution de variété inconnue peut être conservée pour analyse, mais elle ne doit pas être fusionnée silencieusement avec une variété connue.

## 8. Parole naturelle

La parole naturelle suit une règle distincte de la traduction élicitée.

```text
consigne de discussion
      ↓
audio San naturel
      ↓
transcription San
      ↓
segmentation
      ↓
traduction française par segment
      ↓
validation
```

La consigne française est une métadonnée d'élicitation, jamais la traduction du récit.

Tous les segments d'un même récit gardent le même `source_id` et le même split train/validation/test afin d'éviter une fuite de contenu entre entraînement et évaluation.

## 9. Correction, suppression et retrait

Un contributeur authentifié dispose de la page `/mes-donnees` pour :

- demander une correction ;
- supprimer un enregistrement audio ;
- retirer entièrement une contribution ;
- demander l'anonymisation ou la suppression de ses données de compte ;
- soumettre une autre demande concernant ses données.

### Retrait d'une contribution

Le retrait est appliqué immédiatement dans le collecteur :

1. `withdrawn_at` est renseigné ;
2. l'audio privé est supprimé ;
3. le texte San soumis et la transcription de travail sont supprimés ;
4. les segments naturels sont supprimés ;
5. les validations liées au contenu sont supprimées ;
6. les futurs exports excluent systématiquement la contribution.

La ligne technique de contribution peut rester comme trace minimale de retrait sans contenu linguistique.

### Suppression d'audio

Une demande de suppression d'audio supprime immédiatement le fichier privé et son enregistrement en base, sans obligatoirement retirer le texte de la contribution.

### Correction et anonymisation

Les demandes nécessitant une intervention humaine passent par la file administrateur `Demandes de données`. L'objectif opérationnel est un traitement sous **30 jours**.

### Datasets déjà publiés

Le projet peut garantir l'exclusion des données retirées de ses **futurs exports et futures versions**. Il ne peut pas garantir l'effacement de copies d'un dataset déjà téléchargées ou redistribuées par des tiers avant la demande. Cette limite doit être indiquée clairement au contributeur.

## 10. Ressources tierces

Ne pas copier automatiquement le contenu d'une application, d'un livre ou d'un dictionnaire dans le dataset.

Avant réutilisation, vérifier explicitement le droit de :

- consulter ;
- numériser ;
- transformer ;
- entraîner un modèle ;
- republier ;
- exploiter commercialement, si cela devient pertinent.

## 11. Publication future

Avant toute publication d'un dataset :

- retirer ou pseudonymiser les données personnelles non nécessaires ;
- exclure les contributions retirées ;
- vérifier les consentements ;
- vérifier les licences de toutes les sources ;
- documenter la méthode de collecte ;
- documenter les variétés couvertes et leurs volumes ;
- publier une data card ;
- choisir une licence dataset distincte ;
- versionner le dataset ;
- figer les splits train/validation/test.

## 12. Principe de réversibilité

Le système de collecte doit être conçu de sorte qu'une contribution puisse être retirée sans nécessiter une réinitialisation globale du corpus. Les exports sont reconstruits à partir des données actuellement éligibles plutôt que considérés comme une copie permanente de toute donnée historique.
