@extends('admin.layouts.app')
@section('title','Modifier un modérateur')
@section('content')
<div class="pagetitle"><h1>Modifier {{ $user->name }}</h1></div>
<section class="section">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Compte modérateur</h5>
            <form method="POST" action="{{ route('admin.users.update',$user) }}">
                @method('PUT')
                @include('admin.users._form')
            </form>
        </div>
    </div>
</section>
@endsection
