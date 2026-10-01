@extends('layouts.app')

@section('title', 'მართვა და კონტროლი')

@section('content')
<x-ui.page-header eyebrow="ადმინისტრირება" title="მართვა და კონტროლი" description="რეგისტრაციის, სასწავლო წლის, ოპერაციების, უსაფრთხოებისა და საჯარო პორტალის ერთიანი მართვა." />
<x-ui.page class="hub-page">
  <section class="hub-section hub-section--planning" data-tour="control-hub-learning">
    <div class="hub-section__heading"><div><h2>რეგისტრაცია და სასწავლო წელი</h2><p>რეგისტრაციის რეჟიმი, თარიღები, სამუშაო დღეები და წლიური გადასვლა.</p></div></div>
    <div class="hub-grid">
      <a class="hub-card" href="{{ route('settings.index') }}"><span class="hub-card__icon"><x-admin-icon name="sliders-h" /></span><span><strong>რეგისტრაციის პარამეტრები</strong><small>გახსენით ან დახურეთ რეგისტრაცია და მართეთ ძირითადი რეჟიმები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('settings.date') }}"><span class="hub-card__icon"><x-admin-icon name="calendar-alt" /></span><span><strong>სასწავლო თარიღები</strong><small>სასწავლო წლის დაწყებისა და დასრულების თარიღები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('calendar.index') }}"><span class="hub-card__icon"><x-admin-icon name="calendar-check" /></span><span><strong>სამუშაო კალენდარი</strong><small>უქმეები და ბაღების გამონაკლისი სამუშაო დღეები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('settings.porting-preview') }}"><span class="hub-card__icon"><x-admin-icon name="exchange-alt" /></span><span><strong>ჯგუფების პორტირება</strong><small>წინასწარ შეამოწმეთ წლიური გადაყვანის შედეგი.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('analytics.registration') }}"><span class="hub-card__icon"><x-admin-icon name="chart-bar" /></span><span><strong>რეგისტრაციის ანალიტიკა</strong><small>ტევადობა, რიგი და მომდევნო წლის დაგეგმვა.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>

  <section class="hub-section hub-section--operations" data-tour="control-hub-operations">
    <div class="hub-section__heading"><div><h2>ოპერაციები და მშობლის პროცესი</h2><p>რიგი, შეთავაზებები, შეტყობინებები და აღდგენის დოკუმენტები.</p></div></div>
    <div class="hub-grid">
      <a class="hub-card" href="{{ route('operations.index') }}"><span class="hub-card__icon"><x-admin-icon name="tasks" /></span><span><strong>ოპერაციების კონტროლი</strong><small>მომლოდინეთა რიგი, შეთავაზებები და SMS-ის მიწოდების ჟურნალი.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('reinstatement.index') }}"><span class="hub-card__icon"><x-admin-icon name="file-medical" /></span><span><strong>აღდგენის მოთხოვნები</strong><small>განიხილეთ გაცდენის დოკუმენტები და მიიღეთ გადაწყვეტილება.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>

  <section class="hub-section hub-section--monitoring" data-tour="control-hub-monitoring">
    <div class="hub-section__heading"><div><h2>მონიტორინგი და უსაფრთხოება</h2><p>გადაამოწმეთ მომხმარებლების მოქმედებები, მონაცემების სისწორე და ავტომატური პროცესები.</p></div></div>
    <div class="hub-grid">
      <a class="hub-card" href="{{ route('audit-logs.index') }}"><span class="hub-card__icon"><x-admin-icon name="clipboard-list" /></span><span><strong>აუდიტის ჟურნალი</strong><small>ვინ, როდის და რა შეცვალა სისტემაში.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('data-quality.index') }}"><span class="hub-card__icon"><x-admin-icon name="clipboard-check" /></span><span><strong>მონაცემების ხარისხი</strong><small>აღმოაჩინეთ არასრული ან წინააღმდეგობრივი ჩანაწერები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('system-health.index') }}"><span class="hub-card__icon"><x-admin-icon name="heartbeat" /></span><span><strong>სისტემის გამართულობა</strong><small>რიგის, გაცდენებისა და SMS პროცესების მდგომარეობა.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>

  <section class="hub-section hub-section--public" data-tour="control-hub-public">
    <div class="hub-section__heading"><div><h2>საჯარო პორტალი და დახმარება</h2><p>განაახლეთ მშობლისთვის ხილული ინფორმაცია და გამოიყენეთ ადმინისტრატორის ინსტრუქცია.</p></div></div>
    <div class="hub-grid">
      <a class="hub-card" href="{{ route('public-pages.index') }}"><span class="hub-card__icon"><x-admin-icon name="globe" /></span><span><strong>საჯარო გვერდები და ტექსტები</strong><small>მთავარი გვერდის, რეგისტრაციისა და წესების შინაარსი.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('guide.index') }}"><span class="hub-card__icon"><x-admin-icon name="book-open" /></span><span><strong>ადმინისტრატორის ინსტრუქცია</strong><small>პროცესების გამოყენების მოკლე და პრაქტიკული გზამკვლევი.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>
</x-ui.page>
@endsection
