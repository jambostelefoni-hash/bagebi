@extends('layouts.login')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden w-100" style="max-width: 900px;">
        <div class="row g-0">
            {{-- Left: Login form --}}
            <div class="col-lg-7 col-md-12 p-5 order-2 order-lg-1 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">{{ __('ავტორიზაცია') }}</h3>
                    <p class="text-muted small mb-0">{{ __('შეიყვანეთ მონაცემები ანგარიშზე შესასვლელად') }}</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">{{ __('ელ.ფოსტა') }}</label>
                        <input id="email" type="email"
                               class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <div class="invalid-feedback small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">{{ __('პაროლი') }}</label>
                        <input id="password" type="password"
                               class="form-control form-control-lg rounded-3 @error('password') is-invalid @enderror"
                               name="password" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember">
                                {{ __('დამახსოვრება') }}
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="small text-primary text-decoration-none fw-semibold" href="{{ route('password.request') }}">
                                {{ __('პაროლის აღდგენა') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold">
                        {{ __('შესვლა') }}
                    </button>
                </form>
            </div>

            {{-- Right: City badge with elegant glow --}}
            <div class="col-lg-5 col-md-12 d-flex align-items-center justify-content-center bg-white text-center p-5 order-1 order-lg-2">
                <div>
                    <img src="{{ asset('city-badge.png') }}"
                         alt="City Badge"
                         class="img-fluid mb-4 glow-badge"
                         style="max-height: 160px;">

                    <h5 class="fw-bold text-dark">{{ __('') }}</h5>
                    <p class="text-muted small">
                        {{ __('ა(ა)იპ სიღნაღის მუნიციპალიტეტის სკოლამდელი აღზრდის დაწესებულებათა გაერთიანება') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

