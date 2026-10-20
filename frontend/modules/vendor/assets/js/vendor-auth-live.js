(function () {
  'use strict';
  var root = '../../../../api/vendor/auth/';
  function show(form, message) {
    var box = form.querySelector('[data-form-message]');
    if (box) { box.hidden = false; box.textContent = message; }
  }
  async function post(path, payload) {
    var response = await fetch(path.startsWith('../../../../') ? path : root + path, {
      method: 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(data.message || 'Please try again.');
    return data;
  }
  function busy(form, value) {
    var button = form.querySelector('[type="submit"]');
    if (button) button.disabled = value;
  }
  document.querySelectorAll('[data-password-toggle]').forEach(function(button){button.addEventListener('click',function(){var input=document.getElementById(button.dataset.passwordToggle);if(!input)return;var visible=input.type==='password';input.type=visible?'text':'password';button.setAttribute('aria-label',visible?'Hide password':'Show password');button.querySelector('i').className=visible?'fa-regular fa-eye-slash':'fa-regular fa-eye';});});
  document.querySelectorAll('.vv-password-rules').forEach(function(rules){var form=rules.closest('form'),password=form&&form.elements.password;if(!password)return;password.addEventListener('input',function(){var checks={length:password.value.length>=12,upper:/[A-Z]/.test(password.value),lower:/[a-z]/.test(password.value),number:/[0-9]/.test(password.value),special:/[^A-Za-z0-9\s]/.test(password.value)};Object.entries(checks).forEach(function(entry){var item=rules.querySelector('[data-rule="'+entry[0]+'"]');if(item)item.classList.toggle('valid',entry[1]);});});});
  var vendorType=document.querySelector('[data-vendor-register]');if(vendorType){vendorType.querySelectorAll('[name="vendor_type"]').forEach(function(input){input.addEventListener('change',function(){var label=vendorType.querySelector('[data-name-label]');if(label)label.textContent=input.value==='artisan'?'Artisan, group or store name':'Brand or store name';});});}
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
      location.assign(result.redirect || '../dashboard.php');
    } catch (error) { show(login, error.message); busy(login, false); }
  }, true);
  var register = document.querySelector('[data-vendor-register]');
  if (register) register.addEventListener('submit', async function (event) {
    event.preventDefault(); event.stopImmediatePropagation();
    if (!register.checkValidity()) { register.reportValidity(); return; }
    if (register.elements.password.value !== register.elements.confirm_password.value) {
      show(register, 'Passwords do not match.'); return;
    }
    busy(register, true);
    try {
      var result = await post('register.php', {
        vendor_type: register.elements.vendor_type.value,
        owner_name: register.elements.owner_name.value.trim(),
        display_name: register.elements.display_name.value.trim(),
        email: register.elements.email.value.trim(),
        phone: register.elements.phone.value.trim(),
        password: register.elements.password.value,
        terms: Boolean(register.elements.terms.checked)
      });
      sessionStorage.setItem('ci_vendor_pending_email', result.email);
      location.assign(result.redirect || 'verify-contact.php');
    } catch (error) { show(register, error.message); busy(register, false); }
  }, true);
  var verify = document.querySelector('[data-vendor-verify]');
  if (verify) {
    var email = sessionStorage.getItem('ci_vendor_pending_email');
    var demoCallout = verify.querySelector('.vc-callout');
    if (email && demoCallout) demoCallout.textContent = 'Enter the code sent to ' + email + '.';
    if (email) verify.addEventListener('submit', async function (event) {
      event.preventDefault(); event.stopImmediatePropagation();
      if (!verify.checkValidity()) { verify.reportValidity(); return; }
      busy(verify, true);
      try {
        var result = await post('verify-contact.php', { email: email, code: verify.elements.code.value.trim() });
        sessionStorage.removeItem('ci_vendor_pending_email');
        if (result.csrf_token) sessionStorage.setItem('ci_vendor_csrf_token', result.csrf_token);
        location.assign(result.redirect || '../onboarding.php');
      } catch (error) { show(verify, error.message); busy(verify, false); }
    }, true);
    if (!email && demoCallout) demoCallout.textContent = 'Start from registration to receive a verification code.';
    var resend=verify.querySelector('[data-resend-code]');if(resend)resend.addEventListener('click',async function(){if(!email){show(verify,'Start from registration to resend a code.');return;}resend.disabled=true;try{var result=await post('resend-code.php',{email:email});show(verify,result.message||'If an unverified account exists, a new code has been sent.');}catch(error){show(verify,error.message);}finally{resend.disabled=false;}});
  }
  var recovery=document.querySelector('[data-vendor-recovery]');if(recovery)recovery.addEventListener('submit',async function(event){event.preventDefault();if(!recovery.checkValidity()){recovery.reportValidity();return;}busy(recovery,true);try{var result=await post('../../../../api/auth/forgot-password.php',{email:recovery.elements.email.value.trim(),account_type:'vendor'});show(recovery,result.message||'If the account exists, a recovery link has been sent.');}catch(error){show(recovery,error.message);}finally{busy(recovery,false);}});
  var reset=document.querySelector('[data-vendor-reset]');if(reset)reset.addEventListener('submit',async function(event){event.preventDefault();if(!reset.checkValidity()){reset.reportValidity();return;}if(reset.elements.password.value!==reset.elements.confirm_password.value){show(reset,'Passwords do not match.');return;}var token=new URLSearchParams(location.search).get('token');if(!token){show(reset,'Open the secure recovery link from your email.');return;}busy(reset,true);try{var result=await post('../../../../api/auth/reset-password.php',{token:token,password:reset.elements.password.value,password_confirmation:reset.elements.confirm_password.value});show(reset,result.message||'Password changed.');location.assign('login.php');}catch(error){show(reset,error.message);busy(reset,false);}});
  var status=document.querySelector('[data-vendor-status]');if(status){var refresh=status.querySelector('[data-refresh-status]');var loadStatus=async function(){try{var response=await fetch('../../../../api/vendor/onboarding.php',{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});var data=await response.json();if(!response.ok||!data.success)throw Error(data.message||'Sign in to view your application.');status.querySelector('[data-status-type]').textContent=data.vendor.vendor_type||'vendor';status.querySelector('[data-status-reference]').textContent=data.application.reference||'—';status.querySelector('[data-status-date]').textContent=data.application.submitted_at||'Not submitted';status.querySelector('[data-status-current]').textContent=String(data.application.status||'draft').replaceAll('_',' ');}catch(error){status.querySelector('[data-status-reference]').textContent=error.message;}};if(refresh)refresh.addEventListener('click',loadStatus);loadStatus();}
})();
