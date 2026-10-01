@php
  $links = [
    ['home', 'სამუშაო დაფა', 'sidebar-dashboard', ['home']],
    ['kindergarteners.index', 'აღსაზრდელები', 'sidebar-children', ['kindergarteners*']],
    ['attendance.index', 'დასწრება', 'sidebar-attendance', ['attendance*']],
  ];
  if (auth()->user()->isUnionAdmin()) {
    $links[] = ['structure.index', 'სტრუქტურა და წვდომები', 'layer-group', ['structure', 'kindergartens*', 'group-age-ranges*', 'users*', 'auth/register', 'municipalities*', 'regions*', 'prioriteties*']];
    $links[] = ['control-center.index', 'მართვა და კონტროლი', 'tasks', ['control-center', 'settings*', 'work-calendar*', 'reinstatement-requests*', 'operations*', 'audit-logs*', 'registration-analytics*', 'system-health*', 'data-quality*', 'public-pages*', 'registration-texts*', 'guide']];
  }
@endphp
<aside class="main-sidebar" id="admin-sidebar" aria-label="მთავარი მენიუ">
  <a href="{{ route('home') }}" class="brand-link">
    <img src="{{ asset('images/admin-georgia-coat-of-arms.png') }}" alt="საქართველოს სახელმწიფო გერბი" class="brand-image brand-image--coat">
    <span class="brand-copy"><strong class="brand-text">{{ config('app.name', 'ბაღების გაერთიანება') }}</strong></span>
  </a>
  <button class="admin-sidebar-close" type="button" aria-label="მენიუს დახურვა"><span aria-hidden="true">×</span></button>
  <div class="sidebar">
    <nav aria-label="ადმინისტრაციული გვერდები">
      <p class="admin-nav-heading">მთავარი მენიუ</p>
      <ul class="nav nav-sidebar flex-column">
        @foreach($links as [$route, $label, $icon, $patterns])
          @php($active = collect($patterns)->contains(function ($pattern) { return request()->is($pattern); }))
          <li class="nav-item" @if($route === 'home') data-tour="dashboard" @endif>
            <a href="{{ route($route) }}" class="nav-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
              <x-admin-icon :name="$route === 'structure.index' ? 'sidebar-structure' : ($route === 'control-center.index' ? 'sidebar-control' : $icon)" class="nav-icon" /><span>{{ $label }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <div class="admin-sidebar-footer">მოქმედებები აღირიცხება აუდიტში</div>
  </div>
</aside>
