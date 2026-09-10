@extends('admin.layouts.app')
@section('title','Contributions')
@section('content')
<div class="pagetitle"><h1>Contributions</h1></div>
<section class="section">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">File de transcription et validation</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>#</th><th>Contributeur</th><th>Prompt</th><th>Localité</th><th>Texte</th><th>Audio</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @forelse($contributions as $contribution)
                        @php($profile = $contribution->contributorProfile)
                        <tr>
                            <td>{{ $contribution->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $profile?->user?->name ?? $profile?->public_code ?? 'Anonyme' }}</div>
                                @if(!$profile?->user_id)<small class="text-muted">Sans compte</small>@endif
                            </td>
                            <td>{{ Str::limit($contribution->prompt->french_text,45) }}</td>
                            <td>{{ $contribution->locality?->name ?? '—' }}</td>
                            <td>{{ $contribution->san_text ? 'Oui' : 'Non' }}</td>
                            <td>{{ $contribution->recording ? 'Oui' : 'Non' }}</td>
                            <td><span class="badge bg-secondary">{{ $contribution->status->value }}</span></td>
                            <td><a href="{{ route('admin.contributions.show',$contribution) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Aucune contribution.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $contributions->links() }}
        </div>
    </div>
</section>
@endsection
