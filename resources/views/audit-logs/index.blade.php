@extends('layouts.app')
@section('title','აუდიტის ჟურნალი')
@section('content')
@php
  $actionLabels = [
    'auth.login'=>'სისტემაში შესვლა','auth.logout'=>'სისტემიდან გასვლა',
    'kindergartener.create'=>'აღსაზრდელის დამატება','kindergartener.update'=>'აღსაზრდელის განახლება','kindergartener.delete'=>'აღსაზრდელის წაშლა','kindergartener.bulk_action'=>'მასობრივი მოქმედება',
    'application.status'=>'განაცხადის სტატუსის შეცვლა','attendance.store'=>'დასწრების შენახვა','attendance.evaluate'=>'გაცდენების ხელით შემოწმება',
    'reinstatement.approved'=>'აღდგენის დამტკიცება','reinstatement.rejected'=>'აღდგენის უარყოფა',
    'kindergarten.create'=>'ბაღის დამატება','kindergarten.update'=>'ბაღის განახლება','kindergarten.delete'=>'ბაღის წაშლა',
    'group_age_range.create'=>'ასაკობრივი ჯგუფის დამატება','group_age_range.update'=>'ასაკობრივი ჯგუფის განახლება','group_age_range.delete'=>'ასაკობრივი ჯგუფის წაშლა',
    'user.update'=>'მომხმარებლის განახლება','user.delete'=>'მომხმარებლის წაშლა',
    'calendar.update'=>'სამუშაო კალენდრის შეცვლა','settings.update'=>'პარამეტრების შეცვლა','settings.date'=>'სასწავლო თარიღების შეცვლა',
    'settings.learningStart'=>'სწავლის დაწყება','settings.learningEnd'=>'სწავლის დასრულება','settings.learning'=>'ჯგუფების პორტირება',
    'public_page.update'=>'საჯარო გვერდის შეცვლა','registration_text.update'=>'რეგისტრაციის ტექსტის შეცვლა','registration_text.rules.update'=>'რეგისტრაციის წესების შეცვლა',
    'waiting_list.offer_created'=>'რიგიდან ადგილის შეთავაზება','waiting_list.offer_expired'=>'შეთავაზების ვადის გასვლა','notification.resend'=>'შეტყობინების ხელახალი გაგზავნა','notification.delivery_status'=>'SMS მიწოდების სტატუსი','audit_logs.export'=>'აუდიტის ექსპორტი',
  ];
  $fieldLabels = [
    'kids_first_name'=>'ბავშვის სახელი','kids_last_name'=>'ბავშვის გვარი','kids_personal_number'=>'ბავშვის პირადი ნომერი',
    'mother_personal_number'=>'დედის პირადი ნომერი','father_personal_number'=>'მამის პირადი ნომერი','mother_first_name'=>'დედის სახელი','mother_last_name'=>'დედის გვარი','father_first_name'=>'მამის სახელი','father_last_name'=>'მამის გვარი',
    'birth_date'=>'დაბადების თარიღი','mobile_number'=>'მობილური','email'=>'ელფოსტა','municipality_id'=>'მუნიციპალიტეტი','kindergarten_id'=>'ბაღი','group_id'=>'ჯგუფი','priority_id'=>'პრიორიტეტი','has_permission'=>'პრიორიტეტის დადასტურება','active_status_id'=>'სტატუსი',
    'application_status'=>'განაცხადის სტატუსი','provider_status'=>'მიწოდების სტატუსი','from'=>'ძველი სტატუსი','to'=>'ახალი სტატუსი','reason'=>'მიზეზი','note'=>'შენიშვნა','date'=>'თარიღი','count'=>'ჩანაწერების რაოდენობა',
    'title'=>'სათაური','subtitle'=>'ქვესათაური','description'=>'აღწერა','name'=>'სახელი','password'=>'პაროლი','role'=>'როლი','is_working_day'=>'სამუშაო დღე','waiting_list_entry_id'=>'რიგის ჩანაწერი','attempts'=>'მცდელობა',
  ];
  $modelLabels = ['User'=>'მომხმარებელი','Kindergartener'=>'აღსაზრდელი','Kindergarten'=>'ბაღი','GroupAgeRange'=>'ასაკობრივი ჯგუფი','Attendance'=>'დასწრება','Setting'=>'პარამეტრი','PublicPage'=>'საჯარო გვერდი','RegistrationText'=>'რეგისტრაციის ტექსტი','ReinstatementRequest'=>'აღდგენის მოთხოვნა','WorkCalendarDay'=>'კალენდრის დღე'];
  $dangerActions = ['kindergartener.delete','kindergartener.bulk_action','kindergarten.delete','user.delete','reinstatement.rejected','settings.learning','settings.learningStart','settings.learningEnd','waiting_list.offer_expired'];
  $warningActions = ['application.status','kindergartener.update','group_age_range.update','user.update','settings.update','settings.date','calendar.update','public_page.update','registration_text.update','registration_text.rules.update','attendance.store','waiting_list.offer_created','notification.resend'];
  $renderValue = function($value) {
    if (is_array($value) && array_key_exists('changed',$value)) return 'შეცვლილია — მნიშვნელობა დაფარულია';
    if ($value === null || $value === '') return '—';
    if (is_bool($value)) return $value ? 'დიახ' : 'არა';
    if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return config('statuses.application.'.$value, config('statuses.attendance.'.$value, (string)$value));
  };
@endphp

<div class="content-header modern-page-header"><div class="container-fluid"><div class="page-heading-row"><div><span class="dashboard-eyebrow">გამჭვირვალობა და კონტროლი</span><h1>აუდიტის ჟურნალი</h1><p>სისტემაში შესრულებული მოქმედებების უცვლელი ისტორია — ვინ, როდის, საიდან და რა შეცვალა.</p></div><span class="audit-protection"><i class="fas fa-shield-alt"></i> ჩანაწერები დაცულია ცვლილებისა და წაშლისგან</span></div></div></div>

<section class="content audit-page">
  <div class="audit-stats">
    <article><span><i class="fas fa-database"></i></span><div><small>სულ მოქმედება</small><strong>{{number_format($stats['total'])}}</strong></div></article>
    <article><span><i class="far fa-clock"></i></span><div><small>დღეს</small><strong>{{number_format($stats['today'])}}</strong></div></article>
    <article><span><i class="far fa-calendar-alt"></i></span><div><small>ბოლო 7 დღე</small><strong>{{number_format($stats['week'])}}</strong></div></article>
    <article><span><i class="fas fa-users"></i></span><div><small>მოქმედი მომხმარებელი</small><strong>{{number_format($stats['actors'])}}</strong></div></article>
  </div>

  <div class="card audit-filter-card"><div class="card-body"><form method="GET" action="{{route('audit-logs.index')}}" class="audit-filters">
    <div class="form-group audit-search-field"><label for="search">ძებნა</label><div class="audit-search-control"><i class="fas fa-search" aria-hidden="true"></i><input id="search" name="search" type="search" class="form-control" value="{{request('search')}}" placeholder="მოქმედება, მომხმარებელი, IP ან ჩანაწერის ID"></div></div>
    <div class="form-group"><label for="user_id">მომხმარებელი</label><select id="user_id" name="user_id" class="custom-select"><option value="">ყველა მომხმარებელი</option>@foreach($users as $user)<option value="{{$user->id}}" @selected((string)request('user_id')===(string)$user->id)>{{$user->name}} · {{$user->email}}</option>@endforeach</select></div>
    <div class="form-group"><label for="action">მოქმედება</label><select id="action" name="action" class="custom-select"><option value="">ყველა მოქმედება</option>@foreach($actions as $action)<option value="{{$action}}" @selected(request('action')===$action)>{{$actionLabels[$action]??$action}}</option>@endforeach</select></div>
    <div class="form-group"><label for="severity">მნიშვნელობა</label><select id="severity" name="severity" class="custom-select"><option value="">ყველა დონე</option><option value="critical" @selected(request('severity')==='critical')>კრიტიკული</option><option value="change" @selected(request('severity')==='change')>მონაცემის ცვლილება</option><option value="normal" @selected(request('severity')==='normal')>ჩვეულებრივი</option></select></div>
    <div class="form-group"><label for="actor">წყარო</label><select id="actor" name="actor" class="custom-select"><option value="">ყველა წყარო</option><option value="user" @selected(request('actor')==='user')>მომხმარებელი</option><option value="system" @selected(request('actor')==='system')>ავტომატური სისტემა</option></select></div>
    <div class="form-group"><label for="date_from">თარიღიდან</label><input id="date_from" type="date" name="date_from" class="form-control" value="{{request('date_from')}}"></div>
    <div class="form-group"><label for="date_to">თარიღამდე</label><input id="date_to" type="date" name="date_to" class="form-control" value="{{request('date_to')}}"></div>
    <div class="audit-filter-actions"><button class="btn btn-primary"><i class="fas fa-filter"></i> გაფილტვრა</button><a href="{{route('audit-logs.export', request()->query())}}" class="btn btn-outline-primary"><i class="fas fa-file-csv"></i> CSV ექსპორტი</a><a href="{{route('audit-logs.index')}}" class="btn btn-light"><i class="fas fa-redo"></i> გასუფთავება</a></div>
  </form></div></div>

  <div class="card audit-log-card"><div class="card-header"><div><h3 class="card-title">მოქმედებების ისტორია</h3><span class="kids-list-caption">ნაპოვნია {{number_format($logs->total())}} ჩანაწერი</span></div><div class="audit-legend"><span><i class="audit-dot audit-dot--critical"></i> კრიტიკული</span><span><i class="audit-dot audit-dot--change"></i> ცვლილება</span><span><i class="audit-dot audit-dot--normal"></i> ჩვეულებრივი</span></div></div>
    <div class="card-body audit-feed">
      @forelse($logs as $log)
        @php
          $severity = in_array($log->action,$dangerActions,true)?'critical':(in_array($log->action,$warningActions,true)?'change':'normal');
          $modelShort = $log->model_type ? class_basename($log->model_type) : null;
          $modelLabel = $modelShort ? ($modelLabels[$modelShort]??$modelShort) : 'სისტემა';
        @endphp
        <article class="audit-entry audit-entry--{{$severity}}">
          <div class="audit-entry-marker"><i class="fas {{$severity==='critical'?'fa-exclamation-triangle':($severity==='change'?'fa-pen':'fa-check')}}"></i></div>
          <div class="audit-entry-main">
            <div class="audit-entry-head"><div><span class="audit-action-label">{{$actionLabels[$log->action]??$log->action}}</span><span class="audit-action-code">{{$log->action}}</span></div><time class="date-stack" datetime="{{$log->created_at->toIso8601String()}}"><strong>{{$log->created_at->format('d.m.Y')}}</strong><small>{{$log->created_at->format('H:i')}}</small></time></div>
            <div class="audit-actor-row">
              @php($actorName=$log->actor_name??optional($log->user)->name)
              @php($actorEmail=$log->actor_email??optional($log->user)->email)
              @php($actorRole=$log->actor_role??optional($log->user)->role)
              <span class="audit-avatar">{{$actorName?mb_strtoupper(mb_substr($actorName,0,1)):'S'}}</span>
              <div class="audit-actor"><strong>{{$actorName?:($log->user_id?'წაშლილი მომხმარებელი':'სისტემური მოქმედება')}}</strong><small>{{$actorEmail?:($log->user_id?'ანგარიში აღარ არსებობს':'ავტომატური პროცესი')}} @if($log->user_id) · {{$actorRole==='union_admin'?'გაერთიანების ადმინისტრატორი':($actorRole==='director'?'ბაღის დირექტორი':'როლი უცნობია')}} · ID {{$log->user_id}} @endif</small></div>
              <div class="audit-target"><span>ობიექტი</span><strong>{{$modelLabel}}{{$log->model_id?' #'.$log->model_id:''}}</strong></div>
              <div class="audit-origin"><span>IP მისამართი</span><strong>{{$log->ip?:'—'}}</strong></div>
            </div>
            @if($log->description)<p class="audit-description">{{$log->description}}</p>@endif
            <details class="audit-details" @if($severity==='critical') open @endif><summary><i class="fas fa-code-branch"></i> ცვლილების დეტალები <span>{{is_array($log->changes)?count($log->changes):0}} ველი</span><i class="fas fa-chevron-down"></i></summary>
              <div class="audit-details-body">
                @if(is_array($log->changes) && count($log->changes))
                  <div class="audit-change-list">@foreach($log->changes as $key=>$value)<div class="audit-change-item"><strong>{{$fieldLabels[$key]??$key}}</strong>@if(is_array($value)&&array_key_exists('old',$value)&&array_key_exists('new',$value))<div><span class="audit-old">{{$renderValue($value['old'])}}</span><i class="fas fa-long-arrow-alt-right"></i><span class="audit-new">{{$renderValue($value['new'])}}</span></div>@else<div><span class="audit-new">{{$renderValue($value)}}</span></div>@endif</div>@endforeach</div>
                @else<div class="audit-no-changes">ამ მოქმედებას ველების ცვლილება არ ახლავს.</div>@endif
                <div class="audit-technical"><div><span>ჩანაწერის ID</span><code>#{{$log->id}}</code></div><div><span>ობიექტის კლასი</span><code>{{$log->model_type?:'—'}}</code></div><div><span>მოწყობილობა / ბრაუზერი</span><code>{{$log->user_agent?:'—'}}</code></div></div>
              </div>
            </details>
          </div>
        </article>
      @empty<div class="empty-state"><i class="fas fa-search"></i><h3>ჩანაწერები ვერ მოიძებნა</h3><p>შეცვალეთ ფილტრები ან თარიღის დიაპაზონი.</p></div>@endforelse
    </div>
    @if($logs->hasPages())<div class="card-footer">{{$logs->links()}}</div>@endif
  </div>
</section>
@endsection
