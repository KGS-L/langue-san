@extends('admin.layouts.app')
@section('title','Modifier une catégorie')
@section('content')<div class="pagetitle"><h1>Modifier {{ $category->name }}</h1></div><section class="section"><div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.categories.update',$category) }}">@method('PUT') @include('admin.categories._form')</form></div></div></section>@endsection
