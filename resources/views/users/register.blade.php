@extends('layouts.app')
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">წვდომის შექმნა</span><h1>ახალი მომხმარებელი</h1><p>დაამატეთ დირექტორი ან გაერთიანების ადმინისტრატორი.</p></div></div>
<section class="content"><form method="POST" action="{{ route('users.register') }}">@csrf @include('users._form',['model'=>new \App\User,'isEdit'=>false])</form></section>
@endsection
