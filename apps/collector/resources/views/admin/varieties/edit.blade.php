@extends('admin.layouts.app')
@section('title','Modifier une variété')
@section('content')<div class="pagetitle"><h1>Modifier {{ $variety->name }}</h1></div><section class="section"><div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.varieties.update',$variety) }}">@method('PUT') @include('admin.varieties._form')</form></div></div></section>@endsection
