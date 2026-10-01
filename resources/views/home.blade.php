@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{asset('css/attendance-risk.css')}}?v={{file_exists(public_path('css/attendance-risk.css'))?filemtime(public_path('css/attendance-risk.css')):'1'}}">@endpush

@section('title', 'სამუშაო დაფა')

@section('content')
<div class="dashboard-page dashboard-workspace">
  <header class="dashboard-heading" data-tour="dashboard-overview">
    <div>
      <span class="dashboard-eyebrow">{{ auth()->user()->isUnionAdmin() ? 'გაერთიანების ადმინისტრაცია' : 'ბაღის დირექტორის პანელი' }}</span>
      <h1>სამუშაო დაფა</h1>
      <p>{{ auth()->user()->isUnionAdmin() ? 'რეგისტრაციისა და სასწავლო პროცესის მიმდინარე სურათი.' : 'დღევანდელი დასწრება, შესასრულებელი ამოცანები და მნიშვნელოვანი გაფრთხილებები.' }}</p>
    </div>
    <div class="dashboard-heading__status" aria-label="რეგისტრაციის მდგომარეობა">
      <span class="status-chip {{ data_get($basic, 'object.isRegistrationStart') ? 'status-chip--success' : 'status-chip--neutral' }}"><x-admin-icon name="circle" /> რეგისტრაცია {{ data_get($basic, 'object.isRegistrationStart') ? 'ღიაა' : 'დახურულია' }}</span>
      <small>{{ auth()->user()->name }}</small>
    </div>
  </header>

  @if(!auth()->user()->isUnionAdmin() && $directorTasks)
    <section class="director-tasks dashboard-priority" aria-label="დღევანდელი ამოცანები" data-tour="director-daily-tasks">
      <div class="director-tasks__head">
        <div><span class="dashboard-eyebrow">{{ $directorTasks['working_day'] ? 'დღევანდელი კონტროლი' : 'დღევანდელი კალენდარი' }}</span><h2>{{ $directorTasks['working_day'] ? 'გასაკეთებელი საქმეები' : 'დღეს არასამუშაო დღეა' }}</h2><p>{{ $directorTasks['working_day'] ? 'აქ ჩანს მხოლოდ თქვენი ბაღის დღევანდელი მოქმედებები.' : 'დასწრების შევსება დღეს საჭირო არ არის.' }}</p></div>
        <a class="btn btn-primary" href="{{route('attendance.index')}}"><x-admin-icon name="user-check" /> დასწრების გახსნა</a>
      </div>
      @if($directorTasks['working_day'])
        <div class="director-task-grid director-task-grid--priority">
          <article class="director-task-card director-task-card--priority"><div class="director-task-card__title"><span class="director-task-icon director-task-icon--amber"><x-admin-icon name="clipboard-check" /></span><div><h3>დასწრება შესავსებია</h3><small>{{$directorTasks['unfilled_groups']->count()}} ჯგუფი</small></div></div><div class="director-task-list">@forelse($directorTasks['unfilled_groups'] as $group)<a href="{{route('attendance.index',['group_id'=>$group->id])}}"><span>{{$group->range}} წ.</span><small>{{$group->recorded}} / {{$group->total}} შევსებულია <x-admin-icon name="chevron-right" /></small></a>@empty<p>ყველა აქტიური ჯგუფის დასწრება შევსებულია.</p>@endforelse</div></article>
          <article class="director-task-card"><div class="director-task-card__title"><span class="director-task-icon director-task-icon--coral"><x-admin-icon name="user-times" /></span><div><h3>დღეს გაცდენილია</h3><small>{{$directorTasks['absent_count']}} ჩანაწერი · მოკლე მიმოხილვა</small></div></div><div class="director-task-list">@forelse($directorTasks['absent'] as $attendance)<div><span>{{optional($attendance->kindergartener)->kids_first_name}} {{optional($attendance->kindergartener)->kids_last_name}}</span><small>{{optional(optional($attendance->kindergartener)->groupRange)->range}} წ.</small></div>@empty<p>დღეს გაცდენა არ დაფიქსირებულა.</p>@endforelse @if($directorTasks['absent_count'] > $directorTasks['absent']->count())<a href="{{route('attendance.index',['date'=>now()->toDateString()])}}"><span>ყველა გაცდენილის ნახვა</span><small><x-admin-icon name="arrow-right" /></small></a>@endif</div></article>
          <article class="director-task-card"><div class="director-task-card__title"><span class="director-task-icon"><x-admin-icon name="file-medical" /></span><div><h3>დოკუმენტი ელოდება პასუხს</h3><small>გაერთიანების განხილვაში</small></div></div><strong class="director-task-number">{{$directorTasks['pending_documents']}}</strong><p>გადაწყვეტილებას იღებს გაერთიანების ადმინისტრაცია.</p></article>
          <article class="director-task-card"><div class="director-task-card__title"><span class="director-task-icon director-task-icon--navy"><x-admin-icon name="envelope-open-text" /></span><div><h3>აქტიური შეთავაზებები</h3><small>{{$directorTasks['offers']->count()}} მოქმედი შეთავაზება</small></div></div><div class="director-task-list">@forelse($directorTasks['offers'] as $offer)<div><span>{{optional(optional($offer->entry)->kindergartener)->kids_first_name}} {{optional(optional($offer->entry)->kindergartener)->kids_last_name}}<small>{{optional(optional(optional($offer->entry)->kindergartener)->groupRange)->range}} წ.</small></span><small>ვადა: {{$offer->expires_at->format('d.m. H:i')}}</small></div>@empty<p>მოქმედი შეთავაზება არ არის.</p>@endforelse</div></article>
        </div>
        @if($directorTasks['attendance_risks']->isNotEmpty())
          <section class="attendance-risk-panel" aria-label="შეჩერების რისკის ქვეშ მყოფი აღსაზრდელები"><div class="attendance-risk-panel__head"><span><x-admin-icon name="exclamation-triangle" /></span><div><h3>შეჩერების რისკის ქვეშ</h3><p>ზღვრამდე 3 ან ნაკლები სამუშაო დღე დარჩა. ითვლება მხოლოდ არასაპატიო გაცდენა.</p></div></div><div class="attendance-risk-list">@foreach($directorTasks['attendance_risks'] as $risk)<a href="{{route('attendance.index',['group_id'=>$risk->child->group_id])}}" class="attendance-risk-row attendance-risk-row--{{$risk->level}}"><span class="attendance-risk-person"><b>{{$risk->child->kids_first_name}} {{$risk->child->kids_last_name}}</b><small>{{optional($risk->child->groupRange)->range ?: '—'}} წ.</small></span><span class="attendance-risk-counts"><small>ზედიზედ <b>{{$risk->streak}}</b></small><small>თვეში <b>{{$risk->monthly}}</b></small></span><strong>{{$risk->remaining > 0 ? 'შეჩერებამდე დარჩა '.$risk->remaining.' დღე' : 'ზღვარი მიღწეულია'}}</strong><x-admin-icon name="chevron-right" /></a>@endforeach</div></section>
        @endif
      @endif
    </section>
  @endif

  <section class="metric-grid" aria-label="ძირითადი მაჩვენებლები" data-tour="dashboard-metrics">
    <a class="metric-card metric-card--navy" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><x-admin-icon name="child" /></span><span class="metric-copy"><span class="metric-label">აღსაზრდელები</span><strong>{{ $kindergartner_count }}</strong><span class="metric-link">სიის ნახვა <x-admin-icon name="arrow-right" /></span></span></a>
    <a class="metric-card metric-card--amber" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><x-admin-icon name="hourglass-half" /></span><span class="metric-copy"><span class="metric-label">მომლოდინე</span><strong>{{ $waiting_count }}</strong><span class="metric-link">რიგის ნახვა <x-admin-icon name="arrow-right" /></span></span></a>
    <a class="metric-card metric-card--coral" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><x-admin-icon name="pause-circle" /></span><span class="metric-copy"><span class="metric-label">შეჩერებული</span><strong>{{ $suspended_count }}</strong><span class="metric-link">დეტალების ნახვა <x-admin-icon name="arrow-right" /></span></span></a>
    @if(auth()->user()->isUnionAdmin())
      <a class="metric-card metric-card--info" href="{{ route('kindergartens.list') }}"><span class="metric-icon"><x-admin-icon name="school" /></span><span class="metric-copy"><span class="metric-label">ბაღები</span><strong>{{ $kindergarten_count }}</strong><span class="metric-link">სტრუქტურის ნახვა <x-admin-icon name="arrow-right" /></span></span></a>
    @else
      <a class="metric-card metric-card--teal" href="{{ route('kindergarteners.index') }}"><span class="metric-icon"><x-admin-icon name="user-check" /></span><span class="metric-copy"><span class="metric-label">ჩარიცხული აღსაზრდელები</span><strong>{{ $enrolled_count }}</strong><span class="metric-link">სიის ნახვა <x-admin-icon name="arrow-right" /></span></span></a>
    @endif
  </section>

  @if(auth()->user()->isUnionAdmin() && $adminOverview)
    <section class="dashboard-panel dashboard-attention" aria-label="ადმინისტრატორის მიმდინარე კონტროლი" data-tour="dashboard-attention">
        <div class="dashboard-panel__head"><div><span class="dashboard-eyebrow">გაერთიანების კონტროლი</span><h2>გასაკეთებელი საქმეები</h2><p>საკითხები, რომლებსაც ადმინისტრაციის რეაგირება ან გადამოწმება სჭირდება.</p></div><a href="{{ route('control-center.index') }}">მართვის ცენტრი <x-admin-icon name="arrow-right" /></a></div>
        <div class="dashboard-attention-list">
          <a href="{{ route('reinstatement.index') }}"><span class="dashboard-attention-icon dashboard-attention-icon--{{ $adminOverview['pending_documents'] ? 'warning' : 'success' }}"><x-admin-icon name="file-medical" /></span><span><strong>აღდგენის დოკუმენტები</strong><small>{{ $adminOverview['pending_documents'] ? 'ელოდება ადმინისტრაციის გადაწყვეტილებას' : 'განსახილველი დოკუმენტი არ არის' }}</small></span><span class="semantic-status semantic-status--{{ $adminOverview['pending_documents'] ? 'warning' : 'success' }}">{{ $adminOverview['pending_documents'] }}</span></a>
          <a href="{{ route('operations.index') }}"><span class="dashboard-attention-icon dashboard-attention-icon--info"><x-admin-icon name="hourglass-half" /></span><span><strong>აქტიური შეთავაზებები</strong><small>მშობლის პასუხის მოლოდინში მყოფი შეთავაზებები</small></span><span class="semantic-status semantic-status--info">{{ $adminOverview['active_offers'] }}</span></a>
          <a href="{{ route('data-quality.index') }}"><span class="dashboard-attention-icon dashboard-attention-icon--{{ $adminOverview['open_quality_issues'] ? 'warning' : 'success' }}"><x-admin-icon name="tasks" /></span><span><strong>მონაცემების ხარისხი</strong><small>{{ $adminOverview['open_quality_issues'] ? 'ღია საკითხები საჭიროებს გადამოწმებას' : 'ღია პრობლემა არ არის' }}</small></span><span class="semantic-status semantic-status--{{ $adminOverview['open_quality_issues'] ? 'warning' : 'success' }}">{{ $adminOverview['open_quality_issues'] }}</span></a>
          <a href="{{ route('operations.index', ['notification_status' => 'failed']) }}"><span class="dashboard-attention-icon dashboard-attention-icon--{{ $adminOverview['failed_sms'] ? 'danger' : 'success' }}"><x-admin-icon name="exclamation-triangle" /></span><span><strong>ჩავარდნილი SMS</strong><small>{{ $adminOverview['failed_sms'] ? 'გადაამოწმეთ ნომერი და ხელახლა გაგზავნეთ' : 'ჩავარდნილი შეტყობინება არ არის' }}</small></span><span class="semantic-status semantic-status--{{ $adminOverview['failed_sms'] ? 'danger' : 'success' }}">{{ $adminOverview['failed_sms'] }}</span></a>
        </div>
    </section>
  @endif

  <section class="dashboard-columns" data-tour="dashboard-status">
    <article class="dashboard-panel"><div class="dashboard-panel__head"><div><span class="dashboard-eyebrow">სასწავლო წელი</span><h2>მიმდინარე მდგომარეობა</h2></div><span class="system-state {{ data_get($basic, 'object.isLearningStart') ? 'system-state--active' : '' }}"><x-admin-icon name="circle" />{{ data_get($basic, 'object.isLearningStart') ? 'აქტიურია' : 'მოლოდინშია' }}</span></div><dl class="status-list"><div><dt><x-admin-icon name="calendar-alt" /> დაწყება</dt><dd>{{ data_get($date, 'object.start') ?: 'არ არის მითითებული' }}</dd></div><div><dt><x-admin-icon name="calendar-check" /> დასრულება</dt><dd>{{ data_get($date, 'object.end') ?: 'არ არის მითითებული' }}</dd></div><div><dt><x-admin-icon name="user-plus" /> რეგისტრაცია</dt><dd><span class="status-chip {{ data_get($basic, 'object.isRegistrationStart') ? 'status-chip--success' : 'status-chip--neutral' }}">{{ data_get($basic, 'object.isRegistrationStart') ? 'ღიაა' : 'დახურულია' }}</span></dd></div></dl></article>
    <article class="dashboard-panel dashboard-panel--actions"><div class="dashboard-panel__head"><div><span class="dashboard-eyebrow">ხშირი მოქმედებები</span><h2>სწრაფი გადასვლა</h2></div></div><div class="quick-actions"><a href="{{ route('attendance.index') }}"><span><x-admin-icon name="user-check" /></span><b>დასწრების აღრიცხვა</b><x-admin-icon name="chevron-right" /></a><a href="{{ route('kindergarteners.index') }}"><span><x-admin-icon name="users" /></span><b>აღსაზრდელების მართვა</b><x-admin-icon name="chevron-right" /></a>@if(auth()->user()->isUnionAdmin())<a href="{{ route('reinstatement.index') }}"><span><x-admin-icon name="file-medical" /></span><b>აღდგენის მოთხოვნები</b><x-admin-icon name="chevron-right" /></a><a href="{{ route('calendar.index') }}"><span><x-admin-icon name="calendar-check" /></span><b>სამუშაო კალენდარი</b><x-admin-icon name="chevron-right" /></a>@else<a href="{{ route('attendance.export', 'xlsx') }}"><span><x-admin-icon name="file-excel" /></span><b>დასწრების ანგარიში</b><x-admin-icon name="chevron-right" /></a>@endif</div></article>
  </section>
</div>
@endsection
