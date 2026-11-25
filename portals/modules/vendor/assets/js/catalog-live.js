(function () {
  'use strict';
  const api='/conscious/api/vendor/';
  const state={csrf:'',products:[],current:null,variants:[],images:[],readyToSell:false,scanBypass:false,step:1,dirty:false};
  const form=document.getElementById('catalog-form');
  const editor=document.getElementById('catalog-editor');
  const standalone=editor.dataset.standalone==='1';
  const initialProductId=editor.dataset.productId||'';
  const actions=document.querySelector('.cat-wizard-actions');
  const submitDialog=document.getElementById('catalog-submit-dialog');
  const money=new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'});
  const stepNames=['Basic details','Description & story','Pricing & stock','Photos','Variants','Review'];
  const nextHints=['Add the basic product details','Write the product description and its story','Set pricing, GST and available stock','Upload at least one product photo','Check the available variants','Review and submit the product'];
  const catalogueChrome=[document.getElementById('catalog-workflow-intro'),document.getElementById('catalog-security-note'),document.getElementById('catalog-products-card')];

  function item(tag,cls,value){const node=document.createElement(tag);if(cls)node.className=cls;if(value!==undefined)node.textContent=String(value);return node;}
  function friendly(value){return String(value||'').replaceAll('_',' ');}
  function notice(value,failure=false){const box=editor.hidden?document.getElementById('catalog-message'):document.getElementById('catalog-editor-message');box.hidden=false;box.textContent=value;box.classList.toggle('cat-error',failure);box.scrollIntoView({block:'nearest'});}
  async function call(path,method='GET',body,multipart=false){
    const headers={Accept:'application/json'};if(method!=='GET')headers['X-CSRF-Token']=state.csrf;if(body!==undefined&&!multipart)headers['Content-Type']='application/json';
    const response=await fetch(api+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:multipart?body:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));if(!response.ok){const error=Error(data.message||'Unable to complete this action.');error.fieldErrors=data.errors||{};throw error;}return data;
  }
  async function loadProducts(){const data=await call('products.php');state.products=data.products||[];document.getElementById('catalog-count').textContent=state.products.length+' product'+(state.products.length===1?'':'s');renderProducts();}
  function renderProducts(){
    const target=document.getElementById('catalog-list');target.replaceChildren();const search=document.getElementById('catalog-search').value.trim().toLowerCase();const status=document.getElementById('catalog-filter').value;
    const matches=state.products.filter(product=>(!status||product.status===status)&&(!search||(product.name+' '+product.sku).toLowerCase().includes(search)));
    if(!matches.length){target.append(item('p','vc-help',state.products.length?'No products match this filter.':'No products yet. Create your first product draft.'));return;}
    matches.forEach(product=>{const row=item('div','cat-row');const info=item('div');info.append(item('strong','',product.name),item('small','',product.sku+' · '+money.format(Number(product.selling_price))+' · '+friendly(product.status)));if(product.rejection_reason)info.append(item('small','',product.rejection_reason));const controls=item('div','vc-actions');const open=item('a','vc-btn small','Edit');open.href='/conscious/partner/catalog/'+encodeURIComponent(product.id)+'/edit';controls.append(open);row.append(info,controls);target.append(row);});
  }
  function fieldValue(name){return String(form.elements[name]?.value||'').trim();}
  function clearFieldError(control){if(!control)return;const field=control.closest('.vc-field');field?.classList.remove('has-error');control.removeAttribute('aria-invalid');field?.querySelector('.cat-field-error')?.remove();}
  function fieldMessage(control){const label=control.closest('.vc-field')?.querySelector('label')?.textContent.replace('*','').trim()||'This field';if(control.validity.valueMissing)return label+' is required.';if(control.validity.tooShort)return 'Use at least '+control.minLength+' characters.';if(control.validity.tooLong)return 'Use no more than '+control.maxLength+' characters.';if(control.validity.rangeUnderflow)return 'Enter a value of '+control.min+' or more.';if(control.validity.rangeOverflow)return 'Enter a value of '+control.max+' or less.';if(control.validity.stepMismatch)return 'Enter a valid whole number or allowed increment.';if(control.validity.typeMismatch)return 'Enter a valid '+(control.type==='email'?'email address':'value')+'.';if(control.validity.patternMismatch)return 'Use only the requested format.';return control.validationMessage||'Enter a valid value.';}
  function showFieldError(control,messageText){if(!control)return;clearFieldError(control);const field=control.closest('.vc-field');if(!field)return;field.classList.add('has-error');control.setAttribute('aria-invalid','true');const error=item('small','cat-field-error',messageText);error.setAttribute('role','alert');const wrappedLabel=control.closest('label');if(wrappedLabel&&field.contains(wrappedLabel))field.append(error);else control.insertAdjacentElement('afterend',error);}
  function applyFieldErrors(errors,container=form){let first=null;Object.entries(errors||{}).forEach(([name,messageText])=>{const control=container.elements?.[name]||form.elements[name];if(control){showFieldError(control,messageText);if(!first)first=control;}});first?.focus();return Boolean(first);}
  function validateContainer(container){const controls=[...container.querySelectorAll('input,select,textarea')].filter(control=>!control.disabled);controls.forEach(clearFieldError);const invalid=controls.filter(control=>!control.checkValidity());invalid.forEach(control=>showFieldError(control,fieldMessage(control)));invalid[0]?.focus();return invalid.length===0;}
  function stepComplete(step){
    if(step===1)return fieldValue('name').length>=3&&fieldValue('sku').length>=2;
    if(step===2)return fieldValue('description').length>=40&&fieldValue('product_story').length>=40;
    if(step===3)return Boolean(state.current);
    if(step===4)return state.images.some(image=>Boolean(image.scan_reviewable));
    if(step===5)return Boolean(state.current&&state.variants.length);
    return Boolean(state.current&&['pending_review','approved','active'].includes(state.current.status));
  }
  function completedSteps(){return stepNames.map((_,index)=>stepComplete(index+1));}
  function renderSummary(){
    const complete=completedSteps();const count=complete.filter(Boolean).length;const percent=Math.round(count/6*100);
    document.getElementById('catalog-progress-percent').textContent=percent+'%';document.getElementById('catalog-progress-count').textContent=count+' of 6 complete';document.getElementById('catalog-progress-ring').style.setProperty('--progress',(percent*3.6)+'deg');
    document.getElementById('catalog-summary-name').textContent=fieldValue('name')||'Untitled product';document.getElementById('catalog-summary-sku').textContent=fieldValue('sku')||'Add a SKU to identify it';
    document.querySelectorAll('[data-summary-step]').forEach(row=>{const step=Number(row.dataset.summaryStep);row.classList.toggle('complete',complete[step-1]);row.classList.toggle('active',step===state.step);});
    document.querySelectorAll('[data-catalog-step]').forEach(button=>{const step=Number(button.dataset.catalogStep);button.classList.toggle('complete',complete[step-1]);button.classList.toggle('active',step===state.step);button.disabled=step>=4&&!state.current;button.setAttribute('aria-current',step===state.step?'step':'false');});
    const firstMissing=complete.findIndex(done=>!done);document.getElementById('catalog-next-hint').textContent=firstMissing<0?'Product setup is complete':nextHints[firstMissing];
    const preview=document.getElementById('catalog-summary-preview');const availableImage=state.images.find(image=>Number(image.is_primary)&&image.scan_reviewable)||state.images.find(image=>image.scan_reviewable);preview.replaceChildren();if(availableImage){const image=item('img');image.src='/conscious/api/files.php?id='+encodeURIComponent(availableImage.file_id);image.alt=availableImage.alt_text;preview.append(image);}else{const icon=item('i','fa-solid fa-image');icon.setAttribute('aria-hidden','true');preview.append(icon,item('span','',state.images.length?'Image unavailable':'Product preview'));}
    renderReview(complete);
  }
  function renderReview(complete){
    const target=document.getElementById('catalog-review-list');target.replaceChildren();const descriptions=['Name, SKU and origin','Description, product story, material and care','Price, tax, stock and packed weight','At least one product image and a featured selection','Default or optional product variants'];
    for(let step=1;step<=5;step+=1){const row=item('div','cat-review-row'+(complete[step-1]?' complete':''));const icon=item('i',complete[step-1]?'fa-solid fa-circle-check':'fa-solid fa-circle-exclamation');const copy=item('div');copy.append(item('strong','',stepNames[step-1]),item('small','',complete[step-1]?descriptions[step-1]+' completed':descriptions[step-1]+' needs attention'));const edit=item('button','vc-btn small','Edit');edit.type='button';edit.addEventListener('click',()=>setStep(step));row.append(icon,copy,edit);target.append(row);}
    const ready=complete.slice(0,5).every(Boolean)&&state.readyToSell;const button=document.getElementById('catalog-submit-review');button.disabled=!ready||!state.current||!['draft','changes_requested','rejected'].includes(state.current.status||'');document.getElementById('catalog-review-help').textContent=!state.readyToSell?'Complete seller approval before submitting a product.':!complete[3]?'Upload at least one product image.':ready?'Everything required is ready for admin review.':'Complete the remaining product sections.';
  }
  function updateDraftStatus(){const node=document.getElementById('catalog-draft-status');if(state.current&&!state.dirty){node.className='is-saved';node.innerHTML='<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Draft saved';}else if(state.current){node.className='';node.innerHTML='<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Unsaved changes';}else{node.className='';node.innerHTML='<i class="fa-regular fa-circle" aria-hidden="true"></i> Complete pricing to save draft';}}
  function setStep(step,focus=true){
    if(step<1||step>6||(step>=4&&!state.current))return;state.step=step;
    document.querySelectorAll('[data-catalog-step-panel]').forEach(panel=>{panel.hidden=Number(panel.dataset.catalogStepPanel)!==step;});
    actions.hidden=step===6;document.getElementById('catalog-step-back').disabled=step===1;const next=document.getElementById('catalog-step-next');next.textContent=step===3?'Save draft & continue':'Continue';
    renderSummary();updateDraftStatus();if(focus){const heading=document.querySelector('[data-catalog-step-panel="'+step+'"] h3');heading?.focus?.({preventScroll:true});editor.scrollIntoView({behavior:'smooth',block:'start'});}
  }
  function validateStep(step){return validateContainer(document.querySelector('[data-catalog-step-panel="'+step+'"]'));}
  function resetEditor(){
    state.current=null;state.variants=[];state.images=[];state.dirty=false;form.reset();form.elements.stock.disabled=false;document.getElementById('catalog-stock-label').textContent='Opening Stock';document.getElementById('catalog-stock-note').textContent='The quantity available when this product is first saved.';document.getElementById('catalog-message').hidden=true;document.getElementById('catalog-editor-message').hidden=true;document.getElementById('catalog-editor-title').textContent='New product';document.getElementById('catalog-editor-subtitle').textContent='Complete the essentials first. You can return to the draft later.';document.getElementById('catalog-images').replaceChildren(item('p','vc-help','Save the product draft before uploading photos.'));document.getElementById('catalog-variants').replaceChildren(item('p','vc-help','A default variant will be created when the draft is saved.'));catalogueChrome.forEach(node=>{if(node)node.hidden=true;});editor.hidden=false;setStep(1,false);editor.scrollIntoView({behavior:'smooth',block:'start'});window.setTimeout(()=>document.getElementById('cat-name').focus({preventScroll:true}),0);
  }
  function setAddProductUrl(){location.href='/conscious/partner/catalog/new';}
  function closeEditor(){if(standalone){location.href='/conscious/partner/catalog';return;}editor.hidden=true;state.current=null;catalogueChrome.forEach(node=>{if(node)node.hidden=false;});document.getElementById('catalog-products-card')?.scrollIntoView({behavior:'smooth',block:'start'});}
  async function openProduct(id,requestedStep=1){
    const [detail,images]=await Promise.all([call('products.php?id='+encodeURIComponent(id)),call('product-images.php?product_id='+encodeURIComponent(id))]);state.current=detail.product;state.variants=detail.variants||[];state.images=images.images||[];state.scanBypass=Boolean(images.scan_bypass_enabled);state.dirty=false;form.reset();document.getElementById('catalog-message').hidden=true;for(const input of form.elements){if(input.name&&Object.hasOwn(state.current,input.name)&&state.current[input.name]!==null)input.value=state.current[input.name];}form.elements.stock.disabled=false;form.elements.stock.value=state.variants[0]?.stock_quantity??0;document.getElementById('catalog-stock-label').textContent='Stock Quantity';document.getElementById('catalog-stock-note').textContent='Updates the default variant stock and records the change in Inventory history.';document.getElementById('cat-attest').checked=false;document.getElementById('catalog-editor-title').textContent=state.current.name;document.getElementById('catalog-editor-subtitle').textContent='Draft · '+friendly(state.current.status);catalogueChrome.forEach(node=>{if(node)node.hidden=true;});editor.hidden=false;renderImages();renderVariants();const editable=['draft','changes_requested','rejected','approved','active','paused','out_of_stock'].includes(state.current.status);document.getElementById('catalog-save-draft').disabled=!editable;document.getElementById('catalog-image-form').querySelector('[type=submit]').disabled=!['draft','changes_requested','rejected'].includes(state.current.status);document.getElementById('catalog-variant-form').hidden=!['draft','changes_requested','rejected'].includes(state.current.status);setStep(requestedStep,false);editor.scrollIntoView({behavior:'smooth',block:'start'});
  }
  function renderImages(){
    const target=document.getElementById('catalog-images');target.replaceChildren();
    document.getElementById('catalog-scan-bypass-note').hidden=!state.scanBypass;
    if(!state.images.length){target.append(item('p','vc-help','No gallery images uploaded yet.'));renderSummary();return;}
    state.images.forEach(image=>{
      const card=item('article','cat-gallery-card'+(Number(image.is_primary)?' is-featured':''));
      const photo=item('img','cat-gallery-photo');photo.src='/conscious/api/files.php?id='+encodeURIComponent(image.file_id);photo.alt=image.alt_text;photo.loading='lazy';card.append(photo);
      const body=item('div','cat-gallery-body');const heading=item('div','cat-gallery-title');heading.append(item('strong','',Number(image.is_primary)?'Featured image':'Gallery image'));if(Number(image.is_primary))heading.append(item('span','cat-featured-badge','Featured'));
      body.append(heading,item('p','',image.alt_text),item('small','',image.scan_status==='bypassed'?'Available · scan bypassed for local testing':friendly(image.scan_status)));
      if(!Number(image.is_primary)){
        const featured=item('button','vc-btn small','Set as featured');featured.type='button';featured.addEventListener('click',async()=>{featured.disabled=true;try{const result=await call('product-images.php?product_id='+encodeURIComponent(state.current.id),'PATCH',{image_id:image.id,alt_text:image.alt_text,sort_order:image.sort_order,is_primary:true});notice(result.message);await openProduct(state.current.id,4);}catch(error){notice(error.message,true);}finally{featured.disabled=false;}});body.append(featured);
      }
      card.append(body);target.append(card);
    });
    renderSummary();
  }

  function renderSelectedImages(){
    const input=document.getElementById('cat-image');const target=document.getElementById('catalog-image-selection');const files=[...input.files];target.replaceChildren();
    if(!files.length){target.append(item('p','vc-help','Selected images will appear here so you can add a description and choose the featured image.'));return;}
    files.forEach((file,index)=>{
      const row=item('div','cat-selected-image');const preview=item('img');preview.src=URL.createObjectURL(file);preview.alt='';preview.addEventListener('load',()=>URL.revokeObjectURL(preview.src),{once:true});
      const fields=item('div','cat-selected-fields');fields.append(item('strong','',file.name));
      const label=item('label','', 'Image description');const alt=item('input','vc-input');alt.type='text';alt.required=true;alt.minLength=3;alt.maxLength=220;alt.placeholder='Describe what is visible in this image';alt.dataset.galleryAlt=String(index);label.append(alt);fields.append(label);
      const featureLabel=item('label','vc-check');const radio=item('input');radio.type='radio';radio.name='featured_selection';radio.value=String(index);radio.checked=(state.images.length===0&&index===0);featureLabel.append(radio,document.createTextNode(' Use as featured image'));fields.append(featureLabel);row.append(preview,fields);target.append(row);
    });
  }
  function renderVariants(){const target=document.getElementById('catalog-variants');target.replaceChildren();if(!state.variants.length){target.append(item('p','vc-help','No variants created yet.'));renderSummary();return;}state.variants.forEach(variant=>{const row=item('div','cat-image');row.append(item('span','',variant.name+' · '+variant.sku),item('small','',variant.stock_quantity+' in stock · '+variant.available_quantity+' available'));target.append(row);});renderSummary();}
  function bind(formNode,action){formNode.setAttribute('novalidate','');formNode.addEventListener('submit',async event=>{event.preventDefault();if(!validateContainer(formNode))return;const button=event.submitter||formNode.querySelector('[type=submit]');button.disabled=true;try{await action(new FormData(formNode));}catch(error){if(!applyFieldErrors(error.fieldErrors,formNode))notice(error.message,true);}finally{button.disabled=false;}});formNode.addEventListener('input',event=>{if(event.target.matches('input,select,textarea'))clearFieldError(event.target);});formNode.addEventListener('change',event=>{if(event.target.matches('input,select,textarea'))clearFieldError(event.target);});}

  bind(form,async data=>{if(Number(data.get('selling_price'))>Number(data.get('mrp'))){const error=Error('Selling price cannot exceed MRP.');error.fieldErrors={selling_price:'Selling price cannot exceed MRP.'};throw error;}if(Number(data.get('processing_max_days'))<Number(data.get('processing_min_days'))){const error=Error('Maximum processing days cannot be less than minimum.');error.fieldErrors={processing_max_days:'Maximum days must be the same as or greater than minimum days.'};throw error;}const creating=!state.current;const payload={};for(const [key,value] of data)payload[key]=key==='stock'?Number(value):value;const result=await call('products.php'+(creating?'':'?id='+encodeURIComponent(state.current.id)),creating?'POST':'PUT',payload);if(creating&&standalone)history.replaceState(null,'','/conscious/partner/catalog/'+encodeURIComponent(result.product.id)+'/edit');notice(result.message);await loadProducts();await openProduct(result.product.id,4);});
  bind(document.getElementById('catalog-image-form'),async()=>{
    if(!state.current)throw Error('Save the product draft first.');
    const imageForm=document.getElementById('catalog-image-form');const files=[...document.getElementById('cat-image').files];
    if(!files.length)throw Error('Choose one or more product images.');
    if(state.images.length+files.length>10)throw Error('A product can have at most 10 gallery images.');
    const allowed=['image/jpeg','image/png','image/webp'];
    for(const file of files){if(!allowed.includes(file.type))throw Error(file.name+' must be a JPG, PNG or WebP image.');if(file.size>5*1024*1024)throw Error(file.name+' must be 5 MB or smaller.');}
    const featured=Number(imageForm.elements.featured_selection?.value??-1);let lastMessage='Images uploaded.';
    for(let index=0;index<files.length;index+=1){
      const alt=imageForm.querySelector('[data-gallery-alt="'+index+'"]')?.value.trim()||'';if(alt.length<3)throw Error('Add a description for '+files[index].name+'.');
      const data=new FormData();data.append('image',files[index]);data.append('alt_text',alt);data.append('sort_order',String(state.images.length+index));if(index===featured)data.append('is_primary','1');
      const result=await call('product-images.php?product_id='+encodeURIComponent(state.current.id),'POST',data,true);lastMessage=result.message;
    }
    notice(files.length+' images uploaded to the gallery. '+(state.scanBypass?'Security scanning is disabled for local testing.':lastMessage));imageForm.reset();renderSelectedImages();await openProduct(state.current.id,4);
  });
  bind(document.getElementById('catalog-variant-form'),async data=>{if(!state.current)throw Error('Save the product draft first.');const payload={name:String(data.get('name')).trim(),sku:String(data.get('sku')).trim().toUpperCase(),stock:Number(data.get('stock')),price_adjustment:Number(data.get('price_adjustment'))};const result=await call('variants.php?product_id='+encodeURIComponent(state.current.id),'POST',payload);notice(result.message);document.getElementById('catalog-variant-form').reset();await openProduct(state.current.id,5);});

  document.getElementById('catalog-step-next').addEventListener('click',()=>{if(state.step<=2){if(validateStep(state.step))setStep(state.step+1);return;}if(state.step===3){if(validateStep(3))form.requestSubmit(document.getElementById('catalog-save-draft'));return;}if(state.step<6)setStep(state.step+1);});
  document.getElementById('catalog-step-back').addEventListener('click',()=>setStep(state.step-1));
  document.getElementById('cat-image').addEventListener('change',renderSelectedImages);
  document.querySelectorAll('[data-catalog-step]').forEach(button=>button.addEventListener('click',()=>{const target=Number(button.dataset.catalogStep);if(target<=state.step||state.current){if(target>state.step&&state.step<=3&&!validateStep(state.step))return;setStep(target);}}));
  form.addEventListener('input',()=>{if(state.current)state.dirty=true;renderSummary();updateDraftStatus();});
  document.getElementById('catalog-submit-review').addEventListener('click',()=>submitDialog.showModal());
  document.getElementById('catalog-submit-close').addEventListener('click',()=>submitDialog.close());document.getElementById('catalog-submit-cancel').addEventListener('click',()=>submitDialog.close());
  document.getElementById('catalog-submit-confirm').addEventListener('click',async event=>{if(!state.current)return;event.currentTarget.disabled=true;try{const result=await call('product-review.php','POST',{product_id:state.current.id});submitDialog.close();notice(result.message);await loadProducts();await openProduct(state.current.id,6);}catch(error){notice(error.message,true);}finally{event.currentTarget.disabled=false;}});
  document.getElementById('catalog-new').addEventListener('click',setAddProductUrl);document.getElementById('catalog-close').addEventListener('click',closeEditor);document.getElementById('catalog-refresh').addEventListener('click',()=>loadProducts().catch(error=>notice(error.message,true)));document.getElementById('catalog-search').addEventListener('input',renderProducts);document.getElementById('catalog-filter').addEventListener('change',renderProducts);
  (async()=>{try{const identity=await call('auth/me.php');state.csrf=identity.csrf_token;const vendor=identity.vendor||{};state.readyToSell=vendor.status==='active'&&['approved','demo_fixture'].includes(vendor.verification_status)&&vendor.onboarding_status==='completed';await loadProducts();if(standalone){if(initialProductId)await openProduct(initialProductId,1);else resetEditor();}else if(location.hash==='#add-product'){location.replace('/conscious/partner/catalog/new');return;}renderSummary();}catch(error){notice(error.message,true);}})();
})();
