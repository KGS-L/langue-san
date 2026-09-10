<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contribuer - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
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
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Déconnexion</button></form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Se connecter</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                        <div>
                            <span class="badge text-bg-success mb-2">Aucun compte obligatoire</span>
                            <h1 class="h2 mb-2">Contribuer à Langue SAN</h1>
                            <p class="text-muted mb-0">Vous pouvez répondre au questionnaire directement. Votre progression est associée à un identifiant anonyme enregistré dans ce navigateur.</p>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $profile->public_code }}</span>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Nous ne vous demanderons pas de choisir « Maka », « Matya » ou « Maya ». Nous commencerons par la localité où vous avez principalement appris ou parlé le San.
                    </div>

                    <div class="row g-3 my-2">
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-geo-alt fs-4"></i><h2 class="h6 mt-2">1. Votre contexte</h2><p class="small text-muted mb-0">Localité et quelques informations linguistiques.</p></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-grid fs-4"></i><h2 class="h6 mt-2">2. Votre thème</h2><p class="small text-muted mb-0">Salutations, famille, nombres, marché, etc.</p></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100"><i class="bi bi-mic fs-4"></i><h2 class="h6 mt-2">3. Vos réponses</h2><p class="small text-muted mb-0">10 questions, en texte, en audio ou les deux.</p></div></div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <button class="btn btn-primary btn-lg" disabled><i class="bi bi-arrow-right-circle me-1"></i> Commencer le questionnaire</button>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Créer un compte (facultatif)</a>
                        @endguest
                    </div>
                    <p class="small text-muted mt-2 mb-0">Le parcours de collecte sera branché sur ce bouton à l’étape suivante. Le compte restera facultatif.</p>
                </div>
            </div>

            @guest
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h2 class="h5">Pourquoi créer un compte ?</h2>
                    <p class="text-muted mb-3">Ce n’est pas nécessaire pour contribuer. Un compte servira surtout à retrouver vos statistiques, vos contributions et continuer plus facilement lors de prochaines visites.</p>
                    <div class="d-flex gap-2"><a href="{{ route('register') }}" class="btn btn-outline-primary">Créer mon compte</a><a href="{{ route('login') }}" class="btn btn-light">J’ai déjà un compte</a></div>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h2 class="h5">Votre compte est lié à vos contributions</h2>
                    <p class="text-muted mb-0">Les futures statistiques et l’historique utiliseront le profil <strong>{{ $profile->public_code }}</strong>.</p>
                </div>
            </div>
            @endguest
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
