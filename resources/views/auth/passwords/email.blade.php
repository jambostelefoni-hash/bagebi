@extends('layouts.login')
@section('title','პაროლის აღდგენა')
@section('content')
<section class="modern-auth-page"><div class="modern-auth-card modern-auth-card--single"><main class="modern-auth-form"><a href="{{route('login')}}" class="modern-auth-back">← ავტორიზაციაზე დაბრუნება</a><span class="modern-card-kicker">ანგარიშის უსაფრთხოება</span><h2>პაროლის აღდგენა</h2><p>მიუთითეთ ელფოსტა და აღდგენის უსაფრთხო ბმულს გამოგიგზავნით.</p>@if(session('status'))<div class="modern-auth-alert">{{session('status')}}</div>@endif<form method="POST" action="{{route('password.email')}}">@csrf<div class="modern-auth-field"><label for="email">ელფოსტა</label><input id="email" type="email" name="email" value="{{old('email')}}" class="@error('email') is-invalid @enderror" required autofocus>@error('email')<small>{{$message}}</small>@enderror</div><button class="modern-auth-submit">ბმულის გაგზავნა <span>→</span></button></form></main></div></section>
@endsection
