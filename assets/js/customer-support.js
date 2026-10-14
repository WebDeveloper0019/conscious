/* Conscious Indi — Customer support tickets: list, create, thread, reply. */
'use strict';

(function () {
    var API = '../api/support.php';

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

    function statusClass(s) {
        s = String(s || '').toLowerCase();
        if (s === 'open') return 'ci-status-processing';
        if (s === 'waiting_customer') return 'ci-status-shipped';
        if (s === 'waiting_vendor') return 'ci-status-shipped';
        if (s === 'resolved') return 'ci-status-delivered';
        if (s === 'closed') return 'ci-status-cancelled';
        return '';
    }

    function statusLabel(s) {
        s = String(s || '').toLowerCase();
        return s.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function categoryLabel(c) {
        return String(c || '').replace(/_/g, ' ').replace(/\b\w/g, function (x) { return x.toUpperCase(); });
    }

    /* ─── Stats cards ─── */
    function loadStats() {
        return fetch(API + '?stats=1', { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var el = function (id) { return document.getElementById(id); };
                if (el('statOpen'))     el('statOpen').textContent     = d.stats.open;
                if (el('statResolved')) el('statResolved').textContent = d.stats.resolved;
                if (el('statTotal'))    el('statTotal').textContent    = d.stats.total;
                if (el('statInProg'))   el('statInProg').textContent   = d.stats.inProgress;
            })
            .catch(function () {});
    }

    /* ─── Tickets table ─── */
    function loadTickets(page, search, status) {
        var tbody = document.getElementById('supportTicketsBody');
        if (!tbody) return;

        var qs = '?page=' + (page || 1) + '&limit=20';
        if (search) qs += '&search=' + encodeURIComponent(search);
        if (status) qs += '&status=' + encodeURIComponent(status);

        tbody.innerHTML = '<tr><td colspan="6" style="padding:20px;text-align:center;">Loading…</td></tr>';

        fetch(API + qs, { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');

                if (!d.tickets.length) {
                    tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:var(--text-muted);">No tickets yet.</td></tr>';
                    return;
                }

                tbody.innerHTML = d.tickets.map(function (t) {
                    return ''
                        + '<tr>'
                        +   '<td><a href="#" onclick="openTicket(\'' + t.id + '\');return false;">#' + esc(t.ticket_number) + '</a></td>'
                        +   '<td>' + esc(t.subject) + '</td>'
                        +   '<td>' + (t.order_id ? '#' + esc(t.order_id) : '—') + '</td>'
                        +   '<td><span class="ci-status ' + statusClass(t.status) + '">' + esc(statusLabel(t.status)) + '</span></td>'
                        +   '<td>' + esc(fmtDate(t.updated_at)) + '</td>'
                        +   '<td><div class="ci-table-actions">'
                        +     '<button class="ci-table-action-btn" title="View" onclick="openTicket(\'' + t.id + '\')"><i class="fa-solid fa-eye"></i></button>'
                        +   '</div></td>'
                        + '</tr>';
                }).join('');

                var footer = document.getElementById('supportTableFooter');
                if (footer) {
                    footer.textContent = 'Showing ' + d.tickets.length + ' of ' + d.pagination.total + ' tickets';
                }
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:#E53E3E;">Could not load tickets.</td></tr>';
            });
    }

    /* ─── New ticket modal ─── */
    window.newTicket = function () {
        var modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;';
        modal.innerHTML = ''
            + '<div style="background:#fff;border-radius:10px;max-width:560px;width:100%;max-height:90vh;overflow:auto;">'
            +   '<div style="padding:20px;border-bottom:1px solid #eee;"><h3 style="margin:0;">Raise a New Ticket</h3></div>'
            +   '<div style="padding:20px;">'
            +     '<div style="margin-bottom:12px;"><label style="font-weight:600;display:block;margin-bottom:6px;">Category</label>'
            +       '<select id="ntCategory" class="ci-form-input" style="width:100%;padding:8px;">'
            +         '<option value="order">Order related</option>'
            +         '<option value="payment">Payment</option>'
            +         '<option value="product">Product</option>'
            +         '<option value="return">Return / Refund</option>'
            +         '<option value="account">Account</option>'
            +         '<option value="other">Other</option>'
            +       '</select></div>'
            +     '<div style="margin-bottom:12px;"><label style="font-weight:600;display:block;margin-bottom:6px;">Priority</label>'
            +       '<select id="ntPriority" class="ci-form-input" style="width:100%;padding:8px;">'
            +         '<option value="normal">Normal</option>'
            +         '<option value="low">Low</option>'
            +         '<option value="high">High</option>'
            +         '<option value="urgent">Urgent</option>'
            +       '</select></div>'
            +     '<div style="margin-bottom:12px;"><label style="font-weight:600;display:block;margin-bottom:6px;">Subject</label>'
            +       '<input type="text" id="ntSubject" maxlength="180" class="ci-form-input" placeholder="Brief summary" style="width:100%;padding:8px;"></div>'
            +     '<div style="margin-bottom:12px;"><label style="font-weight:600;display:block;margin-bottom:6px;">Description</label>'
            +       '<textarea id="ntMessage" rows="5" maxlength="5000" class="ci-form-input" placeholder="Explain the issue (min 10 chars)" style="width:100%;padding:8px;"></textarea></div>'
            +   '</div>'
            +   '<div style="padding:16px 20px;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:8px;">'
            +     '<button class="bc-btn bc-btn-outline" id="ntCancel">Cancel</button>'
            +     '<button class="bc-btn bc-btn-primary" id="ntSubmit">Submit Ticket</button>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(modal);

        modal.querySelector('#ntCancel').addEventListener('click', function () { modal.remove(); });

        modal.querySelector('#ntSubmit').addEventListener('click', function () {
            var payload = {
                category: modal.querySelector('#ntCategory').value,
                priority: modal.querySelector('#ntPriority').value,
                subject:  modal.querySelector('#ntSubject').value.trim(),
                message:  modal.querySelector('#ntMessage').value.trim(),
            };

            if (payload.subject.length < 5) { alert('Subject must be at least 5 characters.'); return; }
            if (payload.message.length < 10) { alert('Description must be at least 10 characters.'); return; }

            var btn = modal.querySelector('#ntSubmit');
            btn.disabled = true;
            btn.textContent = 'Submitting…';

            fetch(API, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');
                modal.remove();
                alert('Ticket created: ' + d.ticket_number);
                loadTickets(1);
                loadStats();
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.textContent = 'Submit Ticket';
            });
        });
    };

    /* ─── Thread view ─── */
    window.openTicket = function (ticketId) {
        fetch(API + '?id=' + encodeURIComponent(ticketId), { credentials: 'include' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');
                renderThread(d.ticket);
            })
            .catch(function (err) { alert('Error: ' + err.message); });
    };

    function renderThread(ticket) {
        var threadModal = document.createElement('div');
        threadModal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;';
        threadModal.innerHTML = ''
            + '<div style="background:#fff;border-radius:10px;max-width:680px;width:100%;max-height:90vh;display:flex;flex-direction:column;">'
            +   '<div style="padding:20px;border-bottom:1px solid #eee;">'
            +     '<div style="display:flex;justify-content:space-between;align-items:center;">'
            +       '<div>'
            +         '<h3 style="margin:0;">#' + esc(ticket.ticket_number) + '</h3>'
            +         '<div style="color:var(--text-muted);font-size:.85rem;">' + esc(ticket.subject) + '</div>'
            +       '</div>'
            +       '<span class="ci-status ' + statusClass(ticket.status) + '">' + esc(statusLabel(ticket.status)) + '</span>'
            +     '</div>'
            +   '</div>'
            +   '<div id="threadMessages" style="padding:16px 20px;overflow-y:auto;flex:1;"></div>'
            +   '<div style="padding:16px 20px;border-top:1px solid #eee;">'
            +     '<textarea id="threadReply" rows="3" maxlength="5000" class="ci-form-input" placeholder="Type your reply…" style="width:100%;padding:8px;"></textarea>'
            +     '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">'
            +       '<button class="bc-btn bc-btn-outline" id="threadClose">Close</button>'
            +       '<button class="bc-btn bc-btn-primary" id="threadSend">Send Reply</button>'
            +     '</div>'
            +   '</div>'
            + '</div>';
        document.body.appendChild(threadModal);

        var msgBox = threadModal.querySelector('#threadMessages');
        var isClosed = ['closed', 'resolved'].indexOf(String(ticket.status).toLowerCase()) !== -1;

        msgBox.innerHTML = (ticket.messages || []).map(function (m) {
            var isMe = m.senderType === 'customer' || m.senderType === 'vendor' || m.senderType === 'influencer';
            var bg = isMe ? '#F0F9FF' : '#F9FAFB';
            return ''
                + '<div style="margin-bottom:12px;padding:10px 12px;border-radius:8px;background:' + bg + ';">'
                +   '<div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--text-muted);">'
                +     '<span>' + esc(m.senderName) + (m.isInternal ? ' (internal note)' : '') + '</span>'
                +     '<span>' + esc(fmtDate(m.createdAt)) + '</span>'
                +   '</div>'
                +   '<div style="margin-top:6px;white-space:pre-wrap;">' + esc(m.message) + '</div>'
                + '</div>';
        }).join('');

        msgBox.scrollTop = msgBox.scrollHeight;

        var replyBox = threadModal.querySelector('#threadReply');
        var sendBtn  = threadModal.querySelector('#threadSend');

        if (isClosed) {
            replyBox.disabled = true;
            replyBox.placeholder = 'This ticket is ' + ticket.status + '. Open a new ticket for further help.';
            sendBtn.disabled = true;
        }

        threadModal.querySelector('#threadClose').addEventListener('click', function () { threadModal.remove(); });

        sendBtn.addEventListener('click', function () {
            var msg = replyBox.value.trim();
            if (msg.length < 10) { alert('Reply must be at least 10 characters.'); return; }

            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending…';

            fetch(API, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
                body: JSON.stringify({ ticket_id: ticket.id, message: msg })
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error(d.message || 'Failed');
                threadModal.remove();
                openTicket(ticket.id);
                loadTickets(1);
                loadStats();
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send Reply';
            });
        });
    }

    /* ─── Boot ─── */
    function boot() {
        loadStats();
        loadTickets(1);

        var searchInput = document.getElementById('supportSearch');
        var statusFilter = document.getElementById('supportStatusFilter');

        var searchTimer = null;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    loadTickets(1, searchInput.value.trim(), statusFilter ? statusFilter.value : '');
                }, 300);
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                loadTickets(1, searchInput ? searchInput.value.trim() : '', statusFilter.value);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();