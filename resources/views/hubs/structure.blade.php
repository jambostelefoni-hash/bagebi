@extends('layouts.app')

@section('title', 'სტრუქტურა და წვდომები')

@section('content')
<x-ui.page-header eyebrow="სისტემის მოწყობა" title="სტრუქტურა და წვდომები" description="მართეთ ბაღების ქსელი, ასაკობრივი ჯგუფები, მომხმარებლები და რეგისტრაციის პრიორიტეტები." />
<x-ui.page class="hub-page">
  <section class="hub-section hub-section--structure" data-tour="structure-hub-main">
    <div class="hub-section__heading"><div><h2>ძირითადი სტრუქტურა</h2><p>ეს პარამეტრები განსაზღვრავს ბაღების, ჯგუფებისა და ადგილების ხელმისაწვდომობას.</p></div></div>
    <div class="hub-grid hub-grid--primary">
      <a class="hub-card" href="{{ route('kindergartens.list') }}"><span class="hub-card__icon"><x-admin-icon name="school" /></span><span><strong>ბაღები და ტევადობა</strong><small>ბაღები, ჯგუფების ზღვარი და თავისუფალი ადგილები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <a class="hub-card" href="{{ route('group-age-ranges.list') }}"><span class="hub-card__icon"><x-admin-icon name="layer-group" /></span><span><strong>ასაკობრივი ჯგუფები</strong><small>დაამატეთ ან დაარედაქტირეთ ასაკობრივი დიაპაზონები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>

  <section class="hub-section hub-section--access" data-tour="structure-hub-access">
    <div class="hub-section__heading"><div><h2>წვდომები და კლასიფიკაცია</h2><p>მომხმარებლების უფლებები და საცნობარო მონაცემები იმართება ამ ბლოკიდან.</p></div></div>
    <div class="hub-grid">
      <a class="hub-card" href="{{ route('users.list') }}"><span class="hub-card__icon"><x-admin-icon name="user-tie" /></span><span><strong>დირექტორები და მომხმარებლები</strong><small>შექმენით ანგარიში და მიუთითეთ ბაღთან წვდომა.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
      <article class="hub-card hub-card--split"><span class="hub-card__icon"><x-admin-icon name="city" /></span><span><strong>რეგიონები და მუნიციპალიტეტები</strong><small>მართეთ ტერიტორიული სტრუქტურის ჩანაწერები.</small><span class="hub-card__actions"><a href="{{ route('regions.list') }}">რეგიონები</a><a href="{{ route('municipalities.list') }}">მუნიციპალიტეტები</a></span></span></article>
      <a class="hub-card" href="{{ route('prioriteties.list') }}"><span class="hub-card__icon"><x-admin-icon name="star" /></span><span><strong>პრიორიტეტები</strong><small>რიგის დამუშავებისთვის საჭირო პრიორიტეტული კატეგორიები.</small></span><x-admin-icon name="arrow-right" class="hub-card__arrow" /></a>
    </div>
  </section>
</x-ui.page>
@endsection
