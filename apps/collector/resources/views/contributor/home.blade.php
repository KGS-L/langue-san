<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contribuer - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN">
            Langue SAN
        </a>
        <div class="d-flex gap-2 align-items-center">
            @auth
                <span class="small text-muted d-none d-md-inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('contributor.logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Déconnexion</button></form>
            @else
                <a href="{{ route('contributor.auth.show') }}" class="btn btn-outline-secondary btn-sm">Mon compte</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <span class="badge text-bg-success mb-2">Aucun compte obligatoire</span>
                    <h1 class="h2 mb-2">Contribuer à Langue SAN</h1>
                    <p class="text-muted mb-4">Partagez quelques mots ou phrases en San. Votre voix est recommandée, mais vous pouvez également écrire vos réponses.</p>

                    <div class="row g-3 my-2">
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-geo-alt fs-4"></i><h2 class="h6 mt-2">1. Votre contexte</h2><p class="small text-muted mb-0">Localité et quelques informations sur votre pratique du San.</p></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-grid fs-4"></i><h2 class="h6 mt-2">2. Votre thème</h2><p class="small text-muted mb-0">Salutations, famille, nombres, marché, etc.</p></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-mic-fill fs-4"></i><h2 class="h6 mt-2">3. Vos réponses</h2><p class="small text-muted mb-0">Enregistrez votre voix et, si vous le souhaitez, ajoutez aussi le texte.</p></div></div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <a href="{{ route('contributor.context.edit') }}" class="btn btn-primary btn-lg"><i class="bi bi-arrow-right-circle me-1"></i> Commencer le questionnaire</a>
                        @guest
                            <a href="{{ route('contributor.auth.show') }}" class="btn btn-outline-primary btn-lg">Créer ou retrouver mon compte</a>
                        @endguest
                    </div>
                    <p class="small text-muted mt-2 mb-0">Vous pouvez commencer sans compte. Si vous vous connectez plus tard depuis ce navigateur, vos contributions déjà enregistrées seront rattachées automatiquement à votre compte.</p>
                </div>
            </div>

            @guest
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h2 class="h5">Pourquoi créer un compte ?</h2>
                        <p class="text-muted mb-3">Pour retrouver vos contributions, vos futures statistiques et reprendre plus facilement vos participations sur d’autres visites.</p>
                        <a href="{{ route('contributor.auth.show') }}" class="btn btn-outline-primary">Mon compte</a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h2 class="h5">Vos contributions sont liées à votre compte</h2>
                        <p class="text-muted mb-0">Les réponses que vous envoyez maintenant seront conservées dans votre historique.</p>
                    </div>
                </div>
            @endguest
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
