<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Merci - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5 min-vh-100 d-flex align-items-center">
    <div class="row justify-content-center w-100">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-md-5">
                    <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="72" height="72" alt="Logo Langue SAN" class="mb-3">
                    <div class="text-success fs-1 mb-2"><i class="bi bi-check-circle-fill"></i></div>
                    <h1 class="h2">Merci pour votre contribution !</h1>
                    <p class="text-muted">Votre session « {{ $session->category->name }} » est terminée. Les réponses seront relues et validées avant toute utilisation dans les ressources linguistiques du projet.</p>

                    <div class="row g-3 my-4">
                        <div class="col-4"><div class="border rounded p-3"><strong class="fs-4 d-block">{{ $progress['answered'] }}</strong><small class="text-muted">réponses</small></div></div>
                        <div class="col-4"><div class="border rounded p-3"><strong class="fs-4 d-block">{{ $progress['skipped'] }}</strong><small class="text-muted">passées</small></div></div>
                        <div class="col-4"><div class="border rounded p-3"><strong class="fs-4 d-block">{{ $progress['total'] }}</strong><small class="text-muted">questions</small></div></div>
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="{{ route('contributor.themes.index') }}" class="btn btn-primary btn-lg">Contribuer à un autre thème</a>
                        <a href="{{ route('home') }}" class="btn btn-light btn-lg">Retour à l’accueil</a>
                    </div>

                    @guest
                        <div class="border-top mt-4 pt-4">
                            <p class="small text-muted mb-2">Vous pouvez créer un compte pour retrouver plus facilement vos contributions et vos futures statistiques.</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">Créer un compte (facultatif)</a>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
