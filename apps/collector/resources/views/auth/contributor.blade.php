<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon compte - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        :root { --san-navy:#172640; --san-gold:#d3a84a; --san-cream:#f8f5ee; }
        body { background:#f7f8fb; color:#1f2937; }
        .auth-shell { min-height:100vh; }
        .auth-visual { min-height:620px; position:relative; overflow:hidden; background:var(--san-navy); }
        .auth-visual img { width:100%; height:100%; object-fit:cover; position:absolute; inset:0; }
        .auth-visual::after { content:""; position:absolute; inset:0; background:linear-gradient(180deg,rgba(23,38,64,.04),rgba(23,38,64,.3)); pointer-events:none; }
        .auth-panel { max-width:520px; width:100%; }
        .brand-link { color:var(--san-navy); text-decoration:none; font-weight:800; }
        .google-btn { min-height:54px; border:1px solid #d9dee7; background:#fff; color:#1f2937; font-weight:700; }
        .google-btn:hover { background:#f8fafc; color:#111827; }
        .otp-input { letter-spacing:.45rem; font-size:1.35rem; font-weight:700; text-align:center; }
        .divider { display:flex; align-items:center; gap:12px; color:#94a3b8; font-size:.85rem; }
        .divider::before,.divider::after { content:""; height:1px; background:#e2e8f0; flex:1; }
        .privacy-note { font-size:.82rem; color:#64748b; }
        @media (max-width: 991.98px) { .auth-visual { min-height:300px; } }
    </style>
</head>
<body>
<div class="container-fluid px-0 auth-shell">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-6 auth-visual d-none d-lg-block">
            <img
                src="{{ asset('assets/img/langue-san-auth-illustration.jpg') }}"
                onerror="this.onerror=null;this.src='{{ asset('assets/img/card.jpg') }}';"
                alt="Un jeune utilise Langue SAN avec sa grand-mère"
            >
        </div>

        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5 bg-white">
            <div class="auth-panel">
                <a class="brand-link d-inline-flex align-items-center gap-2 mb-5" href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="42" height="42" alt="Logo Langue SAN">
                    <span class="fs-5">Langue SAN</span>
                </a>

                <div class="mb-4">
                    <span class="badge rounded-pill text-bg-light border mb-3">Compte facultatif</span>
                    <h1 class="display-6 fw-bold mb-2" style="color:var(--san-navy)">Retrouvez vos contributions.</h1>
                    <p class="text-muted mb-0">Connectez-vous ou créez votre compte en quelques secondes. Les réponses déjà faites sans compte seront automatiquement rattachées à votre compte.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                @if($googleConfigured)
                    <a href="{{ route('contributor.auth.google.redirect') }}" class="btn google-btn w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                        <svg width="20" height="20" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615Z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.91-2.258c-.806.54-1.835.858-3.046.858-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A9 9 0 0 0 9 18Z"/><path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.28-1.706V4.962H.957A9 9 0 0 0 0 9c0 1.453.348 2.827.956 4.038l3.007-2.332Z"/><path fill="#EA4335" d="M9 3.58c1.322 0 2.507.455 3.442 1.346l2.582-2.582C13.463.892 11.426 0 9 0A9 9 0 0 0 .956 4.962l3.007 2.332C4.672 5.165 6.656 3.58 9 3.58Z"/></svg>
                        Continuer avec Google
                    </a>
                @else
                    <button class="btn google-btn w-100 d-flex align-items-center justify-content-center gap-2 mb-4" type="button" disabled>
                        <i class="bi bi-google"></i> Connexion Google bientôt disponible
                    </button>
                @endif

                <div class="divider mb-4">ou recevoir un code par email</div>

                @if(!$codeSent)
                    <form method="POST" action="{{ route('contributor.auth.email.send') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label class="form-label fw-semibold" for="email">Adresse email</label>
                            <input id="email" type="email" name="email" value="{{ $email }}" class="form-control form-control-lg" placeholder="vous@gmail.com" required autocomplete="email">
                        </div>
                        <button class="btn btn-primary btn-lg w-100" type="submit">Recevoir mon code à 8 chiffres</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('contributor.auth.email.verify') }}" class="vstack gap-3">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <div>
                            <label class="form-label fw-semibold" for="code">Code reçu par email</label>
                            <input id="code" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" name="code" class="form-control form-control-lg otp-input" placeholder="00000000" required autofocus autocomplete="one-time-code">
                            <div class="form-text">Code envoyé à <strong>{{ $email }}</strong>. Il expire après quelques minutes.</div>
                        </div>
                        <button class="btn btn-primary btn-lg w-100" type="submit">Valider et continuer</button>
                    </form>

                    <form method="POST" action="{{ route('contributor.auth.email.send') }}" class="mt-3 text-center">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button class="btn btn-link btn-sm" type="submit">Renvoyer un nouveau code</button>
                    </form>
                @endif

                <p class="privacy-note mt-4 mb-3">En continuant, vous acceptez que ce compte serve à retrouver vos contributions et vos statistiques. Consultez notre <a href="{{ route('privacy') }}">politique de confidentialité</a>.</p>
                <div class="d-flex flex-wrap gap-3 small">
                    <a href="{{ route('contributor.home') }}">Continuer sans compte</a>
                    <a href="{{ route('home') }}">Retour à l’accueil</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
