@extends('admin.layouts.app')
@section('title','Segmentation #'.$contribution->id)
@section('content')
@php
    $suggestedVariety = $contribution->locality?->suggestedVariety;
@endphp

<div class="pagetitle">
    <h1>Parole naturelle · Contribution #{{ $contribution->id }}</h1>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.contributions.show',$contribution) }}">Contribution</a></li><li class="breadcrumb-item active">Segmentation</li></ol></nav>
</div>

<section class="section">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Sujet de départ</h5>
                    <div class="fw-semibold">{{ $contribution->prompt->french_text }}</div>
                    <div class="small text-muted mt-2">Cette consigne sert uniquement à déclencher le récit. Le contenu San doit être transcrit selon ce qui est réellement dit.</div>
                </div>
            </div>

            @if($contribution->recording)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-mic-fill me-1"></i>Audio source</h5>
                        <audio class="w-100" controls preload="metadata" src="{{ route('admin.recordings.show',$contribution->recording) }}"></audio>
                        @if($contribution->recording->duration_ms)
                            <div class="small text-muted mt-2">Durée : {{ gmdate('i:s', intdiv($contribution->recording->duration_ms,1000)) }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Transcription San complète</h5>
                    <div class="border rounded p-3 bg-light" style="white-space:pre-wrap">{{ $contribution->san_text ?: 'Aucune transcription complète disponible.' }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contexte linguistique</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Localité</dt><dd class="col-sm-8">{{ $contribution->locality?->name ?? 'Non renseignée' }}</dd>
                        <dt class="col-sm-4">Suggestion</dt>
                        <dd class="col-sm-8">
                            @if($suggestedVariety)
                                {{ $suggestedVariety->name }}{{ $suggestedVariety->iso_code ? ' ('.$suggestedVariety->iso_code.')' : '' }}
                                <span class="badge bg-warning text-dark">à confirmer</span>
                            @else
                                Aucune
                            @endif
                        </dd>
                    </dl>
                    @if($contribution->locality?->suggested_variety_source)
                        <div class="small text-muted mt-2">Source interne : {{ $contribution->locality->suggested_variety_source }}</div>
                    @endif
                    <div class="small text-muted mt-2"><strong>Important :</strong> la suggestion liée à la localité n’est jamais une validation automatique.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 mb-1">Phrases segmentées</h2>
                    <div class="small text-muted">Chaque segment doit conserver le San naturel puis recevoir une traduction française fidèle.</div>
                </div>
                <span class="badge bg-light text-dark border">{{ $segments->count() }} segment(s)</span>
            </div>

            @forelse($segments as $segment)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Segment {{ $segment->position }}</strong>
                            @if($segment->variety)<span class="badge bg-light text-dark border">{{ $segment->variety->name }}</span>@endif
                        </div>

                        @if($editable)
                            <form method="POST" action="{{ route('admin.contributions.segments.update',[$contribution,$segment]) }}">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">San</label>
                                    <textarea name="san_text" class="form-control" rows="3" required maxlength="10000">{{ $segment->san_text }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Traduction française</label>
                                    <textarea name="french_translation" class="form-control" rows="3" required maxlength="10000">{{ $segment->french_translation }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Variété <span class="text-muted">(facultatif avant validation)</span></label>
                                    <select name="variety_id" class="form-select">
                                        <option value="">À confirmer</option>
                                        @foreach($varieties as $variety)
                                            <option value="{{ $variety->id }}" @selected($segment->variety_id === $variety->id)>{{ $variety->name }}{{ $variety->iso_code ? ' ('.$variety->iso_code.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Mettre à jour</button>
                            </form>
                            <form method="POST" action="{{ route('admin.contributions.segments.destroy',[$contribution,$segment]) }}" class="mt-2" onsubmit="return confirm('Supprimer ce segment ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Supprimer</button>
                            </form>
                        @else
                            <div class="small text-muted mb-1">San</div>
                            <div class="border-start border-3 ps-3 mb-3">{{ $segment->san_text }}</div>
                            <div class="small text-muted mb-1">Français</div>
                            <div class="border-start border-3 ps-3">{{ $segment->french_translation }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-light border">Aucun segment n’a encore été créé.</div>
            @endforelse

            @if($editable)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Ajouter le segment suivant</h5>
                        <form method="POST" action="{{ route('admin.contributions.segments.store',$contribution) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="new_san_text">Transcription San du segment</label>
                                <textarea id="new_san_text" name="san_text" class="form-control" rows="3" required maxlength="10000">{{ old('san_text') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="new_french_translation">Traduction française</label>
                                <textarea id="new_french_translation" name="french_translation" class="form-control" rows="3" required maxlength="10000">{{ old('french_translation') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new_variety">Variété <span class="text-muted">(facultatif)</span></label>
                                <select id="new_variety" name="variety_id" class="form-select">
                                    <option value="">À confirmer pendant la validation</option>
                                    @foreach($varieties as $variety)
                                        <option value="{{ $variety->id }}" @selected((string)old('variety_id') === (string)$variety->id)>{{ $variety->name }}{{ $variety->iso_code ? ' ('.$variety->iso_code.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Ajouter le segment</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-4 mb-0"><i class="bi bi-lock me-1"></i>La segmentation est en lecture seule dès que le cycle de validation commence ou si votre rôle n’autorise pas la transcription.</div>
            @endif
        </div>
    </div>
</section>
@endsection
