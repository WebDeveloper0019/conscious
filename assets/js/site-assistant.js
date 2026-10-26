(function () {
  'use strict';

  if (window.__consciousIndiGuideLoaded) return;
  window.__consciousIndiGuideLoaded = true;

  var script = document.currentScript;
  if (!script) return;
  var siteRoot = new URL('../../', script.src);
  var apiUrl = new URL('../../api/public/assistant.php', script.src);
  var avatarUrl = new URL('../images/assistant/website-guide.png', script.src);
  var context = { intent: '', products: {}, events: {}, lastQuestion: '' };
  var busy = false;

  function node(tag, className, text) {
    var item = document.createElement(tag);
    if (className) item.className = className;
    if (text !== undefined) item.textContent = text;
    return item;
  }
  function icon(name) {
    var item = node('i', 'fa-solid ' + name);
    item.setAttribute('aria-hidden', 'true');
    return item;
  }
  function link(path) { return new URL(path, siteRoot).href; }
  function normalize(value) {
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9₹]+/g, ' ').replace(/\s+/g, ' ').trim();
  }
  function similarity(a, b) {
    a = normalize(a); b = normalize(b);
    if (!a || !b) return 0;
    var matrix = Array.from({length: a.length + 1}, function () { return []; });
    for (var i = 0; i <= a.length; i += 1) matrix[i][0] = i;
    for (var j = 0; j <= b.length; j += 1) matrix[0][j] = j;
    for (i = 1; i <= a.length; i += 1) for (j = 1; j <= b.length; j += 1) {
      matrix[i][j] = Math.min(matrix[i - 1][j] + 1, matrix[i][j - 1] + 1, matrix[i - 1][j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
    }
    return 1 - matrix[a.length][b.length] / Math.max(a.length, b.length);
  }
  function hasWord(text, words) {
    var tokens = normalize(text).split(' ').filter(Boolean);
    return words.some(function (word) {
      var normalized = normalize(word);
      if (normalized.includes(' ') && normalize(text).includes(normalized)) return true;
      return tokens.some(function (token) { return token === normalized || (Math.max(token.length, normalized.length) >= 4 && similarity(token, normalized) >= .72); });
    });
  }
  function titleCase(value) { return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, function (letter) { return letter.toUpperCase(); }); }
  function money(value) { return new Intl.NumberFormat('en-IN', {style:'currency',currency:'INR',maximumFractionDigits:0}).format(Number(value) || 0); }
  function dateTime(value) {
    if (!value) return '';
    var date = new Date(String(value).replace(' ', 'T') + (String(value).endsWith('Z') ? '' : 'Z'));
    if (Number.isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat('en-IN', {day:'numeric',month:'short',year:'numeric',hour:'numeric',minute:'2-digit',timeZone:'Asia/Kolkata'}).format(date);
  }
  function safeImage(raw) {
    if (!raw) return '';
    try { var url = new URL(raw, apiUrl); return url.origin === location.origin ? url.href : ''; } catch (error) { return ''; }
  }

  var root = node('div', 'ci-guide');
  root.setAttribute('data-ci-guide', '');
  var launcher = node('button', 'ci-guide-launcher');
  launcher.type = 'button'; launcher.setAttribute('aria-label', 'Open Conscious Indi website guide'); launcher.setAttribute('aria-expanded', 'false'); launcher.setAttribute('aria-controls', 'ci-guide-panel');
  var launcherImage = node('img'); launcherImage.src = avatarUrl.href; launcherImage.alt = '';
  var launcherBadge = node('span', 'ci-guide-launcher-badge'); launcherBadge.append(icon('fa-message'));
  launcher.append(launcherImage, launcherBadge);
  var panel = node('section', 'ci-guide-panel'); panel.id = 'ci-guide-panel'; panel.hidden = true; panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-modal', 'false'); panel.setAttribute('aria-labelledby', 'ci-guide-title');
  var head = node('header', 'ci-guide-head');
  var headAvatar = node('span', 'ci-guide-head-avatar'), headImage = node('img'); headImage.src = avatarUrl.href; headImage.alt = ''; headAvatar.append(headImage);
  var headCopy = node('div'); var headTitle = node('strong', '', 'Conscious Indi guide'); headTitle.id = 'ci-guide-title'; headCopy.append(headTitle, node('small', '', 'Current listings and reviewed help information'));
  var headActions = node('div', 'ci-guide-head-actions');
  var restart = node('button', 'ci-guide-icon-button'); restart.type = 'button'; restart.setAttribute('aria-label', 'Start a new conversation'); restart.title = 'Start over'; restart.append(icon('fa-rotate-right'));
  var close = node('button', 'ci-guide-icon-button'); close.type = 'button'; close.setAttribute('aria-label', 'Minimise website guide'); close.title = 'Minimise'; close.append(icon('fa-minus'));
  headActions.append(restart, close); head.append(headAvatar, headCopy, headActions);
  var body = node('div', 'ci-guide-body'); var log = node('ol', 'ci-guide-log'); log.setAttribute('role', 'log'); log.setAttribute('aria-live', 'polite'); log.setAttribute('aria-relevant', 'additions'); body.append(log);
  var compose = node('footer', 'ci-guide-compose'); var form = node('form', 'ci-guide-form');
  var label = node('label', 'ci-guide-sr-only', 'Ask the website guide'); label.htmlFor = 'ci-guide-input';
  var field = node('div', 'ci-guide-field'); var input = node('textarea', 'ci-guide-input'); input.id = 'ci-guide-input'; input.name = 'question'; input.rows = 1; input.maxLength = 160; input.placeholder = 'Ask about products, events or help…'; field.append(label, input);
  var send = node('button', 'ci-guide-send'); send.type = 'submit'; send.setAttribute('aria-label', 'Send question'); send.append(icon('fa-arrow-up'));
  form.append(field, send); compose.append(form);
  var disclosure = node('p', 'ci-guide-disclosure'); disclosure.append(icon('fa-circle-info'), document.createTextNode('Website guide · answers from current listings and help information. No AI.'));
  compose.append(disclosure); panel.append(head, body, compose); root.append(panel, launcher); document.body.append(root);
  if (/checkout(?:\.html|\/|$)/i.test(location.pathname)) document.body.classList.add('ci-guide-checkout');

  function scrollEnd() { body.scrollTop = body.scrollHeight; }
  function choice(text, value) {
    var button = node('button', 'ci-guide-choice', text); button.type = 'button'; button.dataset.question = value || text; return button;
  }
  function assistantMessage(paragraphs, choices, modifier) {
    var entry = node('li', 'ci-guide-entry' + (modifier ? ' ' + modifier : ''));
    var avatar = node('span', 'ci-guide-entry-avatar'), image = node('img'); image.src = avatarUrl.href; image.alt = ''; avatar.append(image);
    var bubble = node('div', 'ci-guide-bubble' + (modifier ? ' ' + modifier : ''));
    (Array.isArray(paragraphs) ? paragraphs : [paragraphs]).forEach(function (text) { bubble.append(node('p', '', text)); });
    if (choices && choices.length) { var row = node('div', 'ci-guide-choices'); choices.forEach(function (item) { row.append(choice(item.label || item, item.value || item)); }); bubble.append(row); }
    entry.append(avatar, bubble); log.append(entry); scrollEnd(); return {entry:entry,bubble:bubble};
  }
  function userMessage(text) { var entry = node('li', 'ci-guide-entry user'); entry.append(node('div', 'ci-guide-bubble', text)); log.append(entry); scrollEnd(); }
  function addLink(bubble, text, path) { var paragraph = node('p'); var anchor = node('a', '', text); anchor.href = link(path); paragraph.append(anchor); bubble.append(paragraph); }
  function loadingMessage() { var message = assistantMessage('', null, 'loading'); message.bubble.replaceChildren(); var line = node('span', 'ci-guide-loading'); line.append(icon('fa-spinner fa-spin'), document.createTextNode('Checking current listings…')); message.bubble.append(line); return message.entry; }
  function setBusy(value) { busy = value; input.disabled = value; send.disabled = value; }

  var faq = [
    {words:['pay','payment','razorpay','upi','card','netbanking','cash','cod'], answer:'Online payment uses the existing secure Razorpay checkout for supported methods. Cash on delivery is shown only when every vendor in the order supports it for the delivery address. The website guide never collects payment details.', links:[['Go to your cart','frontend/modules/customer/cart.php']]},
    {words:['shipping','delivery','courier','shiprocket','india wide','pan india'], answer:'Delivery is arranged and updated manually by each vendor. Available delivery areas, charges and estimates are calculated for your address during checkout. Conscious Indi does not promise automatic courier booking or Shiprocket.', links:[['Track an existing delivery','frontend/modules/customer/tracking.php'],['Get delivery help','frontend/modules/customer/support.php']]},
    {words:['track','tracking','where order','order status'], answer:'Sign in and open Track delivery to see updates entered by the vendor. For privacy, this public guide cannot accept an order number or reveal account-specific status.', links:[['Track delivery securely','frontend/modules/customer/tracking.php'],['Get order help','frontend/modules/customer/support.php']]},
    {words:['return','refund','exchange','damaged'], answer:'Return eligibility depends on the product and the policy recorded with your order. Sign in to see eligible delivered items, submit the reason and add evidence where helpful. A request is reviewed; this guide cannot promise approval or a refund date.', links:[['Open returns','frontend/modules/customer/returns.php'],['Get return help','frontend/modules/customer/support.php']]},
    {words:['event booking','booking','tickets','book workshop'], answer:'The guide can show upcoming published events. Ask “Show upcoming events” to see current listings. Open an event to review its current ticket options, then sign in to use the existing booking flow. The guide does not create bookings or take payment.', links:[['Open your event bookings','frontend/modules/customer/events.php#bookings']]},
    {words:['order','checkout','buy','purchase'], answer:'Choose a currently published product, select an available variant, add it to your cart, sign in and use the secure checkout. Stock, delivery options and final charges are confirmed by the live checkout flow.', links:[['Browse current products','shop.html'],['Open your cart','frontend/modules/customer/cart.php']]},
    {words:['sell','seller','brand partner','brand onboarding'], answer:'Conscious brands can apply through the Brand Partner route. The application and product review steps must be completed before products can be published.', links:[['Apply as a brand','become-brand-partner.html']]},
    {words:['artisan','maker partner','craftsperson'], answer:'Artisans and collectives can apply through the Artisan Partner route. Identity, craft and store information are reviewed before selling access is approved.', links:[['Apply as an artisan','become-artisan-partner.html']]},
    {words:['influencer','creator','collaboration','affiliate'], answer:'Creators can review the participation information and register for Creator Studio. Verification is required before approved packages or campaign activity can become active.', links:[['Explore the creator programme','creators.html'],['Register as a creator','frontend/modules/influencer/auth/register.php']]},
    {words:['contact','support','help','complaint'], answer:'For a personal order, payment, return or account issue, please use the authenticated support area. General website questions can use the public contact page.', links:[['Open account support','frontend/modules/customer/support.php'],['Contact Conscious Indi','contact.html']]}
  ];

  var categoryRules = [
    {terms:['ceramic','ceramics','pottery','clay'], value:'Pottery'},
    {terms:['jewellery','jewelry','kundan'], value:'Jewellery'},
    {terms:['painting','paintings','madhubani','warli','art'], value:'Painting'},
    {terms:['textile','textiles','handloom','weave','woven','saree','shawl'], value:'Textile'},
    {terms:['home decor','decor'], value:'Home Decor'},
    {terms:['wood','wooden','woodwork'], value:'Wood'},
    {terms:['gift','gifts','gifting'], value:'Gift'}
  ];
  var cities = ['Ahmedabad','Bengaluru','Bangalore','Chennai','Delhi','Hyderabad','Jaipur','Jodhpur','Kochi','Kolkata','Lucknow','Mumbai','Pune','Surat','Udaipur','Varanasi'];

  function extractPlace(text) { var found = cities.find(function (city) { return hasWord(text, [city]); }); return found === 'Bangalore' ? 'Bengaluru' : (found || ''); }
  function extractPrices(text, filters) {
    var clean = String(text || '').toLowerCase().replace(/,/g, '').replace(/\s+/g, ' '); var match;
    match = clean.match(/(?:under|below|less than|upto|up to|max(?:imum)?)\s*₹?\s*(\d+)/); if (match) filters.max_price = Number(match[1]);
    match = clean.match(/(?:over|above|more than|min(?:imum)?)\s*₹?\s*(\d+)/); if (match) filters.min_price = Number(match[1]);
    match = clean.match(/(?:between|from)\s*₹?\s*(\d+)\s*(?:and|to|-)\s*₹?\s*(\d+)/); if (match) { filters.min_price = Number(match[1]); filters.max_price = Number(match[2]); }
  }
  function productFilters(text, keep) {
    var filters = keep ? Object.assign({}, context.products) : {};
    categoryRules.some(function (rule) { if (hasWord(text, rule.terms)) { filters.category = rule.value; return true; } return false; });
    var place = extractPlace(text); if (place) filters.origin = place;
    extractPrices(text, filters);
    if (hasWord(text, ['available','in stock','ready to buy'])) filters.availability = 'in_stock';
    var by = normalize(text).match(/(?:by|from maker|from vendor)\s+([a-z0-9 ]{3,50})/); if (by && !place) filters.vendor = by[1].replace(/\b(?:under|below|above|over)\b.*$/, '').trim();
    var query = normalize(text).replace(/₹/g, '').replace(/\b(products?|items?|show|find|browse|looking|matching|for|only|makers?|vendors?|available|stock|under|below|less|than|upto|up|to|over|above|more|between|and|from|by|price|rupees?|rs)\b/g, ' ').replace(/\b\d+\b/g, ' ').replace(/\s+/g, ' ').trim();
    categoryRules.forEach(function (rule) { rule.terms.forEach(function (term) { query = query.replace(new RegExp('\\b' + normalize(term).replace(/ /g,'\\s+') + '\\b','g'),' '); }); });
    cities.forEach(function (city) { query = query.replace(new RegExp('\\b' + normalize(city) + '\\b','g'),' '); });
    query = query.replace(/\s+/g, ' ').trim(); if (query.length >= 3) filters.q = query; else if (!keep) filters.q = '';
    return filters;
  }
  function iso(date) { return date.toISOString().slice(0, 10); }
  function eventFilters(text, keep) {
    var filters = keep ? Object.assign({}, context.events) : {};
    var place = extractPlace(text); if (place) filters.location = place;
    var today = new Date(), lower = normalize(text);
    if (lower.includes('today')) filters.date_from = filters.date_to = iso(today);
    else if (lower.includes('tomorrow')) { var tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1); filters.date_from = filters.date_to = iso(tomorrow); }
    else if (lower.includes('weekend')) { var saturday = new Date(today); saturday.setDate(today.getDate() + ((6 - today.getDay() + 7) % 7)); var sunday = new Date(saturday); sunday.setDate(saturday.getDate() + 1); filters.date_from = iso(saturday); filters.date_to = iso(sunday); }
    else if (lower.includes('next month')) { var next = new Date(today.getFullYear(), today.getMonth() + 1, 1); var nextEnd = new Date(today.getFullYear(), today.getMonth() + 2, 0); filters.date_from = iso(next); filters.date_to = iso(nextEnd); }
    else if (lower.includes('this month')) { filters.date_from = iso(today); filters.date_to = iso(new Date(today.getFullYear(), today.getMonth() + 1, 0)); }
    var query = lower.replace(/\b(events?|upcoming|show|find|in|near|today|tomorrow|this|next|month|weekend)\b/g, ' ');
    cities.forEach(function (city) { query = query.replace(new RegExp('\\b' + normalize(city) + '\\b','g'),' '); });
    query = query.replace(/\s+/g, ' ').trim(); filters.q = query.length >= 3 ? query : '';
    return filters;
  }
  function isProductQuestion(text) { return hasWord(text, ['product','shop','buy','price','cost','available','stock','ceramic','ceramics','pottery','jewellery','jewelry','painting','textile','handloom','decor','gift','wooden']) || categoryRules.some(function (rule) { return hasWord(text, rule.terms); }); }
  function isEventQuestion(text) { return hasWord(text, ['event','workshop','bazaar','festival','exhibition','fair','experience','class']); }

  function productCard(product) {
    var card = node('article', 'ci-guide-card'), media = node('span', 'ci-guide-card-media');
    var imageUrl = safeImage(product.image_url); if (imageUrl) { var image = node('img'); image.src = imageUrl; image.alt = product.name; image.loading = 'lazy'; image.addEventListener('error', function () { media.replaceChildren(icon('fa-bag-shopping')); }); media.append(image); } else media.append(icon('fa-bag-shopping'));
    var copy = node('div', 'ci-guide-card-copy'); copy.append(node('small', '', [product.category, product.vendor].filter(Boolean).join(' · ')), node('strong', '', product.name));
    var meta = node('div', 'ci-guide-card-meta'); meta.append(node('span', 'ci-guide-card-price', money(product.price)), node('span', '', product.availability));
    var locationText = product.maker_location || product.origin; if (locationText) meta.append(node('span', '', locationText)); copy.append(meta);
    var anchor = node('a', 'ci-guide-card-link', 'View product'); anchor.href = link(product.url); anchor.append(icon('fa-arrow-right')); copy.append(anchor); card.append(media, copy); return card;
  }
  function eventCard(event) {
    var card = node('article', 'ci-guide-card'), media = node('span', 'ci-guide-card-media');
    var imageUrl = safeImage(event.image_url); if (imageUrl) { var image = node('img'); image.src = imageUrl; image.alt = event.title; image.loading = 'lazy'; image.addEventListener('error', function () { media.replaceChildren(icon('fa-calendar-days')); }); media.append(image); } else media.append(icon('fa-calendar-days'));
    var copy = node('div', 'ci-guide-card-copy'); copy.append(node('small', '', titleCase(event.event_type) + ' · ' + event.host), node('strong', '', event.title));
    var meta = node('div', 'ci-guide-card-meta'); meta.append(node('span', '', dateTime(event.starts_at)), node('span', '', event.location));
    meta.append(node('span', 'ci-guide-card-price', Number(event.from_price) > 0 ? 'From ' + money(event.from_price) : 'Free entry/tickets where listed')); copy.append(meta);
    var anchor = node('a', 'ci-guide-card-link', 'View events'); anchor.href = link(event.url); anchor.append(icon('fa-arrow-right')); copy.append(anchor); card.append(media, copy); return card;
  }
  function describeFilters(filters, kind) {
    var bits = [];
    if (filters.category) bits.push(filters.category);
    if (filters.q) bits.push('matching “' + filters.q + '”');
    if (filters.origin || filters.location) bits.push('in ' + (filters.origin || filters.location));
    if (filters.vendor) bits.push('from ' + filters.vendor);
    if (filters.min_price !== undefined) bits.push('from ' + money(filters.min_price));
    if (filters.max_price !== undefined) bits.push('under ' + money(filters.max_price));
    if (filters.availability) bits.push('currently in stock');
    if (filters.date_from || filters.date_to) bits.push('for the requested date');
    return bits.length ? bits.join(', ') : (kind === 'products' ? 'current products' : 'upcoming events');
  }
  async function discover(kind, filters, original) {
    context.intent = kind; context[kind] = filters; context.lastQuestion = original;
    var pending = loadingMessage(); setBusy(true);
    try {
      var params = new URLSearchParams({kind:kind,limit:'6'});
      Object.keys(filters).forEach(function (key) { if (filters[key] !== '' && filters[key] !== undefined && filters[key] !== null) params.set(key, String(filters[key])); });
      var response = await fetch(apiUrl.href + '?' + params.toString(), {credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});
      var data = await response.json().catch(function () { return {}; });
      if (!response.ok || !data.success) throw Error(data.message || 'Current listings are unavailable.');
      pending.remove();
      if (!Array.isArray(data.items) || !data.items.length) {
        assistantMessage(['I could not find any ' + (kind === 'products' ? 'currently published products' : 'upcoming published events') + ' for ' + describeFilters(filters, kind) + '.', 'Try removing one filter or choose a broader search.'], [
          {label:'Clear filters',value:kind === 'products' ? 'Show products' : 'Show upcoming events'},
          {label:'Get help',value:'How can I contact support?'}
        ], 'empty'); return;
      }
      var countCopy = data.match_count > data.items.length ? 'Here are the closest ' + data.items.length + ' of ' + data.match_count : 'I found ' + data.items.length;
      var kindCopy = data.items.length === 1 ? (kind === 'products' ? 'current product' : 'upcoming event') : (kind === 'products' ? 'current products' : 'upcoming events');
      var message = assistantMessage(countCopy + ' ' + kindCopy + ' for ' + describeFilters(filters, kind) + '.');
      var results = node('div', 'ci-guide-results'); data.items.forEach(function (item) { results.append(kind === 'products' ? productCard(item) : eventCard(item)); }); message.bubble.append(results);
      var follow = node('div', 'ci-guide-choices');
      if (kind === 'products') { follow.append(choice('Only in stock','Show only products in stock'), choice('Under ₹2,000','Show only under ₹2,000'), choice('Jaipur makers','Show only Jaipur makers')); }
      else { follow.append(choice('This month','Show events this month'), choice('In Bengaluru','Show events in Bengaluru'), choice('Workshops','Show workshops')); }
      message.bubble.append(follow); scrollEnd();
    } catch (error) {
      pending.remove(); assistantMessage(['I could not check the current listings.', error.message || 'Please try again.'], [{label:'Try again',value:original},{label:'Open help centre',value:'How can I contact support?'}], 'error');
    } finally { setBusy(false); input.focus(); }
  }
  function answerFaq(item) {
    var message = assistantMessage(item.answer); (item.links || []).forEach(function (entry) { addLink(message.bubble, entry[0], entry[1]); }); scrollEnd();
  }
  function routeQuestion(question) {
    var text = question.trim(); var followUp = /^(show|only|make it|filter|what about|how about|under|above|over|in )/i.test(text);
    var explicitHelp = hasWord(text, ['shipping','delivery','courier','shiprocket','track','tracking','return','refund','exchange','razorpay','payment','cash','cod','support','contact']);
    var asksHow = /\b(?:how|where|can i|what is|tell me about)\b/i.test(text);
    var matched = faq.find(function (item) { return hasWord(text, item.words); });
    if ((explicitHelp || asksHow) && matched) { answerFaq(matched); return; }
    if (isEventQuestion(text)) { discover('events', eventFilters(text, followUp && context.intent === 'events' && !hasWord(text,['all'])), text); return; }
    if (isProductQuestion(text) && !hasWord(text,['order status','tracking'])) { discover('products', productFilters(text, followUp && context.intent === 'products' && !hasWord(text,['all'])), text); return; }
    if (followUp && context.intent === 'products') { discover('products', productFilters(text, !hasWord(text,['all'])), text); return; }
    if (followUp && context.intent === 'events') { discover('events', eventFilters(text, !hasWord(text,['all'])), text); return; }
    if (matched) { answerFaq(matched); return; }
    assistantMessage(['I do not understand that well enough to give a reliable answer. I can search current products and upcoming events, or help with the reviewed topics below.'], [
      {label:'Find products',value:'Show available products'}, {label:'Upcoming events',value:'Show upcoming events'},
      {label:'Shipping and tracking',value:'How does shipping and tracking work?'}, {label:'Returns',value:'How do returns work?'}
    ]);
  }
  function submitQuestion(question) {
    question = String(question || '').trim(); if (!question || busy) return;
    userMessage(question); input.value = ''; input.style.height = ''; routeQuestion(question);
  }
  function welcome() {
    log.replaceChildren(); context = {intent:'',products:{},events:{},lastQuestion:''};
    assistantMessage(['Namaste. I’m the Conscious Indi website guide.', 'I use current approved listings and reviewed help information—not AI. What would you like to explore?'], [
      {label:'Find products',value:'Show available products'}, {label:'Upcoming events',value:'Show upcoming events'},
      {label:'Track an order',value:'How do I track my order?'}, {label:'Selling and partnerships',value:'How can I sell as a brand or artisan?'}
    ]);
  }
  function openPanel() { panel.hidden = false; launcher.hidden = true; launcher.setAttribute('aria-expanded','true'); window.setTimeout(function () { input.focus(); }, 30); }
  function closePanel() { panel.hidden = true; launcher.hidden = false; launcher.setAttribute('aria-expanded','false'); launcher.focus(); }
  launcher.addEventListener('click', openPanel); close.addEventListener('click', closePanel); restart.addEventListener('click', function () { welcome(); input.focus(); });
  root.addEventListener('click', function (event) { var button = event.target.closest('[data-question]'); if (button) submitQuestion(button.dataset.question); });
  form.addEventListener('submit', function (event) { event.preventDefault(); submitQuestion(input.value); });
  input.addEventListener('keydown', function (event) { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); form.requestSubmit(); } });
  input.addEventListener('input', function () { input.style.height = 'auto'; input.style.height = Math.min(92, input.scrollHeight) + 'px'; });
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !panel.hidden) closePanel(); });
  welcome();
})();
