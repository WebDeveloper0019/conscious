(() => {
  'use strict';
  const productsRoot = document.getElementById('ci-maker-products');
  const craftsRoot = document.getElementById('ci-craft-grid');
  if (!productsRoot || !craftsRoot) return;
  const create = (tag, className, value) => { const node = document.createElement(tag); if (className) node.className = className; if (value !== undefined) node.textContent = value; return node; };
  const money = value => new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR', maximumFractionDigits:0}).format(Number(value) || 0);
  const imageUrl = raw => { if (!raw) return ''; try { const url = new URL(raw, new URL('api/public/catalog.php', location.href)); if (url.origin !== location.origin || !/^https?:$/.test(url.protocol)) return ''; if (url.pathname.endsWith('/api/files.php')) url.searchParams.set('v', '20261026-images1'); return url.href; } catch { return ''; } };
  const feedback = create('p', 'ci-maker-feedback'); feedback.setAttribute('role', 'status'); productsRoot.after(feedback);
  async function request(path, options) { const response = await fetch(path, Object.assign({credentials:'same-origin', headers:{Accept:'application/json'}}, options || {})); const data = await response.json().catch(() => ({})); if (!response.ok || !data.success) { const error = Error(data.message || 'Please try again.'); error.login = response.status === 401; throw error; } return data; }
  async function addToCart(product, button) {
    button.disabled = true; feedback.replaceChildren();
    try {
      const me = await request('api/auth/me.php');
      const detail = await request('api/public/catalog.php?id=' + encodeURIComponent(product.id));
      const variant = (detail.variants || []).find(item => Number(item.available_quantity) > 0);
      if (!variant) throw Error('This product is currently out of stock.');
      await request('api/user/cart.php', {method:'POST', headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-Token':me.csrf_token || ''}, body:JSON.stringify({variant_id:variant.id,quantity:1})});
      button.textContent = 'Added to cart'; feedback.textContent = product.name + ' was added to your cart.';
      const count = document.querySelector('.bc-cart-count'); if (count) count.textContent = String((Number(count.textContent) || 0) + 1);
    } catch (error) {
      feedback.textContent = error.message;
      if (error.login || /sign in|log in|authentication|unauthorized/i.test(error.message)) { const link = create('a', '', ' Sign in'); link.href = 'portals/modules/customer/auth/login.php'; feedback.append(link); }
      button.disabled = false;
    }
  }
  function renderProducts(products) {
    productsRoot.replaceChildren();
    const visible = products.filter(item => imageUrl(item.image_url)).slice(0, 3);
    if (!visible.length) { productsRoot.append(create('p', 'ci-maker-error', 'New products are coming soon. Explore the full marketplace for current listings.')); return; }
    visible.forEach(product => {
      const card = create('article', 'ci-maker-product');
      const link = create('a'); link.href = 'product-detail.html?id=' + encodeURIComponent(product.id);
      const image = create('img'); image.src = imageUrl(product.image_url); image.alt = product.name; image.loading = 'lazy'; link.append(image);
      const name = create('h3', '', product.name), vendor = create('small', '', 'by ' + (product.vendor_name || 'Conscious India maker') + (product.vendor_verification_status === 'demo_fixture' ? ' · Demo' : ''));
      const price = create('strong', '', money(product.selling_price));
      const button = create('button', 'ci-maker-add', 'Add to cart'); button.type = 'button'; button.addEventListener('click', () => addToCart(product, button));
      card.append(link, name, vendor, price, button); productsRoot.append(card);
    });
  }
  function renderCrafts(products) {
    craftsRoot.replaceChildren();
    const seen = new Set();
    products.forEach(product => {
      const category = String(product.category_name || '').trim(), photo = imageUrl(product.image_url);
      if (!category || !photo || seen.has(category) || seen.size >= 5) return;
      seen.add(category);
      const link = create('a', 'ci-craft-card'); link.href = 'shop.html?category=' + encodeURIComponent(category); link.setAttribute('aria-label', 'Shop ' + category);
      const image = create('img'); image.src = photo; image.alt = ''; image.loading = 'lazy';
      const caption = create('span', '', category + ' '); caption.append(create('b', '', '→')); link.append(image, caption); craftsRoot.append(link);
    });
    if (!seen.size) craftsRoot.append(create('p', 'ci-maker-error', 'Craft collections will appear when products are published.'));
  }
  request('api/public/catalog.php?limit=60').then(data => { const products = data.products || []; renderProducts(products); renderCrafts(products); }).catch(() => { productsRoot.replaceChildren(create('p', 'ci-maker-error', 'Products could not load right now. Please visit the marketplace.')); craftsRoot.replaceChildren(create('p', 'ci-maker-error', 'Crafts could not load right now.')); });
})();
