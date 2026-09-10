@extends('admin.layouts.app')
@section('title','Modifier un prompt')
@section('content')<div class="pagetitle"><h1>Modifier le prompt</h1></div><section class="section"><div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.prompts.update',$prompt) }}">@method('PUT') @include('admin.prompts._form')</form></div></div></section>@endsection
