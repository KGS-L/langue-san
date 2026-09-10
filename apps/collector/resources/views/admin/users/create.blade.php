@extends('admin.layouts.app')
@section('title','Ajouter un utilisateur')
@section('content')<div class="pagetitle"><h1>Ajouter un utilisateur</h1></div><section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Nouveau compte</h5><form method="POST" action="{{ route('admin.users.store') }}">@include('admin.users._form')</form></div></div></section>@endsection
