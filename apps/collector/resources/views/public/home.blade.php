<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Langue SAN — Préservons chaque voix</title>
    <meta name="description" content="Partagez votre voix et vos mots en San pour aider à préserver la langue et construire de futurs outils de traduction et d’apprentissage.">
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
                    <li class="nav-item"><a class="nav-link san-nav-link" href="{{ route('contributor.auth.show') }}">Mon compte</a></li>
                @endguest
                <li class="nav-item"><a class="btn btn-san-primary" href="{{ $contributeUrl }}">Contribuer</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-san">
    <div class="container hero-content">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-kicker"><i class="bi bi-mic-fill"></i> Nos voix aujourd’hui, notre langue demain</span>
                <h1 class="hero-title">Préservons le San,<br><span>une voix à la fois.</span></h1>
                <p class="hero-lead mt-4">Le San vit dans les familles, les villages et les conversations du quotidien. En partageant quelques mots ou phrases avec votre voix, vous nous aidez à préserver cette richesse et à préparer de futurs outils qui permettront de traduire, apprendre et transmettre la langue.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a class="btn btn-san-primary btn-lg" href="{{ $contributeUrl }}"><i class="bi bi-mic-fill me-2"></i>Partager ma voix</a>
                    <a class="btn btn-san-outline btn-lg" href="#fonctionnement">Découvrir le projet</a>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-4 text-secondary small">
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Aucun compte obligatoire</span>
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Audio recommandé</span>
                    <span><i class="bi bi-check-circle-fill me-1 text-success"></i> Validation humaine</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-real-card">
                    <img
                        class="hero-real-image"
                        src="{{ asset('assets/img/langue-san-hero.jpg') }}"
                        onerror="this.onerror=null;this.src='{{ asset('assets/img/card.jpg') }}';"
                        alt="Un jeune échange avec sa grand-mère autour d’un outil vocal en San"
                    >
                    <div class="hero-real-caption">
                        <span><i class="bi bi-translate me-1"></i> Français ↔ San</span>
                        <strong>Une technologie construite à partir des voix de la communauté.</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="pourquoi" class="section-san">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="section-kicker">Pourquoi votre voix compte</div>
                <h2 class="section-title display-6 mt-2">Une langue se préserve d’abord avec celles et ceux qui la parlent.</h2>
                <p class="section-copy">Chaque contribution ajoute une petite pièce à une ressource commune : des mots, des phrases et des voix reliés à leur contexte, puis vérifiés avant d’être réutilisés.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-mic-fill"></i></div><h3 class="h5 fw-bold">Préserver les voix</h3><p class="section-copy mb-0">Documenter la manière dont le San est réellement parlé aujourd’hui, notamment par celles et ceux qui l’écrivent peu.</p></div></div>
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-translate"></i></div><h3 class="h5 fw-bold">Préparer la traduction</h3><p class="section-copy mb-0">Construire progressivement les données nécessaires à de futurs outils Français ↔ San utiles au quotidien.</p></div></div>
            <div class="col-md-4"><div class="value-card"><div class="icon-box mb-3"><i class="bi bi-people"></i></div><h3 class="h5 fw-bold">Transmettre entre générations</h3><p class="section-copy mb-0">Créer demain des outils d’apprentissage et de découverte qui rapprochent les jeunes, les familles et leur langue.</p></div></div>
        </div>
    </div>
</section>

<section id="fonctionnement" class="section-san section-soft">
    <div class="container">
        <div class="text-center mx-auto mb-5" style="max-width:760px">
            <div class="section-kicker">Simple et rapide</div>
            <h2 class="section-title display-6 mt-2">Comment contribuer ?</h2>
            <p class="section-copy">Quelques minutes suffisent. Vous pouvez participer sans compte, avec votre téléphone ou votre ordinateur.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">1</div><h3 class="h5 fw-bold">Votre contexte</h3><p class="section-copy mb-0">Indiquez où vous avez principalement appris ou parlé le San et votre niveau de pratique.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">2</div><h3 class="h5 fw-bold">Choisissez un thème</h3><p class="section-copy mb-0">Famille, salutations, marché, école, travail et d’autres sujets du quotidien.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">3</div><h3 class="h5 fw-bold">Parlez naturellement</h3><p class="section-copy mb-0">Enregistrez votre réponse avec le micro. Vous pouvez aussi écrire si vous le souhaitez.</p></div></div>
            <div class="col-md-3"><div class="step-card"><div class="step-number mb-3">4</div><h3 class="h5 fw-bold">Nous vérifions</h3><p class="section-copy mb-0">Les réponses sont relues, transcrites et validées humainement avant leur utilisation.</p></div></div>
        </div>
    </div>
</section>

<section id="themes" class="section-san">
    <div class="container">
        <div class="row align-items-end mb-4">
            <div class="col-lg-7"><div class="section-kicker">À votre rythme</div><h2 class="section-title display-6 mt-2">Commencez par ce que vous connaissez le mieux.</h2></div>
            <div class="col-lg-5"><p class="section-copy mb-0">Une session comporte environ 10 petites questions. Vous pouvez en faire une seule aujourd’hui et revenir plus tard.</p></div>
        </div>
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
                <div class="col-lg-5"><div class="section-kicker" style="color:#d3a84a">Votre voix suffit</div><h2 class="display-6 fw-bold mt-2">Vous parlez San mais vous ne savez pas bien l’écrire ?</h2><p class="mb-0">C’est justement pour cela que l’audio est au cœur du projet. Parlez naturellement : la transcription pourra être faite et vérifiée ensuite.</p></div>
                <div class="col-lg-7"><div class="row g-3"><div class="col-md-6"><div class="trust-item"><i class="bi bi-mic-fill"></i><div><strong>Enregistrement direct</strong><p class="mb-0 small">Le microphone du navigateur permet de répondre sans chercher un fichier.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-shield-check"></i><div><strong>Validation humaine</strong><p class="mb-0 small">Une réponse n’est jamais considérée comme correcte automatiquement.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-arrow-repeat"></i><div><strong>Reprenez plus tard</strong><p class="mb-0 small">Votre progression est conservée afin que vous puissiez reprendre votre session.</p></div></div></div><div class="col-md-6"><div class="trust-item"><i class="bi bi-lock-fill"></i><div><strong>Audio privé</strong><p class="mb-0 small">Les enregistrements sont protégés et soumis à votre consentement.</p></div></div></div></div></div>
            </div>
        </div>
    </div>
</section>

<section class="section-san pt-0">
    <div class="container">
        <div class="cta-san text-center">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="64" height="64" alt="" class="mb-3">
            <h2 class="section-title display-6">Votre voix peut aider le San à entrer dans le numérique.</h2>
            <p class="section-copy mx-auto" style="max-width:680px">Participez maintenant sans créer de compte. Si vous le souhaitez ensuite, un compte vous permettra de retrouver vos contributions et vos statistiques.</p>
            <a class="btn btn-san-primary btn-lg mt-2" href="{{ $contributeUrl }}"><i class="bi bi-mic-fill me-2"></i>Commencer maintenant</a>
        </div>
    </div>
</section>

@include('public.partials.footer')

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
