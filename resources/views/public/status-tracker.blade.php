@extends('layouts.basic')
@section('title','განაცხადის სტატუსი')
@section('content')
@php
  $status = $kid->application_status ?? null;
  $statusInfo = [
    'registered' => ['title'=>'განაცხადი მიღებულია','text'=>'თქვენი განაცხადი დარეგისტრირებულია და დამუშავების პროცესშია.','icon'=>'document'],
    'waiting' => ['title'=>'მომლოდინეთა რიგში ხართ','text'=>'თავისუფალი ადგილის გამოჩენისას შეთავაზებას SMS-ით მიიღებთ.','icon'=>'clock'],
    'enrolled' => ['title'=>'ბავშვი ჩარიცხულია','text'=>'რეგისტრაცია წარმატებით დასრულებულია.','icon'=>'check'],
    'suspended' => ['title'=>'რეგისტრაცია შეჩერებულია','text'=>'დამატებითი ინფორმაციისთვის შეამოწმეთ SMS შეტყობინება.','icon'=>'pause'],
    'cancelled' => ['title'=>'რეგისტრაცია გაუქმებულია','text'=>'დეტალებისთვის დაუკავშირდით გაერთიანების ადმინისტრაციას.','icon'=>'close'],
    'graduated' => ['title'=>'სასწავლო წელი დასრულებულია','text'=>'ამ განაცხადისთვის სასწავლო პროცესი დასრულებულია.','icon'=>'flag'],
  ];
  $info = $statusInfo[$status] ?? ['title'=>'სტატუსი განახლებადია','text'=>'მიმდინარე ინფორმაცია ქვემოთ არის ნაჩვენები.','icon'=>'info'];
@endphp
<section class="portal-page">
<div class="portal-container">
    <nav class="portal-breadcrumb" aria-label="გვერდის მდებარეობა">
        <a href="{{ route('public.home') }}">მთავარი</a>
        <span aria-current="page">სტატუსის შემოწმება</span>
    </nav>

    <header class="portal-page-heading">
        <span class="portal-kicker">თქვენი განაცხადი</span>
        <h1>სტატუსის შემოწმება</h1>
        <p>ნახეთ ბავშვის რეგისტრაციის მიმდინარე მდგომარეობა ანგარიშის შექმნის გარეშე.</p>
    </header>

    <div class="portal-page-layout">
        <div>
            <article class="portal-document">
                <header><h2>მოძებნეთ განაცხადი</h2></header>
                <form class="portal-status-form" method="POST" action="{{ route('public.status-tracker.search') }}">
                    @csrf
                    <div class="portal-field">
                        <label for="kids_personal_number">ბავშვის პირადი ნომერი</label>
                        <input id="kids_personal_number" type="text" name="kids_personal_number" value="{{ old('kids_personal_number', $query) }}" maxlength="11" inputmode="numeric" autocomplete="off" placeholder="11-ნიშნა პირადი ნომერი" aria-describedby="personal-number-hint @error('kids_personal_number') personal-number-error @enderror" @error('kids_personal_number') aria-invalid="true" @enderror required>
                        <small id="personal-number-hint" class="portal-field-hint">მიუთითეთ განაცხადში ჩაწერილი პირადი ნომერი.</small>
                        @error('kids_personal_number')<span id="personal-number-error" class="portal-field-error" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <div class="portal-field">
                        <label for="mobile_last_four">მობილური ნომრის ბოლო 4 ციფრი</label>
                        <input id="mobile_last_four" type="text" name="mobile_last_four" value="{{ old('mobile_last_four', $mobileLastFour ?? '') }}" maxlength="4" inputmode="numeric" autocomplete="off" placeholder="მაგალითად: 1234" aria-describedby="mobile-number-hint @error('mobile_last_four') mobile-number-error @enderror" @error('mobile_last_four') aria-invalid="true" @enderror required>
                        <small id="mobile-number-hint" class="portal-field-hint">იმ ნომრის, რომელიც რეგისტრაციისას მიუთითეთ.</small>
                        @error('mobile_last_four')<span id="mobile-number-error" class="portal-field-error" role="alert">{{ $message }}</span>@enderror
                    </div>
                    <button class="portal-button" type="submit">სტატუსის შემოწმება</button>
                </form>
            </article>
  @if($query)<section class="status-result" aria-live="polite">@if($kid)<article class="status-result-card status-result-card--{{$status}}"><div class="status-result-card__main"><span class="status-result-icon">@if($info['icon']==='check')<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>@elseif($info['icon']==='clock')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>@elseif($info['icon']==='pause')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M10 9v6M14 9v6"/></svg>@elseif($info['icon']==='close')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/></svg>@else<svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 8h6M9 12h6"/></svg>@endif</span><div><span class="status-result-card__eyebrow">მიმდინარე მდგომარეობა</span><h2>{{$info['title']}}</h2><p>{{$info['text']}}</p></div></div><span class="status-pill status-pill--{{$status}}">{{ $statusLabel ?? 'უცნობი' }}</span></article><div class="status-details"><article><span>ბაღი</span><strong>{{$kid->kindergarten?$kid->kindergarten->name:'—'}}</strong></article><article><span>ასაკობრივი ჯგუფი</span><strong>{{$kid->groupRange?$kid->groupRange->range.' წ.':'—'}}</strong></article><article><span>ბოლო განახლება</span><strong>{{$kid->status_changed_at ? \Carbon\Carbon::parse($kid->status_changed_at)->format('d.m.Y') : $kid->updated_at->format('d.m.Y')}}</strong></article></div>@else<article class="status-not-found"><span><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M11 8v3"/></svg></span><div><h2>განაცხადი ვერ მოიძებნა</h2><p>გადაამოწმეთ 11-ნიშნა პირადი ნომერი და სცადეთ ხელახლა.</p></div></article>@endif</section>@endif
        </div>

        <aside class="portal-aside" aria-label="სტატუსის შემოწმების დახმარება">
            <span class="portal-kicker">სასარგებლო ინფორმაცია</span>
            <h2>რას ნახავთ შედეგში</h2>
            <p>განაცხადის სტატუსს, არჩეულ ბაღს, ასაკობრივ ჯგუფსა და ბოლო განახლების თარიღს.</p>
            <dl class="portal-facts">
                <div><dt>მომლოდინეთა რიგში ხართ?</dt><dd>თავისუფალი ადგილის შეთავაზებას რეგისტრაციისას მითითებულ ნომერზე SMS-ით მიიღებთ.</dd></div>
                <div><dt>განაცხადი ვერ მოიძებნა?</dt><dd>გადაამოწმეთ ბავშვის პირადი ნომერი და მშობლის მობილური ნომრის ბოლო ოთხი ციფრი.</dd></div>
            </dl>
            <ul>
                <li><a href="{{ route('public.contact') }}">დახმარება და კონტაქტი <span aria-hidden="true">↗</span></a></li>
                <li><a href="{{ route('public.registration-rules') }}">რეგისტრაციის წესები <span aria-hidden="true">↗</span></a></li>
            </ul>
        </aside>
    </div>
</div>
</section>
@endsection
