(function () {
  'use strict';
  var key = 'ci_vendor_onboarding_v2';
  var wrap = document.getElementById('vc-toast-wrap');

  function toast(message, type) {
    if (!wrap) return;
    var el = document.createElement('div');
    el.className = 'vc-toast ' + (type || 'success');
    el.textContent = message;
    wrap.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });
    setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 250); }, 3200);
  }

  function read() {
    try {
      var data = JSON.parse(sessionStorage.getItem(key) || '{}');
      return { type: data.type === 'brand' ? 'brand' : 'artisan', status: ['draft', 'submitted', 'approved', 'changes_requested'].includes(data.status) ? data.status : 'draft', step: Math.max(1, Math.min(5, Number(data.step) || 1)), reference: String(data.reference || '').slice(0, 30), submittedAt: String(data.submittedAt || '').slice(0, 40) };
    } catch (error) { return { type: 'artisan', status: 'draft', step: 1 }; }
  }

  function save(data) {
    sessionStorage.setItem(key, JSON.stringify({ type: data.type === 'brand' ? 'brand' : 'artisan', status: data.status || 'draft', step: Math.max(1, Math.min(5, Number(data.step) || 1)), reference: String(data.reference || '').slice(0, 30), submittedAt: String(data.submittedAt || '').slice(0, 40) }));
  }

  function setError(form, name, message) { var node = form.querySelector('[data-error-for="' + name + '"]'); if (node) node.textContent = message || ''; }
  function clearErrors(form) { form.querySelectorAll('.vv-error').forEach(function (el) { el.textContent = ''; }); }
  function passwordValid(value) { return value.length >= 12 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9\s]/.test(value); }
  function setLoading(form, label) { var button = form.querySelector('.vv-submit'); if (!button) return; button.disabled = true; var span = button.querySelector('span'); if (span) span.textContent = label; }
  function showMessage(form, message) { var box = form.querySelector('[data-form-message]'); if (!box) return; box.hidden = false; box.textContent = message; }
  function updateRules(form, value) {
    var tests = { length: value.length >= 12, upper: /[A-Z]/.test(value), lower: /[a-z]/.test(value), number: /[0-9]/.test(value), special: /[^A-Za-z0-9\s]/.test(value) };
    Object.keys(tests).forEach(function (name) { var rule = form.querySelector('[data-rule="' + name + '"]'); if (rule) rule.classList.toggle('valid', tests[name]); });
  }

  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () { var input = document.getElementById(button.dataset.passwordToggle); if (!input) return; var show = input.type === 'password'; input.type = show ? 'text' : 'password'; button.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'; button.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); });
  });

  var register = document.querySelector('[data-vendor-register]');
  if (register) {
    var password = register.elements.password; var confirmPassword = register.elements.confirm_password; var phone = register.elements.phone;
    function updateType() { var label = register.querySelector('[data-name-label]'); if (label) label.textContent = register.elements.vendor_type.value === 'artisan' ? 'Artisan, group or store name' : 'Brand or store name'; }
    register.querySelectorAll('[name="vendor_type"]').forEach(function (input) { input.addEventListener('change', updateType); });
    phone.addEventListener('input', function () { phone.value = phone.value.replace(/\D/g, '').slice(0, 10); });
    password.addEventListener('input', function () { updateRules(register, password.value); });
    register.addEventListener('submit', function (event) {
      event.preventDefault(); clearErrors(register); var type = register.elements.vendor_type.value; var ok = true;
      if (!type) { setError(register, 'vendor_type', 'Choose Artisan or Brand to continue.'); ok = false; }
      if (!/^[A-Za-z][A-Za-z .'-]{1,79}$/.test(register.elements.owner_name.value.trim())) { setError(register, 'owner_name', 'Enter your legal name using valid characters.'); ok = false; }
      if (register.elements.display_name.value.trim().length < 2) { setError(register, 'display_name', 'Enter the public name customers will see.'); ok = false; }
      if (!register.elements.email.validity.valid) { setError(register, 'email', 'Enter a valid email address.'); ok = false; }
      if (!/^[6-9][0-9]{9}$/.test(phone.value)) { setError(register, 'phone', 'Enter a valid 10-digit Indian mobile number.'); ok = false; }
      if (!passwordValid(password.value)) { setError(register, 'password', 'Use 12+ characters with uppercase, lowercase, number and special character.'); ok = false; }
      if (confirmPassword.value !== password.value) { setError(register, 'confirm_password', 'Passwords do not match.'); ok = false; }
      if (!register.elements.terms.checked) { toast('Accept the required vendor terms to continue.', 'error'); ok = false; }
      if (!ok) { register.querySelector('.vv-error:not(:empty)')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
      save({ type: type, status: 'draft', step: 1 }); setLoading(register, 'Preparing verification');
      setTimeout(function () { location.href = 'verify-contact.php?type=' + encodeURIComponent(type); }, 500);
    });
  }

  var login = document.querySelector('[data-vendor-login]');
  if (login) {
    document.querySelectorAll('[data-demo-login]').forEach(function (button) { button.addEventListener('click', function () { var type = button.dataset.demoLogin; login.elements.email.value = type + '@consciousindi.test'; login.elements.password.value = 'Vendor@12345'; save({ type: type, status: 'draft', step: 1 }); toast((type === 'brand' ? 'Brand' : 'Artisan') + ' demo filled.', 'info'); }); });
    login.addEventListener('submit', function (event) { event.preventDefault(); clearErrors(login); var email = login.elements.email.value.trim().toLowerCase(); var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); if (!valid) setError(login, 'email', 'Enter a valid email address.'); if (login.elements.password.value.length < 12) setError(login, 'password', 'Password must contain at least 12 characters.'); if (!valid || login.elements.password.value.length < 12) return; var state = read(); if (email === 'brand@consciousindi.test') state.type = 'brand'; if (email === 'artisan@consciousindi.test') state.type = 'artisan'; save(state); setLoading(login, 'Signing in'); setTimeout(function () { location.href = state.status === 'submitted' ? 'status.php' : '../onboarding.php?type=' + state.type; }, 450); });
  }

  var recovery = document.querySelector('[data-vendor-recovery]');
  if (recovery) recovery.addEventListener('submit', function (event) { event.preventDefault(); clearErrors(recovery); if (!recovery.elements.email.validity.valid) { setError(recovery, 'email', 'Enter a valid email address.'); return; } showMessage(recovery, 'Demo recovery link created. Continue to the reset-password screen.'); var button = recovery.querySelector('.vv-submit'); button.type = 'button'; button.disabled = false; button.querySelector('span').textContent = 'Open demo recovery link'; button.onclick = function () { location.href = 'reset-password.php'; }; });

  var reset = document.querySelector('[data-vendor-reset]');
  if (reset) { var resetPassword = reset.elements.password; resetPassword.addEventListener('input', function () { updateRules(reset, resetPassword.value); }); reset.addEventListener('submit', function (event) { event.preventDefault(); clearErrors(reset); var ok = true; if (!passwordValid(resetPassword.value)) { setError(reset, 'password', 'Use all five password requirements.'); ok = false; } if (reset.elements.confirm_password.value !== resetPassword.value) { setError(reset, 'confirm_password', 'Passwords do not match.'); ok = false; } if (!ok) return; showMessage(reset, 'Password updated in the frontend preview. You can now sign in.'); setLoading(reset, 'Password updated'); setTimeout(function () { location.href = 'login.php'; }, 1100); }); }

  var verify = document.querySelector('[data-vendor-verify]');
  if (verify) { var code = verify.elements.code; code.addEventListener('input', function () { code.value = code.value.replace(/\D/g, '').slice(0, 6); }); verify.addEventListener('submit', function (event) { event.preventDefault(); clearErrors(verify); if (code.value !== '123456') { setError(verify, 'code', 'Use the demo verification code 123456.'); return; } var state = read(); var queryType = new URLSearchParams(location.search).get('type'); if (queryType === 'brand' || queryType === 'artisan') state.type = queryType; save(state); setLoading(verify, 'Verified'); toast('Contact verified. Opening onboarding.', 'success'); setTimeout(function () { location.href = '../onboarding.php?type=' + state.type; }, 550); }); verify.querySelector('[data-resend-code]').addEventListener('click', function () { toast('A new demo code was sent. Use 123456.', 'info'); }); }

  var status = document.querySelector('[data-vendor-status]');
  if (status) { var state = read(); status.querySelector('[data-status-type]').textContent = state.type; if (state.reference) status.querySelector('[data-status-reference]').textContent = state.reference; if (state.submittedAt) status.querySelector('[data-status-date]').textContent = state.submittedAt; status.querySelector('[data-refresh-status]').addEventListener('click', function () { toast('Status refreshed. The application is still under review.', 'info'); }); }

  if (window.gsap && !matchMedia('(prefers-reduced-motion: reduce)').matches) { gsap.from('.vv-auth-panel-inner', { y: 16, opacity: 0, duration: .55, ease: 'power2.out' }); gsap.from('.vv-story-copy>*', { y: 12, opacity: 0, duration: .5, stagger: .08, ease: 'power2.out' }); }
})();
