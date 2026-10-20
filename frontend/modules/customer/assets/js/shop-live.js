(function () {
  'use strict';
  if (sessionStorage.getItem('ci_demo_customer') || localStorage.getItem('ci_demo_customer')) return;
  var grid = document.querySelector('.cp-shop-results .cp-product-grid');
  var detail = document.querySelector('.cp-product-detail');
  if (!grid && !detail) return;
  var catalogUrl = '../../../api/public/catalog.php';
  var cartUrl = '../../../api/user/cart.php';
  var currency = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
  function el(tag, className, content) {
    var result = document.createElement(tag);
    if (className) result.className = className;
    if (content !== undefined) result.textContent = content;
    return result;
  }
  function imageUrl(raw) {
    if (!raw) return '';
    var url = new URL(raw, new URL(catalogUrl, location.href));
    return url.origin === location.origin && /^https?:$/.test(url.protocol) ? url.href : '';
  }
  function alertUser(message) {
    var area = document.getElementById('cc-alert');
    if (area) { area.textContent = message; area.scrollIntoView({ block: 'nearest' }); }
  }
  async function get(url) {
    var response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' });
    var result = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(result.message || 'Products could not be loaded.');
    return result;
  }
  async function add(variantId, button) {
    button.disabled = true;
    try {
      var cookie = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
      var token = sessionStorage.getItem('ci_csrf_token') || (cookie ? decodeURIComponent(cookie[1]) : '');
      var response = await fetch(cartUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': token },
        body: JSON.stringify({ variant_id: variantId, quantity: 1 })
      });
      var result = await response.json().catch(function () { return {}; });
      if (!response.ok) throw new Error(result.message || 'Could not add this product.');
      var badge = document.querySelector('.cc-cart b');
      if (badge && result.cart) badge.textContent = String(result.cart.item_count || 0);
      alertUser('Added to your bag.');
    } catch (error) { alertUser(error.message); }
    finally { button.disabled = false; }
  }
  async function save(productId, button) {
    button.disabled = true;
    try {
      var cookie = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
      var token = sessionStorage.getItem('ci_csrf_token') || (cookie ? decodeURIComponent(cookie[1]) : '');
      var response = await fetch('../../../api/user/wishlist.php', {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': token },
        body: JSON.stringify({ productId: productId })
      });
      var result = await response.json().catch(function () { return {}; });
      if (!response.ok || !result.success) throw new Error(result.message || 'Could not save this product.');
      button.textContent = 'Saved'; alertUser('Saved to your wishlist.');
    } catch (error) { alertUser(error.message); button.disabled = false; }
  }
  async function followVendor(vendorId, button) {
    button.disabled = true;
    try {
      var cookie = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
      var token = sessionStorage.getItem('ci_csrf_token') || (cookie ? decodeURIComponent(cookie[1]) : '');
      var response = await fetch('../../../api/user/saved-vendors.php', {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': token },
        body: JSON.stringify({ vendor_id: vendorId })
      });
      var result = await response.json().catch(function () { return {}; });
      if (!response.ok || !result.success) throw new Error(result.message || 'Could not follow this maker.');
      button.textContent = 'Following'; alertUser('Maker saved.');
    } catch (error) { alertUser(error.message); button.disabled = false; }
  }
  async function loadShop(offset, append) {
    if (!append) grid.replaceChildren(el('p', '', 'Loading products…'));
    var search = new URLSearchParams(location.search);
    var query = search.get('q') || '';
    var params = new URLSearchParams({ limit: '24', offset: String(offset) });
    if (query) params.set('q', query);
    if (search.get('vendor_id')) params.set('vendor_id', search.get('vendor_id'));
    try {
      var result = await get(catalogUrl + '?' + params);
      var products = Array.isArray(result.products) ? result.products : [];
      if (!append) grid.replaceChildren();
      if (!products.length && !append) grid.append(el('p', '', 'No published products found yet.'));
      products.forEach(function (product) {
        var card = el('article', 'cp-product-card');
        var link = el('a'); link.href = 'product.php?id=' + encodeURIComponent(product.id);
        var image = imageUrl(product.image_url);
        if (image) { var photo = el('img'); photo.src = image; photo.alt = product.name || 'Product'; link.append(photo); }
        var body = el('div', 'cp-product-copy');
        body.append(el('small', '', product.vendor_name || 'Verified maker'));
        var name = el('h3'); name.textContent = product.name || 'Product'; body.append(name);
        body.append(el('strong', '', currency.format(Number(product.selling_price) || 0)));
        var button = el('button', 'cc-btn primary', 'Add to bag'); button.type = 'button';
        button.addEventListener('click', async function () {
          button.disabled = true;
          try {
            var details = await get(catalogUrl + '?id=' + encodeURIComponent(product.id));
            var variant = (details.variants || []).find(function (entry) { return Number(entry.available_quantity) > 0; });
            if (!variant) throw new Error('This product is currently out of stock.');
            await add(variant.id, button);
          } catch (error) { alertUser(error.message); }
          finally { button.disabled = false; }
        });
        var saveButton = el('button', 'cc-btn', 'Save'); saveButton.type = 'button';
        saveButton.addEventListener('click', function () { save(product.id, saveButton); });
        if (product.vendor_id) { var followButton = el('button', 'cc-btn', 'Follow maker'); followButton.type = 'button'; followButton.addEventListener('click', function () { followVendor(product.vendor_id, followButton); }); card.append(link, body, button, saveButton, followButton); }
        else card.append(link, body, button, saveButton);
        grid.append(card);
      });
      var count = document.querySelector('.cp-shop-results header strong');
      if (count) count.textContent = products.length ? 'Published products' : 'No products available';
      var more = document.querySelector('.cp-load-more');
      if (more) {
        more.hidden = products.length < 24;
        more.onclick = function () { loadShop(offset + 24, true); };
      }
    } catch (error) { if (!append) grid.replaceChildren(el('p', '', error.message)); alertUser(error.message); }
  }
  async function loadDetail() {
    var id = new URLSearchParams(location.search).get('id');
    detail.replaceChildren(el('p', '', 'Loading product…'));
    document.querySelector('.cp-product-story')?.remove();
    document.querySelector('.cp-recommend')?.remove();
    if (!id) { detail.replaceChildren(el('p', '', 'Open a published product from the marketplace.')); return; }
    try {
      var result = await get(catalogUrl + '?id=' + encodeURIComponent(id));
      var product = result.product || {};
      var variants = Array.isArray(result.variants) ? result.variants : [];
      var gallery = el('div', 'cp-gallery');
      var firstImage = (result.images || [])[0];
      var source = imageUrl(firstImage && firstImage.url);
      if (source) { var photo = el('img'); photo.src = source; photo.alt = firstImage.alt_text || product.name || 'Product'; gallery.append(photo); }
      var panel = el('div', 'cp-buy-panel');
      panel.append(el('p', 'cp-maker', product.vendor_name || 'Verified maker'));
      panel.append(el('h2', '', product.name || 'Product'));
      panel.append(el('p', '', product.short_description || 'Made by an Indian maker.'));
      panel.append(el('div', 'cp-price-line', currency.format(Number(product.selling_price) || 0)));
      var available = variants.filter(function (item) { return Number(item.available_quantity) > 0; });
      var label = el('label', '', 'Choose variant ');
      var select = el('select', 'cc-select');
      available.forEach(function (item) { var option = el('option', '', item.name || 'Standard'); option.value = item.id; select.append(option); });
      label.append(select); panel.append(label);
      var button = el('button', 'cc-btn primary', 'Add to bag'); button.type = 'button';
      button.disabled = !available.length;
      if (!available.length) panel.append(el('p', '', 'Currently out of stock.'));
      button.addEventListener('click', function () { add(select.value, button); });
      panel.append(button);
      var saveButton = el('button', 'cc-btn', 'Save to wishlist'); saveButton.type = 'button';
      saveButton.addEventListener('click', function () { save(product.id, saveButton); });
      panel.append(saveButton);
      if (product.vendor_id) { var followButton = el('button', 'cc-btn', 'Follow maker'); followButton.type = 'button'; followButton.addEventListener('click', function () { followVendor(product.vendor_id, followButton); }); panel.append(followButton); }
      var bag = el('a', 'cc-btn', 'View bag'); bag.href = 'cart.php'; panel.append(bag);
      detail.replaceChildren(gallery, panel);
      var crumb = document.querySelector('.cp-breadcrumb span');
      if (crumb) crumb.textContent = product.name || 'Product';
    } catch (error) { detail.replaceChildren(el('p', '', error.message)); alertUser(error.message); }
  }
  if (grid) {
    document.querySelector('.cp-category-row')?.setAttribute('hidden', '');
    document.querySelector('.cp-filter-panel')?.setAttribute('hidden', '');
    var sort = document.querySelector('.cp-shop-results header select'); if (sort) sort.hidden = true;
    loadShop(0, false);
  }
  if (detail) loadDetail();
})();
