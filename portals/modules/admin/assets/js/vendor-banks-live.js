(() => {
  'use strict';
  const rows = document.getElementById('bank-review-rows');
  const message = document.getElementById('bank-review-message');
  const search = document.getElementById('bank-review-search');
  const filter = document.getElementById('bank-review-filter');
  let accounts = [];

  async function request() {
    const response = await fetch('/conscious/api/admin/vendor-banks.php', {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}});
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) throw Error(data.message || 'Unable to load bank accounts.');
    return data;
  }
  function cell(value, cls = '') { const node = document.createElement('td'); node.className = cls; node.textContent = String(value ?? '—'); return node; }
  function status(value) { const node = document.createElement('span'); node.className = 'ac-status ' + (value === 'verified' || value === 'clean' ? 'success' : value === 'rejected' ? 'danger' : value === 'changes_requested' ? 'warning' : 'info'); node.textContent = String(value || 'pending').replaceAll('_', ' '); return node; }
  function action(label, icon, href, cls = '') { const link = document.createElement('a'); link.className = 'ac-btn small ' + cls; link.href = href; link.innerHTML = '<i class="fa-solid ' + icon + '"></i> ' + label; return link; }

  function render() {
    const query = search.value.trim().toLowerCase(), wanted = filter.value;
    const matches = accounts.filter(account => (!wanted || account.verification_status === wanted) && (!query || (account.display_name + ' ' + account.email + ' ' + account.beneficiary_name + ' ' + account.account_last4).toLowerCase().includes(query)));
    rows.replaceChildren();
    if (!matches.length) { const tr = document.createElement('tr'), td = cell(accounts.length ? 'No accounts match these filters.' : 'No payout accounts have been submitted.'); td.colSpan = 6; tr.append(td); rows.append(tr); return; }
    matches.forEach(account => {
      const tr = document.createElement('tr');
      const seller = cell('');
      const name = document.createElement('strong'), email = document.createElement('small');
      name.textContent = account.display_name; email.className = 'ac-help'; email.textContent = account.email; seller.append(name, email);
      const proof = cell(''); proof.append(status(account.proof?.scan_status || 'not uploaded'));
      const stateCell = cell(''); stateCell.append(status(account.verification_status));
      const controls = cell('', 'ac-row-actions');
      const reviewUrl = '/conscious/admin/bank-accounts/' + encodeURIComponent(account.id);
      controls.append(action('View', 'fa-eye', reviewUrl, 'primary'));
      if (account.verification_status !== 'verified') controls.append(action('Correct', 'fa-rotate', reviewUrl + '?intent=correct'));
      if (!['verified', 'rejected'].includes(account.verification_status)) controls.append(action('Reject', 'fa-ban', reviewUrl + '?intent=reject', 'danger'));
      tr.append(seller, cell(account.beneficiary_name), cell('•••• ' + account.account_last4), proof, stateCell, controls);
      rows.append(tr);
    });
  }
  async function load() {
    message.textContent = 'Loading payout accounts…';
    try { const result = await request(); accounts = result.accounts || []; message.textContent = accounts.length + ' payout account' + (accounts.length === 1 ? '' : 's') + ' found. Open a record to inspect and decide.'; render(); }
    catch (error) { message.textContent = error.message; message.classList.add('danger'); }
  }
  search.addEventListener('input', render);
  filter.addEventListener('change', render);
  document.getElementById('bank-review-refresh').addEventListener('click', load);
  load();
})();
