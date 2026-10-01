<header class="portal-header" data-public-header>
    <div class="portal-utility"><div class="portal-container"><span>სკოლამდელი განათლება · მშობლის პორტალი</span><a href="{{ route('login') }}">თანამშრომლის შესვლა <span aria-hidden="true">↗</span></a></div></div>
    <div class="portal-container portal-masthead">
        <a class="portal-brand" href="{{ route('public.home') }}" aria-label="მთავარ გვერდზე დაბრუნება">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="" width="64" height="64">
            @endif
            <span><strong>{{ $publicBrand ?? config('app.name') }}</strong><small>ერთიანი სარეგისტრაციო სივრცე</small></span>
        </a>
        <a class="portal-header-help" href="{{ route('public.contact') }}"><span>გჭირდებათ დახმარება?</span><strong>დაგვიკავშირდით <span aria-hidden="true">↗</span></strong></a>
        <button class="portal-menu-toggle" id="publicMenuToggle" type="button" aria-expanded="false" aria-controls="publicNavMenu" aria-label="მენიუს გახსნა"><span>მენიუ</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    </div>
    <nav class="portal-nav" id="publicNavMenu" aria-label="მთავარი ნავიგაცია">
        <div class="portal-container portal-nav-inner">
            <div class="portal-nav-links">
                <a href="{{ route('public.home') }}" @if(request()->routeIs('public.home','public.home.alias')) aria-current="page" @endif>მთავარი</a>
                <a href="{{ route('public.news') }}" @if(request()->routeIs('public.news')) aria-current="page" @endif>{{ $publicNavLabels['news'] ?? 'განცხადებები' }}</a>
                <a href="{{ route('public.registration-rules') }}" @if(request()->routeIs('public.registration-rules')) aria-current="page" @endif>{{ $publicNavLabels['rules'] ?? 'წესები' }}</a>
                <a href="{{ route('public.contact') }}" @if(request()->routeIs('public.contact')) aria-current="page" @endif>{{ $publicNavLabels['contact'] ?? 'კონტაქტი' }}</a>
                <a href="{{ route('public.status-tracker') }}" @if(request()->routeIs('public.status-tracker*')) aria-current="page" @endif>{{ $publicNavLabels['status'] ?? 'სტატუსი' }}</a>
            </div>
            <a class="portal-nav-register" href="{{ route('children') }}" @if(request()->routeIs('children')) aria-current="page" @endif>{{ $publicNavLabels['register'] ?? 'რეგისტრაცია' }} <span aria-hidden="true">↗</span></a>
        </div>
    </nav>
</header>
