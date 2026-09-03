<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'რეგისტრაცია') }}</title>

    @stack('styles')
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vendors/admin-lte-core.css') }}">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
</head>
<body class="font-sans antialiased hold-transition sidebar-mini layout-fixed app-shell">
  @include('partials.navigation')
  @include('partials.main-sidebar')
    <main class="content-wrapper" id="app">
        @if (Session::has('flashMessage'))
          <div class="alert alert-dismissible {{ Session::has('flashType') ? 'alert-'.session('flashType') : '' }}">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> შეტყობინება!</h5>
            {{ session('flashMessage') }}
          </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
          @endif
        @yield('content')
    </main>

 <!-- Scripts -->
 <script src="{{ mix('js/manifest.js') }}"></script>
 <script src="{{ mix('js/vendor.js') }}"></script>
 <script src="{{ mix('js/bootstrap.js') }}"></script>
 <script src="{{ mix('js/app.js') }}"></script>
 <script src="{{ mix('js/vendors/admin-lte-core.js') }}"></script>
 <script>window.jQuery = $</script>
 @stack('scripts')
</body>
</html>
