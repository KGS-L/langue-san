@extends('admin.layouts.app')
@section('title','Examiner une candidature')
@section('content')
<div class="pagetitle"><h1>Candidature de {{ $application->user->name }}</h1></div>
<section class="section">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body"><h5 class="card-title">Profil</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><div class="small text-muted">Nom</div><strong>{{ $application->user->name }}</strong></div>
                    <div class="col-md-6"><div class="small text-muted">Email</div><strong>{{ $application->user->email }}</strong></div>
                    <div class="col-md-6"><div class="small text-muted">Profession</div><strong>{{ $application->user->userProfile?->professionLabel() ?? '—' }}</strong></div>
                    <div class="col-md-6"><div class="small text-muted">Pays</div><strong>{{ $application->user->userProfile?->country ?? '—' }}</strong></div>
                </div>

                <h6 class="fw-bold">Domaines proposés</h6><div class="d-flex flex-wrap gap-2 mb-4">@foreach($application->contribution_areas as $area) @php($enum=\App\Enums\ProjectContributionArea::tryFrom($area)) <span class="badge bg-light text-dark border">{{ $enum?->label() ?? $area }}</span> @endforeach</div>
                <h6 class="fw-bold">Expérience</h6><p style="white-space:pre-line">{{ $application->experience }}</p>
                <h6 class="fw-bold mt-4">Motivation</h6><p style="white-space:pre-line">{{ $application->motivation }}</p>
                @if($application->san_connection)<h6 class="fw-bold mt-4">Lien avec le San / communautés</h6><p style="white-space:pre-line">{{ $application->san_connection }}</p>@endif
                <div class="row g-3 mt-1"><div class="col-md-6"><div class="small text-muted">Disponibilité</div><strong>{{ $application->availability ?: 'Non précisée' }}</strong></div><div class="col-md-6"><div class="small text-muted">Lien externe</div>@if($application->portfolio_url)<a href="{{ $application->portfolio_url }}" target="_blank" rel="noopener">Ouvrir le profil / portfolio</a>@else<strong>—</strong>@endif</div></div>
            </div></div>

            @if($application->status->value === 'approved' && $application->user->projectMembership?->is_active)
                <div class="card mt-4"><div class="card-body">
                    <h5 class="card-title">Accès de travail</h5>
                    <p class="text-muted">L’acceptation au projet ne donne aucun accès sensible automatiquement. Seul l’administrateur peut attribuer un rôle de transcription ou de validation.</p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark border">Contributeur</span>
                        @if($application->user->hasRole('transcriber'))<span class="badge bg-info text-dark">Transcripteur</span>@endif
                        @if($application->user->hasRole('validator'))<span class="badge bg-success">Validateur linguistique</span>@endif
                    </div>

                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('admin.project-applications.access.update', $application) }}">
                            @csrf
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="transcriber" id="role_transcriber" @checked($application->user->hasRole('transcriber'))>
                                <label class="form-check-label" for="role_transcriber"><strong>Transcripteur</strong> — accès uniquement à la file des audios à transcrire.</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="validator" id="role_validator" @checked($application->user->hasRole('validator'))>
                                <label class="form-check-label" for="role_validator"><strong>Validateur linguistique</strong> — accès uniquement à la validation des contributions.</label>
                            </div>
                            <button class="btn btn-primary"><i class="bi bi-shield-check me-1"></i>Mettre à jour les accès</button>
                        </form>
                    @else
                        <div class="alert alert-light border mb-0 small">Un administrateur peut modifier les accès spécialisés de ce membre.</div>
                    @endif
                </div></div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card"><div class="card-body"><h5 class="card-title">Décision</h5>
                <div class="mb-3"><span class="badge {{ $application->status->value === 'approved' ? 'bg-success' : ($application->status->value === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $application->status->label() }}</span></div>
                @if(in_array($application->status->value,['pending','under_review'],true))
                    <form method="POST" action="{{ route('admin.project-applications.review',$application) }}">@csrf
                        <div class="mb-3"><label class="form-label fw-semibold" for="decision_reason">Message / motif</label><textarea id="decision_reason" name="decision_reason" class="form-control" rows="5" maxlength="2000" placeholder="Obligatoire en cas de refus. Facultatif si la candidature est acceptée.">{{ old('decision_reason') }}</textarea></div>
                        <div class="d-grid gap-2">
                            <button name="decision" value="approved" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Accepter la candidature</button>
                            <button name="decision" value="rejected" class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Refuser la candidature</button>
                        </div>
                    </form>
                @else
                    <div class="small text-muted">Décision prise {{ optional($application->reviewed_at)->format('d/m/Y H:i') }} par {{ $application->reviewer?->name ?? '—' }}.</div>
                    @if($application->decision_reason)<div class="alert alert-light border mt-3 mb-0"><strong>Message :</strong><br>{{ $application->decision_reason }}</div>@endif
                @endif
            </div></div>
            <a href="{{ route('admin.project-applications.index') }}" class="btn btn-light w-100 mt-3"><i class="bi bi-arrow-left me-1"></i>Retour aux candidatures</a>
        </div>
    </div>
</section>
@endsection
