<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'საბავშვო ბაღების მართვის სისტემა')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ file_exists(public_path('css/public-modern.css')) ? filemtime(public_path('css/public-modern.css')) : '1' }}">
    <link rel="stylesheet" href="{{ asset('css/platform-unified.css') }}?v={{ filemtime(public_path('css/platform-unified.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-system.css') }}?v={{ filemtime(public_path('css/public-system.css')) }}">
</head>
<body class="login-shell">
    <div id="app">
        
        <main class="login-shell-main">
            @yield('content')
        </main>
    </div>
</body>
</html>
