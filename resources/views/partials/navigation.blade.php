<nav class="main-header navbar navbar-expand admin-topbar">
    <button class="admin-menu-button" data-widget="pushmenu" type="button" aria-label="გვერდითი მენიუს გახსნა"><i class="fas fa-bars"></i></button>
    <div class="admin-topbar-context"><span>მართვის პანელი</span><small>{{ auth()->user()->role === 'director' ? 'ბაღის დირექტორი' : 'გაერთიანების ადმინისტრაცია' }}</small></div>
    <div class="navbar-nav ml-auto"><div class="nav-item dropdown">
        <button class="admin-profile-button" data-toggle="dropdown" type="button" aria-haspopup="true" aria-expanded="false"><span class="admin-profile-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><span class="admin-profile-copy"><strong>{{auth()->user()->name}}</strong><small>ანგარიშის მართვა</small></span><i class="fas fa-chevron-down"></i></button>
        <div class="dropdown-menu dropdown-menu-right admin-profile-menu"><a class="dropdown-item" href="{{route('profile.edit')}}"><i class="fas fa-user-shield"></i> ანგარიშის პარამეტრები</a><div class="dropdown-divider"></div><form id="logout-form" method="POST" action="{{route('logout')}}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="fas fa-sign-out-alt"></i> გასვლა</button></form></div>
    </div></div>
</nav>
