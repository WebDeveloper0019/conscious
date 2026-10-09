
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

  function showMessage(message) {
    const element = $('trackingMessage');
    if (!element) return;

    element.textContent = message;
    element.hidden = false;
  }

  async function loadTracking() {
    const params = new URLSearchParams(window.location.search);
    const orderNumber = params.get('orderNumber');
    const orderId = params.get('id');

    if (!orderNumber && !orderId) {
      showMessage('Please select Track Order from one of your orders.');
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
        throw new Error(data.message || 'Unable to load this order.');
      }

      const order = data.order;
      const status = String(order.status || 'Unknown')
        .replace(/_/g, ' ');

      const message = $('trackingMessage');
      message.textContent =
        'Order ' + (order.orderNumber || '') +
        ' — Current status: ' + status + '.';
      message.hidden = false;

      const content = $('trackingContent');
      content.hidden = false;

      const timeline = $('trackingTimeline');
      timeline.replaceChildren();

      const statusText = document.createElement('p');
      statusText.textContent = 'Current order status: ' + status;
      timeline.appendChild(statusText);

      const trackingNote = document.createElement('p');
      trackingNote.textContent =
        'Courier tracking events are not available in the current order data.';
      timeline.appendChild(trackingNote);

      $('trackingAddress').textContent =
        'Delivery address is not available from the current order API.';

      $('trackingCourier').textContent =
        'Courier, tracking number and estimated delivery date have not been recorded in the current order data.';

      document.title =
        'Track Order ' + (order.orderNumber || '') + ' — Conscious Indi';
    } catch (error) {
      showMessage(error.message || 'Could not load tracking information.');
      console.error('Tracking error:', error);
    }
  }

  document.addEventListener('DOMContentLoaded', loadTracking);
})();

