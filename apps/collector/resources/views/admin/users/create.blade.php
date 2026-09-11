@extends('admin.layouts.app')
@section('title','Inviter un modérateur')
@section('content')
<div class="pagetitle"><h1>Inviter un modérateur</h1></div>
<section class="section">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Nouvel accès équipe</h5>
            <p class="text-muted">Le compte sera créé avec le rôle modérateur. La personne recevra un email pour définir son mot de passe.</p>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @include('admin.users._form')
            </form>
        </div>
    </div>
</section>
@endsection
