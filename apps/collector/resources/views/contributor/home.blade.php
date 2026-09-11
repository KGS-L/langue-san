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
        <div class="col-lg-9">
            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <span class="badge text-bg-success mb-2">Aucun compte obligatoire</span>
                    <h1 class="h2 mb-2">Contribuer à Langue SAN</h1>
                    <p class="text-muted mb-4">Nous collectons deux formes complémentaires de données : des traductions ciblées et de la parole naturelle en San.</p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-4 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-translate fs-3"></i><span class="badge bg-light text-dark border">Français → San</span></div>
                                <h2 class="h4">Traduire des mots et phrases</h2>
                                <p class="text-muted">Une session courte de 10 questions : généralement 7 mots ou expressions et 3 phrases du quotidien.</p>
                                <a href="{{ route('contributor.context.edit', ['next' => 'translation']) }}" class="btn btn-primary w-100">Commencer une session</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-4 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-mic-fill fs-3"></i><span class="badge bg-light text-dark border">San → San</span></div>
                                <h2 class="h4">Parler naturellement en San</h2>
                                <p class="text-muted">Choisissez un sujet et racontez librement pendant environ 2 à 5 minutes : histoire, tradition, souvenir, activité ou récit.</p>
                                <a href="{{ route('contributor.natural-speech.index') }}" class="btn btn-outline-primary w-100">Choisir un sujet</a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-4 mb-0">
                        <strong>Pourquoi les deux ?</strong>
                        <span class="text-muted">Les traductions donnent des paires français–San contrôlées. Les récits naturels permettent aussi de documenter la façon dont le San s’organise spontanément, sans calquer systématiquement la structure du français.</span>
                    </div>

                    @guest
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <a href="{{ route('contributor.auth.show') }}" class="btn btn-outline-primary">Créer ou retrouver mon compte</a>
                        </div>
                    @endguest
                    <p class="small text-muted mt-3 mb-0">Vous pouvez commencer sans compte. Si vous vous connectez plus tard depuis ce navigateur, vos contributions déjà enregistrées seront rattachées automatiquement à votre compte.</p>
                </div>
            </div>

            @guest
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h2 class="h5">Pourquoi créer un compte ?</h2>
                        <p class="text-muted mb-3">Pour retrouver vos contributions, vos statistiques et reprendre plus facilement vos participations lors de prochaines visites.</p>
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
