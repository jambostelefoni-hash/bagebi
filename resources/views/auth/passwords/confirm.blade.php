@extends('layouts.login')
@section('title','პაროლის დადასტურება')
@section('content')
<section class="modern-auth-page"><div class="modern-auth-card modern-auth-card--single"><main class="modern-auth-form"><span class="modern-card-kicker">დაცული მოქმედება</span><h2>დაადასტურეთ პაროლი</h2><p>გასაგრძელებლად ხელახლა შეიყვანეთ მიმდინარე პაროლი.</p><form method="POST" action="{{route('password.confirm')}}">@csrf<div class="modern-auth-field"><label for="password">პაროლი</label><input id="password" type="password" name="password" class="@error('password') is-invalid @enderror" required autofocus>@error('password')<small>{{$message}}</small>@enderror</div><button class="modern-auth-submit">დადასტურება <span>→</span></button></form></main></div></section>
@endsection
