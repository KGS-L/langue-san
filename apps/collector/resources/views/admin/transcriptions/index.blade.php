@extends('admin.layouts.app')
@section('title', 'À transcrire')
@section('content')
<div class="pagetitle">
    <h1>À transcrire</h1>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Transcriptions</li></ol></nav>
</div>

<section class="section">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">File de transcription</h5>
            <p class="text-muted">Contributions encore en attente d’une transcription ou d’une confirmation de la forme écrite avant validation linguistique.</p>

            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label for="q" class="form-label">Recherche</label>
                    <input id="q" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Français, San ou contributeur">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string)($filters['category_id'] ?? '') === (string)$category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="locality_id" class="form-label">Localité</label>
                    <select id="locality_id" name="locality_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($localities as $locality)
                            <option value="{{ $locality->id }}" @selected((string)($filters['locality_id'] ?? '') === (string)$locality->id)>{{ $locality->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="has_audio" class="form-label">Source</label>
                    <select id="has_audio" name="has_audio" class="form-select">
                        <option value="">Toutes</option>
                        <option value="1" @selected(($filters['has_audio'] ?? null) === '1')>Avec audio</option>
                        <option value="0" @selected(($filters['has_audio'] ?? null) === '0')>Texte uniquement</option>
                    </select>
                </div>
                <div class="col-md-3 col-lg-1">
                    <label for="from" class="form-label">Du</label>
                    <input id="from" type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-3 col-lg-1">
                    <label for="to" class="form-label">Au</label>
                    <input id="to" type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-lg-1 d-grid">
                    <button class="btn btn-primary" title="Filtrer"><i class="bi bi-funnel"></i></button>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.transcriptions.index') }}" class="small text-decoration-none"><i class="bi bi-x-circle me-1"></i>Réinitialiser les filtres</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
                <h5 class="card-title mb-0">{{ $contributions->total() }} contribution(s) à traiter</h5>
                <span class="small text-muted">Les plus anciennes sont affichées en premier.</span>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr><th>#</th><th>Prompt français</th><th>Contributeur</th><th>Source</th><th>Localité</th><th>Envoyée</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                    @forelse($contributions as $contribution)
                        @php($profile = $contribution->contributorProfile)
                        <tr>
                            <td class="text-muted">{{ $contribution->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($contribution->prompt->french_text, 70) }}</div>
                                <small class="text-muted">{{ $contribution->prompt->category->name }}</small>
                            </td>
                            <td>
                                {{ $profile?->user?->name ?? 'Anonyme' }}
                                @if(!$profile?->user_id)<div><small class="text-muted">Sans compte</small></div>@endif
                            </td>
                            <td>
                                @if($contribution->recording)<span class="badge bg-light text-dark border me-1"><i class="bi bi-mic-fill"></i> Audio</span>@endif
                                @if($contribution->san_text)<span class="badge bg-light text-dark border"><i class="bi bi-fonts"></i> Texte fourni</span>@endif
                                @if(!$contribution->recording && !$contribution->san_text)<span class="text-muted">—</span>@endif
                            </td>
                            <td>{{ $contribution->locality?->name ?? '—' }}</td>
                            <td><span title="{{ optional($contribution->submitted_at)->format('d/m/Y H:i') }}">{{ optional($contribution->submitted_at)->format('d/m/Y') ?? '—' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.contributions.show', ['contribution' => $contribution, 'from' => 'transcriptions']) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-headphones me-1"></i>Transcrire
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5"><i class="bi bi-check2-circle fs-2 text-success"></i><div class="fw-semibold mt-2">Aucune transcription en attente</div><div class="text-muted small">La file est à jour pour les filtres sélectionnés.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $contributions->links() }}</div>
        </div>
    </div>
</section>
@endsection
