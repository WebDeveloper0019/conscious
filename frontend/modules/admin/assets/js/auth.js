(function () {
  'use strict';
  var wrap = document.getElementById('ac-toast-wrap');
  function toast(message, type) {
    var el = document.createElement('div'); el.className = 'ac-toast ' + (type || '');
    el.textContent = message; if (wrap) wrap.appendChild(el);
    setTimeout(function () { el.remove(); }, 5000);
  }
  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.dataset.passwordToggle); if (!input) return;
      var showing = input.type === 'text'; input.type = showing ? 'password' : 'text';
      button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      button.querySelector('i').className = showing ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
    });
  });
  async function post(path, payload) {
    var response = await fetch('../../../../api/admin/auth/' + path, {
      method: 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(data.message || 'The request could not be completed.');
    return data;
  }
  document.querySelectorAll('form[data-admin-form]').forEach(function (form) {
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      var action = form.dataset.adminForm;
      if (action !== 'admin-login' && action !== 'admin-mfa') {
        toast('Recovery requires the authorised backend service.', 'error'); return;
      }
      var button = form.querySelector('[type="submit"]'); button.disabled = true;
      try {
        if (action === 'admin-login') {
          var login = await post('login.php', { email: form.elements.email.value.trim(), password: form.elements.password.value });
          if (login.mfa_required) {
            sessionStorage.setItem('ci_admin_mfa_challenge', login.challenge_token);
            location.assign('verify.php'); return;
          }
          sessionStorage.removeItem('ci_admin_mfa_challenge');
          if (login.csrf_token) sessionStorage.setItem('ci_admin_csrf_token', login.csrf_token);
          location.assign('../index.php');
        } else {
          var challenge = sessionStorage.getItem('ci_admin_mfa_challenge');
          if (!challenge) throw new Error('This verification session has expired. Sign in again.');
          var verified = await post('verify-mfa.php', { challenge_token: challenge, code: form.elements.code.value.trim() });
          sessionStorage.removeItem('ci_admin_mfa_challenge');
          if (verified.csrf_token) sessionStorage.setItem('ci_admin_csrf_token', verified.csrf_token);
          location.assign('../index.php');
        }
      } catch (error) { toast(error.message, 'error'); }
      finally { button.disabled = false; }
    });
  });
  document.querySelectorAll('[data-demo-action]').forEach(function (button) {
    button.addEventListener('click', function () { toast('Contact the security administrator for MFA recovery.', 'error'); });
  });
})();
