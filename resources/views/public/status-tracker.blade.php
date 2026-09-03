@extends('layouts.basic')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;500;600;700&display=swap');

  * { box-sizing: border-box; }

  .public-page-shell {
    min-height: 100vh;
    background: #f4f7fb;
    padding: 56px 20px 100px;
    font-family: 'Noto Sans Georgian', sans-serif;
  }

  .public-container {
    max-width: 560px;
    margin: 0 auto;
    width: 100%;
  }

  .public-article {
    background: #ffffff;
    border-radius: 6px;
    box-shadow: 0 1px 8px rgba(22,40,64,0.08);
    overflow: hidden;
  }

  .public-article-header {
    padding: 36px 40px 28px;
    border-bottom: 1px solid #eef1f6;
  }

  .public-article-header h1 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a2535;
    margin: 0 0 6px 0;
    line-height: 1.3;
  }

  .public-article-header p {
    font-size: 0.78rem;
    color: #9aa5b4;
    margin: 0;
  }

  /* Form */
  .public-tracker-form {
    padding: 28px 40px;
    border-bottom: 1px solid #eef1f6;
    display: flex;
    gap: 10px;
    align-items: flex-start;
  }

  .public-tracker-form .form-group {
    flex: 1;
    margin: 0;
  }

  .public-tracker-form input.form-control {
    width: 100%;
    padding: 11px 14px;
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.88rem;
    color: #1a2535;
    background: #f7f9fc;
    border: 1.5px solid #e2e8f0;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
  }

  .public-tracker-form input.form-control:focus {
    border-color: #162840;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(22,40,64,0.07);
  }

  .public-tracker-form input.form-control::placeholder {
    color: #b0bac8;
  }

  .public-tracker-form button {
    padding: 11px 22px;
    background: #162840;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-family: 'Noto Sans Georgian', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.18s;
    flex-shrink: 0;
  }

  .public-tracker-form button:hover {
    background: #1e3a5f;
  }

  /* Result */
  .public-tracker-result {
    padding: 28px 40px 36px;
  }

  .public-status-list {
    border: 1px solid #eef1f6;
    border-radius: 4px;
    overflow: hidden;
  }

  .public-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid #eef1f6;
    transition: background 0.15s;
  }

  .public-status-row:last-child {
    border-bottom: none;
  }

  .public-status-row:hover {
    background: #fafbfd;
  }

  .public-status-row span {
    font-size: 0.8rem;
    color: #9aa5b4;
    font-weight: 400;
  }

  .public-status-row strong {
    font-size: 0.88rem;
    color: #1a2535;
    font-weight: 600;
  }

  .alert-warning {
    padding: 14px 18px;
    background: #fffbea;
    border-left: 3px solid #e6b800;
    border-radius: 4px;
    font-size: 0.84rem;
    color: #7a6000;
  }

  /* Mobile */
  @media (max-width: 600px) {
    .public-page-shell {
      padding: 24px 12px 60px;
    }

    .public-article-header {
      padding: 24px 20px 20px;
    }

    .public-tracker-form {
      flex-direction: column;
      padding: 20px;
    }

    .public-tracker-form button {
      width: 100%;
    }

    .public-tracker-result {
      padding: 20px;
    }
    
    
  }
</style>

<section class="public-page-shell">
  <div class="public-container">
    <article class="public-article">
      <header class="public-article-header">
        <h1>სტატუსის შემოწმება</h1>
        <p>შეიყვანეთ ბავშვის პირადი ნომერი (11 ციფრი)</p>
      </header>

      <form method="GET" action="{{ route('public.status-tracker') }}" class="public-tracker-form">
        <div class="form-group">
          <input
            type="text"
            name="kids_personal_number"
            class="form-control"
            placeholder="ბავშვის პირადი ნომერი"
            value="{{ $query }}"
            maxlength="11"
            required
          >
        </div>
        <button type="submit">შემოწმება</button>
      </form>

      @if ($query)
        <div class="public-tracker-result">
          @if ($kid)
            <div class="public-status-list">
              <div class="public-status-row">
  <span>სტატუსი</span>
  <strong>
   @php
  $label = $statusLabel ?? 'უცნობი';
  $inlineStyle = match($label) {
    'აქტიური' => 'background:#e6f4ea;color:#1a7f3c;',
    'მომლოდინე' => 'background:#fffbea;color:#b45309;',
    'გასული' => 'background:#fde8e8;color:#c53030;',
    default => 'background:#f0f4f8;color:#4a5568;'
  };
@endphp
<span style="{{ $inlineStyle }}; display:inline-block; padding:4px 12px; border-radius:3px; font-size:0.78rem; font-weight:600;">{{ $label }}</span>
  </strong>
</div>
              <div class="public-status-row">
                <span>ბაღი</span>
                <strong>{{ $kid->kindergarten ? $kid->kindergarten->name : '---' }}</strong>
              </div>
              <div class="public-status-row">
                <span>ჯგუფი</span>
                <strong>{{ $kid->groupRange ? $kid->groupRange->range : '---' }}</strong>
              </div>
            </div>
          @else
            <div class="alert alert-warning">ჩანაწერი ვერ მოიძებნა.</div>
          @endif
        </div>
      @endif

    </article>
  </div>
</section>

@endsection