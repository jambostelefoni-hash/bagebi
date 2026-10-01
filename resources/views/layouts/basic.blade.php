<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="csp-nonce" content="{{ $cspNonce }}">

    <title>@yield('title', $publicBrand ?? config('app.name', 'ბაღების გაერთიანება'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ file_exists(public_path('css/public-modern.css')) ? filemtime(public_path('css/public-modern.css')) : '1' }}">
    <link rel="stylesheet" href="{{ asset('css/platform-unified.css') }}?v={{ filemtime(public_path('css/platform-unified.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-system.css') }}?v={{ filemtime(public_path('css/public-system.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-portal.css') }}?v={{ filemtime(public_path('css/public-portal.css')) }}">
</head>
<body class="basic-shell landing-shell">
 <a class="public-skip-link" href="#public-content">შინაარსზე გადასვლა</a>
 @include('partials.public-nav')
 <main class="public-shell" id="public-content" tabindex="-1">
  @yield('content')
 </main>
 @include('partials.public-footer')
 <div class="modal fade" id="publicRulesModal" tabindex="-1" role="dialog" aria-labelledby="publicRulesTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
   <div class="modal-content">
    <div class="modal-header">
     <h5 class="modal-title" id="publicRulesTitle">რეგისტრაციის წესები</h5>
     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
     </button>
    </div>
    <div class="modal-body">
     {!! nl2br(e($publicRules ?? '')) !!}
    </div>
    <div class="modal-footer">
     <button type="button" class="btn btn-secondary" data-dismiss="modal">დახურვა</button>
    </div>
   </div>
  </div>
 </div>
 <!-- Scripts -->
 <script src="{{ mix('js/manifest.js') }}" defer></script>
 <script src="{{ mix('js/vendor.js') }}" defer></script>
 <script src="{{ mix('js/bootstrap.js') }}" defer></script>
 <script src="{{ mix('js/app.js') }} " defer></script>
 <script src="{{ asset('js/public-shell.js') }}?v={{ filemtime(public_path('js/public-shell.js')) }}" defer></script>

</body>
</html>
