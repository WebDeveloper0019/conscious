(function () {
  'use strict';
  var logout = document.querySelector('.ac-side-foot a[href="auth/login.php"]');
  fetch('../../../api/admin/auth/me.php', { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' })
    .then(function (response) { if (!response.ok) throw new Error('Session expired'); return response.json(); })
    .then(function (result) {
      if (result.csrf_token) sessionStorage.setItem('ci_admin_csrf_token', result.csrf_token);
      var admin = result.admin || {};
      var name = String(admin.full_name || 'Administrator');
      var label = document.querySelector('.ci-sidebar-user-name');
      var top = document.querySelector('.ac-profile b');
      var avatar = document.querySelector('.ci-sidebar-avatar');
      if (label) label.textContent = name;
      if (top) top.textContent = name;
      if (avatar) avatar.textContent = name.split(/\s+/).slice(0, 2).map(function (part) { return part.charAt(0); }).join('').toUpperCase();
    })
    .catch(function () { location.replace('auth/login.php'); });
  if (logout) logout.addEventListener('click', async function (event) {
    event.preventDefault();
    try {
      await fetch('../../../api/admin/auth/logout.php', {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-Token': sessionStorage.getItem('ci_admin_csrf_token') || '' }
      });
    } finally {
      sessionStorage.removeItem('ci_admin_csrf_token');
      sessionStorage.removeItem('ci_admin_mfa_challenge');
      location.replace('auth/login.php');
    }
  });
})();
