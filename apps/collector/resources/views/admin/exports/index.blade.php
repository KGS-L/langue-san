@extends('admin.layouts.app')
@section('title','Export dataset')
@section('content')
<div class="pagetitle"><h1>Export dataset</h1></div>
<section class="section">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4"><div class="card-body">
                <h5 class="card-title">Corpus élicité Français → San — version {{ $summary['version'] }}</h5>
                <p>Seules les contributions de type <strong>mot</strong> ou <strong>phrase</strong>, approuvées, associées à une variété validée et couvertes par un consentement autorisant l'entraînement sont exportées.</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Éligibles</div><div class="fs-4 fw-bold">{{ $summary['translationEligible'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Train</div><div class="fs-4 fw-bold">{{ $summary['splits']['train'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Validation</div><div class="fs-4 fw-bold">{{ $summary['splits']['validation'] }}</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 h-100"><div class="small text-muted">Test</div><div class="fs-4 fw-bold">{{ $summary['splits']['test'] }}</div></div></div>
                </div>

                <a class="btn btn-primary" href="{{ route('admin.exports.download') }}"><i class="bi bi-filetype-csv"></i> Télécharger Français → San</a>
            </div></div>

            <div class="card"><div class="card-body">
                <h5 class="card-title">Parole naturelle San → Français</h5>
                <p>Ce corpus provient des récits libres : <strong>audio San → transcription San → segmentation → traduction française</strong>. La consigne française qui a déclenché le récit reste une métadonnée et n'est jamais traitée comme la traduction du récit.</p>

                <div class="border rounded p-3 mb-3">
                    <div class="small text-muted">Segments éligibles</div>
                    <div class="fs-4 fw-bold">{{ $summary['naturalSpeechSegmentsEligible'] }}</div>
                </div>

                <div class="alert alert-light border">
                    <strong>Protection contre les fuites.</strong>
                    Tous les segments issus du même récit partagent le même <code>source_id</code> et le même split train / validation / test.
                </div>

                <a class="btn btn-primary" href="{{ route('admin.exports.natural-speech.download') }}"><i class="bi bi-filetype-csv"></i> Télécharger parole naturelle San → Français</a>
            </div></div>

            @if(!$summary['sourceSaltConfigured'])
                <div class="alert alert-warning mt-4"><i class="bi bi-exclamation-triangle me-1"></i>Le sel d’anonymisation utilise encore la valeur locale par défaut. Définissez <code>DATASET_SOURCE_SALT</code> avant un export de production.</div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4"><div class="card-body">
                <h5 class="card-title">Règle de séparation</h5>
                <p class="small mb-2">Nous conservons deux familles de données distinctes :</p>
                <ul class="small mb-0">
                    <li><strong>Élicité :</strong> Français → San, mots et phrases contrôlés.</li>
                    <li><strong>Naturel :</strong> San → Français, phrases extraites d'un récit spontané.</li>
                </ul>
            </div></div>

            <div class="card"><div class="card-body">
                <h5 class="card-title">Métadonnées principales</h5>
                <ul class="small mb-0">
                    <li><code>dataset_version</code></li>
                    <li><code>source_id</code> anonymisé</li>
                    <li><code>split</code></li>
                    <li><code>direction</code></li>
                    <li>variété + code ISO</li>
                    <li>San et français validés</li>
                    <li>catégorie et localité</li>
                    <li>dates de collecte / validation</li>
                </ul>
            </div></div>
        </div>
    </div>
</section>
@endsection
