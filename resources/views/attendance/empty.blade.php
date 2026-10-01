@extends('layouts.app')
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">ყოველდღიური ჟურნალი</span><h1>დასწრების აღრიცხვა</h1><p>დასწრების ჟურნალის გამოსაყენებლად ჯერ შექმენით ბაღი და მასში ასაკობრივი ჯგუფები.</p></div></div>
<section class="content"><div class="card"><div class="card-body"><div class="empty-state"><i class="fas fa-school"></i><h3>ბაღი ჯერ არ არის დამატებული</h3><p>ბაღისა და ჯგუფების შექმნის შემდეგ აქ ავტომატურად გამოჩნდება ჩარიცხული ბავშვების სახელობითი სია.</p>@if(auth()->user()->isUnionAdmin())<a href="{{ route('kindergartens.list') }}" class="btn btn-primary"><i class="fas fa-plus"></i> ბაღების მართვა</a>@endif</div></div></div></section>
@endsection
