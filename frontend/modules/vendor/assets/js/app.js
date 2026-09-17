(function () {
  'use strict';

  var stateKey = 'conscious-vendor-ui-v1';
  var allowedDemoKeys = ['deliveryRules', 'coverage', 'draftProducts', 'orderUpdates', 'preferences'];

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
    function close() { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); open.setAttribute('aria-expanded', 'false'); document.body.classList.remove('vc-nav-open'); }
    open.addEventListener('click', function () { var isOpen = sidebar.classList.toggle('open'); if (overlay) overlay.classList.toggle('show', isOpen); open.setAttribute('aria-expanded', String(isOpen)); document.body.classList.toggle('vc-nav-open', isOpen); });
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
        toast(form.dataset.success || 'Saved in this frontend preview.', 'success');
      });
    });
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

  function initDialogActions() {
    document.querySelectorAll('[data-open-dialog]').forEach(function (button) { button.addEventListener('click', function () { document.getElementById(button.dataset.openDialog)?.showModal(); }); });
    document.querySelectorAll('[data-close-dialog]').forEach(function (button) { button.addEventListener('click', function () { button.closest('dialog')?.close(); }); });
    document.querySelectorAll('[data-demo-action]').forEach(function (button) { button.addEventListener('click', function () { toast(button.dataset.demoAction + ' is ready for backend connection.', 'info'); }); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar(); initTabs(); initValidation(); initCommerceValidation(); initSearchAndFilters(); initPincodeChecker(); initRateCalculator(); initDialogActions();
  });
})();
