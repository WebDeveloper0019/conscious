(function () {
  'use strict';
  var root=document.querySelector('[data-live-tracking]');if(!root)return;
  var choice=root.querySelector('[data-track-order]'),content=root.querySelector('[data-tracking-content]');
  var params=new URLSearchParams(location.search);var requested=params.get('order_id')||'';
  function el(tag,className,text){var node=document.createElement(tag);if(className)node.className=className;if(text!==undefined)node.textContent=text;return node;}
  function clean(value){return String(value||'').replace(/_/g,' ').replace(/\b\w/g,function(m){return m.toUpperCase();});}
  function date(value){if(!value)return 'Not yet recorded';var raw=String(value);var d=new Date(raw.includes('T')?raw:raw.replace(' ','T')+'Z');return Number.isNaN(d.getTime())?raw:new Intl.DateTimeFormat('en-IN',{dateStyle:'medium',timeStyle:raw.length>10?'short':undefined,timeZone:'Asia/Kolkata'}).format(d);}
  function safeTrackingUrl(value){try{var url=new URL(String(value));return url.protocol==='https:'?url.href:null;}catch(_){return null;}}
  async function get(path){var response=await fetch('../../../api/user/'+path,{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'});var result=await response.json().catch(function(){return{};});if(!response.ok)throw Error(result.message||'Delivery information is unavailable.');return result;}
  function render(data){
    content.replaceChildren();var order=data.order||{},groups=Array.isArray(data.vendor_groups)?data.vendor_groups:[];
    var heading=el('section','cc-card cp-live-track-heading');heading.append(el('span','cx-kicker','Order '+(order.order_number||'')),el('h2','',clean(order.status)));heading.append(el('p','','Payment: '+clean(order.payment_status)+' · '+groups.length+' vendor group(s). Updates below are entered manually by vendors.'));content.append(heading);
    if(!groups.length){content.append(el('p','cp-live-track-empty','Your vendors have not created delivery groups yet. Check again after the order is confirmed.'));}
    groups.forEach(function(group){
      var section=el('section','cc-card cp-live-vendor-track');section.append(el('span','cx-kicker','Vendor-managed delivery'),el('h2','',group.vendor_name||'Vendor'));section.append(el('p','',clean(group.status)));
      var items=el('div','cp-live-track-items');(group.items||[]).forEach(function(item){var row=el('div');row.append(el('strong','',item.product_name||'Product'),el('small','','Qty '+String(item.quantity||1)+' · '+clean(item.status)));items.append(row);});section.append(items);
      var packages=Array.isArray(group.packages)?group.packages:[];
      if(!packages.length)section.append(el('p','cp-live-track-empty','The vendor has not entered a package yet. No courier has been automatically booked.'));
      packages.forEach(function(pack){
        var box=el('article','cp-live-package');box.append(el('h3','','Package '+(pack.package_number||'')+' · '+clean(pack.status)));
        var meta=el('div','cp-live-package-meta');if(pack.courier_name)meta.append(el('span','','Courier: '+pack.courier_name));if(pack.tracking_reference)meta.append(el('span','','Reference: '+pack.tracking_reference));if(pack.expected_delivery_date)meta.append(el('span','','Estimated delivery: '+date(pack.expected_delivery_date)));box.append(meta);
        var url=safeTrackingUrl(pack.tracking_url);if(url){var link=el('a','cc-btn','Open vendor-provided tracking link');link.href=url;link.target='_blank';link.rel='noopener noreferrer';box.append(link);}
        var events=Array.isArray(pack.events)?pack.events:[];var timeline=el('div','cp-live-timeline');timeline.append(el('h4','','Delivery journey'));
        if(!events.length)timeline.append(el('p','','No milestones have been entered yet.'));
        events.forEach(function(event){var row=el('article');var marker=el('span','cp-live-marker');marker.append(el('i','fa-solid fa-circle-check'));var body=el('div');body.append(el('strong','',clean(event.status)));if(event.location)body.append(el('p','','Location: '+event.location));if(event.note)body.append(el('p','',event.note));body.append(el('small','',date(event.event_at)+' · Entered by vendor'));row.append(marker,body);timeline.append(row);});box.append(timeline);section.append(box);
      });content.append(section);
    });
    var address=order.address||{};if(address.address_line1){var aside=el('section','cc-card cp-live-track-address');aside.append(el('h3','','Delivery address'),el('strong','',address.full_name||''),el('p','',[address.address_line1,address.address_line2,address.city,address.state,address.pincode].filter(Boolean).join(', ')));content.append(aside);}
  }
  async function loadOrder(id){if(!id){content.replaceChildren(el('p','','Choose an order to see delivery updates.'));return;}content.replaceChildren(el('p','','Loading vendor delivery updates…'));try{render(await get('tracking.php?order_id='+encodeURIComponent(id)));}catch(error){content.replaceChildren(el('p','cp-orders-error',error.message));}}
  choice.addEventListener('change',function(){requested=choice.value;var url=new URL(location.href);if(requested)url.searchParams.set('order_id',requested);else url.searchParams.delete('order_id');history.replaceState(null,'',url.href);loadOrder(requested);});
  if(sessionStorage.getItem('ci_demo_customer')||localStorage.getItem('ci_demo_customer')){choice.replaceChildren(new Option('Preview account has no real orders',''));content.replaceChildren(el('p','','Sign in with a seeded customer account to view vendor-entered delivery updates.'));return;}
  get('orders.php?limit=50').then(function(result){var orders=Array.isArray(result.orders)?result.orders:[];choice.replaceChildren(new Option(orders.length?'Choose an order':'No orders yet',''));orders.forEach(function(order){choice.append(new Option(order.orderNumber+' · '+clean(order.status),String(order.id)));});if(requested&&orders.some(function(order){return String(order.id)===requested;}))choice.value=requested;else if(orders.length){requested=String(orders[0].id);choice.value=requested;}else{content.replaceChildren(el('p','cp-live-track-empty','You have no orders to track yet.'));return;}loadOrder(requested);}).catch(function(error){choice.replaceChildren(new Option('Orders unavailable',''));content.replaceChildren(el('p','cp-orders-error',error.message));});
})();
