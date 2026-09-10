@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Accueil</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Prompts <span>| total</span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-card-text"></i></div>
                        <div class="ps-3"><h6>0</h6><span class="text-muted small">À alimenter</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Contributions <span>| total</span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-mic"></i></div>
                        <div class="ps-3"><h6>0</h6><span class="text-muted small">Collectées</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">À valider</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-hourglass-split"></i></div>
                        <div class="ps-3"><h6>0</h6><span class="text-muted small">En attente</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Approuvées</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi bi-check2-circle"></i></div>
                        <div class="ps-3"><h6>0</h6><span class="text-muted small">Éligibles dataset</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Démarrage du projet</h5>
                    <p>Le dashboard est prêt. Les statistiques seront reliées aux modèles Laravel lorsque les migrations et le workflow de collecte seront implémentés.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
