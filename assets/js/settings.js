(function () {
'use strict';

function base(p) {
return new URL(p, window.location.href).href;
}

var URL_PROFILE = base('../api/user/profile.php');
var URL_PREFS = base('../api/user/preferences.php');
var URL_EXPORT = base('../api/user/data-export.php');
var URL_DEACT = base('../api/user/account-deactivate.php');
var URL_DELETE = base('../api/user/account-delete.php');
var URL_REVOKE = base('../api/user/sessions-revoke-all.php');

function getCsrf() {
var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
return m ? decodeURIComponent(m[1]) : '';
}

function api(method, url, body) {
var headers = { 'Accept': 'application/json' };
var opts = { method: method, credentials: 'include', headers: headers };
if (body !== undefined) {
headers['Content-Type'] = 'application/json';
headers['X-CSRF-Token'] = getCsrf();
opts.body = JSON.stringify(body);
}
return fetch(url, opts).then(function (r) {
return r.json().catch(function () { return { success: false, message: 'Invalid JSON response.' }; });
});
}

function setText(id, v) {
var el = document.getElementById(id);
if (el) el.textContent = v;
}

function showBanner(kind, msg) {
var el = document.getElementById('ci-settings-banner');
if (!el) return;
var colors = {
success: { bg: '#ECFDF5', fg: '#065F46', border: '#A7F3D0' },
error: { bg: '#FEF2F2', fg: '#991B1B', border: '#FECACA' },
info: { bg: '#EFF6FF', fg: '#1E40AF', border: '#BFDBFE' }
};
var c = colors[kind] || colors.info;
el.style.background = c.bg;
el.style.color = c.fg;
el.style.border = '1px solid ' + c.border;
el.textContent = msg;
el.style.display = 'block';
}

function hideBanner() {
var el = document.getElementById('ci-settings-banner');
if (el) el.style.display = 'none';
}

// ── Sidebar user ──
function loadSidebarUser() {
api('GET', URL_PROFILE).then(function (res) {
if (!res || res.success === false || !res.user) return;
var u = res.user;
var first = u.first_name || '';
var last = u.last_name || '';
var full = (first + ' ' + last).trim() || u.email || 'Customer';
setText('ci-sidebar-user-name', full);
setText('ci-sidebar-user-email', u.email || '—');
var av = document.getElementById('ci-sidebar-avatar');
if (av) av.textContent = (first.charAt(0) || u.email || '?').toUpperCase();
}).catch(function () {});
}

// ── Data export ──
function renderExportStatus(job) {
var el = document.getElementById('export-status');
var btn = document.getElementById('btn-export-request');
if (!el) return;

if (!job) {
el.textContent = 'No export requested yet.';
el.style.color = 'var(--text-muted)';
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-download"></i> Request Export';
btn.onclick = requestExport;
}
return;
}

var s = job.status;
if (s === 'queued' || s === 'processing') {
el.textContent = '⏳ Export ' + s + '… we\'ll email you when ready.';
el.style.color = '#1E40AF';
if (btn) {
btn.disabled = true;
btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ' + s;
}
} else if (s === 'ready') {
    var size = job.sizeBytes ? (Math.round(job.sizeBytes / 1024) + ' KB') : '';
    el.textContent = '✓ Ready ' + size;
    el.style.color = '#065F46';

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-download"></i> Download';
        btn.onclick = function () {
            if (job.downloadUrl) window.location.href = job.downloadUrl;
        };
    }

    // Show a small "request new" link below
    var newLinkId = 'ci-export-new-link';
    var existing = document.getElementById(newLinkId);
    if (existing) existing.remove();
    var link = document.createElement('a');
    link.id = newLinkId;
    link.href = '#';
    link.textContent = 'Request a new export';
    link.style.cssText = 'display:inline-block;margin-left:10px;font-size:.8125rem;color:var(--text-muted);text-decoration:underline;';
    link.addEventListener('click', function (e) {
        e.preventDefault();
        link.remove();
        requestExport();
    });
    el.parentNode.appendChild(link);
} else if (s === 'failed') {
el.textContent = '✗ Export failed. ' + (job.error || '');
el.style.color = '#991B1B';
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-download"></i> Retry Export';
btn.onclick = requestExport;
}
} else {
el.textContent = 'Status: ' + s;
el.style.color = 'var(--text-muted)';
if (btn) {
btn.disabled = false;
btn.onclick = requestExport;
}
}
}

function loadExportStatus() {
api('GET', URL_EXPORT).then(function (res) {
if (!res || res.success === false) { renderExportStatus(null); return; }
renderExportStatus(res.job || null);
}).catch(function () { renderExportStatus(null); });
}

function requestExport() {
var btn = document.getElementById('btn-export-request');
if (btn) {
btn.disabled = true;
btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Requesting…';
}

api('POST', URL_EXPORT, {}).then(function (res) {
if (res && res.success) {
showBanner('success', res.message || 'Export queued.');
loadExportStatus();
} else {
showBanner('error', (res && res.message) || 'Could not start export.');
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-download"></i> Request Export';
}
}
}).catch(function () {
showBanner('error', 'Network error.');
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-download"></i> Request Export';
}
});
}

// ── Revoke all sessions ──
function revokeAllSessions() {
if (!confirm('Sign out of ALL devices? You will need to sign in again on this device too.')) return;

var btn = document.getElementById('btn-revoke-sessions');
if (btn) {
btn.disabled = true;
btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Working…';
}

api('POST', URL_REVOKE, {}).then(function (res) {
if (res && res.success) {
showBanner('success', res.message || 'All sessions revoked. Redirecting to login…');
setTimeout(function () { window.location.href = '../login.html'; }, 1500);
} else {
showBanner('error', (res && res.message) || 'Failed to revoke sessions.');
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-laptop"></i> Log out from all devices';
}
}
}).catch(function () {
showBanner('error', 'Network error.');
if (btn) {
btn.disabled = false;
btn.innerHTML = '<i class="fa-solid fa-laptop"></i> Log out from all devices';
}
});
}

// ── Deactivate ──
function submitDeactivate() {
var reason = document.getElementById('deactivate-reason').value.trim();
var confirmW = document.getElementById('deactivate-confirm').value.trim();
var errEl = document.getElementById('deactivate-error');
var btn = document.getElementById('deactivate-submit');

errEl.style.display = 'none';

if (confirmW !== 'DEACTIVATE') {
errEl.textContent = 'You must type DEACTIVATE exactly.';
errEl.style.display = 'block';
return;
}

btn.disabled = true;
btn.textContent = 'Working…';

api('POST', URL_DEACT, { confirm: 'DEACTIVATE', reason: reason }).then(function (res) {
if (res && res.success) {
showBanner('success', res.message || 'Account deactivated. Redirecting…');
setTimeout(function () { window.location.href = '../login.html'; }, 1500);
} else {
errEl.textContent = (res && res.message) || 'Failed.';
errEl.style.display = 'block';
btn.disabled = false;
btn.textContent = 'Deactivate';
}
}).catch(function () {
errEl.textContent = 'Network error.';
errEl.style.display = 'block';
btn.disabled = false;
btn.textContent = 'Deactivate';
});
}

// ── Delete ──
function submitDelete() {
var reason = document.getElementById('delete-reason').value.trim();
var confirmW = document.getElementById('delete-confirm').value.trim();
var errEl = document.getElementById('delete-error');
var btn = document.getElementById('delete-submit');

errEl.style.display = 'none';

if (confirmW !== 'DELETE MY ACCOUNT') {
errEl.textContent = 'You must type "DELETE MY ACCOUNT" exactly.';
errEl.style.display = 'block';
return;
}

btn.disabled = true;
btn.textContent = 'Working…';

api('POST', URL_DELETE, { confirm: 'DELETE MY ACCOUNT', reason: reason }).then(function (res) {
if (res && res.success) {
var msg = res.message || 'Account scheduled for deletion.';
if (res.scheduledFor) msg += ' Scheduled for: ' + res.scheduledFor;
showBanner('success', msg + ' Signing you out…');
setTimeout(function () { window.location.href = '../login.html'; }, 2500);
} else {
errEl.textContent = (res && res.message) || 'Failed.';
errEl.style.display = 'block';
btn.disabled = false;
btn.textContent = 'Delete Permanently';
}
}).catch(function () {
errEl.textContent = 'Network error.';
errEl.style.display = 'block';
btn.disabled = false;
btn.textContent = 'Delete Permanently';
});
}

// ── Safe event binding helper ──
function on(id, event, fn) {
var el = document.getElementById(id);
if (el) el.addEventListener(event, fn);
}

function init() {
loadSidebarUser();
loadExportStatus();

on('btn-export-request', 'click', requestExport);
on('btn-revoke-sessions', 'click', revokeAllSessions);

on('btn-deactivate', 'click', function () {
document.getElementById('deactivate-reason').value = '';
document.getElementById('deactivate-confirm').value = '';
document.getElementById('deactivate-error').style.display = 'none';
var m = new bootstrap.Modal(document.getElementById('modal-deactivate'));
m.show();
});
on('deactivate-submit', 'click', submitDeactivate);

on('btn-delete', 'click', function () {
document.getElementById('delete-reason').value = '';
document.getElementById('delete-confirm').value = '';
document.getElementById('delete-error').style.display = 'none';
var m = new bootstrap.Modal(document.getElementById('modal-delete'));
m.show();
});
on('delete-submit', 'click', submitDelete);
}

if (document.readyState === 'loading') {
document.addEventListener('DOMContentLoaded', init);
} else {
init();
}
})();