@extends('admin.layouts.app')
@section('title','Utilisateurs')
@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
    <div><h1>Utilisateurs</h1><p class="text-muted mb-0">Comptes contributeurs et accès équipe.</p></div>
    @can('create', App\Models\User::class)<a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Inviter un modérateur</a>@endcan
</div>
<section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Comptes</h5><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Profession</th><th>Statut</th><th>Localité</th><th></th></tr></thead><tbody>
@forelse($users as $user)
<tr>
    <td><strong>{{ $user->name }}</strong></td>
    <td>{{ $user->email }}</td>
    <td>@foreach($user->getRoleNames() as $role)<span class="badge bg-secondary me-1">{{ $role === 'admin' ? 'Admin' : ($role === 'moderator' ? 'Modérateur' : 'Contributeur') }}</span>@endforeach</td>
    <td>{{ $user->userProfile?->professionLabel() ?? '—' }}</td>
    <td><span class="badge bg-{{ $user->status->value === 'active' ? 'success' : 'danger' }}">{{ $user->status->value === 'active' ? 'Actif' : 'Suspendu' }}</span></td>
    <td>{{ $user->profile?->locality?->name ?? '—' }}</td>
    <td class="text-end">@can('update',$user)<a href="{{ route('admin.users.edit',$user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan @can('delete',$user)<form action="{{ route('admin.users.destroy',$user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce modérateur ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endcan</td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted">Aucun utilisateur.</td></tr>@endforelse
</tbody></table></div>{{ $users->links() }}</div></div></section>
@endsection
