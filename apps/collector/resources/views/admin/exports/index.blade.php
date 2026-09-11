@extends('admin.layouts.app')
@section('title','Export dataset')
@section('content')
<div class="pagetitle"><h1>Export dataset</h1></div>
<section class="section">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body">
                <h5 class="card-title">Corpus approuvé — version {{ $summary['version'] }}</h5>
                <p>Seules les contributions <strong>approuvées</strong>, associées à une variété validée et couvertes par un consentement autorisant l'entraînement sont exportées.</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Éligibles</div><div class="fs-4 fw-bold">{{ $summary['eligible'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Train</div><div class="fs-4 fw-bold">{{ $summary['splits']['train'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Validation</div><div class="fs-4 fw-bold">{{ $summary['splits']['validation'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Test</div><div class="fs-4 fw-bold">{{ $summary['splits']['test'] }}</div></div></div>
                </div>

                <div class="alert alert-light border">
                    <strong>Protection contre les fuites train/test.</strong>
                    Chaque contribution reçoit un <code>source_id</code> stable et un split déterministe. Si nous générons plus tard la paire inverse San→Français, elle devra conserver exactement le même <code>source_id</code> et le même split.
                </div>

                @if(!$summary['sourceSaltConfigured'])
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>Le sel d’anonymisation utilise encore la valeur locale par défaut. Définissez <code>DATASET_SOURCE_SALT</code> avant un export de production.</div>
                @endif

                <a class="btn btn-primary" href="{{ route('admin.exports.download') }}"><i class="bi bi-filetype-csv"></i> Télécharger le CSV versionné</a>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-body">
                <h5 class="card-title">Colonnes principales</h5>
                <ul class="small mb-0">
                    <li><code>dataset_version</code></li>
                    <li><code>source_id</code> anonymisé</li>
                    <li><code>split</code> train / validation / test</li>
                    <li><code>direction</code> = fr-san</li>
                    <li>variété + code ISO</li>
                    <li>texte français + contexte</li>
                    <li>texte San validé</li>
                    <li>catégorie, type, localité</li>
                    <li>niveau et dates de validation</li>
                </ul>
            </div></div>
        </div>
    </div>
</section>
@endsection
