@extends('layouts.basic')
@section('title', 'ბავშვის რეგისტრაცია | საბავშვო ბაღების გაერთიანება')
@section('content')
<section class="modern-home">
    <div class="modern-home-glow modern-home-glow-one"></div><div class="modern-home-glow modern-home-glow-two"></div>
    <div class="modern-public-container modern-hero-grid">
        <div class="modern-hero-copy">
            <span class="modern-eyebrow"><i></i> ახალი სასწავლო წლის რეგისტრაცია</span>
            <h1>ბაღში რეგისტრაცია<br><em>მარტივად და უსაფრთხოდ</em></h1>
            <p>შეარჩიეთ სასურველი საბავშვო ბაღი, შეავსეთ ბავშვის მონაცემები და განაცხადის მდგომარეობას ონლაინ ადევნეთ თვალი.</p>
            <div class="modern-hero-actions">
                <a class="modern-primary-button" href="{{ url('/kids-registration') }}">რეგისტრაციის დაწყება <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></a>
                <button class="modern-text-button" type="button" data-toggle="modal" data-target="#publicRulesModal">რეგისტრაციის წესები</button>
            </div>
            <div class="modern-trust-row">
                <span><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg> დაცული მონაცემები</span>
                <span><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg> ონლაინ განაცხადი</span>
                <span><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg> სტატუსის კონტროლი</span>
            </div>
        </div>
        <aside class="modern-status-card">
            <div class="modern-card-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M11 8v3l2 2"/></svg></div>
            <span class="modern-card-kicker">განაცხადის მონიტორინგი</span><h2>შეამოწმეთ სტატუსი</h2>
            <p>მიუთითეთ ბავშვის პირადი ნომერი და მიიღეთ მიმდინარე ინფორმაცია.</p>
            <form id="status-check-form" class="modern-status-form">
                <label for="statusPersonalNumber">ბავშვის პირადი ნომერი</label>
                <div class="modern-input-wrap"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h4M7 13h7"/></svg><input id="statusPersonalNumber" type="text" name="kids_personal_number" inputmode="numeric" maxlength="11" placeholder="მაგ: 01001010101" required></div>
                <button type="submit"><span>სტატუსის შემოწმება</span><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
            </form>
            <div id="status-result" class="modern-status-result" aria-live="polite"></div>
        </aside>
    </div>
</section>
<section class="modern-how"><div class="modern-public-container">
    <div class="modern-section-heading"><span>როგორ მუშაობს</span><h2>სამი მარტივი ნაბიჯი</h2></div>
    <div class="modern-steps">
        <article><b>01</b><div class="modern-step-icon"><svg viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/></svg></div><h3>შეავსეთ განაცხადი</h3><p>მიუთითეთ ბავშვისა და წარმომადგენლის საჭირო ინფორმაცია.</p></article>
        <article><b>02</b><div class="modern-step-icon"><svg viewBox="0 0 24 24"><path d="M4 19V8l8-5 8 5v11H4Z"/><path d="M9 19v-6h6v6"/></svg></div><h3>აირჩიეთ ბაღი</h3><p>შეარჩიეთ თქვენთვის სასურველი ბაღი და შესაბამისი ჯგუფი.</p></article>
        <article><b>03</b><div class="modern-step-icon"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/></svg></div><h3>მიიღეთ პასუხი</h3><p>სტატუსის ცვლილების შესახებ შეტყობინებას ავტომატურად მიიღებთ.</p></article>
    </div>
</div></section>
<script>
document.getElementById('status-check-form').addEventListener('submit', function (event) {
    event.preventDefault(); var result = document.getElementById('status-result'); var button = this.querySelector('button');
    result.className = 'modern-status-result is-loading'; result.textContent = 'მიმდინარეობს შემოწმება...'; button.disabled = true;
    fetch('/api/find-kid', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify({kids_personal_number:this.kids_personal_number.value})})
    .then(function(response){return response.json();}).then(function(data){
        if(data.status === 'success' && data.data){var kid=data.data; result.className='modern-status-result is-success'; result.textContent=kid.application_status_label||'სტატუსი არ არის მითითებული';}
        else{result.className='modern-status-result is-empty'; result.textContent='მითითებული პირადი ნომრით განაცხადი ვერ მოიძებნა.';}
    }).catch(function(){result.className='modern-status-result is-error'; result.textContent='ინფორმაციის მიღება ვერ მოხერხდა. სცადეთ მოგვიანებით.';}).finally(function(){button.disabled=false;});
});
</script>
@endsection
