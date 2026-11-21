(function () {
  'use strict';

  var sequence = 0;
  var selector = 'input, select, textarea';
  var excludedTypes = /^(?:hidden|button|submit|reset|image)$/i;
  var feedbackClasses = ['vc-field-error', 'cc-field-error', 'ac-field-error', 'if-field-error'];

  var style = document.createElement('style');
  style.textContent = '[data-inline-invalid="true"]:not([type="checkbox"]):not([type="radio"]){border-color:#A63D24!important;outline:2px solid rgba(166,61,36,.18)!important;outline-offset:1px;box-shadow:0 0 0 3px rgba(166,61,36,.1)!important}' +
    'input[type="checkbox"][data-inline-invalid="true"],input[type="radio"][data-inline-invalid="true"]{outline:2px solid #A63D24!important;outline-offset:2px}' +
    '.ci-inline-error{display:block!important;color:#9F321C!important;font-size:12px!important;font-weight:600!important;line-height:1.4!important;margin:5px 0 0!important}' +
    '.ci-inline-error[hidden]{display:none!important}';
  document.head.appendChild(style);

  function eligible(control) {
    return control && control.matches && control.matches(selector) && !control.closest('[data-inline-managed]') && !excludedTypes.test(control.type || '') && control.willValidate && !control.disabled;
  }

  function labelFor(control) {
    var label = control.labels && control.labels[0];
    if (!label) return '';
    var direct = Array.from(label.childNodes).filter(function (node) { return node.nodeType === 3; }).map(function (node) { return node.textContent; }).join(' ').trim();
    var text = direct || label.textContent.trim();
    return text.replace(/\s*\*\s*/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 65);
  }

  function placeholderFor(control) {
    var label = labelFor(control);
    var lower = label.toLowerCase();
    if (control.type === 'email') return 'name@example.com';
    if (control.type === 'tel') return 'Enter a 10-digit mobile number';
    if (control.type === 'url') return 'https://example.com';
    if (control.type === 'search') return label ? 'Search ' + lower.replace(/^search\s*/, '') : 'Search…';
    if (control.type === 'password') return /confirm|re-enter/i.test(label) ? 'Re-enter your password' : /new|create/i.test(label) ? 'Create a strong password' : 'Enter your password';
    if (!label || label.length > 55 || /^(i |use |allow |offer )/i.test(label)) return '';
    if (control.tagName === 'TEXTAREA') return /^(describe|tell|explain|what)/i.test(label) ? label.replace(/[?.]+$/, '') + '…' : 'Enter ' + lower.replace(/\s*\(optional\)$/i, '') + '…';
    if (control.type === 'number') return 'Enter ' + lower.replace(/\s*\([^)]*\)/g, '');
    return 'Enter ' + lower.replace(/\s*\(optional\)$/i, '');
  }

  function prepare(control) {
    if (!eligible(control)) return;
    if (!control.id) control.id = 'ci-form-control-' + (++sequence);
    if (control.hasAttribute('required')) control.setAttribute('aria-required', 'true');
    if (control.hasAttribute('placeholder') || control.hasAttribute('data-no-placeholder')) return;
    if (control.tagName === 'SELECT') {
      var first = control.options[0];
      if (first && first.value === '' && /^(?:select|choose|please select|--)/i.test(first.textContent.trim()) === false) {
        var label = labelFor(control);
        if (label) first.textContent = 'Select ' + label.toLowerCase();
      }
      return;
    }
    if (control.type === 'file' || control.type === 'checkbox' || control.type === 'radio' || control.type === 'date' || control.type === 'datetime-local' || control.type === 'time' || control.type === 'color' || control.type === 'range') return;
    var hint = placeholderFor(control);
    if (hint) control.placeholder = hint;
  }

  function messageFor(control) {
    var v = control.validity;
    if (v.valueMissing) return control.type === 'checkbox' || control.type === 'radio' ? 'Please select this option to continue.' : 'This field is required.';
    if (v.typeMismatch) return control.type === 'email' ? 'Enter a valid email address.' : control.type === 'url' ? 'Enter a valid URL starting with https://.' : 'Enter a valid value.';
    if (v.tooShort) return 'Enter at least ' + control.minLength + ' characters.';
    if (v.tooLong) return 'Use no more than ' + control.maxLength + ' characters.';
    if (v.rangeUnderflow) return 'Enter a value of at least ' + control.min + '.';
    if (v.rangeOverflow) return 'Enter a value no greater than ' + control.max + '.';
    if (v.stepMismatch) return 'Enter a valid amount.';
    if (v.badInput) return 'Enter a valid number.';
    if (v.patternMismatch) return control.validationMessage || 'Use the requested format.';
    return control.validationMessage || 'Check this field.';
  }

  function containerFor(control) {
    var field = control.closest('.vc-field,.cc-field,.ac-field,.if-field,.ca-field,.form-group,.form-field,.bc-form-field');
    if (field) return field;
    var wrappingLabel = control.closest('label');
    return wrappingLabel && wrappingLabel.contains(control) ? wrappingLabel : null;
  }

  function feedbackFor(control, create) {
    var id = control.id + '-inline-error';
    var existing = document.getElementById(id);
    if (existing || !create) return existing;
    var field = containerFor(control);
    var custom = field && field.querySelector('.ci-inline-error,.vc-field-error,.cc-field-error,.ac-field-error,.if-field-error,.ca-field-error');
    if (custom && (!custom.dataset.inlineOwner || custom.dataset.inlineOwner === control.id)) {
      custom.id = custom.id || id;
      custom.dataset.inlineOwner = control.id;
      custom.classList.add('ci-inline-error');
      return custom;
    }
    var error = document.createElement('small');
    error.id = id;
    error.className = 'ci-inline-error';
    if (document.body.classList.contains('ci-role-vendor')) error.classList.add(feedbackClasses[0]);
    if (document.body.classList.contains('ci-role-customer')) error.classList.add(feedbackClasses[1]);
    if (document.body.classList.contains('ci-role-admin')) error.classList.add(feedbackClasses[2]);
    if (document.body.classList.contains('ci-role-influencer')) error.classList.add(feedbackClasses[3]);
    error.dataset.inlineOwner = control.id;
    error.setAttribute('role', 'alert');
    if (field) field.appendChild(error);
    else (control.closest('.ca-input-wrap,.input-group') || control).insertAdjacentElement('afterend', error);
    return error;
  }

  function show(control) {
    if (!eligible(control)) return true;
    prepare(control);
    var valid = control.validity.valid;
    var error = feedbackFor(control, !valid);
    if (valid) {
      control.removeAttribute('data-inline-invalid');
      control.removeAttribute('aria-invalid');
      if (error) { error.textContent = ''; error.hidden = true; }
      return true;
    }
    control.dataset.inlineInvalid = 'true';
    control.setAttribute('aria-invalid', 'true');
    if (error) {
      error.textContent = messageFor(control);
      error.hidden = false;
      var ids = (control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
      if (!ids.includes(error.id)) ids.push(error.id);
      control.setAttribute('aria-describedby', ids.join(' '));
    }
    return false;
  }

  function prepareTree(root) {
    if (root.matches && root.matches(selector)) prepare(root);
    if (root.querySelectorAll) root.querySelectorAll(selector).forEach(prepare);
  }

  function init() {
    prepareTree(document);
    document.addEventListener('invalid', function (event) { show(event.target); }, true);
    document.addEventListener('input', function (event) {
      if (!eligible(event.target)) return;
      prepare(event.target);
      if (event.target.hasAttribute('data-inline-invalid')) show(event.target);
    }, true);
    document.addEventListener('change', function (event) {
      if (eligible(event.target) && event.target.hasAttribute('data-inline-invalid')) show(event.target);
    }, true);
    document.addEventListener('focusin', function (event) { prepare(event.target); }, true);
    document.addEventListener('submit', function (event) {
      var form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.hasAttribute('data-inline-managed')) return;
      var controls = Array.from(form.elements).filter(eligible);
      var first = null;
      controls.forEach(function (control) { if (!control.validity.valid && !show(control) && !first) first = control; });
      if (first) {
        event.preventDefault();
        event.stopImmediatePropagation();
        first.focus();
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, true);
    var observer = new MutationObserver(function (records) {
      records.forEach(function (record) { record.addedNodes.forEach(function (node) { if (node.nodeType === 1) prepareTree(node); }); });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
