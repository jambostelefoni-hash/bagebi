@extends('layouts.login')
@section('title','ელფოსტის დადასტურება')
@section('content')
<section class="modern-auth-page"><div class="modern-auth-card modern-auth-card--single"><main class="modern-auth-form"><span class="modern-card-kicker">ანგარიშის გააქტიურება</span><h2>დაადასტურეთ ელფოსტა</h2>@if(session('resent'))<div class="modern-auth-alert">დადასტურების ახალი ბმული გამოგზავნილია.</div>@endif<p>გთხოვთ, შეამოწმოთ ელფოსტა და გახსნათ დადასტურების ბმული.</p><form method="POST" action="{{route('verification.resend')}}">@csrf<button class="modern-auth-submit">ბმულის ხელახლა გაგზავნა <span>→</span></button></form></main></div></section>
@endsection
