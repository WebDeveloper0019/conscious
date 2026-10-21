(function () {
  'use strict';

  console.log("🚀 auth.js loaded");

  var root = '../../../../api/influencer/auth/';
  var messageEl = document.getElementById('if-auth-message');

  function showMessage(msg, isError) {
    if (!messageEl) return;
    messageEl.textContent = msg;
    messageEl.style.color = isError ? '#c62828' : '#2e7d32';
    messageEl.hidden = false;
  }

  async function post(path, payload) {
    console.log("📡 Sending to:", root + path, payload);
    var response = await fetch(root + path, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    var text = await response.text();
    console.log("📥 Response:", response.status, text);

    var data = {};
    try {
      data = JSON.parse(text);
    } catch (e) {
      throw new Error('Server error (invalid JSON). Check PHP logs.');
    }

    if (!response.ok) {
      throw new Error(data.message || 'Request failed with status ' + response.status);
    }
    return data;
  }

  // --- LOGIN FORM ---
  var loginForm = document.getElementById('if-login');
  if (loginForm) {
    console.log("✅ Login form found");
    
    loginForm.addEventListener('submit', async function (event) {
      event.preventDefault(); // Stops the page from blinking/reloading
      console.log("🖱️ Login button clicked");

      if (!loginForm.checkValidity()) {
        loginForm.reportValidity();
        return;
      }

      var btn = loginForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      if (messageEl) messageEl.hidden = true;

      var payload = {
        email: loginForm.elements.email.value.trim(),
        password: loginForm.elements.password.value,
        remember: loginForm.elements.remember ? loginForm.elements.remember.checked : false
      };

      try {
        var result = await post('login.php', payload);
        
        // Success!
        if (result.csrf_token) {
          sessionStorage.setItem('ci_influencer_csrf', result.csrf_token);
        }
        console.log("🎉 Login successful, redirecting...");
        window.location.href = '../index.php';

      } catch (error) {
        console.error("❌ Login failed:", error.message);
        showMessage(error.message, true);
        if (btn) btn.disabled = false;
      }
    });
  } else {
    console.warn("⚠️ Login form not found on this page.");
  }

  // --- REGISTER FORM ---
  var registerForm = document.getElementById('if-register');
  if (registerForm) {
    registerForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (!registerForm.checkValidity()) {
        registerForm.reportValidity();
        return;
      }

      var password = registerForm.elements.password.value;
      if (password !== registerForm.elements.confirm_password.value) {
        showMessage('Passwords do not match.', true);
        return;
      }

      var btn = registerForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      if (messageEl) messageEl.hidden = true;

      var payload = {
        full_name: registerForm.elements.full_name.value.trim(),
        email: registerForm.elements.email.value.trim(),
        phone: registerForm.elements.phone.value.trim(),
        handle: registerForm.elements.handle.value.trim(),
        password: password,
        terms: registerForm.elements.terms.checked
      };

      try {
        var result = await post('register.php', payload);
        
        if (result.skip_verify && result.otp_code) {
          // Auto-verify if OTP is disabled in backend
          var v = await post('verify.php', { email: result.email, code: result.otp_code });
          if (v.csrf_token) sessionStorage.setItem('ci_influencer_csrf', v.csrf_token);
          window.location.href = '../index.php#application';
        } else {
          sessionStorage.setItem('ci_influencer_pending_email', result.email);
          window.location.href = 'verify.php';
        }
      } catch (error) {
        showMessage(error.message, true);
        if (btn) btn.disabled = false;
      }
    });
  }

})();