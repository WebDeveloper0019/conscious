(function () {
  'use strict';

  var wrap = document.getElementById('ac-toast-wrap');
  var sidebar = document.querySelector('.ci-dash-sidebar');
  var menu = document.querySelector('[data-menu]');
  var overlay = document.querySelector('[data-overlay]');

  function toast(message, type) {
    var item = document.createElement('div');
    item.className = 'ac-toast ' + (type || '');
    item.textContent = message;
    if (wrap) wrap.appendChild(item);
    setTimeout(function () { item.remove(); }, 3500);
  }

  function closeNavigation() {
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('show');
    if (menu && window.innerWidth <= 1024) { menu.setAttribute('aria-expanded', 'false'); menu.setAttribute('aria-label', 'Open admin navigation'); }
    document.body.classList.remove('ac-nav-open');
  }

  if (menu) menu.addEventListener('click', function () {
    if (window.innerWidth > 1024) {
      var collapsed = document.body.classList.toggle('ac-sidebar-collapsed');
      menu.setAttribute('aria-expanded', String(!collapsed));
      menu.setAttribute('aria-label', collapsed ? 'Expand admin navigation' : 'Collapse admin navigation');
      try { localStorage.setItem('conscious-admin-sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (error) {}
      return;
    }
    var open = sidebar && sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show', Boolean(open));
    menu.setAttribute('aria-expanded', String(Boolean(open)));
    menu.setAttribute('aria-label', open ? 'Close admin navigation' : 'Open admin navigation');
    document.body.classList.toggle('ac-nav-open', Boolean(open));
  });
  if (overlay) overlay.addEventListener('click', closeNavigation);
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeNavigation(); });

  if (menu && window.innerWidth > 1024) {
    try {
      var savedSidebarState = localStorage.getItem('conscious-admin-sidebar');
      if (savedSidebarState === 'collapsed') {
        document.body.classList.add('ac-sidebar-collapsed');
        menu.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-label', 'Expand admin navigation');
      }
    } catch (error) {}
  }

  document.querySelectorAll('[data-nav-group-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var group = button.closest('[data-nav-group]');
      var items = document.getElementById(button.getAttribute('aria-controls'));
      if (!group || !items) return;
      if (window.innerWidth > 1024 && document.body.classList.contains('ac-sidebar-collapsed')) {
        document.body.classList.remove('ac-sidebar-collapsed');
        if (menu) { menu.setAttribute('aria-expanded', 'true'); menu.setAttribute('aria-label', 'Collapse admin navigation'); }
        try { localStorage.setItem('conscious-admin-sidebar', 'expanded'); } catch (error) {}
      }
      var willOpen = !group.classList.contains('is-open');
      group.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', String(willOpen));
      items.hidden = !willOpen;
    });
  });

  document.querySelectorAll('[data-tabs]').forEach(function (root) {
    var buttons = root.querySelectorAll('[data-tab-target]');
    var panels = root.querySelectorAll('[data-tab-panel]');
    buttons.forEach(function (button) {
      button.setAttribute('role', 'tab');
      button.setAttribute('aria-selected', String(button.classList.contains('active')));
      button.addEventListener('click', function () {
        buttons.forEach(function (item) { item.classList.remove('active'); item.setAttribute('aria-selected', 'false'); });
        panels.forEach(function (panel) { panel.hidden = true; });
        button.classList.add('active'); button.setAttribute('aria-selected', 'true');
        var panel = root.querySelector('[data-tab-panel="' + CSS.escape(button.dataset.tabTarget) + '"]');
        if (panel) panel.hidden = false;
      });
    });
  });

  document.querySelectorAll('[data-open-dialog]').forEach(function (button) {
    button.addEventListener('click', function () { var dialog = document.getElementById(button.dataset.openDialog); if (dialog) dialog.showModal(); });
  });
  document.querySelectorAll('[data-close-dialog]').forEach(function (button) {
    button.addEventListener('click', function () { var dialog = button.closest('dialog'); if (dialog) dialog.close(); });
  });

  function fieldState(input) {
    var field = input.closest('.ac-field');
    if (!field) return;
    var error = field.querySelector('.ac-field-error');
    if (input.validity.valid) { input.removeAttribute('aria-invalid'); if (error) error.remove(); return; }
    if (!error) { error = document.createElement('small'); error.className = 'ac-field-error'; error.setAttribute('role', 'alert'); field.appendChild(error); }
    error.textContent = input.validity.valueMissing ? 'This field is required.' : input.validationMessage;
    input.setAttribute('aria-invalid', 'true');
  }
  document.addEventListener('invalid', function (event) { fieldState(event.target); }, true);
  document.addEventListener('input', function (event) { if (event.target.matches('input,select,textarea')) fieldState(event.target); });

  document.querySelectorAll('form[data-admin-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault(); form.classList.add('ac-validated');
      if (!form.checkValidity()) { form.reportValidity(); toast('Correct the highlighted fields.', 'error'); return; }
      toast(form.dataset.success || 'Fields validated. An authorized backend action is still required.', 'success');
    });
  });

  document.querySelectorAll('[data-demo-action]').forEach(function (button) {
    button.remove();
  });

  function prepareTable(table) {
    var headings = Array.from(table.querySelectorAll('thead th')).map(function (item) { return item.textContent.trim(); });
    table.classList.add('ac-table-responsive');
    table.querySelectorAll('tbody tr').forEach(function (row) {
      Array.from(row.children).forEach(function (cell, index) { if (cell.colSpan > 1) cell.dataset.fullRow = 'true'; else if (headings[index]) cell.dataset.label = headings[index]; });
    });
  }
  document.querySelectorAll('table.ac-table').forEach(prepareTable);
  var observer = new MutationObserver(function (records) { records.forEach(function (record) { var table = record.target.closest && record.target.closest('table.ac-table'); if (table) prepareTable(table); }); });
  document.querySelectorAll('table.ac-table tbody').forEach(function (body) { observer.observe(body, { childList: true, subtree: true }); });
})();
