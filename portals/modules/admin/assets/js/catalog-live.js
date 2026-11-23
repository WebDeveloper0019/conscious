(function () {
  'use strict';
  var status = document.getElementById('admin-catalog-status');
  var message = document.getElementById('admin-catalog-message');
  var listBox = document.getElementById('admin-catalog-list');
  if (!status || !message || !listBox) return;
  function text(tag, value, className) { var node = document.createElement(tag); node.textContent = String(value); if (className) node.className = className; return node; }
  function show(value, error) { message.hidden = false; message.textContent = value; message.classList.toggle('danger', Boolean(error)); }
  function name(value) { return String(value || '').replaceAll('_', ' '); }
  async function load() {
    var response = await fetch('/conscious/api/admin/products.php?status=' + encodeURIComponent(status.value), { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
    var result = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(result.message || 'Unable to load products.');
    listBox.replaceChildren();
    if (!result.products.length) { listBox.append(text('p', 'No products in this review state.')); show('No matching product submissions.'); return; }
    result.products.forEach(function (product) {
      var row = text('div', '', 'ac-info-row');
      var details = text('div', product.name + ' · ' + product.sku + ' · ' + product.vendor_name + ' · ' + name(product.status));
      var link = text('a', 'Open review', 'ac-btn small');
      link.href = '/conscious/admin/catalog/' + encodeURIComponent(product.id);
      row.append(details, link);
      listBox.append(row);
    });
    show(result.products.length + ' product submission' + (result.products.length === 1 ? '' : 's') + ' shown.');
  }
  document.getElementById('admin-catalog-refresh').addEventListener('click', function () { load().catch(function (error) { show(error.message, true); }); });
  status.addEventListener('change', function () { load().catch(function (error) { show(error.message, true); }); });
  load().catch(function (error) { show(error.message, true); });
})();
