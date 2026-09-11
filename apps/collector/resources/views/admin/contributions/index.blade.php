@extends('admin.layouts.app')
@section('title','Contributions')
@section('content')
<div class="pagetitle"><h1>Contributions</h1></div>
<section class="section">
    <div class="d-flex flex-wrap gap-2 mb-3">
        @can('transcribe contributions')<a href="{{ route('admin.transcriptions.index') }}" class="btn btn-outline-primary"><i class="bi bi-headphones me-1"></i>File de transcription</a>@endcan
        @can('validate contributions')<a href="{{ route('admin.validations.index') }}" class="btn btn-outline-success"><i class="bi bi-patch-check me-1"></i>File de validation</a>@endcan
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Toutes les contributions</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead><tr><th>#</th><th>Contributeur</th><th>Prompt</th><th>Localité</th><th>Source</th><th>Validations</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @forelse($contributions as $contribution)
                        @php($profile = $contribution->contributorProfile)
                        <tr>
                            <td>{{ $contribution->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $profile?->user?->name ?? 'Anonyme' }}</div>
                                @if(!$profile?->user_id)<small class="text-muted">Sans compte</small>@endif
                            </td>
                            <td><div>{{ Str::limit($contribution->prompt->french_text,45) }}</div><small class="text-muted">{{ $contribution->prompt->category->name }}</small></td>
                            <td>{{ $contribution->locality?->name ?? '—' }}</td>
                            <td>
                                @if($contribution->recording)<span class="badge bg-light text-dark border"><i class="bi bi-mic"></i> Audio</span>@endif
                                @if($contribution->submitted_san_text ?? $contribution->san_text)<span class="badge bg-light text-dark border"><i class="bi bi-fonts"></i> Texte</span>@endif
                            </td>
                            <td>{{ $contribution->validations_count }}</td>
                            <td><span class="badge {{ $contribution->status->badgeClass() }}">{{ $contribution->status->label() }}</span></td>
                            <td><a href="{{ route('admin.contributions.show',$contribution) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucune contribution.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $contributions->links() }}
        </div>
    </div>
</section>
@endsection
