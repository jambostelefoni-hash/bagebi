@extends('layouts.app')

@section('content')
<div class="dashboard-page">
  <section class="dashboard-hero">
    <div>
      <span class="dashboard-eyebrow">{{ auth()->user()->isUnionAdmin() ? 'გაერთიანების ადმინისტრაცია' : 'ბაღის დირექტორის პანელი' }}</span>
      <h1>კეთილი იყოს თქვენი მობრძანება, {{ auth()->user()->name }}</h1>
      <p>აქ ნახავთ დღევანდელ მდგომარეობას, საჭირო მოქმედებებსა და ძირითად მაჩვენებლებს.</p>
    </div>
    <div class="dashboard-hero-mark" aria-hidden="true"><img src="{{ asset('images/bagebi-brand-mark.png') }}" alt=""></div>
  </section>

  <section class="metric-grid" aria-label="ძირითადი მაჩვენებლები">
    <a class="metric-card metric-card--teal" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><i class="fas fa-child"></i></span><span class="metric-label">აღსაზრდელები</span><strong>{{ $kindergartner_count }}</strong><span class="metric-link">სიის ნახვა <i class="fas fa-arrow-right"></i></span></a>
    <a class="metric-card metric-card--amber" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><i class="fas fa-hourglass-half"></i></span><span class="metric-label">მომლოდინე</span><strong>{{ $waiting_count }}</strong><span class="metric-link">რიგის ნახვა <i class="fas fa-arrow-right"></i></span></a>
    <a class="metric-card metric-card--coral" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><i class="fas fa-pause-circle"></i></span><span class="metric-label">შეჩერებული</span><strong>{{ $suspended_count }}</strong><span class="metric-link">დეტალების ნახვა <i class="fas fa-arrow-right"></i></span></a>
    <a class="metric-card metric-card--navy" href="{{ auth()->user()->isUnionAdmin() ? route('kindergartens.list') : route('attendance.index') }}"><span class="metric-icon"><i class="fas {{ auth()->user()->isUnionAdmin() ? 'fa-school' : 'fa-user-check' }}"></i></span><span class="metric-label">{{ auth()->user()->isUnionAdmin() ? 'ბაღები' : 'დღიური დასწრება' }}</span><strong>{{ auth()->user()->isUnionAdmin() ? $kindergarten_count : $enrolled_count }}</strong><span class="metric-link">გადასვლა <i class="fas fa-arrow-right"></i></span></a>
  </section>

  <section class="dashboard-columns">
    <article class="dashboard-panel">
      <div class="dashboard-panel__head"><div><span class="dashboard-eyebrow">მიმდინარე პროცესი</span><h2>სისტემის მდგომარეობა</h2></div><span class="system-state {{ data_get($basic, 'object.isLearningStart') ? 'system-state--active' : '' }}"><i class="fas fa-circle"></i>{{ data_get($basic, 'object.isLearningStart') ? 'აქტიურია' : 'მოლოდინშია' }}</span></div>
      <dl class="status-list">
        <div><dt><i class="far fa-calendar-alt"></i> სასწავლო წლის დაწყება</dt><dd>{{ data_get($date, 'object.start') ?: 'არ არის მითითებული' }}</dd></div>
        <div><dt><i class="far fa-calendar-check"></i> სასწავლო წლის დასრულება</dt><dd>{{ data_get($date, 'object.end') ?: 'არ არის მითითებული' }}</dd></div>
        <div><dt><i class="fas fa-user-plus"></i> რეგისტრაცია</dt><dd><span class="status-chip {{ data_get($basic, 'object.isRegistrationStart') ? 'status-chip--success' : '' }}">{{ data_get($basic, 'object.isRegistrationStart') ? 'ღიაა' : 'დახურულია' }}</span></dd></div>
      </dl>
    </article>
    <article class="dashboard-panel dashboard-panel--actions">
      <div class="dashboard-panel__head"><div><span class="dashboard-eyebrow">ყოველდღიური მუშაობა</span><h2>სწრაფი მოქმედებები</h2></div></div>
      <div class="quick-actions">
        <a href="{{ route('attendance.index') }}"><span><i class="fas fa-user-check"></i></span><b>დასწრების აღრიცხვა</b><i class="fas fa-chevron-right"></i></a>
        <a href="{{ route('kindergarteners.index') }}"><span><i class="fas fa-users"></i></span><b>აღსაზრდელების მართვა</b><i class="fas fa-chevron-right"></i></a>
        @if(auth()->user()->isUnionAdmin())
          <a href="{{ route('reinstatement.index') }}"><span><i class="fas fa-file-medical"></i></span><b>აღდგენის მოთხოვნები</b><i class="fas fa-chevron-right"></i></a>
          <a href="{{ route('calendar.index') }}"><span><i class="fas fa-calendar-check"></i></span><b>სამუშაო კალენდარი</b><i class="fas fa-chevron-right"></i></a>
        @else
          <a href="{{ route('attendance.export', 'xlsx') }}"><span><i class="fas fa-file-excel"></i></span><b>დასწრების ანგარიში</b><i class="fas fa-chevron-right"></i></a>
        @endif
      </div>
    </article>
  </section>

  <section class="dashboard-callout">
    <div class="dashboard-callout__icon"><i class="fas fa-file-excel"></i></div>
    <div><h2>აღსაზრდელების Excel ექსპორტი</h2><p>{{ auth()->user()->isUnionAdmin() ? 'ჩამოტვირთეთ ყველა ბაღის აღსაზრდელების სრული მონაცემები და მიმდინარე სტატუსები.' : 'ჩამოტვირთეთ თქვენი ბაღის აღსაზრდელების სრული მონაცემები და მიმდინარე სტატუსები.' }}</p></div>
    <a class="btn btn-success" href="{{ route('kindergarteners.export') }}"><i class="fas fa-download"></i> Excel ჩამოტვირთვა</a>
  </section>

  @if(auth()->user()->isUnionAdmin())
    <section class="dashboard-callout"><div class="dashboard-callout__icon"><i class="fas fa-user-shield"></i></div><div><h2>გაერთიანების სრული კონტროლი</h2><p>მართეთ ბაღები, ტევადობები, დირექტორები, სამუშაო კალენდარი და ცვლილებების აუდიტი ერთი ადგილიდან.</p></div><a class="btn btn-primary" href="{{ route('kindergartens.list') }}">ბაღების მართვა</a></section>
  @endif
</div>
@endsection
