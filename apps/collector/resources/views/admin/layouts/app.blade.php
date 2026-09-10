<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'Administration') - Langue SAN</title>
    <meta name="description" content="Administration de la plateforme de collecte Langue SAN">
    @include('admin.partials.head')
    @stack('styles')
</head>
<body>
    @include('admin.partials.header')
    @include('admin.partials.sidebar')

    <main id="main" class="main">
        @include('admin.partials.alerts')
        @yield('content')
    </main>

    @include('admin.partials.footer')
    @include('admin.partials.scripts')
    @stack('scripts')
</body>
</html>
