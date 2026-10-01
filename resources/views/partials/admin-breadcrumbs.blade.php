@php
  $breadcrumb = null;
  $path = request()->path();
  $items = [
    ['kindergarteners*', 'სამუშაო დაფა', route('home'), 'აღსაზრდელები'],
    ['attendance*', 'სამუშაო დაფა', route('home'), 'დასწრება'],
    ['kindergartens*', 'სტრუქტურა და წვდომები', route('structure.index'), 'ბაღები და ტევადობა'],
    ['group-age-ranges*', 'სტრუქტურა და წვდომები', route('structure.index'), 'ასაკობრივი ჯგუფები'],
    ['users*', 'სტრუქტურა და წვდომები', route('structure.index'), 'დირექტორები და მომხმარებლები'],
    ['auth/register', 'სტრუქტურა და წვდომები', route('structure.index'), 'ახალი მომხმარებელი'],
    ['municipalities*', 'სტრუქტურა და წვდომები', route('structure.index'), 'მუნიციპალიტეტები'],
    ['regions*', 'სტრუქტურა და წვდომები', route('structure.index'), 'რეგიონები'],
    ['prioriteties*', 'სტრუქტურა და წვდომები', route('structure.index'), 'პრიორიტეტები'],
    ['settings/porting-preview', 'მართვა და კონტროლი', route('control-center.index'), 'ჯგუფების პორტირება'],
    ['settings/date', 'მართვა და კონტროლი', route('control-center.index'), 'სასწავლო თარიღები'],
    ['settings', 'მართვა და კონტროლი', route('control-center.index'), 'რეგისტრაციის პარამეტრები'],
    ['work-calendar*', 'მართვა და კონტროლი', route('control-center.index'), 'სამუშაო კალენდარი'],
    ['reinstatement-requests*', 'მართვა და კონტროლი', route('control-center.index'), 'აღდგენის მოთხოვნები'],
    ['operations*', 'მართვა და კონტროლი', route('control-center.index'), 'ოპერაციების კონტროლი'],
    ['audit-logs*', 'მართვა და კონტროლი', route('control-center.index'), 'აუდიტის ჟურნალი'],
    ['registration-analytics*', 'მართვა და კონტროლი', route('control-center.index'), 'რეგისტრაციის ანალიტიკა'],
    ['system-health*', 'მართვა და კონტროლი', route('control-center.index'), 'სისტემის გამართულობა'],
    ['data-quality*', 'მართვა და კონტროლი', route('control-center.index'), 'მონაცემების ხარისხი'],
    ['public-pages*', 'მართვა და კონტროლი', route('control-center.index'), 'საჯარო გვერდები'],
    ['registration-texts*', 'მართვა და კონტროლი', route('control-center.index'), 'რეგისტრაციის ტექსტები'],
    ['guide', 'მართვა და კონტროლი', route('control-center.index'), 'ინსტრუქცია'],
    ['profile*', 'სამუშაო დაფა', route('home'), 'პროფილი'],
  ];
  foreach ($items as $item) {
    if (request()->is($item[0])) { $breadcrumb = $item; break; }
  }
@endphp
@if($breadcrumb && !in_array($path, ['home', 'structure', 'control-center'], true))
  <nav class="admin-breadcrumbs" aria-label="გვერდის მდებარეობა">
    <a href="{{ $breadcrumb[2] }}">{{ $breadcrumb[1] }}</a>
    <x-admin-icon name="chevron-right" />
    <span aria-current="page">{{ $breadcrumb[3] }}</span>
  </nav>
@endif
