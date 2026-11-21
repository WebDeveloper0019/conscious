(function () {
  'use strict';
  var root = '/conscious/api/vendor/auth/';
  function show(form, message) {
    var box = form.querySelector('[data-form-message]');
    if (box) { box.hidden = false; box.textContent = message; }
  }
  async function post(path, payload) {
    var response = await fetch(path.startsWith('/') ? path : root + path, {
      method: 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok) { var error = new Error(data.message || 'Please try again.'); error.fields = data.errors || null; throw error; }
    return data;
  }
  function busy(form, value) {
    var button = form.querySelector('[type="submit"]');
    if (button) button.disabled = value;
  }
  document.querySelectorAll('[data-password-toggle]').forEach(function(button){button.addEventListener('click',function(){var input=document.getElementById(button.dataset.passwordToggle);if(!input)return;var visible=input.type==='password';input.type=visible?'text':'password';button.setAttribute('aria-label',visible?'Hide password':'Show password');button.querySelector('i').className=visible?'fa-regular fa-eye-slash':'fa-regular fa-eye';});});
  document.querySelectorAll('.vv-password-rules').forEach(function(rules){var form=rules.closest('form'),password=form&&form.elements.password;if(!password)return;password.addEventListener('input',function(){var checks={length:password.value.length>=12,upper:/[A-Z]/.test(password.value),lower:/[a-z]/.test(password.value),number:/[0-9]/.test(password.value),special:/[^A-Za-z0-9\s]/.test(password.value)};Object.entries(checks).forEach(function(entry){var item=rules.querySelector('[data-rule="'+entry[0]+'"]');if(item)item.classList.toggle('valid',entry[1]);});});});
  var vendorType=document.querySelector('[data-vendor-register]');
  function registerStep(step) {
    if (!vendorType) return;
    vendorType.querySelectorAll('[data-register-step]').forEach(function (panel) { panel.hidden = Number(panel.dataset.registerStep) !== step; });
    vendorType.querySelectorAll('[data-progress-step]').forEach(function (item) {
      var active = Number(item.dataset.progressStep) === step;
      item.classList.toggle('active', active);
      if (active) item.setAttribute('aria-current', 'step'); else item.removeAttribute('aria-current');
    });
    var heading = document.querySelector('[data-register-heading]');
    if (heading) heading.textContent = step === 1 ? 'Join Conscious India' : 'One last detail';
    var intro = document.querySelector('[data-register-intro]');
    if (intro) intro.textContent = step === 1 ? 'Create your account in two short steps.' : 'Your profile details can be completed after verification.';
  }
  function registerError(name, message) {
    var field = vendorType.elements.namedItem(name);
    var hint = vendorType.querySelector('[data-error-for="' + name + '"]');
    if (field && field instanceof HTMLElement) { field.setAttribute('aria-invalid', 'true'); field.setAttribute('data-inline-invalid', 'true'); }
    if (hint) {
      hint.textContent = message;
      hint.hidden = false;
      if (!hint.id) hint.id = 'register-error-' + name;
      if (field && field instanceof HTMLElement) field.setAttribute('aria-describedby', hint.id);
    }
  }
  function clearRegisterErrors() {
    vendorType.querySelectorAll('[data-inline-invalid]').forEach(function (input) { input.removeAttribute('data-inline-invalid'); input.removeAttribute('aria-invalid'); input.removeAttribute('aria-describedby'); });
    vendorType.querySelectorAll('.vv-error').forEach(function (hint) { hint.textContent = ''; hint.hidden = true; });
    var message = vendorType.querySelector('[data-form-message]');
    if (message) { message.textContent = ''; message.hidden = true; }
  }
  function validateRegisterStep(step) {
    clearRegisterErrors();
    var first = null;
    if (step === 1 && !vendorType.querySelector('[name="registration_type"]:checked')) {
      registerError('registration_type', 'Choose how you want to join.');
      first = vendorType.querySelector('[name="registration_type"]');
    }
    var names = step === 1 ? ['owner_name', 'phone', 'email', 'password', 'confirm_password'] : [vendorType.elements.registration_type.value === 'influencer' ? 'handle' : 'display_name', 'terms'];
    names.forEach(function (name) {
      var input = vendorType.elements[name];
      if (!input || input.disabled || input.checkValidity()) return;
      var message = input.validity.valueMissing ? 'This field is required.' : (name === 'phone' ? 'Enter a valid 10-digit Indian mobile number.' : name === 'email' ? 'Enter a valid email address.' : name === 'handle' ? 'Use 3–100 letters, numbers, dots, underscores or hyphens.' : 'Please check this value.');
      if (name === 'terms') message = 'Please agree to the terms to continue.';
      registerError(name, message);
      if (!first) first = input;
    });
    if (step === 1) {
      var password = vendorType.elements.password.value;
      if (password && !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s]).{12,}/.test(password)) {
        registerError('password', 'Use 12+ characters with upper and lowercase, a number, and a symbol.');
        if (!first) first = vendorType.elements.password;
      }
      if (vendorType.elements.confirm_password.value && password !== vendorType.elements.confirm_password.value) {
        registerError('confirm_password', 'Passwords do not match.');
        if (!first) first = vendorType.elements.confirm_password;
      }
    }
    if (first) { first.focus(); return false; }
    return true;
  }
  function setRegisterType(type) {
    if (!vendorType) return;
    var creator = type === 'influencer';
    vendorType.querySelectorAll('[data-vendor-only]').forEach(function (field) {
      field.hidden = creator;
      field.querySelectorAll('input').forEach(function (input) { input.disabled = creator; });
    });
    vendorType.querySelectorAll('[data-creator-only]').forEach(function (field) {
      field.hidden = !creator;
      field.querySelectorAll('input').forEach(function (input) { input.disabled = !creator; input.required = creator; });
    });
    var nameLabel = vendorType.querySelector('[data-name-label]');
    if (nameLabel) nameLabel.textContent = type === 'artisan' ? 'Artisan, group or store name' : 'Brand or store name';
    var emailLabel = vendorType.querySelector('[data-email-label]');
    if (emailLabel) emailLabel.textContent = creator ? 'Email address' : 'Business email';
    var terms = vendorType.querySelector('[data-terms-copy]');
    if (terms) terms.textContent = 'I agree to the';
    var submit = vendorType.querySelector('[data-submit-copy]');
    if (submit) submit.textContent = 'Create account';
    var loginLink = document.querySelector('[data-register-login]');
    if (loginLink) loginLink.href = creator ? '/conscious/creator/login' : '/conscious/partner/login';
    var eyebrow = document.querySelector('[data-register-eyebrow]');
    if (eyebrow) eyebrow.textContent = creator ? 'Creator registration' : 'Partner registration';
    var heading = document.querySelector('[data-register-heading]');
    if (heading) heading.textContent = 'Join Conscious India';
    var intro = document.querySelector('[data-register-intro]');
    if (intro) intro.textContent = 'Create your account in two short steps.';
    var storyTitle = document.querySelector('.vv-story-copy h1');
    if (storyTitle) storyTitle.textContent = creator ? 'Share stories that matter.' : 'Make more of what you love.';
    var storyIntro = document.querySelector('.vv-story-copy > p');
    if (storyIntro) storyIntro.textContent = creator
      ? 'Connect with Indian makers and conscious brands.'
      : 'One place for your craft, customers and orders.';
    var storyPoints = document.querySelectorAll('.vv-story-points article');
    var pointContent = creator
      ? [
          ['fa-solid fa-user-shield', 'Verified profile', ''],
          ['fa-solid fa-handshake', 'Brand partnerships', ''],
          ['fa-solid fa-chart-line', 'Clear earnings', '']
        ]
      : [
          ['fa-solid fa-user-shield', 'Verified seller', ''],
          ['fa-solid fa-truck-fast', 'Your delivery, your way', ''],
          ['fa-solid fa-chart-line', 'One simple workspace', '']
        ];
    storyPoints.forEach(function (point, index) {
      var content = pointContent[index];
      if (!content) return;
      var icon = point.querySelector('i'); if (icon) icon.className = content[0];
      var title = point.querySelector('b'); if (title) title.textContent = content[1];
      var detail = point.querySelector('small'); if (detail) detail.textContent = content[2];
    });
    var studioLabel = document.querySelector('.vv-auth-logo small');
    if (studioLabel) studioLabel.textContent = creator ? 'Creator Studio' : 'Vendor Studio';
    var stepTwoTitle = vendorType.querySelector('[data-step-two-title]');
    if (stepTwoTitle) stepTwoTitle.textContent = creator ? 'Choose your creator handle' : 'Name your store';
    var stepTwoDescription = vendorType.querySelector('[data-step-two-description]');
    if (stepTwoDescription) stepTwoDescription.textContent = creator ? 'This appears on your public creator profile.' : 'This appears on your storefront.';
    var stepTwoIcon = vendorType.querySelector('[data-step-two-icon]');
    if (stepTwoIcon) stepTwoIcon.className = creator ? 'fa-solid fa-at' : 'fa-solid fa-store';
    document.title = creator ? 'Create creator account — Conscious India' : 'Create vendor account — Conscious India';
    clearRegisterErrors();
  }
  if (vendorType) {
    vendorType.querySelector('[data-register-next]').addEventListener('click', function () { if (validateRegisterStep(1)) registerStep(2); });
    vendorType.querySelector('[data-register-back]').addEventListener('click', function () { registerStep(1); });
    vendorType.querySelectorAll('[name="registration_type"]').forEach(function (input) {
      input.addEventListener('change', function () { setRegisterType(input.value); });
    });
    var requestedType = new URLSearchParams(location.search).get('type');
    if (['artisan', 'brand', 'influencer', 'creator'].includes(requestedType)) {
      if (requestedType === 'creator') requestedType = 'influencer';
      vendorType.querySelector('[name="registration_type"][value="' + requestedType + '"]').checked = true;
      setRegisterType(requestedType);
    }
  }
  var login = document.querySelector('[data-vendor-login]');
  if (login) login.addEventListener('submit', async function (event) {
    event.preventDefault(); event.stopImmediatePropagation();
    if (!login.checkValidity()) { login.reportValidity(); return; }
    busy(login, true);
    try {
      var result = await post('login.php', {
        email: login.elements.email.value.trim(), password: login.elements.password.value,
        remember: Boolean(login.elements.remember_device.checked)
      });
      if (result.csrf_token) sessionStorage.setItem('ci_vendor_csrf_token', result.csrf_token);
      location.assign('/conscious/partner/dashboard');
    } catch (error) { show(login, error.message); busy(login, false); }
  }, true);
  var register = document.querySelector('[data-vendor-register]');
  if (register) register.addEventListener('submit', async function (event) {
    event.preventDefault(); event.stopImmediatePropagation();
    if (!validateRegisterStep(1)) { registerStep(1); return; }
    if (!validateRegisterStep(2)) { registerStep(2); return; }
    busy(register, true);
    try {
      var type = register.elements.registration_type.value;
      if (type === 'influencer') {
        var creatorResult = await post('/conscious/api/influencer/auth/register.php', {
          full_name: register.elements.owner_name.value.trim(),
          email: register.elements.email.value.trim(),
          phone: register.elements.phone.value.trim(),
          handle: register.elements.handle.value.trim(),
          password: register.elements.password.value,
          terms: Boolean(register.elements.terms.checked)
        });
        sessionStorage.setItem('ci_influencer_pending_email', creatorResult.email);
        location.assign('/conscious/creator/verify');
      } else {
        var result = await post('register.php', {
          vendor_type: type,
          owner_name: register.elements.owner_name.value.trim(),
          display_name: register.elements.display_name.value.trim(),
          email: register.elements.email.value.trim(),
          phone: register.elements.phone.value.trim(),
          password: register.elements.password.value,
          terms: Boolean(register.elements.terms.checked)
        });
        sessionStorage.setItem('ci_vendor_pending_email', result.email);
        location.assign('/conscious/partner/verify-contact');
      }
    } catch (error) {
      show(register, error.message);
      if (error.fields) Object.entries(error.fields).forEach(function (entry) {
        var name = entry[0] === 'full_name' ? 'owner_name' : entry[0] === 'mobile' ? 'phone' : entry[0] === 'vendor_type' ? 'registration_type' : entry[0];
        registerError(name, String(entry[1]));
      });
      if (error.fields && Object.keys(error.fields).some(function (name) { return ['full_name','mobile','vendor_type','owner_name','phone','email','password','confirm_password','registration_type'].includes(name); })) registerStep(1);
      busy(register, false);
    }
  }, true);
  var verify = document.querySelector('[data-vendor-verify]');
  if (verify) {
    var email = sessionStorage.getItem('ci_vendor_pending_email');
    var demoCallout = verify.querySelector('.vc-callout');
    if (email && demoCallout) demoCallout.textContent = demoCallout.textContent.includes('Local test mode:')
      ? 'Verifying ' + email + '. The random code is in api/data/mail-outbox; no email was sent.'
      : 'Enter the code sent to ' + email + '.';
    if (email) verify.addEventListener('submit', async function (event) {
      event.preventDefault(); event.stopImmediatePropagation();
      if (!verify.checkValidity()) { verify.reportValidity(); return; }
      busy(verify, true);
      try {
        var result = await post('verify-contact.php', { email: email, code: verify.elements.code.value.trim() });
        sessionStorage.removeItem('ci_vendor_pending_email');
        if (result.csrf_token) sessionStorage.setItem('ci_vendor_csrf_token', result.csrf_token);
        location.assign('/conscious/partner/onboarding');
      } catch (error) { show(verify, error.message); busy(verify, false); }
    }, true);
    if (!email && demoCallout) demoCallout.textContent = 'Start from registration to receive a verification code.';
    var resend=verify.querySelector('[data-resend-code]');if(resend)resend.addEventListener('click',async function(){if(!email){show(verify,'Start from registration to resend a code.');return;}resend.disabled=true;try{var result=await post('resend-code.php',{email:email});show(verify,result.message||'If an unverified account exists, a new code has been sent.');}catch(error){show(verify,error.message);}finally{resend.disabled=false;}});
  }
  var recovery=document.querySelector('[data-vendor-recovery]');if(recovery)recovery.addEventListener('submit',async function(event){event.preventDefault();if(!recovery.checkValidity()){recovery.reportValidity();return;}busy(recovery,true);try{var result=await post('/conscious/api/auth/forgot-password.php',{email:recovery.elements.email.value.trim(),account_type:'vendor'});show(recovery,result.message||'If the account exists, a recovery link has been sent.');}catch(error){show(recovery,error.message);}finally{busy(recovery,false);}});
  var reset=document.querySelector('[data-vendor-reset]');if(reset)reset.addEventListener('submit',async function(event){event.preventDefault();if(!reset.checkValidity()){reset.reportValidity();return;}if(reset.elements.password.value!==reset.elements.confirm_password.value){show(reset,'Passwords do not match.');return;}var token=new URLSearchParams(location.search).get('token');if(!token){show(reset,'Open the secure recovery link from your email.');return;}busy(reset,true);try{var result=await post('/conscious/api/auth/reset-password.php',{token:token,password:reset.elements.password.value,password_confirmation:reset.elements.confirm_password.value});show(reset,result.message||'Password changed.');location.assign('/conscious/partner/login');}catch(error){show(reset,error.message);busy(reset,false);}});
  var status=document.querySelector('[data-vendor-status]');if(status){var refresh=status.querySelector('[data-refresh-status]');var loadStatus=async function(){try{var response=await fetch('/conscious/api/vendor/onboarding.php',{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});var data=await response.json();if(!response.ok||!data.success)throw Error(data.message||'Sign in to view your application.');status.querySelector('[data-status-type]').textContent=data.vendor.vendor_type||'vendor';status.querySelector('[data-status-reference]').textContent=data.application.reference||'—';status.querySelector('[data-status-date]').textContent=data.application.submitted_at||'Not submitted';status.querySelector('[data-status-current]').textContent=String(data.application.status||'draft').replaceAll('_',' ');}catch(error){status.querySelector('[data-status-reference]').textContent=error.message;}};if(refresh)refresh.addEventListener('click',loadStatus);loadStatus();}
})();
