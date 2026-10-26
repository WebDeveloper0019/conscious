/* Conscious India — Customer Reviews: list, submit, edit, delete. */
'use strict';

(function () {
    var API_LIST    = '../api/user/reviews.php';
    var API_PENDING = '../api/user/pending-reviews.php';

    function csrf() {
        var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function stars(n) {
        var out = '';
        n = Number(n) || 0;
        for (var i = 1; i <= 5; i++) out += (i <= n ? '★' : '☆');
        return out;
    }

    function fmtDate(v) {
        if (!v) return '—';
        var d = new Date(String(v).replace(' ', 'T') + (String(v).indexOf('Z') === -1 ? 'Z' : ''));
        return isNaN(d.getTime()) ? v : d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    /* ─── Load my reviews ─── */
    function loadMyReviews() {
        var list = document.getElementById('myReviewsList');
        if (!list) return;

        return fetch(API_LIST + '?limit=50', { credentials: 'include' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');

                var sub = document.getElementById('reviewsSubtitle');
                if (sub) {
                    var total = (data.pagination && data.pagination.total) || data.reviews.length;
                    sub.textContent = total + ' review' + (total === 1 ? '' : 's') + ' written';
                }

                if (!data.reviews.length) {
                    list.innerHTML = '<div style="padding:24px;text-align:center;color:var(--text-muted);">No reviews yet. Once your orders are delivered, you can review them.</div>';
                    return;
                }

                list.innerHTML = data.reviews.map(function (r) {
                    var vendorReplyHtml = r.vendorReply
                        ? '<div style="background:#F9FAFB;padding:10px;margin-top:10px;border-radius:6px;border-left:3px solid var(--saffron);">'
                          + '<div style="font-size:.8rem;font-weight:600;color:var(--text-secondary);">Vendor Reply</div>'
                          + '<div style="margin-top:4px;">' + esc(r.vendorReply) + '</div></div>'
                        : '';

                    var editBtn = r.editable
                        ? '<button class="bc-btn bc-btn-outline bc-btn-sm" onclick="editReview(\'' + r.id + '\')">Edit</button>'
                        : '';

                    return ''
                        + '<div class="ci-review-card" style="padding:16px;border-bottom:1px solid var(--border-light);">'
                        +   '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">'
                        +     '<div style="flex:1;">'
                        +       '<div style="font-weight:600;color:var(--text-primary);margin-bottom:4px;">' + esc(r.productName || 'Product') + '</div>'
                        +       '<div style="color:#F59E0B;font-size:1.1rem;">' + stars(r.rating) + '</div>'
                        +       (r.title ? '<div style="font-weight:600;margin-top:6px;">' + esc(r.title) + '</div>' : '')
                        +       '<div style="color:var(--text-secondary);margin-top:6px;line-height:1.5;">' + esc(r.comment || '') + '</div>'
                        +       '<div style="color:var(--text-muted);font-size:.8rem;margin-top:8px;">'
                        +         esc(fmtDate(r.createdAt)) + ' · Order ' + esc(r.orderNumber || '') + ' · '
                        +         '<span style="text-transform:capitalize;">' + esc(r.status) + '</span>'
                        +       '</div>'
                        +       vendorReplyHtml
                        +     '</div>'
                        +     '<div style="display:flex;gap:6px;flex-shrink:0;">'
                        +       editBtn
                        +       '<button class="bc-btn bc-btn-outline bc-btn-sm" style="color:#E53E3E;border-color:#E53E3E;" onclick="deleteReview(\'' + r.id + '\')">Delete</button>'
                        +     '</div>'
                        +   '</div>'
                        + '</div>';
                }).join('');

                window.__myReviews = data.reviews;
            })
            .catch(function () {
                list.innerHTML = '<div style="padding:24px;text-align:center;color:#E53E3E;">Could not load reviews.</div>';
            });
    }

    /* ─── Load pending ─── */
    function loadPending() {
        var box = document.getElementById('pendingReviewsList');
        if (!box) return;

        return fetch(API_PENDING, { credentials: 'include' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');

                if (!data.pending.length) {
                    box.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">Nothing pending. All delivered items have been reviewed! 🎉</div>';
                    return;
                }

                box.innerHTML = data.pending.map(function (p) {
                    return ''
                        + '<div style="display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border-light);align-items:center;">'
                        +   '<img src="' + esc(p.productImage || '') + '" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;background:#eee;">'
                        +   '<div style="flex:1;min-width:0;">'
                        +     '<div style="font-weight:600;">' + esc(p.productName) + '</div>'
                        +     '<div style="color:var(--text-muted);font-size:.8rem;">Order ' + esc(p.orderNumber) + '</div>'
                        +   '</div>'
                        +   '<button class="bc-btn bc-btn-primary bc-btn-sm" onclick="writeReview(' + Number(p.orderItemId) + ')">Write Review</button>'
                        + '</div>';
                }).join('');

                window.__pendingReviews = data.pending;
            })
            .catch(function () {
                box.innerHTML = '<div style="padding:20px;color:#E53E3E;">Could not load pending items.</div>';
            });
    }

    /* ─── Write review modal ─── */
    window.writeReview = function (orderItemId) {
        var item = (window.__pendingReviews || []).find(function (x) { return x.orderItemId === orderItemId; });
        if (!item) return;

        var modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;';
        modal.innerHTML = ''
            + '<div style="background:#fff;border-radius:10px;max-width:520px;width:100%;max-height:90vh;overflow:auto;">'
            +   '<div style="padding:20px;border-bottom:1px solid #eee;">'
            +     '<h3 style="margin:0;">Review "' + esc(item.productName) + '"</h3>'
            +   '</div>'
            +   '<div style="padding:20px;">'
            +     '<div style="margin-bottom:12px;">'
            +       '<label style="font-weight:600;display:block;margin-bottom:6px;">Rating</label>'
            +       '<div id="starPicker" style="font-size:2rem;color:#F59E0B;cursor:pointer;user-select:none;">'
            +         '<span data-star="1">☆</span><span data-star="2">☆</span><span data-star="3">☆</span><span data-star="4">☆</span><span data-star="5">☆</span>'
            +       '</div>'
            +       '<input type="hidden" id="reviewRating" value="0">'
            +     '</div>'
            +     '<div style="margin-bottom:12px;">'
            +       '<label style="font-weight:600;display:block;margin-bottom:6px;">Title (optional)</label>'
            +       '<input type="text" id="reviewTitle" maxlength="180" class="ci-form-input" placeholder="Summarize your experience" style="width:100%;padding:8px;">'
            +     '</div>'
            +     '<div style="margin-bottom:12px;">'
            +       '<label style="font-weight:600;display:block;margin-bottom:6px;">Your Review</label>'
            +       '<textarea id="reviewComment" rows="5" maxlength="2000" class="ci-form-input" placeholder="What did you like or dislike? (min 10 chars)" style="width:100%;padding:8px;"></textarea>'
            +     '</div>'
            +   '</div>'
            +   '<div style="padding:16px 20px;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:8px;">'
            +     '<button class="bc-btn bc-btn-outline" id="cancelReviewBtn">Cancel</button>'
            +     '<button class="bc-btn bc-btn-primary" id="submitReviewBtn">Submit Review</button>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(modal);

        var picker = modal.querySelector('#starPicker');
        var ratingInput = modal.querySelector('#reviewRating');

        picker.addEventListener('click', function (e) {
            var s = e.target.getAttribute('data-star');
            if (!s) return;
            ratingInput.value = s;
            var spans = picker.querySelectorAll('span');
            for (var i = 0; i < spans.length; i++) {
                spans[i].textContent = Number(spans[i].getAttribute('data-star')) <= Number(s) ? '★' : '☆';
            }
        });

        modal.querySelector('#cancelReviewBtn').addEventListener('click', function () { modal.remove(); });

        modal.querySelector('#submitReviewBtn').addEventListener('click', function () {
            var rating = parseInt(ratingInput.value, 10);
            var title = modal.querySelector('#reviewTitle').value.trim();
            var comment = modal.querySelector('#reviewComment').value.trim();

            if (rating < 1) { alert('Please select a rating.'); return; }
            if (comment.length < 10) { alert('Review must be at least 10 characters.'); return; }

            var btn = modal.querySelector('#submitReviewBtn');
            btn.disabled = true;
            btn.textContent = 'Submitting...';

            fetch(API_LIST, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
                body: JSON.stringify({ order_item_id: orderItemId, rating: rating, title: title, comment: comment })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed');
                modal.remove();
                alert('Review submitted! Thank you.');
                loadMyReviews();
                loadPending();
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.textContent = 'Submit Review';
            });
        });
    };

    /* ─── Edit ─── */
    window.editReview = function (id) {
        var r = (window.__myReviews || []).find(function (x) { return x.id === id; });
        if (!r) return;

        var rating = prompt('Rating (1-5):', r.rating);
        if (rating === null) return;
        var rn = parseInt(rating, 10);
        if (rn < 1 || rn > 5) { alert('Rating must be 1-5.'); return; }

        var title = prompt('Title (optional):', r.title || '');
        if (title === null) return;

        var comment = prompt('Review text:', r.comment || '');
        if (comment === null) return;

        fetch(API_LIST, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ id: id, rating: rn, title: title, comment: comment })
        })
        .then(function (x) { return x.json(); })
        .then(function (data) {
            if (!data.success) throw new Error(data.message || 'Failed');
            alert('Review updated.');
            loadMyReviews();
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    /* ─── Delete ─── */
    window.deleteReview = function (id) {
        if (!confirm('Delete this review permanently?')) return;

        fetch(API_LIST + '?id=' + encodeURIComponent(id), {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'X-CSRF-Token': csrf() }
        })
        .then(function (x) { return x.json(); })
        .then(function (data) {
            if (!data.success) throw new Error(data.message || 'Failed');
            alert('Review deleted.');
            loadMyReviews();
            loadPending();
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    /* ─── Boot ─── */
    function boot() {
        loadMyReviews();
        loadPending();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();