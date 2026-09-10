@extends('auth.layout')
@section('title','Créer un compte')
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="pt-4 pb-2">
            <h5 class="card-title text-center pb-0 fs-4">Créer un compte contributeur</h5>
            <p class="text-center small">Le compte est facultatif. Il permet surtout de retrouver vos statistiques, votre historique et vos contributions lors de prochaines visites.</p>
        </div>
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="row g-3" method="POST" action="{{ route('register') }}">
            @csrf
            <div class="col-12"><label class="form-label">Nom</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
            <div class="col-12"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Confirmer le mot de passe</label><input type="password" name="password_confirmation" class="form-control" required></div>
            <div class="col-12"><button class="btn btn-primary w-100">Créer mon compte</button></div>
            <div class="col-12 text-center"><a href="{{ route('contributor.home') }}" class="small">Continuer sans créer de compte</a></div>
            <div class="col-12"><p class="small mb-0">Déjà inscrit ? <a href="{{ route('login') }}">Connexion</a></p></div>
        </form>
    </div>
</div>
@endsection
