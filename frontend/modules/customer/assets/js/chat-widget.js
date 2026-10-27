(function () {
  'use strict';

  var ENDPOINT = '/api/ai/chat.php';

  var CSS = [
    '#ci-chat-widget { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: inherit; }',
    '#ci-chat-toggle { background: #1a3c2e; color: #fff; border: none; border-radius: 50px; padding: 12px 20px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 16px rgba(0,0,0,0.2); transition: background 0.2s; }',
    '#ci-chat-toggle:hover { background: #245c45; }',
    '#ci-chat-panel { position: absolute; bottom: 56px; right: 0; width: 320px; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; }',
    '#ci-chat-header { background: #1a3c2e; color: #fff; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 14px; }',
    '#ci-chat-close { background: none; border: none; color: #fff; cursor: pointer; font-size: 18px; line-height: 1; padding: 0; opacity: 0.8; }',
    '#ci-chat-close:hover { opacity: 1; }',
    '#ci-chat-messages { height: 300px; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; }',
    '.ci-msg { max-width: 85%; padding: 8px 12px; border-radius: 10px; font-size: 13px; line-height: 1.5; word-break: break-word; }',
    '.ci-msg-you { background: #e8f4ee; color: #1a1a1a; align-self: flex-end; border-bottom-right-radius: 3px; }',
    '.ci-msg-bot { background: #f4f4f4; color: #1a1a1a; align-self: flex-start; border-bottom-left-radius: 3px; }',
    '#ci-chat-input-row { display: flex; border-top: 1px solid #eee; }',
    '#ci-chat-input { flex: 1; border: none; padding: 12px 14px; font-size: 13px; outline: none; font-family: inherit; }',
    '#ci-chat-send { background: #1a3c2e; color: #fff; border: none; padding: 0 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s; }',
    '#ci-chat-send:hover { background: #245c45; }',
    '#ci-chat-send:disabled { opacity: 0.5; cursor: not-allowed; }',
    '@media (max-width: 400px) { #ci-chat-panel { width: calc(100vw - 32px); right: -8px; } }',
  ].join('\n');

  /**
   * Initialise the customer chat widget.
   * @param {{ productContext?: string }} options
   */
  function initChatWidget(options) {
    options = options || {};
    var productContext = (options.productContext || '').slice(0, 800);

    if (document.getElementById('ci-chat-widget')) return;

    // Inject styles
    var style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    // Build widget HTML
    var widget = document.createElement('div');
    widget.id = 'ci-chat-widget';
    widget.innerHTML =
      '<button id="ci-chat-toggle" aria-label="Open chat assistant" aria-expanded="false">Chat with us</button>' +
      '<div id="ci-chat-panel" hidden aria-label="Chat assistant">' +
        '<div id="ci-chat-header">' +
          '<span>Ask Conscious Indi</span>' +
          '<button id="ci-chat-close" aria-label="Close chat">×</button>' +
        '</div>' +
        '<div id="ci-chat-messages" role="log" aria-live="polite" aria-atomic="false"></div>' +
        '<div id="ci-chat-input-row">' +
          '<input id="ci-chat-input" type="text" placeholder="Ask anything…" maxlength="500" autocomplete="off" aria-label="Your message" />' +
          '<button id="ci-chat-send" aria-label="Send message">Send</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(widget);

    var toggle   = document.getElementById('ci-chat-toggle');
    var panel    = document.getElementById('ci-chat-panel');
    var closeBtn = document.getElementById('ci-chat-close');
    var input    = document.getElementById('ci-chat-input');
    var sendBtn  = document.getElementById('ci-chat-send');
    var messages = document.getElementById('ci-chat-messages');

    function openPanel() {
      panel.hidden = false;
      toggle.setAttribute('aria-expanded', 'true');
      input.focus();
      if (!messages.hasChildNodes()) {
        appendMessage('bot', 'Hi! How can I help you today?');
      }
    }

    function closePanel() {
      panel.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      toggle.focus();
    }

    toggle.addEventListener('click', function () {
      panel.hidden ? openPanel() : closePanel();
    });

    closeBtn.addEventListener('click', closePanel);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !panel.hidden) closePanel();
    });

    async function sendMessage() {
      var text = input.value.trim();
      if (!text || sendBtn.disabled) return;
      input.value = '';
      appendMessage('you', text);

      var thinking = appendMessage('bot', '…');
      sendBtn.disabled = true;

      try {
        var res = await fetch(ENDPOINT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text, product_context: productContext }),
        });
        var data = await res.json();
        thinking.textContent = data.success
          ? data.reply
          : (data.message || 'Something went wrong. Please try again.');
      } catch (_) {
        thinking.textContent = 'Could not connect. Please check your connection and try again.';
      } finally {
        sendBtn.disabled = false;
        input.focus();
      }
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    function appendMessage(role, text) {
      var el = document.createElement('div');
      el.className = 'ci-msg ci-msg-' + role;
      el.textContent = text;
      messages.appendChild(el);
      messages.scrollTop = messages.scrollHeight;
      return el;
    }
  }

  window.initChatWidget = initChatWidget;

})();
