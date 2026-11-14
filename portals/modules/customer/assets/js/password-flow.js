(function () {
  'use strict';
  var form = document.querySelector('[data-password-flow]');
  if (!form) return;
  var mode = form.dataset.passwordFlow;
  var alert = form.querySelector('[data-form-alert]');
  var submit = form.querySelector('[data-submit-button]');
  function message(value, error) {
    alert.textContent = value;
    alert.hidden = false;
    alert.classList.toggle('success', !error);
    if (error) alert.focus();
  }
  function invalid(name, value) {
    var input = form.elements[name];
    var hint = form.querySelector('[data-error-for="' + name + '"]');
    if (input) { input.setAttribute('aria-invalid', 'true'); input.closest('.ca-field').classList.add('invalid'); }
    if (hint) hint.textContent = value;
  }
  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    alert.hidden = true;
    form.querySelectorAll('.ca-field').forEach(function (field) { field.classList.remove('invalid'); });
    form.querySelectorAll('input').forEach(function (input) { input.removeAttribute('aria-invalid'); });
    form.querySelectorAll('[data-error-for]').forEach(function (hint) { hint.textContent = ''; });
    if (!form.checkValidity()) { form.reportValidity(); return; }
    var payload;
    if (mode === 'forgot') {
      payload = { email: form.elements.email.value.trim(), account_type: 'customer' };
    } else {
      var token = new URLSearchParams(location.search).get('token') || '';
      var password = form.elements.password.value;
      if (token.length < 40) { message('This reset link is missing or expired. Request a new one.', true); return; }
      if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{12,}/.test(password)) { invalid('password', 'Use 12+ characters with uppercase, lowercase, number and symbol.'); return; }
      if (password !== form.elements.password_confirmation.value) { invalid('password_confirmation', 'Passwords do not match.'); return; }
      payload = { token: token, password: password, password_confirmation: form.elements.password_confirmation.value };
    }
    submit.disabled = true;
    try {
      var response = await fetch('/conscious/api/auth/' + (mode === 'forgot' ? 'forgot-password.php' : 'reset-password.php'), {
        method: 'POST', credentials: 'same-origin', cache: 'no-store',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
      });
      var data = await response.json().catch(function () { return {}; });
      if (!response.ok) throw new Error(data.message || 'Please try again.');
      message(data.message || (mode === 'forgot' ? 'If the account exists, a reset link has been sent.' : 'Password changed. You can sign in now.'), false);
      if (mode === 'reset') { form.reset(); history.replaceState({}, '', location.pathname); }
    } catch (error) { message(error.message, true); }
    finally { submit.disabled = false; }
  });
})();
