@extends('layouts.basic')
@section('title', 'მშობლის პორტალი | ' . ($publicBrand ?? config('app.name')))
@section('content')
@php($registrationOpen = (bool) data_get($settings, 'basic.object.isRegistrationStart', false))
<section class="portal-hero">
    <div class="portal-container portal-hero-grid">
        <div class="portal-hero-copy">
            <span class="portal-kicker">საბავშვო ბაღები · ონლაინ მომსახურება</span>
            <h1>ბაღისკენ პირველი ნაბიჯი<br><span>იწყება აქ.</span></h1>
            <p>დაარეგისტრირეთ ბავშვი საბავშვო ბაღში, შეამოწმეთ განაცხადის სტატუსი და მიიღეთ საჭირო ინფორმაცია ერთ სივრცეში.</p>
            <div class="portal-actions"><a class="portal-button" href="{{ route('children') }}">რეგისტრაციის დაწყება <span aria-hidden="true">↗</span></a><a class="portal-text-link" href="{{ route('public.registration-rules') }}">გაეცანით წესებს <span aria-hidden="true">→</span></a></div>
            <div class="portal-registration-state"><span class="portal-state {{ $registrationOpen ? 'portal-state--open' : '' }}">{{ $registrationOpen ? 'რეგისტრაცია გახსნილია' : 'რეგისტრაცია დახურულია' }}</span><a href="{{ route('public.news') }}">იხილეთ განცხადებები <span aria-hidden="true">→</span></a></div>
        </div>
        <aside class="portal-service-directory" aria-labelledby="portal-services-title">
            <span class="portal-kicker">საიდან დავიწყო?</span><h2 id="portal-services-title">თქვენთვის საჭირო<br>მომსახურება</h2>
            <a href="{{ route('children') }}"><span class="portal-service-index" aria-hidden="true">01</span><span><strong>ახალი განაცხადი</strong><small>ბავშვის რეგისტრაცია ბაღში</small></span><span aria-hidden="true">↗</span></a>
            <a href="{{ route('public.status-tracker') }}"><span class="portal-service-index" aria-hidden="true">02</span><span><strong>განაცხადის სტატუსი</strong><small>ჩარიცხვა და მომლოდინეთა რიგი</small></span><span aria-hidden="true">↗</span></a>
            <a href="{{ route('public.contact') }}"><span class="portal-service-index" aria-hidden="true">03</span><span><strong>დახმარება</strong><small>კითხვები და საკონტაქტო ინფორმაცია</small></span><span aria-hidden="true">↗</span></a>
            <p>რეგისტრაციისთვის მშობლის ანგარიშის შექმნა საჭირო არ არის.</p>
        </aside>
    </div>
</section>
<section class="portal-process" aria-labelledby="portal-process-title">
    <div class="portal-container">
        <div class="portal-section-heading"><div><span class="portal-kicker">მარტივი პროცესი</span><h2 id="portal-process-title">განაცხადიდან ჩარიცხვამდე</h2></div><a class="portal-text-link" href="{{ route('public.registration-rules') }}">სრული წესები <span aria-hidden="true">↗</span></a></div>
        <ol class="portal-steps">
            <li><span aria-hidden="true">01</span><h3>მოამზადეთ მონაცემები</h3><p>ბავშვის პირადი ნომერი, დაბადების თარიღი და მშობლის საკონტაქტო ინფორმაცია.</p></li>
            <li><span aria-hidden="true">02</span><h3>აირჩიეთ ბაღი</h3><p>შეარჩიეთ შესაბამისი ასაკობრივი ჯგუფი, შეამოწმეთ ადგილები და შეავსეთ განაცხადი.</p></li>
            <li><span aria-hidden="true">03</span><h3>გაეცანით შედეგს</h3><p>იხილეთ განაცხადის სტატუსი. ადგილის არქონისას ბავშვი მომლოდინეთა რიგში მოხვდება.</p></li>
        </ol>
    </div>
</section>
<section class="portal-lookup" aria-labelledby="portal-lookup-title">
    <div class="portal-container portal-lookup-grid">
        <div><span class="portal-kicker">უკვე შეავსეთ განაცხადი?</span><h2 id="portal-lookup-title">შეამოწმეთ<br>მიმდინარე სტატუსი</h2><p>გამოიყენეთ რეგისტრაციისას მითითებული ბავშვის პირადი ნომერი და მობილურის ბოლო ოთხი ციფრი.</p></div>
        <div>
            <form id="status-check-form" class="portal-status-form" method="POST" action="{{ route('public.status-tracker.search') }}" data-status-url="{{ url('/api/find-kid') }}">
                @csrf
                <div class="portal-field"><label for="statusPersonalNumber">ბავშვის პირადი ნომერი</label><input id="statusPersonalNumber" type="text" name="kids_personal_number" inputmode="numeric" maxlength="11" minlength="11" pattern="[0-9]{11}" autocomplete="off" placeholder="11 ციფრი" required></div>
                <div class="portal-field"><label for="statusMobileLastFour">მობილურის ბოლო 4 ციფრი</label><input id="statusMobileLastFour" type="text" name="mobile_last_four" inputmode="numeric" maxlength="4" minlength="4" pattern="[0-9]{4}" autocomplete="off" placeholder="მაგ. 1234" required></div>
                <button type="submit" class="portal-button">სტატუსის შემოწმება <span aria-hidden="true">→</span></button>
            </form>
            <div id="status-result" class="portal-lookup-result" role="status" aria-live="polite"></div>
        </div>
    </div>
</section>
<section class="portal-container portal-updates" aria-labelledby="portal-updates-title">
    <div><span class="portal-kicker">გაერთიანების ინფორმაცია</span><h2 id="portal-updates-title">{{ $meta['hero_title'] ?: $page->title }}</h2><p>{{ $meta['hero_lead'] ?: $page->body }}</p><a class="portal-text-link" href="{{ route('public.news') }}">ყველა განცხადება <span aria-hidden="true">↗</span></a></div>
    <aside><span class="portal-kicker">იცოდით?</span><h3>ადგილის არქონა<br>განაცხადს არ აჩერებს.</h3><p>შეგიძლიათ დარეგისტრირდეთ მომლოდინეთა რიგში. ადგილის შეთავაზებას SMS-ით მიიღებთ.</p><a class="portal-text-link" href="{{ route('public.registration-rules') }}">როგორ მუშაობს რიგი <span aria-hidden="true">→</span></a></aside>
</section>
@endsection
