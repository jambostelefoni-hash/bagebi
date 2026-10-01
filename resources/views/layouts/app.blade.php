<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'ბაღების მართვის სისტემა'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @stack('styles')
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}?v=20260912-6" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vendors/admin-lte-core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-modern.css') }}?v={{ file_exists(public_path('css/admin-modern.css')) ? filemtime(public_path('css/admin-modern.css')) : '1' }}">
    <link rel="stylesheet" href="{{ asset('css/admin-csp.css') }}?v={{ filemtime(public_path('css/admin-csp.css')) }}">


    <link rel="stylesheet" href="{{ asset('css/platform-unified.css') }}?v={{ filemtime(public_path('css/platform-unified.css')) }}">
    @stack('page-styles')
    <link rel="stylesheet" href="{{ asset('css/admin-reference.css') }}?v={{ filemtime(public_path('css/admin-reference.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/guided-tour-page.css') }}?v={{ filemtime(public_path('css/guided-tour-page.css')) }}">
</head>
@php($tourKey = 'page-tour-v4:'.(Route::currentRouteName() ?: 'dashboard'))
<body class="font-sans antialiased hold-transition sidebar-mini layout-fixed app-shell" data-tour-role="{{ auth()->user()->role }}" data-tour-page="{{ Route::currentRouteName() ?: 'dashboard' }}" data-tour-completed="{{ !empty((auth()->user()->tour_progress ?? [])[$tourKey]) ? 'true' : 'false' }}" data-tour-progress='@json(array_keys(auth()->user()->tour_progress ?? []))' data-tour-complete-url="{{ \Illuminate\Support\Facades\Route::has('guided-tour.complete') ? route('guided-tour.complete') : '' }}">
  <a class="admin-skip-link" href="#app">შინაარსზე გადასვლა</a>
  @include('partials.navigation')
  @include('partials.main-sidebar')
  <button class="admin-sidebar-backdrop" type="button" aria-label="მენიუს დახურვა" tabindex="-1" hidden></button>
    <main class="content-wrapper" id="app" tabindex="-1">
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
        @include('partials.admin-breadcrumbs')
        @yield('content')
    </main>

 <!-- Scripts -->
 <script src="{{ mix('js/manifest.js') }}"></script>
 <script src="{{ mix('js/vendor.js') }}"></script>
 <script src="{{ mix('js/bootstrap.js') }}"></script>
 <script src="{{ mix('js/app.js') }}"></script>
 <script src="{{ mix('js/vendors/admin-lte-core.js') }}"></script>
 <script src="{{ asset('js/guided-tour-page.js') }}?v={{ filemtime(public_path('js/guided-tour-page.js')) }}"></script>
 <script src="{{ asset('js/admin-shell.js') }}?v={{ filemtime(public_path('js/admin-shell.js')) }}"></script>
 <script nonce="{{ $cspNonce }}">
    window.jQuery = $;
    if (typeof window.nottify === 'function') {
        document.querySelectorAll('[data-confirm-action], [data-href], [data-submit]').forEach(function (action) {
            action.addEventListener('click', window.nottify);
        });
    }
 </script>
 @stack('scripts')
</body>
</html>
