<style>
  .main-sidebar {
    background: #162840 !important;
    border-right: none !important;
    box-shadow: 2px 0 12px rgba(0,0,0,0.18) !important;
  }

  /* Brand */
  .brand-link {
    background: #0f1e2e !important;
    border-bottom: 1px solid rgba(255,255,255,0.07) !important;
    padding: 18px 20px !important;
    text-decoration: none !important;
    min-height: 57px !important;
  }

  .brand-link:hover {
    background: #0f1e2e !important;
  }

  .brand-text {
    color: #ffffff !important;
    font-family: 'Noto Sans Georgian', sans-serif !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    letter-spacing: 0.01em !important;
  }

  /* Sidebar wrapper */
  .sidebar {
    background: transparent !important;
    padding: 8px 0 !important;
  }

  /* Section labels */
  .nav-sidebar .nav-item > .nav-link p {
    font-family: 'Noto Sans Georgian', sans-serif !important;
    font-size: 0.82rem !important;
    font-weight: 500 !important;
    color: rgba(255,255,255,0.72) !important;
    letter-spacing: 0.01em !important;
  }

  /* Icons */
  .nav-sidebar .nav-link .nav-icon {
    color: rgba(255,255,255,0.4) !important;
    font-size: 0.88rem !important;
    width: 1.4rem !important;
    transition: color 0.15s !important;
  }

  /* All nav links */
  .nav-sidebar .nav-link {
    padding: 9px 16px 9px 18px !important;
    border-radius: 0 !important;
    margin: 0 !important;
    border-left: 3px solid transparent !important;
    transition: background 0.15s, border-color 0.15s !important;
    background: transparent !important;
  }

  .nav-sidebar .nav-link:hover {
    background: rgba(255,255,255,0.06) !important;
    border-left-color: rgba(255,255,255,0.25) !important;
  }

  .nav-sidebar .nav-link:hover .nav-icon {
    color: rgba(255,255,255,0.75) !important;
  }

  .nav-sidebar .nav-link:hover p {
    color: #ffffff !important;
  }

  /* Active state */
  .nav-sidebar .nav-link.active {
    background: rgba(255,255,255,0.10) !important;
    border-left: 3px solid #4a9eff !important;
  }

  .nav-sidebar .nav-link.active .nav-icon {
    color: #4a9eff !important;
  }

  .nav-sidebar .nav-link.active p {
    color: #ffffff !important;
    font-weight: 600 !important;
  }

  /* Parent menu items (ძირითადი, მართვა) */
  .nav-sidebar > .nav-item > .nav-link {
    padding: 10px 16px 10px 18px !important;
    margin-top: 4px !important;
  }

  .nav-sidebar > .nav-item > .nav-link p {
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.08em !important;
    color: rgba(255,255,255,0.45) !important;
  }

  .nav-sidebar > .nav-item > .nav-link .nav-icon {
    color: rgba(255,255,255,0.3) !important;
  }

  .nav-sidebar > .nav-item > .nav-link .right {
    color: rgba(255,255,255,0.3) !important;
    font-size: 0.75rem !important;
  }

  /* Treeview (sub items) */
  .nav-treeview {
    background: rgba(0,0,0,0.15) !important;
    padding: 4px 0 !important;
  }

  .nav-treeview .nav-link {
    padding: 8px 16px 8px 36px !important;
    border-left: 3px solid transparent !important;
  }

  .nav-treeview .nav-link p {
    font-size: 0.82rem !important;
    color: rgba(255,255,255,0.62) !important;
  }

  .nav-treeview .nav-link .nav-icon {
    color: rgba(255,255,255,0.28) !important;
    font-size: 0.8rem !important;
  }

  .nav-treeview .nav-link:hover {
    background: rgba(255,255,255,0.06) !important;
    border-left-color: rgba(74,158,255,0.5) !important;
  }

  .nav-treeview .nav-link:hover p {
    color: #ffffff !important;
  }

  .nav-treeview .nav-link:hover .nav-icon {
    color: rgba(255,255,255,0.7) !important;
  }

  .nav-treeview .nav-link.active {
    background: rgba(74,158,255,0.12) !important;
    border-left: 3px solid #4a9eff !important;
  }

  .nav-treeview .nav-link.active p {
    color: #ffffff !important;
    font-weight: 600 !important;
  }

  .nav-treeview .nav-link.active .nav-icon {
    color: #4a9eff !important;
  }

  /* Danger / portireba */
  .nav-link.nav-danger p,
  .nav-link.nav-danger .nav-icon {
    color: #ff6b6b !important;
  }

  .nav-link.nav-danger:hover {
    background: rgba(255,107,107,0.10) !important;
    border-left-color: #ff6b6b !important;
  }

  /* Disabled */
  .nav-link.disabled {
    opacity: 0.35 !important;
    cursor: not-allowed !important;
  }

  /* Scrollbar */
  .sidebar::-webkit-scrollbar {
    width: 4px;
  }

  .sidebar::-webkit-scrollbar-track {
    background: transparent;
  }

  .sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.12);
    border-radius: 2px;
  }
</style>

<aside class="main-sidebar sidebar-light-primary elevation-3">
    <a href="{{ route('home') }}" class="brand-link d-flex align-items-center justify-content-center gap-2">
        <span class="brand-text fw-bold"></span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->is('home*') ? 'active' : '' }}">
                        <i class="fas fa-home nav-icon"></i>
                        <p>ინფორმაცია</p>
                    </a>
                </li>

                <!-- Basic -->
                <li class="nav-item menu-open">
                    <a href="#" class="nav-link">
                        <i class="fas fa-layer-group nav-icon"></i>
                        <p>
                            ძირითადი
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('regions.list') }}" class="nav-link {{ request()->is('regions*') ? 'active' : '' }}">
                                <i class="fas fa-map-marked-alt nav-icon"></i>
                                <p>რეგიონი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('municipalities.list') }}" class="nav-link {{ request()->is('municipalities*') ? 'active' : '' }}">
                                <i class="fas fa-city nav-icon"></i>
                                <p>მუნიციპალიტეტი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('prioriteties.list') }}" class="nav-link {{ request()->is('prioriteties*') ? 'active' : '' }}">
                                <i class="fas fa-star nav-icon"></i>
                                <p>პრიორიტეტი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('kindergartens.list') }}" class="nav-link {{ request()->is('kindergartens*') ? 'active' : '' }}">
                                <i class="fas fa-school nav-icon"></i>
                                <p>ბაღი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('kindergarteners.index') }}" class="nav-link {{ request()->is('kindergarteners*') ? 'active' : '' }}">
                                <i class="fas fa-child nav-icon"></i>
                                <p>აღსაზრდელი</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Manage -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cogs nav-icon"></i>
                        <p>
                            მართვა
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->is('settings') ? 'active' : '' }}">
                                <i class="fas fa-sliders-h nav-icon"></i>
                                <p>პარამეტრები</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('settings.date') }}" class="nav-link {{ request()->is('settings/date') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt nav-icon"></i>
                                <p>თარიღი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('registration-texts.index') }}" class="nav-link {{ request()->is('registration-texts') ? 'active' : '' }}">
                                <i class="fas fa-align-left nav-icon"></i>
                                <p>ტექსტი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('public-pages.index') }}" class="nav-link {{ request()->is('public-pages*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt nav-icon"></i>
                                <p>საჯარო გვერდები</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('registration-texts.rules') }}" class="nav-link {{ request()->is('registration-texts/rules') ? 'active' : '' }}">
                                <i class="fas fa-book nav-icon"></i>
                                <p>წესები</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->is('audit-logs') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list nav-icon"></i>
                                <p>აუდიტის ჟურნალი</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if (data_get($settings, 'basic.object.canPorting'))
                                <form id="portireba" method="POST" action="{{ route('settings.learning') }}">
                                    @csrf
                                    <a class="nav-link nav-danger"
                                       style="cursor:pointer;"
                                       data-submit="portireba"
                                       data-message="პორტირება აუცილებლად უნდა შესრულდეს მხოლოდ სასწავლო წლის დასრულების შემდეგ ერთჯერადად!"
                                       data-title="ნამდვილად გსურთ პორტირების შესრულება?"
                                       onclick="nottify(event)">
                                        <i class="fas fa-exchange-alt nav-icon"></i>
                                        <p>პორტირება</p>
                                    </a>
                                </form>
                            @else
                                <a class="nav-link disabled text-muted" style="cursor:not-allowed;"
                                   data-message="დროის ამ მომენტში პორტირება ნებადართული არ არის!"
                                   data-no-buttons="true"
                                   onclick="nottify(event)">
                                    <i class="fas fa-exchange-alt nav-icon"></i>
                                    <p>პორტირება</p>
                                </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('users.list') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                                <i class="fas fa-users nav-icon"></i>
                                <p>მომხმარებლები</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>