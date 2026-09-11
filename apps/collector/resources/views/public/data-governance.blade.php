@extends('public.legal-layout')

@section('title', 'Gouvernance des données')
@section('description', 'Principes de gouvernance des données du projet Langue SAN.')
@section('heading', 'Gouvernance des données')

@section('content')
<p class="text-muted">Cette page résume les principes appliqués au collecteur et aux futurs corpus Langue SAN.</p>

<h2>1. Code et données sont séparés</h2>
<p>La licence du code source ne s’applique pas automatiquement aux données linguistiques ni aux enregistrements audio. Chaque publication de dataset doit préciser sa propre licence et ses conditions de réutilisation.</p>

<h2>2. Provenance</h2>
<p>Chaque donnée doit pouvoir être reliée à son prompt ou sujet d’élicitation, son contexte de collecte, sa localité déclarée, son niveau de validation et la version de consentement applicable.</p>

<h2>3. Validation avant export</h2>
<p>Une réponse brute n’entre pas automatiquement dans le corpus d’entraînement. Le projet distingue collecte, transcription, segmentation éventuelle, validation et approbation. Seules les données répondant aux critères du pipeline et non retirées peuvent être exportées.</p>

<h2>4. Variétés linguistiques</h2>
<p>San Maka, San Matya et San Maya restent trois variétés suivies séparément. Le projet ne choisit pas à l’avance une seule variété comme cible définitive : la couverture réelle des données validées déterminera les jeux de données et expériences possibles. Une localité peut fournir une suggestion documentée, mais seule la validation humaine confirme la variété.</p>

<h2>5. Données personnelles</h2>
<p>Le collecteur privilégie des profils pseudonymisés et limite les informations personnelles demandées. Les exports ML ne contiennent pas directement les identifiants de compte, mots de passe, jetons de navigation ou autres données inutiles à l’objectif linguistique.</p>

<h2>6. Conservation des audios</h2>
<ul>
    <li>audio d’une contribution rejetée : suppression automatique après 90 jours ;</li>
    <li>audio resté en attente sans traitement : suppression après 12 mois d’inactivité ;</li>
    <li>audio d’une contribution retenue : stockage privé tant qu’il est utile au projet et couvert par le consentement, sauf demande de suppression.</li>
</ul>

<h2>7. Retrait et correction</h2>
<p>Un contributeur connecté peut demander une correction, supprimer son audio, retirer une contribution ou demander l’anonymisation de son compte. Le retrait d’une contribution supprime immédiatement le contenu linguistique encore stocké et l’audio associé, puis bloque toute inclusion dans les futurs exports. Les autres demandes nécessitant une intervention humaine ont un objectif de traitement sous 30 jours.</p>

<h2>8. Ressources tierces</h2>
<p>Les dictionnaires, applications, livres, fichiers audio et autres ressources externes ne doivent pas être copiés dans le dataset sans vérifier les droits applicables ou obtenir l’autorisation nécessaire.</p>

<h2>9. Publication</h2>
<p>Avant publication d’un dataset, le projet doit vérifier les consentements, pseudonymiser les données, documenter les variétés couvertes, figer les splits train/validation/test, choisir une licence adaptée et publier une data card. L’audio brut reste privé par défaut et exige une autorisation spécifique pour toute publication publique.</p>

<div class="alert alert-light border mt-4 mb-0">
    <strong>Documentation technique :</strong>
    <a href="https://github.com/KGS-L/langue-san/blob/main/docs/DATA_GOVERNANCE.md" target="_blank" rel="noopener noreferrer">consulter DATA_GOVERNANCE.md sur GitHub</a>.
</div>
@endsection
