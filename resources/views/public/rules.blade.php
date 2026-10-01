@extends('layouts.basic')
@section('title', 'რეგისტრაციის წესები | ' . ($publicBrand ?? 'ბაღების გაერთიანება'))
@section('content')
<section class="portal-page">
    <div class="portal-container">
        <nav class="portal-breadcrumb" aria-label="გვერდის მდებარეობა">
            <a href="{{ route('public.home') }}">მთავარი</a>
            <span aria-current="page">რეგისტრაციის წესები</span>
        </nav>

        <header class="portal-page-heading">
            <span class="portal-kicker">განაცხადის შევსებამდე</span>
            <h1>რეგისტრაციის წესები</h1>
            <p>რა უნდა იცოდეთ ბავშვის რეგისტრაციის, თავისუფალი ადგილებისა და მომლოდინეთა რიგის შესახებ.</p>
        </header>

        <div class="portal-page-layout">
            <div>
                <ol class="portal-rule-list" aria-label="ძირითადი მოთხოვნები">
                    <li>
                        <h2>შეამოწმეთ ბავშვის ასაკი</h2>
                        <p>სასწავლო წლის დაწყების დღეს ბავშვს უნდა ჰქონდეს შესრულებული 2 წელი და ჯერ არ უნდა იყოს 6 წლის.</p>
                    </li>
                    <li>
                        <h2>მოამზადეთ საჭირო მონაცემები</h2>
                        <p>დაგჭირდებათ ბავშვისა და მშობლის ან წარმომადგენლის პირადი ინფორმაცია და მოქმედი მობილური ნომერი. მონაცემები მიუთითეთ ოფიციალური დოკუმენტების მიხედვით.</p>
                    </li>
                    <li>
                        <h2>აირჩიეთ ბაღი და ჯგუფი</h2>
                        <p>ფორმაში გამოჩნდება თავისუფალი ადგილებისა და რიგში მყოფი ბავშვების რაოდენობა. ადგილის არქონისას განაცხადი მომლოდინეთა რიგში მოხვდება.</p>
                    </li>
                    <li>
                        <h2>შეამოწმეთ შედეგი და შეტყობინებები</h2>
                        <p>შევსების შემდეგ ნახავთ განაცხადის სტატუსს. რიგში ყოფნისას თავისუფალი ადგილის შეთავაზებას SMS-ით მიიღებთ — შეთავაზებას მითითებულ ვადაში უნდა უპასუხოთ.</p>
                    </li>
                </ol>

                <article class="portal-document" id="full-registration-rules">
                    <header>
                        <div>
                            <span class="portal-kicker">ოფიციალური ინფორმაცია</span>
                            <h2>რეგისტრაციის სრული წესები</h2>
                        </div>
                    </header>
                    <div class="portal-prose">{!! nl2br(e($publicRules)) !!}</div>
                </article>
            </div>

            <aside class="portal-aside" aria-label="რეგისტრაციის დახმარება">
                <span class="portal-kicker">მზად ხართ?</span>
                <h2>განაცხადი შეავსეთ ონლაინ</h2>
                <p>ფორმა დაყოფილია ხუთ ეტაპად. ვარსკვლავით მონიშნული ველები სავალდებულოა.</p>
                <a class="portal-button" href="{{ route('children') }}">რეგისტრაციის დაწყება</a>
                <ul>
                    <li><a href="{{ route('public.status-tracker') }}">უკვე შევსებული განაცხადის სტატუსი <span aria-hidden="true">↗</span></a></li>
                    <li><a href="{{ route('public.contact') }}">დახმარება და კონტაქტი <span aria-hidden="true">↗</span></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
@endsection
