(function () {
  'use strict';
  var root = document.querySelector('[data-customer-dashboard]');
  if (!root) return;
  var demo = sessionStorage.getItem('ci_demo_customer') || localStorage.getItem('ci_demo_customer');
  var errorBox = root.querySelector('[data-dashboard-error]');
  var retry = root.querySelector('[data-dashboard-retry]');
  function target(name) { return root.querySelector('[data-dashboard-' + name + ']'); }
  function put(name, value) { var el = target(name); if (el) el.textContent = value; }
  function element(tag, text, className) { var el = document.createElement(tag); if (text !== undefined) el.textContent = text; if (className) el.className = className; return el; }
  function money(value) { return new Intl.NumberFormat('en-IN', {style:'currency',currency:'INR'}).format(Number(value) || 0); }
  function date(value) { if (!value) return ''; var d = new Date(String(value).replace(' ', 'T') + (String(value).includes('Z') ? '' : 'Z')); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('en-IN',{day:'numeric',month:'short',year:'numeric',timeZone:'Asia/Kolkata'}).format(d); }
  function loading() {
    if (errorBox) errorBox.hidden = true;
    if (retry) retry.disabled = true;
    put('intro', 'Loading your orders, bookings and rewards…');
    ['orders','bookings','actions'].forEach(function (name) { put(name, 'Loading…'); });
  }
  function render(data) {
    var customer = data.customer || {}, stats = data.stats || {};
    var name = customer.first_name || customer.firstName || 'there';
    put('date', new Intl.DateTimeFormat('en-IN',{weekday:'long',day:'numeric',month:'long',timeZone:'Asia/Kolkata'}).format(new Date()));
    put('greeting', 'Welcome back, ' + name + '.');
    put('intro', 'Your real orders, vendor-managed deliveries, event bookings and rewards are shown below.');
    put('points', String(Number(stats.rewardPoints) || 0) + ' points');
    put('order-count', String(Number(stats.orders) || 0) + ' total orders');
    put('active-count', String(Number(stats.activeOrders) || 0) + ' active orders');
    var bookings = Array.isArray(data.upcomingBookings) ? data.upcomingBookings : [];
    put('booking-count', String(bookings.length) + ' upcoming bookings');
    var orderBox = target('orders'); orderBox.replaceChildren();
    var orders = Array.isArray(data.recentOrders) ? data.recentOrders : [];
    if (!orders.length) {
      orderBox.append(element('p','No orders yet. Your first order will appear here after checkout.'));
      var shop = element('a','Explore products','cc-btn'); shop.href='shop.php'; orderBox.append(shop);
    } else orders.forEach(function (order) {
      var row = element('div',undefined,'cx-order-meta');
      var summary = element('span'); summary.append(element('small','Order'),element('b',order.orderNumber || 'Order')); row.append(summary);
      var status = element('span'); status.append(element('small','Status'),element('b',String(order.status || 'pending').replace(/_/g,' '))); row.append(status);
      var total = element('span'); total.append(element('small','Total'),element('b',money(order.totalAmount))); row.append(total);
      orderBox.append(row);
      if(order.estimatedDeliveryDate)orderBox.append(element('p','Estimated delivery: '+date(order.estimatedDeliveryDate)+' · updated manually by vendor'));
    });
    var bookingBox = target('bookings'); bookingBox.replaceChildren();
    if (!bookings.length) {
      bookingBox.append(element('p','You have no upcoming event bookings.'));
      var explore = element('a','Explore events','cc-btn primary'); explore.href='events.php'; bookingBox.append(explore);
    } else bookings.forEach(function (booking) {
      bookingBox.append(element('h3',booking.title || 'Event booking'));
      bookingBox.append(element('p',date(booking.starts_at) + ' · ' + (booking.venue_name || 'Online or venue details to follow')));
      bookingBox.append(element('p',String(booking.quantity || 1)+' ticket(s) · '+String(booking.status || 'pending').replace(/_/g,' ')));
      var link=element('a','View booking','cc-btn'); link.href='events.php#bookings'; bookingBox.append(link);
    });
    var actionBox=target('actions'); actionBox.replaceChildren();
    var actions=Array.isArray(data.pendingActions)?data.pendingActions:[];
    if(!actions.length)actionBox.append(element('p','You are all caught up. We will show payment, review and delivery tasks here when needed.'));
    actions.forEach(function(action){var p=element('p',action.title || 'Action needed'); actionBox.append(p); if(action.type==='payment') {var a=element('a','View orders','cc-btn');a.href='orders.php';actionBox.append(a);} });
    if (errorBox) errorBox.hidden = true;
    if (retry) retry.disabled = false;
  }
  if (demo) {
    put('greeting','Preview dashboard'); put('intro','This browser-only preview has no real orders or bookings. Sign in with a seeded customer account to use the live workflow.');
    ['orders','bookings','actions'].forEach(function (name) { put(name,'Sign in to see live data.'); });
    put('points','—'); put('order-count','Preview only'); put('active-count','Preview only'); put('booking-count','Preview only');
    return;
  }
  function load() {
    loading();
    fetch('../../../api/user/dashboard.php',{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'}).then(function(response){return response.json().then(function(data){if(!response.ok)throw Error(data.message || 'Dashboard is unavailable.');return data;});}).then(render).catch(function(error){
      put('intro','Your account summary is temporarily unavailable.');
      ['orders','bookings','actions'].forEach(function(name){put(name,'Unable to load right now.');});
      if (errorBox) {
        errorBox.hidden = false;
        var message = errorBox.querySelector('[data-dashboard-error-message]');
        if (message) message.textContent = error.message || 'Please check your connection and try again.';
      }
      if (retry) retry.disabled = false;
    });
  }
  if (retry) retry.addEventListener('click', load);
  load();
})();
