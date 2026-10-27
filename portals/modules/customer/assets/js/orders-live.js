(function () {
  'use strict';
  var root = document.querySelector('[data-live-orders]');
  if (!root) return;
  var list = document.querySelector('[data-order-list]');
  var detail = document.querySelector('[data-order-detail]');
  var more = document.querySelector('[data-order-more]');
  var search = document.querySelector('[data-order-search]');
  var dialog = document.querySelector('[data-order-cancel-dialog]');
  var cancelForm = document.querySelector('[data-cancel-form]');
  var offset = 0, query = '', total = 0, cancelling = '';
  var formatMoney = new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'});
  function money(value) { return formatMoney.format(Number(value) || 0); }
  function node(tag, className, value) { var el=document.createElement(tag); if(className)el.className=className; if(value!==undefined)el.textContent=value; return el; }
  function date(value) { if(!value)return 'Date pending'; var raw=String(value); var parsed=new Date(raw.includes('T')?raw:raw.replace(' ','T')+'Z'); return Number.isNaN(parsed.getTime())?raw:new Intl.DateTimeFormat('en-IN',{day:'numeric',month:'short',year:'numeric',timeZone:'Asia/Kolkata'}).format(parsed); }
  function statusLabel(value) { return String(value||'pending').replace(/_/g,' ').replace(/\b\w/g,function(m){return m.toUpperCase();}); }
  function csrf() { var match=document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/); return sessionStorage.getItem('ci_csrf_token')||(match?decodeURIComponent(match[1]):''); }
  async function api(path, method, body, key) {
    var headers={Accept:'application/json'}; if(body)headers['Content-Type']='application/json';if(method!=='GET')headers['X-CSRF-Token']=csrf();if(key)headers['Idempotency-Key']=key;
    var response=await fetch('../../../api/user/'+path,{method:method,credentials:'same-origin',cache:'no-store',headers:headers,body:body?JSON.stringify(body):undefined});
    var result=await response.json().catch(function(){return {};}); if(!response.ok)throw Error(result.message||'Request failed.'); return result;
  }
  function action(label, fn, className) { var button=node('button',className||'cc-btn',label);button.type='button';button.addEventListener('click',fn);return button; }
  function itemLine(item) { var row=node('div','cp-live-order-item'); if(item.image){var image=node('img');image.src=item.image;image.alt=item.productName||'Product';row.append(image);}var body=node('div');body.append(node('strong','',item.productName||'Product'),node('small','',String(item.quantity||1)+' × '+money(item.unitPrice)));row.append(body);return row; }
  function showCard(order) {
    var card=node('article','cp-order-card');var head=node('header');var left=node('div');left.append(node('span','','ORDER '+order.orderNumber),node('small','','Placed '+date(order.createdAt)));var right=node('div');right.append(node('b','',money(order.totalAmount)),node('span','cc-status',statusLabel(order.status)));head.append(left,right);card.append(head);
    var body=node('div','cp-live-order-body');body.append(node('p','',String(order.itemCount||0)+' item(s) · '+(order.status==='payment_pending'?'Awaiting payment confirmation':'Vendor-managed delivery')));
    var buttons=node('div','cp-order-buttons');buttons.append(action('Order details',function(){openDetails(order.orderNumber);}));
    var track=node('a','cc-btn','Track packages');track.href='tracking.php?order_id='+encodeURIComponent(order.id);buttons.append(track);body.append(buttons);card.append(body);return card;
  }
  async function load(reset) {
    if(reset){offset=0;total=0;list.replaceChildren();detail.hidden=true;}
    more.disabled=true;if(offset===0)list.append(node('p','cp-orders-status','Loading your orders…'));
    try{
      var result=await api('orders.php?limit=10&offset='+offset+'&search='+encodeURIComponent(query),'GET');
      if(offset===0)list.replaceChildren();
      total=Number(result.total)||0;var orders=Array.isArray(result.orders)?result.orders:[];
      if(!orders.length&&offset===0){var empty=node('section','cc-card cp-orders-empty');empty.append(node('h2','','No orders found'),node('p','',query?'Try another order number.':'Your orders and vendor delivery updates will appear here after checkout.'));var shop=node('a','cc-btn primary','Explore products');shop.href='shop.php';empty.append(shop);list.append(empty);}
      orders.forEach(function(order){list.append(showCard(order));});offset+=orders.length;more.hidden=offset>=total;more.disabled=false;
    }catch(error){if(offset===0)list.replaceChildren();list.append(node('p','cp-orders-error',error.message));more.hidden=true;}
  }
  async function openDetails(orderNumber) {
    detail.hidden=false;detail.replaceChildren(node('p','','Loading order details…'));detail.scrollIntoView({behavior:'smooth',block:'start'});
    try{
      var results=await Promise.all([api('order-details.php?orderNumber='+encodeURIComponent(orderNumber),'GET'),api('cancel-preview.php?orderNumber='+encodeURIComponent(orderNumber),'GET')]);
      var order=results[0].order, preview=results[1];detail.replaceChildren();
      var head=node('header');var title=node('div');title.append(node('span','cx-kicker','Order details'),node('h2','',order.orderNumber));var help=node('a','cc-btn','Get help');help.href='support.php';head.append(title,help);detail.append(head);
      var grid=node('div','cp-detail-grid');[
        ['Status',statusLabel(order.status),'Payment and vendor updates are shown separately.'],
        ['Total',money(order.totalAmount),'Products '+money(order.subtotal)+' · delivery '+money(order.shippingAmount)],
        ['Placed',date(order.createdAt),String(order.items.length)+' product line(s)']
      ].forEach(function(entry){var col=node('div');col.append(node('small','',entry[0]),node('b','',entry[1]),node('p','',entry[2]));grid.append(col);});detail.append(grid);
      var items=node('div','cp-live-order-items');order.items.forEach(function(item){items.append(itemLine(item));});detail.append(items);
      var controls=node('div','cp-live-order-actions');var track=node('a','cc-btn primary','Track vendor packages');track.href='tracking.php?order_id='+encodeURIComponent(order.id);controls.append(track);
      controls.append(action('View invoice',function(){showInvoice(order.orderNumber);}));
      if(preview.canCancel)controls.append(action('Cancel eligible order',function(){openCancel(preview);},'cc-btn danger'));
      detail.append(controls);
    }catch(error){detail.replaceChildren(node('p','cp-orders-error',error.message));}
  }
  async function showInvoice(orderNumber) {
    var box=detail.querySelector('[data-order-invoice]');if(!box){box=node('div','cp-live-invoice');box.dataset.orderInvoice='';detail.append(box);}box.textContent='Loading invoice…';
    try{var result=await api('invoice.php?orderNumber='+encodeURIComponent(orderNumber),'GET');var inv=result.invoice||{};box.replaceChildren(node('h3','','Invoice '+(inv.number||'')),node('p','','Issued '+date(inv.issuedAt)+' · Total '+money(inv.totals&&inv.totals.grandTotal)+' · GST '+money(inv.totals&&inv.totals.totalTax)));}
    catch(error){box.textContent=error.message;}
  }
  function openCancel(preview){cancelling=preview.orderNumber;cancelForm.reset();document.querySelector('[data-cancel-info]').textContent='Order '+preview.orderNumber+' · cancellation available until '+date(preview.cancelDeadline)+'. '+(preview.estimatedRefund?'Estimated refund: '+money(preview.estimatedRefund)+'. A refund record may need admin review.':'No payment has been captured for this order.');document.querySelector('[data-cancel-status]').textContent='';dialog.showModal();}
  document.querySelector('[data-cancel-close]').addEventListener('click',function(){dialog.close();});
  cancelForm.addEventListener('submit',async function(event){event.preventDefault();if(!cancelForm.reportValidity()||!cancelling)return;var button=cancelForm.querySelector('button[type=submit]');button.disabled=true;var status=document.querySelector('[data-cancel-status]');status.textContent='Submitting cancellation…';try{var reason=cancelForm.elements.reason.value;if(cancelForm.elements.note.value.trim())reason+=': '+cancelForm.elements.note.value.trim();var key=crypto.randomUUID?crypto.randomUUID():String(Date.now())+'-cancel';var result=await api('cancel-order.php','POST',{orderNumber:cancelling,scope:'full',reason:reason},key);status.textContent=result.message||'Cancellation recorded.';dialog.close();await load(true);await openDetails(cancelling);}catch(error){status.textContent=error.message;}finally{button.disabled=false;}});
  search.addEventListener('submit',function(event){event.preventDefault();query=search.querySelector('input').value.trim();load(true);});more.addEventListener('click',function(){load(false);});
  if(sessionStorage.getItem('ci_demo_customer')||localStorage.getItem('ci_demo_customer')){list.replaceChildren(node('p','','This browser-only preview has no real orders. Sign in with a seeded customer account to use live order tracking.'));more.hidden=true;return;}
  load(true);
})();
