@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nom</label><input name="name" class="form-control" value="{{ old('name',$user->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$user->email ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Rôle</label><select name="role" class="form-select" required>@foreach($roles as $role)<option value="{{ $role->value }}" @selected(old('role',$user->role->value ?? 'contributor')===$role->value)>{{ ucfirst($role->value) }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Statut</label><select name="status" class="form-select" required>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status',$user->status->value ?? 'active')===$status->value)>{{ ucfirst($status->value) }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Mot de passe {{ isset($user) ? '(laisser vide pour conserver)' : '' }}</label><input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}></div>
    <div class="col-md-6"><label class="form-label">Confirmation</label><input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}></div>
</div>
<div class="mt-4"><button class="btn btn-primary">Enregistrer</button><a href="{{ route('admin.users.index') }}" class="btn btn-light">Annuler</a></div>
