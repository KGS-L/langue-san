<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        :root{--san-navy:#172640;--san-gold:#d3a84a;--san-cream:#f8f5ee}
        body{background:#f7f8fb;color:#1f2937}.brand{color:var(--san-navy);font-weight:800;text-decoration:none}.card{border-radius:18px}.btn-san{background:var(--san-navy);border-color:var(--san-navy);color:#fff}.btn-san:hover{background:#243b60;color:#fff}.section-note{background:var(--san-cream);border:1px solid #eee7d7;border-radius:14px}
    </style>
</head>
<body>
<nav class="navbar bg-white border-bottom py-3">
    <div class="container">
        <a class="brand d-flex align-items-center gap-2" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt=""><span>Langue SAN</span></a>
        <form method="POST" action="{{ route('contributor.logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Déconnexion</button></form>
    </div>
</nav>

<main class="container py-5" style="max-width:900px">
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="mb-4">
        <span class="badge rounded-pill text-bg-light border mb-2">Profil contributeur</span>
        <h1 class="fw-bold mb-2" style="color:var(--san-navy)">{{ $profile->isComplete() ? 'Mettre à jour mon profil' : 'Quelques informations avant de continuer' }}</h1>
        <p class="text-muted mb-0">Ces informations nous aident à mieux comprendre la communauté qui construit le projet. Nous demandons seulement ce qui est réellement utile.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4 p-md-5">
        <form method="POST" action="{{ route('contributor.profile.update') }}" class="row g-4">
            @csrf
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="name">Nom et prénom <span class="text-danger">*</span></label>
                <input id="name" name="name" class="form-control form-control-lg" value="{{ old('name', auth()->user()->name) }}" required maxlength="120">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="country">Pays de résidence <span class="text-danger">*</span></label>
                <input id="country" name="country" class="form-control form-control-lg" value="{{ old('country', $profile->country) }}" placeholder="Ex. Burkina Faso" required maxlength="120">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="age_range">Tranche d’âge <span class="text-danger">*</span></label>
                <select id="age_range" name="age_range" class="form-select form-select-lg" required>
                    <option value="">Veuillez sélectionner</option>
                    @foreach($ageRanges as $range)
                        <option value="{{ $range->value }}" @selected(old('age_range', $profile->age_range?->value) === $range->value)>{{ $range->label() }}</option>
                    @endforeach
                </select>
                <div class="form-text">Nous ne demandons pas votre date de naissance.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="profession">Profession / domaine principal <span class="text-danger">*</span></label>
                <select id="profession" name="profession" class="form-select form-select-lg" required>
                    <option value="">Veuillez sélectionner</option>
                    @foreach($professions as $profession)
                        <option value="{{ $profession->value }}" @selected(old('profession', $profile->profession?->value) === $profession->value)>{{ $profession->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div id="profession_other_wrap" class="col-md-6 {{ old('profession', $profile->profession?->value) === 'other' ? '' : 'd-none' }}">
                <label class="form-label fw-semibold" for="profession_other">Précisez votre profession <span class="text-danger">*</span></label>
                <input id="profession_other" name="profession_other" class="form-control form-control-lg" value="{{ old('profession_other', $profile->profession_other) }}" maxlength="150">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="organization">Organisation / université <span class="text-muted fw-normal">(facultatif)</span></label>
                <input id="organization" name="organization" class="form-control form-control-lg" value="{{ old('organization', $profile->organization) }}" maxlength="180">
            </div>
            <div class="col-12"><button class="btn btn-san btn-lg px-4">{{ $profile->isComplete() ? 'Enregistrer les modifications' : 'Terminer mon profil' }}</button></div>
        </form>
    </div></div>

    @if($profile->isComplete())
    <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5">
        <h2 class="h4 fw-bold" style="color:var(--san-navy)">Apparaître dans la communauté publique</h2>
        <p class="text-muted">C’est totalement facultatif et désactivé par défaut. Votre email, votre tranche d’âge, votre contexte linguistique et vos enregistrements audio ne seront jamais affichés sur la page publique.</p>
        <form method="POST" action="{{ route('contributor.profile.public.update') }}" class="row g-3">
            @csrf
            <input type="hidden" name="public_profile_enabled" value="0">
            <div class="col-12">
                <div class="form-check form-switch section-note p-3 ps-5">
                    <input class="form-check-input" type="checkbox" role="switch" id="public_profile_enabled" name="public_profile_enabled" value="1" @checked(old('public_profile_enabled', $profile->public_profile_enabled))>
                    <label class="form-check-label fw-semibold" for="public_profile_enabled">Je souhaite apparaître sur la page publique de la communauté.</label>
                </div>
            </div>
            <div class="col-md-6"><label class="form-label" for="public_display_name">Nom public</label><input class="form-control" id="public_display_name" name="public_display_name" value="{{ old('public_display_name', $profile->public_display_name ?: auth()->user()->name) }}" maxlength="120"></div>
            <div class="col-md-6"><label class="form-label" for="public_bio">Courte présentation</label><input class="form-control" id="public_bio" name="public_bio" value="{{ old('public_bio', $profile->public_bio) }}" maxlength="500" placeholder="Ex. Développeur passionné par les langues africaines"></div>
            <div class="col-md-6"><label class="form-label" for="github_url">GitHub</label><input type="url" class="form-control" id="github_url" name="github_url" value="{{ old('github_url', $profile->github_url) }}" placeholder="https://github.com/..."></div>
            <div class="col-md-6"><label class="form-label" for="linkedin_url">LinkedIn</label><input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" placeholder="https://linkedin.com/in/..."></div>
            <div class="col-12"><button class="btn btn-outline-primary">Mettre à jour ma visibilité</button></div>
        </form>
    </div></div>
    @endif

    <div class="mt-4"><a href="{{ $profile->isComplete() ? route('contributor.dashboard') : route('home') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ $profile->isComplete() ? 'Retour à mon espace' : 'Retour à l’accueil' }}</a></div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
(() => {
    const select = document.getElementById('profession');
    const wrap = document.getElementById('profession_other_wrap');
    const input = document.getElementById('profession_other');
    const sync = () => {
        const other = select.value === 'other';
        wrap.classList.toggle('d-none', !other);
        input.required = other;
    };
    select.addEventListener('change', sync); sync();
})();
</script>
</body>
</html>
