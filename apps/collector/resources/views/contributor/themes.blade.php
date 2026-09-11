<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choisir un thème - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        .theme-option {
            cursor: pointer;
            border: 2px solid #e9ecef !important;
            transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .theme-option:hover {
            border-color: #9ec5fe !important;
            transform: translateY(-1px);
        }
        .theme-check {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            border: 2px solid #ced4da;
            color: transparent;
            background: #fff;
            flex: 0 0 auto;
            transition: all .18s ease;
        }
        .btn-check:checked + .theme-option {
            border-color: #0d6efd !important;
            background: #eef5ff !important;
            box-shadow: 0 0 0 .18rem rgba(13, 110, 253, .10);
        }
        .btn-check:checked + .theme-option .theme-check {
            border-color: #0d6efd;
            background: #0d6efd;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN"> Langue SAN
        </a>
        <a href="{{ route('contributor.context.edit') }}" class="btn btn-outline-secondary btn-sm">Modifier mes informations</a>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <div class="mb-2">
                    <span class="small text-muted">Étape 2 sur 3</span>
                </div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:66%"></div></div>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-2">Choisissez un thème</h1>
                    <p class="text-muted mb-4">Touchez simplement le thème qui vous intéresse. Nous vous proposerons ensuite jusqu’à 10 questions.</p>

                    <form method="POST" action="{{ route('contributor.themes.store') }}">
                        @csrf
                        <div class="row g-3 mb-4">
                            @foreach($categories as $category)
                                <div class="col-md-6">
                                    <input class="btn-check" type="radio" name="category_id" id="category_{{ $category->id }}" value="{{ $category->id }}" @checked((string) old('category_id') === (string) $category->id) required>
                                    <label class="theme-option rounded-3 p-3 w-100 h-100 d-flex gap-3 align-items-center bg-white" for="category_{{ $category->id }}">
                                        <span class="fs-4"><i class="{{ $category->icon ?: 'bi bi-chat-square-text' }}"></i></span>
                                        <span class="flex-grow-1">
                                            <strong class="d-block">{{ $category->name }}</strong>
                                            <small class="text-muted">{{ $category->active_prompts_count }} question(s) disponible(s)</small>
                                        </span>
                                        <span class="theme-check" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="border rounded-3 p-3 p-md-4 bg-light mb-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-check fs-4 text-success"></i>
                                <div class="flex-grow-1">
                                    <h2 class="h6 fw-bold mb-2">Avant de commencer <span class="text-danger">*</span></h2>
                                    @if($consentVersion)
                                        <p class="small text-muted mb-3">{{ $consentVersion->content }}</p>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" @checked(old('consent', $hasAcceptedConsent)) required>
                                            <label class="form-check-label" for="consent">J’ai lu et j’accepte l’utilisation de mes réponses dans les conditions indiquées ci-dessus.</label>
                                        </div>
                                        <div class="small mt-2">
                                            <a href="{{ route('privacy') }}" target="_blank">Confidentialité</a>
                                            <span class="text-muted mx-1">·</span>
                                            <a href="{{ route('data-governance') }}" target="_blank">Gouvernance des données</a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">Le questionnaire est temporairement indisponible. Veuillez réessayer un peu plus tard.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button class="btn btn-primary btn-lg" type="submit" @disabled(!$consentVersion)>
                                Démarrer les questions <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <a href="{{ route('contributor.context.edit') }}" class="btn btn-light btn-lg">Retour</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
