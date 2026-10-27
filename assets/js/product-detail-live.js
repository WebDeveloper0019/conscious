(() => {
  'use strict';
  const root=document.getElementById('pd-live');
  const status=document.getElementById('pd-status');
  if(!root||!status)return;
  const id=new URLSearchParams(location.search).get('id');
  const money=value=>new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(Number(value)||0);
  const node=(tag,className,text)=>{const item=document.createElement(tag);if(className)item.className=className;if(text!==undefined)item.textContent=text;return item;};
  const imageUrl=raw=>{if(!raw)return '';try{const url=new URL(raw,new URL('api/public/catalog.php',location.href));return url.origin===location.origin?url.href:'';}catch{return '';}};
  async function api(path,method='GET',body){
    const headers={Accept:'application/json'};
    if(method!=='GET'){
      const response=await fetch('api/auth/me.php',{credentials:'same-origin',headers});
      if(!response.ok){const error=Error('Sign in to add products to your bag, save them, or place an order.');error.login=true;throw error;}
      const account=await response.json();
      headers['X-CSRF-Token']=account.csrf_token||'';
      headers['Content-Type']='application/json';
    }
    const response=await fetch(path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));
    if(!response.ok||!data.success)throw Error(data.message||'Request failed.');
    return data;
  }
  const feedback=(message,needsLogin=false)=>{status.hidden=false;status.replaceChildren(document.createTextNode(message));if(needsLogin){const link=node('a','',' Sign in');link.href='portals/modules/customer/auth/login.php';status.append(link);}status.scrollIntoView({behavior:'smooth',block:'center'});};
  function action(label,icon,className,handler){const button=node('button',className);button.type='button';button.innerHTML='<i class="fa-solid '+icon+'" aria-hidden="true"></i> ';button.append(document.createTextNode(label));button.addEventListener('click',async()=>{button.disabled=true;try{await handler();}catch(error){feedback(error.message,Boolean(error.login));button.disabled=false;}});return button;}
  function render(data){
    const product=data.product,images=(data.images||[]).map(item=>({...item,src:imageUrl(item.url)})).filter(item=>item.src);
    const variants=(data.variants||[]).filter(item=>Number(item.available_quantity)>0);
    root.replaceChildren();status.hidden=true;
    document.title=product.name+' — Conscious India';
    const back=node('a','pd-back','← Back to marketplace');back.href='shop.html';root.append(back);
    if(product.vendor_verification_status==='demo_fixture')root.append(node('p','pd-demo-notice','Local demo listing: product, vendor, price, image, and stock are stored in MySQL. This is not a real seller offer.'));
    const layout=node('div','pd-live-layout');root.append(layout);
    const gallery=node('div','pd-live-gallery');layout.append(gallery);
    if(images.length){const hero=node('img','pd-live-hero');hero.src=images[0].src;hero.alt=images[0].alt_text||product.name;gallery.append(hero);if(images.length>1){const thumbs=node('div','pd-live-thumbs');images.forEach(item=>{const button=node('button');button.type='button';const image=node('img');image.src=item.src;image.alt=item.alt_text||product.name;button.append(image);button.addEventListener('click',()=>{hero.src=item.src;hero.alt=image.alt;});thumbs.append(button);});gallery.append(thumbs);}}
    else gallery.append(node('p','','No approved image is available for this product.'));
    const info=node('div','pd-live-info');layout.append(info);
    const maker=node('p','pd-live-maker',product.vendor_name||'Seller');info.append(maker);
    info.append(node('h1','',product.name));
    if(product.short_description)info.append(node('p','pd-live-short',product.short_description));
    const price=node('div','pd-live-price');price.append(node('strong','',money(product.selling_price)));if(Number(product.mrp)>Number(product.selling_price))price.append(node('del','',money(product.mrp)));info.append(price);
    const stock=node('p','pd-live-stock',variants.length?'In stock · vendor-managed dispatch':'Currently out of stock');info.append(stock);
    const controls=node('div','pd-live-controls');info.append(controls);
    const variantLabel=node('label','','Option');const variantSelect=node('select','');variantSelect.setAttribute('aria-label','Product option');variants.forEach(item=>{const option=node('option','',item.name+' · '+item.available_quantity+' available');option.value=item.id;variantSelect.append(option);});variantLabel.append(variantSelect);controls.append(variantLabel);
    const quantityLabel=node('label','','Quantity');const quantity=node('input');quantity.type='number';quantity.min='1';quantity.max='20';quantity.value='1';quantityLabel.append(quantity);controls.append(quantityLabel);
    const selected=()=>{const variant=variants.find(item=>item.id===variantSelect.value);const amount=Number(quantity.value);if(!variant)throw Error('This product is out of stock.');const maximum=Math.min(20,Number(variant.available_quantity));if(!Number.isInteger(amount)||amount<1||amount>maximum)throw Error('Enter a whole-number quantity from 1 to '+maximum+' for this option.');return {variant_id:variant.id,quantity:amount};};
    const buttons=node('div','pd-live-actions');info.append(buttons);
    const add=action('Add to cart','fa-bag-shopping','bc-btn bc-btn-primary',async()=>{await api('api/user/cart.php','POST',selected());location.href='portals/modules/customer/cart.php';});
    const buy=action('Buy now','fa-bolt','bc-btn bc-btn-navy',async()=>{await api('api/user/cart.php','POST',selected());location.href='portals/modules/customer/checkout.php';});
    const save=action('Save','fa-heart','bc-btn bc-btn-outline',async()=>{await api('api/user/wishlist.php','POST',{productId:product.id});feedback('Saved to your wishlist.');save.disabled=false;});
    buttons.append(add,buy,save);info.append(status);if(!variants.length){add.disabled=true;buy.disabled=true;}
    info.append(node('p','pd-live-delivery','Delivery coverage, vendor charges, COD availability and eligible returns are checked against your address at checkout.'));
    const detail=node('section','pd-live-description');detail.append(node('h2','','About this product'),node('p','',product.description||product.short_description||'Product details are not available.'));
    const specs=node('dl','pd-live-specs');[['Material',product.material],['Craft',product.craft_technique],['Origin',product.origin_state],['Dimensions',product.dimensions],['Care',product.care_instructions]].forEach(([label,value])=>{if(!value)return;specs.append(node('dt','',label),node('dd','',value));});detail.append(specs);root.append(detail);
  }
  if(!id){feedback('Choose a product from the marketplace.');const link=node('a','bc-btn bc-btn-primary','Browse products');link.href='shop.html';root.append(link);return;}
  api('api/public/catalog.php?id='+encodeURIComponent(id)).then(render).catch(error=>{feedback(error.message);const link=node('a','bc-btn bc-btn-primary','Browse products');link.href='shop.html';root.append(link);});
})();
