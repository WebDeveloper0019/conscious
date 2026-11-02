(function () {
  'use strict';
  
  // 1. Setup paths and DOM elements
  const apiBase = '/conscious/api/'; // Fixed absolute path
  const grid = document.getElementById('vendor-package-grid');
  if (!grid) return;
  
  const message = document.getElementById('vendor-package-message');
  const search = document.getElementById('vendor-package-search');
  const ordersGrid = document.getElementById('vendor-package-orders');
  const ordersMessage = document.getElementById('vendor-package-orders-message');
  const dialog = document.getElementById('vendor-package-dialog');
  const form = document.getElementById('vendor-package-form');
  const formMessage = document.getElementById('vendor-package-form-message');
  const quoteBox = document.getElementById('vendor-package-quote');
  const title = document.getElementById('vendor-package-dialog-title');
  const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
  const state = { csrf: '', packages: [], quote: null, busy: false };

  // 2. Helpers
  function element(tag, className, content) {
    const item = document.createElement(tag);
    if (className) item.className = className;
    if (content !== undefined) item.textContent = String(content);
    return item;
  }
  function list(value) {
    return String(value || '').split(/\r?\n|,/).map(item => item.trim()).filter(Boolean);
  }
  function key() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'creator-' + Date.now() + '-' + Math.random().toString(16).slice(2);
  }

  // 3. API Request wrapper
  async function request(path, method = 'GET', body, idempotencyKey) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') headers['X-CSRF-Token'] = state.csrf;
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    if (idempotencyKey) headers['Idempotency-Key'] = idempotencyKey;
    
    const response = await fetch(apiBase + path, {
      method, credentials: 'same-origin', cache: 'no-store', headers,
      body: body === undefined ? undefined : JSON.stringify(body)
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.success === false) throw Error(data.message || 'The request could not be completed.');
    return data;
  }

  // 4. Render Packages
  function renderPackages() {
    const term = search ? search.value.trim().toLocaleLowerCase() : '';
    const matches = state.packages.filter(pkg => [
      pkg.full_name, pkg.handle, pkg.title, pkg.description,
      ...(pkg.platforms_json || []), ...(pkg.deliverables_json || [])
    ].join(' ').toLocaleLowerCase().includes(term));
    
    grid.replaceChildren();
    
    if (message) {
      message.textContent = matches.length
        ? matches.length + (matches.length === 1 ? ' reviewed package available.' : ' reviewed packages available.')
        : 'No approved packages match your search.';
    }
    
    if (matches.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No active packages found. Check back later!</p></div>';
        return;
    }

    matches.forEach(pkg => {
      const column = element('div', 'col-md-6 col-xl-4');
      const card = element('article', 'card h-100');
      const body = element('div', 'card-body d-flex flex-column');
      
      body.append(
        element('small', 'text-muted', '@' + pkg.handle + ' · ' + pkg.full_name),
        element('h3', 'h6 mt-2', pkg.title),
        element('p', 'text-muted', pkg.description),
        element('p', 'small', 'Includes: ' + (pkg.deliverables_json || []).join(', ')),
        element('p', 'small', 'Platforms: ' + (pkg.platforms_json || []).join(', ')),
        element('p', 'small', 'Turnaround: ' + pkg.turnaround_days + ' days · Revisions: ' + pkg.revisions),
        element('strong', 'mt-auto', money.format(Number(pkg.price_inr)))
      );
      
      const buy = element('button', 'vc-btn primary mt-3', 'Review & purchase');
      buy.type = 'button';
      buy.addEventListener('click', () => openPackage(pkg.id));
      
      const profile = element('a', 'vc-btn mt-2', 'View creator');
      profile.href = '../../../influencer-profile.php?handle=' + encodeURIComponent(pkg.handle);
      
      body.append(buy, profile);
      card.append(body); column.append(card); grid.append(column);
    });
  }

  // 5. Load Data
  async function loadOrders() {
    try {
        const data = await request('vendor/package-orders.php');
        if (ordersGrid) renderOrders(Array.isArray(data.orders) ? data.orders : []);
    } catch(e) { console.log(e); }
  }

  function renderOrders(orders) {
    if(!ordersGrid) return;
    ordersGrid.replaceChildren();
    if (ordersMessage) ordersMessage.textContent = orders.length ? orders.length + ' collaboration order(s).' : 'You have not purchased a creator package yet.';
    // ... (Rest of renderOrders logic if you need it)
  }

  // 6. Initialize
  (async () => {
    try {
      const identity = await request('vendor/auth/me.php');
      state.csrf = identity.csrf_token || '';
      const [catalogue] = await Promise.all([request('public/packages.php'), loadOrders()]);
      state.packages = Array.isArray(catalogue.packages) ? catalogue.packages : [];
      renderPackages();
    } catch (error) {
      if(message) message.textContent = error.message;
      if(ordersMessage) ordersMessage.textContent = error.message;
    }
  })();
})();