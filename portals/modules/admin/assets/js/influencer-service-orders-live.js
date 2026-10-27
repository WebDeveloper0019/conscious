(function () {
  'use strict';
  const container = document.getElementById('if-admin-service-orders');
  if (!container) return;
  const message = document.getElementById('if-admin-service-orders-message');
  const refresh = document.getElementById('if-admin-service-orders-refresh');
  const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
  const transitions = {
    paid: ['contact_pending', 'accepted', 'cancelled', 'disputed'],
    contact_pending: ['accepted', 'scheduled', 'cancelled', 'disputed'],
    accepted: ['scheduled', 'in_progress', 'cancelled', 'disputed'],
    scheduled: ['in_progress', 'cancelled', 'disputed'],
    in_progress: ['delivered', 'cancelled', 'disputed'],
    delivered: ['revision_requested', 'completed', 'disputed'],
    revision_requested: ['in_progress', 'delivered', 'completed', 'disputed'],
    cancellation_requested: ['cancelled', 'in_progress', 'disputed'],
    disputed: ['in_progress', 'delivered', 'completed', 'cancelled']
  };
  function el(tag, value, className) {
    const item = document.createElement(tag);
    if (value !== undefined) item.textContent = String(value);
    if (className) item.className = className;
    return item;
  }
  function csrf() {
    const match = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    return sessionStorage.getItem('ci_admin_csrf_token') || (match ? decodeURIComponent(match[1]) : '');
  }
  async function request(method = 'GET', body) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') headers['X-CSRF-Token'] = csrf();
    if (body) headers['Content-Type'] = 'application/json';
    const response = await fetch('../../../api/admin/influencer-service-orders.php', {
      method, credentials: 'same-origin', cache: 'no-store', headers,
      body: body ? JSON.stringify(body) : undefined
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.success === false) throw Error(data.message || 'Creator orders could not be loaded.');
    return data;
  }
  function orderCard(order) {
    const card = el('article', undefined, 'ac-card mt-3');
    const body = el('div', undefined, 'ac-body');
    body.append(
      el('h3', order.package_title + ' · ' + order.order_number),
      el('p', order.vendor_name + ' → ' + order.influencer_name + ' (@' + order.influencer_handle + ')'),
      el('p', 'Status: ' + String(order.status).replaceAll('_', ' ') + ' · Payment: ' + String(order.payment_status).replaceAll('_', ' ')),
      el('p', 'Total: ' + money.format(Number(order.total_amount)) + ' · Creator earning: ' + money.format(Number(order.influencer_earning_amount))),
      el('p', 'Brief: ' + order.brief),
      el('p', 'Vendor contact: ' + order.contact_name + ' · ' + order.contact_email + (order.contact_mobile ? ' · ' + order.contact_mobile : ''))
    );
    const allowed = transitions[order.status] || [];
    if (allowed.length) {
      const controls = el('div', undefined, 'ac-form-grid mt-3');
      const selectField = el('label', 'Next status', 'ac-field');
      const select = el('select', undefined, 'ac-select');
      allowed.forEach(status => { const option = el('option', status.replaceAll('_', ' ')); option.value = status; select.append(option); });
      selectField.append(select);
      const noteField = el('label', 'Coordination note', 'ac-field');
      const note = el('textarea', undefined, 'ac-textarea');
      note.maxLength = 1000;
      note.placeholder = 'Required for cancellation, dispute or revision request.';
      noteField.append(note);
      controls.append(selectField, noteField);
      const save = el('button', 'Save status', 'ac-btn primary');
      save.type = 'button';
      save.addEventListener('click', async () => {
        if (['cancelled', 'disputed', 'revision_requested'].includes(select.value) && note.value.trim().length < 10) {
          message.textContent = 'Enter at least 10 characters explaining this decision.';
          return;
        }
        save.disabled = true;
        try {
          const result = await request('PATCH', { order_id: order.id, status: select.value, note: note.value.trim() });
          message.textContent = result.message;
          await load();
        } catch (error) { message.textContent = error.message; save.disabled = false; }
      });
      body.append(controls, save);
    }
    card.append(body);
    return card;
  }
  async function load() {
    message.textContent = 'Loading creator collaboration orders…';
    try {
      const data = await request();
      container.replaceChildren();
      message.textContent = data.orders.length ? data.orders.length + ' creator collaboration order(s).' : 'No creator package has been purchased yet.';
      data.orders.forEach(order => container.append(orderCard(order)));
    } catch (error) {
      message.textContent = error.message;
      container.replaceChildren();
    }
  }
  refresh.addEventListener('click', load);
  load();
})();
