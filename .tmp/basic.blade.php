<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravels') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
</head>
<body class="basic-shell">
 <main class="public-shell">
  @yield('content')
 </main>
 <!-- Scripts -->
 <script src="{{ mix('js/manifest.js') }}" defer></script>
 <script src="{{ mix('js/vendor.js') }}" defer></script>
 <script src="{{ mix('js/bootstrap.js') }}" defer></script>
 <script src="{{ mix('js/app.js') }} " defer></script>
</body>
</html>
