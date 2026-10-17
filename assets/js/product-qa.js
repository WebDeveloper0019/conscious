/* Conscious Indi — Product Q&A (public view + ask). */
'use strict';

(function () {
    var _path = window.location.pathname;
    var _prefix = /\/customer\/|\/vendor\/|\/admin\/|\/influencer\//.test(_path) ? '../api/' : 'api/';
    var API_PUBLIC = _prefix + 'public/product-questions.php';
    var API_ASK    = _prefix + 'user/product-questions.php';

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
        return isNaN(d.getTime()) ? v : d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function render(q, productId) {
        var answersHtml = q.answers.map(function (a) {
            var badge = a.isOfficial
                ? '<span style="background:var(--saffron-pale);color:var(--saffron-dark);font-size:.7rem;padding:2px 6px;border-radius:8px;font-weight:600;margin-left:6px;">OFFICIAL</span>'
                : '';
            return ''
                + '<div style="margin-top:10px;padding:10px 12px;background:#F9FAFB;border-left:3px solid var(--saffron);border-radius:6px;">'
                +   '<div style="font-size:.8rem;color:var(--text-muted);font-weight:600;">'
                +     esc(a.answeredByName)
                +     badge
                +     ' · ' + esc(fmtDate(a.createdAt))
                +   '</div>'
                +   '<div style="margin-top:6px;">' + esc(a.answerText) + '</div>'
                + '</div>';
        }).join('');

        var noAnswers = !q.answers.length
            ? '<div style="margin-top:8px;font-size:.85rem;color:var(--text-muted);font-style:italic;">Awaiting answer from seller…</div>'
            : '';

        return ''
            + '<div style="padding:16px 0;border-bottom:1px solid var(--border-warm);">'
            +   '<div style="display:flex;justify-content:space-between;gap:12px;">'
            +     '<div style="flex:1;">'
            +       '<div style="font-weight:600;color:var(--text-primary);">'
            +         '<i class="fa-solid fa-circle-question" style="color:var(--saffron);margin-right:6px;"></i>'
            +         esc(q.questionText)
            +       '</div>'
            +       '<div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">'
            +         'Asked by ' + esc(q.askedBy) + ' · ' + esc(fmtDate(q.createdAt))
            +       '</div>'
            +     '</div>'
            +   '</div>'
            +   answersHtml
            +   noAnswers
            + '</div>';
    }

    function load(productId) {
        var box = document.getElementById('productQaList');
        if (!box) return;

        box.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">Loading…</div>';

        fetch(API_PUBLIC + '?product_id=' + encodeURIComponent(productId) + '&limit=20', { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');

                var countEl = document.getElementById('productQaCount');
                if (countEl) countEl.textContent = d.pagination.total;

                if (!d.questions.length) {
                    box.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">No questions yet. Be the first to ask!</div>';
                    return;
                }

                box.innerHTML = d.questions.map(function (q) { return render(q, productId); }).join('');
            })
            .catch(function () {
                box.innerHTML = '<div style="padding:20px;text-align:center;color:#E53E3E;">Could not load Q&A.</div>';
            });
    }

    window.askProductQuestion = function (productId) {
        if (!productId) { alert('Product ID missing.'); return; }

        var text = prompt('Ask a question about this product (min 10 characters):');
        if (text === null) return;
        text = text.trim();
        if (text.length < 10) { alert('Question must be at least 10 characters.'); return; }

              // Unique idempotency key per click
        var idemKey = 'q-' + Date.now() + '-' + Math.random().toString(36).slice(2, 12);

        fetch(API_ASK, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf(),
                'Idempotency-Key': idemKey
            },
            body: JSON.stringify({ product_id: productId, question_text: text })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            alert(d.message);
            load(productId);
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    window.loadProductQa = load;
})();