@extends('layouts.app')
@section('title', 'მონაცემების ხარისხი')
@push('page-styles')
<link rel="stylesheet" href="{{ asset('css/data-quality.css') }}?v={{ filemtime(public_path('css/data-quality.css')) }}">
@endpush
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><div class="page-heading-row"><div><span class="dashboard-eyebrow">მონაცემების კონტროლი</span><h1>მონაცემების ხარისხი</h1><p>აღმოაჩინეთ არასრული ან ურთიერთსაწინააღმდეგო ჩანაწერები. სისტემა მონაცემებს ავტომატურად არ ცვლის.</p></div><form method="POST" action="{{ route('data-quality.scan') }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> შემოწმების დაწყება</button></form></div></div></div>
<section class="content">
  <div class="quality-summary">
    <article class="quality-stat quality-stat--critical"><span>კრიტიკული</span><strong>{{ $summary['critical'] }}</strong></article>
    <article class="quality-stat quality-stat--warning"><span>საყურადღებო</span><strong>{{ $summary['warning'] }}</strong></article>
    <article class="quality-stat"><span>ღია საკითხი</span><strong>{{ $summary['open'] }}</strong></article>
    <article class="quality-stat quality-stat--resolved"><span>გამოსწორებული</span><strong>{{ $summary['resolved'] }}</strong></article>
  </div>
  <div class="quality-scan-note"><i class="fas fa-clock"></i><span>@if($lastScan)<strong>ბოლო შემოწმება:</strong> {{ optional($lastScan->finished_at ?: $lastScan->started_at)->format('d.m.Y H:i') }} <span class="semantic-status semantic-status--{{ $lastScan->status === 'completed' ? 'success' : ($lastScan->status === 'failed' ? 'danger' : 'info') }}">{{ $lastScan->status === 'completed' ? $lastScan->issues_found.' საკითხი' : ($lastScan->status === 'failed' ? 'შეცდომით დასრულდა' : 'მიმდინარეობს') }}</span>@else შემოწმება ჯერ არ ჩატარებულა.@endif</span></div>
  <div class="card quality-filter-card"><div class="card-body"><form method="GET" action="{{ route('data-quality.index') }}" class="quality-filters">
    <select name="status"><option value="">ყველა მდგომარეობა</option><option value="open" @selected(request('status')==='open')>მიმდინარე</option><option value="resolved" @selected(request('status')==='resolved')>გამოსწორებული</option></select>
    <select name="severity"><option value="">ყველა სიმძიმე</option><option value="critical" @selected(request('severity')==='critical')>კრიტიკული</option><option value="warning" @selected(request('severity')==='warning')>საყურადღებო</option><option value="info" @selected(request('severity')==='info')>ინფორმაციული</option></select>
    <select name="type"><option value="">ყველა ტიპი</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ $typeLabels[$type] ?? $type }}</option>@endforeach</select>
    <select name="kindergarten_id"><option value="">ყველა ბაღი</option>@foreach($gardens as $id=>$name)<option value="{{ $id }}" @selected((string)request('kindergarten_id')===(string)$id)>{{ $name }}</option>@endforeach</select>
    <button class="btn btn-primary" type="submit">გაფილტვრა</button><a class="btn btn-light" href="{{ route('data-quality.index') }}">გასუფთავება</a>
  </form></div></div>
  <div class="card"><div class="card-body table-responsive p-0"><table class="table quality-table"><thead><tr><th>სიმძიმე</th><th>პრობლემა</th><th>ბაღი</th><th>მდგომარეობა</th><th>ბოლო აღმოჩენა</th><th></th></tr></thead><tbody>
  @forelse($issues as $issue)<tr><td><span class="quality-badge quality-badge--{{ $issue->severity }}">{{ ['critical'=>'კრიტიკული','warning'=>'საყურადღებო','info'=>'ინფორმაციული'][$issue->severity] ?? $issue->severity }}</span></td><td><strong>{{ $typeLabels[$issue->type] ?? $issue->type }}</strong><small>{{ $issue->message }}</small></td><td>{{ optional($issue->kindergarten)->name ?: '—' }}</td><td><span class="quality-status quality-status--{{ $issue->status }}">{{ $issue->status === 'open' ? 'მიმდინარე' : 'გამოსწორებული' }}</span></td><td>{{ optional($issue->last_detected_at)->format('d.m.Y') }}<small>{{ optional($issue->last_detected_at)->format('H:i') }}</small></td><td>@if($issue->status==='open')<a class="btn btn-sm btn-outline-primary" href="{{ route('data-quality.edit', $issue) }}">გასწორება</a>@endif</td></tr>
  @empty<tr><td colspan="6" class="quality-empty"><i class="fas fa-check-circle"></i><strong>არჩეული ფილტრებით პრობლემა არ მოიძებნა.</strong></td></tr>@endforelse
  </tbody></table></div><div class="card-footer">{{ $issues->links() }}</div></div>
</section>
@endsection
