(() => {
  'use strict';
  const root=document.querySelector('[data-vendor-onboarding]'); if(!root)return;
  const form=root.querySelector('[data-onboarding-form]');
  const sectionNames=['profile','verification','store_delivery','policies_impact'];
  const state={type:root.dataset.initialType,step:1,sections:{},documents:[],application:null,csrf:'',busy:false};
  const field=name=>form.elements.namedItem(name);
  const value=name=>String(field(name)?.value||'').trim();
  const checked=name=>Boolean(field(name)?.checked);
  const set=(name,content)=>{const input=field(name);if(input&&content!==undefined&&content!==null&&input.type!=='file')input.value=String(content);};
  function notice(message,kind='info'){
    const wrap=document.getElementById('vc-toast-wrap'); if(!wrap)return;
    const node=document.createElement('div');node.className='vc-toast '+kind;node.textContent=message;wrap.append(node);
    requestAnimationFrame(()=>node.classList.add('show'));
    setTimeout(()=>{node.classList.remove('show');setTimeout(()=>node.remove(),250);},5200);
  }
  async function request(path,method='GET',body,multipart=false){
    const headers={Accept:'application/json'};
    if(method!=='GET')headers['X-CSRF-Token']=state.csrf;
    if(body!==undefined&&!multipart)headers['Content-Type']='application/json';
    const response=await fetch('../../../api/vendor/'+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:multipart?body:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));
    if(!response.ok||!data.success){const missing=[...(data.missing_sections||[]),...(data.missing_documents||[])];throw Error((data.message||'Unable to save this section.')+(missing.length?' Missing: '+missing.join(', '):''));}
    return data;
  }
  function applyType(){
    root.querySelectorAll('[data-role-fields]').forEach(group=>{const enabled=group.dataset.roleFields===state.type;group.hidden=!enabled;group.querySelectorAll('input,select,textarea').forEach(input=>input.disabled=!enabled);});
    root.querySelectorAll('[data-type-name],[data-pending-type]').forEach(node=>node.textContent=state.type);
    root.querySelector('[data-registration-path]').textContent=state.type==='brand'?'Brand or registered business':'Artisan, maker or collective';
    root.querySelector('[data-type-intro]').textContent=state.type==='brand'?'Tell us who owns and operates the registered brand.':'Tell us about you, your craft and the people who make the products.';
    root.querySelector('[data-review-profile]').textContent=state.type==='brand'?'Brand and representative profile':'Artisan and craft profile';
    root.querySelector('[data-change-type]').hidden=true;
    history.replaceState(null,'','onboarding.php?type='+encodeURIComponent(state.type));
  }
  function showStep(next){
    state.step=Math.max(1,Math.min(5,Number(next)||1));
    root.querySelectorAll('[data-step-panel]').forEach(panel=>panel.hidden=Number(panel.dataset.stepPanel)!==state.step);
    root.querySelectorAll('[data-onboarding-step]').forEach(button=>{const step=Number(button.dataset.onboardingStep);button.classList.toggle('active',step===state.step);button.classList.toggle('done',step<state.step&&Boolean(state.sections[sectionNames[step-1]]?.complete));button.setAttribute('aria-current',step===state.step?'step':'false');});
    sessionStorage.setItem('ci_vendor_onboarding_step',String(state.step));
  }
  function documentType(input){return input.name==='product_samples'?'product_sample':input.name;}
  function uploaded(type){return state.documents.filter(document=>document.document_type===type).length;}
  function fileRequirements(){
    root.querySelectorAll('input[type="file"]').forEach(input=>{
      if(input.dataset.originalRequired===undefined)input.dataset.originalRequired=input.required?'1':'0';
      const needed=input.dataset.originalRequired==='1'?(input.name==='product_samples'?3:1):0;
      input.required=uploaded(documentType(input))<needed;
      input.setCustomValidity('');
    });
  }
  function validate(step){
    const panel=root.querySelector('[data-step-panel="'+step+'"]');
    if(!panel)return false;
    panel.querySelectorAll('input[type="file"]:not(:disabled)').forEach(input=>{
      input.setCustomValidity('');const files=[...(input.files||[])];
      const needed=input.dataset.originalRequired==='1'?(input.name==='product_samples'?3:1):0;
      if(uploaded(documentType(input))+files.length<needed){input.setCustomValidity('Choose the required document'+(needed>1?'s':'')+'.');return;}
      if(input.name==='product_samples'&&files.length>6){input.setCustomValidity('Choose at most six product photographs.');return;}
      const max=Number(input.dataset.maxBytes||5242880),allowed=(input.accept||'').split(',').map(v=>v.trim());
      if(files.some(file=>file.size<1||file.size>max||!allowed.includes(file.type)))input.setCustomValidity('Use an allowed file type within the size limit.');
    });
    if(step===2&&value('bank_account')!==value('bank_confirm'))field('bank_confirm').setCustomValidity('Bank account numbers must match.');
    else field('bank_confirm')?.setCustomValidity('');
    const invalid=panel.querySelector(':invalid');if(invalid){invalid.focus();invalid.reportValidity();notice('Complete the highlighted required information.','error');return false;}return true;
  }
  function payload(step){
    if(step===1){const brand=state.type==='brand';return{
      legal_name:value(brand?'brand_legal_name':'artisan_legal_name'),public_name:value(brand?'brand_public_name':'artisan_public_name'),primary_category:brand?'Brand':value('artisan_category'),story:value(brand?'brand_story':'artisan_story'),preferred_language:brand?'English':value('artisan_language'),address:value('address'),city:value('city'),district:value('district'),state:value('state'),pincode:value('pincode'),
      ...(brand?{legal_structure:value('brand_structure'),registration_number:value('brand_registration_number'),representative_name:value('brand_contact_name'),representative_designation:value('brand_designation'),year_established:Number(value('brand_year')),team_size:Number(value('brand_team')),website:value('brand_website'),social_profile:value('brand_social')}:{operating_model:value('artisan_model'),craft_tradition:value('artisan_craft'),years_practising:Number(value('artisan_years')),maker_count:Number(value('artisan_count'))})
    };}
    if(step===2)return{pan:value('pan'),gst_status:value('gst_status'),gstin:value('gstin'),udyam:value('udyam'),beneficiary:value('beneficiary'),bank_account:value('bank_account'),bank_confirm:value('bank_confirm'),ifsc:value('ifsc')};
    if(step===3){const days={'1–2 business days':[1,2],'3–5 business days':[3,5],'6–10 business days':[6,10],'Made to order: 11–21 days':[11,21],'Made to order: 22–30 days':[22,30]}[value('processing_days')]||[0,0];return{support_email:value('support_email'),support_phone:value('support_phone'),store_description:value('store_description'),dispatch_address:value('dispatch_address'),dispatch_city:value('dispatch_city'),dispatch_state:value('dispatch_state'),dispatch_pin:value('dispatch_pin'),processing_min_days:days[0],processing_max_days:days[1],coverage_model:'all_india',delivery_partners:value('delivery_partners'),base_charge:Number(value('base_charge')),free_above:value('free_above')?Number(value('free_above')):null,cod:checked('cod'),fragile:checked('fragile')};}
    return{return_policy:value('return_policy'),cancellation_policy:value('cancellation_policy'),damage_policy:value('damage_policy'),handmade_policy:value('handmade_policy'),materials:value('materials'),packaging:value('packaging'),maker_payment:value('maker_payment'),authenticity:checked('authenticity'),claims_truth:checked('claims_truth'),delivery_control:checked('delivery_control'),customer_policy:checked('customer_policy')};
  }
  async function upload(step){
    const panel=root.querySelector('[data-step-panel="'+step+'"]');
    for(const input of panel.querySelectorAll('input[type="file"]:not(:disabled)')){
      for(const file of input.files||[]){const body=new FormData();body.append('document_type',documentType(input));body.append('file',file);await request('documents.php','POST',body,true);}
      input.value='';
    }
  }
  function populate(){
    const profile=state.sections.profile?.data||{};
    if(state.type==='brand'){set('brand_legal_name',profile.legal_name);set('brand_public_name',profile.public_name);set('brand_story',profile.story);set('brand_structure',profile.legal_structure);set('brand_registration_number',profile.registration_number);set('brand_contact_name',profile.representative_name);set('brand_designation',profile.representative_designation);set('brand_year',profile.year_established);set('brand_team',profile.team_size);set('brand_website',profile.website);set('brand_social',profile.social_profile);}
    else{set('artisan_legal_name',profile.legal_name);set('artisan_public_name',profile.public_name);set('artisan_story',profile.story);set('artisan_category',profile.primary_category);set('artisan_language',profile.preferred_language);set('artisan_model',profile.operating_model);set('artisan_craft',profile.craft_tradition);set('artisan_years',profile.years_practising);set('artisan_count',profile.maker_count);}
    ['address','city','district','state','pincode'].forEach(name=>set(name,profile[name]));
    const verification=state.sections.verification?.data||{};set('gst_status',verification.gst_status);set('beneficiary',verification.beneficiary);
    if(state.sections.verification?.complete)root.querySelector('[data-step-panel="2"]').querySelectorAll('input:not([type="file"]),select').forEach(input=>input.required=false);
    const delivery=state.sections.store_delivery?.data||{};
    ['support_email','support_phone','store_description','dispatch_address','dispatch_city','dispatch_state','dispatch_pin','delivery_partners','base_charge','free_above'].forEach(name=>set(name,delivery[name]));
    const period={'1-2':'1–2 business days','3-5':'3–5 business days','6-10':'6–10 business days','11-21':'Made to order: 11–21 days','22-30':'Made to order: 22–30 days'}[String(delivery.processing_min_days)+'-'+String(delivery.processing_max_days)];if(period)set('processing_days',period);
    if(field('cod'))field('cod').checked=Boolean(delivery.cod);if(field('fragile'))field('fragile').checked=Boolean(delivery.fragile);
    const policy=state.sections.policies_impact?.data||{};
    ['return_policy','cancellation_policy','damage_policy','handmade_policy','materials','packaging','maker_payment'].forEach(name=>set(name,policy[name]));
    ['authenticity','claims_truth','delivery_control','customer_policy'].forEach(name=>{if(field(name))field(name).checked=Boolean(policy[name]);});
    fileRequirements();updateGst();
  }
  function updateGst(){const registered=value('gst_status')==='registered';root.querySelectorAll('[data-gst-field]').forEach(section=>{section.hidden=!registered;section.querySelectorAll('input').forEach(input=>{input.disabled=!registered;input.required=registered&&!(input.type==='file'&&uploaded(documentType(input))>0);});});}
  function pending(){form.hidden=true;root.querySelector('.vo-steps').hidden=true;root.querySelector('.vo-intro').hidden=true;const panel=root.querySelector('[data-onboarding-pending]');panel.hidden=false;panel.querySelector('[data-application-reference]').textContent=state.application?.reference||'Pending';panel.querySelector('[data-pending-type]').textContent=state.type;}
  async function load(){
    const identity=await request('auth/me.php');state.csrf=identity.csrf_token||'';
    const data=await request('onboarding.php');state.type=data.vendor.vendor_type==='brand'?'brand':'artisan';state.sections=data.sections||{};state.documents=data.documents||[];state.application=data.application;
    applyType();populate();
    if(['approved','demo_fixture'].includes(data.application.status)&&data.vendor.status==='active'){location.replace('dashboard.php');return;}
    if(!['draft','changes_requested'].includes(data.application.status)){pending();return;}
    const firstIncomplete=sectionNames.findIndex(name=>!state.sections[name]?.complete);
    const wanted=Number(sessionStorage.getItem('ci_vendor_onboarding_step'))||1;
    showStep(Math.min(Math.max(wanted,1),firstIncomplete===-1?5:firstIncomplete+1));
  }
  root.querySelectorAll('[data-step-next]').forEach(button=>button.addEventListener('click',async()=>{
    const step=state.step;if(state.busy||!validate(step))return;state.busy=true;button.disabled=true;
    try{
      if(!(step===2&&state.sections.verification?.complete&&!value('pan')&&!value('bank_account')))await request('onboarding.php?section='+sectionNames[step-1],'PATCH',payload(step));
      await upload(step);
      const data=await request('onboarding.php');state.sections=data.sections||{};state.documents=data.documents||[];fileRequirements();showStep(step+1);notice('Section saved to your application.','success');
    }catch(error){notice(error.message,'error');}finally{state.busy=false;button.disabled=false;}
  }));
  root.querySelectorAll('[data-step-back]').forEach(button=>button.addEventListener('click',()=>showStep(state.step-1)));
  root.querySelectorAll('[data-go-step]').forEach(button=>button.addEventListener('click',()=>showStep(button.dataset.goStep)));
  root.querySelectorAll('[data-onboarding-step]').forEach(button=>button.addEventListener('click',()=>{const step=Number(button.dataset.onboardingStep);if(sectionNames.slice(0,step-1).every(name=>state.sections[name]?.complete))showStep(step);else notice('Save earlier sections first.');}));
  root.querySelectorAll('[data-uppercase]').forEach(input=>input.addEventListener('input',()=>{input.value=input.value.toUpperCase().replace(/\s/g,'');}));
  root.querySelectorAll('[data-pincode],[data-indian-phone]').forEach(input=>input.addEventListener('input',()=>{input.value=input.value.replace(/\D/g,'').slice(0,input.hasAttribute('data-pincode')?6:10);}));
  root.querySelector('[data-gst-status]')?.addEventListener('change',updateGst);
  root.querySelectorAll('input[type="file"]').forEach(input=>input.addEventListener('change',()=>{input.setCustomValidity('');notice(input.files.length+' file(s) selected for upload when this section is saved.');}));
  const processing=field('processing_days');if(processing){const old=[...processing.options].find(option=>option.textContent.includes('more than 21'));if(old){old.textContent='Made to order: 22–30 days';old.value='Made to order: 22–30 days';}}
  const dispatchPin=field('dispatch_pin');
  if(dispatchPin&&!field('dispatch_city')){
    const city=document.createElement('div');city.className='vc-field';
    city.innerHTML='<label for="dispatch-city">Dispatch city <em>*</em></label><input class="vc-input" id="dispatch-city" name="dispatch_city" required maxlength="80" autocomplete="address-level2">';
    const stateField=document.createElement('div');stateField.className='vc-field';
    const stateSelect=field('state').cloneNode(true);stateSelect.id='dispatch-state';stateSelect.name='dispatch_state';stateSelect.value='';stateSelect.setAttribute('autocomplete','address-level1');
    const stateLabel=document.createElement('label');stateLabel.htmlFor='dispatch-state';stateLabel.innerHTML='Dispatch state or UT <em>*</em>';
    stateField.append(stateLabel,stateSelect);
    dispatchPin.closest('.vc-field').before(city,stateField);
  }
  const coverage=field('coverage_model');if(coverage)[...coverage.options].forEach(option=>{if(option.value!=='all')option.remove();});
  const exclusions=field('delivery_exclusions');if(exclusions){exclusions.disabled=true;exclusions.placeholder='After approval, add exclusions in Delivery before publishing any product.';}
  root.querySelector('[data-final-submit]')?.addEventListener('click',async()=>{
    if(state.busy||!validate(5))return;
    if(!sectionNames.every(name=>state.sections[name]?.complete)){notice('Save all four sections before submitting.','error');return;}
    state.busy=true;
    try{const result=await request('onboarding.php?action=submit','POST',{});state.application.reference=result.application_reference;state.application.status=result.status;pending();notice('Application submitted for review.','success');}
    catch(error){notice(error.message,'error');}finally{state.busy=false;}
  });
  load().catch(error=>notice(error.message,'error'));
})();
