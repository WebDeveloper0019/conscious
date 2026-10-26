(function () {
  'use strict';

  var stateKey = 'conscious-vendor-ui-v1';
  var allowedDemoKeys = ['deliveryRules', 'coverage', 'draftProducts', 'orderUpdates', 'preferences', 'activity', 'completedActions', 'formSubmissions'];

  function safeReadState() {
    try {
      var parsed = JSON.parse(localStorage.getItem(stateKey) || '{}');
      var clean = {};
      allowedDemoKeys.forEach(function (key) { if (Object.prototype.hasOwnProperty.call(parsed, key)) clean[key] = parsed[key]; });
      return clean;
    } catch (error) { return {}; }
  }

  function safeWriteState(next) {
    var clean = {};
    allowedDemoKeys.forEach(function (key) { if (Object.prototype.hasOwnProperty.call(next, key)) clean[key] = next[key]; });
    localStorage.setItem(stateKey, JSON.stringify(clean));
  }

  var demoState = safeReadState();

  function toast(message, type) {
    var wrap = document.getElementById('vc-toast-wrap');
    if (!wrap) return;
    var item = document.createElement('div');
    item.className = 'vc-toast ' + (type || 'success');
    item.textContent = String(message).slice(0, 240);
    wrap.appendChild(item);
    requestAnimationFrame(function () { item.classList.add('show'); });
    setTimeout(function () { item.classList.remove('show'); setTimeout(function () { item.remove(); }, 250); }, 3500);
  }

  function initSidebar() {
    var sidebar = document.getElementById('ci-sidebar');
    var open = document.getElementById('ci-sidebar-hamburger');
    var overlay = document.getElementById('ci-sidebar-overlay');
    if (!sidebar || !open) return;
    function close() { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); open.setAttribute('aria-expanded', 'false'); open.setAttribute('aria-label', 'Open navigation'); document.body.classList.remove('vc-nav-open'); }
    open.addEventListener('click', function () { var isOpen = sidebar.classList.toggle('open'); if (overlay) overlay.classList.toggle('show', isOpen); open.setAttribute('aria-expanded', String(isOpen)); open.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation'); document.body.classList.toggle('vc-nav-open', isOpen); });
    if (overlay) overlay.addEventListener('click', close);
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
  }

  function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach(function (root) {
      var buttons = root.querySelectorAll('[data-tab-target]');
      var panels = root.querySelectorAll('[data-tab-panel]');
      function openNamedTab(name) {
        var button = root.querySelector('[data-tab-target="' + name + '"]');
        if (button) button.click();
      }
      var hashTarget = window.location.hash.slice(1);
      if (hashTarget) setTimeout(function () { openNamedTab(hashTarget); }, 0);
      window.addEventListener('hashchange', function () { openNamedTab(window.location.hash.slice(1)); });
      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          var target = button.getAttribute('data-tab-target');
          buttons.forEach(function (item) { item.classList.toggle('active', item === button); item.setAttribute('aria-selected', String(item === button)); });
          panels.forEach(function (panel) { panel.hidden = panel.getAttribute('data-tab-panel') !== target; });
        });
      });
    });
  }

  function validatePincode(input) {
    var value = input.value.trim();
    input.setCustomValidity(value && !/^[1-9][0-9]{5}$/.test(value) ? 'Enter a valid six-digit Indian PIN code.' : '');
  }

  function validatePhone(input) {
    var value = input.value.replace(/\D/g, '');
    input.setCustomValidity(value && !/^[6-9][0-9]{9}$/.test(value) ? 'Enter a valid 10-digit Indian mobile number.' : '');
  }

  function validateMoney(input) {
    var value = Number(input.value);
    input.setCustomValidity(input.value !== '' && (!Number.isFinite(value) || value < Number(input.min || 0)) ? 'Enter a valid non-negative amount.' : '');
  }

  function initValidation() {
    document.querySelectorAll('[data-pincode]').forEach(function (input) { input.addEventListener('input', function () { validatePincode(input); }); });
    document.querySelectorAll('[data-indian-phone]').forEach(function (input) { input.addEventListener('input', function () { validatePhone(input); }); });
    document.querySelectorAll('input[type="number"][data-money]').forEach(function (input) { input.addEventListener('input', function () { validateMoney(input); }); });
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
      input.addEventListener('change', function () {
        var max = Number(input.dataset.maxBytes || 5242880);
        var accepted = (input.accept || '').split(',').map(function (value) { return value.trim(); }).filter(Boolean);
        Array.from(input.files || []).forEach(function (file) {
          var typeOkay = !accepted.length || accepted.some(function (rule) { return rule.endsWith('/*') ? file.type.startsWith(rule.slice(0, -1)) : file.type === rule || file.name.toLowerCase().endsWith(rule.toLowerCase()); });
          if (!typeOkay) input.setCustomValidity('This file type is not allowed.');
          else if (file.size > max) input.setCustomValidity('File is larger than the allowed limit.');
          else input.setCustomValidity('');
        });
      });
    });
    document.querySelectorAll('form[data-frontend-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        form.classList.add('was-validated');
        if (!form.checkValidity()) { form.querySelector(':invalid')?.focus(); toast('Please correct the highlighted fields.', 'error'); return; }
        var purpose = form.dataset.frontendForm;
        if (purpose === 'delivery-rate') {
          var data = Object.fromEntries(new FormData(form).entries());
          demoState.deliveryRules = Array.isArray(demoState.deliveryRules) ? demoState.deliveryRules : [];
          demoState.deliveryRules.push({ name: String(data.rule_name).slice(0, 80), scope: String(data.scope).slice(0, 80), charge: Number(data.charge), freeAbove: Number(data.free_above || 0) });
          safeWriteState(demoState);
        }
        if (purpose === 'coverage') {
          demoState.coverage = Object.fromEntries(new FormData(form).entries());
          safeWriteState(demoState);
        }
        recordFormCompletion(form, purpose);
        toast(form.dataset.success || 'Saved in this frontend preview.', 'success');
      });
    });
  }

  function initAccessibleForms() {
    var controlIndex = 0;
    function messageFor(input) {
      if (input.validity.valueMissing) return 'This field is required.';
      if (input.validity.typeMismatch) return input.type === 'email' ? 'Enter a valid email address.' : 'Enter a valid value.';
      if (input.validity.patternMismatch) return input.validationMessage || 'Use the requested format.';
      if (input.validity.tooShort) return 'Enter at least ' + input.minLength + ' characters.';
      if (input.validity.tooLong) return 'Use no more than ' + input.maxLength + ' characters.';
      if (input.validity.rangeUnderflow) return 'Enter a value of at least ' + input.min + '.';
      if (input.validity.rangeOverflow) return 'Enter a value no greater than ' + input.max + '.';
      if (input.validity.stepMismatch) return 'Enter a valid increment.';
      return input.validationMessage || 'Check this field.';
    }
    function prepare(input) {
      if (!input.matches('input:not([type="hidden"]),select,textarea')) return;
      if (!input.id) input.id = 'vendor-field-' + (++controlIndex);
      var field = input.closest('.vc-field');
      var label = field && field.querySelector('label:not([for])');
      if (label && !label.contains(input)) label.htmlFor = input.id;
      if (input.required) input.setAttribute('aria-required', 'true');
    }
    function showState(input) {
      prepare(input);
      var field = input.closest('.vc-field');
      if (!field) return;
      var error = field.querySelector('.vc-field-error');
      if (input.validity.valid) {
        input.removeAttribute('aria-invalid');
        if (error) { error.textContent = ''; error.hidden = true; }
        return;
      }
      if (!error) {
        error = document.createElement('small');
        error.className = 'vc-field-error';
        error.id = input.id + '-error';
        error.setAttribute('role', 'alert');
        field.appendChild(error);
      }
      error.textContent = messageFor(input);
      error.hidden = false;
      input.setAttribute('aria-invalid', 'true');
      var described = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
      if (!described.includes(error.id)) described.push(error.id);
      input.setAttribute('aria-describedby', described.join(' '));
    }
    document.querySelectorAll('input,select,textarea').forEach(prepare);
    document.addEventListener('invalid', function (event) { showState(event.target); }, true);
    document.addEventListener('input', function (event) { if (event.target.matches('input,select,textarea') && (event.target.validity.valid || event.target.getAttribute('aria-invalid') === 'true')) showState(event.target); });
    document.addEventListener('change', function (event) { if (event.target.matches('input,select,textarea')) showState(event.target); });
    document.addEventListener('submit', function (event) {
      var form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        var first = form.querySelector(':invalid');
        if (first) { showState(first); setTimeout(function () { first.focus(); }, 0); }
      }
    }, true);
  }

  function initResponsiveTables() {
    function prepare(table) {
      if (!table.classList.contains('vc-table')) return;
      table.classList.add('vc-table-responsive');
      var headings = Array.from(table.querySelectorAll('thead th')).map(function (item) { return item.textContent.trim(); });
      table.querySelectorAll('tbody tr').forEach(function (row) {
        Array.from(row.children).forEach(function (cell, index) {
          if (cell.colSpan > 1) cell.dataset.fullRow = 'true';
          else if (headings[index]) cell.dataset.label = headings[index];
        });
      });
    }
    document.querySelectorAll('table.vc-table').forEach(prepare);
    var observer = new MutationObserver(function (records) {
      records.forEach(function (record) {
        var table = record.target.closest && record.target.closest('table.vc-table');
        if (table) prepare(table);
      });
    });
    document.querySelectorAll('table.vc-table tbody').forEach(function (body) { observer.observe(body, {childList:true, subtree:true}); });
  }

  function initSearchAndFilters() {
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
      var table = document.querySelector(input.dataset.tableSearch);
      if (!table) return;
      input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();
        table.querySelectorAll('tbody tr').forEach(function (row) { row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query); });
      });
    });
    document.querySelectorAll('[data-filter-status]').forEach(function (select) {
      var table = document.querySelector(select.dataset.table);
      if (!table) return;
      select.addEventListener('change', function () {
        table.querySelectorAll('tbody tr').forEach(function (row) { row.hidden = select.value !== '' && row.dataset.status !== select.value; });
      });
    });
  }

  function initPincodeChecker() {
    document.querySelectorAll('[data-pincode-checker]').forEach(function (checker) {
      var output = checker.querySelector('[data-pincode-result]');
      var button = checker.querySelector('[data-check-pincode]');
      if (!button) return;
      button.addEventListener('click', function () {
        var input = checker.querySelector('[data-pincode]');
        validatePincode(input);
        if (!input.checkValidity()) { input.reportValidity(); return; }
        var pin = input.value.trim();
        var excluded = ['682555', '744301'];
        output.textContent = excluded.includes(pin) ? 'Not serviceable: this PIN code is currently excluded.' : 'Serviceable: standard vendor delivery ₹99, estimated 5–9 business days. COD depends on order value.';
        output.className = 'vc-inline-result ' + (excluded.includes(pin) ? 'error' : 'success');
      });
    });
  }

  function initRateCalculator() {
    var form = document.querySelector('[data-rate-calculator]');
    if (!form) return;
    form.addEventListener('input', function () {
      var weight = Math.max(0, Number(form.elements.weight.value || 0));
      var length = Math.max(0, Number(form.elements.length.value || 0));
      var width = Math.max(0, Number(form.elements.width.value || 0));
      var height = Math.max(0, Number(form.elements.height.value || 0));
      var divisor = Math.max(1, Number(form.elements.divisor.value || 5000));
      var volumetric = (length * width * height) / divisor;
      var chargeable = Math.max(weight, volumetric);
      var base = 99 + Math.max(0, Math.ceil(chargeable - 0.5)) * 50;
      form.querySelector('[data-volumetric]').textContent = volumetric.toFixed(2) + ' kg';
      form.querySelector('[data-chargeable]').textContent = chargeable.toFixed(2) + ' kg';
      form.querySelector('[data-estimated-rate]').textContent = '₹' + base.toFixed(2);
    });
  }

  function initCommerceValidation() {
    var mrp = document.getElementById('mrp');
    var sale = document.getElementById('sale-price');
    function checkPrice() {
      if (!mrp || !sale) return;
      sale.setCustomValidity(Number(sale.value) > Number(mrp.value) ? 'Selling price cannot be greater than MRP.' : '');
    }
    if (mrp && sale) { mrp.addEventListener('input', checkPrice); sale.addEventListener('input', checkPrice); }
    document.querySelectorAll('[data-tab-jump]').forEach(function (button) {
      button.addEventListener('click', function () {
        var root = button.closest('[data-tabs]');
        var targetButton = root && root.querySelector('[data-tab-target="' + button.dataset.tabJump + '"]');
        if (targetButton) { targetButton.click(); targetButton.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
      });
    });
  }

  function addActivity(label, detail) {
    demoState.activity = Array.isArray(demoState.activity) ? demoState.activity : [];
    demoState.activity.unshift({ label: String(label).slice(0, 100), detail: String(detail || 'Completed in frontend preview').slice(0, 180), at: new Date().toISOString() });
    demoState.activity = demoState.activity.slice(0, 25);
    safeWriteState(demoState);
    renderDemoActivity();
  }

  function recordFormCompletion(form, purpose) {
    demoState.formSubmissions = Array.isArray(demoState.formSubmissions) ? demoState.formSubmissions : [];
    demoState.formSubmissions = demoState.formSubmissions.filter(function (item) { return item.purpose !== purpose; });
    demoState.formSubmissions.unshift({ purpose: purpose, at: new Date().toISOString() });
    demoState.formSubmissions = demoState.formSubmissions.slice(0, 40);
    addActivity('Saved ' + purpose.replace(/-/g, ' '), 'Validated and saved for this browser preview.');
    form.classList.add('vc-form-saved');
    var submit = form.querySelector('[type="submit"]');
    if (submit) { submit.dataset.originalLabel = submit.dataset.originalLabel || submit.textContent; submit.textContent = 'Saved in preview'; }
    var dialog = form.closest('dialog');
    if (dialog) setTimeout(function () { dialog.close(); }, 450);
  }

  function downloadPreview(action) {
    var csv = 'Conscious Indi Vendor Preview\nAction,' + action.replace(/,/g, ' ') + '\nGenerated,' + new Date().toLocaleString('en-IN') + '\nStatus,Demo file generated successfully\n';
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = action.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') + '-demo.csv';
    document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(link.href);
    addActivity(action, 'Demo download created.'); toast(action + ' downloaded as a demo file.', 'success');
  }

  function actionStatus(action) {
    if (/accept order/i.test(action)) return 'Accepted';
    if (/approve cancellation/i.test(action)) return 'Approved';
    if (/approve return/i.test(action)) return 'Return approved';
    if (/reject/i.test(action)) return 'Rejected';
    if (/quality check/i.test(action)) return 'Quality checked';
    if (/packed/i.test(action)) return 'Packed';
    if (/restock/i.test(action)) return 'Restock recorded';
    if (/submit product/i.test(action)) return 'Under review';
    if (/waitlist/i.test(action)) return 'Waitlisted';
    if (/re-delivery/i.test(action)) return 'Re-delivery scheduled';
    if (/RTO/i.test(action)) return 'RTO updated';
    if (/two-factor/i.test(action)) return '2FA setup started';
    return 'Updated';
  }

  function completeDemoAction(button, action, detail) {
    var isReadOnly = /^(View|Open|Preview|Review|Manage)/i.test(action);
    if (!isReadOnly) {
      button.classList.add('vc-demo-complete');
      var scope = button.closest('tr, .vc-card, .vc-list-item');
      var status = scope && scope.querySelector('.vc-status');
      if (status) { status.textContent = actionStatus(action); status.className = 'vc-status success'; }
    }
    if (/mark all notifications read/i.test(action)) document.querySelectorAll('.vc-dot').forEach(function (dot) { dot.remove(); });
    demoState.completedActions = Array.isArray(demoState.completedActions) ? demoState.completedActions : [];
    if (!demoState.completedActions.includes(action)) demoState.completedActions.unshift(action);
    demoState.completedActions = demoState.completedActions.slice(0, 60);
    addActivity(action, detail || (isReadOnly ? 'Opened in frontend preview.' : 'Status updated in frontend preview.'));
    toast(action + (isReadOnly ? ' opened.' : ' completed.'), isReadOnly ? 'info' : 'success');
  }

  function appendDemoField(container, label, type, required) {
    var wrap = document.createElement('div'); wrap.className = 'vc-demo-field full';
    var caption = document.createElement('label'); caption.textContent = label;
    var control = type === 'textarea' ? document.createElement('textarea') : document.createElement('input');
    control.className = type === 'textarea' ? 'vc-textarea' : 'vc-input'; control.required = Boolean(required);
    if (type !== 'textarea') control.type = type || 'text';
    control.name = 'demo_detail'; control.placeholder = required ? 'Required for this demo action' : 'Optional note';
    wrap.append(caption, control); container.appendChild(wrap); return control;
  }

  function openDemoAction(button) {
    var action = button.dataset.demoAction || 'Vendor action';
    if (/^(Download|Export|Print)/i.test(action) || /template/i.test(action)) { downloadPreview(action); completeDemoAction(button, action, 'Demo file generated.'); return; }
    var dialog = document.getElementById('vc-demo-dialog'); var title = document.getElementById('vc-demo-title');
    var summary = document.getElementById('vc-demo-summary'); var fields = document.getElementById('vc-demo-fields'); var confirm = document.getElementById('vc-demo-confirm');
    if (!dialog || !title || !summary || !fields || !confirm) return;
    title.textContent = action; fields.replaceChildren();
    summary.textContent = /^(View|Open|Preview|Review|Manage)/i.test(action) ? 'This preview shows how the vendor reaches this information. The backend will replace the sample records.' : 'Complete this sample action to see the dashboard state change. No real order, payment or customer is affected.';
    var input;
    if (/global search/i.test(action)) { input = appendDemoField(fields, 'Search products, orders, customers or help', 'search', true); confirm.textContent = 'Search preview'; }
    else if (/account menu/i.test(action)) { input = appendDemoField(fields, 'Choose destination: profile, security, staff or logout', 'text', false); input.value = 'Account settings'; confirm.textContent = 'Open account settings'; }
    else if (/reject|report|deactivation|closure|stop new orders|dispute/i.test(action)) { input = appendDemoField(fields, 'Reason and internal note', 'textarea', true); confirm.textContent = 'Confirm action'; }
    else if (/reply|answer|invite|attach/i.test(action)) { input = appendDemoField(fields, 'Message or recipient details', 'textarea', true); confirm.textContent = 'Save response'; }
    else if (/^(View|Open|Preview|Review|Manage)/i.test(action)) { confirm.textContent = 'Done'; }
    else { input = appendDemoField(fields, 'Optional note', 'textarea', false); confirm.textContent = 'Complete demo action'; }
    confirm.onclick = function () {
      if (input && input.required && !input.value.trim()) { input.focus(); input.reportValidity(); return; }
      if (/global search/i.test(action)) { var query = input.value.toLowerCase(); var route = /order/.test(query) ? 'orders.php' : /product|catalog/.test(query) ? 'catalog.php' : /delivery|shipping/.test(query) ? 'delivery.php' : /payment|earning|payout/.test(query) ? 'finance.php' : 'dashboard.php'; completeDemoAction(button, action, 'Search: ' + input.value); dialog.close(); location.href = route; return; }
      if (/account menu/i.test(action)) { completeDemoAction(button, action, input ? input.value : ''); dialog.close(); location.href = 'account.php'; return; }
      completeDemoAction(button, action, input ? input.value : ''); dialog.close();
    };
    dialog.showModal(); setTimeout(function () { if (input) input.focus(); }, 50);
  }

  function restoreCompletedActions() {
    var completed = Array.isArray(demoState.completedActions) ? demoState.completedActions : [];
    document.querySelectorAll('[data-demo-action]').forEach(function (button) { if (completed.includes(button.dataset.demoAction)) button.classList.add('vc-demo-complete'); });
    var submitted = Array.isArray(demoState.formSubmissions) ? demoState.formSubmissions : [];
    document.querySelectorAll('form[data-frontend-form]').forEach(function (form) { if (submitted.some(function (item) { return item.purpose === form.dataset.frontendForm; })) form.classList.add('vc-form-saved'); });
  }

  function renderDemoActivity() {
    var container = document.querySelector('[data-demo-activity]');
    if (!container) return;
    var activity = Array.isArray(demoState.activity) ? demoState.activity : [];
    if (!activity.length) return;
    container.replaceChildren();
    activity.slice(0, 6).forEach(function (item) {
      var row = document.createElement('div'); row.className = 'vc-demo-activity-item';
      var title = document.createElement('b'); title.textContent = item.label;
      var detail = document.createElement('span'); detail.textContent = item.detail;
      var time = document.createElement('small'); time.textContent = new Date(item.at).toLocaleString('en-IN');
      row.append(title, detail, time); container.appendChild(row);
    });
  }

  function initDemoReset() {
    document.querySelectorAll('[data-reset-vendor-demo]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!window.confirm('Clear all vendor preview actions and saved demo preferences?')) return;
        localStorage.removeItem(stateKey); sessionStorage.removeItem('ci_vendor_onboarding_v2');
        toast('Vendor preview reset. Reloading the clean dashboard.', 'success');
        setTimeout(function () { location.reload(); }, 500);
      });
    });
  }

  function initDialogActions() {
    document.querySelectorAll('[data-open-dialog]').forEach(function (button) { button.addEventListener('click', function () { document.getElementById(button.dataset.openDialog)?.showModal(); }); });
    document.querySelectorAll('[data-close-dialog]').forEach(function (button) { button.addEventListener('click', function () { button.closest('dialog')?.close(); }); });
    document.querySelectorAll('[data-close-demo-dialog]').forEach(function (button) { button.addEventListener('click', function () { document.getElementById('vc-demo-dialog')?.close(); }); });
    document.querySelectorAll('[data-demo-action]').forEach(function (button) { button.addEventListener('click', function () { openDemoAction(button); }); });
    restoreCompletedActions();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar(); initTabs(); initValidation(); initAccessibleForms(); initResponsiveTables(); initCommerceValidation(); initSearchAndFilters(); initPincodeChecker(); initRateCalculator(); initDialogActions(); initDemoReset(); renderDemoActivity();
  });
})();
