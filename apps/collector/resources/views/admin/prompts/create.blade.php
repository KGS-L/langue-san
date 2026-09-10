@extends('admin.layouts.app')
@section('title','Nouveau prompt')
@section('content')<div class="pagetitle"><h1>Nouveau prompt</h1></div><section class="section"><div class="card"><div class="card-body"><h5 class="card-title">Mot ou phrase française</h5><form method="POST" action="{{ route('admin.prompts.store') }}">@include('admin.prompts._form')</form></div></div></section>@endsection
