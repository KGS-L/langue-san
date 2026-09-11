@extends('auth.layout')

@section('title','Connexion équipe')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="pt-4 pb-2">
            <h5 class="card-title text-center pb-0 fs-4">Espace équipe</h5>
            <p class="text-center small">Connexion réservée aux administrateurs et modérateurs.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">Identifiants incorrects ou compte non autorisé.</div>
        @endif

        <form class="row g-3" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="col-12">
                <label class="form-label" for="staff_email">Email</label>
                <input id="staff_email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="col-12">
                <label class="form-label" for="staff_password">Mot de passe</label>
                <input id="staff_password" type="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary w-100" type="submit">Se connecter à l’espace équipe</button>
            </div>
            <div class="col-12 d-flex justify-content-between gap-2 small">
                <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                <a href="{{ route('home') }}">Retour au site</a>
            </div>
        </form>
    </div>
</div>
@endsection
