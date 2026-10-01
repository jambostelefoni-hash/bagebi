(function () {
    'use strict';

    var body = document.body;
    if (!body || !body.dataset.tourRole) return;

    var version = 'admin-tour-v1';
    var role = body.dataset.tourRole;
    var activeStep = 0;
    var steps = [];
    var opener = null;
    var backdrop = null;
    var previousFocus = null;

    var commonSteps = [
        {title: 'სისტემის გაცნობა', text: 'ეს მოკლე გზამკვლევი გაგაცნობთ მართვის პანელის მთავარ სამუშაო ნაწილებს. ნებისმიერ დროს შეგიძლიათ თავიდან გაუშვათ ზედა ზოლიდან.', selector: null},
        {title: 'სამუშაო დაფა', text: 'აქ ჩანს ძირითადი მაჩვენებლები და აქტიური ამოცანები. დირექტორისთვის — ყოველდღიური დასწრება, გაცდენები და შეთავაზებები; გაერთიანებისთვის — სისტემის საერთო სურათი.', selector: '[data-tour="dashboard"]'},
        {title: 'ყოველდღიური მუშაობა', text: '„აღსაზრდელები“ გამოიყენეთ განაცხადების, სტატუსების, რიგისა და ექსპორტისთვის. „დასწრებაში“ ყოველდღიურად მონიშნეთ ყოფნა ან გაცდენა — სისტემა ავტომატურად აკონტროლებს შეჩერების ზღვარს.', selector: '[data-tour="daily"]'}
    ];

    var unionSteps = [
        {title: 'სტრუქტურის მართვა', text: 'აქ იქმნება ბაღები, ასაკობრივი ჯგუფები და დირექტორის ანგარიშები. ტევადობის ზღვარი მხოლოდ გაერთიანების ადმინისტრაციამ უნდა განსაზღვროს.', selector: '[data-tour="structure"]'},
        {title: 'სისტემის მართვა', text: 'ამ განყოფილებაშია სასწავლო წელი, სამუშაო კალენდარი, აღდგენის მოთხოვნები, რიგისა და შეტყობინებების კონტროლი, ანალიტიკა, გამართულობა და სრული აუდიტის ჟურნალი.', selector: '[data-tour="system"]'}
    ];

    steps = commonSteps.concat(role === 'union_admin' ? unionSteps : []).concat([
        {title: 'ანგარიშის უსაფრთხოება', text: 'აქ შეგიძლიათ შეცვალოთ ანგარიშის მონაცემები და პაროლი. არასოდეს გაუზიაროთ პაროლი სხვა თანამშრომელს; ყველა მნიშვნელოვანი მოქმედება აღირიცხება აუდიტის ჟურნალში.', selector: '.admin-profile-button'},
        {title: 'მზად ხართ სამუშაოდ', text: 'გზამკვლევი დასრულდა. თუ რომელიმე ფუნქციაზე დამატებითი განმარტება დაგჭირდებათ, ზედა ზოლში „სისტემის გაცნობიდან“ ნებისმიერ დროს თავიდან გაუშვით.', selector: null}
    ]);

    function csrfToken() {
        var token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    function clearHighlight() {
        var current = document.querySelector('.guided-tour-highlight');
        if (current) current.classList.remove('guided-tour-highlight');
    }

    function highlight(selector) {
        clearHighlight();
        if (!selector) return;
        var target = document.querySelector(selector);
        if (!target) return;
        target.classList.add('guided-tour-highlight');
        target.scrollIntoView({block: 'nearest', behavior: 'smooth'});
    }

    function persistCompletion() {
        if (!body.dataset.tourCompleteUrl || !window.fetch) return;
        window.fetch(body.dataset.tourCompleteUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken()},
            body: JSON.stringify({version: version})
        });
    }

    function close(markComplete) {
        clearHighlight();
        if (backdrop) backdrop.remove();
        backdrop = null;
        document.body.classList.remove('guided-tour-open');
        if (markComplete) persistCompletion();
        if (previousFocus) previousFocus.focus();
    }

    function focusable(container) {
        return Array.prototype.slice.call(container.querySelectorAll('button:not([disabled]), [href], input:not([disabled])'));
    }

    function render() {
        var step = steps[activeStep];
        highlight(step.selector);
        backdrop.innerHTML = '<section class="guided-tour-dialog" role="dialog" aria-modal="true" aria-labelledby="guided-tour-title" aria-describedby="guided-tour-copy">'
            + '<header class="guided-tour-header"><div><p class="guided-tour-kicker">სისტემის გზამკვლევი</p><h2 class="guided-tour-title" id="guided-tour-title"></h2></div><button type="button" class="guided-tour-close" aria-label="გზამკვლევის დახურვა">×</button></header>'
            + '<div class="guided-tour-content"><p id="guided-tour-copy"></p><div class="guided-tour-progress" aria-hidden="true"><span></span></div></div>'
            + '<footer class="guided-tour-footer"><span class="guided-tour-count"></span><div class="guided-tour-actions"><button type="button" class="guided-tour-button guided-tour-button--quiet" data-tour-skip>გამოტოვება</button><button type="button" class="guided-tour-button guided-tour-button--primary" data-tour-next></button></div></footer>'
            + '</section>';
        backdrop.querySelector('#guided-tour-title').textContent = step.title;
        backdrop.querySelector('#guided-tour-copy').textContent = step.text;
        backdrop.querySelector('.guided-tour-count').textContent = (activeStep + 1) + ' / ' + steps.length;
        var progress = backdrop.querySelector('.guided-tour-progress span');
        progress.className = 'guided-tour-progress__value guided-tour-progress__value--' + (activeStep + 1) + '-of-' + steps.length;
        backdrop.querySelector('[data-tour-next]').textContent = activeStep === steps.length - 1 ? 'დასრულება' : 'შემდეგი';
        backdrop.querySelector('[data-tour-next]').focus();
    }

    function start(event) {
        if (backdrop) return;
        previousFocus = document.activeElement;
        opener = event && event.currentTarget;
        activeStep = 0;
        backdrop = document.createElement('div');
        backdrop.className = 'guided-tour-backdrop';
        document.body.appendChild(backdrop);
        document.body.classList.add('guided-tour-open');
        render();
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-guided-tour-start]');
        if (trigger) { start({currentTarget: trigger}); return; }
        if (!backdrop) return;
        if (event.target.closest('[data-tour-next]')) {
            if (activeStep === steps.length - 1) close(true);
            else { activeStep++; render(); }
        } else if (event.target.closest('[data-tour-skip], .guided-tour-close')) {
            close(true);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!backdrop) return;
        if (event.key === 'Escape') { close(true); return; }
        if (event.key !== 'Tab') return;
        var items = focusable(backdrop);
        if (!items.length) return;
        var first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });

    if (body.dataset.tourCompleted !== 'true' && window.location.pathname === '/home') {
        window.setTimeout(start, 500);
    }
}());
