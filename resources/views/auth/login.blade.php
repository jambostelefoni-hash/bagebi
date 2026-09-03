@extends('layouts.login')

@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;500;600;700&display=swap');

  * { box-sizing: border-box; }

  body, html {
    font-family: 'Noto Sans Georgian', sans-serif !important;
  }

  .login-wrapper {
    min-height: 10vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f4f8;
    padding: 24px;
    position: relative;
    overflow: hidden;
  }



  .login-card {
    width: 100%;
    max-width: 860px;
    background: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 4px 32px rgba(22,40,64,0.13), 0 1px 4px rgba(22,40,64,0.08);
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  /* ── LEFT PANEL ── */
  .login-left {
    background: #162840;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 56px 44px;
    text-align: center;
    position: relative;
  }

  .login-left::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 30% 40%, rgba(74,158,255,0.08) 0%, transparent 65%);
    pointer-events: none;
  }

  .login-badge {
    width: 110px;
    height: auto;
    margin-bottom: 28px;
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 4px 16px rgba(0,0,0,0.3));
    transition: transform 0.35s ease;
  }

  .login-badge:hover {
    transform: scale(1.24);
  }

  .login-org-title {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.45);
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
  }

  .login-org-name {
    font-size: 0.88rem;
    font-weight: 500;
    color: rgba(255,255,255,0.82);
    line-height: 1.6;
    position: relative;
    z-index: 1;
  }

  /* ── RIGHT PANEL ── */
  .login-right {
    padding: 56px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .login-heading {
    font-size: 1.3rem;
    font-weight: 700;
    color: #162840;
    margin-bottom: 6px;
    letter-spacing: -0.01em;
  }

  .login-subheading {
    font-size: 0.82rem;
    color: #8a96a8;
    margin-bottom: 36px;
    font-weight: 400;
  }

  /* Form elements */
  .login-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #4a5568;
    margin-bottom: 7px;
  }

  .login-input {
    width: 100%;
    padding: 11px 14px;
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.88rem;
    color: #162840;
    background: #f7f9fc;
    border: 1.5px solid #e2e8f0;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
    margin-bottom: 20px;
  }

  .login-input:focus {
    border-color: #162840;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(22,40,64,0.08);
  }

  .login-input.is-invalid {
    border-color: #e53e3e;
  }

  .invalid-feedback {
    font-size: 0.75rem;
    color: #e53e3e;
    margin-top: -14px;
    margin-bottom: 14px;
    display: block;
  }

  .login-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
  }

  .login-check-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .login-check-wrap input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: #162840;
    cursor: pointer;
  }

  .login-check-wrap label {
    font-size: 0.8rem;
    color: #4a5568;
    cursor: pointer;
    font-weight: 400;
  }

  .login-forgot {
    font-size: 0.78rem;
    color: #4a9eff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.15s;
  }

  .login-forgot:hover {
    color: #162840;
  }

  .login-btn {
    width: 100%;
    padding: 13px;
    background: #162840;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
    box-shadow: 0 2px 8px rgba(22,40,64,0.20);
  }

  .login-btn:hover {
    background: #1e3a5f;
    box-shadow: 0 4px 16px rgba(22,40,64,0.28);
    transform: translateY(-1px);
  }

  .login-btn:active {
    transform: translateY(0);
  }

  /* Mobile */
  @media (max-width: 640px) {
    .login-card {
      grid-template-columns: 1fr;
    }

    .login-left {
      padding: 40px 28px 32px;
    }

    .login-badge {
      width: 80px;
      margin-bottom: 18px;
    }

    .login-right {
      padding: 36px 28px;
    }
  }
</style>

<div class="login-wrapper">
  <div class="login-card">

    {{-- LEFT: Badge --}}
    <div class="login-left">
      <img src="{{ asset('city-badge.png') }}" alt="City Badge" class="login-badge">
      <div class="login-org-title">ოფიციალური პორტალი</div>
      <div class="login-org-name">
        {{ __('ა(ა)იპ სიღნაღის მუნიციპალიტეტის სკოლამდელი აღზრდის დაწესებულებათა გაერთიანება') }}
      </div>
    </div>

    {{-- RIGHT: Form --}}
    <div class="login-right">
      <div class="login-heading">{{ __('ავტორიზაცია') }}</div>
      <div class="login-subheading">{{ __('შეიყვანეთ მონაცემები ანგარიშზე შესასვლელად') }}</div>

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email" class="login-label">{{ __('ელ.ფოსტა') }}</label>
        <input id="email" type="email"
               class="login-input @error('email') is-invalid @enderror"
               name="email" value="{{ old('email') }}"
               required autocomplete="email" autofocus>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <label for="password" class="login-label">{{ __('პაროლი') }}</label>
        <input id="password" type="password"
               class="login-input @error('password') is-invalid @enderror"
               name="password" required autocomplete="current-password">
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="login-row">
          <div class="login-check-wrap">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember">{{ __('დამახსოვრება') }}</label>
          </div>
          @if (Route::has('password.request'))
            <a class="login-forgot" href="{{ route('password.request') }}">{{ __('პაროლის აღდგენა') }}</a>
          @endif
        </div>

        <button type="submit" class="login-btn">{{ __('შესვლა') }}</button>
      </form>
    </div>

  </div>
</div>

@endsection