(function () {
  'use strict';
  const api = '/conscious/api/vendor/';
  const state = {csrf:'', products:[], current:null, variants:[], images:[]};
  const form = document.getElementById('catalog-form');
  const editor = document.getElementById('catalog-editor');
  const extras = document.getElementById('catalog-extras');
  const money = new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'});
  function item(tag, cls, value) { const node=document.createElement(tag);if(cls)node.className=cls;if(value!==undefined)node.textContent=String(value);return node; }
  function notice(value, failure=false) { const box=document.getElementById('catalog-message');box.hidden=false;box.textContent=value;box.classList.toggle('cat-error',failure);box.scrollIntoView({block:'nearest'}); }
  function friendly(value) { return String(value||'').replaceAll('_',' '); }
  async function call(path,method='GET',body,multipart=false) {
    const headers={Accept:'application/json'};
    if(method!=='GET')headers['X-CSRF-Token']=state.csrf;
    if(body!==undefined&&!multipart)headers['Content-Type']='application/json';
    const response=await fetch(api+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:multipart?body:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));
    if(!response.ok)throw Error(data.message||'Unable to complete this action.');
    return data;
  }
  async function loadProducts() {
    const data=await call('products.php');state.products=data.products||[];
    document.getElementById('catalog-count').textContent=state.products.length+' real product'+(state.products.length===1?'':'s');
    renderProducts();
  }
  function renderProducts() {
    const target=document.getElementById('catalog-list');target.replaceChildren();
    const search=document.getElementById('catalog-search').value.trim().toLowerCase();
    const status=document.getElementById('catalog-filter').value;
    const matches=state.products.filter(p=>(!status||p.status===status)&&(!search||(p.name+' '+p.sku).toLowerCase().includes(search)));
    if(!matches.length){target.append(item('p','vc-help',state.products.length?'No products match this filter.':'No products yet. Create your first accurate product draft.'));return;}
    matches.forEach(product=>{
      const row=item('div','cat-row');
      const info=item('div');info.append(item('strong','',product.name),item('small','',product.sku+' · '+money.format(Number(product.selling_price))+' · '+friendly(product.status)));
      if(product.rejection_reason)info.append(item('small','',product.rejection_reason));
      const actions=item('div','vc-actions');const open=item('button','vc-btn small','Open');open.type='button';open.addEventListener('click',()=>openProduct(product.id).catch(error=>notice(error.message,true)));actions.append(open);row.append(info,actions);target.append(row);
    });
  }
  function resetEditor() {
    state.current=null;state.variants=[];state.images=[];form.reset();
    form.elements.stock.disabled=false;document.getElementById('catalog-stock-note').hidden=false;
    document.getElementById('catalog-editor-title').textContent='New product draft';
    editor.hidden=false;extras.hidden=true;editor.scrollIntoView({block:'start'});
  }
  async function openProduct(id) {
    const [detail,images]=await Promise.all([call('products.php?id='+encodeURIComponent(id)),call('product-images.php?product_id='+encodeURIComponent(id))]);
    state.current=detail.product;state.variants=detail.variants||[];state.images=images.images||[];
    form.reset();
    for(const input of form.elements)if(input.name&&Object.hasOwn(state.current,input.name)&&state.current[input.name]!==null)input.value=state.current[input.name];
    form.elements.stock.disabled=true;document.getElementById('catalog-stock-note').hidden=false;
    document.getElementById('catalog-editor-title').textContent=state.current.name+' · '+friendly(state.current.status);
    document.getElementById('cat-attest').checked=false;
    editor.hidden=false;extras.hidden=false;renderImages();renderVariants();
    const editable=['draft','changes_requested','rejected','approved','active','paused','out_of_stock'].includes(state.current.status);
    form.querySelector('[type=submit]').disabled=!editable;
    document.getElementById('catalog-image-form').querySelector('[type=submit]').disabled=!['draft','changes_requested','rejected'].includes(state.current.status);
    document.getElementById('catalog-variant-form').hidden=!['draft','changes_requested','rejected'].includes(state.current.status);
    document.getElementById('catalog-submit-review').disabled=!['draft','changes_requested','rejected'].includes(state.current.status);
    editor.scrollIntoView({block:'start'});
  }
  function renderImages() {
    const target=document.getElementById('catalog-images');target.replaceChildren();
    if(!state.images.length){target.append(item('p','vc-help','No images uploaded.'));return;}
    state.images.forEach(image=>{
      const row=item('div','cat-image');
      if(image.scan_status==='clean'){const photo=item('img','cat-thumb');photo.src='../../../api/files.php?id='+encodeURIComponent(image.file_id);photo.alt=image.alt_text;photo.loading='lazy';row.append(photo);}
      row.append(item('span','',image.alt_text+(Number(image.is_primary)?' · primary':'')),item('small','',friendly(image.scan_status)));
      target.append(row);
    });
  }
  function renderVariants() {
    const target=document.getElementById('catalog-variants');target.replaceChildren();
    state.variants.forEach(variant=>{const row=item('div','cat-image');row.append(item('span','',variant.name+' · '+variant.sku),item('small','',variant.stock_quantity+' in stock · '+variant.available_quantity+' available'));target.append(row);});
  }
  function bind(formNode,action) {
    formNode.addEventListener('submit',async event=>{
      event.preventDefault();if(!formNode.reportValidity())return;
      const button=formNode.querySelector('[type=submit]');button.disabled=true;
      try { await action(new FormData(formNode)); }
      catch(error) { notice(error.message,true); }
      finally { button.disabled=false; }
    });
  }
  bind(form,async data=>{
    if(Number(data.get('selling_price'))>Number(data.get('mrp')))throw Error('Selling price cannot exceed MRP.');
    if(Number(data.get('processing_max_days'))<Number(data.get('processing_min_days')))throw Error('Maximum processing days cannot be less than minimum.');
    const payload={};for(const [key,value] of data)if(key!=='stock')payload[key]=value;
    if(!state.current)payload.stock=Number(data.get('stock'));
    const result=await call('products.php'+(state.current?'?id='+encodeURIComponent(state.current.id):''),state.current?'PUT':'POST',payload);
    notice(result.message);await loadProducts();await openProduct(result.product.id);
  });
  bind(document.getElementById('catalog-image-form'),async data=>{
    if(!state.current)throw Error('Save the product draft first.');
    const file=data.get('image');if(!(file instanceof File)||!file.size)throw Error('Choose a product image.');
    if(file.size>5*1024*1024)throw Error('Image must be 5 MB or smaller.');
    const result=await call('product-images.php?product_id='+encodeURIComponent(state.current.id),'POST',data,true);
    notice(result.message);await openProduct(state.current.id);
  });
  bind(document.getElementById('catalog-variant-form'),async data=>{
    if(!state.current)throw Error('Save the product draft first.');
    const payload={name:String(data.get('name')).trim(),sku:String(data.get('sku')).trim().toUpperCase(),stock:Number(data.get('stock')),price_adjustment:Number(data.get('price_adjustment'))};
    const result=await call('variants.php?product_id='+encodeURIComponent(state.current.id),'POST',payload);
    notice(result.message);await openProduct(state.current.id);
  });
  document.getElementById('catalog-submit-review').addEventListener('click',async event=>{
    if(!state.current)return;
    if(!confirm('Submit this product for admin review? You cannot edit it while review is pending.'))return;
    event.currentTarget.disabled=true;
    try {const result=await call('product-review.php','POST',{product_id:state.current.id});notice(result.message);await loadProducts();await openProduct(state.current.id);}
    catch(error){notice(error.message,true);event.currentTarget.disabled=false;}
  });
  document.getElementById('catalog-new').addEventListener('click',resetEditor);
  document.getElementById('catalog-close').addEventListener('click',()=>{editor.hidden=true;extras.hidden=true;state.current=null;});
  document.getElementById('catalog-refresh').addEventListener('click',()=>loadProducts().catch(error=>notice(error.message,true)));
  document.getElementById('catalog-search').addEventListener('input',renderProducts);
  document.getElementById('catalog-filter').addEventListener('change',renderProducts);
  (async()=>{try{const identity=await call('auth/me.php');state.csrf=identity.csrf_token;await loadProducts();if(location.hash==='#add-product')resetEditor();}catch(error){notice(error.message,true);}})();
})();
