/* Conscious Indi — Granular notification preferences. */
'use strict';

(function () {
    var API = '../api/user/notification-preferences.php';

    function csrf() {
        var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    var state = { prefs: {}, meta: null };

    function render() {
        var wrap = document.getElementById('notifPrefsGrid');
        if (!wrap || !state.meta) return;

        var cats = state.meta.categories;
        var chans = state.meta.channels;
        var channelKeys = Object.keys(chans);

        var html = ''
            + '<table style="width:100%;border-collapse:collapse;font-size:.85rem;">'
            +   '<thead>'
            +     '<tr style="background:var(--bg-cream);border-bottom:2px solid var(--border-warm);">'
            +       '<th style="text-align:left;padding:10px 8px;font-weight:700;">Category</th>';
        channelKeys.forEach(function (ch) {
            html += '<th style="text-align:center;padding:10px 6px;font-weight:700;white-space:nowrap;">'
                 + '<i class="fa-solid ' + esc(chans[ch].icon) + '" style="color:var(--saffron);margin-right:4px;"></i>'
                 + esc(chans[ch].label) + '</th>';
        });
        html += '</tr></thead><tbody>';

        Object.keys(cats).forEach(function (cat, idx) {
            var meta = cats[cat];
            var border = idx < Object.keys(cats).length - 1 ? 'border-bottom:1px solid var(--border-warm);' : '';
            html += '<tr style="' + border + '">';
            html +=   '<td style="padding:10px 8px;">'
                   +    '<div style="font-weight:600;color:var(--text-primary);">'
                   +      '<i class="fa-solid ' + esc(meta.icon) + '" style="color:var(--saffron);margin-right:6px;"></i>'
                   +      esc(meta.label)
                   +    '</div>'
                   +    '<div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">' + esc(meta.description) + '</div>'
                   +  '</td>';

            channelKeys.forEach(function (ch) {
                var checked = !!(state.prefs[cat] && state.prefs[cat][ch]);
                var inputId = 'notif-pref-' + cat + '-' + ch;
                html += '<td style="text-align:center;padding:10px 6px;">'
                     + '<input type="checkbox" id="' + esc(inputId) + '" '
                     +   'data-cat="' + esc(cat) + '" data-ch="' + esc(ch) + '" '
                     +   (checked ? 'checked' : '') + ' '
                     +   'style="accent-color:var(--saffron);width:20px;height:20px;cursor:pointer;">'
                     + '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        wrap.innerHTML = html;

        // Bind change
        wrap.querySelectorAll('input[type=checkbox]').forEach(function (el) {
            el.addEventListener('change', function () {
                var cat = el.getAttribute('data-cat');
                var ch  = el.getAttribute('data-ch');
                if (!state.prefs[cat]) state.prefs[cat] = {};
                state.prefs[cat][ch] = el.checked;
            });
        });
    }

    function load() {
        var wrap = document.getElementById('notifPrefsGrid');
        if (!wrap) return;
        wrap.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">Loading…</div>';

        fetch(API, { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');
                state.prefs = d.preferences;
                state.meta  = d.metadata;
                render();
            })
            .catch(function (err) {
                wrap.innerHTML = '<div style="padding:20px;text-align:center;color:#E53E3E;">Could not load preferences.</div>';
            });
    }

    function save() {
        var btn = document.getElementById('notifPrefsSaveBtn');
        var status = document.getElementById('notifPrefsStatus');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…'; }
        if (status) status.textContent = '';

        fetch(API, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify(state.prefs)
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            if (status) {
                status.textContent = '✓ Saved';
                status.style.color = 'var(--green-dark)';
                setTimeout(function () { status.textContent = ''; }, 3000);
            }
        })
        .catch(function (err) {
            if (status) {
                status.textContent = '✗ ' + err.message;
                status.style.color = '#E53E3E';
            }
        })
        .finally(function () {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-save"></i> Save Preferences'; }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        load();
        var btn = document.getElementById('notifPrefsSaveBtn');
        if (btn) btn.addEventListener('click', save);
    });
})();