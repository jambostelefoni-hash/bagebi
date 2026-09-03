@extends('layouts.basic')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;500;600;700&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

 

  .public-container {
    width: 100%;
    max-width: 880px;
  }

  .public-hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: stretch;
  }

  /* ── LEFT BLOCK ── */
  .public-hero-panel-link,
  .public-hero-panel-link:hover,
  .public-hero-panel-link:visited,
  .public-hero-panel-link:active,
  .public-hero-panel-link:focus {
    background: #162840 !important;
    background-color: #162840 !important;
  }

  .public-hero-panel-link {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 18px !important;
    padding: 60px 40px !important;
    text-decoration: none !important;
    border-radius: 16px 0 0 16px !important;
    min-height: 380px !important;
    transition: opacity 0.25s !important;
  }

  .public-hero-panel-link:hover {
    opacity: 0.9 !important;
  }

  .public-hero-panel-link:hover .reg-icon {
    transform: scale(1.08);
  }

  .reg-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1.5px solid rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s;
  }

  .reg-icon svg {
    width: 24px;
    height: 24px;
    stroke: #fff;
    fill: none;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .public-title {
    font-size: 1.9rem !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    letter-spacing: 0.02em !important;
    text-align: center !important;
  }

  .reg-hint {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.4);
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  /* ── RIGHT BLOCK ── */
  .public-hero-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 40px;
    background: #ffffff;
    border-radius: 0 16px 16px 0;
    box-shadow: 4px 0 32px rgba(0,0,0,0.06);
    min-height: 380px;
  }

  .card-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #1a6ef5;
    margin-bottom: 6px;
  }

  .card-desc {
    font-size: 0.95rem;
    color: #4a5568;
    line-height: 1.55;
    margin-bottom: 28px;
  }

  /* Form */
  .status-check-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .status-check-form input[type="text"] {
    width: 100% !important;
    padding: 12px 15px !important;
    font-family: 'Noto Sans Georgian', sans-serif !important;
    font-size: 0.9rem !important;
    color: #1a202c !important;
    background: #f7f9fc !important;
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important;
    outline: none !important;
    margin-bottom: 0 !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
  }

  .status-check-form input[type="text"]::placeholder {
    color: #a0aec0;
  }

  .status-check-form input[type="text"]:focus {
    border-color: #1a6ef5 !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(26,110,245,0.10) !important;
  }

  .status-check-form button[type="submit"] {
    width: 100% !important;
    padding: 12px !important;
    font-family: 'Noto Sans Georgian', sans-serif !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    color: #fff !important;
    background: #162840 !important;
    background-color: #162840 !important;
    border: none !important;
    border-radius: 10px !important;
    cursor: pointer !important;
    letter-spacing: 0.04em !important;
    transition: background 0.2s, transform 0.15s !important;
  }

  .status-check-form button[type="submit"]:hover {
    background: #1a6ef5 !important;
    background-color: #1a6ef5 !important;
    transform: translateY(-1px) !important;
  }

  /* Result */
  #status-result {
    margin-top: 16px !important;
    text-align: left !important;
  }

  #status-result:not(:empty) {
    background: #f7f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 18px;
  }

  #status-result div {
    font-size: 0.85rem;
    color: #4a5568;
    line-height: 1.8;
  }

  #status-result div b {
    color: #1a202c;
    font-weight: 600;
  }

  /* ── MOBILE ── */
  @media (max-width: 640px) {
    .public-hero-grid {
      grid-template-columns: 1fr;
    }

    .public-hero-panel-link {
      border-radius: 16px 16px 0 0 !important;
      min-height: 220px !important;
      padding: 48px 28px !important;
    }

    .public-hero-card {
      border-radius: 0 0 16px 16px;
      padding: 40px 28px;
      box-shadow: none;
    }
  }
</style>

<section class="public-hero">
  <div class="public-container">
    <div class="public-hero-grid">

      <a class="public-hero-panel public-hero-panel-link" href="/kids-registration">
        <div class="reg-icon">
          <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
        </div>
        <h1 class="public-title">რეგისტრაცია</h1>
        <span class="reg-hint">გადასვლა →</span>
      </a>

      <div class="public-hero-card">
        <div class="card-label">სტატუსის შემოწმება</div>
        <div class="card-desc">გადაამოწმეთ რეგისტრაციის სტატუსი პირადი ნომრით</div>

        <form id="status-check-form" class="status-check-form">
          <input type="text" name="kids_personal_number" placeholder="პირადი ნომერი" required>
          <button type="submit">შეამოწმე</button>
        </form>

        <div id="status-result"></div>

        <script>
        document.getElementById('status-check-form').addEventListener('submit', function(e) {
          e.preventDefault();
          var input = this.kids_personal_number.value;
          var resultDiv = document.getElementById('status-result');
          resultDiv.innerHTML = 'იტვირთება...';
          fetch('/api/find-kid', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ kids_personal_number: input })
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
              let kid = data.data[0];
              let name = kid.kids_first_name ? kid.kids_first_name : '';
              let surname = kid.kids_last_name ? kid.kids_last_name : '';
              let kindergarten = kid.kindergarten && kid.kindergarten.name ? kid.kindergarten.name : '';
              let status = kid.active_status ? kid.active_status.name : '';
              resultDiv.innerHTML =
                '<div>სახელი: <b>' + name + '</b></div>' +
                '<div>გვარი: <b>' + surname + '</b></div>' +
                '<div>ბაღი: <b>' + kindergarten + '</b></div>' +
                '<div>სტატუსი: <b>' + status + '</b></div>';
            } else {
              resultDiv.innerHTML = 'მონაცემი არ მოიძებნა';
            }
          })
          .catch(() => { resultDiv.innerHTML = 'შეცდომა!'; });
        });
        </script>
      </div>

    </div>
  </div>
</section>
@endsection