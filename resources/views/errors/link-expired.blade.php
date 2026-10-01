@extends('layouts.basic')
@section('title', 'ბმულის ვადა ამოიწურა')
@section('content')
<section class="status-page"><div class="modern-public-container status-page__container">
    <article class="status-not-found status-not-found--expired">
        <span><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <div><h2>ბმულის ვადა ამოიწურა</h2><p>ეს ბმული უკვე გამოყენებულია ან მისი მოქმედების ვადა დასრულდა. ახალი ბმულის მისაღებად დაუკავშირდით ბაღის ადმინისტრაციას.</p><p class="status-not-found__action"><a class="modern-primary-button" href="{{ route('public.home') }}">მთავარ გვერდზე დაბრუნება</a></p></div>
    </article>
</div></section>
@endsection
