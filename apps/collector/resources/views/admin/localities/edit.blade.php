@extends('admin.layouts.app')
@section('title','Modifier une localité')
@section('content')<div class="pagetitle"><h1>Modifier {{ $locality->name }}</h1></div><section class="section"><div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.localities.update',$locality) }}">@method('PUT') @include('admin.localities._form')</form></div></div></section>@endsection
