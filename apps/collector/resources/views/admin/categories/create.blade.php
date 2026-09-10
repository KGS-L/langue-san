@extends('admin.layouts.app')
@section('title','Nouvelle catégorie')
@section('content')<div class="pagetitle"><h1>Nouvelle catégorie</h1></div><section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Thème de collecte</h5><form method="POST" action="{{ route('admin.categories.store') }}">@include('admin.categories._form')</form></div></div></section>@endsection
