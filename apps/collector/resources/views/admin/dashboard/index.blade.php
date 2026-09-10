@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="pagetitle"><h1>Dashboard</h1><nav><ol class="breadcrumb"><li class="breadcrumb-item active">Vue d'ensemble</li></ol></nav></div>
<section class="section dashboard">
    <div class="row">
        @foreach([
            ['Contributeurs', $stats['contributors'], 'bi-people'],
            ['Catégories', $stats['categories'], 'bi-tags'],
            ['Prompts', $stats['prompts'], 'bi-card-text'],
            ['Contributions', $stats['contributions'], 'bi-mic'],
        ] as [$label,$value,$icon])
        <div class="col-xxl-3 col-md-6"><div class="card info-card"><div class="card-body"><h5 class="card-title">{{ $label }}</h5><div class="d-flex align-items-center"><div class="card-icon rounded-circle d-flex align-items-center justify-content-center"><i class="bi {{ $icon }}"></i></div><div class="ps-3"><h6>{{ $value }}</h6></div></div></div></div></div>
        @endforeach
    </div>
</section>
@endsection
