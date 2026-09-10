@csrf

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control text-uppercase" placeholder="SAL-W-001" value="{{ old('code', $prompt->code ?? '') }}" required>
        <div class="form-text">Identifiant métier stable, ex. SAL-W-001.</div>
    </div>

    <div class="col-md-5">
        <label class="form-label">Catégorie</label>
        <select name="category_id" class="form-select" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $prompt->category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            @foreach($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', isset($prompt) ? $prompt->type->value : 'word') === $type->value)>
                    {{ $type->value === 'word' ? 'Mot / concept' : 'Phrase' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Difficulté</label>
        <input type="number" min="1" max="5" name="difficulty" class="form-control" value="{{ old('difficulty', $prompt->difficulty ?? 1) }}" required>
    </div>

    <div class="col-12">
        <label class="form-label">Texte français</label>
        <textarea name="french_text" class="form-control" rows="3" required>{{ old('french_text', $prompt->french_text ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Contexte <span class="text-muted fw-normal">(facultatif)</span></label>
        <textarea name="context" class="form-control" rows="2" placeholder="Ex. frère du père, vous = plusieurs personnes...">{{ old('context', $prompt->context ?? '') }}</textarea>
        <div class="form-text">Sert uniquement à lever une ambiguïté sans proposer la traduction San.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Priorité</label>
        <input type="number" min="0" max="100" name="priority" class="form-control" value="{{ old('priority', $prompt->priority ?? 10) }}" required>
        <div class="form-text">Plus elle est élevée, plus le prompt est favorisé à couverture égale.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Nombre cible de contributions</label>
        <input type="number" min="1" max="100" name="target_contributions" class="form-control" value="{{ old('target_contributions', $prompt->target_contributions ?? 3) }}" required>
    </div>

    <div class="col-md-4 d-flex align-items-end pb-2">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $prompt->is_active ?? true))>
            <label class="form-check-label">Prompt actif</label>
        </div>
    </div>
</div>

<div class="mt-4">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Enregistrer</button>
    <a href="{{ route('admin.prompts.index') }}" class="btn btn-light">Annuler</a>
</div>
