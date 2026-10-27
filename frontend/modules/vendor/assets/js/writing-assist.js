(function () {
  'use strict';

  var ACTIONS = [
    { key: 'improve',           label: 'Improve writing' },
    { key: 'shorten',           label: 'Make it shorter' },
    { key: 'translate_hindi',   label: 'Translate to Hindi' },
    { key: 'translate_english', label: 'Translate to English' },
    { key: 'suggest_tags',      label: 'Suggest tags' },
  ];

  var ENDPOINT = '/api/ai/writing-assist.php';

  /**
   * Attach the AI writing toolbar to a textarea.
   * Call manually or use the data-writing-assist attribute for auto-init.
   *
   * @param {string|HTMLTextAreaElement} target - CSS selector or element
   */
  function initWritingAssist(target) {
    var textarea = typeof target === 'string' ? document.querySelector(target) : target;
    if (!textarea || textarea.dataset.waInitialised) return;
    textarea.dataset.waInitialised = '1';

    var wrapper = document.createElement('div');
    wrapper.className = 'wa-wrapper';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(textarea);

    var toolbar = document.createElement('div');
    toolbar.className = 'wa-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'AI writing assistant');

    var btnHTML = ACTIONS.map(function (a) {
      return '<button type="button" class="wa-btn" data-action="' + a.key + '">' + escHtml(a.label) + '</button>';
    }).join('');

    toolbar.innerHTML =
      '<span class="wa-label">AI Assist</span>' +
      btnHTML +
      '<span class="wa-status" aria-live="polite"></span>';

    wrapper.appendChild(toolbar);

    var statusEl = toolbar.querySelector('.wa-status');

    toolbar.addEventListener('click', function (e) {
      var btn = e.target.closest('.wa-btn');
      if (!btn || btn.disabled) return;
      handleAction(btn.dataset.action, textarea, toolbar, statusEl);
    });
  }

  function handleAction(action, textarea, toolbar, statusEl) {
    var text = textarea.value.trim();
    if (!text) {
      setStatus(statusEl, 'Type something first.', 'warn');
      return;
    }

    setAllBtns(toolbar, true);
    setStatus(statusEl, 'Thinking…', 'loading');

    fetch(ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text: text, action: action }),
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data.success) {
          setStatus(statusEl, data.message || 'Something went wrong.', 'error');
          return;
        }
        if (action === 'suggest_tags') {
          setStatus(statusEl, 'Suggested tags: ' + data.result, 'info');
        } else {
          showSuggestion(textarea, toolbar, statusEl, data.result);
        }
      })
      .catch(function () {
        setStatus(statusEl, 'Could not reach AI. Please try again.', 'error');
      })
      .finally(function () {
        setAllBtns(toolbar, false);
      });
  }

  function showSuggestion(textarea, toolbar, statusEl, suggestion) {
    removeSuggestion(toolbar);

    var panel = document.createElement('div');
    panel.className = 'wa-suggestion';
    panel.innerHTML =
      '<p class="wa-suggestion-label">AI suggestion — review before applying:</p>' +
      '<p class="wa-suggestion-text">' + escHtml(suggestion) + '</p>' +
      '<div class="wa-suggestion-actions">' +
        '<button type="button" class="wa-accept">Use this</button>' +
        '<button type="button" class="wa-discard">Discard</button>' +
      '</div>';

    toolbar.parentNode.insertBefore(panel, toolbar.nextSibling);
    setStatus(statusEl, '', '');

    panel.querySelector('.wa-accept').addEventListener('click', function () {
      textarea.value = suggestion;
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
      removeSuggestion(toolbar);
      setStatus(statusEl, 'Applied.', 'success');
      setTimeout(function () { setStatus(statusEl, '', ''); }, 3000);
    });

    panel.querySelector('.wa-discard').addEventListener('click', function () {
      removeSuggestion(toolbar);
    });
  }

  function removeSuggestion(toolbar) {
    var existing = toolbar.parentNode.querySelector('.wa-suggestion');
    if (existing) existing.remove();
  }

  function setStatus(el, msg, type) {
    el.textContent = msg;
    el.className = 'wa-status' + (type ? ' wa-status-' + type : '');
  }

  function setAllBtns(toolbar, disabled) {
    toolbar.querySelectorAll('.wa-btn').forEach(function (b) { b.disabled = disabled; });
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Auto-init any textarea with data-writing-assist attribute
  function autoInit() {
    document.querySelectorAll('textarea[data-writing-assist]').forEach(function (el) {
      initWritingAssist(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
  } else {
    autoInit();
  }

  window.initWritingAssist = initWritingAssist;

})();
