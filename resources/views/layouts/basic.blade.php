<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravels') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=20260212">

</head>
<body class="basic-shell">
 @include('partials.public-nav')
 <main class="public-shell">
  @yield('content')
 </main>
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
 <script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.public-menu-toggle');
        var links = document.getElementById('publicNavLinks');
        function isMobile() {
            return window.innerWidth <= 768;
        }
        function updateMenu() {
            if (!isMobile()) {
                links.classList.remove('is-open');
                links.style.display = 'flex';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                links.style.display = links.classList.contains('is-open') ? 'flex' : 'none';
            }
        }
        if (!toggle || !links) return;
        toggle.addEventListener('click', function () {
            if (isMobile()) {
                var isOpen = links.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                updateMenu();
            }
        });
        window.addEventListener('resize', updateMenu);
        updateMenu();
    });
 </script>

</body>
</html>
