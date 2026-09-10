<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Langue SAN — Chaque mot compte</title>
    <meta name="description" content="Contribuez à documenter le San du Burkina Faso en traduisant quelques mots et phrases en texte ou en audio.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/landing.css') }}" rel="stylesheet">
</head>
<body>
@php
    $contributeUrl = auth()->check() && auth()->user()->isStaff()
        ? route('admin.dashboard')
        : route('contributor.home');
@endphp

<nav class="navbar navbar-expand-lg san-navbar fixed-top">
    <div class="container py-2">
        <a class="san-brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" alt="Logo Langue SAN">
            <span>Langue SAN</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sanNav" aria-controls="sanNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="sanNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3 py-3 py-lg-0">
                <li class="nav-item"><a class="nav-link san-nav-link" href="#pourquoi">Pourquoi contribuer ?</a></li>
                <li class="nav-item"><a class="nav-link san-nav-link" href="#fonctionnement">Comment ça marche</a></li>
                <li class="nav-item"><a class="nav-link san-nav-link" href="#themes">Thèmes</a></li>
                @guest
                    <li class="nav-item"><a class="nav-link san-nav-link" href="{{ route('login') }}">Connexion</a></li>
                @endguest
                <li class="nav-item"><a class="btn btn-san-primary" href="{{ $contributeUrl }}">Commencer</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-san">
    <div class="container hero-content">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="hero-kicker"><i class="bi bi-chat-square-text"></i> Une initiative ouverte pour la langue San</span>
                <h1 class="hero-title">Chaque mot compte.<br><span>Faisons vivre le San dans le numérique.</span></h1>
                <p class="hero-lead mt-4">Vous parlez San à Toma, Tougan ou dans une autre localité ? En quelques minutes, vous pouvez traduire des mots et des phrases simples, par écrit, par audio, ou avec les deux.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a class="btn btn-san-primary btn-lg" href="{{ $contributeUrl }}"><i class="bi bi-arrow-right-circle me-2"></i>Commencer à contribuer</a>
                    <a class="btn btn-san-outline btn-lg" href="#fonctionnement">Voir comment ça marche</a>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-4 text-secondary small">
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Aucun compte obligatoire</span>
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Texte ou audio</span>
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Contribution validée humainement</span>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div><div class="fw-bold text-dark">Exemple de session</div><small class="text-muted">Salutations · 3/10</small></div>
                        <span class="badge rounded-pill text-bg-light">~ 3 min</span>
                    </div>
                    <div class="prompt-preview d-flex gap-3 align-items-start">
                        <div class="prompt-num">1</div>
                        <div class="flex-grow-1"><small class="text-muted">Comment dites-vous en San ?</small><div class="fw-bold fs-5">Bonjour</div><span class="audio-pill mt-2"><i class="bi bi-mic"></i> Répondre par audio</span></div>
                    </div>
                    <div class="prompt-preview d-flex gap-3 align-items-start">
                        <div class="prompt-num">2</div>
                        <div><small class="text-muted">Comment dites-vous en San ?</small><div class="fw-bold">Comment vas-tu ?</div></div>
                    </div>
                    <div class="progress mt-4" role="progressbar" aria-label="Progression" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="height:8px"><div class="progress-bar" style="width:30%; background:#d3a84a"></div></div>
                    <small class="text-muted d-block mt-2">Vous pouvez passer une question si vous n’êtes pas sûr.</small>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="pourquoi" class="section-san">
    <div class="container">
        <div class="row mb-4"><div class="col-lg-7"><div class="section-kicker">Pourquoi votre voix compte</div><h2 class="section-title display-6 mt-2">Construire une ressource utile avec celles et ceux qui parlent réellement la langue.</h2><p class="section-copy">L’objectif n’est pas de remplacer les locuteurs ni d’inventer une manière de parler. Les contributions sont conservées avec leur contexte puis relues et validées avant d’alimenter les ressources du projet.</p></div></div>
        <div class="row g-4">
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-archive"></i></div><h3 class="h5 fw-bold">Documenter</h3><p class="section-copy mb-0">Créer progressivement un corpus de mots, phrases et voix San correctement contextualisés.</p></div></div>
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-translate"></i></div><h3 class="h5 fw-bold">Préparer la traduction</h3><p class="section-copy mb-0">Constituer les données nécessaires à de futurs outils Français ↔ San sans mélanger les variétés.</p></div></div>
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-book"></i></div><h3 class="h5 fw-bold">Transmettre</h3><p class="section-copy mb-0">Réutiliser plus tard les contenus validés dans une application d’apprentissage et de découverte.</p></div></div>
        </div>
    </div>
</section>

<section id="fonctionnement" class="section-san section-soft">
    <div class="container">
        <div class="text-center mx-auto mb-5" style="max-width:720px"><div class="section-kicker">Simple et rapide</div><h2 class="section-title display-6 mt-2">Comment contribuer ?</h2><p class="section-copy">Pas besoin de créer un compte ni de connaître le nom technique de votre variété de San. Nous vous demandons des informations concrètes comme la localité où vous avez appris ou principalement parlé la langue.</p></div>
        <div class="row g-4">
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">1</div><h3 class="h5 fw-bold">Donner votre contexte</h3><p class="section-copy mb-0">Quelques informations minimales pour contextualiser vos réponses, sans inscription obligatoire.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">2</div><h3 class="h5 fw-bold">Choisir un thème</h3><p class="section-copy mb-0">Salutations, présentation, famille, nombres, marché et autres sujets.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">3</div><h3 class="h5 fw-bold">Répondre à 10 questions</h3><p class="section-copy mb-0">Écrivez en San, enregistrez votre voix, ou utilisez les deux.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">4</div><h3 class="h5 fw-bold">Validation</h3><p class="section-copy mb-0">Des personnes compétentes relisent, transcrivent et valident les contributions.</p></div></div>
        </div>
    </div>
</section>

<section id="themes" class="section-san">
    <div class="container">
        <div class="row align-items-end mb-4"><div class="col-lg-7"><div class="section-kicker">Vous choisissez</div><h2 class="section-title display-6 mt-2">Commencez par un thème qui vous parle.</h2></div><div class="col-lg-5"><p class="section-copy mb-0">Le système vous proposera ensuite les questions qui ont le plus besoin de nouvelles contributions.</p></div></div>
        <div class="row g-3">
            @foreach ([['bi-chat-heart','Salutations'],['bi-person-badge','Présentation'],['bi-people','Famille'],['bi-123','Nombres'],['bi-shop','Marché / commerce'],['bi-signpost','Déplacements']] as [$icon,$label])
                <div class="col-6 col-md-4 col-lg-2"><div class="theme-card text-center"><div class="icon-box mx-auto mb-3"><i class="bi {{ $icon }}"></i></div><div class="fw-bold">{{ $label }}</div></div></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-san pt-0">
    <div class="container">
        <div class="trust-panel">
            <div class="row g-4 align-items-center position-relative" style="z-index:2">
                <div class="col-lg-5"><div class="section-kicker" style="color:#d3a84a">Vous pouvez aussi parler</div><h2 class="display-6 fw-bold mt-2">Vous parlez San mais vous ne savez pas bien l’écrire ?</h2><p class="mb-0">Votre contribution reste précieuse. Enregistrez simplement votre réponse. Un validateur pourra ensuite l’écouter et la transcrire.</p></div>
                <div class="col-lg-7"><div class="row g-3"><div class="col-md-6"><div class="trust-item"><i class="bi bi-mic-fill"></i><div><strong>Audio accepté</strong><p class="mb-0 small">Le texte n’est pas obligatoire si vous préférez répondre oralement.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-shield-check"></i><div><strong>Validation humaine</strong><p class="mb-0 small">Les réponses ne deviennent pas automatiquement des données d’entraînement.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-geo-alt-fill"></i><div><strong>Localité conservée</strong><p class="mb-0 small">Le contexte géographique aide à ne pas mélanger les usages.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-lock-fill"></i><div><strong>Audio privé</strong><p class="mb-0 small">Les enregistrements sont stockés dans un espace privé et soumis au consentement.</p></div></div></div></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section-san pt-0">
    <div class="container"><div class="cta-san text-center"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="64" height="64" alt="" class="mb-3"><h2 class="section-title display-6">Prêt à partager quelques mots ?</h2><p class="section-copy mx-auto" style="max-width:650px">Une session contient environ 10 questions. Vous pouvez participer sans compte et créer un compte plus tard si vous souhaitez retrouver vos statistiques et vos contributions.</p><a class="btn btn-san-primary btn-lg mt-2" href="{{ $contributeUrl }}">Commencer maintenant <i class="bi bi-arrow-right ms-1"></i></a></div></div>
</section>

<footer class="san-footer"><div class="container d-flex flex-column flex-md-row justify-content-between gap-2"><div class="d-flex align-items-center gap-2"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="30" height="30" alt=""><strong class="text-dark">Langue SAN</strong><span>— projet open source</span></div><div>Collecter · Valider · Transmettre</div></div></footer>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
