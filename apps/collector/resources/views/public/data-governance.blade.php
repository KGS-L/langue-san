@extends('public.legal-layout')

@section('title', 'Gouvernance des données')
@section('description', 'Principes de gouvernance des données du projet Langue SAN.')
@section('heading', 'Gouvernance des données')

@section('content')
<p class="text-muted">Cette page résume les principes retenus pour le collecteur. La documentation technique détaillée reste également disponible dans le dépôt open source.</p>

<h2>1. Code et données sont séparés</h2>
<p>La licence du code source ne s’applique pas automatiquement aux données linguistiques ni aux enregistrements audio. Chaque publication de dataset doit préciser sa propre licence et ses conditions de réutilisation.</p>

<h2>2. Provenance</h2>
<p>Chaque donnée doit pouvoir être reliée à son prompt, son contexte de collecte, sa localité déclarée, son niveau de validation et la version de consentement applicable.</p>

<h2>3. Validation avant export</h2>
<p>Une réponse brute n’entre pas automatiquement dans le corpus d’entraînement. Le projet distingue les états de collecte, transcription, validation et approbation. Seules les données répondant aux critères du pipeline peuvent être exportées vers le dataset.</p>

<h2>4. Variétés linguistiques</h2>
<p>Les variétés ne doivent pas être mélangées sans indication. Une localité peut aider à orienter l’analyse, mais elle ne constitue pas à elle seule une preuve suffisante pour attribuer une variété linguistique.</p>

<h2>5. Données personnelles</h2>
<p>Le collecteur privilégie des profils pseudonymisés et limite les informations personnelles demandées. Les exports ML ne doivent pas contenir directement les identifiants de compte, mots de passe, jetons de navigation ou autres données inutiles à l’objectif linguistique.</p>

<h2>6. Ressources tierces</h2>
<p>Les dictionnaires, applications, livres, fichiers audio et autres ressources externes ne doivent pas être copiés dans le dataset sans vérifier les droits applicables ou obtenir l’autorisation nécessaire.</p>

<div class="alert alert-light border mt-4 mb-0">
    <strong>Documentation technique :</strong>
    <a href="https://github.com/KGS-L/langue-san/blob/main/docs/DATA_GOVERNANCE.md" target="_blank" rel="noopener noreferrer">consulter DATA_GOVERNANCE.md sur GitHub</a>.
</div>
@endsection
