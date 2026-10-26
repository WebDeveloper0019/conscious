(function () {
  'use strict';
  const container = document.getElementById('if-admin-packages');
  const message = document.getElementById('if-admin-packages-message');
  const refresh = document.getElementById('if-admin-packages-refresh');
  function el(tag, value) {
    const node = document.createElement(tag);
    if (value !== undefined) node.textContent = String(value);
    return node;
  }
  function csrf() {
    const match = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    return sessionStorage.getItem('ci_admin_csrf_token') || (match ? decodeURIComponent(match[1]) : '');
  }
  async function request(method, body) {
    const response = await fetch('../../../api/admin/influencer-packages.php', {
      method, credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
      body: body ? JSON.stringify(body) : undefined
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Package review could not be saved.');
    return data;
  }
  async function load() {
    message.textContent = 'Loading packages…';
    try {
      const data = await request('GET');
      container.replaceChildren();
      const pending = data.packages.filter(pkg => pkg.status === 'pending_approval');
      message.textContent = pending.length
        ? pending.length + ' package(s) await review.'
        : 'No packages are awaiting review.';
      pending.forEach(pkg => {
        const card = el('article');
        card.className = 'ac-card mt-3';
        const body = el('div');
        body.className = 'ac-body';
        body.append(
          el('h3', pkg.title),
          el('p', pkg.full_name + ' · @' + pkg.handle + ' · ₹' + pkg.price_inr),
          el('p', pkg.description),
          el('p', 'Deliverables: ' + (JSON.parse(pkg.deliverables_json || '[]')).join(', ')),
          el('p', 'Platforms: ' + (JSON.parse(pkg.platforms_json || '[]')).join(', ')),
          el('p', 'Turnaround: ' + pkg.turnaround_days + ' days · Revisions: ' + pkg.revisions + ' · Capacity: ' + pkg.capacity),
          el('p', 'Usage rights: ' + pkg.usage_rights),
          el('p', 'Vendor requirements: ' + pkg.vendor_requirements),
          el('p', 'Tax: ' + pkg.tax_treatment + ' · Cancellation: ' + pkg.cancellation_terms)
        );
        const label = el('label', 'Review note or reason');
        const note = el('textarea');
        note.className = 'ac-textarea';
        note.maxLength = 500;
        label.append(note);
        body.append(label);
        const actions = el('div');
        actions.className = 'ac-actions mt-3';
        [['approve', 'Approve'], ['request_changes', 'Request changes'], ['reject', 'Reject']].forEach(([action, title]) => {
          const button = el('button', title);
          button.type = 'button';
          button.className = 'ac-btn' + (action === 'approve' ? ' primary' : action === 'reject' ? ' danger' : '');
          button.addEventListener('click', async () => {
            const reason = note.value.trim();
            if (action !== 'approve' && reason.length < 10) {
              message.textContent = 'Enter at least 10 characters explaining your decision.';
              return;
            }
            button.disabled = true;
            try {
              await request('POST', { package_id: pkg.id, action, note: reason });
              await load();
            } catch (error) {
              message.textContent = error.message;
              button.disabled = false;
            }
          });
          actions.append(button);
        });
        body.append(actions);
        card.append(body);
        container.append(card);
      });
    } catch (error) { message.textContent = error.message; }
  }
  refresh.addEventListener('click', load);
  load();
})();
