@extends('layouts.basic')
@section('title',$accepted?'ადგილი დადასტურებულია':'უარი მიღებულია')
@section('content')
<section class="modern-action-page"><div class="modern-action-card"><div class="modern-action-icon {{$accepted?'modern-action-icon--success':'modern-action-icon--neutral'}}"><svg viewBox="0 0 24 24">@if($accepted)<path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/>@else<path d="M6 6l12 12M18 6 6 18"/><circle cx="12" cy="12" r="10"/>@endif</svg></div><span class="modern-card-kicker">პასუხი მიღებულია</span><h1>{{$accepted?'ადგილი დადასტურებულია':'უარი მიღებულია'}}</h1><p>{{$accepted?'ბავშვის სტატუსი წარმატებით შეიცვალა ჩარიცხულად.':'ადგილი შეთავაზებული იქნება რიგის შემდეგი კანდიდატისთვის.'}}</p><a class="modern-action-link" href="{{route('public.home')}}">მთავარ გვერდზე დაბრუნება →</a></div></section>
@endsection
