@php
    $topbarSection = request()->is('kindergarteners*') ? 'აღსაზრდელები'
        : (request()->is('attendance*') ? 'დასწრება'
        : (request()->is('structure', 'kindergartens*', 'group-age-ranges*', 'users*', 'municipalities*', 'regions*', 'prioriteties*') ? 'სტრუქტურა და წვდომები'
        : (request()->is('control-center', 'settings*', 'work-calendar*', 'reinstatement-requests*', 'operations*', 'audit-logs*', 'registration-analytics*', 'system-health*', 'data-quality*', 'public-pages*', 'registration-texts*', 'guide') ? 'მართვა და კონტროლი'
        : (request()->is('profile*') ? 'პროფილი' : 'სამუშაო დაფა'))));
@endphp
<nav class="main-header navbar navbar-expand admin-topbar">
    <button class="admin-menu-button" type="button" aria-label="გვერდითი მენიუს გახსნა" aria-controls="admin-sidebar" aria-expanded="true"><x-admin-icon name="bars" /></button>
    <div class="admin-topbar-context"><span>{{ $topbarSection }}</span><small>{{ auth()->user()->role === 'director' ? 'ბაღის დირექტორი' : 'გაერთიანების ადმინისტრაცია' }}</small></div>
    <div class="navbar-nav ml-auto"><button class="admin-tour-trigger" type="button" data-guided-tour-start aria-label="სისტემის გაცნობის დაწყება"><x-admin-icon name="compass" /><span>სისტემის გაცნობა</span></button><div class="nav-item dropdown">
        <button class="admin-profile-button" data-toggle="dropdown" type="button" aria-haspopup="true" aria-expanded="false"><span class="admin-profile-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><span class="admin-profile-copy"><strong>{{auth()->user()->name}}</strong><small>ანგარიშის მართვა</small></span><x-admin-icon name="chevron-down" /></button>
        <div class="dropdown-menu dropdown-menu-right admin-profile-menu"><a class="dropdown-item" href="{{route('profile.edit')}}"><x-admin-icon name="user-shield" /> ანგარიშის პარამეტრები</a><div class="dropdown-divider"></div><form id="logout-form" method="POST" action="{{route('logout')}}">@csrf<button class="dropdown-item text-danger" type="submit"><x-admin-icon name="sign-out-alt" /> გასვლა</button></form></div>
    </div></div>
</nav>
