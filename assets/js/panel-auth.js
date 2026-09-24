/**
 * Conscious Indi — Shared panel authentication UI
 * Handles logout and renders the currently signed-in user's name.
 */
(function () {
  'use strict';

  var TOKEN_KEY = 'ci_token';
  var USER_KEY = 'ci_user';

  function getUser() {
    try {
      return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
    } catch (e) {
      return null;
    }
  }

  function loginUrl() {
    return '../login';
  }

  function setTextUserName(el, user) {
    var first = String(user.firstName || '').trim();
    var last = String(user.lastName || '').trim();
    var name = (first + ' ' + last).trim() || user.email || 'User';
    var email = user.email || '';
    var text = el.textContent || '';

    if (/Rahul Sharma/i.test(text)) el.textContent = name;
    else if (/Welcome back,\s*Rahul!/i.test(text)) el.textContent = text.replace(/Welcome back,\s*Rahul!/i, 'Welcome back, ' + first + '!');
    else if (el.hasAttribute('data-user-name')) el.textContent = name;

    if (el.classList.contains('ci-sidebar-user-email') && email) el.textContent = email;
    if (el.classList.contains('ci-sidebar-avatar')) el.textContent = (first || name || 'U').charAt(0).toUpperCase();
  }

  function renderUser(user) {
    if (!user) return;
    document.querySelectorAll('.ci-sidebar-user-name, .ci-profile-trigger-name, [data-user-name]').forEach(function (el) {
      setTextUserName(el, user);
    });
    document.querySelectorAll('.ci-sidebar-user-email').forEach(function (el) {
      if (user.email) el.textContent = user.email;
    });
    document.querySelectorAll('.ci-sidebar-avatar').forEach(function (el) {
      var first = String(user.firstName || '').trim();
      var name = (first || user.email || 'U');
      el.textContent = name.charAt(0).toUpperCase();
    });

    // Replace demo greeting text without touching unrelated content.
    document.querySelectorAll('*').forEach(function (el) {
      if (el.children.length) return;
      var text = el.textContent || '';
      if (/Welcome back,\s*Rahul!/i.test(text)) {
        var first = String(user.firstName || '').trim();
        el.textContent = text.replace(/Welcome back,\s*Rahul!/i, 'Welcome back, ' + (first || 'there') + '!');
      }
    });

    // Profile forms/labels that still contain the old demo identity.
    document.querySelectorAll('input, textarea').forEach(function (el) {
      if (typeof el.value !== 'string') return;
      if (/Rahul Sharma/i.test(el.value)) {
        el.value = ((user.firstName || '') + ' ' + (user.lastName || '')).trim();
      } else if (/rahul@example\.com/i.test(el.value)) {
        el.value = user.email || '';
      }
    });
  }

  function logout(e) {
    if (e) e.preventDefault();
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    // Also clear any accidental session copy used by older builds.
    try { sessionStorage.removeItem(TOKEN_KEY); sessionStorage.removeItem(USER_KEY); } catch (_) {}
    window.location.replace(loginUrl());
  }

  function protectPanel() {
    if (!localStorage.getItem(TOKEN_KEY)) {
      window.location.replace(loginUrl());
      return false;
    }
    return true;
  }

  function init() {
    if (!protectPanel()) return;

    var user = getUser();
    if (user) renderUser(user);

    document.querySelectorAll('.ci-profile-menu-item').forEach(function (link) {
      if ((link.textContent || '').trim().toLowerCase().includes('log out')) {
        link.classList.add('ci-logout-link');
        link.addEventListener('click', logout);
      }
    });

    // Safety net for dynamically rendered logout links.
    document.addEventListener('click', function (e) {
      var link = e.target.closest && e.target.closest('.ci-logout-link');
      if (link) logout(e);
    }, true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
