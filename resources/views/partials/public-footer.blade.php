<footer class="portal-footer">
    <div class="portal-container portal-footer-main">
        <div class="portal-footer-brand"><span class="portal-kicker">მშობლის პორტალი</span><strong>{{ $publicBrand ?? config('app.name') }}</strong><p>განაცხადი, ინფორმაცია და მხარდაჭერა ერთ სივრცეში.</p></div>
        <nav aria-label="მშობლის მომსახურება"><h2>მომსახურება</h2><a href="{{ route('children') }}">ბავშვის რეგისტრაცია</a><a href="{{ route('public.status-tracker') }}">განაცხადის სტატუსი</a><a href="{{ route('public.registration-rules') }}">რეგისტრაციის წესები</a></nav>
        <nav aria-label="გაერთიანების ინფორმაცია"><h2>გაერთიანება</h2><a href="{{ route('public.about') }}">ჩვენ შესახებ</a><a href="{{ route('public.news') }}">განცხადებები</a><a href="{{ route('public.contact') }}">კონტაქტი</a></nav>
    </div>
    <div class="portal-container portal-footer-bottom"><span>© {{ date('Y') }} · {{ $publicBrand ?? config('app.name') }}</span><a href="{{ route('login') }}">თანამშრომლის შესვლა <span aria-hidden="true">↗</span></a></div>
</footer>
