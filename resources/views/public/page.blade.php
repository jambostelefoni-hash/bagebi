@extends('layouts.basic')
@section('title', $page->title)
@section('content')
@php($isContact = $page->slug === 'contact')
@php($isNews = $page->slug === 'news')
<section class="portal-page">
    <div class="portal-container">
        <nav class="portal-breadcrumb" aria-label="გვერდის მდებარეობა">
            <a href="{{ route('public.home') }}">მთავარი</a>
            <span aria-current="page">{{ $page->title }}</span>
        </nav>

        <header class="portal-page-heading">
            <span class="portal-kicker">{{ $isContact ? 'მშობლების მხარდაჭერა' : 'გაერთიანების ინფორმაცია' }}</span>
            <h1>{{ $page->title }}</h1>
            <p>{{ $isContact ? 'რეგისტრაციასთან ან ბაღის მომსახურებასთან დაკავშირებული კითხვებისთვის დაგვიკავშირდით.' : ($isNews ? 'ოფიციალური განცხადებები რეგისტრაციისა და ბაღების საქმიანობის შესახებ.' : 'ინფორმაცია მშობლებისა და კანონიერი წარმომადგენლებისთვის.') }}</p>
        </header>

        <div class="portal-page-layout">
            <article class="portal-document">
                <header>
                    <h2>{{ $isNews ? 'განცხადება' : ($isContact ? 'საკონტაქტო ინფორმაცია' : $page->title) }}</h2>
                    @if($isNews)
                        <time datetime="{{ $page->updated_at->toDateString() }}">განახლდა: {{ $page->updated_at->format('d.m.Y') }}</time>
                    @endif
                </header>
                <div class="portal-prose">{!! nl2br(e($page->body)) !!}</div>
            </article>

            <aside class="portal-aside" aria-label="სასარგებლო ბმულები">
                <span class="portal-kicker">ონლაინ მომსახურება</span>
                <h2>{{ $isContact ? 'უკვე შეავსეთ განაცხადი?' : 'რეგისტრაცია ბაღში' }}</h2>
                <p>{{ $isContact ? 'განაცხადის მიმდინარე მდგომარეობის ნახვა შეგიძლიათ ბავშვის პირადი ნომრით და მშობლის მობილური ნომრის ბოლო ოთხი ციფრით.' : 'განაცხადის შევსებამდე გაეცანით ასაკობრივ მოთხოვნებს, საჭირო მონაცემებსა და მომლოდინეთა რიგის წესს.' }}</p>
                <ul>
                    <li><a href="{{ route('public.status-tracker') }}">განაცხადის სტატუსის შემოწმება <span aria-hidden="true">↗</span></a></li>
                    <li><a href="{{ route('public.registration-rules') }}">რეგისტრაციის წესები <span aria-hidden="true">↗</span></a></li>
                    @unless($isContact)
                        <li><a href="{{ route('public.contact') }}">დაგვიკავშირდით <span aria-hidden="true">↗</span></a></li>
                    @endunless
                </ul>
                <a class="portal-button" href="{{ route('children') }}">რეგისტრაციის დაწყება</a>
            </aside>
        </div>
    </div>
</section>
@endsection
