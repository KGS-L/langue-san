<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La communauté - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/landing.css') }}" rel="stylesheet">
    <style>.community-avatar{width:64px;height:64px;border-radius:50%;display:grid;place-items:center;background:#172640;color:#fff;font-weight:800;font-size:1.2rem}.community-card{border:1px solid #e8edf4;border-radius:18px;height:100%;background:#fff}.member-badge{background:#f8f5ee;color:#8b671e}</style>
</head>
<body>
<nav class="navbar bg-white border-bottom py-3"><div class="container"><a class="san-brand" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" alt=""><span>Langue SAN</span></a><div class="d-flex gap-2"><a href="{{ route('project.join') }}" class="btn btn-san-outline">Rejoindre le projet</a><a href="{{ route('contributor.home') }}" class="btn btn-san-primary">Contribuer</a></div></div></nav>

<header class="section-san section-soft"><div class="container text-center"><div class="section-kicker">Communauté</div><h1 class="section-title display-5 mt-2">Des personnes derrière chaque mot, chaque voix et chaque ligne de code.</h1><p class="section-copy mx-auto" style="max-width:760px">Cette page affiche uniquement les personnes qui ont choisi volontairement d’apparaître publiquement. Les informations sensibles, les audios et les données linguistiques individuelles restent privés.</p></div></header>

<main class="section-san"><div class="container">
    <div class="row g-4">
        @forelse($profiles as $profile)
            @php
                $name = $profile->public_display_name ?: $profile->user->name;
                $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn($part)=>mb_strtoupper(mb_substr($part,0,1)))->implode('');
            @endphp
            <div class="col-md-6 col-lg-4"><article class="community-card p-4 shadow-sm">
                <div class="d-flex gap-3 align-items-start">
                    <div class="community-avatar">{{ $initials ?: 'SAN' }}</div>
                    <div class="flex-grow-1"><h2 class="h5 fw-bold mb-1">{{ $name }}</h2><div class="text-muted small">{{ $profile->professionLabel() }}</div><div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $profile->country ?: 'Pays non indiqué' }}</div></div>
                </div>
                @if($profile->user->projectMembership?->is_active)<span class="badge member-badge mt-3"><i class="bi bi-stars me-1"></i>Membre du projet</span>@else<span class="badge text-bg-light border mt-3">Contributeur</span>@endif
                @if($profile->public_bio)<p class="text-muted small mt-3 mb-0">{{ $profile->public_bio }}</p>@endif
                @if($profile->github_url || $profile->linkedin_url)<div class="d-flex gap-3 mt-3 small">@if($profile->github_url)<a href="{{ $profile->github_url }}" target="_blank" rel="noopener" class="text-decoration-none"><i class="bi bi-github me-1"></i>GitHub</a>@endif @if($profile->linkedin_url)<a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" class="text-decoration-none"><i class="bi bi-linkedin me-1"></i>LinkedIn</a>@endif</div>@endif
            </article></div>
        @empty
            <div class="col-12"><div class="text-center py-5 bg-light rounded-4"><i class="bi bi-people fs-1 text-muted"></i><h2 class="h4 mt-3">La communauté publique se construit.</h2><p class="text-muted">Les profils apparaîtront ici au fur et à mesure que les membres choisiront de les rendre publics.</p><a href="{{ route('project.join') }}" class="btn btn-san-primary">Rejoindre le projet</a></div></div>
        @endforelse
    </div>
</div></main>
@include('public.partials.footer')
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body></html>
