(function () {
  'use strict';
  var root = document.querySelector('[data-cart-items]');
  if (!root) return;

  var endpoint = '../../../api/user/cart.php';
  var note = root.querySelector('.cp-delivery-note');
  var checkout = document.querySelector('[data-cart-checkout]');
  var formatter = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 });
  function node(tag, className, value) {
    var element = document.createElement(tag);
    if (className) element.className = className;
    if (value !== undefined) element.textContent = value;
    return element;
  }
  function money(value) { return formatter.format(Number(value) || 0); }
  function message(value) {
    var alert = document.getElementById('cc-alert');
    if (alert) alert.textContent = value;
  }
  async function request(method, url, body) {
    var headers = { Accept: 'application/json' };
    if (body) headers['Content-Type'] = 'application/json';
    var cookie = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    var token = sessionStorage.getItem('ci_csrf_token') || (cookie ? decodeURIComponent(cookie[1]) : '');
    if (token) headers['X-CSRF-Token'] = token;
    var response = await fetch(url, { method: method, credentials: 'same-origin', headers: headers, body: body ? JSON.stringify(body) : undefined, cache: 'no-store' });
    var result = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(result.message || 'Could not update your bag. Please try again.');
    return result;
  }
  function render(cart) {
    root.replaceChildren();
    var items = Array.isArray(cart.items) ? cart.items : [];
    var count = Number(cart.item_count) || 0;
    document.querySelector('[data-cart-count]').textContent = count + (count === 1 ? ' item' : ' items');
    document.querySelector('.cc-cart b').textContent = String(count);
    document.querySelector('[data-cart-subtotal]').textContent = money(cart.subtotal);
    document.querySelector('[data-cart-reward]').textContent = 'Calculated at checkout';
    document.querySelector('[data-cart-total]').textContent = money(cart.subtotal);
    checkout.setAttribute('aria-disabled', items.length ? 'false' : 'true');
    if (!items.length) {
      var empty = node('div', 'cc-card cp-cart-empty');
      empty.append(node('h2', '', 'Your shopping bag is empty'), node('p', '', 'Explore handmade products and add one to continue.'));
      var link = node('a', 'cc-btn primary', 'Explore products');
      link.href = 'shop.php'; empty.append(link); root.append(empty);
    }
    items.forEach(function (item) {
      var article = node('article', 'cp-cart-item');
      var link = node('a'); link.href = 'product.php?id=' + encodeURIComponent(item.product_id || '');
      if (item.image_url) {
        var image = node('img'); image.src = new URL(item.image_url, new URL(endpoint, location.href)).href;
        image.alt = item.name || 'Product'; link.append(image);
      }
      var copy = node('div', 'cp-cart-copy'); var description = node('div');
      description.append(node('p', '', item.vendor_name || ''), node('h3', '', item.name || 'Product'));
      description.append(node('small', '', item.variant_name || 'Standard')); copy.append(description);
      var controls = node('div', 'cp-cart-controls'); var label = node('label', '', 'Qty ');
      var select = node('select', 'cc-select'); select.setAttribute('aria-label', 'Quantity for ' + (item.name || 'product'));
      for (var quantity = 1; quantity <= Math.min(20, Math.max(1, Number(item.available_quantity) || 1)); quantity++) {
        var option = node('option', '', String(quantity)); option.value = String(quantity);
        if (quantity === Number(item.quantity)) option.selected = true;
        select.append(option);
      }
      select.addEventListener('change', async function () {
        select.disabled = true;
        try { render((await request('PATCH', endpoint, { item_id: item.id, variant_id: item.variant_id, quantity: Number(select.value) })).cart); }
        catch (error) { message(error.message); select.value = String(item.quantity); }
        finally { select.disabled = false; }
      });
      label.append(select); controls.append(label);
      var remove = node('button', '', 'Remove'); remove.type = 'button';
      remove.setAttribute('aria-label', 'Remove ' + (item.name || 'product'));
      remove.addEventListener('click', async function () {
        remove.disabled = true;
        try { render((await request('DELETE', endpoint + '?item_id=' + encodeURIComponent(item.id))).cart); message('Item removed from your bag.'); }
        catch (error) { message(error.message); remove.disabled = false; }
      });
      controls.append(remove); copy.append(controls);
      var price = node('div', 'cp-cart-price');
      price.append(node('strong', '', money(item.line_subtotal)), node('span', '', Number(item.available_quantity) > 0 ? 'In stock' : 'Out of stock'));
      article.append(link, copy, price); root.append(article);
    });
    if (note) root.append(note);
  }
  checkout.addEventListener('click', function (event) {
    if (checkout.getAttribute('aria-disabled') === 'true') { event.preventDefault(); message('Add a product before checkout.'); }
  });
  request('GET', endpoint).then(function (result) { render(result.cart || {}); }).catch(function (error) {
    root.replaceChildren(node('p', '', 'Your bag could not be loaded. Please refresh the page.'));
    checkout.setAttribute('aria-disabled', 'true');
    message(error.message);
  });
})();
