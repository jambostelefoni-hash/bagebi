@php
  $dailyOpen = request()->is('kindergarteners*') || request()->is('attendance*');
  $structureOpen = request()->is('kindergartens*') || request()->is('group-age-ranges*') || request()->is('users*') || request()->is('municipalities*') || request()->is('regions*') || request()->is('prioriteties*');
  $systemOpen = request()->is('settings*') || request()->is('work-calendar*') || request()->is('reinstatement-requests*') || request()->is('registration-texts*') || request()->is('public-pages*') || request()->is('audit-logs*') || request()->is('operations*');
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-3">
  <a href="{{route('home')}}" class="brand-link"><img src="{{asset('images/bagebi-brand-mark.png')}}" alt="" class="brand-image"><span class="brand-text">ბაღების პლატფორმა</span></a>
  <div class="sidebar">
    <div class="sidebar-user"><span>{{mb_strtoupper(mb_substr(auth()->user()->name,0,1))}}</span><div><strong>{{auth()->user()->name}}</strong><small>{{auth()->user()->isUnionAdmin()?'გაერთიანების ადმინისტრატორი':optional(auth()->user()->kindergarten)->name}}</small></div></div>
    <nav><ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
      <li class="nav-header">მთავარი</li>
      <li class="nav-item"><a href="{{route('home')}}" class="nav-link {{request()->is('home')?'active':''}}"><i class="nav-icon fas fa-chart-pie"></i><p>სამუშაო დაფა</p></a></li>
      <li class="nav-item"><a href="{{route('guide.index')}}" class="nav-link {{request()->is('guide')?'active':''}}"><i class="nav-icon fas fa-book-open"></i><p>დახმარება და გზამკვლევი</p></a></li>

      <li class="nav-item has-treeview {{$dailyOpen?'menu-open':''}}"><a href="#" class="nav-link {{$dailyOpen?'active':''}}"><i class="nav-icon fas fa-briefcase"></i><p>ყოველდღიური მუშაობა<i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview">
        <li class="nav-item"><a href="{{route('kindergarteners.index')}}" class="nav-link {{request()->is('kindergarteners*')?'active':''}}"><i class="fas fa-child nav-icon"></i><p>აღსაზრდელები</p></a></li>
        <li class="nav-item"><a href="{{route('attendance.index')}}" class="nav-link {{request()->is('attendance*')?'active':''}}"><i class="fas fa-user-check nav-icon"></i><p>დასწრება</p></a></li>
      </ul></li>

      @if(auth()->user()->isUnionAdmin())
      <li class="nav-item has-treeview {{$structureOpen?'menu-open':''}}"><a href="#" class="nav-link {{$structureOpen?'active':''}}"><i class="nav-icon fas fa-sitemap"></i><p>სტრუქტურა<i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview">
        <li class="nav-item"><a href="{{route('kindergartens.list')}}" class="nav-link {{request()->is('kindergartens*')?'active':''}}"><i class="fas fa-school nav-icon"></i><p>ბაღები და ტევადობა</p></a></li>
        <li class="nav-item"><a href="{{route('group-age-ranges.list')}}" class="nav-link {{request()->is('group-age-ranges*')?'active':''}}"><i class="fas fa-layer-group nav-icon"></i><p>ასაკობრივი ჯგუფები</p></a></li>
        <li class="nav-item"><a href="{{route('users.list')}}" class="nav-link {{request()->is('users*')||request()->is('auth/register')?'active':''}}"><i class="fas fa-user-tie nav-icon"></i><p>დირექტორები</p></a></li>
        <li class="nav-item"><a href="{{route('municipalities.list')}}" class="nav-link {{request()->is('municipalities*')?'active':''}}"><i class="fas fa-city nav-icon"></i><p>მუნიციპალიტეტები</p></a></li>
        <li class="nav-item"><a href="{{route('regions.list')}}" class="nav-link {{request()->is('regions*')?'active':''}}"><i class="fas fa-map-marked-alt nav-icon"></i><p>რეგიონები</p></a></li>
        <li class="nav-item"><a href="{{route('prioriteties.list')}}" class="nav-link {{request()->is('prioriteties*')?'active':''}}"><i class="fas fa-star nav-icon"></i><p>პრიორიტეტები</p></a></li>
      </ul></li>

      <li class="nav-item has-treeview {{$systemOpen?'menu-open':''}}"><a href="#" class="nav-link {{$systemOpen?'active':''}}"><i class="nav-icon fas fa-cogs"></i><p>სისტემის მართვა<i class="right fas fa-angle-left"></i></p></a><ul class="nav nav-treeview">
        <li class="nav-item"><a href="{{route('settings.index')}}" class="nav-link {{request()->is('settings')?'active':''}}"><i class="fas fa-sliders-h nav-icon"></i><p>პარამეტრები</p></a></li>
        <li class="nav-item"><a href="{{route('settings.date')}}" class="nav-link {{request()->is('settings/date')?'active':''}}"><i class="far fa-calendar-alt nav-icon"></i><p>სასწავლო თარიღები</p></a></li>
        <li class="nav-item"><a href="{{route('calendar.index')}}" class="nav-link {{request()->is('work-calendar*')?'active':''}}"><i class="fas fa-calendar-check nav-icon"></i><p>სამუშაო კალენდარი</p></a></li>
        <li class="nav-item"><a href="{{route('reinstatement.index')}}" class="nav-link {{request()->is('reinstatement-requests*')?'active':''}}"><i class="fas fa-file-medical nav-icon"></i><p>აღდგენის მოთხოვნები</p></a></li>
        <li class="nav-item"><a href="{{route('public-pages.index')}}" class="nav-link {{request()->is('public-pages*')||request()->is('registration-texts*')?'active':''}}"><i class="fas fa-globe nav-icon"></i><p>საჯარო გვერდები</p></a></li>
        <li class="nav-item"><a href="{{route('audit-logs.index')}}" class="nav-link {{request()->is('audit-logs*')?'active':''}}"><i class="fas fa-clipboard-list nav-icon"></i><p>აუდიტის ჟურნალი</p></a></li>
        <li class="nav-item"><a href="{{route('operations.index')}}" class="nav-link {{request()->is('operations*')?'active':''}}"><i class="fas fa-tasks nav-icon"></i><p>ოპერაციების კონტროლი</p></a></li>
        <li class="nav-item"><a class="nav-link porting-nav" href="{{route('settings.index')}}#annual-porting"><i class="fas fa-exchange-alt nav-icon" aria-hidden="true"></i><p>ჯგუფების პორტირება</p></a></li>
      </ul></li>
      @endif
    </ul></nav>
  </div>
</aside>
