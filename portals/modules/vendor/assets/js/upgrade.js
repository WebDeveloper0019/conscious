(function () {
  'use strict';
  var dialog = document.getElementById('vu-plan-dialog');
  if (!dialog) return;
  var selectedPlan = document.getElementById('vu-selected-plan');
  var selectedPrice = document.getElementById('vu-selected-price');
  var note = document.getElementById('vu-note');
  var emailLink = document.getElementById('vu-email-link');

  function updateEmail() {
    var plan = selectedPlan.textContent.trim();
    var body = 'Hello Conscious India Partnerships,\n\nI would like to discuss the ' + plan + ' plan for my brand.';
    if (note.value.trim()) body += '\n\nGoals / questions:\n' + note.value.trim();
    body += '\n\nPlease confirm current pricing, GST, eligibility and final deliverables.';
    emailLink.href = 'mailto:partners@consciousindi.com?subject=' + encodeURIComponent('Plan enquiry: ' + plan) + '&body=' + encodeURIComponent(body);
  }

  document.querySelectorAll('.vu-plan-button').forEach(function (button) {
    button.addEventListener('click', function () {
      selectedPlan.textContent = button.dataset.plan || 'Subscription plan';
      selectedPrice.textContent = button.dataset.price || '';
      note.value = '';
      updateEmail();
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else dialog.setAttribute('open', '');
    });
  });
  document.querySelectorAll('[data-vu-close]').forEach(function (button) {
    button.addEventListener('click', function () { dialog.close(); });
  });
  note.addEventListener('input', updateEmail);
  dialog.addEventListener('click', function (event) {
    if (event.target === dialog) dialog.close();
  });
})();

