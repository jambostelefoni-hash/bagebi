@extends('layouts.app')
@section('title', 'დასწრების აღრიცხვა')
@push('page-styles')
<link rel="stylesheet" href="{{ asset('css/attendance-workbench.css') }}?v={{ filemtime(public_path('css/attendance-workbench.css')) }}">
@endpush
@section('content')
@php
  $savedStatuses = $children->mapWithKeys(fn($child) => [$child->id => optional($child->attendances->first())->status]);
  $statusCounts = $savedStatuses->filter()->countBy();
  $recordedCount = $savedStatuses->filter()->count();
@endphp
<div class="content-header modern-page-header attendance-page-header"><div class="container-fluid"><div class="page-heading-row"><div><span class="dashboard-eyebrow">ყოველდღიური ჟურნალი</span><h1>დასწრების აღრიცხვა</h1><p>მონიშნეთ თითოეული ბავშვის სტატუსი და შეინახეთ დასრულებული ჟურნალი.</p></div><div class="page-actions">@if($isWorkingDay && $children->isNotEmpty())<button class="btn btn-outline-primary" type="button" data-bulk-status="present"><i class="fas fa-check"></i> ყველა მოვიდა</button>@endif<button class="btn btn-primary" type="submit" form="attendance-form" @disabled($children->isEmpty())><i class="fas fa-save"></i> შენახვა</button></div></div></div></div>

<section class="content attendance-workbench">
  <div class="attendance-toolbar card"><div class="card-body">
    <form method="GET" class="attendance-filter-form">
      @if(auth()->user()->isUnionAdmin())<label><span>ბაღი</span><select name="kindergarten_id" class="custom-select" required>@foreach($gardens as $id=>$name)<option value="{{$id}}" @selected($gardenId==$id)>{{$name}}</option>@endforeach</select></label>@endif
      <label><span>თარიღი</span><input type="date" name="date" value="{{$date}}" max="{{now()->toDateString()}}" class="form-control"></label>
      <label><span>ჯგუფი</span><select name="group_id" class="custom-select"><option value="">ყველა ჯგუფი</option>@foreach($groups as $id=>$name)<option value="{{$id}}" @selected($groupId==$id)>{{$name}} წ.</option>@endforeach</select></label>
      <button class="btn btn-outline-primary"><i class="fas fa-filter"></i> ჩვენება</button>
    </form>
    <div class="attendance-toolbar-state attendance-toolbar-state--{{$isWorkingDay?'working':'closed'}}"><i class="fas {{$isWorkingDay?'fa-calendar-check':'fa-calendar-times'}}"></i><span>{{$isWorkingDay?'სამუშაო დღე':'არასამუშაო დღე'}}</span></div>
  </div></div>

  <div class="attendance-stats" aria-label="დასწრების მოკლე მაჩვენებლები">
    <article><strong id="attendance-total-count">{{$children->count()}}</strong><span><i class="attendance-dot attendance-dot--total"></i> სულ</span></article>
    <article><strong id="attendance-present-count">{{$statusCounts->get('present',0)}}</strong><span><i class="attendance-dot attendance-dot--present"></i> დასწრებული</span></article>
    <article><strong id="attendance-absent-count">{{$statusCounts->get('absent',0)}}</strong><span><i class="attendance-dot attendance-dot--absent"></i> გაცდენილი</span></article>
    <article><strong id="attendance-excused-count">{{$statusCounts->get('excused',0)}}</strong><span><i class="attendance-dot attendance-dot--excused"></i> საპატიო</span></article>
    <article><strong id="attendance-unmarked-count">{{max(0,$children->count()-$recordedCount)}}</strong><span><i class="attendance-dot attendance-dot--unmarked"></i> მოუნიშნავი</span></article>
  </div>

  <form method="POST" action="{{route('attendance.store')}}" id="attendance-form" class="attendance-register">@csrf<input type="hidden" name="date" value="{{$date}}"><input type="hidden" name="kindergarten_id" value="{{$gardenId}}">
    <div class="attendance-register__progress"><span>მონიშნულია <strong id="attendance-recorded-count">{{$recordedCount}}</strong> / {{$children->count()}}</span><progress id="attendance-progress" value="{{$recordedCount}}" max="{{max(1,$children->count())}}">{{$recordedCount}} / {{$children->count()}}</progress></div>
    <div class="attendance-register__rows">
      @forelse($children as $child)
      @php($attendance=$child->attendances->first())
      @php($current=$attendance?->status ?? ($isWorkingDay ? '' : 'non_working'))
      @php($risk=$attendanceRisks->get($child->id))
      <article class="attendance-person {{$risk?'attendance-person--'.$risk->level:''}}" data-attendance-row>
        <div class="attendance-person__identity"><span>{{mb_strtoupper(mb_substr($child->kids_first_name,0,1))}}</span><div><strong>{{$child->kids_first_name}} {{$child->kids_last_name}}</strong><small>{{optional($child->groupRange)->range ? optional($child->groupRange)->range.' წ.' : 'ჯგუფი არ არის მითითებული'}} · № {{$child->kids_personal_number}}</small></div></div>
        @if($isWorkingDay)
        <div class="attendance-choice" role="group" aria-label="{{$child->kids_first_name}} {{$child->kids_last_name}} — დასწრების სტატუსი">
          <input type="hidden" name="records[{{$child->id}}]" value="{{$current}}" class="attendance-status" required>
          <button type="button" class="attendance-choice__button attendance-choice__button--present {{$current==='present'?'is-selected':''}}" data-status="present"><i class="fas fa-check"></i><span>დასწრებული</span></button>
          <button type="button" class="attendance-choice__button attendance-choice__button--absent {{$current==='absent'?'is-selected':''}}" data-status="absent"><i class="fas fa-times"></i><span>გაცდენილი</span></button>
          <button type="button" class="attendance-choice__button attendance-choice__button--excused {{$current==='excused'?'is-selected':''}}" data-status="excused"><i class="fas fa-file-medical"></i><span>საპატიო</span></button>
        </div>
        @else
        <div class="attendance-nonworking"><input type="hidden" name="records[{{$child->id}}]" value="non_working" class="attendance-status"><i class="fas fa-calendar-times"></i> არასამუშაო დღე</div>
        @endif
        <label class="attendance-note"><span class="sr-only">შენიშვნა — {{$child->kids_first_name}} {{$child->kids_last_name}}</span><input type="text" name="notes[{{$child->id}}]" value="{{old('notes.'.$child->id,$attendance?->note)}}" maxlength="500" class="form-control" placeholder="შენიშვნა"></label>
        @if($risk)<div class="attendance-person__risk"><i class="fas fa-exclamation-triangle"></i><span><strong>{{$risk->remaining>0?'შეჩერებამდე დარჩა '.$risk->remaining.' დღე':'გაცდენის ზღვარი მიღწეულია'}}</strong><small>ზედიზედ {{$risk->streak}} · თვეში {{$risk->monthly}}</small></span></div>@endif
      </article>
      @empty
      <div class="empty-state"><i class="fas fa-user-check"></i><h3>აღსაზრდელები ვერ მოიძებნა</h3><p>შეცვალეთ ბაღი, ჯგუფი ან თარიღი.</p></div>
      @endforelse
    </div>
    @if($children->isNotEmpty())<div class="attendance-savebar"><span id="attendance-save-hint">{{$isWorkingDay?'შენახვამდე მონიშნეთ ყველა აღსაზრდელი.':'თარიღი სამუშაო კალენდარში არასამუშაო დღედ არის მონიშნული.'}}</span><button class="btn btn-primary"><i class="fas fa-save"></i> დასწრების შენახვა</button></div>@endif
  </form>

  <details class="attendance-report-panel"><summary><span><i class="fas fa-file-export"></i><strong>პერიოდის ანგარიშები</strong><small>Excel, PDF ან CSV ფორმატში ჩამოტვირთვა</small></span><i class="fas fa-chevron-down"></i></summary><form method="GET" class="attendance-report-form"><input type="hidden" name="kindergarten_id" value="{{$gardenId}}"><label><span>თარიღიდან</span><input type="date" name="date_from" value="{{request('date_from',$date)}}" class="form-control" required></label><label><span>თარიღამდე</span><input type="date" name="date_to" value="{{request('date_to',$date)}}" class="form-control" required></label><button class="btn btn-outline-success" formaction="{{route('attendance.export','xlsx')}}"><i class="fas fa-file-excel"></i> Excel</button><button class="btn btn-outline-primary" formaction="{{route('attendance.export','pdf')}}" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button><button class="btn btn-outline-secondary" formaction="{{route('attendance.export','csv')}}"><i class="fas fa-file-csv"></i> CSV</button></form></details>

  @if(auth()->user()->isUnionAdmin())<div class="attendance-admin-action"><div><strong>გაცდენების წესების ხელით შემოწმება</strong><span>ამოწმებს 10 ზედიზედ ან თვეში 15 არასაპატიო სამუშაო დღის გაცდენას.</span></div><form method="POST" action="{{route('attendance.evaluate')}}">@csrf<input type="hidden" name="kindergarten_id" value="{{$gardenId}}"><button class="btn btn-warning" type="submit"><i class="fas fa-bolt"></i> შემოწმება ახლა</button></form></div>@endif
</section>
@endsection
@push('scripts')
<script src="{{asset('js/attendance-workbench.js')}}?v={{filemtime(public_path('js/attendance-workbench.js'))}}" defer></script>
@endpush
