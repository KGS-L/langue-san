<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon espace - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        :root{--san-navy:#172640;--san-gold:#d3a84a;--san-cream:#f8f5ee}.brand{color:var(--san-navy);font-weight:800;text-decoration:none}body{background:#f7f8fb}.stat-card,.panel{border:0;border-radius:18px}.stat-icon{width:46px;height:46px;border-radius:14px;background:var(--san-cream);display:grid;place-items:center;color:var(--san-navy);font-size:1.25rem}.btn-san{background:var(--san-navy);border-color:var(--san-navy);color:#fff}.btn-san:hover{background:#243b60;color:#fff}
    </style>
</head>
<body>
<nav class="navbar bg-white border-bottom py-3"><div class="container">
    <a class="brand d-flex align-items-center gap-2" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt=""><span>Langue SAN</span></a>
    <div class="d-flex align-items-center gap-2"><a href="{{ route('contributor.profile.edit') }}" class="btn btn-light btn-sm"><i class="bi bi-person me-1"></i>Mon profil</a><form method="POST" action="{{ route('contributor.logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Déconnexion</button></form></div>
</div></nav>

<main class="container py-5">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end mb-4">
        <div><span class="badge rounded-pill text-bg-light border mb-2">Espace contributeur</span><h1 class="fw-bold mb-1" style="color:var(--san-navy)">Bonjour {{ $user->name }} 👋</h1><p class="text-muted mb-0">Merci de faire vivre le San dans le numérique.</p></div>
        <div class="d-flex gap-2 flex-wrap"><a href="{{ route('contributor.home') }}" class="btn btn-san"><i class="bi bi-mic-fill me-1"></i>Contribuer</a><a href="{{ route('project.join') }}" class="btn btn-outline-primary">Rejoindre le projet</a></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm h-100"><div class="card-body"><div class="stat-icon mb-3"><i class="bi bi-chat-square-text"></i></div><div class="fs-3 fw-bold">{{ $stats['contributions'] }}</div><div class="text-muted small">Contributions</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm h-100"><div class="card-body"><div class="stat-icon mb-3"><i class="bi bi-patch-check"></i></div><div class="fs-3 fw-bold">{{ $stats['approved'] }}</div><div class="text-muted small">Réponses validées</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm h-100"><div class="card-body"><div class="stat-icon mb-3"><i class="bi bi-check2-circle"></i></div><div class="fs-3 fw-bold">{{ $stats['sessions'] }}</div><div class="text-muted small">Sessions terminées</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card stat-card shadow-sm h-100"><div class="card-body"><div class="stat-icon mb-3"><i class="bi bi-grid"></i></div><div class="fs-3 fw-bold">{{ $stats['themes'] }}</div><div class="text-muted small">Thèmes explorés</div></div></div></div>
    </div>

    @if($activeSession)
        <div class="alert border-0 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="background:#fff8e8;border-radius:16px!important"><div><strong>Vous avez une session en cours : {{ $activeSession->category->name }}</strong><div class="small text-muted">Reprenez là où vous vous étiez arrêté.</div></div><a href="{{ route('contributor.sessions.show',$activeSession) }}" class="btn btn-warning">Continuer</a></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card panel shadow-sm"><div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">Mes dernières sessions</h2><a href="{{ route('contributor.home') }}" class="small text-decoration-none">Nouvelle contribution</a></div>
                <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Thème</th><th>Statut</th><th>Réponses</th><th>Date</th></tr></thead><tbody>
                @forelse($recentSessions as $session)
                    <tr><td class="fw-semibold">{{ $session->category->name }}</td><td><span class="badge {{ $session->status->value === 'completed' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $session->status->value === 'completed' ? 'Terminée' : 'En cours' }}</span></td><td>{{ $session->contributions_count }}</td><td>{{ optional($session->started_at)->format('d/m/Y') }}</td></tr>
                @empty<tr><td colspan="4" class="text-center text-muted py-4">Aucune session pour le moment.</td></tr>@endforelse
                </tbody></table></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card panel shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5 fw-bold">Ma candidature au projet</h2>
                @if($user->projectMembership?->is_active)
                    <span class="badge text-bg-success mb-2">Membre du projet</span><p class="text-muted small mb-0">Votre candidature a été acceptée. Merci de contribuer avec votre expérience au projet.</p>
                @elseif($application)
                    <span class="badge {{ $application->status->value === 'rejected' ? 'text-bg-danger' : 'text-bg-warning' }} mb-2">{{ $application->status->label() }}</span><p class="text-muted small mb-2">Dernière mise à jour : {{ $application->updated_at->format('d/m/Y') }}</p>@if($application->decision_reason)<div class="small bg-light rounded p-2">{{ $application->decision_reason }}</div>@endif
                @else
                    <p class="text-muted small">Vous êtes développeur, linguiste, enseignant, chercheur, spécialiste IA ou vous avez un réseau à mobiliser ?</p><a href="{{ route('project.join') }}" class="btn btn-outline-primary w-100">Rejoindre le projet</a>
                @endif
            </div></div>
            <div class="card panel shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold">Ma visibilité</h2><p class="small text-muted">{{ $user->userProfile?->public_profile_enabled ? 'Votre profil est visible dans la communauté publique.' : 'Votre profil public est désactivé.' }}</p><a href="{{ route('contributor.profile.edit') }}" class="btn btn-light w-100">Gérer mon profil</a></div></div>
        </div>
    </div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body></html>
