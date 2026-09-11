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
        :root {
            --san-navy:#172640;
            --san-navy-soft:#243b60;
            --san-gold:#d3a84a;
            --san-cream:#f8f5ee;
            --san-muted:#64748b;
            --san-border:#e5e9f0;
        }

        body {
            background:#f7f8fb;
            color:#1f2937;
            font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
        }

        .auth-shell { min-height:100vh; }

        .auth-visual {
            min-height:100vh;
            height:100vh;
            position:sticky;
            top:0;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            padding:24px;
            background:#12336f;
        }

        .auth-visual img {
            position:static;
            display:block;
            width:auto;
            max-width:100%;
            height:auto;
            max-height:calc(100vh - 48px);
            object-fit:contain;
            object-position:center;
            border-radius:24px;
            box-shadow:0 28px 70px rgba(0,0,0,.22);
        }

        .auth-side {
            min-height:100vh;
            background:linear-gradient(180deg,#fbfcfe 0%,#f7f8fb 100%);
        }

        .auth-panel {
            max-width:560px;
            width:100%;
        }

        .brand-link {
            color:var(--san-navy);
            text-decoration:none;
            font-weight:800;
        }

        .brand-link:hover { color:var(--san-navy); }

        .auth-card {
            background:#fff;
            border:1px solid var(--san-border);
            border-radius:24px;
            padding:clamp(1.5rem,3vw,2.2rem);
            box-shadow:0 24px 60px rgba(23,38,64,.08);
        }

        .account-badge {
            color:var(--san-navy);
            background:#f8fafc;
            border:1px solid #dbe2ea;
            font-weight:700;
            padding:.45rem .75rem;
        }

        .auth-title {
            color:var(--san-navy);
            letter-spacing:-.025em;
            line-height:1.08;
        }

        .google-btn {
            position:relative;
            min-height:56px;
            border:1px solid #d9dee7;
            border-radius:14px;
            background:#fff;
            color:#202124;
            font-weight:700;
            box-shadow:0 1px 2px rgba(16,24,40,.04);
            transition:.18s ease;
        }

        .google-btn:not(:disabled):hover {
            background:#f8fafc;
            border-color:#cbd5e1;
            color:#111827;
            box-shadow:0 6px 18px rgba(15,23,42,.07);
            transform:translateY(-1px);
        }

        .google-btn:disabled {
            opacity:1;
            color:#475569;
            background:#fff;
            cursor:not-allowed;
        }

        .google-badge {
            position:absolute;
            right:14px;
            top:50%;
            transform:translateY(-50%);
            font-size:.72rem;
            font-weight:800;
            color:#8a6414;
            background:#fff7dc;
            border:1px solid #f0d88f;
            border-radius:999px;
            padding:.25rem .5rem;
        }

        .divider {
            display:flex;
            align-items:center;
            gap:14px;
            color:#94a3b8;
            font-size:.82rem;
            font-weight:600;
        }

        .divider::before,
        .divider::after {
            content:"";
            height:1px;
            background:#e2e8f0;
            flex:1;
        }

        .form-label {
            color:#334155;
            font-size:.92rem;
            margin-bottom:.5rem;
        }

        .auth-input {
            min-height:54px;
            border-radius:14px;
            border:1px solid #d7dee8;
            padding:.8rem 1rem;
            font-size:1rem;
            box-shadow:none;
        }

        .auth-input:focus {
            border-color:var(--san-navy-soft);
            box-shadow:0 0 0 .22rem rgba(23,38,64,.10);
        }

        .email-hint {
            color:var(--san-muted);
            font-size:.82rem;
            line-height:1.5;
        }

        .primary-auth-btn {
            min-height:54px;
            border:0;
            border-radius:14px;
            background:var(--san-navy);
            color:#fff;
            font-weight:800;
            box-shadow:0 8px 20px rgba(23,38,64,.16);
            transition:.18s ease;
        }

        .primary-auth-btn:hover {
            background:var(--san-navy-soft);
            color:#fff;
            transform:translateY(-1px);
            box-shadow:0 10px 26px rgba(23,38,64,.20);
        }

        .otp-input {
            letter-spacing:.45rem;
            font-size:1.35rem;
            font-weight:800;
            text-align:center;
        }

        .privacy-note {
            font-size:.8rem;
            line-height:1.6;
            color:#64748b;
        }

        .privacy-note a,
        .text-link {
            color:var(--san-navy);
            text-decoration:none;
            font-weight:700;
        }

        .privacy-note a:hover,
        .text-link:hover {
            color:var(--san-navy-soft);
            text-decoration:underline;
            text-underline-offset:3px;
        }

        .secondary-links {
            display:flex;
            flex-wrap:wrap;
            gap:.65rem 1rem;
            padding-top:.25rem;
        }

        .secondary-links a {
            display:inline-flex;
            align-items:center;
            gap:.35rem;
            color:#475569;
            text-decoration:none;
            font-size:.86rem;
            font-weight:700;
        }

        .secondary-links a:hover { color:var(--san-navy); }

        .resend-btn {
            color:var(--san-navy);
            text-decoration:none;
            font-weight:700;
        }

        .resend-btn:hover {
            color:var(--san-navy-soft);
            text-decoration:underline;
            text-underline-offset:3px;
        }

        @media (max-width:1199.98px) {
            .auth-visual { padding:16px; }
            .auth-visual img { max-height:calc(100vh - 32px); }
        }

        @media (max-width:991.98px) {
            .auth-visual { display:none !important; }
            .auth-side { min-height:100vh; }
        }
    </style>
</head>
<body>
<div class="container-fluid px-0 auth-shell">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-6 auth-visual d-none d-lg-flex">
            <img
                src="{{ asset('assets/img/langue-san-auth-illustration.jpg') }}"
                onerror="this.onerror=null;this.src='{{ asset('assets/img/langue-san-auth-illustration.png') }}';"
                alt="Un jeune utilise Langue SAN avec sa grand-mère"
            >
        </div>

        <div class="col-lg-6 auth-side d-flex align-items-center justify-content-center p-4 p-md-5">
            <div class="auth-panel">
                <a class="brand-link d-inline-flex align-items-center gap-2 mb-4" href="{{ route('home') }}">
                    <img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="42" height="42" alt="Logo Langue SAN">
                    <span class="fs-5">Langue SAN</span>
                </a>

                <div class="auth-card">
                    <div class="mb-4">
                        <span class="badge rounded-pill account-badge mb-3">Compte facultatif</span>
                        <h1 class="auth-title display-6 fw-bold mb-3">Retrouvez vos contributions.</h1>
                        <p class="text-muted mb-0">Connectez-vous ou créez votre compte en quelques secondes. Vos réponses déjà envoyées sans compte seront automatiquement rattachées à votre profil.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    @if($googleConfigured)
                        <a href="{{ route('contributor.auth.google.redirect') }}" class="btn google-btn w-100 d-flex align-items-center justify-content-center gap-3 mb-4">
                            <svg width="20" height="20" viewBox="0 0 18 18" aria-hidden="true">
                                <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615Z"/>
                                <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.91-2.258c-.806.54-1.835.858-3.046.858-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A9 9 0 0 0 9 18Z"/>
                                <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.28-1.706V4.962H.957A9 9 0 0 0 0 9c0 1.453.348 2.827.956 4.038l3.007-2.332Z"/>
                                <path fill="#EA4335" d="M9 3.58c1.322 0 2.507.455 3.442 1.346l2.582-2.582C13.463.892 11.426 0 9 0A9 9 0 0 0 .956 4.962l3.007 2.332C4.672 5.165 6.656 3.58 9 3.58Z"/>
                            </svg>
                            <span>Continuer avec Google</span>
                        </a>
                    @else
                        <button class="btn google-btn w-100 d-flex align-items-center justify-content-center gap-3 mb-4" type="button" disabled>
                            <svg width="20" height="20" viewBox="0 0 18 18" aria-hidden="true">
                                <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615Z"/>
                                <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.91-2.258c-.806.54-1.835.858-3.046.858-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A9 9 0 0 0 9 18Z"/>
                                <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.167.28-1.706V4.962H.957A9 9 0 0 0 0 9c0 1.453.348 2.827.956 4.038l3.007-2.332Z"/>
                                <path fill="#EA4335" d="M9 3.58c1.322 0 2.507.455 3.442 1.346l2.582-2.582C13.463.892 11.426 0 9 0A9 9 0 0 0 .956 4.962l3.007 2.332C4.672 5.165 6.656 3.58 9 3.58Z"/>
                            </svg>
                            <span>Continuer avec Google</span>
                            <span class="google-badge">Bientôt</span>
                        </button>
                    @endif

                    <div class="divider mb-4">ou continuer par email</div>

                    @if(!$codeSent)
                        <form method="POST" action="{{ route('contributor.auth.email.send') }}" class="vstack gap-3">
                            @csrf
                            <div>
                                <label class="form-label fw-semibold" for="email">Adresse email</label>
                                <input id="email" type="email" name="email" value="{{ $email }}" class="form-control auth-input" placeholder="vous@exemple.com" required autocomplete="email">
                                <div class="email-hint mt-2"><i class="bi bi-shield-check me-1"></i>Nous vous enverrons un code sécurisé de 8 chiffres. Aucun mot de passe à créer.</div>
                            </div>
                            <button class="btn primary-auth-btn w-100 d-flex align-items-center justify-content-center gap-2" type="submit">
                                <i class="bi bi-envelope-arrow-up"></i>
                                Recevoir le code
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('contributor.auth.email.verify') }}" class="vstack gap-3">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <div>
                                <label class="form-label fw-semibold" for="code">Code de connexion</label>
                                <input id="code" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" name="code" class="form-control auth-input otp-input" placeholder="00000000" required autofocus autocomplete="one-time-code">
                                <div class="email-hint mt-2">Code envoyé à <strong>{{ $email }}</strong>. Il expire après quelques minutes.</div>
                            </div>
                            <button class="btn primary-auth-btn w-100 d-flex align-items-center justify-content-center gap-2" type="submit">
                                <i class="bi bi-arrow-right-circle"></i>
                                Continuer
                            </button>
                        </form>

                        <form method="POST" action="{{ route('contributor.auth.email.send') }}" class="mt-3 text-center">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button class="btn btn-link btn-sm resend-btn" type="submit">Renvoyer un code</button>
                        </form>
                    @endif

                    <p class="privacy-note mt-4 mb-3">En continuant, vous acceptez que ce compte serve à retrouver vos contributions et vos statistiques. Consultez notre <a href="{{ route('privacy') }}">politique de confidentialité</a>.</p>

                    <div class="secondary-links">
                        <a href="{{ route('contributor.home') }}"><i class="bi bi-arrow-left"></i>Continuer sans compte</a>
                        <a href="{{ route('home') }}"><i class="bi bi-house"></i>Retour à l’accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
