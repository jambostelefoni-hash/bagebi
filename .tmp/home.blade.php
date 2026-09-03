@extends('layouts.app')
@section('content')


<div class="modern-header">
    <div class="container-fluid header-content">
        <div>
            <h1>ზოგადი ინფორმაცია</h1>
            <p>სიღნაღის მუნიციპალიტეტის სკოლამდელი აღზრდის დაწესებულებათა გაერთიანება</p>
        </div>
        <div class="header-emblem">
            <img src="{{ asset('images/city-badge.png') }}" alt="City Emblem">
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <a href="#" class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-card-content">
                                <p>მომხმარებელი</p>
                                @if($user_count)
                                    <h3>{{$user_count}}</h3>
                                @else
                                    <div class="shimmer"></div>
                                @endif
                            </div>
                            <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <a href="{{ route('municipalities.list') }}" class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-card-content">
                                <p>მუნიციპალიტეტი</p>
                                @if($municipality_count)
                                    <h3>{{$municipality_count}}</h3>
                                @else
                                    <div class="shimmer"></div>
                                @endif
                            </div>
                            <div class="stat-card-icon"><i class="fas fa-city"></i></div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <a href="{{ route('kindergarteners.index') }}" class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-card-content">
                                <p>ბავშვი</p>
                                @if($kindergartner_count)
                                    <h3>{{$kindergartner_count}}</h3>
                                @else
                                    <div class="shimmer"></div>
                                @endif
                            </div>
                            <div class="stat-card-icon"><i class="fas fa-child"></i></div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <a href="{{ route('kindergartens.list') }}" class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-card-content">
                                <p>ბაღი</p>
                                @if($kindergarten_count)
                                    <h3>{{$kindergarten_count}}</h3>
                                @else
                                    <div class="shimmer"></div>
                                @endif
                            </div>
                            <div class="stat-card-icon"><i class="fas fa-school"></i></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-header">
                <h2><i class="fas fa-info-circle me-2"></i> სისტემის ინფორმაცია</h2>
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div>
                        <h3>სწავლის სტატუსი</h3>
                        <p>ამჟამინდელი მდგომარეობა</p>
                    </div>
                    <span class="status-badge {{ data_get($basic,'object.isLearningStart') ? 'active':'inactive' }}">
                        <i class="fas fa-graduation-cap"></i>
                        {{ data_get($basic,'object.isLearningStart') ? 'მიმდინარე':'დასრულებული' }}
                    </span>
                </div>
                <div class="info-row">
                    <div>
                        <h3>სწავლის დაწყების დრო</h3>
                        <p>პროცესის საწყისი თარიღი</p>
                    </div>
                    <span class="status-badge inactive">{{ data_get($date,'object.start') ?: 'მითითებული არ არის' }}</span>
                </div>
                <div class="info-row">
                    <div>
                        <h3>სწავლის დასრულების დრო</h3>
                        <p>პროცესის საბოლოო თარიღი</p>
                    </div>
                    <span class="status-badge inactive">{{ data_get($date,'object.end') ?: 'მითითებული არ არის' }}</span>
                </div>
                <div class="info-row">
                    <div>
                        <h3>პორტირების ნებართვა</h3>
                        <p>მონაცემების გადატანის უფლება</p>
                    </div>
                    <span class="status-badge {{ data_get($basic,'object.canPorting') ? 'yes':'no' }}">
                        <i class="fas {{ data_get($basic,'object.canPorting') ? 'fa-check':'fa-times' }}"></i>
                        {{ data_get($basic,'object.canPorting') ? 'დიახ':'არა' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Export -->
        <div class="export-card d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-file-excel text-success me-3" style="font-size:2rem;"></i>
                <div>
                    <h3>მონაცემების ექსპორტი</h3>
                    <p>აღსაზრდელების სრული ცხრილის ჩამოტვირთვა</p>
                </div>
            </div>
            <a href="{{ route('kindergarteners.export') }}" class="export-btn">
                <i class="fas fa-download"></i> Excel ჩამოტვირთვა
            </a>
        </div>
    </div>
</section>

<div class="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fas fa-arrow-up"></i>
</div>
@endsection
