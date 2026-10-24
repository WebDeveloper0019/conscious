(function () {
  'use strict';
  var host = document.querySelector('.cp-checkout-layout');
  if (!host) return;
  if (sessionStorage.getItem('ci_demo_customer') || localStorage.getItem('ci_demo_customer')) {
    host.replaceChildren();
    var demoNotice=document.createElement('section');demoNotice.className='cp-check-section';
    var title=document.createElement('h2');title.textContent='Preview account cannot place orders';
    var copy=document.createElement('p');copy.textContent='Sign in with a real customer account to check delivery prices and use Razorpay or cash on delivery. This browser-only preview never processes a payment.';
    var link=document.createElement('a');link.className='cc-btn primary';link.href='auth/login.php';link.textContent='Sign in to continue';
    demoNotice.append(title,copy,link);host.append(demoNotice);return;
  }
  var base = '../../../api/user/';
  var money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
  var quote = null;
  var selectedAddress = '';
  var cart = null;
  var idempotency = '';
  var paymentMethod = 'online';
  var pendingPayment = null;
  try { pendingPayment = JSON.parse(sessionStorage.getItem('ci_pending_online_order') || 'null'); } catch (_) { pendingPayment = null; }
  function make(tag, className, value) {
    var element = document.createElement(tag);
    if (className) element.className = className;
    if (value !== undefined) element.textContent = value;
    return element;
  }
  function field(name, label, required, value) {
    var wrap = make('label', 'cc-field', label);
    var input = make('input', 'cc-input'); input.name = name; input.required = required;
    input.value = value || ''; wrap.append(input); return wrap;
  }
  function csrf() {
    var match = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    return sessionStorage.getItem('ci_csrf_token') || (match ? decodeURIComponent(match[1]) : '');
  }
  async function request(path, method, payload, key) {
    var headers = { Accept: 'application/json' };
    if (payload) headers['Content-Type'] = 'application/json';
    if (method !== 'GET') headers['X-CSRF-Token'] = csrf();
    if (key) headers['Idempotency-Key'] = key;
    var response = await fetch(base + path, {
      method: method, credentials: 'same-origin', cache: 'no-store', headers: headers,
      body: payload ? JSON.stringify(payload) : undefined
    });
    var result = await response.json().catch(function () { return {}; });
    if (!response.ok) throw new Error(result.message || 'Please try again.');
    return result;
  }
  var steps = document.querySelector('.cp-checkout-steps'); if (steps) steps.hidden = true;
  host.replaceChildren();
  var main = make('div', 'cp-checkout-main');
  var addresses = make('section', 'cp-check-section');
  addresses.append(make('h2', '', '1. Delivery address'));
  var addressList = make('div', 'cp-address-grid'); addresses.append(addressList);
  var addressForm = make('form', 'cc-card-body');
  addressForm.append(make('h3', '', 'Add an address'));
  [['label', 'Label, for example Home'], ['fullName', 'Full name'], ['phone', '10-digit mobile'],
    ['pincode', '6-digit PIN code'], ['addressLine1', 'Street address'], ['addressLine2', 'Area or building'],
    ['city', 'City'], ['state', 'State or union territory'], ['landmark', 'Landmark']].forEach(function (entry) {
    if(entry[0]==='state'){var wrap=make('label','cc-field',entry[1]);var select=make('select','cc-input');select.name='state';select.required=true;select.append(new Option('Choose state or UT',''));wrap.append(select);addressForm.append(wrap);}
    else addressForm.append(field(entry[0], entry[1], !['addressLine2', 'landmark'].includes(entry[0])));
  });
  addressForm.elements.phone.inputMode = 'numeric'; addressForm.elements.phone.pattern = '[6-9][0-9]{9}';
  addressForm.elements.pincode.inputMode = 'numeric'; addressForm.elements.pincode.pattern = '[1-9][0-9]{5}';
  var saveAddress = make('button', 'cc-btn', 'Save address'); saveAddress.type = 'submit'; addressForm.append(saveAddress);
  addresses.append(addressForm); main.append(addresses);
  var pricing = make('section', 'cp-check-section'); pricing.append(make('h2', '', '2. Vendor delivery and price'));
  var quoteBox = make('div'); pricing.append(quoteBox);
  var couponLabel = field('coupon', 'Coupon code (optional)', false); pricing.append(couponLabel);
  var recalculate = make('button', 'cc-btn', 'Calculate delivery and total'); recalculate.type = 'button'; pricing.append(recalculate);
  main.append(pricing);
  var payment = make('section', 'cp-check-section'); payment.append(make('h2', '', '3. Payment'));
  payment.append(make('p', '', 'Choose how to pay. Cash on delivery is confirmed when we calculate delivery for your address; each vendor handles dispatch manually.'));
  var onlineChoice = make('label', 'cc-check cp-payment-method'); var onlineRadio = make('input'); onlineRadio.type='radio'; onlineRadio.name='checkout-payment'; onlineRadio.value='online'; onlineRadio.checked=true;
  onlineChoice.append(onlineRadio, document.createTextNode(' Pay online securely with Razorpay (UPI, card or net banking)'));
  var codChoice = make('label', 'cc-check cp-payment-method'); var codRadio = make('input'); codRadio.type='radio'; codRadio.name='checkout-payment'; codRadio.value='cod';
  codChoice.append(codRadio, document.createTextNode(' Cash on delivery — pay when your order arrives'));
  payment.append(onlineChoice,codChoice);
  var terms = make('label', 'cc-check cp-terms-accept'); var accept = make('input'); accept.type = 'checkbox';
  terms.append(accept, document.createTextNode(' I accept the order, delivery, cancellation and return terms.'));
  payment.append(terms); main.append(payment);
  var side = make('aside', 'cp-order-summary cp-checkout-summary'); side.append(make('h2', '', 'Your order'));
  var itemBox = make('div'); side.append(itemBox);
  var total = make('strong', '', 'Total calculated after address selection'); side.append(total);
  var place = make('button', 'cc-btn primary', 'Continue to online payment'); place.type = 'button'; place.disabled = true;
  side.append(place);
  var feedback = make('p','cp-checkout-feedback'); feedback.setAttribute('role', 'status'); side.append(feedback);
  host.append(main, side);
  var alert=document.getElementById('cc-alert'),alertTimer;
  new MutationObserver(function(){var message=feedback.textContent.trim();if(!message||!alert)return;alert.textContent=message;clearTimeout(alertTimer);alertTimer=setTimeout(function(){alert.textContent='';},8000);}).observe(feedback,{childList:true,subtree:true,characterData:true});
  function showError(error) { feedback.textContent = error.message || String(error); }
  function choosePayment(){paymentMethod=codRadio.checked?'cod':'online';place.textContent=paymentMethod==='cod'?'Place cash-on-delivery order':'Continue to online payment';}
  onlineRadio.addEventListener('change',choosePayment);codRadio.addEventListener('change',choosePayment);
  function finishOrder(order, simulated){sessionStorage.removeItem('ci_pending_online_order');host.replaceChildren();var confirmation=make('section','cp-check-section');confirmation.append(make('h2','','Order confirmed'),make('p','','Order '+order.order_number+' is confirmed.'),make('p','',simulated?'This was a local demo payment. No money was charged.':paymentMethod==='cod'?'Pay the order amount to the vendor on delivery. Your vendor will update each delivery milestone.':'Your vendor will manually update packing, dispatch and delivery status.'));var orders=make('a','cc-btn primary','View your orders');orders.href='orders.php';confirmation.append(orders);host.append(confirmation);}
  function loadRazorpay(){if(window.Razorpay)return Promise.resolve();return new Promise(function(resolve,reject){var script=document.createElement('script');script.src='https://checkout.razorpay.com/v1/checkout.js';script.async=true;script.onload=function(){window.Razorpay?resolve():reject(Error('Razorpay checkout did not load.'));};script.onerror=function(){reject(Error('Could not load secure Razorpay checkout. Please retry.'));};document.head.append(script);});}
  function showPending(){if(!pendingPayment)return;place.disabled=true;feedback.replaceChildren();feedback.append(document.createTextNode('Online payment for order '+pendingPayment.order_number+' is pending. Do not place a duplicate order. '));var retry=make('button','cc-btn','Continue payment');retry.type='button';retry.addEventListener('click',function(){retry.disabled=true;startOnlinePayment(pendingPayment).catch(showError).finally(function(){retry.disabled=false;});});feedback.append(retry);}
  async function refreshPending(){
    if(!pendingPayment)return;
    try{
      var status=await request('../payment/order-status.php?payment_attempt_id='+encodeURIComponent(pendingPayment.attempt_id),'GET');
      if(status.payment_status==='paid'){finishOrder({order_number:status.order_number},false);return;}
      if(status.payment_status==='failed'||status.order_status==='payment_failed'){
        sessionStorage.removeItem('ci_pending_online_order');pendingPayment=null;
        feedback.textContent='The previous online payment was not completed. No new payment was taken. You can add products and place a new order.';
        return;
      }
      pendingPayment.order_number=status.order_number;
      if(status.reservation_expired){
        place.disabled=true;feedback.replaceChildren();
        feedback.append(document.createTextNode('Order '+status.order_number+' passed its stock reservation time. Do not pay again while its payment is being reconciled. '));
        var link=make('a','cc-btn','View orders');link.href='orders.php';feedback.append(link);
        return;
      }
      showPending();
    }catch(error){
      if(error.message==='Payment attempt not found.'){
        sessionStorage.removeItem('ci_pending_online_order');pendingPayment=null;
        feedback.textContent='Your earlier checkout belongs to another session. Start a new order when ready.';
      }else{place.disabled=true;feedback.textContent='Could not check the pending payment. Do not pay again until your order status is available. '+error.message;}
    }
  }
  async function startOnlinePayment(pending){var gateway=await request('../payment/razorpay-order.php','POST',{payment_attempt_id:pending.attempt_id});if(gateway.gateway==='mock'){feedback.replaceChildren();feedback.append(document.createTextNode('Local demo payment only — no charge will be made. '));var simulate=make('button','cc-btn primary','Simulate successful online payment');simulate.type='button';simulate.addEventListener('click',async function(){simulate.disabled=true;try{var done=await request('../payment/local-demo-confirm.php','POST',{payment_attempt_id:pending.attempt_id});finishOrder({order_number:done.order_number},true);}catch(error){showError(error);simulate.disabled=false;}});feedback.append(simulate);return;}if(gateway.gateway!=='razorpay'||!gateway.key_id)throw Error('Online payment is not configured.');await loadRazorpay();var options={key:gateway.key_id,amount:gateway.amount,currency:gateway.currency,name:'Conscious Indi',description:'Order '+pending.order_number,order_id:gateway.order_id,handler:async function(response){try{await request('../payment/razorpay-verify.php','POST',{payment_attempt_id:pending.attempt_id,razorpay_order_id:response.razorpay_order_id,razorpay_payment_id:response.razorpay_payment_id,razorpay_signature:response.razorpay_signature});finishOrder(pending,false);}catch(error){feedback.textContent='Payment confirmation needs attention. '+error.message+' Do not pay again; check your orders or contact support.';}},modal:{ondismiss:function(){feedback.textContent='Payment was not confirmed. Your order remains pending; use Continue payment to retry before the reservation expires.';showPending();}}};new window.Razorpay(options).open();}
  function showAddresses(list) {
    addressList.replaceChildren();
    if (!list.length) addressList.append(make('p', '', 'Add your first delivery address below.'));
    list.forEach(function (address) {
      var label = make('label'); var radio = make('input'); radio.type = 'radio'; radio.name = 'saved-address';
      radio.value = address.id; radio.checked = address.id === selectedAddress;
      radio.addEventListener('change', function () { selectedAddress = address.id; quote = null; place.disabled = true; quoteBox.textContent = 'Calculate delivery for this address.'; });
      label.append(radio, make('strong', '', address.label + ' · ' + address.fullName),
        make('p', '', address.addressLine1 + ', ' + address.city + ', ' + address.state + ' ' + address.pincode));
      addressList.append(label);
    });
  }
  async function refreshAddresses(preferred) {
    var result = await request('addresses.php', 'GET');
    selectedAddress = preferred || selectedAddress || (result.addresses.find(function (entry) { return entry.isDefault; }) || result.addresses[0] || {}).id || '';
    showAddresses(result.addresses || []);
  }
  addressForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!addressForm.checkValidity()) { showError(Error('Complete the required delivery address fields, including a valid 10-digit mobile number and 6-digit PIN code.'));addressForm.reportValidity();return; }
    saveAddress.disabled = true; feedback.textContent = '';
    try {
      var body = Object.fromEntries(new FormData(addressForm).entries());
      var result = await request('addresses.php', 'POST', body);
      addressForm.reset(); quote = null; place.disabled = true;
      await refreshAddresses(result.address.id);
      feedback.textContent = 'Address saved. Checking delivery and payment options…';
      if (cart && cart.items && cart.items.length) recalculate.click();
    } catch (error) { showError(error); }
    finally { saveAddress.disabled = false; }
  });
  recalculate.addEventListener('click', async function () {
    if (!selectedAddress) { feedback.textContent = 'Choose or add a delivery address first.'; return; }
    recalculate.disabled = true; place.disabled = true; feedback.textContent = '';
    try {
      var result = await request('checkout-quote.php', 'POST', { address_id: selectedAddress, coupon_code: couponLabel.querySelector('input').value.trim() });
      quote = result.quote; idempotency = '';
      var vendors = Object.values(quote.vendors || {});
      var canCod = vendors.length > 0 && vendors.every(function(vendor){return vendor.cod_enabled === true || Number(vendor.cod_enabled) === 1;});
      codRadio.disabled = !canCod;
      if(!canCod && codRadio.checked){onlineRadio.checked=true;choosePayment();}
      if(!canCod) {
        var unavailable = vendors.filter(function(vendor){return vendor.cod_enabled !== true && Number(vendor.cod_enabled) !== 1;}).map(function(vendor){return vendor.vendor_name;});
        feedback.textContent = 'Cash on delivery is not offered by ' + (unavailable.join(', ') || 'this order') + '. Choose online payment or remove that vendor’s items.';
      }
      quoteBox.replaceChildren();
      quoteBox.append(make('p', '', 'Products: ' + money.format(Number(quote.subtotal))));
      Object.keys(quote.vendors || {}).forEach(function (key) {
        var vendor = quote.vendors[key];
        quoteBox.append(make('p', '', 'Vendor-managed delivery: ' + money.format(Number(vendor.shipping))));
      });
      quoteBox.append(make('p', '', 'GST: ' + money.format(Number(quote.tax_amount))));
      if (Number(quote.discount_amount)) quoteBox.append(make('p', '', 'Discount: −' + money.format(Number(quote.discount_amount))));
      total.textContent = 'Total: ' + money.format(Number(quote.total_amount));
      place.disabled = false;
      if(canCod) feedback.textContent = 'Cash on delivery is available. Price reserved for 15 minutes; delivery is managed manually by each vendor.';
    } catch (error) { quote = null; showError(error); }
    finally { recalculate.disabled = false; }
  });
  place.addEventListener('click', async function () {
    if (pendingPayment) { showPending(); return; }
    if (!quote) { feedback.textContent = 'Calculate delivery first.'; return; }
    if (!accept.checked) { feedback.textContent = 'Accept the order terms to continue.'; accept.focus(); return; }
    place.disabled = true;
    if (!idempotency) idempotency = crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + '-checkout-' + Math.random().toString(36).slice(2);
    try {
      var result = await request('checkout-place.php', 'POST', { quote_id: quote.id, payment_method: paymentMethod }, idempotency);
      if(paymentMethod==='cod'){finishOrder(result.order,false);return;}
      pendingPayment={attempt_id:result.payment.attempt_id,order_number:result.order.order_number};
      sessionStorage.setItem('ci_pending_online_order',JSON.stringify(pendingPayment));
      showPending();
      await startOnlinePayment(pendingPayment);
    } catch (error) { showError(error); place.disabled = false; }
  });
  Promise.all([request('cart.php', 'GET'), refreshAddresses(),request('../public/india-states.php','GET')]).then(function (results) {
    (results[2].states||[]).forEach(function(state){addressForm.elements.state.append(new Option(state,state));});
    cart = results[0].cart;
    if (!cart || !cart.items || !cart.items.length) { itemBox.append(make('p', '', pendingPayment?'Your cart was converted to a pending online order.':'Your bag is empty. Add a product before checkout.')); recalculate.disabled = true; return; }
    cart.items.forEach(function (item) { itemBox.append(make('p', '', item.name + ' × ' + item.quantity + ' · ' + money.format(Number(item.line_subtotal)))); });
    total.textContent = 'Products: ' + money.format(Number(cart.subtotal)) + ' · Checking delivery…';
    if (selectedAddress && !pendingPayment) recalculate.click();
  }).catch(showError);
  if(pendingPayment)refreshPending();
})();
