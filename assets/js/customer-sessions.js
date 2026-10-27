/* Conscious India — Active sessions: list, current-device badge, logout. */
'use strict';

(function () {
    var API = '../api/user/sessions.php';

    function csrf() {
        var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function fmtDate(v) {
        if (!v) return '—';
        var d = new Date(String(v).replace(' ', 'T') + (String(v).indexOf('Z') === -1 ? 'Z' : ''));
        return isNaN(d.getTime()) ? v : d.toLocaleString('en-IN', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function relative(v) {
        if (!v) return '—';
        var d = new Date(String(v).replace(' ', 'T') + (String(v).indexOf('Z') === -1 ? 'Z' : ''));
        if (isNaN(d.getTime())) return v;
        var sec = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));
        if (sec < 60) return 'Just now';
        if (sec < 3600) return Math.floor(sec / 60) + ' min ago';
        if (sec < 86400) return Math.floor(sec / 3600) + ' hour' + (Math.floor(sec / 3600) > 1 ? 's' : '') + ' ago';
        if (sec < 604800) return Math.floor(sec / 86400) + ' day' + (Math.floor(sec / 86400) > 1 ? 's' : '') + ' ago';
        return fmtDate(v);
    }

    function loadSessions() {
        var box = document.getElementById('activeSessionsList');
        if (!box) return;

        box.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);">Loading…</div>';

        fetch(API, { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');

                var subtitle = document.getElementById('sessionsSubtitle');
                if (subtitle) subtitle.textContent = d.total + ' active session' + (d.total === 1 ? '' : 's');

                if (!d.sessions.length) {
                    box.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);">No active sessions.</div>';
                    return;
                }

                box.innerHTML = d.sessions.map(function (s) {
                    var badge = s.isCurrent
                        ? '<span style="background:#10B981;color:#fff;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600;margin-left:8px;">CURRENT DEVICE</span>'
                        : '';

                    var logoutBtn = s.isCurrent
                        ? '<button class="bc-btn bc-btn-outline bc-btn-sm" style="color:#E53E3E;border-color:#E53E3E;" onclick="logoutDevice(\'' + s.id + '\', true)"><i class="fa-solid fa-right-from-bracket"></i> Log Out This Device</button>'
                        : '<button class="bc-btn bc-btn-outline bc-btn-sm" style="color:#E53E3E;border-color:#E53E3E;" onclick="logoutDevice(\'' + s.id + '\', false)"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>';

                    return ''
                        + '<div style="padding:14px 16px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">'
                        +   '<div style="flex:1;min-width:0;">'
                        +     '<div style="font-weight:600;font-size:.95rem;color:var(--text-primary);">'
                        +       '<i class="fa-solid fa-desktop" style="color:var(--saffron);margin-right:6px;"></i>'
                        +       esc(s.deviceLabel)
                        +       badge
                        +     '</div>'
                        +     '<div style="color:var(--text-muted);font-size:.8rem;margin-top:6px;">'
                        +       (s.ipAddress ? 'IP: ' + esc(s.ipAddress) + ' · ' : '')
                        +       'Last active: ' + esc(relative(s.lastSeenAt))
                        +     '</div>'
                        +     '<div style="color:var(--text-muted);font-size:.75rem;margin-top:2px;">'
                        +       'Signed in: ' + esc(fmtDate(s.createdAt))
                        +     '</div>'
                        +   '</div>'
                        +   '<div>' + logoutBtn + '</div>'
                        + '</div>';
                }).join('');
            })
            .catch(function () {
                box.innerHTML = '<div style="padding:16px;color:#E53E3E;text-align:center;">Could not load sessions.</div>';
            });
    }

    window.logoutDevice = function (sessionId, isCurrent) {
        var msg = isCurrent
            ? 'Log out THIS device? You will need to sign in again.'
            : 'Log out this device?';
        if (!confirm(msg)) return;

        fetch(API + '?id=' + encodeURIComponent(sessionId), {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-Token': csrf() }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            if (d.wasCurrent) {
                window.location.href = '../login.html';
            } else {
                loadSessions();
            }
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    window.logoutAllOtherDevices = function () {
        if (!confirm('Log out all other devices? Only this device will stay signed in.')) return;

        fetch(API + '?all_others=1', {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-Token': csrf() }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            alert(d.message);
            loadSessions();
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadSessions);
    } else {
        loadSessions();
    }
})();