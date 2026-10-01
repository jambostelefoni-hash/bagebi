(function () {
    'use strict';
    var toggle = document.getElementById('publicMenuToggle');
    var menu = document.getElementById('publicNavMenu');
    if (!toggle || !menu) return;
    function closeMenu(restoreFocus) {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'მენიუს გახსნა');
        if (restoreFocus) toggle.focus();
    }
    toggle.addEventListener('click', function () {
        var open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'მენიუს დახურვა' : 'მენიუს გახსნა');
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && menu.classList.contains('is-open')) closeMenu(true);
    });
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-public-header]')) closeMenu(false);
    });
    menu.addEventListener('click', function (event) {
        if (event.target.closest('a')) closeMenu(false);
    });
    window.matchMedia('(min-width: 761px)').addEventListener('change', function (event) {
        if (event.matches) closeMenu(false);
    });
}());

// The homepage uses the same restricted status API as before, without inline script.
(function () {
    'use strict';
    var form = document.getElementById('status-check-form');
    var result = document.getElementById('status-result');
    if (!form || !result) return;
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        result.dataset.state = 'loading';
        result.textContent = 'მიმდინარეობს შემოწმება…';
        result.setAttribute('aria-busy', 'true');
        button.disabled = true;
        fetch(form.dataset.statusUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify({kids_personal_number: form.elements.kids_personal_number.value, mobile_last_four: form.elements.mobile_last_four.value})
        }).then(function (response) {
            if (!response.ok) throw new Error(response.status === 429 ? 'rate-limit' : 'request-failed');
            return response.json();
        }).then(function (data) {
            result.dataset.state = data.status === 'success' && data.data ? 'success' : 'empty';
            result.textContent = data.status === 'success' && data.data
                ? data.data.application_status_label || 'სტატუსი არ არის მითითებული'
                : 'განაცხადი ვერ მოიძებნა. გადაამოწმეთ პირადი ნომერი და მობილურის ბოლო ოთხი ციფრი.';
        }).catch(function (error) {
            result.dataset.state = 'error';
            result.textContent = error.message === 'rate-limit'
                ? 'მოთხოვნების რაოდენობა ამოიწურა. სცადეთ რამდენიმე წუთში.'
                : 'ინფორმაციის მიღება ვერ მოხერხდა. სცადეთ მოგვიანებით.';
        }).finally(function () {
            button.disabled = false;
            result.setAttribute('aria-busy', 'false');
        });
    });
}());
