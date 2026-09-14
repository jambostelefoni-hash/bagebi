@extends('layouts.app')
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">სისტემის კონფიგურაცია</span><h1>სასწავლო პროცესის მართვა</h1><p>მართეთ რეგისტრაცია, პრიორიტეტები და სასწავლო წლის ძირითადი ეტაპები.</p></div></div>
<section class="content">
  <div class="settings-action-grid">
    <article class="process-card"><span class="process-card__icon"><i class="fas fa-play"></i></span><div><span class="dashboard-eyebrow">სასწავლო წელი</span><h2>სწავლის დაწყება</h2><p>დაწყების თარიღი: <strong>{{ data_get($permission, 'object.start') ?: 'არ არის მითითებული' }}</strong></p></div>@if($canStart){!! Form::model($model, ['route' => 'settings.learningStart']) !!}<button class="btn btn-warning" type="submit"><i class="fas fa-play"></i> დაწყება</button>{!! Form::close() !!}@else<span class="status-chip"><i class="far fa-clock"></i> ჯერ მიუწვდომელია</span>@endif</article>
    <article class="process-card"><span class="process-card__icon process-card__icon--coral"><i class="fas fa-flag-checkered"></i></span><div><span class="dashboard-eyebrow">სასწავლო წელი</span><h2>სწავლის დასრულება</h2><p>დასრულების თარიღი: <strong>{{ data_get($permission, 'object.end') ?: 'არ არის მითითებული' }}</strong></p></div>@if($canEnd){!! Form::model($model, ['route' => 'settings.learningEnd']) !!}<button class="btn btn-danger" type="submit"><i class="fas fa-flag-checkered"></i> დასრულება</button>{!! Form::close() !!}@else<span class="status-chip"><i class="far fa-clock"></i> ჯერ მიუწვდომელია</span>@endif</article>
  </div>
  @php($portingAvailable = $canEnd && data_get($model->object, 'canPorting') && data_get($model->object, 'isLearningStart') === false)
  <article id="annual-porting" class="porting-panel" aria-labelledby="porting-title">
    <span class="porting-panel__icon" aria-hidden="true"><i class="fas fa-exchange-alt"></i></span>
    <div class="porting-panel__copy">
      <span class="porting-panel__eyebrow">სასწავლო წლის გადასვლა</span>
      <h2 id="porting-title">ჯგუფების პორტირება</h2>
      <p>ბავშვების გადაყვანა მომდევნო ასაკობრივ ჯგუფში, ადგილების შემოწმებითა და ზღვრების შენარჩუნებით.</p>
      <span class="porting-panel__status"><i class="fas {{ $portingAvailable ? 'fa-check-circle' : 'fa-lock' }}" aria-hidden="true"></i>{{ $portingAvailable ? 'ხელმისაწვდომია — გაშვებამდე გადაამოწმეთ ჯგუფების ზღვრები' : 'ხელმისაწვდომი გახდება სასწავლო წლის დასრულების შემდეგ, თუ პორტირება ჯერ არ შესრულებულა' }}</span>
    </div>
    <div class="porting-panel__actions">
      <form id="portireba" method="POST" action="{{ route('settings.learning') }}">@csrf
        <button type="button" class="btn btn-primary porting-run" @disabled(!$portingAvailable) data-submit="portireba" data-title="დავიწყოთ ჯგუფების პორტირება?" data-message="ბავშვები გადავა მომდევნო ასაკობრივ ჯგუფში. ოპერაცია სრულდება სასწავლო წელიწადში ერთხელ; ადგილების უკმარისობისას ცვლილებები არ შეინახება." onclick="nottify(event)"><i class="fas fa-exchange-alt" aria-hidden="true"></i> პორტირების დაწყება</button>
      </form>
      <a href="{{ route('kindergartens.list') }}">ჯგუფების ზღვრების ნახვა <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </article>
  {!! Form::model($model, ['route' => 'settings.store']) !!}<input type="hidden" name="slug" value="basic">
  <div class="card settings-card"><div class="card-header"><div><h3 class="card-title">რეგისტრაციის პარამეტრები</h3><span class="kids-list-caption">ცვლილებები საჯარო რეგისტრაციის ფორმაზე დაუყოვნებლივ აისახება</span></div></div><div class="card-body">
    <div class="setting-toggle-grid">
      <label class="setting-toggle" for="registration-toggle"><span class="setting-toggle__icon"><i class="fas fa-user-plus"></i></span><span><strong>რეგისტრაციის მიღება</strong><small>მშობლებისთვის ახალი განაცხადის ფორმის ხელმისაწვდომობა</small></span><input id="registration-toggle" type="checkbox" value="true" name="object[isRegistrationStart]" @checked(data_get($model->object, 'isRegistrationStart'))><span class="toggle-track" aria-hidden="true"></span></label>
      <label class="setting-toggle" for="priority-toggle"><span class="setting-toggle__icon"><i class="fas fa-star"></i></span><span><strong>პრიორიტეტების მიღება</strong><small>განაცხადში პრიორიტეტის არჩევისა და დამოწმების ჩართვა</small></span><input id="priority-toggle" type="checkbox" value="true" name="object[isPrioritetiesStart]" @checked(data_get($model->object, 'isPrioritetiesStart'))><span class="toggle-track" aria-hidden="true"></span></label>
    </div>
    <div class="form-group mt-4"><label for="inputDescription">შეტყობინება რეგისტრაციის გვერდზე</label><textarea name="object[nottification]" id="inputDescription" class="form-control" rows="4" placeholder="მაგალითად: რეგისტრაცია დასრულდება 20 სექტემბერს.">{{ old('object.nottification', data_get($model->object, 'nottification', '')) }}</textarea><small class="form-text text-muted">ტექსტი გამოჩნდება მშობლისთვის რეგისტრაციის პროცესში.</small></div>
  </div><div class="card-footer modern-card-footer"><a href="{{ route('home') }}" class="btn btn-light">გაუქმება</a><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ცვლილებების შენახვა</button></div></div>{!! Form::close() !!}
</section>
@endsection
@push('styles')
<style>
.settings-action-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px}.process-card{display:grid;grid-template-columns:52px 1fr auto;align-items:center;gap:16px;padding:22px;background:#fff;border:1px solid var(--panel-line);border-radius:var(--panel-radius);box-shadow:0 7px 19px rgba(18,52,84,.06)}.process-card__icon{display:grid;place-items:center;width:52px;height:52px;border-radius:15px;background:#e7f8f5;color:var(--panel-teal)}.process-card__icon--coral{background:#fff0ed;color:var(--panel-coral)}.process-card h2{margin:4px 0;font-size:1rem;font-weight:800}.process-card p{margin:0;color:var(--panel-muted);font-size:.78rem}.process-card form{margin:0}.setting-toggle-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.setting-toggle{position:relative;display:grid;grid-template-columns:46px 1fr 48px;align-items:center;gap:13px;margin:0;padding:18px;border:1px solid var(--panel-line);border-radius:15px;cursor:pointer;transition:.18s}.setting-toggle:hover{border-color:#adddd7;background:#fbfefd}.setting-toggle__icon{display:grid;place-items:center;width:46px;height:46px;border-radius:13px;background:#eef8f7;color:var(--panel-teal)}.setting-toggle strong,.setting-toggle small{display:block}.setting-toggle small{margin-top:4px;color:var(--panel-muted);font-size:.72rem;font-weight:500}.setting-toggle input{position:absolute;opacity:0}.toggle-track{position:relative;width:44px;height:24px;border-radius:99px;background:#cbd5e1;transition:.2s}.toggle-track:after{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;border-radius:50%;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,.18);transition:.2s}.setting-toggle input:checked+.toggle-track{background:var(--panel-teal)}.setting-toggle input:checked+.toggle-track:after{transform:translateX(20px)}.setting-toggle input:focus-visible+.toggle-track{outline:3px solid rgba(13,155,138,.22)}.modern-card-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 22px!important;background:#fbfcfe!important;border-top:1px solid var(--panel-line)!important}@media(max-width:900px){.settings-action-grid,.setting-toggle-grid{grid-template-columns:1fr}}@media(max-width:576px){.process-card{grid-template-columns:46px 1fr}.process-card form,.process-card>.status-chip{grid-column:1/-1}.process-card .btn{width:100%}}
</style>
@endpush
