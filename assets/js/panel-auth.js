/**
 * Conscious Indi — Shared panel authentication UI
 * Loads the authenticated user from the API and renders real account data.
 */
(function () {
  'use strict';

  var TOKEN_KEY = 'ci_token';
  var USER_KEY = 'ci_user';

  function getStoredUser() {
    try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null'); }
    catch (e) { return null; }
  }

  function loginUrl() { return '../login'; }

  function nameOf(user) {
    var first = String(user && user.firstName || '').trim();
    var last = String(user && user.lastName || '').trim();
    return (first + ' ' + last).trim() || (user && user.email) || 'User';
  }

  function initialsOf(user) {
    var first = String(user && user.firstName || '').trim();
    var last = String(user && user.lastName || '').trim();
    if (first && last) return (first.charAt(0) + last.charAt(0)).toUpperCase();
    return (first || (user && user.email) || 'U').charAt(0).toUpperCase();
  }

  function renderUser(user) {
    if (!user) return;
    var name = nameOf(user);
    var first = String(user.firstName || '').trim();
    var email = String(user.email || '').trim();
    var mobile = String(user.mobile || '').trim();

    document.querySelectorAll('.ci-sidebar-user-name, .ci-profile-trigger-name, [data-user-name]').forEach(function (el) {
      el.textContent = name;
    });
    document.querySelectorAll('.ci-sidebar-user-email, [data-user-email]').forEach(function (el) {
      if (email) el.textContent = email;
    });
    document.querySelectorAll('.ci-sidebar-avatar, .ci-profile-trigger-avatar, [data-user-initials]').forEach(function (el) {
      el.textContent = initialsOf(user);
    });

    document.querySelectorAll('[data-user-first-name]').forEach(function (el) { el.textContent = first || 'User'; });
    document.querySelectorAll('[data-user-full-name]').forEach(function (el) { el.textContent = name; });

    document.querySelectorAll('input[data-user-field="firstName"]').forEach(function (el) { el.value = user.firstName || ''; });
    document.querySelectorAll('input[data-user-field="lastName"]').forEach(function (el) { el.value = user.lastName || ''; });
    document.querySelectorAll('input[data-user-field="email"]').forEach(function (el) { el.value = email; });
    document.querySelectorAll('input[data-user-field="mobile"]').forEach(function (el) { el.value = mobile; });

    // Backward compatibility for remaining demo text on panel pages.
    document.querySelectorAll('*').forEach(function (el) {
      if (el.children.length) return;
      var text = el.textContent || '';
      if (/Welcome back,\s*Rahul!/i.test(text)) {
        el.textContent = text.replace(/Welcome back,\s*Rahul!/i, 'Welcome back, ' + (first || 'there') + '!');
      } else if (/^Rahul Sharma$/.test(text.trim()) && el.classList.contains('ci-sidebar-user-name')) {
        el.textContent = name;
      }
    });
  }

  async function loadCurrentUser(token) {
    var response = await fetch('../api/auth/me.php', {
      method: 'GET',
      headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
      cache: 'no-store'
    });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok || !data.success || !data.user) throw new Error(data.message || 'Session expired');
    localStorage.setItem(USER_KEY, JSON.stringify(data.user));
    return data.user;
  }

  function logout(e) {
    if (e) e.preventDefault();
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    try { sessionStorage.removeItem(TOKEN_KEY); sessionStorage.removeItem(USER_KEY); } catch (_) {}
    window.location.replace(loginUrl());
  }

  function protectPanel() {
    var token = localStorage.getItem(TOKEN_KEY);
    if (!token) {
      window.location.replace(loginUrl());
      return false;
    }
    return true;
  }

  async function init() {
    if (!protectPanel()) return;

    var token = localStorage.getItem(TOKEN_KEY);
    var user = getStoredUser();
    if (user) renderUser(user);

    try {
      user = await loadCurrentUser(token);
      renderUser(user);
    } catch (e) {
      // A stale/invalid token must never show old demo or another user's data.
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      window.location.replace(loginUrl());
      return;
    }

    document.querySelectorAll('.ci-profile-menu-item').forEach(function (link) {
      if ((link.textContent || '').trim().toLowerCase().includes('log out')) {
        link.classList.add('ci-logout-link');
        link.addEventListener('click', logout);
      }
    });

    document.addEventListener('click', function (e) {
      var link = e.target.closest && e.target.closest('.ci-logout-link');
      if (link) logout(e);
    }, true);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
