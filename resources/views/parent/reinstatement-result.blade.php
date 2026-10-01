@extends('layouts.basic')
@section('title','დოკუმენტი მიღებულია')
@section('content')
<section class="modern-action-page"><div class="modern-action-card"><div class="modern-action-icon modern-action-icon--success"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/></svg></div><span class="modern-card-kicker">ატვირთვა დასრულებულია</span><h1>დოკუმენტი მიღებულია</h1><p>გაერთიანების ადმინისტრაცია განიხილავს მოთხოვნას და გადაწყვეტილებას SMS-ით გაცნობებთ.</p><a class="modern-action-link" href="{{route('public.home')}}">მთავარ გვერდზე დაბრუნება →</a></div></section>
@endsection
