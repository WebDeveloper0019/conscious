(function () {
  'use strict';

  var toastWrap = document.getElementById('cc-toast-wrap');
  var menu = document.querySelector('[data-menu]');
  var overlay = document.querySelector('[data-overlay]');

  function toast(message, type) {
    var item = document.createElement('div');
    item.className = 'cc-toast ' + (type || '');
    item.textContent = message;
    if (toastWrap) toastWrap.appendChild(item);
    setTimeout(function () { item.remove(); }, 3600);
  }

  function closeNavigation() {
    document.body.classList.remove('cc-nav-open');
    if (menu) {
      menu.setAttribute('aria-expanded', 'false');
      menu.setAttribute('aria-label', 'Open account navigation');
    }
  }

  if (menu) menu.addEventListener('click', function () {
    var open = !document.body.classList.contains('cc-nav-open');
    document.body.classList.toggle('cc-nav-open', open);
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Close account navigation' : 'Open account navigation');
  });
  if (overlay) overlay.addEventListener('click', closeNavigation);
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeNavigation(); });

  document.querySelectorAll('[data-tabs]').forEach(function (root) {
    var buttons = root.querySelectorAll('[data-tab-target]');
    var panels = root.querySelectorAll('[data-tab-panel]');
    buttons.forEach(function (button) {
      button.setAttribute('role', 'tab');
      button.setAttribute('aria-selected', String(button.classList.contains('active')));
      button.addEventListener('click', function () {
        buttons.forEach(function (item) { item.classList.remove('active'); item.setAttribute('aria-selected', 'false'); });
        panels.forEach(function (panel) { panel.hidden = true; });
        button.classList.add('active');
        button.setAttribute('aria-selected', 'true');
        var panel = root.querySelector('[data-tab-panel="' + CSS.escape(button.dataset.tabTarget) + '"]');
        if (panel) panel.hidden = false;
      });
    });
  });

  document.querySelectorAll('[data-open-dialog]').forEach(function (button) {
    button.addEventListener('click', function () {
      var dialog = document.getElementById(button.dataset.openDialog);
      if (dialog) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-dialog]').forEach(function (button) {
    button.addEventListener('click', function () { var dialog = button.closest('dialog'); if (dialog) dialog.close(); });
  });

  function fieldState(input) {
    var field = input.closest('.cc-field');
    if (!field) return;
    var error = field.querySelector('.cc-field-error');
    if (input.validity.valid) {
      input.removeAttribute('aria-invalid');
      if (error) error.remove();
      return;
    }
    if (!error) {
      error = document.createElement('small');
      error.className = 'cc-field-error';
      error.setAttribute('role', 'alert');
      field.appendChild(error);
    }
    error.textContent = input.validity.valueMissing ? 'This field is required.' : input.validationMessage;
    input.setAttribute('aria-invalid', 'true');
  }
  document.addEventListener('invalid', function (event) { fieldState(event.target); }, true);
  document.addEventListener('input', function (event) {
    var input = event.target;
    if (!input.matches('input,select,textarea')) return;
    if (input.matches('[data-indian-phone]')) input.setCustomValidity(/^[6-9][0-9]{9}$/.test(input.value.trim()) ? '' : 'Enter a valid 10-digit Indian mobile number.');
    if (input.matches('[data-pincode]')) input.setCustomValidity(/^[1-9][0-9]{5}$/.test(input.value.trim()) ? '' : 'Enter a valid six-digit Indian PIN code.');
    fieldState(input);
  });

  document.querySelectorAll('[data-customer-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      form.querySelectorAll('[data-indian-phone]').forEach(function (input) {
        input.setCustomValidity(/^[6-9][0-9]{9}$/.test(input.value.trim()) ? '' : 'Enter a valid 10-digit Indian mobile number.');
      });
      form.querySelectorAll('[data-pincode]').forEach(function (input) {
        input.setCustomValidity(/^[1-9][0-9]{5}$/.test(input.value.trim()) ? '' : 'Enter a valid six-digit Indian PIN code.');
      });
      form.querySelectorAll('input[type=file]').forEach(function (input) {
        input.setCustomValidity('');
        var maximum = Number(input.dataset.maxBytes || 5242880);
        if (Array.from(input.files || []).some(function (file) { return file.size > maximum; })) input.setCustomValidity('A selected file is too large.');
      });
      if (!form.checkValidity()) {
        form.querySelectorAll('input,select,textarea').forEach(fieldState);
        form.reportValidity();
        toast('Please correct the highlighted fields.', 'error');
        return;
      }
      toast(form.dataset.success || 'Fields validated. Connect the separate backend to complete this action.', 'success');
    });
  });

  document.querySelectorAll('[data-demo-action]').forEach(function (button) {
    button.addEventListener('click', function () { toast(button.dataset.demoAction + ' is ready for backend connection.'); });
  });
  document.querySelectorAll('[data-copy]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (navigator.clipboard) navigator.clipboard.writeText(button.dataset.copy);
      toast('Copied.', 'success');
    });
  });
  document.querySelectorAll('[data-qty]').forEach(function (box) {
    var input = box.querySelector('input');
    box.querySelectorAll('button').forEach(function (button) {
      button.addEventListener('click', function () {
        var next = Number(input.value || 1) + Number(button.dataset.step);
        input.value = Math.max(Number(input.min || 1), Math.min(Number(input.max || 99), next));
      });
    });
  });
})();
