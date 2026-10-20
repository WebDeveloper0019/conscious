(function () {
  'use strict';

  const base = '../../../api/vendor/';
  const state = {csrf: '', group: null, packages: [], settings: null};
  const message = document.getElementById('dl-message');
  const rupees = new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR'});
  const transitions = {
    ready_to_dispatch:['dispatched'], dispatched:['picked_up','in_transit','delivered','delivery_attempted'],
    picked_up:['in_transit','at_destination','delivery_attempted'],
    in_transit:['at_destination','out_for_delivery','delivery_attempted','delivery_failed','delivered','return_in_transit'],
    at_destination:['out_for_delivery','delivery_attempted','delivery_failed','delivered','return_in_transit'],
    out_for_delivery:['delivered','delivery_attempted','delivery_failed'],
    delivery_attempted:['out_for_delivery','delivery_failed','delivered','return_in_transit'],
    delivery_failed:['out_for_delivery','return_in_transit'],return_in_transit:['returned_to_vendor']
  };
  const orderSteps = {awaiting_acceptance:['accepted','Accept order'],accepted:['processing','Start processing'],processing:['quality_check','Record quality check'],quality_check:['packing','Start packing'],packing:['ready_to_dispatch','Ready to dispatch']};

  function element(tag, className, value) {
    const item = document.createElement(tag);
    if (className) item.className = className;
    if (value !== undefined) item.textContent = String(value);
    return item;
  }
  function announce(value, error) {
    message.textContent = value;
    message.hidden = false;
    message.classList.toggle('vc-delivery-error', Boolean(error));
    message.scrollIntoView({block:'nearest'});
  }
  function normalize(value) { return String(value || '').replaceAll('_', ' '); }
  function token() {
    const match = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    return state.csrf || (match ? decodeURIComponent(match[1]) : '');
  }
  async function request(path, method='GET', body) {
    const headers = {Accept:'application/json'};
    if (method !== 'GET') headers['X-CSRF-Token'] = token();
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    if (path === 'shipments.php' && method === 'POST') headers['Idempotency-Key'] = crypto.randomUUID();
    if (path === 'shipments.php' && method === 'PATCH') headers['Idempotency-Key'] = crypto.randomUUID();
    const response = await fetch(base+path, {method, credentials:'same-origin', cache:'no-store', headers, body:body === undefined ? undefined : JSON.stringify(body)});
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw Error(data.message || 'Unable to complete this action.');
    return data;
  }
  function submit(form, handler) {
    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (!form.reportValidity()) return;
      const button = form.querySelector('[type=submit]');
      button.disabled = true;
      try { await handler(new FormData(form)); }
      catch (error) { announce(error.message, true); }
      finally { button.disabled = false; }
    });
  }

  async function loadOrders() {
    const data = await request('orders.php');
    const list = document.getElementById('dl-order-list');
    list.replaceChildren();
    if (!data.orders.length) {
      list.append(element('p','vc-help','No real orders yet. When a customer places an order, it will appear here.'));
      return;
    }
    data.orders.forEach(order => {
      const row = element('div','dl-row');
      const text = element('div');
      text.append(element('strong','',order.group_number),element('small','',normalize(order.status)+' · '+order.item_count+' item(s) · '+rupees.format(Number(order.total_amount))));
      const button = element('button','vc-btn small','Open');
      button.type = 'button';
      button.addEventListener('click', () => openOrder(order.id).catch(error => announce(error.message, true)));
      row.append(text,button);list.append(row);
    });
  }
  async function openOrder(id) {
    const [orderResult, packagesResult] = await Promise.all([request('orders.php?id='+encodeURIComponent(id)),request('shipments.php?group_id='+encodeURIComponent(id))]);
    state.group = orderResult.order;
    state.packages = packagesResult.packages || [];
    const group = state.group;
    document.getElementById('dl-order-detail').hidden = false;
    document.getElementById('dl-order-title').textContent = group.group_number;
    document.getElementById('dl-order-status').textContent = normalize(group.status);
    const address = group.address || {};
    document.getElementById('dl-order-address').textContent = [address.full_name,address.address_line1,address.address_line2,address.city,address.state,address.pincode].filter(Boolean).join(', ');
    const items = document.getElementById('dl-order-items');
    items.replaceChildren(element('strong','','Order items'));
    (group.items || []).forEach(item => items.append(element('p','',item.product_name+' · '+item.quantity+' × '+rupees.format(Number(item.unit_price)))));
    const actions = document.getElementById('dl-order-actions');actions.replaceChildren();
    const step = orderSteps[group.status];
    if (step) {
      const button = element('button','vc-btn primary',step[1]);button.type='button';
      button.addEventListener('click', async () => {
        button.disabled=true;
        try { const result=await request('orders.php','POST',{group_id:group.id,status:step[0]});announce(result.message);await Promise.all([loadOrders(),openOrder(group.id)]); }
        catch(error){announce(error.message,true);button.disabled=false;}
      });actions.append(button);
    }
    renderPackageItems();
    renderPackages();
    document.getElementById('dl-package-card').hidden = !['packing','ready_to_dispatch','partially_dispatched'].includes(group.status);
    document.getElementById('dl-packages-card').hidden = state.packages.length === 0;
  }
  function remaining(item) {
    return Math.max(0, Number(item.quantity)-state.packages.reduce((sum,packageItem) => sum+(packageItem.items||[]).filter(entry=>Number(entry.order_item_id)===Number(item.id)).reduce((part,entry)=>part+Number(entry.quantity),0),0));
  }
  function renderPackageItems() {
    const box=document.getElementById('dl-package-items');box.replaceChildren();
    (state.group.items||[]).forEach(item=>{
      const available=remaining(item);
      if (!available) return;
      const label=element('label','dl-item-quantity',item.product_name+' · remaining '+available);
      const input=element('input','vc-input');input.type='number';input.min='0';input.max=String(available);input.step='1';input.value=String(available);input.dataset.itemId=item.id;label.append(input);box.append(label);
    });
    if (!box.children.length) box.append(element('p','vc-help','All order items have been assigned to packages.'));
  }
  function localNow() {
    const now=new Date();const shifted=new Date(now.getTime()-now.getTimezoneOffset()*60000);return shifted.toISOString().slice(0,16);
  }
  function renderPackages() {
    const box=document.getElementById('dl-packages');box.replaceChildren();
    state.packages.forEach(pkg=>{
      const article=element('article','dl-package');
      article.append(element('strong','',pkg.package_number),element('p','vc-help',normalize(pkg.status)+' · '+pkg.courier_name+' · Ref: '+pkg.tracking_reference));
      const history=element('div','dl-history');
      (pkg.events||[]).forEach(event=>history.append(element('p','',event.event_at+' · '+normalize(event.status)+(event.location?' · '+event.location:'')+(event.note?' · '+event.note:''))));
      article.append(history);
      const next=transitions[pkg.status]||[];
      if(next.length){
        const form=element('form','dl-milestone-form');
        const select=element('select','vc-select');select.name='status';select.required=true;
        select.append(new Option('Choose next milestone',''));
        next.forEach(status=>select.append(new Option(normalize(status),status)));
        const location=element('input','vc-input');location.name='location';location.maxLength=160;location.placeholder='City or location';
        const note=element('textarea','vc-textarea');note.name='note';note.maxLength=500;note.placeholder='Customer-visible update or failure reason';
        const date=element('input','vc-input');date.type='datetime-local';date.name='event_at';date.value=localNow();date.required=true;
        const button=element('button','vc-btn small primary','Record milestone');button.type='submit';
        form.append(select,date,location,note,button);
        submit(form,async data=>{
          const status=String(data.get('status')||'');
          if(['delivery_attempted','delivery_failed','return_in_transit','returned_to_vendor'].includes(status)&&String(data.get('note')||'').trim().length<10)throw Error('Enter at least 10 characters explaining this milestone.');
          const result=await request('shipments.php','PATCH',{package_id:pkg.id,status,event_at:String(data.get('event_at')),location:String(data.get('location')||'').trim(),note:String(data.get('note')||'').trim()});
          announce(result.message);await Promise.all([loadOrders(),openOrder(state.group.id)]);
        });
        article.append(form);
      }
      box.append(article);
    });
  }

  async function loadSettings() {
    const data=await request('shipping-settings.php');state.settings=data.settings;
    const form=document.getElementById('dl-settings-form');
    if(data.settings){
      form.elements.coverage_model.value=data.settings.coverage_model;
      form.elements.base_charge.value=data.settings.base_charge;
      form.elements.free_above.value=data.settings.free_above??'';
      form.elements.processing_min_days.value=data.settings.processing_min_days;
      form.elements.processing_max_days.value=data.settings.processing_max_days;
      form.elements.cod_enabled.checked=Number(data.settings.cod_enabled)===1;
      form.elements.fragile_handling.checked=Number(data.settings.fragile_handling)===1;
    } else {
      form.elements.base_charge.value='99';form.elements.processing_min_days.value='1';form.elements.processing_max_days.value='3';
    }
    const zones=document.getElementById('dl-zone-list');zones.replaceChildren();
    if(!data.zones.length)zones.append(element('p','vc-help','No location rules saved. The default delivery charge applies when All India is selected.'));
    data.zones.forEach(zone=>{
      const row=element('div','dl-row');
      const summary=element('div');
      let location=zone.rule_type==='all_india'?'All India':zone.rule_type==='pincode'?zone.pincode_from+'–'+zone.pincode_to:zone.rule_type==='city'?zone.city+', '+zone.state:zone.state;
      let rates=[];
      try { const parsed=typeof zone.rates==='string'?JSON.parse(zone.rates||'[]'):zone.rates;rates=Array.isArray(parsed)?parsed:[]; } catch (_) { rates=[]; }
      summary.append(element('strong','',zone.zone_name),element('small','',(Number(zone.is_exclusion)?'EXCLUDED · ':'')+location+(rates.length?' · '+rupees.format(Number(rates[0].charge)):' · default rate')));
      const remove=element('button','vc-btn small','Remove');remove.type='button';
      remove.addEventListener('click',async()=>{
        if(!confirm('Remove this delivery rule? This may change checkout availability or charges.'))return;
        remove.disabled=true;
        try{await request('shipping-settings.php?zone_id='+encodeURIComponent(zone.id),'DELETE');announce('Delivery rule removed.');await loadSettings();}
        catch(error){announce(error.message,true);remove.disabled=false;}
      });row.append(summary,remove);zones.append(row);
    });
  }

  document.getElementById('dl-refresh-orders').addEventListener('click',()=>loadOrders().catch(error=>announce(error.message,true)));
  submit(document.getElementById('dl-package-form'),async data=>{
    if(!state.group)throw Error('Select an order first.');
    const items=Array.from(document.querySelectorAll('#dl-package-items [data-item-id]')).map(input=>({order_item_id:Number(input.dataset.itemId),quantity:Number(input.value)})).filter(item=>item.quantity>0);
    if(!items.length)throw Error('Select at least one item for this package.');
    const result=await request('shipments.php','POST',{group_id:state.group.id,courier_name:String(data.get('courier_name')).trim(),tracking_reference:String(data.get('tracking_reference')||'').trim(),tracking_url:String(data.get('tracking_url')||'').trim(),expected_delivery_date:String(data.get('expected_delivery_date')),vendor_shipping_cost:data.get('vendor_shipping_cost')===''?null:Number(data.get('vendor_shipping_cost')),items});
    announce(result.message+' Reference: '+result.package_number);document.getElementById('dl-package-form').reset();await Promise.all([loadOrders(),openOrder(state.group.id)]);
  });
  submit(document.getElementById('dl-settings-form'),async data=>{
    const min=Number(data.get('processing_min_days')),max=Number(data.get('processing_max_days'));
    if(max<min)throw Error('Maximum processing days must not be less than the minimum.');
    const couriers=state.settings?.courier_partners?String(state.settings.courier_partners).split(',').map(part=>part.trim()).filter(Boolean):[];
    const result=await request('shipping-settings.php','PUT',{coverage_model:data.get('coverage_model'),base_charge:Number(data.get('base_charge')),free_above:data.get('free_above')===''?null:Number(data.get('free_above')),processing_min_days:min,processing_max_days:max,cod_enabled:data.has('cod_enabled'),fragile_handling:data.has('fragile_handling'),courier_partners:couriers});
    announce(result.message);await loadSettings();
  });
  submit(document.getElementById('dl-zone-form'),async data=>{
    const type=String(data.get('rule_type'));
    const stateName=String(data.get('state')||'');const city=String(data.get('city')||'').trim();
    const from=String(data.get('pincode_from')||'');const to=String(data.get('pincode_to')||'');
    if(type==='state'&&!stateName||type==='city'&&(!stateName||city.length<2)||type==='pincode'&&(!/^[1-9][0-9]{5}$/.test(from)||!/^[1-9][0-9]{5}$/.test(to)||from>to))throw Error('Complete the location fields for this rule.');
    const excluded=data.has('is_exclusion');const charge=String(data.get('charge')||'');
    if(!excluded&&charge==='')throw Error('Enter a delivery charge for an included location.');
    const result=await request('shipping-settings.php','POST',{zone_name:String(data.get('zone_name')).trim(),rule_type:type,state:stateName,city,pincode_from:from,pincode_to:to,is_exclusion:excluded,rates:excluded?[]:[{calculation_type:'flat',charge:Number(charge)}]});
    announce(result.message);document.getElementById('dl-zone-form').reset();await loadSettings();
  });
  submit(document.getElementById('dl-quote-form'),async data=>{
    const params=new URLSearchParams(data);
    const result=await request('shipping-quote.php?'+params.toString());
    const box=document.getElementById('dl-quote-result');box.hidden=false;
    box.textContent=result.quote.serviceable?'Serviceable. Delivery charge: '+rupees.format(Number(result.quote.amount))+'.':'Not serviceable: '+result.quote.reason;
    box.classList.toggle('vc-delivery-error',!result.quote.serviceable);
  });
  document.getElementById('dl-zone-type').addEventListener('change',function(){
    const form=document.getElementById('dl-zone-form');
    form.elements.state.required=['state','city'].includes(this.value);
    form.elements.city.required=this.value==='city';
    form.elements.pincode_from.required=this.value==='pincode';form.elements.pincode_to.required=this.value==='pincode';
  });
  document.getElementById('dl-zone-type').dispatchEvent(new Event('change'));

  (async()=>{
    try{
      const identity=await fetch(base+'auth/me.php',{credentials:'same-origin',headers:{Accept:'application/json'},cache:'no-store'}).then(response=>response.json());
      if(!identity.success)throw Error('Your vendor session expired. Please sign in again.');
      state.csrf=identity.csrf_token;
      await Promise.all([loadOrders(),loadSettings()]);
      const selectedOrder=new URLSearchParams(location.search).get('order');
      if(selectedOrder)await openOrder(selectedOrder);
    }catch(error){announce(error.message,true);}
  })();
})();
