(() => {
  'use strict';
  const api = '../../../api/vendor/';
  const el = id => document.getElementById(id);
  const state = {items:[], movements:[], csrf:''};
  async function call(path, method='GET', body) {
    const headers = {Accept:'application/json'};
    if (method !== 'GET') headers['X-CSRF-Token'] = state.csrf;
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    const response = await fetch(api + path, {method, credentials:'same-origin', cache:'no-store', headers, body:body === undefined ? undefined : JSON.stringify(body)});
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) throw new Error(data.message || 'Inventory request failed.');
    return data;
  }
  function cell(row, text) { const td=document.createElement('td'); td.textContent=String(text ?? '—'); row.append(td); return td; }
  function empty(target, columns, text) {const row=document.createElement('tr'); const td=cell(row,text); td.colSpan=columns; target.append(row);}
  function render() {
    const query=el('inventory-search').value.trim().toLocaleLowerCase();
    const items=state.items.filter(item => (item.name+' '+item.sku+' '+item.variant_name).toLocaleLowerCase().includes(query));
    const rows=el('inventory-rows'); rows.replaceChildren();
    if (!items.length) empty(rows,8,query?'No matching stock.':'No product variants yet. Create a product draft in Products.');
    items.forEach(item => {
      const row=document.createElement('tr');
      cell(row,item.name); cell(row,(item.variant_name || 'Default')+' · '+item.sku);
      cell(row,item.stock_quantity); cell(row,item.reserved_quantity); cell(row,item.available_quantity); cell(row,item.low_stock_threshold);
      const available=Number(item.available_quantity);
      const status=available<=0?'Out of stock':available<=Number(item.low_stock_threshold)?'Low stock':'In stock';
      const statusTd=cell(row,status); statusTd.className=available<=0?'text-danger':status==='Low stock'?'text-warning':'';
      const action=cell(row,''); const button=document.createElement('button'); button.type='button'; button.className='vc-btn small'; button.textContent='Adjust'; button.addEventListener('click',()=>{el('inventory-variant').value=item.variant_id;el('inventory-adjust-dialog').showModal();}); action.append(button); rows.append(row);
    });
    const available=state.items.reduce((sum,item)=>sum+Number(item.available_quantity),0);
    el('inventory-available').textContent=available.toLocaleString('en-IN');
    el('inventory-reserved').textContent=state.items.reduce((sum,item)=>sum+Number(item.reserved_quantity),0).toLocaleString('en-IN');
    el('inventory-low').textContent=state.items.filter(item=>Number(item.available_quantity)>0&&Number(item.available_quantity)<=Number(item.low_stock_threshold)).length;
    el('inventory-out').textContent=state.items.filter(item=>Number(item.available_quantity)<=0).length;
    const history=el('inventory-movements'); history.replaceChildren();
    if (!state.movements.length) empty(history,5,'No stock changes recorded yet.');
    state.movements.forEach(item=>{const row=document.createElement('tr');cell(row,item.created_at);cell(row,item.product_name+' · '+item.sku);cell(row,Number(item.quantity_delta)>0?'+'+item.quantity_delta:item.quantity_delta);cell(row,item.stock_after);cell(row,item.note || item.movement_type.replaceAll('_',' '));history.append(row);});
  }
  async function load() {
    try {
      const [identity,data]=await Promise.all([call('auth/me.php'),call('inventory.php')]);
      state.csrf=identity.csrf_token || '';
      state.items=data.inventory || []; state.movements=data.movements || [];
      const select=el('inventory-variant'); select.replaceChildren();
      state.items.forEach(item=>{const option=document.createElement('option');option.value=item.variant_id;option.textContent=item.name+' · '+item.sku;select.append(option);});
      el('inventory-message').textContent='Showing '+state.items.length+' variants from your live store. Every stock change requires a reason and is recorded in history.';
      render();
    } catch(error) {el('inventory-message').textContent=error.message;el('inventory-message').classList.add('warning');}
  }
  el('inventory-refresh').addEventListener('click',load);
  el('inventory-search').addEventListener('input',render);
  el('inventory-close').addEventListener('click',()=>el('inventory-adjust-dialog').close());
  el('inventory-adjust-form').addEventListener('submit',async event=>{
    event.preventDefault();
    const delta=Number(el('inventory-delta').value);
    if (!Number.isInteger(delta)||delta===0) {el('inventory-delta').setCustomValidity('Enter a non-zero whole number.');el('inventory-delta').reportValidity();return;}
    el('inventory-delta').setCustomValidity('');
    const button=event.submitter; button.disabled=true;
    try {await call('inventory.php','POST',{variant_id:el('inventory-variant').value,quantity_delta:delta,note:el('inventory-reason').value.trim()});el('inventory-adjust-dialog').close();event.target.reset();await load();}
    catch(error){el('inventory-message').textContent=error.message;el('inventory-message').classList.add('warning');}
    finally{button.disabled=false;}
  });
  el('inventory-delta').addEventListener('input',()=>el('inventory-delta').setCustomValidity(''));
  load();
})();
