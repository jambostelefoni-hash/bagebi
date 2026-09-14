@extends('layouts.basic')
@section('title', 'ბავშვის ონლაინ რეგისტრაცია')
@section('content')
<section class="modern-registration-page"><div class="modern-public-container">
    <nav class="modern-breadcrumb" aria-label="გვერდის მდებარეობა"><a href="{{ route('public.home') }}">მთავარი</a><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg><span>რეგისტრაცია</span></nav>
    <header class="modern-registration-header">
        <div><span class="modern-eyebrow"><i></i> ონლაინ მომსახურება</span><h1>{{ $registrationText['subtitle'] ?: 'ბავშვის რეგისტრაცია' }}</h1><p>{{ $registrationText['description'] ?: 'შეავსეთ ფორმა ეტაპობრივად. ვარსკვლავით მონიშნული ველები სავალდებულოა.' }}</p></div>
        <button type="button" class="modern-rules-button" data-toggle="modal" data-target="#registrationRulesModal"><svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"/></svg> წესების ნახვა</button>
    </header>
    <div class="modern-registration-layout">
        <aside class="modern-registration-aside">
            <h2>დაწყებამდე მოამზადეთ</h2>
            <ul>
                <li><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg><span>ბავშვის პირადი მონაცემები</span></li>
                <li><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg><span>მშობლის ან წარმომადგენლის ინფორმაცია</span></li>
                <li><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg><span>საკონტაქტო ნომერი და ელფოსტა</span></li>
            </ul>
            <div class="modern-security-note"><svg viewBox="0 0 24 24"><path d="M6 10V8a6 6 0 0 1 12 0v2M5 10h14v11H5z"/></svg><p><strong>მონაცემები დაცულია</strong><br>ინფორმაცია გამოიყენება მხოლოდ რეგისტრაციისთვის.</p></div>
        </aside>
        <main class="modern-form-card"><div class="modern-form-card-head"><span>სარეგისტრაციო ფორმა</span><small>შეავსეთ ყველა საჭირო ველი</small></div><div id="children-form" bla="19"></div></main>
    </div>
</div></section>
<div class="modal fade" id="registrationRulesModal" tabindex="-1" role="dialog" aria-labelledby="registrationRulesTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content modern-modal-content">
    <div class="modal-header"><h5 class="modal-title" id="registrationRulesTitle">რეგისტრაციის წესები</h5><button type="button" class="close" data-dismiss="modal" aria-label="დახურვა"><span aria-hidden="true">&times;</span></button></div>
    <div class="modal-body">{!! nl2br(e($registrationText['rules'])) !!}</div><div class="modal-footer"><button type="button" class="modern-secondary-button" data-dismiss="modal">დახურვა</button></div>
</div></div></div>
@endsection
