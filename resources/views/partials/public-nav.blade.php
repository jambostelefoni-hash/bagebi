<nav class="public-nav" aria-label="მთავარი ნავიგაცია">
    <div class="public-nav-inner">
        <a class="public-brand" href="{{ route('public.home') }}" aria-label="მთავარ გვერდზე დაბრუნება">
            <span class="public-brand-mark" aria-hidden="true"><svg viewBox="0 0 48 48"><path d="M24 5c8 0 15 6 15 14 0 11-9 19-15 24-6-5-15-13-15-24C9 11 16 5 24 5Z"/><path d="M17 22c2-5 12-7 16 0M19 28c3 3 7 3 10 0"/></svg></span>
            <span><strong>{{ $publicBrand ?? 'საბავშვო ბაღების გაერთიანება' }}</strong><small>ერთიანი სარეგისტრაციო პორტალი</small></span>
        </a>
        <button class="public-menu-toggle" id="publicMenuToggle" type="button" aria-expanded="false" aria-controls="publicNavMenu" aria-label="მენიუს გახსნა"><span></span><span></span><span></span></button>
        <div class="public-nav-menu" id="publicNavMenu">
            <a href="{{ route('public.news') }}">{{ $publicNavLabels['news'] ?? 'განცხადებები' }}</a>
            <button type="button" data-toggle="modal" data-target="#publicRulesModal">{{ $publicNavLabels['rules'] ?? 'წესები' }}</button>
            <a href="{{ route('public.contact') }}">{{ $publicNavLabels['contact'] ?? 'კონტაქტი' }}</a>
            <a href="{{ route('public.status-tracker') }}">{{ $publicNavLabels['status'] ?? 'სტატუსი' }}</a>
            <a class="public-nav-cta" href="{{ url('/kids-registration') }}">რეგისტრაცია</a>
        </div>
    </div>
</nav>
