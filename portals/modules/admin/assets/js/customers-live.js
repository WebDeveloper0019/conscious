(function () {
  'use strict';
  var rows = document.getElementById('ac-customer-rows');
  var status = document.getElementById('ac-customer-status');
  var search = document.getElementById('ac-customer-search');
  var filter = document.getElementById('ac-customer-filter');
  if (!rows || !status || !search || !filter) return;
  var customers = [];
  function mask(email) { var parts = String(email || '').split('@'); return parts.length === 2 ? (parts[0].slice(0, 1) || '•') + '•••@' + parts[1] : '—'; }
  function money(value) { return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Number(value) || 0); }
  function cell(row, value) { var item = document.createElement('td'); item.textContent = value; row.append(item); return item; }
  function render() {
    rows.replaceChildren();
    var term = search.value.trim().toLowerCase(); var state = filter.value;
    var visible = customers.filter(function (item) { return (!state || item.status === state) && (!term || ((item.first_name || '') + ' ' + (item.last_name || '') + ' ' + (item.email || '')).toLowerCase().includes(term)); });
    status.textContent = visible.length + ' customer' + (visible.length === 1 ? '' : 's') + ' shown';
    visible.forEach(function (item) { var row = document.createElement('tr'); cell(row, ((item.first_name || '') + ' ' + (item.last_name || '')).trim() + ' · ' + mask(item.email)); cell(row, item.status || '—'); cell(row, String(item.order_count || 0)); cell(row, money(item.lifetime_value)); cell(row, item.created_at ? new Date(item.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('en-IN') : '—'); var action = cell(row, ''); var link = document.createElement('a'); link.className = 'ac-btn small'; link.textContent = 'Review'; link.href = '/conscious/admin/customers/' + encodeURIComponent(item.id); action.append(link); rows.append(row); });
  }
  async function load() { status.textContent = 'Loading customers.'; try { var response = await fetch('/conscious/api/admin/customers.php', { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } }); var data = await response.json().catch(function () { return {}; }); if (!response.ok || !data.success) throw new Error(data.message || 'Customer request failed.'); customers = data.customers || []; render(); } catch (error) { status.textContent = error.message; rows.replaceChildren(); } }
  search.addEventListener('input', render); filter.addEventListener('change', render); document.getElementById('ac-customer-refresh').addEventListener('click', load); load();
})();
