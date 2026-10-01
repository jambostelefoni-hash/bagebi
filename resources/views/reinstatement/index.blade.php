@extends('layouts.app')
@section('title','აღდგენის მოთხოვნები')
@section('content')
@php
  $requestStatusLabels=['pending'=>'მოლოდინში','needs_correction'=>'დასაზუსტებელია','approved'=>'დამტკიცებული','rejected'=>'უარყოფილი','expired'=>'ვადაგასული'];
  $requestStatusVariants=['pending'=>'warning','needs_correction'=>'info','approved'=>'success','rejected'=>'danger','expired'=>'neutral'];
@endphp
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">გაცდენების კონტროლი</span><h1>აღდგენის მოთხოვნები</h1><p>დოკუმენტები და გადაწყვეტილებები ერთ კომპაქტურ სიაში.</p></div></div>
<section class="content"><div class="card">
<div class="card-header"><h3 class="card-title">მოთხოვნები · სულ {{ $requests->total() }}</h3></div><div class="card-body pb-0"><form method="GET" class="operations-filter"><select name="kindergarten_id"><option value="">ყველა ბაღი</option>@foreach($gardens as $id=>$name)<option value="{{$id}}" @selected(request('kindergarten_id')==$id)>{{$name}}</option>@endforeach</select><select name="status"><option value="">ყველა სტატუსი</option><option value="pending" @selected(request('status')==='pending')>მოლოდინში</option><option value="needs_correction" @selected(request('status')==='needs_correction')>დასაზუსტებელია</option><option value="approved" @selected(request('status')==='approved')>დამტკიცებული</option><option value="rejected" @selected(request('status')==='rejected')>უარყოფილი</option><option value="expired" @selected(request('status')==='expired')>ვადაგასული</option></select><select name="document"><option value="">ყველა დოკუმენტი</option><option value="uploaded" @selected(request('document')==='uploaded')>ატვირთული</option><option value="missing" @selected(request('document')==='missing')>არ არის ატვირთული</option></select><button class="btn btn-sm btn-outline-primary">ფილტრი</button><a href="{{route('reinstatement.index')}}" class="btn btn-sm btn-light">გასუფთავება</a></form></div>
<div class="table-responsive" tabindex="0" role="region" aria-label="აღდგენის მოთხოვნების ცხრილი">
<table class="table table-hover reinstatement-table">
<thead><tr><th scope="col">აღსაზრდელი / ბაღი</th><th scope="col">ვადა</th><th scope="col">სტატუსი</th><th scope="col">დოკუმენტი</th><th scope="col">გადაწყვეტილება</th></tr></thead>
<tbody>
@forelse($requests as $item)
<tr>
<td><strong>{{ optional($item->kindergartener)->kids_first_name }} {{ optional($item->kindergartener)->kids_last_name }}</strong><small>{{ optional(optional($item->kindergartener)->kindergarten)->name }}</small></td>
<td class="reinstatement-table__date"><time datetime="{{ $item->expires_at->toIso8601String() }}">{{ $item->expires_at->format('d.m.Y') }}<small>{{ $item->expires_at->format('H:i') }}</small></time></td>
<td><span class="semantic-status semantic-status--{{ $requestStatusVariants[$item->status]??'neutral' }}">{{ $item->status==='pending'&&!$item->document_path&&$item->review_note?'ხელახალი ატვირთვის მოლოდინში':($requestStatusLabels[$item->status]??$item->status) }}</span></td>
<td>@if($item->document_path)<a href="{{ route('reinstatement.download',$item) }}" class="btn btn-sm btn-outline-primary" title="{{ $item->original_filename }}"><i class="fas fa-download" aria-hidden="true"></i> დოკუმენტი</a>@else<span class="text-muted">არ არის ატვირთული</span>@endif</td>
<td class="reinstatement-table__decision">
@if($item->status==='pending'&&$item->document_path)
<details class="reinstatement-action"><summary>განხილვა</summary>
<form method="POST" action="{{ route('reinstatement.review',$item) }}">@csrf
<label for="review-note-{{ $item->id }}">გადაწყვეტილების შენიშვნა</label>
<textarea id="review-note-{{ $item->id }}" name="review_note" class="form-control" rows="2" maxlength="500"></textarea>
<div><button name="decision" value="approved" class="btn btn-sm btn-success">აღდგენა</button><button name="decision" value="needs_correction" class="btn btn-sm btn-outline-primary">დასაზუსტებელია</button><button name="decision" value="rejected" class="btn btn-sm btn-outline-danger">საბოლოო უარი</button></div>
</form></details>
@elseif($item->review_note)
<details class="reinstatement-action"><summary>შენიშვნა</summary><p>{{ $item->review_note }}</p></details>
@elseif($item->status==='pending')
<span class="text-muted">{{ $item->expires_at->isPast()?'ვადა ამოიწურა':'დოკუმენტის მოლოდინში' }}</span>
@else<span class="text-muted">—</span>@endif
</td></tr>
@empty<tr><td colspan="5" class="text-center text-muted">აღდგენის მოთხოვნები არ არის.</td></tr>@endforelse
</tbody></table></div>
@if($requests->hasPages())<div class="card-footer">{{ $requests->links() }}</div>@endif
</div></section>
@endsection
