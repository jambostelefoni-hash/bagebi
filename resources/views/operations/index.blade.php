@extends('layouts.app')
@section('title','ოპერაციების კონტროლი')
@section('content')
@php
    $eventLabels=['application_created'=>'განაცხადის რეგისტრაცია','placement_offer'=>'ადგილის შეთავაზება','placement_offer_declined'=>'შეთავაზებაზე უარი','placement_offer_expired'=>'შეთავაზების ვადა ამოიწურა','registration_suspended'=>'რეგისტრაციის შეჩერება','reinstatement_approved'=>'აღდგენის დამტკიცება','reinstatement_rejected'=>'აღდგენის უარყოფა','reinstatement_needs_correction'=>'დოკუმენტის დაზუსტება','status_registered'=>'სტატუსი: დარეგისტრირებული','status_waiting'=>'სტატუსი: მომლოდინე','status_enrolled'=>'სტატუსი: ჩარიცხული','status_suspended'=>'სტატუსი: შეჩერებული','status_cancelled'=>'სტატუსი: გაუქმებული'];
    $deliveryLabels=['queued'=>'გაგზავნის რიგში','sending'=>'იგზავნება','sent'=>'გაგზავნილია','failed'=>'ვერ გაიგზავნა'];
    $providerLabels=['Delivered'=>'მიწოდებულია','Undelivered'=>'ვერ მიეწოდა','Expired'=>'ვადა ამოიწურა','Pending'=>'მუშავდება','Unknown'=>'უცნობია'];
    $offerLabels=['accepted'=>'მიღებული','declined'=>'უარყოფილი','expired'=>'ვადაგასული'];
    $offerVariants=['accepted'=>'success','declined'=>'danger','expired'=>'neutral'];
@endphp
<div class="content-header modern-page-header"><div class="container-fluid"><div class="page-heading-row"><div><span class="dashboard-eyebrow">რიგი და შეტყობინებები</span><h1>ოპერაციების კონტროლი</h1><p>აქ იმართება რიგი, შეთავაზებები და SMS შეტყობინებების ისტორია.</p></div><form method="POST" action="{{route('operations.process-waiting-list')}}">@csrf<button class="btn btn-primary"><i class="fas fa-sync"></i> რიგის დამუშავება ახლა</button></form></div></div></div>
<section class="content">
<div class="operations-help"><details><summary>როგორ მუშაობს ეს გვერდი?</summary><p>რიგი ირჩევს კანდიდატს პრიორიტეტით და რიგში ჩადგომის დროით. შეთავაზება დროებით ჯავშნის ადგილს. „გაგზავნილი“ ნიშნავს SMS სერვისისთვის გადაცემას და არა წაკითხვას.</p></details></div>
<div class="summary-strip operations-summary"><div><span class="summary-icon summary-icon--navy"><i class="fas fa-school"></i></span><span><small>სრული ტევადობა</small><strong>{{(int)($capacity->total??0)}}</strong></span></div><div><span class="summary-icon"><i class="fas fa-user-check"></i></span><span><small>დაკავებული</small><strong>{{(int)($capacity->filled??0)}}</strong></span></div><div><span class="summary-icon summary-icon--amber"><i class="fas fa-clock"></i></span><span><small>დაჯავშნილი</small><strong>{{(int)($capacity->reserved??0)}}</strong></span></div><div><span class="summary-icon summary-icon--coral"><i class="fas fa-stream"></i></span><span><small>აქტიური რიგი</small><strong>{{$waiting->total()}}</strong></span></div></div>
<div class="card"><div class="card-header"><h3 class="card-title">მომლოდინეთა რიგი</h3></div><div class="card-body pb-0"><form method="GET" class="operations-filter"><select name="waiting_kindergarten_id"><option value="">ყველა ბაღი</option>@foreach($gardens as $id=>$name)<option value="{{$id}}" @selected(request('waiting_kindergarten_id')==$id)>{{$name}}</option>@endforeach</select><select name="waiting_group_id"><option value="">ყველა ჯგუფი</option>@foreach($groups as $id=>$range)<option value="{{$id}}" @selected(request('waiting_group_id')==$id)>{{$range}} წ.</option>@endforeach</select><select name="waiting_state"><option value="">ყველა მდგომარეობა</option><option value="waiting" @selected(request('waiting_state')==='waiting')>მომლოდინე</option><option value="offered" @selected(request('waiting_state')==='offered')>შეთავაზებულია</option></select><button class="btn btn-sm btn-outline-primary">ფილტრი</button><a href="{{route('operations.index')}}" class="btn btn-sm btn-light">გასუფთავება</a></form></div><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>ბავშვი</th><th>ბაღი</th><th>ჯგუფი</th><th>პრიორიტეტი</th><th>რიგშია</th><th>მდგომარეობა</th></tr></thead><tbody>@forelse($waiting as $item)<tr><td>{{optional($item->kindergartener)->kids_first_name}} {{optional($item->kindergartener)->kids_last_name}}</td><td>{{optional($item->kindergarten)->name}}</td><td>{{optional($item->groupRange)->range}}</td><td>{{$item->priority_rank===999?'ჩვეულებრივი':$item->priority_rank}}</td><td>@if($item->queued_at)<time class="date-stack"><strong>{{$item->queued_at->format('d.m.Y')}}</strong><small>{{$item->queued_at->format('H:i')}}</small></time>@else — @endif</td><td><span class="semantic-status semantic-status--{{$item->state==='offered'?'info':'warning'}}">{{$item->state==='offered'?'შეთავაზებულია':'მომლოდინე'}}</span></td></tr>@empty<tr><td colspan="6">ჩანაწერები არ არის.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{$waiting->appends(request()->except('waiting_page'))->links()}}</div></div>
<div class="card">
  <div class="card-header"><h3 class="card-title">ადგილის შეთავაზებები</h3></div>
  <div class="card-body pb-0"><form method="GET" class="operations-filter"><select name="offer_kindergarten_id"><option value="">ყველა ბაღი</option>@foreach($gardens as $id=>$name)<option value="{{$id}}" @selected(request('offer_kindergarten_id')==$id)>{{$name}}</option>@endforeach</select><select name="offer_group_id"><option value="">ყველა ჯგუფი</option>@foreach($groups as $id=>$range)<option value="{{$id}}" @selected(request('offer_group_id')==$id)>{{$range}} წ.</option>@endforeach</select><select name="offer_response"><option value="">ყველა პასუხი</option><option value="pending" @selected(request('offer_response')==='pending')>პასუხის მოლოდინში</option><option value="accepted" @selected(request('offer_response')==='accepted')>მიღებული</option><option value="declined" @selected(request('offer_response')==='declined')>უარყოფილი</option><option value="expired" @selected(request('offer_response')==='expired')>ვადაგასული</option></select><button class="btn btn-sm btn-outline-primary">ფილტრი</button><a href="{{route('operations.index')}}" class="btn btn-sm btn-light">გასუფთავება</a></form></div>
  <div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>ბავშვი</th><th>შექმნილია</th><th>ვადა</th><th>პასუხი</th></tr></thead><tbody>
    @if($offers->isEmpty())
      <tr><td colspan="4">ჩანაწერები არ არის.</td></tr>
    @else
      @foreach($offers as $offer)
        @php
          $offerStatus = $offer->response ?: 'pending';
        @endphp
        <tr><td>{{optional(optional($offer->entry)->kindergartener)->kids_first_name}} {{optional(optional($offer->entry)->kindergartener)->kids_last_name}}</td><td><time class="date-stack"><strong>{{$offer->created_at->format('d.m.Y')}}</strong><small>{{$offer->created_at->format('H:i')}}</small></time></td><td><time class="date-stack"><strong>{{$offer->expires_at->format('d.m.Y')}}</strong><small>{{$offer->expires_at->format('H:i')}}</small></time></td><td><span class="semantic-status semantic-status--{{$offerVariants[$offerStatus]??'warning'}}">{{$offerStatus==='pending'?'პასუხის მოლოდინში':($offerLabels[$offerStatus]??$offerStatus)}}</span></td></tr>
      @endforeach
    @endif
  </tbody></table></div>
  <div class="card-footer">{{$offers->appends(request()->except('offers_page'))->links()}}</div>
</div>
<div class="card sms-log" id="sms-log">
  <div class="card-header sms-log__header">
    <div><h3 class="card-title">SMS-ის მიწოდების ჟურნალი</h3><p>ნახეთ, ვის რა მიზნით გაეგზავნა SMS, მივიდა თუ არა და სად არის საჭირო მოქმედება.</p></div>
  </div>
  <div class="card-body sms-summary" aria-label="SMS შეტყობინებების შეჯამება">
    <div><small>რიგშია</small><strong>{{$notificationSummary['queued']}}</strong></div>
    <div><small>გაგზავნილია</small><strong>{{$notificationSummary['sent']}}</strong></div>
    <div class="sms-summary__success"><small>მიწოდებულია</small><strong>{{$notificationSummary['delivered']}}</strong></div>
    <div class="sms-summary__danger"><small>ვერ გაიგზავნა</small><strong>{{$notificationSummary['failed']}}</strong></div>
  </div>
  <div class="card-body sms-log__tools">
    <nav class="sms-log__tabs" aria-label="SMS ჟურნალის ხედები">
      <a class="{{request('notification_view')==='attention'?'is-active':''}}" href="{{route('operations.index',['notification_view'=>'attention'])}}#sms-log"><i class="fas fa-exclamation-circle"></i> ყურადღებას საჭიროებს @if($notificationSummary['attention'])<span>{{$notificationSummary['attention']}}</span>@endif</a>
      <a class="{{request('notification_view')!=='attention'?'is-active':''}}" href="{{route('operations.index')}}#sms-log"><i class="fas fa-list"></i> ყველა შეტყობინება</a>
    </nav>
    <form method="GET" class="operations-filter sms-log__filter">
      @if(request('notification_view'))<input type="hidden" name="notification_view" value="{{request('notification_view')}}">@endif
      <input type="search" name="notification_search" value="{{request('notification_search')}}" placeholder="ბავშვის სახელი ან ტელეფონი" aria-label="ბავშვის სახელით ან ტელეფონით ძიება">
      <select name="notification_event"><option value="">ყველა მიზეზი</option>@foreach($eventLabels as $event=>$label)<option value="{{$event}}" @selected(request('notification_event')===$event)>{{$label}}</option>@endforeach</select>
      <select name="notification_status"><option value="">ყველა მდგომარეობა</option><option value="queued" @selected(request('notification_status')==='queued')>რიგშია</option><option value="sending" @selected(request('notification_status')==='sending')>იგზავნება</option><option value="sent" @selected(request('notification_status')==='sent')>გაგზავნილია</option><option value="failed" @selected(request('notification_status')==='failed')>ვერ გაიგზავნა</option></select>
      <button class="btn btn-primary"><i class="fas fa-search"></i> ძიება</button>
      <a href="{{route('operations.index')}}#sms-log" class="btn btn-light">გასუფთავება</a>
    </form>
  </div>
  <div class="card-body table-responsive sms-log__table">
    <table class="table table-hover">
      <thead><tr><th>მიმღები</th><th>შეტყობინების მიზანი</th><th>მდგომარეობა</th><th>დრო</th><th class="text-right">მოქმედება</th></tr></thead>
      <tbody>
      @forelse($notifications as $delivery)
        @php
          $child = $delivery->kindergartener;
          $mobile = preg_replace('/\D+/', '', (string) optional($child)->mobile_number);
          $maskedMobile = $mobile ? '••• •• '.substr($mobile, -4) : 'ნომერი არ არის';
          $rawError = (string) ($delivery->provider_reason ?: $delivery->last_error);
          $storedError = (string) $delivery->last_error;
          $invalidMobile = str_contains($rawError, 'Invalid Georgian mobile number') || str_contains($rawError, 'მობილური ნომერი არ არის სწორ') || str_contains($storedError, 'Invalid Georgian mobile number') || str_contains($storedError, 'მობილური ნომერი არ არის სწორ');
          $senderInactive = str_contains(strtolower($rawError), 'sender is not active');
          $insufficientBalance = str_contains(strtolower($rawError), 'balance') || str_contains($rawError, 'ბალანს');
          $temporaryFailure = str_contains(strtolower($rawError), 'timeout') || str_contains(strtolower($rawError), 'connection');
          $errorMessage = $invalidMobile ? 'ტელეფონის ნომერი არასწორია.' : ($senderInactive ? 'SMS გამგზავნის სახელი გააქტიურებული არ არის.' : ($insufficientBalance ? 'SMS Office-ის ბალანსი არასაკმარისია.' : ($temporaryFailure ? 'SMS სერვისი დროებით მიუწვდომელია.' : 'გაგზავნა ვერ შესრულდა. სცადეთ ხელახლა.')));
          $isFailed = $delivery->status === 'failed' || in_array($delivery->provider_status, ['Undelivered', 'Expired'], true);
          $isDelivered = $delivery->provider_status === 'Delivered';
          $isQueued = in_array($delivery->status, ['queued', 'sending'], true);
          $statusLabel = $isFailed ? 'ვერ გაიგზავნა' : ($isDelivered ? 'მიწოდებულია' : ($isQueued ? ($delivery->status === 'sending' ? 'იგზავნება' : 'რიგშია') : 'გაგზავნილია'));
          $statusVariant = $isFailed ? 'danger' : ($isDelivered ? 'success' : ($isQueued ? 'warning' : 'primary'));
          $displayTime = $delivery->delivered_at ?: ($delivery->sent_at ?: $delivery->created_at);
        @endphp
        <tr class="{{$isFailed?'sms-row--attention':''}}">
          <td><strong>{{optional($child)->kids_first_name}} {{optional($child)->kids_last_name}}</strong><small class="sms-recipient-mobile">{{$maskedMobile}}</small></td>
          <td>{{$eventLabels[$delivery->event] ?? 'სისტემური შეტყობინება'}}</td>
          <td><span class="semantic-status semantic-status--{{$statusVariant}}">{{$statusLabel}}</span>@if($isFailed)<small class="sms-error-summary">{{$errorMessage}}</small>@endif</td>
          <td>@if($displayTime)<time class="date-stack"><strong>{{$displayTime->format('d.m.Y')}}</strong><small>{{$displayTime->format('H:i')}}</small></time>@else — @endif</td>
          <td class="sms-log__actions">
            @if($invalidMobile && $child)<a class="btn btn-sm btn-outline-primary" href="{{route('kindergarteners.show',$child->id)}}">ნომრის გასწორება</a>
            @elseif($delivery->status==='failed'&&$delivery->payload)<form method="POST" action="{{route('operations.notifications.resend',$delivery)}}">@csrf<button class="btn btn-sm btn-outline-primary">ხელახლა გაგზავნა</button></form>@endif
            <details class="sms-details"><summary>დეტალები</summary><div><p><b>გაგზავნის მცდელობა:</b> {{$delivery->attempts}}</p><p><b>სერვისის მდგომარეობა:</b> {{$providerLabels[$delivery->provider_status] ?? 'მოლოდინში'}}</p>@if($isFailed)<p><b>პრობლემა:</b> {{$errorMessage}}</p>@endif</div></details>
          </td>
        </tr>
      @empty
        <tr><td colspan="5"><div class="sms-empty"><i class="far fa-comment-dots"></i><strong>შეტყობინებები არ მოიძებნა</strong><span>შეცვალეთ ფილტრი ან გადადით ყველა შეტყობინებაზე.</span></div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{$notifications->appends(request()->except('notifications_page'))->links()}}</div>
</div>
</section>
@endsection
