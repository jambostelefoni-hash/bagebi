@extends('layouts.app')
@section('content')
<x-ui.page-header eyebrow="სისტემის კონფიგურაცია" title="სასწავლო პროცესის მართვა" description="მართეთ რეგისტრაცია, პრიორიტეტები და სასწავლო წლის ძირითადი ეტაპები." />
<x-ui.page>
  <div class="settings-action-grid">
    <article class="process-card"><span class="process-card__icon"><i class="fas fa-play"></i></span><div><span class="dashboard-eyebrow">სასწავლო წელი</span><h2>სწავლის დაწყება</h2><p>დაწყების თარიღი: <strong>{{ data_get($permission, 'object.start') ?: 'არ არის მითითებული' }}</strong></p></div>@if($canStart)<form method="POST" action="{{ route('settings.learningStart') }}">@csrf<button class="btn btn-primary" type="submit"><i class="fas fa-play"></i> დაწყება</button></form>@elseif($isLearningStarted)<span class="status-chip status-chip--success"><i class="fas fa-check-circle"></i> დაწყებულია</span>@else<span class="status-chip status-chip--neutral"><i class="far fa-clock"></i> ჯერ მიუწვდომელია</span>@endif</article>
    <article class="process-card"><span class="process-card__icon process-card__icon--coral"><i class="fas fa-flag-checkered"></i></span><div><span class="dashboard-eyebrow">სასწავლო წელი</span><h2>სწავლის დასრულება</h2><p>დასრულების თარიღი: <strong>{{ data_get($permission, 'object.end') ?: 'არ არის მითითებული' }}</strong></p></div>@if($canEnd)<form method="POST" action="{{ route('settings.learningEnd') }}">@csrf<button class="btn btn-danger" type="submit"><i class="fas fa-flag-checkered"></i> დასრულება</button></form>@elseif($isLearningEnded)<span class="status-chip status-chip--success"><i class="fas fa-check-circle"></i> დასრულებულია</span>@else<span class="status-chip status-chip--neutral"><i class="far fa-clock"></i> ჯერ მიუწვდომელია</span>@endif</article>
  </div>
  <article id="annual-porting" class="porting-panel" aria-labelledby="porting-title">
    <span class="porting-panel__icon" aria-hidden="true"><i class="fas fa-exchange-alt"></i></span>
    <div class="porting-panel__copy">
      <span class="porting-panel__eyebrow">სასწავლო წლის გადასვლა</span>
      <h2 id="porting-title">ჯგუფების პორტირება</h2>
      <p>ბავშვების გადაყვანა მომდევნო ასაკობრივ ჯგუფში, ადგილების შემოწმებითა და ზღვრების შენარჩუნებით.</p>
      <span class="porting-panel__status semantic-status semantic-status--{{ $portingAvailable ? 'success' : 'neutral' }}"><i class="fas {{ $portingAvailable ? 'fa-check-circle' : 'fa-lock' }}" aria-hidden="true"></i>{{ $portingAvailable ? 'ხელმისაწვდომია — გაშვებამდე გადაამოწმეთ ჯგუფების ზღვრები' : 'ხელმისაწვდომი გახდება სასწავლო წლის დასრულების შემდეგ, თუ პორტირება ჯერ არ შესრულებულა' }}</span>
    </div>
    <div class="porting-panel__actions">
      <a class="btn btn-outline-primary" href="{{ route('settings.porting-preview') }}"><i class="fas fa-search"></i> წინასწარი შემოწმება</a>
      <form id="portireba" method="POST" action="{{ route('settings.learning') }}">@csrf
        <button type="button" class="btn btn-primary porting-run" @disabled(!$portingAvailable) data-submit="portireba" data-title="დავიწყოთ ჯგუფების პორტირება?" data-message="ბავშვები გადავა მომდევნო ასაკობრივ ჯგუფში. ოპერაცია სრულდება სასწავლო წელიწადში ერთხელ; ადგილების უკმარისობისას ცვლილებები არ შეინახება."><i class="fas fa-exchange-alt" aria-hidden="true"></i> პორტირების დაწყება</button>
      </form>
      <a href="{{ route('kindergartens.list') }}">ჯგუფების ზღვრების ნახვა <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </article>
  <form method="POST" action="{{ route('settings.store') }}">@csrf<input type="hidden" name="slug" value="basic">
  <x-ui.card class="settings-card" title="რეგისტრაციის პარამეტრები" description="ცვლილებები საჯარო რეგისტრაციის ფორმაზე დაუყოვნებლივ აისახება">
    <div class="setting-toggle-grid">
      <label class="setting-toggle" for="registration-toggle"><span class="setting-toggle__icon"><i class="fas fa-user-plus"></i></span><span><strong>რეგისტრაციის მიღება</strong><small>მშობლებისთვის ახალი განაცხადის ფორმის ხელმისაწვდომობა</small></span><input id="registration-toggle" type="checkbox" value="true" name="object[isRegistrationStart]" @checked(data_get($model->object, 'isRegistrationStart'))><span class="toggle-track" aria-hidden="true"></span></label>
      <label class="setting-toggle" for="priority-toggle"><span class="setting-toggle__icon"><i class="fas fa-star"></i></span><span><strong>პრიორიტეტების მიღება</strong><small>განაცხადში პრიორიტეტის არჩევისა და დამოწმების ჩართვა</small></span><input id="priority-toggle" type="checkbox" value="true" name="object[isPrioritetiesStart]" @checked(data_get($model->object, 'isPrioritetiesStart'))><span class="toggle-track" aria-hidden="true"></span></label>
    </div>
    <div class="mt-4"><x-ui.textarea label="შეტყობინება რეგისტრაციის გვერდზე" name="object[nottification]" id="inputDescription" rows="4" placeholder="მაგალითად: რეგისტრაცია დასრულდება 20 სექტემბერს." :value="data_get($model->object, 'nottification', '')" help="ტექსტი გამოჩნდება მშობლისთვის რეგისტრაციის პროცესში." /></div>
    <x-slot name="footer"><x-ui.button variant="secondary" :href="route('home')">გაუქმება</x-ui.button><x-ui.button type="submit"><i class="fas fa-save"></i> ცვლილებების შენახვა</x-ui.button></x-slot>
  </x-ui.card></form>
</x-ui.page>
@endsection
