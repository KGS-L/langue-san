@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Rôle</label>
        <input class="form-control" value="Modérateur" disabled>
        <div class="form-text">Les administrateurs ne sont jamais créés depuis ce formulaire.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $user->status->value ?? 'active') === $status->value)>
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@if(!isset($user))
    <div class="alert alert-info mt-4 mb-0">
        <i class="bi bi-envelope me-1"></i>
        Un email sera envoyé au modérateur afin qu’il définisse lui-même son mot de passe.
    </div>
@endif

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">{{ isset($user) ? 'Enregistrer' : 'Inviter le modérateur' }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Annuler</a>
</div>
