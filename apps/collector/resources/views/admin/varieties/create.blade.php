@extends('admin.layouts.app')
@section('title','Nouvelle variété')
@section('content')<div class="pagetitle"><h1>Nouvelle variété</h1></div><section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Variété linguistique</h5><form method="POST" action="{{ route('admin.varieties.store') }}">@include('admin.varieties._form')</form></div></div></section>@endsection
