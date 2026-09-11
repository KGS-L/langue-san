<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes contributions - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
    <style>
        :root{--san-navy:#172640;--san-gold:#d3a84a;--san-cream:#f8f5ee}
        body{background:#f7f8fb;color:#1f2937}.brand{color:var(--san-navy);font-weight:800;text-decoration:none}.panel{border:0;border-radius:18px}.answer{white-space:pre-wrap;background:var(--san-cream);border-radius:12px;padding:.75rem 1rem}
    </style>
</head>
<body>
<nav class="navbar bg-white border-bottom py-3"><div class="container">
    <a class="brand d-flex align-items-center gap-2" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="38" height="38" alt=""><span>Langue SAN</span></a>
    <a href="{{ route('contributor.dashboard') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i>Mon espace</a>
</div></nav>

<main class="container py-5" style="max-width:1000px">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end mb-4">
        <div><span class="badge rounded-pill text-bg-light border mb-2">Historique personnel</span><h1 class="fw-bold mb-1" style="color:var(--san-navy)">Mes contributions</h1><p class="text-muted mb-0">Suivez l’avancement de vos réponses sans exposer les commentaires internes des validateurs.</p></div>
        <a href="{{ route('contributor.home') }}" class="btn btn-primary"><i class="bi bi-mic-fill me-1"></i>Nouvelle contribution</a>
    </div>

    <div class="card panel shadow-sm mb-4"><div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="status" class="form-label fw-semibold">Filtrer par statut</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected($activeStatus === $status->value)>{{ $status->publicLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary">Filtrer</button></div>
            @if($activeStatus)<div class="col-auto"><a class="btn btn-light" href="{{ route('contributor.history') }}">Réinitialiser</a></div>@endif
        </form>
    </div></div>

    <div class="vstack gap-3">
        @forelse($contributions as $contribution)
            <article class="card panel shadow-sm"><div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-start">
                    <div class="flex-grow-1">
                        <div class="small text-muted mb-1">{{ $contribution->prompt?->category?->name ?? 'Sans catégorie' }} · {{ optional($contribution->submitted_at)->format('d/m/Y à H:i') }}</div>
                        <h2 class="h5 fw-bold mb-2" style="color:var(--san-navy)">{{ $contribution->prompt?->french_text }}</h2>
                        @if($contribution->san_text)
                            <div class="answer small"><strong>Votre réponse en San</strong><br>{{ $contribution->san_text }}</div>
                        @else
                            <div class="small text-muted"><i class="bi bi-mic-fill me-1"></i>Réponse envoyée principalement par audio.</div>
                        @endif
                    </div>
                    <span class="badge {{ $contribution->status->publicBadgeClass() }} px-3 py-2">{{ $contribution->status->publicLabel() }}</span>
                </div>
            </div></article>
        @empty
            <div class="card panel shadow-sm"><div class="card-body p-5 text-center"><i class="bi bi-chat-square-text fs-1 text-muted"></i><h2 class="h5 mt-3">Aucune contribution trouvée</h2><p class="text-muted">Commencez une session pour partager vos premières réponses.</p><a href="{{ route('contributor.home') }}" class="btn btn-primary">Contribuer maintenant</a></div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $contributions->links() }}</div>
</main>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
