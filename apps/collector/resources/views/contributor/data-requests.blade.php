<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes données - Langue SAN</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/langue-san-logo.svg') }}">
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/public-theme.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar bg-white border-bottom"><div class="container py-2">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}"><img src="{{ asset('assets/img/langue-san-logo.svg') }}" width="34" height="34" alt="">Langue SAN</a>
    <a href="{{ route('contributor.dashboard') }}" class="btn btn-outline-secondary btn-sm">Mon espace</a>
</div></nav>

<main class="container py-5">
    <div class="row justify-content-center"><div class="col-xl-9">
        <div class="mb-4"><h1 class="h2 fw-bold">Mes données et mes demandes</h1><p class="text-muted">Vous pouvez demander une correction, supprimer un audio, retirer une contribution ou demander l’anonymisation de vos données de compte.</p></div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="alert alert-light border mb-4">
            <strong>Retrait d’une contribution :</strong> le contenu linguistique et l’audio encore stockés sont supprimés immédiatement et la contribution est exclue de tous les futurs exports. Une copie d’un dataset déjà publié auparavant ne peut pas être rappelée automatiquement chez des tiers.
        </div>

        <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Nouvelle demande</h2>
            <form method="POST" action="{{ route('contributor.data-requests.store') }}" id="dataRequestForm" class="row g-3">
                @csrf
                <div class="col-md-6"><label class="form-label fw-semibold" for="type">Type de demande</label><select class="form-select" name="type" id="type" required><option value="">Choisir</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
                <div class="col-md-6" id="contributionWrap"><label class="form-label fw-semibold" for="contribution_id">Contribution concernée</label><select class="form-select" name="contribution_id" id="contribution_id"><option value="">Choisir une contribution</option>@foreach($contributions as $contribution)<option value="{{ $contribution->id }}" @selected((string)old('contribution_id') === (string)$contribution->id)>#{{ $contribution->id }} · {{ \Illuminate\Support\Str::limit($contribution->prompt?->french_text, 55) }}{{ $contribution->withdrawn_at ? ' · retirée' : '' }}{{ $contribution->recording ? ' · audio' : '' }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label fw-semibold" for="details">Précisions</label><textarea class="form-control" name="details" id="details" rows="4" maxlength="3000" placeholder="Pour une correction, indiquez précisément ce qui doit être revu. Pour un retrait ou une suppression audio, ce champ est facultatif.">{{ old('details') }}</textarea></div>
                <div class="col-12"><button class="btn btn-primary" type="submit"><i class="bi bi-shield-check me-1"></i>Envoyer la demande</button></div>
            </form>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="h5 fw-bold">Historique de mes demandes</h2>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Demande</th><th>Contribution</th><th>Statut</th><th>Réponse</th></tr></thead><tbody>
            @forelse($requests as $item)
                <tr><td>{{ $item->created_at->format('d/m/Y') }}</td><td>{{ $item->type->label() }}</td><td>{{ $item->contribution_id ? '#'.$item->contribution_id : '—' }}</td><td><span class="badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td><td class="small text-muted">{{ $item->resolution_notes ?: '—' }}</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">Aucune demande pour le moment.</td></tr>@endforelse
            </tbody></table></div>
        </div></div>
    </div></div>
</main>
<script>
(() => {
    const type = document.getElementById('type');
    const contribution = document.getElementById('contribution_id');
    const form = document.getElementById('dataRequestForm');
    const contributionTypes = ['correction','withdraw_contribution','delete_audio'];
    const destructiveTypes = ['withdraw_contribution','delete_audio','anonymize_account'];
    const sync = () => { contribution.required = contributionTypes.includes(type.value); };
    type.addEventListener('change', sync); sync();
    form.addEventListener('submit', event => {
        if (destructiveTypes.includes(type.value) && !confirm('Confirmez-vous cette demande concernant vos données ?')) event.preventDefault();
    });
})();
</script>
</body></html>
