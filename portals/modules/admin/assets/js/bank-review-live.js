(() => {
  'use strict';
  const id = new URLSearchParams(location.search).get('id') || location.pathname.split('/').filter(Boolean).pop();
  const base = '/conscious/api/admin/';
  const el = name => document.getElementById(name);
  const message = el('abr-message');
  const dialog = el('abr-decision-dialog');
  const revealDialog = el('abr-reveal-dialog');
  let record = null;
  let pendingDecision = '';
  let sensitiveTimer = 0;

  async function request(path, method = 'GET', body) {
    const response = await fetch(base + path, {method, credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json', ...(method === 'GET' ? {} : {'Content-Type': 'application/json', 'X-CSRF-Token': sessionStorage.getItem('ci_admin_csrf_token') || ''})}, body: body === undefined ? undefined : JSON.stringify(body)});
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) throw Error(data.message || 'Bank review request failed.');
    return data;
  }
  function status(value) { const node = document.createElement('span'); node.className = 'ac-status ' + (value === 'verified' || value === 'clean' ? 'success' : value === 'rejected' ? 'danger' : value === 'changes_requested' ? 'warning' : 'info'); node.textContent = String(value || 'pending').replaceAll('_', ' '); return node; }
  function fact(label, value) { const box = document.createElement('div'), small = document.createElement('small'), strong = document.createElement('strong'); small.textContent = label; strong.textContent = value || '—'; box.append(small, strong); return box; }
  function button(label, cls, handler, icon) { const node = document.createElement('button'); node.type = 'button'; node.className = 'ac-btn ' + cls; node.innerHTML = (icon ? '<i class="fa-solid ' + icon + '"></i> ' : '') + label; node.addEventListener('click', handler); return node; }
  function hideSensitive() { clearTimeout(sensitiveTimer); const box = el('abr-sensitive'); box.hidden = true; box.replaceChildren(); }
  function copyButton(label, value) { return button(label, 'small', async event => { try { await navigator.clipboard.writeText(value); event.currentTarget.textContent = 'Copied'; setTimeout(() => { event.currentTarget.textContent = label; }, 1400); } catch (_) { message.textContent = 'Copy was blocked by the browser. Select the value manually.'; } }, 'fa-copy'); }
  function showSensitive(data) {
    const box = el('abr-sensitive'); box.replaceChildren();
    const heading = document.createElement('div'); heading.className = 'abr-sensitive-head'; const title = document.createElement('strong'); title.textContent = 'Unlocked payment details'; const hide = button('Hide now', 'small', hideSensitive, 'fa-eye-slash'); heading.append(title, hide);
    const account = fact('Account number', data.account_number), ifsc = fact('IFSC', data.ifsc), beneficiary = fact('Beneficiary', data.beneficiary_name);
    account.append(copyButton('Copy account', data.account_number)); ifsc.append(copyButton('Copy IFSC', data.ifsc)); box.append(heading, beneficiary, account, ifsc);
    const expiry = document.createElement('small'); expiry.className = 'ac-help'; expiry.textContent = 'Automatically hidden after 60 seconds. This access has been audited.'; box.append(expiry); box.hidden = false;
    sensitiveTimer = window.setTimeout(hideSensitive, 60000);
  }

  function openDecision(decision) {
    pendingDecision = decision;
    const approving = decision === 'verified';
    el('abr-reason').value = '';
    el('abr-reason-error').hidden = true;
    el('abr-reason').closest('.ac-field').hidden = approving;
    el('abr-dialog-title').textContent = approving ? 'Approve payout account' : decision === 'changes_requested' ? 'Request bank correction' : 'Reject payout account';
    el('abr-dialog-copy').textContent = approving ? 'Confirm that the account holder, masked account number and verified proof match. This account will be enabled for future payouts.' : decision === 'changes_requested' ? 'Tell the vendor exactly which bank detail or proof must be corrected and resubmitted.' : 'Explain why this payout account cannot be accepted.';
    el('abr-dialog-submit').textContent = approving ? 'Approve account' : decision === 'changes_requested' ? 'Send correction request' : 'Reject account';
    dialog.showModal();
    setTimeout(() => (approving ? el('abr-dialog-submit') : el('abr-reason')).focus(), 30);
  }
  function renderProof(proof) {
    const box = el('abr-proof'); box.replaceChildren();
    if (!proof) { box.append(document.createTextNode('No bank proof was uploaded after the latest account change.')); return; }
    const facts = document.createElement('div'); facts.className = 'ac-record-facts'; facts.append(fact('File', proof.original_name), fact('File type', proof.mime_type), fact('Security scan', proof.scan_status), fact('Proof review', proof.verification_status)); box.append(facts);
    if (!proof.scan_reviewable) return;
    const fileUrl = '/conscious/api/files.php?id=' + encodeURIComponent(proof.file_id);
    if (String(proof.mime_type || '').startsWith('image/')) { const preview = document.createElement('img'); preview.className = 'abr-proof-preview'; preview.src = fileUrl; preview.alt = 'Uploaded bank proof: ' + proof.original_name; box.append(preview); }
    const view = document.createElement('a'); view.className = 'ac-btn mt-3'; view.target = '_blank'; view.rel = 'noopener'; view.href = fileUrl; view.innerHTML = '<i class="fa-solid fa-eye"></i> View full file'; box.append(view);
    if (proof.verification_status !== 'verified') box.append(button('Verify this proof', 'primary mt-3', async event => { event.currentTarget.disabled = true; try { await request('vendor-applications.php', 'POST', {action: 'document_decision', application_id: proof.application_id, document_id: proof.id, decision: 'verified'}); await load(); } catch (error) { message.textContent = error.message; } finally { event.currentTarget.disabled = false; } }, 'fa-file-circle-check'));
  }
  function renderHistory() {
    const history = el('abr-history'); history.replaceChildren();
    if (!record.history.length) { history.textContent = 'No previous bank decisions recorded.'; return; }
    record.history.forEach(entry => { const row = document.createElement('div'), copy = document.createElement('div'), title = document.createElement('strong'), note = document.createElement('p'); row.className = 'ac-item'; copy.className = 'ac-item-copy'; title.textContent = (entry.from_status || 'new') + ' → ' + entry.to_status; note.textContent = (entry.reason || 'No reason recorded') + ' · ' + entry.created_at; copy.append(title, note); row.append(copy); history.append(row); });
  }
  function render() {
    const account = record.account, proof = account.proof, ready = Boolean(proof?.scan_reviewable) && proof?.verification_status === 'verified';
    el('abr-title').textContent = account.display_name + ' payout account'; el('abr-subtitle').textContent = account.email + ' · submitted ' + (account.updated_at || account.created_at); el('abr-status').replaceChildren(status(account.verification_status));
    el('abr-facts').replaceChildren(fact('Seller', account.display_name), fact('Owner', account.owner_name), fact('Account holder', account.beneficiary_name), fact('Account number', '•••• ' + account.account_last4), fact('Bank', account.bank_name || 'Not supplied'), fact('Seller status', account.vendor_status));
    renderProof(proof); renderHistory();
    el('abr-readiness').textContent = ready ? 'Proof is security-cleared and verified. The account can be approved after matching the holder name and final four digits.' : !proof ? 'Approval locked: no current bank proof is available.' : 'Approval locked until the current proof is security-cleared and verified.';
    hideSensitive(); const actions = el('abr-actions'); actions.replaceChildren(); const approve = button('Approve payout account', 'primary', () => openDecision('verified'), 'fa-circle-check'); approve.disabled = !ready || account.verification_status === 'verified'; actions.append(button('Unlock bank details', '', () => { el('abr-reveal-purpose').value = ''; el('abr-reveal-error').hidden = true; revealDialog.showModal(); setTimeout(() => el('abr-reveal-purpose').focus(), 30); }, 'fa-lock-open'), approve, button('Request correction', '', () => openDecision('changes_requested'), 'fa-rotate'), button('Reject account', 'danger', () => openDecision('rejected'), 'fa-ban'));
    message.textContent = 'Review the masked account details and uploaded proof before deciding.';
    const intent = new URLSearchParams(location.search).get('intent'); if (intent === 'correct') openDecision('changes_requested'); if (intent === 'reject') openDecision('rejected'); if (intent) history.replaceState(null, '', location.pathname);
  }
  async function load() { if (!id) { message.textContent = 'Bank account id is missing.'; return; } try { record = await request('vendor-banks.php?id=' + encodeURIComponent(id)); render(); } catch (error) { message.textContent = error.message; } }
  el('abr-decision-form').addEventListener('submit', async event => { event.preventDefault(); const reason = el('abr-reason').value.trim(); if (pendingDecision !== 'verified' && reason.length < 10) { el('abr-reason-error').textContent = 'Enter a clear reason of at least 10 characters.'; el('abr-reason-error').hidden = false; el('abr-reason').focus(); return; } const submit = el('abr-dialog-submit'); submit.disabled = true; try { await request('vendor-banks.php', 'POST', {bank_account_id: id, decision: pendingDecision, reason}); dialog.close(); await load(); } catch (error) { el('abr-reason-error').textContent = error.message; el('abr-reason-error').hidden = false; } finally { submit.disabled = false; } });
  ['abr-dialog-close', 'abr-dialog-cancel'].forEach(name => el(name).addEventListener('click', () => dialog.close()));
  el('abr-reveal-form').addEventListener('submit', async event => { event.preventDefault(); const purpose = el('abr-reveal-purpose').value.trim(); if (purpose.length < 10) { el('abr-reveal-error').textContent = 'Explain the payout purpose in at least 10 characters.'; el('abr-reveal-error').hidden = false; el('abr-reveal-purpose').focus(); return; } const submit = el('abr-reveal-submit'); submit.disabled = true; try { const data = await request('vendor-banks.php', 'POST', {action: 'reveal', bank_account_id: id, purpose}); revealDialog.close(); showSensitive(data); message.textContent = data.message; } catch (error) { el('abr-reveal-error').textContent = error.message; el('abr-reveal-error').hidden = false; } finally { submit.disabled = false; } });
  ['abr-reveal-close', 'abr-reveal-cancel'].forEach(name => el(name).addEventListener('click', () => revealDialog.close()));
  load();
})();
