(function () {
  'use strict';
  var rows = Array.prototype.slice.call(document.querySelectorAll('[data-attendance-row]'));
  if (!rows.length) return;
  var ids = {present: 'attendance-present-count', absent: 'attendance-absent-count', excused: 'attendance-excused-count'};

  function selectStatus(row, status) {
    var input = row.querySelector('.attendance-status');
    if (!input || input.value === 'non_working') return;
    input.value = status;
    row.classList.remove('attendance-person--missing');
    row.querySelectorAll('[data-status]').forEach(function (button) {
      var selected = button.dataset.status === status;
      button.classList.toggle('is-selected', selected);
      button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
  }

  function updateSummary() {
    var counts = {present: 0, absent: 0, excused: 0, non_working: 0};
    rows.forEach(function (row) {
      var input = row.querySelector('.attendance-status');
      if (input && Object.prototype.hasOwnProperty.call(counts, input.value)) counts[input.value]++;
    });
    Object.keys(ids).forEach(function (status) {
      var output = document.getElementById(ids[status]);
      if (output) output.textContent = String(counts[status]);
    });
    var recorded = counts.present + counts.absent + counts.excused + counts.non_working;
    var unmarked = Math.max(0, rows.length - recorded);
    var recordedOutput = document.getElementById('attendance-recorded-count');
    var unmarkedOutput = document.getElementById('attendance-unmarked-count');
    var progress = document.getElementById('attendance-progress');
    var hint = document.getElementById('attendance-save-hint');
    if (recordedOutput) recordedOutput.textContent = String(recorded);
    if (unmarkedOutput) unmarkedOutput.textContent = String(unmarked);
    if (progress) progress.value = recorded;
    if (hint && !counts.non_working) hint.textContent = unmarked ? 'შენახვამდე მონიშნეთ კიდევ ' + unmarked + ' აღსაზრდელი.' : 'ჟურნალი სრულად არის შევსებული და მზადაა შესანახად.';
  }

  rows.forEach(function (row) {
    row.querySelectorAll('[data-status]').forEach(function (button) {
      button.setAttribute('aria-pressed', button.classList.contains('is-selected') ? 'true' : 'false');
      button.addEventListener('click', function () { selectStatus(row, button.dataset.status); updateSummary(); });
    });
  });
  document.querySelectorAll('[data-bulk-status]').forEach(function (button) {
    button.addEventListener('click', function () { rows.forEach(function (row) { selectStatus(row, button.dataset.bulkStatus); }); updateSummary(); });
  });
  var form = document.getElementById('attendance-form');
  if (form) form.addEventListener('submit', function (event) {
    var firstMissing = rows.find(function (row) { var input = row.querySelector('.attendance-status'); return input && input.value === ''; });
    if (!firstMissing) return;
    event.preventDefault();
    firstMissing.classList.add('attendance-person--missing');
    firstMissing.scrollIntoView({behavior: 'smooth', block: 'center'});
    var hint = document.getElementById('attendance-save-hint');
    if (hint) hint.textContent = 'ჟურნალი ვერ შეინახება — ყველა აღსაზრდელს მიუთითეთ სტატუსი.';
  });
  updateSummary();
})();
