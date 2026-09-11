@extends('admin.layouts.app')
@section('title','Contribution #'.$contribution->id)
@section('content')
@php
    $profile = $contribution->contributorProfile;
    $isNarrative = $contribution->prompt->type === \App\Enums\PromptType::NARRATIVE;
    $alreadyValidatedByMe = $contribution->validations->contains('validator_id', auth()->id());
    $naturalReady = !$isNarrative || $contribution->segments->isNotEmpty();
    $canTranscribe = auth()->user()->can('transcribe contributions')
        && $contribution->status->canBeTranscribed()
        && $contribution->validations->isEmpty();
    $canValidate = auth()->user()->can('validate contributions')
        && $contribution->status->canBeValidated()
        && !$alreadyValidatedByMe
        && $naturalReady;
    $suggestedVariety = $contribution->locality?->suggestedVariety;
    $backUrl = match(request('from')) {
        'transcriptions' => route('admin.transcriptions.index'),
        'validations' => route('admin.validations.index'),
        default => route('admin.contributions.index'),
    };
@endphp

<div class="pagetitle">
    <h1>Contribution #{{ $contribution->id }}</h1>
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ $backUrl }}">Modération</a></li><li class="breadcrumb-item active">#{{ $contribution->id }}</li></ol></nav>
</div>

<section class="section">
    @if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <a href="{{ $backUrl }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Retour à la file</a>
        <span class="badge {{ $contribution->status->badgeClass() }} fs-6">{{ $contribution->status->label() }}</span>
        @if($isNarrative)<span class="badge bg-dark fs-6"><i class="bi bi-mic-fill me-1"></i>Parole naturelle</span>@endif
        <span class="small text-muted">{{ $contribution->validations->count() }} validation(s)</span>
    </div>

    @if($isNarrative)
        <div class="alert alert-light border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <strong>Workflow parole naturelle :</strong>
                <span class="text-muted">audio San → transcription San complète → segmentation en phrases → traduction française → validation.</span>
                <div class="small text-muted mt-1">La consigne française ci-dessous est seulement un déclencheur de récit ; elle n’est pas la traduction du contenu San.</div>
            </div>
            <a href="{{ route('admin.contributions.segments.index',$contribution) }}" class="btn btn-primary text-nowrap">
                <i class="bi bi-list-ol me-1"></i>Segmentation ({{ $contribution->segments->count() }})
            </a>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Donnée collectée</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-3">{{ $isNarrative ? 'Consigne' : 'Français' }}</dt><dd class="col-sm-9 fw-semibold">{{ $contribution->prompt->french_text }}</dd>
                        @if($contribution->prompt->context)<dt class="col-sm-3">Contexte</dt><dd class="col-sm-9">{{ $contribution->prompt->context }}</dd>@endif
                        <dt class="col-sm-3">Catégorie</dt><dd class="col-sm-9">{{ $contribution->prompt->category->name }}</dd>
                        <dt class="col-sm-3">Contributeur</dt><dd class="col-sm-9">{{ $profile?->user?->name ?? 'Anonyme' }} @if(!$profile?->user_id)<span class="badge bg-light text-dark border ms-1">Sans compte</span>@endif</dd>
                        <dt class="col-sm-3">Localité</dt><dd class="col-sm-9">{{ $contribution->locality?->name ?? 'Non renseignée' }}</dd>
                        @if($suggestedVariety)
                            <dt class="col-sm-3">Suggestion interne</dt>
                            <dd class="col-sm-9">
                                {{ $suggestedVariety->name }}{{ $suggestedVariety->iso_code ? ' ('.$suggestedVariety->iso_code.')' : '' }}
                                <span class="badge bg-warning text-dark ms-1">à confirmer</span>
                                <div class="small text-muted">La localité sert d’indice, jamais de validation automatique.</div>
                            </dd>
                        @endif
                        <dt class="col-sm-3">Envoyée</dt><dd class="col-sm-9">{{ optional($contribution->submitted_at)->format('d/m/Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            @if($contribution->recording)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-mic-fill me-1"></i>Enregistrement audio</h5>
                        <audio class="w-100" controls preload="metadata" src="{{ route('admin.recordings.show',$contribution->recording) }}"></audio>
                        @if($contribution->recording->duration_ms)
                            <div class="small text-muted mt-2">Durée : {{ gmdate('i:s', intdiv($contribution->recording->duration_ms,1000)) }}</div>
                        @endif
                        <div class="small text-muted mt-2">L’audio reste privé et n’est servi qu’à travers une route authentifiée.</div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $isNarrative ? 'Transcription San complète' : 'Transcription San' }}</h5>

                    @if($contribution->submitted_san_text)
                        <div class="alert alert-light border">
                            <div class="small text-muted fw-semibold mb-1">Texte saisi par le contributeur — conservé comme donnée source</div>
                            <div>{{ $contribution->submitted_san_text }}</div>
                        </div>
                    @endif

                    @if($canTranscribe)
                        <form method="POST" action="{{ route('admin.contributions.transcribe',$contribution) }}">
                            @csrf
                            <label class="form-label fw-bold" for="san_text">{{ $isNarrative ? 'Transcrivez tout le récit en San' : 'Transcription à envoyer en validation' }}</label>
                            <textarea id="san_text" name="san_text" class="form-control" rows="{{ $isNarrative ? 12 : 6 }}" required maxlength="{{ $isNarrative ? 20000 : 5000 }}">{{ old('san_text',$contribution->san_text) }}</textarea>
                            <div class="form-text">
                                @if($isNarrative)
                                    Respectez ce que la personne dit réellement, sans reformuler le récit selon la consigne française. Après enregistrement, ouvrez l’espace de segmentation pour créer les paires San → Français.
                                @else
                                    Écoutez l’audio si disponible et corrigez uniquement la transcription de travail. Le texte original du contributeur reste conservé séparément.
                                @endif
                            </div>
                            <button class="btn btn-primary mt-3"><i class="bi bi-save me-1"></i>{{ $isNarrative ? 'Enregistrer la transcription complète' : 'Enregistrer et envoyer en validation' }}</button>
                        </form>
                    @else
                        <div class="border rounded p-3 bg-light" style="white-space:pre-wrap">
                            <div class="small text-muted mb-1">Transcription de travail / forme canonique</div>
                            <div class="fw-semibold">{{ $contribution->san_text ?: 'Aucune transcription disponible' }}</div>
                        </div>
                        @if(!$contribution->status->canBeTranscribed())
                            <div class="small text-muted mt-2"><i class="bi bi-lock me-1"></i>La transcription est verrouillée dès que le cycle de validation commence.</div>
                        @endif
                    @endif

                    @if($isNarrative && $contribution->status !== \App\Enums\ContributionStatus::PENDING)
                        <a href="{{ route('admin.contributions.segments.index',$contribution) }}" class="btn btn-outline-primary mt-3">
                            <i class="bi bi-list-ol me-1"></i>Segmenter et traduire le récit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Validation linguistique</h5>

                    @if($isNarrative && !$naturalReady && $contribution->status->canBeValidated())
                        <div class="alert alert-warning">
                            <strong>Segmentation requise.</strong>
                            <div class="small mt-1">Avant de valider ce récit, créez ses phrases San et leur traduction française.</div>
                            <a href="{{ route('admin.contributions.segments.index',$contribution) }}" class="btn btn-sm btn-dark mt-2">Ouvrir la segmentation</a>
                        </div>
                    @endif

                    @if($contribution->validations->isNotEmpty())
                        <div class="mb-4">
                            @foreach($contribution->validations as $index => $validation)
                                <div class="border rounded-3 p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div><strong>{{ $validation->validator->name }}</strong><div class="small text-muted">Validation {{ $index + 1 }} · {{ $validation->created_at->format('d/m/Y H:i') }}</div></div>
                                        <span class="badge {{ $validation->decision->badgeClass() }}">{{ $validation->decision->label() }}</span>
                                    </div>
                                    @if($validation->variety)<div class="mt-2"><span class="small text-muted">Variété :</span> <strong>{{ $validation->variety->name }}{{ $validation->variety->iso_code ? ' ('.$validation->variety->iso_code.')' : '' }}</strong></div>@endif
                                    @if($validation->san_text_corrected)<div class="mt-2"><span class="small text-muted">Correction proposée :</span><div class="border-start border-3 ps-2 mt-1">{{ $validation->san_text_corrected }}</div></div>@endif
                                    @if($validation->notes)<div class="mt-2 small text-muted"><i class="bi bi-chat-left-text me-1"></i>{{ $validation->notes }}</div>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">Aucune validation n’a encore été enregistrée.</p>
                    @endif

                    @if($canValidate)
                        @if($contribution->status === \App\Enums\ContributionStatus::VALIDATED_TWICE)
                            <div class="alert alert-warning"><strong>Désaccord à départager.</strong><br><span class="small">Les deux dernières validations ne concordent pas sur la décision, la variété ou la transcription.</span></div>
                        @endif

                        <form method="POST" action="{{ route('admin.contributions.validations.store',$contribution) }}" id="validationForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="decision">Décision</label>
                                <select id="decision" name="decision" class="form-select" required>
                                    <option value="approve" @selected(old('decision') === 'approve')>Approuver la transcription</option>
                                    <option value="correct" @selected(old('decision') === 'correct')>Proposer une correction</option>
                                    <option value="reject" @selected(old('decision') === 'reject')>Rejeter la contribution</option>
                                </select>
                            </div>
                            <div class="mb-3" id="varietyWrap">
                                <label class="form-label fw-semibold" for="variety_id">Variété validée</label>
                                <select id="variety_id" name="variety_id" class="form-select">
                                    <option value="">Sélectionner une variété</option>
                                    @foreach($varieties as $variety)
                                        <option value="{{ $variety->id }}" @selected((string)old('variety_id') === (string)$variety->id)>{{ $variety->name }}{{ $variety->iso_code ? ' ('.$variety->iso_code.')' : '' }}</option>
                                    @endforeach
                                </select>
                                @if($suggestedVariety)
                                    <div class="form-text">Suggestion liée à {{ $contribution->locality?->name }} : <strong>{{ $suggestedVariety->name }}</strong>. À confirmer ou corriger librement.</div>
                                @endif
                            </div>
                            <div class="mb-3 d-none" id="correctionWrap">
                                <label class="form-label fw-semibold" for="san_text_corrected">Texte corrigé</label>
                                <textarea id="san_text_corrected" name="san_text_corrected" class="form-control" rows="{{ $isNarrative ? 8 : 4 }}" maxlength="{{ $isNarrative ? 20000 : 5000 }}">{{ old('san_text_corrected') }}</textarea>
                                <div class="form-text">Saisissez la forme complète que vous estimez correcte.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes internes <span class="text-muted">(facultatif)</span></label>
                                <textarea id="notes" name="notes" class="form-control" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
                            </div>
                            <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Enregistrer ma validation</button>
                        </form>
                    @elseif($alreadyValidatedByMe && !$contribution->status->isTerminal())
                        <div class="alert alert-info mb-0"><i class="bi bi-person-check me-1"></i>Vous avez déjà validé cette contribution. Une autre personne doit effectuer la prochaine validation.</div>
                    @elseif($contribution->status === \App\Enums\ContributionStatus::PENDING)
                        <div class="alert alert-light border mb-0"><i class="bi bi-headphones me-1"></i>La contribution doit d’abord être transcrite avant de pouvoir être validée.</div>
                    @elseif($contribution->status->isTerminal())
                        <div class="alert {{ $contribution->status === \App\Enums\ContributionStatus::APPROVED ? 'alert-success' : 'alert-secondary' }} mb-0">
                            <strong>Workflow terminé :</strong> {{ $contribution->status->label() }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(() => {
    const decision = document.getElementById('decision');
    if (!decision) return;
    const correctionWrap = document.getElementById('correctionWrap');
    const correction = document.getElementById('san_text_corrected');
    const varietyWrap = document.getElementById('varietyWrap');
    const variety = document.getElementById('variety_id');

    const sync = () => {
        const correcting = decision.value === 'correct';
        const rejecting = decision.value === 'reject';
        correctionWrap.classList.toggle('d-none', !correcting);
        correction.required = correcting;
        varietyWrap.classList.toggle('d-none', rejecting);
        variety.required = !rejecting;
    };

    decision.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
@endsection
