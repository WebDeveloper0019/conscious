(() => {
  'use strict';
  const root=document.querySelector('[data-vendor-onboarding]'); if(!root)return;
  const form=root.querySelector('[data-onboarding-form]');
  const sectionNames=['profile','verification'];
  const state={type:root.dataset.initialType,step:1,sections:{},documents:[],application:null,csrf:'',busy:false,submitKey:null};
  const field=name=>form.elements.namedItem(name);
  const value=name=>String(field(name)?.value||'').trim();
  const checked=name=>Boolean(field(name)?.checked);
  const set=(name,content)=>{const input=field(name);if(input&&content!==undefined&&content!==null&&input.type!=='file')input.value=String(content);};
  const apiFieldName=name=>({
    legal_name:state.type==='brand'?'brand_legal_name':'artisan_legal_name',
    public_name:state.type==='brand'?'brand_public_name':'artisan_public_name',
    primary_category:'artisan_category',story:state.type==='brand'?'brand_story':'artisan_story',preferred_language:'artisan_language',
    operating_model:'artisan_model',craft_tradition:'artisan_craft',years_practising:'artisan_years',maker_count:'artisan_count',
    legal_structure:'brand_structure',registration_number:'brand_registration_number',representative_name:'brand_contact_name',representative_designation:'brand_designation',
    year_established:'brand_year',team_size:'brand_team',website:'brand_website',social_profile:'brand_social'
  }[name]||name);
  function clearServerError(input){
    if(!input||input.dataset.serverError!=='1')return;
    input.setCustomValidity('');input.removeAttribute('aria-invalid');delete input.dataset.serverError;
    const error=input.closest('.vc-field')?.querySelector('.vc-field-error[data-server-error]');if(error)error.remove();
  }
  function validationMessage(input){
    if(input.validity.valueMissing)return input.type==='file'?'Choose the required document.':'This field is required.';
    if(input.validity.typeMismatch)return input.type==='email'?'Enter a valid email address.':'Enter a valid value.';
    if(input.validity.tooShort)return'Enter at least '+input.minLength+' characters.';
    if(input.validity.tooLong)return'Use no more than '+input.maxLength+' characters.';
    if(input.validity.rangeUnderflow)return'Enter a value of at least '+input.min+'.';
    if(input.validity.rangeOverflow)return'Enter a value no greater than '+input.max+'.';
    return input.validationMessage||'Check this field.';
  }
  function showClientError(input,message){
    const wrap=input.closest('.vc-field');if(!wrap)return false;
    let error=wrap.querySelector('.vc-field-error[data-client-error]')||wrap.querySelector('.vc-field-error:not([data-server-error])');
    if(!error){error=document.createElement('small');error.className='vc-field-error';wrap.append(error);}
    if(!input.id)input.id='onboarding-field-'+Math.random().toString(36).slice(2);
    error.dataset.clientError='1';error.id=input.id+'-error';error.setAttribute('role','alert');error.textContent=message;error.hidden=false;
    input.setAttribute('aria-invalid','true');input.setAttribute('aria-describedby',error.id);return true;
  }
  function clearClientErrors(panel){panel.querySelectorAll('.vc-field-error[data-client-error]').forEach(error=>error.remove());panel.querySelectorAll('[aria-invalid="true"]:not([data-server-error="1"])').forEach(input=>{input.removeAttribute('aria-invalid');input.removeAttribute('aria-describedby');});}
  function applyServerErrors(errors,step){
    const panel=root.querySelector('[data-step-panel="'+step+'"]');let first=null;
    Object.entries(errors||{}).forEach(([name,message])=>{
      const input=field(apiFieldName(name));if(!input||input.disabled)return;
      clearServerError(input);input.setCustomValidity(String(message));input.dataset.serverError='1';input.setAttribute('aria-invalid','true');
      const wrap=input.closest('.vc-field');if(wrap){let error=wrap.querySelector('.vc-field-error[data-server-error]');if(!error){error=document.createElement('small');error.className='vc-field-error';error.dataset.serverError='1';error.setAttribute('role','alert');wrap.append(error);}error.textContent=String(message);}
      if(!first)first=input;
    });
    if(first){panel?.scrollIntoView({behavior:'smooth',block:'start'});setTimeout(()=>{first.focus({preventScroll:true});first.scrollIntoView({behavior:'smooth',block:'center'});},180);}
    return Boolean(first);
  }
  function notice(message,kind='info'){
    const wrap=document.getElementById('vc-toast-wrap'); if(!wrap)return;
    const node=document.createElement('div');node.className='vc-toast '+kind;node.textContent=message;wrap.append(node);
    requestAnimationFrame(()=>node.classList.add('show'));
    setTimeout(()=>{node.classList.remove('show');setTimeout(()=>node.remove(),250);},5200);
  }
  function idempotencyKey(){
    if(globalThis.crypto?.randomUUID)return 'vendor-onboarding-submit:'+globalThis.crypto.randomUUID();
    const bytes=new Uint32Array(4);globalThis.crypto?.getRandomValues?.(bytes);
    return 'vendor-onboarding-submit:'+Date.now().toString(36)+':'+Array.from(bytes,value=>value.toString(36)).join('.');
  }
  async function request(path,method='GET',body,multipart=false,idempotencyKeyValue=''){
    const headers={Accept:'application/json'};
    if(method!=='GET')headers['X-CSRF-Token']=state.csrf;
    if(body!==undefined&&!multipart)headers['Content-Type']='application/json';
    if(idempotencyKeyValue)headers['Idempotency-Key']=idempotencyKeyValue;
    const response=await fetch('/conscious/api/vendor/'+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:multipart?body:JSON.stringify(body)});
    const data=await response.json().catch(()=>({}));
    if(!response.ok||!data.success){const missing=[...(data.missing_sections||[]),...(data.missing_documents||[])];const error=Error((data.message||'Unable to save this section.')+(missing.length?' Missing: '+missing.join(', '):''));error.fieldErrors=data.errors&&typeof data.errors==='object'?data.errors:null;error.status=response.status;throw error;}
    return data;
  }
  function applyType(){
    root.querySelectorAll('[data-role-fields]').forEach(group=>{const enabled=group.dataset.roleFields===state.type;group.hidden=!enabled;group.querySelectorAll('input,select,textarea').forEach(input=>input.disabled=!enabled);});
    root.querySelectorAll('[data-type-name],[data-pending-type]').forEach(node=>node.textContent=state.type);
    root.querySelector('[data-registration-path]').textContent=state.type==='brand'?'Brand or registered business':'Artisan, maker or collective';
    root.querySelector('[data-type-intro]').textContent=state.type==='brand'?'Tell us who owns and operates the registered brand.':'Tell us about you, your craft and the people who make the products.';
    root.querySelector('[data-review-profile]').textContent=state.type==='brand'?'Brand and representative profile':'Artisan and craft profile';
    root.querySelector('[data-change-type]').hidden=true;
  }
  function showStep(next){
    const firstIncomplete=sectionNames.findIndex(name=>!state.sections[name]?.complete);
    const lastAvailable=firstIncomplete<0?5:firstIncomplete+1;
    state.step=Math.max(1,Math.min(lastAvailable,Number(next)||1));
    root.querySelectorAll('[data-step-panel]').forEach(panel=>panel.hidden=Number(panel.dataset.stepPanel)!==state.step);
    root.querySelectorAll('[data-onboarding-step]').forEach(button=>{const step=Number(button.dataset.onboardingStep);button.hidden=step===3||step===4;button.classList.toggle('active',step===state.step);button.classList.toggle('done',step<state.step&&Boolean(state.sections[sectionNames[step-1]]?.complete));button.disabled=step>lastAvailable;button.setAttribute('aria-current',step===state.step?'step':'false');button.setAttribute('aria-label',step>lastAvailable?'Complete the previous step to unlock '+button.textContent.trim():button.textContent.trim());});
    root.querySelectorAll('.vo-steps>i').forEach((line,index)=>line.hidden=index>1);
    let progress=root.querySelector('.vo-progress-track');if(!progress){progress=document.createElement('div');progress.className='vo-progress-track';progress.setAttribute('role','progressbar');progress.setAttribute('aria-label','Application progress');progress.setAttribute('aria-valuemin','0');progress.setAttribute('aria-valuemax','100');progress.innerHTML='<span></span>';root.querySelector('.vo-steps').after(progress);}const percent=state.step===5?100:(state.step-1)*50;progress.setAttribute('aria-valuenow',String(percent));progress.querySelector('span').style.width=percent+'%';
    sessionStorage.setItem('ci_vendor_onboarding_step',String(state.step));
    history.replaceState(null,'','onboarding.php?type='+encodeURIComponent(state.type)+'&step='+state.step);
  }
  function documentType(input){return input.name==='product_samples'?'product_sample':input.name;}
  function uploaded(type){return state.documents.filter(document=>document.document_type===type&&document.verification_status!=='rejected').length;}
  function latestDocument(type){
    return state.documents.filter(document=>document.document_type===type).at(-1)||null;
  }
  function documentStatus(document){
    if(document.verification_status==='rejected')return 'Needs replacement';
    if(document.verification_status==='verified')return 'Verified';
    if(document.scan_status==='infected')return 'Security check failed';
    if(document.scan_status==='clean')return 'Uploaded · awaiting review';
    if(document.scan_status==='bypassed')return 'Uploaded · available for local testing';
    return 'Uploaded · security scan pending';
  }
  function renderDocumentStatus(input){
    const host=input.closest('.vc-field')||input.closest('label')||input.parentElement;
    let panel=host.querySelector(':scope > .vo-upload-file');
    const uploadedDocument=latestDocument(documentType(input));
    const selected=[...(input.files||[])];
    input.classList.toggle('vo-file-input-hidden',Boolean(uploadedDocument));
    if(!uploadedDocument&&!selected.length){panel?.remove();return;}
    if(!panel){panel=document.createElement('div');panel.className='vo-upload-file';input.after(panel);}
    panel.replaceChildren();panel.classList.toggle('rejected',uploadedDocument?.verification_status==='rejected'||uploadedDocument?.scan_status==='infected');
    const icon=uploadedDocument?.verification_status==='verified'?'fa-circle-check':uploadedDocument?.verification_status==='rejected'||uploadedDocument?.scan_status==='infected'?'fa-triangle-exclamation':'fa-file-shield';
    const copy=document.createElement('div');copy.className='vo-upload-file-copy';
    const title=document.createElement('b');title.textContent=selected.length?(uploadedDocument?'Replacement selected: ':'Selected: ')+selected.map(file=>file.name).join(', '):(uploadedDocument.original_name||'Uploaded document');
    const meta=document.createElement('small');
    if(selected.length)meta.textContent='Save and continue to upload '+(uploadedDocument?'the replacement.':'this file.');
    else {
      const size=uploadedDocument.size_bytes?new Intl.NumberFormat('en-IN',{maximumFractionDigits:1}).format(Number(uploadedDocument.size_bytes)/1024)+' KB':'';
      meta.textContent=[documentStatus(uploadedDocument),size].filter(Boolean).join(' · ');
    }
    copy.append(title,meta);
    if(!selected.length&&uploadedDocument?.rejection_reason){const reason=document.createElement('small');reason.className='vo-upload-reason';reason.textContent=uploadedDocument.rejection_reason;copy.append(reason);}
    const actions=document.createElement('div');actions.className='vo-upload-file-actions';
    if(!selected.length&&uploadedDocument?.file_id&&['clean','bypassed'].includes(uploadedDocument.scan_status)){
      const view=document.createElement('a');view.href='/conscious/api/files.php?id='+encodeURIComponent(uploadedDocument.file_id);view.target='_blank';view.rel='noopener';view.textContent='View';actions.append(view);
    }
    const replace=document.createElement('button');replace.type='button';replace.textContent=uploadedDocument?'Replace':'Choose file';replace.addEventListener('click',()=>input.click());actions.append(replace);
    const badge=document.createElement('span');badge.className='vo-upload-file-icon';badge.innerHTML='<i class="fa-solid '+icon+'" aria-hidden="true"></i>';
    panel.append(badge,copy,actions);
  }
  function fileRequirements(){
    root.querySelectorAll('input[type="file"]').forEach(input=>{
      if(input.dataset.originalRequired===undefined)input.dataset.originalRequired=input.required?'1':'0';
      const needed=input.name==='bank_proof'?0:(input.dataset.originalRequired==='1'?(input.name==='product_samples'?3:1):0);
      input.required=uploaded(documentType(input))<needed;
      input.setCustomValidity('');
      renderDocumentStatus(input);
    });
  }
  function validate(step){
    const panel=root.querySelector('[data-step-panel="'+step+'"]');
    if(!panel)return false;
    form.classList.add('was-validated');clearClientErrors(panel);panel.querySelectorAll('input[data-server-error="1"],select[data-server-error="1"],textarea[data-server-error="1"]').forEach(clearServerError);
    panel.querySelectorAll('input[type="file"]:not(:disabled)').forEach(input=>{
      input.setCustomValidity('');const files=[...(input.files||[])];
      const needed=input.name==='bank_proof'?0:(input.dataset.originalRequired==='1'?(input.name==='product_samples'?3:1):0);
      if(uploaded(documentType(input))+files.length<needed){input.setCustomValidity('Choose the required document'+(needed>1?'s':'')+'.');return;}
      if(input.name==='product_samples'&&files.length>6){input.setCustomValidity('Choose at most six product photographs.');return;}
      const max=Number(input.dataset.maxBytes||5242880),allowed=(input.accept||'').split(',').map(v=>v.trim());
      if(files.some(file=>file.size<1||file.size>max||!allowed.includes(file.type)))input.setCustomValidity('Use an allowed file type within the size limit.');
    });
    if(step===2&&value('bank_account')!==value('bank_confirm'))field('bank_confirm').setCustomValidity('Bank account numbers must match.');
    else field('bank_confirm')?.setCustomValidity('');
    const invalidFields=[...panel.querySelectorAll('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)')].filter(input=>!input.validity.valid);invalidFields.forEach(input=>showClientError(input,validationMessage(input)));const invalid=invalidFields[0];if(invalid){invalid.focus();invalid.scrollIntoView({behavior:'smooth',block:'center'});return false;}return true;
  }
  function payload(step){
    if(step===1){const brand=state.type==='brand';return{
      legal_name:value(brand?'brand_legal_name':'artisan_legal_name'),public_name:value(brand?'brand_public_name':'artisan_public_name'),primary_category:brand?'Brand':value('artisan_category'),story:value(brand?'brand_story':'artisan_story'),preferred_language:brand?'English':value('artisan_language'),address:value('address'),city:value('city'),district:value('district'),state:value('state'),pincode:value('pincode'),
      ...(brand?{legal_structure:value('brand_structure'),registration_number:value('brand_registration_number'),representative_name:value('brand_contact_name'),representative_designation:value('brand_designation'),year_established:value('brand_year')?Number(value('brand_year')):null,team_size:value('brand_team')?Number(value('brand_team')):null,website:value('brand_website'),social_profile:value('brand_social')}:{operating_model:value('artisan_model'),craft_tradition:value('artisan_craft'),years_practising:value('artisan_years')?Number(value('artisan_years')):null,maker_count:value('artisan_count')?Number(value('artisan_count')):null})
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
  function pending(){form.hidden=true;root.querySelector('.vo-steps').hidden=true;root.querySelector('.vo-progress-track')?.setAttribute('hidden','');root.querySelector('.vo-intro').hidden=true;const panel=root.querySelector('[data-onboarding-pending]');panel.hidden=false;panel.querySelector('.vo-pending-actions')?.remove();panel.querySelector('[data-application-reference]').textContent=state.application?.reference||'Pending';panel.querySelector('[data-pending-type]').textContent=state.type;}
  function renderCorrectionNotice(){
    root.querySelector('.vo-correction-notice')?.remove();
    if(state.application?.status!=='changes_requested')return;
    const noticeBox=document.createElement('div');noticeBox.className='vo-correction-notice';noticeBox.setAttribute('role','alert');
    const icon=document.createElement('span');icon.innerHTML='<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>';
    const copy=document.createElement('div');const title=document.createElement('b');title.textContent='Changes requested by the review team';const reason=document.createElement('p');reason.textContent=state.application.decision_reason||'Replace any rejected document shown below, then review and submit the application again.';copy.append(title,reason);noticeBox.append(icon,copy);root.querySelector('.vo-intro').after(noticeBox);
  }
  async function load(){
    const identity=await request('auth/me.php');state.csrf=identity.csrf_token||'';
    const data=await request('onboarding.php');state.type=data.vendor.vendor_type==='brand'?'brand':'artisan';state.sections=data.sections||{};state.documents=data.documents||[];state.application=data.application;
    applyType();populate();renderCorrectionNotice();
    ['artisan_story','brand_story'].forEach(name=>{const input=field(name);if(input){input.required=false;input.closest('.vc-field').hidden=true;}});
    ['artisan_years','artisan_count','artisan_language','brand_year','brand_team','brand_website','brand_social','district'].forEach(name=>{const input=field(name);if(input){input.required=false;input.closest('.vc-field').hidden=true;}});
    const bankFields=field('beneficiary')?.closest('.vo-grid');if(bankFields){bankFields.hidden=true;bankFields.querySelectorAll('input').forEach(input=>{input.required=false;input.disabled=true;});if(bankFields.previousElementSibling?.classList.contains('vo-subhead'))bankFields.previousElementSibling.hidden=true;}
    root.querySelectorAll('[data-step-panel="5"] .vo-review-summary article').forEach((article,index)=>{if(index>1)article.hidden=true;});
    const verificationTitle=root.querySelector('[data-step-panel="2"]>header h2');if(verificationTitle)verificationTitle.textContent='Verify your identity';
    const reviewProof=root.querySelector('[data-step-panel="5"] .vo-review-summary article:nth-child(2) b');if(reviewProof)reviewProof.textContent='Identity and business proof';
    const reviewButton=root.querySelector('[data-onboarding-step="5"] b');if(reviewButton)reviewButton.textContent='Review';
    const reviewStepNumber=root.querySelector('[data-onboarding-step="5"] > span');if(reviewStepNumber)reviewStepNumber.textContent='3';
    root.querySelectorAll('.vo-panel>header small').forEach(node=>{node.textContent=node.closest('[data-step-panel="5"]')?'Step 3 of 3':node.closest('[data-step-panel="2"]')?'Step 2 of 3':'Step 1 of 3';});
    if(['approved','demo_fixture'].includes(data.application.status)&&data.vendor.status==='active'){location.replace('dashboard.php');return;}
    if(!['draft','changes_requested'].includes(data.application.status)){pending();return;}
    const firstIncomplete=sectionNames.findIndex(name=>!state.sections[name]?.complete);
    const requested=Number(new URLSearchParams(location.search).get('step'));
    const wanted=requested||Number(sessionStorage.getItem('ci_vendor_onboarding_step'))||1;
    showStep(Math.min(Math.max(wanted,1),5));
  }
  root.querySelectorAll('[data-step-next]').forEach(button=>button.addEventListener('click',async()=>{
    const step=state.step;if(state.busy||!validate(step))return;state.busy=true;button.disabled=true;
    try{
      if(!(step===2&&state.sections.verification?.complete&&!value('pan')&&!value('bank_account')))await request('onboarding.php?section='+sectionNames[step-1],'PATCH',payload(step));
      await upload(step);
      const data=await request('onboarding.php');state.sections=data.sections||{};state.documents=data.documents||[];fileRequirements();showStep(step===2?5:step+1);notice('Section saved to your application.','success');
    }catch(error){const placed=error.fieldErrors?applyServerErrors(error.fieldErrors,step):false;if(!placed)notice(error.message,'error');}finally{state.busy=false;button.disabled=false;}
  }));
  root.querySelectorAll('[data-step-back]').forEach(button=>button.addEventListener('click',()=>showStep(state.step===5?2:state.step-1)));
  root.querySelectorAll('[data-go-step]').forEach(button=>button.addEventListener('click',()=>showStep(button.dataset.goStep)));
  root.querySelectorAll('[data-onboarding-step]').forEach(button=>button.addEventListener('click',()=>showStep(Number(button.dataset.onboardingStep))));
  root.querySelectorAll('[data-uppercase]').forEach(input=>input.addEventListener('input',()=>{input.value=input.value.toUpperCase().replace(/\s/g,'');}));
  form.addEventListener('input',event=>clearServerError(event.target));
  form.addEventListener('change',event=>clearServerError(event.target));
  root.querySelectorAll('[data-pincode],[data-indian-phone]').forEach(input=>input.addEventListener('input',()=>{input.value=input.value.replace(/\D/g,'').slice(0,input.hasAttribute('data-pincode')?6:10);}));
  root.querySelector('[data-gst-status]')?.addEventListener('change',updateGst);
  root.querySelectorAll('input[type="file"]').forEach(input=>input.addEventListener('change',()=>{input.setCustomValidity('');renderDocumentStatus(input);notice(input.files.length+' file(s) selected for upload when this section is saved.');}));
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
  const submitButton=root.querySelector('[data-final-submit]');
  submitButton?.addEventListener('click',async()=>{
    if(state.busy||!validate(5))return;
    if(!sectionNames.every(name=>state.sections[name]?.complete)){notice('Save your profile and identity details before submitting.','error');return;}
    state.busy=true;submitButton.disabled=true;state.submitKey=state.submitKey||idempotencyKey();
    try{const result=await request('onboarding.php?action=submit','POST',{},false,state.submitKey);state.application.reference=result.application_reference;state.application.status=result.status;state.submitKey=null;pending();notice('Application submitted for review.','success');}
    catch(error){notice(error.message,'error');}finally{state.busy=false;submitButton.disabled=false;}
  });
  load().catch(error=>notice(error.message,'error'));
})();
