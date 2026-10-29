(function () {
  'use strict';
  const ownScript = document.currentScript;
  if (!ownScript || document.getElementById('ci-ai-launcher')) return;
  const endpoint = new URL('../../api/ai/chat.php', ownScript.src).href;
  const css = document.createElement('link');
  css.rel = 'stylesheet'; css.href = new URL('../css/ai-assistant.css', ownScript.src).href;
  document.head.appendChild(css);

  const launcher = document.createElement('button');
  launcher.id = 'ci-ai-launcher'; launcher.type = 'button'; launcher.textContent = 'Chat';
  launcher.setAttribute('aria-label', 'Open Conscious India Assistant'); launcher.setAttribute('aria-expanded', 'false');
  const panel = document.createElement('section'); panel.id = 'ci-ai-panel'; panel.hidden = true; panel.setAttribute('aria-label', 'Conscious India Assistant');
  const title = document.createElement('div'); title.className = 'ci-ai-title'; title.textContent = 'Conscious India Assistant';
  const note = document.createElement('p'); note.className = 'ci-ai-note'; note.textContent = 'Product discovery help. Please do not share payment or personal details.';
  const log = document.createElement('div'); log.className = 'ci-ai-log'; log.setAttribute('aria-live', 'polite');
  const form = document.createElement('form'); form.className = 'ci-ai-form';
  const input = document.createElement('input'); input.required = true; input.maxLength = 500; input.placeholder = 'Ask about crafts or products'; input.setAttribute('aria-label', 'Message');
  const submit = document.createElement('button'); submit.type = 'submit'; submit.textContent = 'Send';
  form.append(input, submit); panel.append(title, note, log, form); document.body.append(panel, launcher);
  function add(text, kind) { const item=document.createElement('p'); item.className='ci-ai-message '+kind; item.textContent=text; log.appendChild(item); log.scrollTop=log.scrollHeight; }
  launcher.addEventListener('click', function () { panel.hidden=!panel.hidden; launcher.setAttribute('aria-expanded', String(!panel.hidden)); if(!panel.hidden) input.focus(); });
  form.addEventListener('submit', async function (event) {
    event.preventDefault(); const message=input.value.trim(); if(!message) return; add(message,'user'); input.value=''; submit.disabled=true;
    try { const response=await fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({message})}); const data=await response.json(); add(data.reply||data.message||'Help is unavailable right now.','assistant'); }
    catch (_) { add('Help is unavailable right now. Please use Contact Us.','assistant'); }
    finally { submit.disabled=false; input.focus(); }
  });
})();
