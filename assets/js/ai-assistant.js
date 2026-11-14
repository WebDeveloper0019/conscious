(function () {
  'use strict';

  const ownScript = document.currentScript;
  if (!ownScript || document.getElementById('ci-ai-launcher')) return;
  const endpoint = new URL('../../api/ai/chat.php', ownScript.src).href;
  const avatarUrl = new URL('../images/assistant/website-guide.png?v=20261114-puji1', ownScript.src).href;
  const css = document.createElement('link');
  css.rel = 'stylesheet'; css.href = new URL('../css/ai-assistant.css?v=20261114-puji1', ownScript.src).href;
  document.head.appendChild(css);

  function node(tag, className, text) {
    const item = document.createElement(tag);
    if (className) item.className = className;
    if (text !== undefined) item.textContent = text;
    return item;
  }

  function icon(name) {
    const item = node('i', 'fa-solid ' + name);
    item.setAttribute('aria-hidden', 'true');
    return item;
  }

  function avatar(className) {
    const frame = node('span', className);
    const image = node('img');
    image.src = avatarUrl; image.alt = '';
    frame.append(image);
    return frame;
  }

  const root = node('div', 'ci-guide');
  const launcher = node('button', 'ci-guide-launcher');
  launcher.id = 'ci-ai-launcher'; launcher.type = 'button'; launcher.title = 'Ask Puji';
  launcher.setAttribute('aria-label', 'Ask Puji');
  launcher.setAttribute('aria-expanded', 'false'); launcher.setAttribute('aria-controls', 'ci-ai-panel');
  const launcherImage = node('img'); launcherImage.src = avatarUrl; launcherImage.alt = '';
  const launcherBadge = node('span', 'ci-guide-launcher-badge'); launcherBadge.append(icon('fa-message'));
  launcher.append(launcherImage, launcherBadge);

  const panel = node('section', 'ci-guide-panel');
  panel.id = 'ci-ai-panel'; panel.hidden = true;
  panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-modal', 'false');
  panel.setAttribute('aria-labelledby', 'ci-ai-title');
  const head = node('header', 'ci-guide-head');
  const headCopy = node('div');
  const title = node('strong', '', 'Puji'); title.id = 'ci-ai-title';
  headCopy.append(title, node('small', '', 'Your CI Assistant'));
  const headActions = node('div', 'ci-guide-head-actions');
  const restart = node('button', 'ci-guide-icon-button');
  restart.type = 'button'; restart.title = 'Start over';
  restart.setAttribute('aria-label', 'Start a new conversation'); restart.append(icon('fa-rotate-right'));
  const close = node('button', 'ci-guide-icon-button');
  close.type = 'button'; close.title = 'Minimise';
  close.setAttribute('aria-label', 'Minimise Puji'); close.append(icon('fa-minus'));
  headActions.append(restart, close);
  head.append(avatar('ci-guide-head-avatar'), headCopy, headActions);

  const body = node('div', 'ci-guide-body');
  const log = node('ol', 'ci-guide-log');
  log.setAttribute('role', 'log'); log.setAttribute('aria-live', 'polite'); log.setAttribute('aria-relevant', 'additions');
  body.append(log);
  const compose = node('footer', 'ci-guide-compose');
  const form = node('form', 'ci-guide-form');
  const field = node('div', 'ci-guide-field');
  const label = node('label', 'ci-guide-sr-only', 'Message Puji'); label.htmlFor = 'ci-ai-input';
  const input = node('textarea', 'ci-guide-input');
  input.id = 'ci-ai-input'; input.name = 'message'; input.rows = 1; input.required = true; input.maxLength = 500;
  input.placeholder = 'Ask about crafts or products';
  field.append(label, input);
  const submit = node('button', 'ci-guide-send');
  submit.type = 'submit'; submit.setAttribute('aria-label', 'Send message'); submit.append(icon('fa-arrow-up'));
  form.append(field, submit);
  const note = node('p', 'ci-guide-disclosure');
  note.append(icon('fa-circle-info'), document.createTextNode('Product discovery help. Please do not share payment or personal details.'));
  compose.append(form, note); panel.append(head, body, compose); root.append(panel, launcher);
  document.body.append(root);
  if (/checkout(?:\.php|\.html|\/|$)/i.test(location.pathname)) document.body.classList.add('ci-guide-checkout');

  let busy = false;
  let conversation = 0;

  function scrollEnd() { body.scrollTop = body.scrollHeight; }

  function add(text, kind) {
    const entry = node('li', 'ci-guide-entry ' + kind);
    if (kind !== 'user') entry.append(avatar('ci-guide-entry-avatar'));
    const bubble = node('div', 'ci-guide-bubble');
    bubble.textContent = text;
    entry.append(bubble); log.append(entry); scrollEnd();
    return bubble;
  }

  function welcome() {
    log.replaceChildren();
    const bubble = add('Namaste. I’m Puji, your CI Assistant. What would you like to explore?', 'assistant');
    const choices = node('div', 'ci-guide-choices');
    [
      ['Find products', 'Show available products'],
      ['Upcoming events', 'Show upcoming events'],
      ['Track an order', 'How do I track my order?'],
      ['Selling and partnerships', 'How can I sell as a brand or artisan?']
    ].forEach(function (choice) {
      const button = node('button', 'ci-guide-choice', choice[0]);
      button.type = 'button'; button.dataset.question = choice[1]; choices.append(button);
    });
    bubble.append(choices);
    scrollEnd();
  }

  function setBusy(value) {
    busy = value; submit.disabled = value;
    panel.setAttribute('aria-busy', String(value));
    root.querySelectorAll('[data-question]').forEach(function (button) { button.disabled = value; });
  }

  function openPanel() {
    panel.hidden = false; launcher.hidden = true;
    launcher.setAttribute('aria-expanded', 'true'); input.focus(); scrollEnd();
  }

  function closePanel() {
    panel.hidden = true; launcher.hidden = false;
    launcher.setAttribute('aria-expanded', 'false'); launcher.focus();
  }

  async function sendMessage(value) {
    const message = String(value || '').trim();
    if (!message || busy) return;
    const activeConversation = conversation;
    add(message, 'user'); input.value = ''; input.style.height = ''; setBusy(true);
    const pending = add('Puji is thinking…', 'assistant');
    pending.classList.add('ci-guide-loading');
    try {
      const response = await fetch(endpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({message})});
      const data = await response.json();
      if (activeConversation === conversation) {
        pending.parentElement.remove();
        add(data.reply || data.message || 'Help is unavailable right now.', 'assistant');
      }
    } catch (_) {
      if (activeConversation === conversation) {
        pending.parentElement.remove();
        add('Help is unavailable right now. Please use Contact Us.', 'assistant');
      }
    } finally {
      if (activeConversation === conversation) {
        setBusy(false);
        if (!panel.hidden) input.focus();
      }
    }
  }

  launcher.addEventListener('click', openPanel);
  close.addEventListener('click', closePanel);
  restart.addEventListener('click', function () { conversation += 1; welcome(); setBusy(false); input.focus(); });
  root.addEventListener('click', function (event) {
    const button = event.target.closest('[data-question]');
    if (button) sendMessage(button.dataset.question);
  });
  form.addEventListener('submit', function (event) { event.preventDefault(); sendMessage(input.value); });
  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); form.requestSubmit(); }
  });
  input.addEventListener('input', function () { input.style.height = 'auto'; input.style.height = Math.min(92, input.scrollHeight) + 'px'; });
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !panel.hidden) closePanel(); });
  welcome();
})();
