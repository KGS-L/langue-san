@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="pagetitle"><h1>Dashboard</h1><nav><ol class="breadcrumb"><li class="breadcrumb-item active">Vue d'ensemble</li></ol></nav></div>

<section class="section dashboard">
    <div class="row g-3">
        @foreach([
            ['Contributeurs', $stats['contributors'], 'bi-people', 'Personnes avec un compte contributeur'],
            ['Contributions', $stats['contributions'], 'bi-mic', $stats['today'].' aujourd’hui · '.$stats['last7days'].' sur 7 jours'],
            ['À transcrire', $stats['toTranscribe'], 'bi-headphones', 'Réponses en attente de transcription'],
            ['À valider', $stats['toValidate'], 'bi-patch-check', 'Transcrites ou à départager'],
            ['Approuvées', $stats['approved'], 'bi-check2-circle', $stats['approvalRate'].' % du total'],
            ['Rejetées', $stats['rejected'], 'bi-x-circle', 'Contributions écartées après validation'],
            ['Prompts', $stats['prompts'], 'bi-card-text', $stats['categories'].' catégories'],
            ['Candidatures', $stats['pendingApplications'], 'bi-person-plus', 'En attente d’examen'],
        ] as [$label,$value,$icon,$hint])
            <div class="col-xxl-3 col-md-6">
                <div class="card info-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $label }}</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi {{ $icon }}"></i></div>
                            <div class="ps-3"><h6>{{ $value }}</h6><span class="text-muted small">{{ $hint }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <h5 class="card-title mb-0">Activité récente</h5>
                        @can('view contributions')<a href="{{ route('admin.contributions.index') }}" class="small">Voir toutes les contributions</a>@endcan
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>#</th><th>Prompt</th><th>Contributeur</th><th>Localité</th><th>Statut</th><th>Date</th></tr></thead>
                            <tbody>
                            @forelse($recentContributions as $contribution)
                                @php($profile = $contribution->contributorProfile)
                                <tr>
                                    <td>@can('view contributions')<a href="{{ route('admin.contributions.show', $contribution) }}">#{{ $contribution->id }}</a>@else#{{ $contribution->id }}@endcan</td>
                                    <td><strong>{{ \Illuminate\Support\Str::limit($contribution->prompt?->french_text, 42) }}</strong><div class="small text-muted">{{ $contribution->prompt?->category?->name }}</div></td>
                                    <td>{{ $profile?->user?->name ?? 'Anonyme' }}</td>
                                    <td>{{ $contribution->locality?->name ?? '—' }}</td>
                                    <td><span class="badge {{ $contribution->status->badgeClass() }}">{{ $contribution->status->label() }}</span></td>
                                    <td>{{ optional($contribution->submitted_at)->format('d/m H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Aucune contribution pour le moment.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Files de travail</h5>
                    <div class="d-grid gap-2">
                        @can('transcribe contributions')
                            <a href="{{ route('admin.transcriptions.index') }}" class="btn btn-outline-primary d-flex justify-content-between align-items-center"><span><i class="bi bi-headphones me-2"></i>À transcrire</span><strong>{{ $stats['toTranscribe'] }}</strong></a>
                        @endcan
                        @can('validate contributions')
                            <a href="{{ route('admin.validations.index') }}" class="btn btn-outline-success d-flex justify-content-between align-items-center"><span><i class="bi bi-patch-check me-2"></i>À valider</span><strong>{{ $stats['toValidate'] }}</strong></a>
                        @endcan
                        @can('review project applications')
                            <a href="{{ route('admin.project-applications.index') }}" class="btn btn-outline-secondary d-flex justify-content-between align-items-center"><span><i class="bi bi-person-plus me-2"></i>Candidatures</span><strong>{{ $stats['pendingApplications'] }}</strong></a>
                        @endcan
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Intégrations</h5>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom"><span>Resend / email</span><span class="badge {{ $integrations['resend'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $integrations['resend'] ? 'Configuré' : 'À configurer' }}</span></div>
                        <div class="d-flex justify-content-between align-items-center py-2"><span>Google OAuth</span><span class="badge {{ $integrations['google'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $integrations['google'] ? 'Configuré' : 'À configurer' }}</span></div>
                        <div class="small text-muted mt-2">Mailer actif : <code>{{ $integrations['mailDriver'] }}</code></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->isStaff())
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Prompts sous-couverts</h5>
                        <p class="small text-muted">Priorité aux concepts ayant moins de 3 contributions indépendantes.</p>
                        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Prompt</th><th>Catégorie</th><th>Contributions</th></tr></thead><tbody>
                            @forelse($underCoveredPrompts as $prompt)
                                <tr><td><strong>{{ $prompt->french_text }}</strong><div class="small text-muted">{{ $prompt->code }}</div></td><td>{{ $prompt->category?->name }}</td><td><span class="badge bg-warning text-dark">{{ $prompt->contributions_count }} / 3</span></td></tr>
                            @empty<tr><td colspan="3" class="text-center text-muted py-4">Tous les prompts actifs ont au moins 3 contributions.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Répartition par localité</h5>
                        @forelse($topLocalities as $row)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}"><span>{{ $row->locality?->name ?? 'Non renseignée' }}</span><strong>{{ $row->total }}</strong></div>
                        @empty
                            <p class="text-muted mb-0">Pas encore assez de données.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
