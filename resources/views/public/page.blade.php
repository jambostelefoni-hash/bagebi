@extends('layouts.basic')
@section('title',$page->title)
@section('content')
<section class="modern-content-page"><div class="modern-public-container"><nav class="modern-breadcrumb"><a href="{{route('public.home')}}">მთავარი</a><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg><span>{{$page->title}}</span></nav><article class="modern-content-card"><header><span class="modern-eyebrow"><i></i> საჯარო ინფორმაცია</span><h1>{{$page->title}}</h1></header><div class="modern-content-body">{!! nl2br(e($page->body)) !!}</div></article></div></section>
@endsection
