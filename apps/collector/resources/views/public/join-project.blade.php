<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rejoindre le projet - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/landing.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom py-3"><div class="container"><a class="san-brand" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" alt=""><span>Langue SAN</span></a><a href="{{ route('community') }}" class="btn btn-light">La communauté</a></div></nav>

<main class="container py-5" style="max-width:980px">
    <div class="text-center mx-auto mb-5" style="max-width:760px"><span class="badge rounded-pill text-bg-light border mb-3">Projet open source & communautaire</span><h1 class="display-5 fw-bold" style="color:#172640">Apportez votre expérience au projet Langue SAN.</h1><p class="lead text-muted">Nous cherchons des personnes capables d’aider sur la langue, la recherche, le logiciel, l’IA, la transcription, la gouvernance des données ou la mobilisation communautaire.</p></div>

    @if($application && in_array($application->status->value, ['pending','under_review','approved'], true))
        <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center"><i class="bi bi-hourglass-split fs-1 text-warning"></i><h2 class="h3 mt-3">Candidature : {{ $application->status->label() }}</h2><p class="text-muted">Vous avez déjà soumis une candidature. Vous pouvez suivre son évolution depuis votre espace contributeur.</p><a href="{{ route('contributor.dashboard') }}" class="btn btn-primary">Voir mon espace</a></div></div>
    @elseif(!$user)
        <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center"><i class="bi bi-person-check fs-1" style="color:#172640"></i><h2 class="h3 mt-3">Connectez-vous avant de candidater</h2><p class="text-muted mx-auto" style="max-width:620px">Votre candidature sera liée à votre compte afin que vous puissiez suivre la décision et recevoir la réponse de l’équipe. La connexion contributeur se fait avec Google ou un code envoyé par email.</p><a href="{{ route('contributor.auth.show') }}" class="btn btn-primary btn-lg">Se connecter / créer mon compte</a></div></div>
    @elseif(!$user->isContributor())
        <div class="alert alert-info">Les membres de l’équipe disposent déjà d’un accès interne au projet.</div>
    @elseif(!$profileComplete)
        <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center"><h2 class="h3">Complétez d’abord votre profil</h2><p class="text-muted">Votre profession et votre pays de résidence nous aident à comprendre votre candidature sans vous demander un CV.</p><a href="{{ route('contributor.profile.edit') }}" class="btn btn-primary">Compléter mon profil</a></div></div>
    @else
        @if($application?->status->value === 'rejected')
            <div class="alert alert-light border mb-4"><strong>Votre précédente candidature n’a pas été retenue.</strong>@if($application->decision_reason)<div class="mt-1 text-muted">{{ $application->decision_reason }}</div>@endif<div class="small mt-2">Vous pouvez soumettre une nouvelle candidature si votre situation ou votre proposition a évolué.</div></div>
        @endif

        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5">
            <h2 class="h3 fw-bold mb-2">Votre candidature</h2><p class="text-muted mb-4">Pas de CV obligatoire. Dites-nous simplement ce que vous savez faire, ce que vous souhaitez apporter et le temps que vous pouvez consacrer.</p>
            <form method="POST" action="{{ route('project.join.store') }}" class="row g-4">@csrf
                <div class="col-12"><label class="form-label fw-semibold">Dans quels domaines souhaitez-vous contribuer ? <span class="text-danger">*</span></label><div class="row g-2">
                    @foreach($areas as $area)
                        <div class="col-md-6"><label class="border rounded-3 p-3 w-100 h-100 d-flex gap-2 align-items-start bg-white"><input class="form-check-input mt-1" type="checkbox" name="contribution_areas[]" value="{{ $area->value }}" @checked(in_array($area->value, old('contribution_areas', []), true))><span>{{ $area->label() }}</span></label></div>
                    @endforeach
                </div><div class="form-text">Choisissez jusqu’à 5 domaines.</div></div>
                <div class="col-12"><label class="form-label fw-semibold" for="experience">Votre expérience <span class="text-danger">*</span></label><textarea id="experience" name="experience" class="form-control" rows="5" required maxlength="3000" placeholder="Parlez-nous de vos compétences, projets, recherches ou expériences utiles…">{{ old('experience') }}</textarea></div>
                <div class="col-12"><label class="form-label fw-semibold" for="motivation">Pourquoi souhaitez-vous rejoindre le projet ? <span class="text-danger">*</span></label><textarea id="motivation" name="motivation" class="form-control" rows="5" required maxlength="3000">{{ old('motivation') }}</textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold" for="availability">Disponibilité approximative</label><select id="availability" name="availability" class="form-select"><option value="">Non précisée</option><option value="lt2" @selected(old('availability')==='lt2')>Moins de 2 h / semaine</option><option value="2-5" @selected(old('availability')==='2-5')>2 à 5 h / semaine</option><option value="5-10" @selected(old('availability')==='5-10')>5 à 10 h / semaine</option><option value="10plus" @selected(old('availability')==='10plus')>Plus de 10 h / semaine</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold" for="portfolio_url">GitHub, LinkedIn ou portfolio</label><input type="url" id="portfolio_url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url') }}" placeholder="https://..."></div>
                <div class="col-12"><label class="form-label fw-semibold" for="san_connection">Lien avec le San ou avec les communautés concernées <span class="text-muted fw-normal">(facultatif)</span></label><textarea id="san_connection" name="san_connection" class="form-control" rows="3" maxlength="1500" placeholder="Ex. locuteur, famille, travail de terrain, réseau associatif…">{{ old('san_connection') }}</textarea></div>
                <div class="col-12"><button class="btn btn-primary btn-lg">Envoyer ma candidature</button></div>
            </form>
        </div></div>
    @endif
</main>
@include('public.partials.footer')
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body></html>
