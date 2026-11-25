(function () {
  'use strict';
  var root = document.getElementById('admin-catalog-detail');
  var message = document.getElementById('admin-catalog-message');
  if (!root || !message) return;
  var productId = root.dataset.recordId;
  var selected = null;
  var base = '/conscious/api/admin/';
  function text(tag, value, className) { var node = document.createElement(tag); node.textContent = String(value); if (className) node.className = className; return node; }
  function show(value, error) { message.textContent = value; message.classList.toggle('danger', Boolean(error)); }
  function name(value) { return String(value || '').replaceAll('_', ' '); }
  async function request(path, method, body) {
    method = method || 'GET';
    var headers = { Accept: 'application/json' };
    if (method !== 'GET') { headers['Content-Type'] = 'application/json'; headers['X-CSRF-Token'] = sessionStorage.getItem('ci_admin_csrf_token') || ''; }
    var response = await fetch(base + path, { method: method, credentials: 'same-origin', cache: 'no-store', headers: headers, body: body === undefined ? undefined : JSON.stringify(body) });
    var result = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(result.message || 'Review action failed.');
    return result;
  }
  async function load() {
    if (!productId) throw new Error('Product reference is missing.');
    var result = await request('products.php?id=' + encodeURIComponent(productId));
    selected = result.product;
    document.getElementById('admin-catalog-name').textContent = selected.name + ' · ' + selected.vendor_name;
    document.getElementById('admin-catalog-state').textContent = name(selected.status);
    var summary = document.getElementById('admin-catalog-summary');
    summary.replaceChildren();
    [['SKU', selected.sku], ['Product description', selected.description], ['Product story', selected.product_story || 'Not supplied'], ['Material', selected.material || 'Not supplied'], ['Origin', selected.origin_state || 'Not supplied'], ['MRP', '₹' + selected.mrp], ['Selling price', '₹' + selected.selling_price], ['GST', selected.gst_percent + '%'], ['Weight', selected.package_weight_grams + ' g']].forEach(function (item) { var fact = text('div', ''); fact.append(text('small', item[0]), text('strong', item[1])); summary.append(fact); });
    var images = document.getElementById('admin-catalog-images');
    images.replaceChildren();
    if (!result.images.length) images.append(text('p', 'No images uploaded.'));
    result.images.forEach(function (image) { var row = text('div', image.alt_text + ' · ' + name(image.scan_status), 'ac-info-row'); var link = text('a', 'Open file'); link.href = '/conscious/api/files.php?id=' + encodeURIComponent(image.file_id); link.target = '_blank'; link.rel = 'noopener'; row.append(link); images.append(row); });
    var actions = document.getElementById('admin-catalog-actions');
    actions.replaceChildren();
    var possible = selected.status === 'pending_review' ? ['start_review'] : selected.status === 'under_review' ? ['approve', 'request_changes', 'reject'] : ['approved', 'active', 'paused', 'out_of_stock'].includes(selected.status) ? ['suspend'] : [];
    possible.forEach(function (decision) { var button = text('button', name(decision), 'ac-btn ' + (decision === 'approve' ? 'primary' : '')); button.type = 'button'; button.disabled = decision === 'approve' && !result.images.some(function (image) { return Boolean(image.scan_reviewable); }); button.addEventListener('click', function () { decide(decision, button); }); actions.append(button); });
    if (!possible.length) actions.append(text('p', 'No review action is available in this state.'));
    show('Product submission loaded.');
  }
  async function decide(decision, button) {
    var notes = document.getElementById('admin-catalog-notes').value.trim();
    if (decision !== 'start_review' && notes.length < 10) { show('Write a review note of at least 10 characters.', true); return; }
    if (!confirm('Apply ' + name(decision) + ' to ' + selected.name + '?')) return;
    button.disabled = true;
    try { var result = await request('products.php', 'POST', { product_id: selected.id, decision: decision, notes: notes }); show(result.message); await load(); }
    catch (error) { show(error.message, true); button.disabled = false; }
  }
  load().catch(function (error) { show(error.message, true); });
})();
