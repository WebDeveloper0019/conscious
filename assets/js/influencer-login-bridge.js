/* Connects the root influencer-login.html to the real api/influencer/auth/login.php */
(function () {
  'use strict';

  const form = document.getElementById('influencer-login-form');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    e.stopPropagation();
    form.classList.add('was-validated');

    if (!form.checkValidity()) return;

    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;

    const payload = {
      email: form.querySelector('#influencer-login-email').value.trim(),
      password: form.querySelector('#influencer-login-password').value,
      remember: false
    };

    try {
      const response = await fetch('api/influencer/auth/login.php', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Login failed.');
      }

      // Success! Redirect to the dashboard
      window.location.href = 'dashboard.html';

    } catch (error) {
      alert(error.message); // Replace with a toast later
      btn.disabled = false;
    }
  });
})();