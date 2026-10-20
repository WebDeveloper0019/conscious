
(function () {
  'use strict';

  const $ = (id) => document.getElementById(id);

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[char]);
  }

  function money(value) {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      maximumFractionDigits: 2
    }).format(Number(value || 0));
  }

  function display(id, value) {
    const element = $(id);
    if (element) element.textContent = value;
  }

  function showMessage(message) {
    const items = $('detailOrderItems');
    if (items) {
      items.innerHTML =
        '<p style="padding:20px;text-align:center;">' +
        escapeHtml(message) +
        '</p>';
    }
  }

  function imageUrl(path) {
    if (!path) return '';

    const value = String(path).trim();

    // Do not allow unsafe URL schemes.
    if (/^(javascript|data|vbscript):/i.test(value)) return '';

    if (/^https?:\/\//i.test(value)) return value;

    // Product image paths in this project may be relative to the site root.
    return '../' + value.replace(/^\.?\//, '');
  }

  function renderItems(items) {
    const container = $('detailOrderItems');
    if (!container) return;

    if (!items || !items.length) {
      showMessage('No product items were found for this order.');
      return;
    }

    container.innerHTML = items.map((item) => {
      const image = imageUrl(item.image);
      const quantity = Number(item.quantity || 0);
      const price = Number(item.unitPrice || 0);

      return `
        <div class="d-flex gap-3 align-items-start py-3"
             style="border-bottom:1px solid var(--border-light);">
          <div style="width:72px;height:72px;flex-shrink:0;">
            ${
              image
                ? `<img src="${escapeHtml(image)}"
                        alt="${escapeHtml(item.productName || 'Product')}"
                        loading="lazy"
                        style="width:72px;height:72px;object-fit:cover;border-radius:8px;"
                        onerror="this.style.display='none';">`
                : '<div style="width:72px;height:72px;background:var(--bg-cream);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-box"></i></div>'
            }
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;color:var(--text-primary);">
              ${escapeHtml(item.productName || 'Product')}
            </div>
            <div style="font-size:.8125rem;color:var(--text-muted);margin-top:5px;">
              Quantity: ${quantity}
            </div>
            <div style="font-size:.8125rem;color:var(--text-muted);margin-top:4px;">
              Unit price: ${money(price)}
            </div>
          </div>
          <div style="font-weight:700;white-space:nowrap;">
            ${money(price * quantity)}
          </div>
        </div>
      `;
    }).join('');
  }

  async function loadOrder() {
    const params = new URLSearchParams(window.location.search);
    const orderNumber = params.get('orderNumber');
    const orderId = params.get('id');

    if (!orderNumber && !orderId) {
      showMessage('Please open Order Details from your My Orders page.');
      display('detailOrderSubtitle', 'Select an order to view its details.');
      return;
    }

    const query = orderNumber
      ? 'orderNumber=' + encodeURIComponent(orderNumber)
      : 'id=' + encodeURIComponent(orderId);

    try {
      const response = await fetch(
        '../api/user/order-details.php?' + query,
        {
          method: 'GET',
          credentials: 'same-origin',
          headers: { Accept: 'application/json' }
        }
      );

      const data = await response.json();

      if (!response.ok || !data.success || !data.order) {
        throw new Error(
          data.message || 'Unable to load this order.'
        );
      }

      const order = data.order;

      display('detailPageTitle', 'Order Details');
      display(
        'detailOrderSubtitle',
        'Order ' + (order.orderNumber || '') +
        ' · Placed on ' +
        (order.createdAt
          ? new Date(String(order.createdAt).replace(' ', 'T'))
              .toLocaleDateString('en-IN')
          : 'Date unavailable')
      );

      display(
        'detailOrderStatus',
        String(order.status || 'Unknown').replace(/_/g, ' ')
      );

const tracking = document.getElementById('detailTracking');

if (tracking) {
  const status = String(order.status || 'Unknown')
    .replace(/_/g, ' ');

  tracking.textContent = 'Current order status: ' + status;
}


      display('detailSubtotal', money(order.subtotal));
      display('detailDiscount', money(order.discountAmount));
      display('detailShipping', money(order.shippingAmount));
      display('detailTotal', money(order.totalAmount));

      renderItems(order.items || []);

    } catch (error) {
      console.error('Order details error:', error);
      showMessage(error.message || 'Could not load order details.');
    }
  }

  document.addEventListener('DOMContentLoaded', loadOrder);
})();