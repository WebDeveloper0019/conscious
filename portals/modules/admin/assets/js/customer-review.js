(function () {
  'use strict';
  var root = document.getElementById('ac-customer-record');
  var statusBox = document.getElementById('ac-customer-status');
  if (!root || !statusBox) return;
  var customerId = root.dataset.recordId;
  var customer = null;
  var endpoint = '/conscious/api/admin/customers.php';
  function mask(email) { var parts = String(email || '').split('@'); return parts.length === 2 ? (parts[0].slice(0, 1) || '•') + '•••@' + parts[1] : '—'; }
  function money(value) { return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Number(value) || 0); }
  function show(value, error) { statusBox.textContent = value; statusBox.classList.toggle('danger', Boolean(error)); }
  function fact(label, value) { var box = document.createElement('div'); var small = document.createElement('small'); var strong = document.createElement('strong'); small.textContent = label; strong.textContent = value; box.append(small, strong); return box; }
  async function request(method, body) {
    method = method || 'GET';
    var response = await fetch(endpoint, { method: method, credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json', ...(method === 'GET' ? {} : { 'Content-Type': 'application/json', 'X-CSRF-Token': sessionStorage.getItem('ci_admin_csrf_token') || '' }) }, body: body ? JSON.stringify(body) : undefined });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok || !data.success) throw new Error(data.message || 'Customer request failed.');
    return data;
  }
  async function load() {
    if (!customerId) throw new Error('Customer reference is missing.');
    var data = await request();
    customer = (data.customers || []).find(function (item) { return String(item.id) === customerId; });
    if (!customer) throw new Error('Customer record was not found.');
    document.getElementById('ac-customer-name').textContent = ((customer.first_name || '') + ' ' + (customer.last_name || '')).trim() || 'Customer';
    document.getElementById('ac-customer-state').textContent = customer.status || 'unknown';
    var facts = document.getElementById('ac-customer-facts'); facts.replaceChildren();
    facts.append(fact('Masked email', mask(customer.email)), fact('Orders', String(customer.order_count || 0)), fact('Lifetime value', money(customer.lifetime_value)), fact('Joined', customer.created_at ? new Date(customer.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-IN') : '—'));
    show('Customer record loaded.');
  }
  document.querySelectorAll('[data-customer-status]').forEach(function (button) { button.addEventListener('click', async function () { if (!customer) return; var reason = document.getElementById('ac-customer-reason').value.trim(); if (button.dataset.customerStatus !== 'active' && reason.length < 10) { show('Explain the decision in at least 10 characters.', true); return; } button.disabled = true; try { var data = await request('POST', { customer_id: customer.id, status: button.dataset.customerStatus, reason: reason }); show(data.message); await load(); } catch (error) { show(error.message, true); } finally { button.disabled = false; } }); });
  load().catch(function (error) { show(error.message, true); });
})();
