@extends('layouts.app')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;500;600;700&display=swap');

  * { box-sizing: border-box; font-family: 'Noto Sans Georgian', sans-serif; }

  /* ── PAGE HEADER ── */
  .modern-header {
    background: #162840;
    padding: 32px 28px;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
  }

  .modern-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 80% 50%, rgba(74,158,255,0.07) 0%, transparent 65%);
    pointer-events: none;
  }

  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 100%;
    position: relative;
    z-index: 1;
  }

  .header-content h1 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 5px 0;
    letter-spacing: -0.01em;
  }

  .header-content p {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.45);
    margin: 0;
    font-weight: 400;
  }

  .header-emblem img {
    width: 52px;
    height: auto;
    opacity: 0.85;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
  }

  /* ── CONTENT SECTION ── */
  .content {
    background: #f0f4f8;
    padding: 28px 0 40px;
  }

  /* ── STAT CARDS ── */
  .stat-card {
    display: block;
    background: #ffffff;
    border-radius: 4px;
    padding: 22px 24px;
    margin-bottom: 20px;
    text-decoration: none;
    border-left: 3px solid #162840;
    box-shadow: 0 1px 4px rgba(22,40,64,0.07);
    transition: box-shadow 0.18s, transform 0.18s, border-color 0.18s;
  }

  .stat-card:hover {
    box-shadow: 0 4px 20px rgba(22,40,64,0.13);
    transform: translateY(-2px);
    border-left-color: #4a9eff;
    text-decoration: none;
  }

  .stat-card-content p {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8a96a8;
    margin: 0 0 8px 0;
  }

  .stat-card-content h3 {
    font-size: 1.9rem;
    font-weight: 700;
    color: #162840;
    margin: 0;
    line-height: 1;
  }

  .stat-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 4px;
    background: #f0f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .stat-card-icon i {
    font-size: 1.1rem;
    color: #162840;
    opacity: 0.6;
  }

  .shimmer {
    height: 32px;
    width: 80px;
    border-radius: 4px;
    background: linear-gradient(90deg, #e8edf3 25%, #f4f7fa 50%, #e8edf3 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
  }

  @keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  /* ── INFO CARD ── */
  .info-card {
    background: #ffffff;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(22,40,64,0.07);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .info-card-header {
    background: #162840;
    padding: 16px 24px;
  }

  .info-card-header h2 {
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.75);
    margin: 0;
  }

  .info-card-header h2 i {
    opacity: 0.55;
  }

  .info-card-body {
    padding: 0;
  }

  .info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid #f0f4f8;
    transition: background 0.15s;
  }

  .info-row:last-child {
    border-bottom: none;
  }

  .info-row:hover {
    background: #fafbfd;
  }

  .info-row h3 {
    font-size: 0.88rem;
    font-weight: 600;
    color: #162840;
    margin: 0 0 3px 0;
  }

  .info-row p {
    font-size: 0.75rem;
    color: #8a96a8;
    margin: 0;
  }

  /* Status badges */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 2px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }

  .status-badge.active {
    background: #e6f4ea;
    color: #1a7f3c;
  }

  .status-badge.inactive {
    background: #f0f4f8;
    color: #4a5568;
  }

  .status-badge.yes {
    background: #e6f4ea;
    color: #1a7f3c;
  }

  .status-badge.no {
    background: #fde8e8;
    color: #c53030;
  }

  /* ── EXPORT CARD ── */
  .export-card {
    background: #ffffff;
    border-radius: 4px;
    padding: 22px 24px;
    box-shadow: 0 1px 4px rgba(22,40,64,0.07);
    margin-bottom: 20px;
    border-left: 3px solid #1a7f3c;
  }

  .export-card h3 {
    font-size: 0.9rem;
    font-weight: 600;
    color: #162840;
    margin: 0 0 3px 0;
  }

  .export-card p {
    font-size: 0.78rem;
    color: #8a96a8;
    margin: 0;
  }

  .export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #162840;
    color: #ffffff;
    border-radius: 4px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.03em;
    transition: background 0.18s, transform 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .export-btn:hover {
    background: #1e3a5f;
    transform: translateY(-1px);
    color: #ffffff;
    text-decoration: none;
  }

  /* ── SCROLL TOP ── */
  .scroll-top {
    position: fixed;
    bottom: 28px;
    right: 28px;
    width: 40px;
    height: 40px;
    background: #162840;
    color: #ffffff;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 12px rgba(22,40,64,0.25);
    transition: background 0.18s, transform 0.15s;
    z-index: 999;
  }

  .scroll-top:hover {
    background: #1e3a5f;
    transform: translateY(-2px);
  }

  .scroll-top i {
    font-size: 0.85rem;
  }
</style>

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
    <div class="row">
      <div class="col-lg-3 col-md-6 col-sm-12">
        <a href="#" class="stat-card">
          <div class="d-flex justify-content-between align-items-center">
            <div class="stat-card-content">
              <p>მომხმარებელი</p>
              @if($user_count)
                <h3>{{ $user_count }}</h3>
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
                <h3>{{ $municipality_count }}</h3>
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
                <h3>{{ $kindergartner_count }}</h3>
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
                <h3>{{ $kindergarten_count }}</h3>
              @else
                <div class="shimmer"></div>
              @endif
            </div>
            <div class="stat-card-icon"><i class="fas fa-school"></i></div>
          </div>
        </a>
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
          <span class="status-badge {{ data_get($basic,'object.isLearningStart') ? 'active' : 'inactive' }}">
            <i class="fas fa-graduation-cap"></i>
            {{ data_get($basic,'object.isLearningStart') ? 'მიმდინარე' : 'დასრულებული' }}
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
          <span class="status-badge {{ data_get($basic,'object.canPorting') ? 'yes' : 'no' }}">
            <i class="fas {{ data_get($basic,'object.canPorting') ? 'fa-check' : 'fa-times' }}"></i>
            {{ data_get($basic,'object.canPorting') ? 'დიახ' : 'არა' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Export -->
    <div class="export-card d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <i class="fas fa-file-excel text-success" style="font-size:1.6rem;"></i>
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