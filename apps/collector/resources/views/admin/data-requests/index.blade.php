@extends('admin.layouts.app')
@section('title','Demandes de données')
@section('content')
<div class="pagetitle"><h1>Demandes de données</h1><p class="text-muted">Correction, retrait, suppression d’audio et anonymisation demandés par les contributeurs.</p></div>
<section class="section">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4"><div class="card-body pt-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label" for="status">Statut</label><select id="status" name="status" class="form-select"><option value="">Tous</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label" for="type">Type</label><select id="type" name="type" class="form-select"><option value="">Tous</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><button class="btn btn-primary w-100">Filtrer</button></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body pt-4">
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Contributeur</th><th>Demande</th><th>Détails</th><th>Statut / traitement</th></tr></thead><tbody>
        @forelse($requests as $item)
            <tr>
                <td class="text-nowrap">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $item->contributorProfile?->user?->name ?? $item->contributorProfile?->public_code ?? 'Anonyme' }}</strong>@if($item->contribution_id)<div class="small"><a href="{{ route('admin.contributions.show',$item->contribution_id) }}">Contribution #{{ $item->contribution_id }}</a></div>@endif</td>
                <td>{{ $item->type->label() }}</td>
                <td class="small" style="min-width:220px">{{ $item->details ?: '—' }}@if($item->resolution_notes)<div class="mt-2 text-muted"><strong>Réponse :</strong> {{ $item->resolution_notes }}</div>@endif</td>
                <td style="min-width:280px">
                    <span class="badge {{ $item->status->badgeClass() }} mb-2">{{ $item->status->label() }}</span>
                    <form method="POST" action="{{ route('admin.data-requests.review',$item) }}" class="row g-2">
                        @csrf
                        <div class="col-12"><select name="status" class="form-select form-select-sm">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected($item->status === $status)>{{ $status->label() }}</option>@endforeach</select></div>
                        <div class="col-12"><textarea name="resolution_notes" rows="2" maxlength="3000" class="form-control form-control-sm" placeholder="Note de traitement">{{ $item->resolution_notes }}</textarea></div>
                        <div class="col-12"><button class="btn btn-sm btn-primary">Mettre à jour</button></div>
                    </form>
                </td>
            </tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-5">Aucune demande.</td></tr>@endforelse
        </tbody></table></div>
        {{ $requests->links() }}
    </div></div>
</section>
@endsection
