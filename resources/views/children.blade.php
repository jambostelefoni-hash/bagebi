@extends('layouts.basic')
@section('title', 'ბავშვის ონლაინ რეგისტრაცია')
@section('content')
<section class="portal-page modern-registration-page"><div class="portal-container">
    <nav class="portal-breadcrumb" aria-label="გვერდის მდებარეობა"><a href="{{ route('public.home') }}">მთავარი</a><span aria-current="page">რეგისტრაცია</span></nav>
    <header class="portal-page-heading">
        <span class="portal-kicker">ონლაინ განაცხადი</span>
        <h1>{{ $registrationText['subtitle'] ?: 'ბავშვის რეგისტრაცია' }}</h1>
        <p>{{ $registrationText['description'] ?: 'შეავსეთ ფორმა ეტაპობრივად. ვარსკვლავით მონიშნული ველები სავალდებულოა.' }}</p>
    </header>
    <div class="modern-registration-layout">
        <section class="modern-form-card" aria-label="სარეგისტრაციო ფორმა"><div class="modern-form-card-head"><span>სარეგისტრაციო ფორმა</span><small>ხუთი ეტაპი · სავალდებულო ველი *</small></div><div id="children-form"></div></section>
        <aside class="portal-aside modern-registration-aside">
            <span class="portal-kicker">სანამ დაიწყებთ</span>
            <h2>მოამზადეთ მონაცემები</h2>
            <dl class="portal-facts">
                <div><dt>ბავშვი</dt><dd>პირადი ნომერი და დაბადების თარიღი</dd></div>
                <div><dt>მშობელი ან წარმომადგენელი</dt><dd>პირადი და საკონტაქტო ინფორმაცია</dd></div>
                <div><dt>მობილური ნომერი</dt><dd>მოქმედი ნომერი SMS შეტყობინებებისთვის</dd></div>
            </dl>
            <p>თუ ჯგუფში ადგილი აღარ არის, განაცხადი მომლოდინეთა რიგში დარეგისტრირდება. რიგის ნომერი გამოჩნდება რეგისტრაციის დასრულებისას.</p>
            <button type="button" class="portal-button portal-button--outline" data-toggle="modal" data-target="#registrationRulesModal">რეგისტრაციის წესები</button>
            <ul>
                <li><a href="{{ route('public.contact') }}">გჭირდებათ დახმარება? <span aria-hidden="true">↗</span></a></li>
                <li><a href="{{ route('public.status-tracker') }}">განაცხადის სტატუსი <span aria-hidden="true">↗</span></a></li>
            </ul>
        </aside>
    </div>
</div></section>
<div class="modal fade" id="registrationRulesModal" tabindex="-1" role="dialog" aria-labelledby="registrationRulesTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content modern-modal-content">
    <div class="modal-header"><h5 class="modal-title" id="registrationRulesTitle">რეგისტრაციის წესები</h5><button type="button" class="close" data-dismiss="modal" aria-label="დახურვა"><span aria-hidden="true">&times;</span></button></div>
    <div class="modal-body">{!! nl2br(e($registrationText['rules'])) !!}</div><div class="modal-footer"><button type="button" class="portal-button portal-button--outline" data-dismiss="modal">დახურვა</button></div>
</div></div></div>
@endsection
