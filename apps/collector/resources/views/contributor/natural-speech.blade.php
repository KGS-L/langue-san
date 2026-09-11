<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parole naturelle - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        .speech-topic { cursor: pointer; transition: .15s ease; }
        .speech-topic:has(input:checked) { border-color: #172640 !important; box-shadow: 0 0 0 3px rgba(23, 38, 64, .08); }
    </style>
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN"> Langue SAN
        </a>
        <a href="{{ route('contributor.home') }}" class="btn btn-outline-primary btn-sm">Retour</a>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <span class="badge text-bg-success mb-2">San → San</span>
                <h1 class="display-6 fw-bold">Parler naturellement en San</h1>
                <p class="lead text-muted">Choisissez un sujet puis racontez librement, comme vous le feriez avec un proche. Il ne s’agit pas de traduire la consigne française mot à mot.</p>
            </div>

            <div class="alert alert-light border mb-4">
                <div class="d-flex gap-3">
                    <i class="bi bi-mic-fill fs-3"></i>
                    <div>
                        <strong>Objectif : 2 à 5 minutes de parole naturelle.</strong>
                        <div class="small text-muted mt-1">Votre audio sera conservé de manière privée, puis transcrit en San, découpé en phrases et traduit en français par l’équipe avant validation.</div>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            @if($prompts->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                        <h2 class="h4 mt-3">Vous avez déjà répondu aux sujets disponibles</h2>
                        <p class="text-muted">Merci. De nouveaux sujets seront proposés progressivement.</p>
                        <a href="{{ route('contributor.home') }}" class="btn btn-primary">Retour aux contributions</a>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('contributor.natural-speech.store') }}">
                    @csrf
                    <div class="row g-3">
                        @foreach($prompts as $prompt)
                            <div class="col-md-6">
                                <label class="speech-topic card border h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex gap-3 align-items-start">
                                            <input class="form-check-input mt-1" type="radio" name="prompt_id" value="{{ $prompt->id }}" @checked(old('prompt_id') == $prompt->id)>
                                            <div>
                                                <span class="badge bg-light text-dark border mb-2">{{ $prompt->category->name }}</span>
                                                <p class="fw-semibold mb-0">{{ $prompt->french_text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body p-4">
                            <h2 class="h5">Consentement</h2>
                            <p class="small text-muted">{{ $consentVersion?->content }}</p>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="consent" name="consent" @checked(old('consent') || $hasAcceptedConsent)>
                                <label class="form-check-label" for="consent">J’ai compris et j’accepte ces conditions pour cette contribution.</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Continuer vers l’enregistrement <i class="bi bi-mic-fill ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
