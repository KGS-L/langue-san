<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Langue SAN</title>
    <meta name="description" content="@yield('description', 'Informations publiques du projet Langue SAN.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        body { background: #f8f9fa; color: #273246; }
        .legal-nav { background: #fff; border-bottom: 1px solid #e8ebef; }
        .legal-brand { color: #172640; text-decoration: none; font-weight: 800; display: inline-flex; gap: .6rem; align-items: center; }
        .legal-card { border: 0; border-radius: 18px; box-shadow: 0 8px 30px rgba(23,38,64,.07); }
        .legal-card h2 { color: #172640; font-weight: 800; margin-top: 2rem; font-size: 1.25rem; }
        .legal-card h2:first-of-type { margin-top: 0; }
        .legal-footer { border-top: 1px solid #e8ebef; background: #fff; }
        .legal-footer a { color: #596579; text-decoration: none; }
        .legal-footer a:hover { color: #172640; }
    </style>
</head>
<body>
<nav class="legal-nav py-3">
    <div class="container d-flex justify-content-between align-items-center gap-3">
        <a class="legal-brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt="Logo Langue SAN">
            <span>Langue SAN</span>
        </a>
        <a href="{{ route('contributor.home') }}" class="btn btn-primary">Contribuer</a>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Retour à l’accueil</a>
            </div>
            <article class="card legal-card">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-6 fw-bold text-dark mb-3">@yield('heading')</h1>
                    @yield('content')
                </div>
            </article>
        </div>
    </div>
</main>

<footer class="legal-footer py-4 mt-4">
    <div class="container d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="30" height="30" alt="">
            <strong>Langue SAN</strong>
        </div>
        <div class="d-flex flex-wrap gap-3 small">
            <a href="{{ route('privacy') }}">Confidentialité</a>
            <a href="{{ route('contribution-policy') }}">Politique de contribution</a>
            <a href="{{ route('data-governance') }}">Gouvernance des données</a>
            <a href="https://github.com/KGS-L/langue-san" target="_blank" rel="noopener noreferrer">GitHub</a>
        </div>
    </div>
</footer>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
