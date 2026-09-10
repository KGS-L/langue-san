@extends('admin.layouts.app')
@section('title', 'Prompts')

@section('content')
<div class="pagetitle d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1>Prompts</h1>
        <p class="text-muted mb-0">Catalogue des mots et phrases françaises proposés aux contributeurs.</p>
    </div>
    <a href="{{ route('admin.prompts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un prompt
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning">
        <strong>Certaines lignes n'ont pas été importées :</strong>
        <ul class="mb-0 mt-2">
            @foreach(array_slice(session('import_errors'), 0, 20) as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        @if(count(session('import_errors')) > 20)
            <div class="mt-2">{{ count(session('import_errors')) - 20 }} autre(s) erreur(s) non affichée(s).</div>
        @endif
    </div>
@endif

<section class="section">
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card info-card h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title">Actifs</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-check-circle"></i></div>
                        <div class="ps-3"><h6>{{ $stats['active'] }}</h6></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card info-card h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title">Mots / concepts</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-fonts"></i></div>
                        <div class="ps-3"><h6>{{ $stats['words'] }}</h6></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card info-card h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title">Phrases</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-chat-left-text"></i></div>
                        <div class="ps-3"><h6>{{ $stats['sentences'] }}</h6></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card info-card h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title">Sous-représentés</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-graph-down-arrow"></i></div>
                        <div class="ps-3"><h6>{{ $stats['under_covered'] }}</h6></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Rechercher et filtrer</h5>
            <form method="GET" action="{{ route('admin.prompts.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Recherche</label>
                    <input type="search" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Code, texte français ou contexte">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->value === 'word' ? 'Mot / concept' : 'Phrase' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">État</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(($filters['status'] ?? null) === 'active')>Actif</option>
                        <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label class="form-label">Affichage</label>
                    <select name="per_page" class="form-select">
                        @foreach([20, 50, 100] as $size)<option value="{{ $size }}" @selected(($filters['per_page'] ?? 20) == $size)>{{ $size }} / page</option>@endforeach
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="under_covered" id="underCovered" @checked(filter_var($filters['under_covered'] ?? false, FILTER_VALIDATE_BOOLEAN))>
                        <label class="form-check-label" for="underCovered">Sous-représentés seulement</label>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                    <a href="{{ route('admin.prompts.index') }}" class="btn btn-light">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-0">Catalogue</h5>
                    <small class="text-muted">{{ $prompts->total() }} prompt(s) trouvé(s)</small>
                </div>
                <form method="POST" action="{{ route('admin.prompts.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf
                    <input type="file" name="file" accept=".csv,.txt" class="form-control form-control-sm" required style="max-width:260px">
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-upload me-1"></i> Importer CSV</button>
                </form>
            </div>
            <p class="small text-muted mt-2 mb-3">Colonnes minimales : <code>code,category,type,french_text</code>. Facultatives : <code>context,difficulty,priority,target_contributions,is_active</code>.</p>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Français</th>
                            <th>Catégorie</th>
                            <th>Type</th>
                            <th>Couverture</th>
                            <th>Priorité</th>
                            <th>État</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($prompts as $prompt)
                        @php($isCovered = $prompt->contributions_count >= $prompt->target_contributions)
                        <tr>
                            <td><code>{{ $prompt->code }}</code></td>
                            <td>
                                <div class="fw-semibold">{{ Str::limit($prompt->french_text, 80) }}</div>
                                @if($prompt->context)<small class="text-muted">{{ Str::limit($prompt->context, 80) }}</small>@endif
                            </td>
                            <td>{{ $prompt->category->name }}</td>
                            <td><span class="badge bg-info text-dark">{{ $prompt->type->value === 'word' ? 'Mot' : 'Phrase' }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $isCovered ? 'bg-success' : 'bg-warning text-dark' }}">{{ $prompt->contributions_count }}/{{ $prompt->target_contributions }}</span>
                                    <small class="text-muted">{{ $prompt->approved_contributions_count }} approuvée(s)</small>
                                </div>
                            </td>
                            <td>{{ $prompt->priority }}</td>
                            <td>
                                <span class="badge {{ $prompt->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $prompt->is_active ? 'Actif' : 'Inactif' }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.prompts.edit', $prompt) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                                @can('delete', $prompt)
                                    <form class="d-inline" method="POST" action="{{ route('admin.prompts.destroy', $prompt) }}" onsubmit="return confirm('Supprimer ce prompt ? S’il possède déjà des contributions, il sera seulement désactivé.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer / désactiver"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun prompt ne correspond aux filtres.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $prompts->links() }}
        </div>
    </div>
</section>
@endsection
