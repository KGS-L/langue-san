@extends('admin.layouts.app')
@section('title','Nouvelle localité')
@section('content')<div class="pagetitle"><h1>Nouvelle localité</h1></div><section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Référentiel géographique</h5><form method="POST" action="{{ route('admin.localities.store') }}">@include('admin.localities._form')</form></div></div></section>@endsection
