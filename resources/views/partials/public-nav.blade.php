<style>
  /* ADD THESE TWO LINES AT THE TOP */
  html, body {
    overflow-x: hidden;
    max-width: 100%;
  }

  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;500;600&display=swap');

  /* ... rest of your existing CSS unchanged ... */
  .public-nav {
    background: #ffffff;
    position: sticky;
    top: 0;
    z-index: 999;
    border-bottom: 1px solid #dde3ec;
  }

  .public-nav::after {
    content: '';
    display: block;
    height: 3px;
    background: #162840;
  }

  .public-nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 32px;
    height: 68px;
  }

  /* Brand */
  .nav-brand-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    text-decoration: none;
  }

  .nav-brand-wrap img {
    width: 40px;
    height: auto;
    display: block;
  }

  .nav-brand-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .public-brand-title {
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    color: #162840;
    line-height: 1.2;
  }

  .nav-brand-sub {
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.68rem;
    color: #9aa5b4;
    font-weight: 400;
  }

  /* Desktop links */
  .public-links {
    display: flex;
    align-items: stretch;
    height: 68px;
    margin-left: auto;
  }

  .public-links a {
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    color: #4a5568;
    text-decoration: none;
    padding: 0 16px;
    display: flex;
    align-items: center;
    border-bottom: 3px solid transparent;
    transition: color 0.15s, border-color 0.15s;
    white-space: nowrap;
  }

  .public-links a:hover {
    color: #162840;
    border-bottom-color: #162840;
  }

  /* Hamburger — hidden on desktop */
  .public-menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    margin-left: auto;
  }

  .public-menu-toggle span {
    display: block;
    width: 22px;
    height: 2px;
    background: #162840;
    transition: opacity 0.2s;
  }

  /* Drawer overlay */
  .public-nav-drawer {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
  }

  .public-nav-drawer.open {
    display: block;
  }

  .drawer-overlay {
    position: absolute;
    inset: 0;
    background: rgba(22, 40, 64, 0.4);
  }

  .drawer-content {
    position: absolute;
    top: 0;
    right: 0;
    width: 260px;
    height: 100%;
    background: #ffffff;
    border-left: 3px solid #162840;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.25s ease;
  }

  .public-nav-drawer.open .drawer-content {
    transform: translateX(0);
  }

  .drawer-close {
    align-self: flex-end;
    background: none;
    border: none;
    font-size: 1.4rem;
    color: #162840;
    cursor: pointer;
    padding: 18px 20px 10px;
    line-height: 1;
  }

  .drawer-content a {
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    color: #4a5568;
    text-decoration: none;
    padding: 14px 24px;
    border-bottom: 1px solid #f0f4f8;
    transition: color 0.15s, background 0.15s, border-left-color 0.15s;
    border-left: 3px solid transparent;
  }

  .drawer-content a:hover {
    color: #162840;
    background: #f7f9fc;
    border-left-color: #162840;
  }

 @media (max-width: 768px) {
  .public-nav-inner {
    padding: 0 16px;
    height: 60px;
  }

  .public-links {
    display: none;
  }

  .public-menu-toggle {
    display: flex;
    flex-shrink: 0;
    margin-left: 12px;
  }

  .nav-brand-sub {
    display: none;
  }

  .public-brand-title {
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.35;
    white-space: normal;
    word-break: break-word;
    max-width: 200px;
  }

  .nav-brand-wrap img {
    width: 28px;
  }
}
  }
</style>

<nav class="public-nav">
  <div class="public-nav-inner">

    <a class="nav-brand-wrap" href="{{ route('public.home') }}">
      <img src="{{ asset('images/city-badge.png') }}" alt="City Emblem">
      <div class="nav-brand-text">
        <span class="public-brand-title">{{ $publicBrand ?? 'საბავშვო ბაღების გაერთიანება' }}</span>
        <span class="nav-brand-sub">ოფიციალური პორტალი</span>
      </div>
    </a>

    <button class="public-menu-toggle" id="hamburgerMenu" type="button" aria-expanded="false" aria-controls="publicNavDrawer" aria-label="მენიუს გახსნა">
      <span></span><span></span><span></span>
    </button>

    <div class="public-links">
      {{-- <a href="{{ route('public.about') }}">{{ $publicNavLabels['about'] ?? 'ჩვენ შესახებ' }}</a> --}}
      <a href="{{ route('public.news') }}">{{ $publicNavLabels['news'] ?? 'განცხადება' }}</a>
      <a href="#" data-toggle="modal" data-target="#publicRulesModal">{{ $publicNavLabels['rules'] ?? 'წესები' }}</a>
      <a href="{{ route('public.contact') }}">{{ $publicNavLabels['contact'] ?? 'კონტაქტი' }}</a>
      <a href="{{ route('public.status-tracker') }}">{{ $publicNavLabels['status'] ?? 'სტატუსი' }}</a>
    </div>

  </div>
</nav>

<div class="public-nav-drawer" id="publicNavDrawer">
  <div class="drawer-overlay" id="drawerOverlay"></div>
  <div class="drawer-content">
    <button class="drawer-close" aria-label="დახურვა">&times;</button>
    {{-- <a href="{{ route('public.about') }}">{{ $publicNavLabels['about'] ?? 'ჩვენ შესახებ' }}</a> --}}
    <a href="{{ route('public.news') }}">{{ $publicNavLabels['news'] ?? 'განცხადება' }}</a>
    <a href="#" data-toggle="modal" data-target="#publicRulesModal">{{ $publicNavLabels['rules'] ?? 'წესები' }}</a>
    <a href="{{ route('public.contact') }}">{{ $publicNavLabels['contact'] ?? 'კონტაქტი' }}</a>
    <a href="{{ route('public.status-tracker') }}">{{ $publicNavLabels['status'] ?? 'სტატუსი' }}</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var hamburger = document.getElementById('hamburgerMenu');
  var drawer = document.getElementById('publicNavDrawer');
  var overlay = document.getElementById('drawerOverlay');
  var closeBtn = drawer.querySelector('.drawer-close');

  function openDrawer() {
    drawer.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', openDrawer);
  closeBtn.addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) closeDrawer();
  });
});
</script>