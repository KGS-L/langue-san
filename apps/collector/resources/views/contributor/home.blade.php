<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contribuer - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container py-1">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="36" height="36" alt="Logo Langue SAN">
            Langue SAN
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Déconnexion</button></form>
    </div>
</nav>
<main class="container py-5"><div class="row justify-content-center"><div class="col-lg-7"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h3">Bienvenue {{ auth()->user()->name }}</h1><p class="text-muted">Votre compte contributeur est prêt. La prochaine étape ajoutera le consentement, votre localité, le choix du thème et les sessions de 10 mots / phrases.</p><div class="alert alert-info mb-0">Nous ne vous demanderons pas de choisir un nom technique de variété San. Le profil commencera par la localité où vous avez principalement appris ou parlé la langue.</div></div></div></div></div></main>
</body>
</html>
