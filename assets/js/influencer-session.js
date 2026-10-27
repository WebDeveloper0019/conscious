/* Conscious Indi — Influencer session helper
   Load BEFORE influencer-panel.js on every dashboard page.
   Stores { id, at } in localStorage and exposes helpers. */
(function () {
  'use strict';

  var REGISTRY_KEY = 'conscious-influencer-demo-registry';
  var SESSION_KEY  = 'conscious-influencer-session';

  function readRegistry() {
    try {
      var raw = localStorage.getItem(REGISTRY_KEY);
      if (!raw) return [];
      var parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : (parsed && parsed.data) || [];
    } catch (e) { return []; }
  }

  function writeRegistry(list) {
    var raw = localStorage.getItem(REGISTRY_KEY);
    var version = '2';
    try {
      var p = JSON.parse(raw || 'null');
      if (p && p.__v) version = p.__v;
    } catch (e) {}
    localStorage.setItem(REGISTRY_KEY, JSON.stringify({ __v: version, data: list }));
  }

  function getSession() {
    try { return JSON.parse(localStorage.getItem(SESSION_KEY) || 'null'); }
    catch (e) { return null; }
  }

  function login(id)  { localStorage.setItem(SESSION_KEY, JSON.stringify({ id: id, at: Date.now() })); }
  function logout()   { localStorage.removeItem(SESSION_KEY); }

  function getCurrentInfluencer() {
    var s = getSession();
    if (!s || !s.id) return null;
    var list = readRegistry();
    for (var i = 0; i < list.length; i++) if (list[i].id === s.id) return list[i];
    return null;
  }

  function requireLogin(redirectTo) {
    var me = getCurrentInfluencer();
    if (!me) {
      window.location.replace(redirectTo || '../influencer-login.html');
      return null;
    }
    return me;
  }

  function logoutAndRedirect(redirectTo) {
    logout();
    window.location.replace(redirectTo || '../influencer-login.html');
  }

  function updateInfluencer(id, patch) {
    var list = readRegistry();
    var idx = -1;
    for (var i = 0; i < list.length; i++) if (list[i].id === id) { idx = i; break; }
    if (idx === -1) return null;
    list[idx] = Object.assign({}, list[idx], patch);
    writeRegistry(list);
    return list[idx];
  }

  window.InfluencerSession = {
    readRegistry: readRegistry,
    writeRegistry: writeRegistry,
    getCurrentInfluencer: getCurrentInfluencer,
    requireLogin: requireLogin,
    login: login,
    logout: logout,
    logoutAndRedirect: logoutAndRedirect,
    updateInfluencer: updateInfluencer
  };
})();