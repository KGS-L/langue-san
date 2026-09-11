@extends('admin.layouts.app')
@section('title','Candidatures projet')
@section('content')
<div class="pagetitle"><h1>Candidatures pour rejoindre le projet</h1></div>
<section class="section">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body"><h5 class="card-title">Soumissions</h5>
        <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>Nom</th><th>Profession</th><th>Domaines</th><th>Statut</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($applications as $application)
                <tr>
                    <td><strong>{{ $application->user->name }}</strong><div class="small text-muted">{{ $application->user->email }}</div></td>
                    <td>{{ $application->user->userProfile?->professionLabel() ?? '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ count($application->contribution_areas ?? []) }} domaine(s)</span></td>
                    <td><span class="badge {{ $application->status->value === 'approved' ? 'bg-success' : ($application->status->value === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $application->status->label() }}</span></td>
                    <td>{{ $application->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end"><a href="{{ route('admin.project-applications.show',$application) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Examiner</a></td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">Aucune candidature.</td></tr>@endforelse
            </tbody>
        </table></div>
        {{ $applications->links() }}
    </div></div>
</section>
@endsection
