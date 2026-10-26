(function () {
  'use strict';
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
  const apiBase = '../../../api/';

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
  function loadRazorpay() {
    if (window.Razorpay) return Promise.resolve();
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://checkout.razorpay.com/v1/checkout.js';
      script.async = true;
      script.onload = () => window.Razorpay ? resolve() : reject(Error('Razorpay checkout did not load.'));
      script.onerror = () => reject(Error('Secure payment could not be loaded. Please retry.'));
      document.head.append(script);
    });
  }
  function renderPackages() {
    const term = search.value.trim().toLocaleLowerCase();
    const matches = state.packages.filter(pkg => [
      pkg.full_name, pkg.handle, pkg.title, pkg.description,
      ...(pkg.platforms_json || []), ...(pkg.deliverables_json || [])
    ].join(' ').toLocaleLowerCase().includes(term));
    grid.replaceChildren();
    message.textContent = matches.length
      ? matches.length + (matches.length === 1 ? ' reviewed package available.' : ' reviewed packages available.')
      : 'No approved packages match your search.';
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
  function quoteText(quote) {
    return [
      quote.creator.name + ' (@' + quote.creator.handle + ')',
      'Deliverables: ' + quote.deliverables.join(', '),
      'Platforms: ' + quote.platforms.join(', '),
      'Turnaround: ' + quote.turnaround_days + ' days · Revisions: ' + quote.revisions,
      'Usage rights: ' + quote.usage_rights,
      'Vendor requirements: ' + quote.vendor_requirements,
      'Cancellation: ' + quote.cancellation_terms,
      'Price ' + money.format(Number(quote.base_amount)) + ' + GST (' + quote.gst_rate + '%) ' + money.format(Number(quote.gst_amount)) + ' = ' + money.format(Number(quote.total_amount))
    ];
  }
  async function openPackage(packageId) {
    form.reset();
    formMessage.textContent = 'Loading server-confirmed package terms…';
    dialog.showModal();
    try {
      const data = await request('vendor/package-purchase-preview.php?package_id=' + encodeURIComponent(packageId));
      state.quote = data.quote;
      title.textContent = data.quote.package_title;
      quoteBox.replaceChildren(...quoteText(data.quote).map(line => element('p', 'mb-1', line)));
      form.elements.contact_name.value = data.vendor.contact_name || '';
      form.elements.contact_email.value = data.vendor.contact_email || '';
      form.elements.contact_mobile.value = data.vendor.contact_mobile || '';
      formMessage.textContent = 'Your total is fixed from the approved package record; the browser cannot change it.';
    } catch (error) {
      state.quote = null;
      formMessage.textContent = error.message;
    }
  }
  async function pay(payment, order) {
    const gateway = await request('payment/service-razorpay-order.php', 'POST', { payment_id: payment.id });
    if (gateway.gateway === 'mock') {
      if (!window.confirm('LOCAL DEMO ONLY: no real money will be charged. Record this demonstration payment?')) {
        formMessage.textContent = 'Payment was not recorded. The reservation remains pending until it expires.';
        return;
      }
      await request('payment/service-local-demo-confirm.php', 'POST', { payment_id: payment.id });
      formMessage.textContent = 'Local demo payment recorded. No real money was charged.';
      await loadOrders();
      window.setTimeout(() => dialog.close(), 900);
      return;
    }
    await loadRazorpay();
    new window.Razorpay({
      key: gateway.key_id, amount: gateway.amount, currency: gateway.currency,
      name: 'Conscious India', description: 'Creator package ' + order.order_number, order_id: gateway.order_id,
      handler: async response => {
        try {
          const result = await request('payment/service-razorpay-verify.php', 'POST', {
            payment_id: payment.id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature
          });
          formMessage.textContent = result.message;
          await loadOrders();
          window.setTimeout(() => dialog.close(), 900);
        } catch (error) { formMessage.textContent = error.message + ' Do not pay again; check your collaboration status.'; }
      },
      modal: { ondismiss: () => { formMessage.textContent = 'Payment was not confirmed. Your reservation remains pending until it expires.'; } }
    }).open();
  }
  function actionButton(order, action, label) {
    const button = element('button', 'vc-btn small', label);
    button.type = 'button';
    button.addEventListener('click', async () => {
      const reason = window.prompt('Give a clear reason (at least 10 characters).');
      if (!reason) return;
      button.disabled = true;
      try {
        const result = await request('vendor/package-orders.php', 'PATCH', { order_id: order.id, action, reason });
        ordersMessage.textContent = result.message;
        await loadOrders();
      } catch (error) { ordersMessage.textContent = error.message; button.disabled = false; }
    });
    return button;
  }
  function renderOrders(orders) {
    ordersGrid.replaceChildren();
    ordersMessage.textContent = orders.length ? orders.length + ' collaboration order(s).' : 'You have not purchased a creator package yet.';
    orders.forEach(order => {
      const column = element('div', 'col-md-6');
      const card = element('article', 'card h-100');
      const body = element('div', 'card-body');
      body.append(
        element('small', 'text-muted', order.order_number),
        element('h3', 'h6 mt-2', order.package_title),
        element('p', 'mb-1', order.influencer_name + ' (@' + order.influencer_handle + ')'),
        element('p', 'small mb-1', 'Status: ' + String(order.status).replaceAll('_', ' ')),
        element('p', 'small mb-1', 'Payment: ' + String(order.payment_status).replaceAll('_', ' ')),
        element('strong', '', money.format(Number(order.total_amount)))
      );
      if (['paid','contact_pending','accepted','scheduled','in_progress','delivered','revision_requested'].includes(order.status)) {
        const actions = element('div', 'd-flex gap-2 flex-wrap mt-3');
        actions.append(actionButton(order, 'request_cancellation', 'Request cancellation'), actionButton(order, 'open_dispute', 'Open dispute'));
        body.append(actions);
      }
      card.append(body); column.append(card); ordersGrid.append(column);
    });
  }
  async function loadOrders() {
    const data = await request('vendor/package-orders.php');
    renderOrders(Array.isArray(data.orders) ? data.orders : []);
  }

  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (state.busy || !state.quote || !form.reportValidity()) return;
    state.busy = true;
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;
    formMessage.textContent = 'Reserving this package…';
    try {
      const result = await request('vendor/package-orders.php', 'POST', {
        package_id: state.quote.package_id,
        brief: form.elements.brief.value.trim(),
        contact_name: form.elements.contact_name.value.trim(),
        contact_email: form.elements.contact_email.value.trim(),
        contact_mobile: form.elements.contact_mobile.value.trim(),
        target_products: list(form.elements.target_products.value),
        requested_dates: list(form.elements.requested_dates.value),
        accept_terms: form.elements.accept_terms.checked
      }, key());
      formMessage.textContent = result.message;
      await pay(result.payment, result.order);
    } catch (error) { formMessage.textContent = error.message; }
    finally { state.busy = false; submit.disabled = false; }
  });
  document.querySelectorAll('[data-close-package-dialog]').forEach(button => button.addEventListener('click', () => dialog.close()));
  search.addEventListener('input', renderPackages);

  (async () => {
    try {
      const identity = await request('vendor/auth/me.php');
      state.csrf = identity.csrf_token || '';
      const [catalogue] = await Promise.all([request('public/packages.php'), loadOrders()]);
      state.packages = Array.isArray(catalogue.packages) ? catalogue.packages : [];
      renderPackages();
    } catch (error) {
      message.textContent = error.message;
      ordersMessage.textContent = error.message;
    }
  })();
})();
