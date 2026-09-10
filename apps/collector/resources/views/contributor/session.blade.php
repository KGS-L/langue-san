<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Question {{ $progress['completed'] + 1 }} - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="Logo Langue SAN"> Langue SAN
        </a>
        <span class="badge bg-light text-dark border">{{ $profile->public_code }}</span>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Étape 3 sur 3 · {{ $session->category->name }}</span>
                    <span class="small fw-semibold">Question {{ $progress['completed'] + 1 }} / {{ $progress['total'] }}</span>
                </div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $progress['percent'] }}%"></div></div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                        <div>
                            <span class="badge bg-light text-dark border mb-2">{{ $sessionPrompt->prompt->type->value === 'word' ? 'Mot / concept' : 'Phrase' }}</span>
                            <div class="small text-muted">Comment dites-vous en San ?</div>
                        </div>
                        <span class="small text-muted">{{ $sessionPrompt->prompt->code }}</span>
                    </div>

                    <h1 class="display-6 fw-bold mb-2">{{ $sessionPrompt->prompt->french_text }}</h1>
                    @if($sessionPrompt->prompt->context)
                        <div class="alert alert-light border"><strong>Contexte :</strong> {{ $sessionPrompt->prompt->context }}</div>
                    @endif

                    <p class="small text-muted mb-4"><span class="text-danger">*</span> Donnez au moins une réponse : texte ou audio. Si vous n’êtes pas sûr, vous pouvez passer cette question.</p>

                    <form method="POST" enctype="multipart/form-data" action="{{ route('contributor.sessions.submit', [$session, $sessionPrompt]) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="san_text">Réponse écrite en San</label>
                            <textarea class="form-control" id="san_text" name="san_text" rows="4" maxlength="5000" placeholder="Écrivez votre réponse ici…">{{ old('san_text') }}</textarea>
                            @if(!$profile->can_write_san)
                                <div class="form-text">Vous avez indiqué ne pas bien écrire le San : vous pouvez utiliser seulement l’audio.</div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="audio"><i class="bi bi-mic me-1"></i>Réponse audio</label>
                            <input class="form-control" type="file" id="audio" name="audio" accept="audio/*" capture>
                            <div class="form-text">Maximum 10 Mo. Sur téléphone, votre navigateur peut proposer l’enregistrement directement.</div>
                        </div>

                        <button class="btn btn-primary btn-lg w-100" type="submit">
                            Enregistrer et continuer <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>

                    <form method="POST" class="mt-2" action="{{ route('contributor.sessions.skip', [$session, $sessionPrompt]) }}">
                        @csrf
                        <button class="btn btn-link text-secondary w-100" type="submit">Je ne sais pas / passer cette question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
