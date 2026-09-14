@extends('layouts.app')
@section('title','საჯარო გვერდები')
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">საჯარო საიტის კონტენტი</span><h1>საჯარო გვერდები</h1><p>მართეთ საიტზე გამოქვეყნებული ყველა ტექსტი და რეგისტრაციის ინფორმაცია ერთი სივრციდან.</p></div></div>
<section class="content">
  <div class="public-content-section"><div class="public-content-heading"><div><span>რეგისტრაციის კონტენტი</span><h2>ფორმის ტექსტი და წესები</h2></div><small>მშობელი ამ ინფორმაციას რეგისტრაციის პროცესში ხედავს</small></div>
    <div class="public-page-grid">
      <article class="public-page-card public-page-card--featured"><span><i class="fas fa-comment-alt"></i></span><div><small>რეგისტრაციის ფორმა</small><h2>რეგისტრაციის ტექსტი</h2><p>მართეთ ფორმის სათაური, აღწერა და მშობლისთვის განკუთვნილი საინფორმაციო ტექსტი.</p></div><a href="{{route('registration-texts.index')}}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> რედაქტირება</a></article>
      <article class="public-page-card public-page-card--featured"><span><i class="fas fa-book-open"></i></span><div><small>წესები და პირობები</small><h2>რეგისტრაციის წესები</h2><p>განაახლეთ ასაკობრივი მოთხოვნები, პრიორიტეტები, რიგისა და დოკუმენტების წესები.</p></div><a href="{{route('registration-texts.rules')}}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> რედაქტირება</a></article>
    </div>
  </div>

  <div class="public-content-section"><div class="public-content-heading"><div><span>საიტის გვერდები</span><h2>ძირითადი საჯარო ინფორმაცია</h2></div><small>ცვლილებები შენახვისთანავე გამოქვეყნდება</small></div>
    <div class="public-page-grid">
      @foreach($pages as $page)
        @continue($page->slug==='about')
        @php($icons=['home'=>'fa-home','news'=>'fa-bullhorn','contact'=>'fa-address-book'])
        <article class="public-page-card"><span><i class="fas {{$icons[$page->slug]??'fa-file-alt'}}"></i></span><div><small>/{{$page->slug}}</small><h2>{{$page->title}}</h2><p>{{\Illuminate\Support\Str::limit(strip_tags($page->body),120)}}</p></div><a href="{{route('public-pages.edit',$page->slug)}}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> რედაქტირება</a></article>
      @endforeach
    </div>
  </div>
</section>
@endsection
