(() => {
  'use strict';
  const grid=document.querySelector('.bc-shop-product-grid');if(!grid)return;
  const sidebar=document.querySelector('.bc-shop-sidebar'),summary=document.querySelector('.bc-shop-sortbar strong'),sort=document.querySelector('.bc-shop-sortbar select'),tabs=document.getElementById('bc-live-tabs');
  const state={products:[],category:new URLSearchParams(location.search).get('category')||'',q:new URLSearchParams(location.search).get('q')||'',vendor:new URLSearchParams(location.search).get('vendor_id')||''};
  const money=value=>new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(Number(value)||0);
  const el=(tag,className,text)=>{const item=document.createElement(tag);if(className)item.className=className;if(text!==undefined)item.textContent=text;return item;};
  const imageUrl=raw=>{if(!raw)return '';try{const url=new URL(raw,new URL('api/public/catalog.php',location.href));if(url.origin!==location.origin||!/^https?:$/.test(url.protocol))return '';if(url.pathname.endsWith('/api/files.php'))url.searchParams.set('v','20261026-images1');return url.href;}catch{return '';}};
  const toast=el('div','bc-shop-toast');toast.setAttribute('role','status');toast.hidden=true;document.body.append(toast);
  let toastTimer;
  function notify(message,needsLogin=false){toast.replaceChildren(document.createTextNode(message));if(needsLogin){const link=el('a','',' Sign in');link.href='portals/modules/customer/auth/login.php';toast.append(link);}toast.hidden=false;clearTimeout(toastTimer);toastTimer=setTimeout(()=>toast.hidden=true,7000);}
  async function api(path,method='GET',body){
    const headers={Accept:'application/json'};
    if(method!=='GET'){
      const response=await fetch('api/auth/me.php',{credentials:'same-origin',headers});
      if(!response.ok){const error=Error('Sign in to use cart and wishlist.');error.login=true;throw error;}
      const me=await response.json();headers['X-CSRF-Token']=me.csrf_token||'';headers['Content-Type']='application/json';
    }
    const response=await fetch(path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw Error(data.message||'Request failed.');return data;
  }
  async function addToCart(product,button){
    const detail=await api('api/public/catalog.php?id='+encodeURIComponent(product.id));
    const variant=(detail.variants||[]).find(item=>Number(item.available_quantity)>0);
    if(!variant)throw Error('This product is currently out of stock.');
    await api('api/user/cart.php','POST',{variant_id:variant.id,quantity:1});
    button.innerHTML='<i class="fa-solid fa-check" aria-hidden="true"></i> Added to cart';
    notify(product.name+' was added to your cart.');
    const cartLink=document.querySelector('.bc-header-action-link[aria-label^="Cart"]');
    if(cartLink){const count=cartLink.querySelector('.bc-cart-count');if(count)count.textContent=String((Number(count.textContent)||0)+1);}
  }
  async function save(product,button){await api('api/user/wishlist.php','POST',{productId:product.id});button.innerHTML='<i class="fa-solid fa-heart" aria-hidden="true"></i>';notify(product.name+' was saved to your wishlist.');}
  function action(className,label,icon,handler){const button=el('button',className);button.type='button';button.setAttribute('aria-label',label);button.innerHTML='<i class="fa-solid '+icon+'" aria-hidden="true"></i> ';if(className.includes('bc-live-add'))button.append(document.createTextNode(label));button.addEventListener('click',async event=>{event.preventDefault();event.stopPropagation();button.disabled=true;try{await handler(button);}catch(error){notify(error.message,Boolean(error.login));button.disabled=false;}});return button;}
  function render(){
    const selectedTypes=[...sidebar.querySelectorAll('[data-seller-type]:checked')].map(input=>input.value);
    const selectedMaterials=[...sidebar.querySelectorAll('[data-material]:checked')].map(input=>input.value);
    const selectedOrigins=[...sidebar.querySelectorAll('[data-origin]:checked')].map(input=>input.value);
    const min=Number(sidebar.querySelector('input[placeholder="Min ₹"]')?.value||0),max=Number(sidebar.querySelector('input[placeholder="Max ₹"]')?.value||0);
    if(min<0||max<0||(max&&max<min)){notify('Enter a valid price range.');return;}
    let items=state.products.filter(product=>(!state.category||product.category_name===state.category)&&(!selectedTypes.length||selectedTypes.includes(product.vendor_type))&&(!selectedMaterials.length||selectedMaterials.includes(product.material))&&(!selectedOrigins.length||selectedOrigins.includes(product.origin_state))&&(!min||Number(product.selling_price)>=min)&&(!max||Number(product.selling_price)<=max));
    if(sort.value==='Price: Low to High')items.sort((a,b)=>Number(a.selling_price)-Number(b.selling_price));
    if(sort.value==='Price: High to Low')items.sort((a,b)=>Number(b.selling_price)-Number(a.selling_price));
    if(sort.value==='Newest First')items.sort((a,b)=>Date.parse(b.published_at||b.created_at||0)-Date.parse(a.published_at||a.created_at||0));
    summary.textContent=items.length+' product'+(items.length===1?'':'s');grid.replaceChildren();grid.dataset.liveReady='1';
    if(!items.length){grid.append(el('p','',state.products.length?'No products match these filters. Try clearing them.':'No published products with approved images are available yet.'));return;}
    items.forEach(product=>{
      const card=el('article','bc-product-card bc-live-card bc-reveal visible');
      const visual=el('div','bc-product-img-wrap');const link=el('a');link.href='product-detail.html?id='+encodeURIComponent(product.id);
      const image=el('img');image.src=imageUrl(product.image_url);image.alt=product.name;image.loading='lazy';link.append(image);visual.append(link);
      visual.append(action('bc-live-save','Save '+product.name,'fa-heart',button=>save(product,button)));
      const body=el('div','bc-product-body');body.append(el('span','bc-live-category',product.category_name||'Craft'));
      body.append(el('div','bc-product-artisan','by '+product.vendor_name+(product.vendor_verification_status==='demo_fixture'?' · Demo':'')));
      const title=el('h3','bc-product-title'),titleLink=el('a','',product.name);titleLink.href=link.href;title.append(titleLink);body.append(title);
      const price=el('div','bc-product-price-row');price.append(el('span','bc-product-price',money(product.selling_price)));if(Number(product.mrp)>Number(product.selling_price))price.append(el('del','bc-product-price-original',money(product.mrp)));body.append(price);
      body.append(action('bc-live-add','Add to cart','fa-bag-shopping',button=>addToCart(product,button)));card.append(visual,body);grid.append(card);
    });
  }
  function renderCategories(){
    const options=sidebar.querySelector('[data-category-options]');options.replaceChildren();tabs.replaceChildren();
    const categories=[...new Set(state.products.map(product=>product.category_name).filter(Boolean))].sort();
    const all=el('button','bc-product-tab'+(!state.category?' active':''),'All products');all.type='button';all.addEventListener('click',()=>setCategory(''));tabs.append(all);
    categories.forEach(category=>{const label=el('label');const input=el('input');input.type='radio';input.name='shop_category';input.value=category;input.checked=state.category===category;input.addEventListener('change',()=>setCategory(category));label.append(input,document.createTextNode(' '+category));options.append(label);const tab=el('button','bc-product-tab'+(state.category===category?' active':''),category);tab.type='button';tab.addEventListener('click',()=>setCategory(category));tabs.append(tab);});
  }
  function renderFacet(sectionId,slotSelector,field,attribute){
    const section=document.getElementById(sectionId),slot=section.querySelector(slotSelector);slot.replaceChildren();
    const values=[...new Set(state.products.map(product=>String(product[field]||'').trim()).filter(Boolean))].sort();
    section.hidden=values.length===0;
    values.forEach(value=>{const label=el('label'),input=el('input');input.type='checkbox';input.value=value;input.setAttribute(attribute,'');input.addEventListener('change',render);label.append(input,document.createTextNode(' '+value));slot.append(label);});
  }
  function setCategory(category){state.category=category;renderCategories();render();}
  async function load(){
    try{const params=new URLSearchParams({limit:'60',offset:'0'});if(state.q)params.set('q',state.q);if(state.vendor)params.set('vendor_id',state.vendor);const data=await api('api/public/catalog.php?'+params);state.products=(data.products||[]).filter(product=>imageUrl(product.image_url));renderCategories();renderFacet('bc-live-materials','[data-material-options]','material','data-material');renderFacet('bc-live-origins','[data-origin-options]','origin_state','data-origin');render();const demo=state.products.some(product=>product.vendor_verification_status==='demo_fixture');const note=document.getElementById('bc-shop-demo-note');if(note){note.textContent=demo?'Local demo catalogue · photos, prices, stock and sellers load from MySQL. Listings are fictional for testing.':'';note.hidden=!demo;}}catch(error){summary.textContent='Products unavailable';grid.replaceChildren(el('p','',error.message));grid.dataset.liveReady='1';}
  }
  [...sort.options].forEach(option=>{if(['Best Rated','Best Selling'].includes(option.textContent))option.remove();});sort.addEventListener('change',render);
  sidebar.querySelectorAll('[data-seller-type]').forEach(input=>input.addEventListener('change',render));
  sidebar.querySelectorAll('button').forEach(button=>{if(/Apply Filters/.test(button.textContent))button.addEventListener('click',render);if(/Clear All/.test(button.textContent))button.addEventListener('click',()=>{sidebar.querySelectorAll('input[type=number]').forEach(input=>input.value='');sidebar.querySelectorAll('[data-seller-type],[data-material],[data-origin]').forEach(input=>input.checked=false);setCategory('');});});
  const search=document.getElementById('bc-search-input');if(search&&state.q)search.value=state.q;
  load();
})();
