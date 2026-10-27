(() => {
  'use strict';
  const api = '../../../api/vendor/';
  const byId = id => document.getElementById(id);
  const money = value => new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR', maximumFractionDigits:2}).format(Number(value) || 0);
  const count = (items, statuses) => items.filter(item => statuses.includes(item.status)).length;
  async function get(path) {
    const response = await fetch(api + path, {credentials:'same-origin', cache:'no-store', headers:{Accept:'application/json'}});
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) throw new Error(data.message || 'Your store activity could not be loaded.');
    return data;
  }
  function task(title, detail, href) {
    const link = document.createElement('a');
    link.className = 'vc-list-item'; link.href = href;
    const copy = document.createElement('div'); copy.className = 'vc-list-copy';
    const heading = document.createElement('strong'); heading.textContent = title;
    const note = document.createElement('p'); note.textContent = detail;
    copy.append(heading, note); link.append(copy);
    return link;
  }
  async function load() {
    try {
      const [identity, productsData, ordersData, stockData, payoutsData, returnsData, codData] = await Promise.all([
        get('auth/me.php'), get('products.php'), get('orders.php'), get('inventory.php'),
        get('payouts.php'), get('returns.php'), get('cod-collections.php')
      ]);
      const vendor = identity.vendor || {};
      const products = productsData.products || [];
      const orders = ordersData.orders || [];
      const stock = stockData.inventory || [];
      const returns = returnsData.returns || [];
      const cod = codData.collections || [];
      byId('vd-store-status').textContent = vendor.status ? 'Store ' + vendor.status.replaceAll('_', ' ') : 'Vendor account';
      byId('vd-verification-status').textContent = vendor.verification_status ? 'Verification: ' + vendor.verification_status.replaceAll('_', ' ') : '';
      byId('vd-new-orders').textContent = count(orders, ['placed','pending','awaiting_acceptance']);
      byId('vd-products').textContent = count(products, ['approved','active']);
      byId('vd-products-note').textContent = products.length + ' total products including drafts';
      byId('vd-payout').textContent = money(payoutsData.available);
      byId('vd-delivered').textContent = count(orders, ['delivered']);
      byId('vd-preparing').textContent = count(orders, ['accepted','processing','quality_check','packing']);
      byId('vd-ready').textContent = count(orders, ['ready_to_dispatch']);
      byId('vd-shipping').textContent = count(orders, ['dispatched','in_transit','out_for_delivery']);
      const pendingReturns = returns.filter(item => ['requested','evidence_requested','received'].includes(item.status));
      const lowStock = stock.filter(item => Number(item.available_quantity) <= Number(item.low_stock_threshold));
      byId('vd-returns').textContent = pendingReturns.length;
      byId('vd-low-stock').textContent = lowStock.length;
      byId('vd-cod').textContent = cod.filter(item => item.status !== 'collected' && item.group_status === 'delivered').length;
      const tasks = byId('vd-tasks'); tasks.replaceChildren();
      orders.filter(item => ['placed','pending','awaiting_acceptance'].includes(item.status)).slice(0,3).forEach(item => tasks.append(task('Review order ' + item.group_number, 'Accept or reject this order after confirming stock.', 'orders.php')));
      orders.filter(item => item.status === 'ready_to_dispatch').slice(0,3).forEach(item => tasks.append(task('Dispatch order ' + item.group_number, 'Arrange your courier and enter package details.', 'delivery.php')));
      pendingReturns.slice(0,3).forEach(item => tasks.append(task('Review return for ' + item.product_name, 'Customer request needs a vendor decision.', 'returns.php')));
      lowStock.slice(0,3).forEach(item => tasks.append(task('Check stock: ' + item.name, item.available_quantity + ' available for ' + item.sku + '.', 'inventory.php')));
      if (!tasks.childElementCount) {
        const empty = document.createElement('div'); empty.className = 'vc-empty'; empty.textContent = 'No urgent tasks from your live store records.'; tasks.append(empty);
      }
      byId('vd-message').textContent = 'Showing live data for ' + (vendor.display_name || 'your store') + '. No preview orders or balances are included.';
    } catch (error) {
      byId('vd-message').textContent = error.message;
      byId('vd-message').classList.add('warning');
    }
  }
  load();
})();
