(function(){
  'use strict';
  var root=document.querySelector('[data-vendor-onboarding]');
  if(!root)return;
  var storageKey='ci_vendor_onboarding_v2';
  var form=root.querySelector('[data-onboarding-form]');
  var stepButtons=Array.from(root.querySelectorAll('[data-onboarding-step]'));
  var panels=Array.from(root.querySelectorAll('[data-step-panel]'));
  var pending=root.querySelector('[data-onboarding-pending]');
  var current=1;
  var completed=0;
  var type=root.dataset.initialType==='brand'?'brand':'artisan';

  function readState(){
    try{
      var raw=JSON.parse(sessionStorage.getItem(storageKey)||'{}');
      return{type:raw.type==='brand'?'brand':'artisan',status:['draft','submitted','approved','changes_requested'].includes(raw.status)?raw.status:'draft',step:Math.max(1,Math.min(5,Number(raw.step)||1)),reference:String(raw.reference||'').slice(0,30),submittedAt:String(raw.submittedAt||'').slice(0,40)};
    }catch(error){return{type:type,status:'draft',step:1,reference:'',submittedAt:''};}
  }
  function saveState(next){
    sessionStorage.setItem(storageKey,JSON.stringify({type:next.type==='brand'?'brand':'artisan',status:next.status||'draft',step:Math.max(1,Math.min(5,Number(next.step)||1)),reference:String(next.reference||'').slice(0,30),submittedAt:String(next.submittedAt||'').slice(0,40)}));
  }
  function toast(message,typeName){
    var wrap=document.getElementById('vc-toast-wrap');if(!wrap)return;
    var item=document.createElement('div');item.className='vc-toast '+(typeName||'success');item.textContent=message;wrap.appendChild(item);
    requestAnimationFrame(function(){item.classList.add('show');});setTimeout(function(){item.classList.remove('show');setTimeout(function(){item.remove();},250);},3300);
  }
  function setRole(nextType){
    type=nextType==='brand'?'brand':'artisan';
    root.querySelectorAll('[data-role-fields]').forEach(function(section){
      var enabled=section.dataset.roleFields===type;section.hidden=!enabled;
      section.querySelectorAll('input,select,textarea,button').forEach(function(control){control.disabled=!enabled;});
    });
    root.querySelectorAll('[data-type-name],[data-pending-type]').forEach(function(node){node.textContent=type;});
    var path=root.querySelector('[data-registration-path]');if(path)path.textContent=type==='brand'?'Brand or registered business':'Artisan, maker or collective';
    var icon=root.querySelector('.vo-type-reminder>span i');if(icon)icon.className=type==='brand'?'fa-solid fa-building':'fa-solid fa-hands';
    var intro=root.querySelector('[data-type-intro]');if(intro)intro.textContent=type==='brand'?'Tell us who owns and operates the registered brand.':'Tell us about you, your craft and the people who make the products.';
    var review=root.querySelector('[data-review-profile]');if(review)review.textContent=type==='brand'?'Brand and representative profile':'Artisan and craft profile';
    history.replaceState(null,'','onboarding.php?type='+encodeURIComponent(type));
    saveState({type:type,status:'draft',step:current});
  }
  function showStep(step,force){
    step=Math.max(1,Math.min(5,Number(step)||1));
    if(!force&&step>completed+1){toast('Complete the current section before moving ahead.','info');return;}
    current=step;
    panels.forEach(function(panel){panel.hidden=Number(panel.dataset.stepPanel)!==step;});
    stepButtons.forEach(function(button){var number=Number(button.dataset.onboardingStep);button.classList.toggle('active',number===step);button.classList.toggle('done',number<step||number<=completed);button.setAttribute('aria-current',number===step?'step':'false');});
    saveState({type:type,status:'draft',step:step});
    root.querySelector('.vo-steps').scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'nearest'});
  }
  function validateFile(input){
    input.setCustomValidity('');var files=Array.from(input.files||[]);var max=Number(input.dataset.maxBytes||5242880);var rules=(input.accept||'').split(',').map(function(rule){return rule.trim();}).filter(Boolean);
    if(input.required&&!files.length){input.setCustomValidity('Choose the required document.');return false;}
    for(var i=0;i<files.length;i+=1){var file=files[i];var allowed=!rules.length||rules.some(function(rule){return rule.endsWith('/*')?file.type.startsWith(rule.slice(0,-1)):file.type===rule;});if(!allowed){input.setCustomValidity('Choose an allowed JPG, PNG, WebP or PDF file.');return false;}if(file.size>max){input.setCustomValidity('Each file must be within the stated size limit.');return false;}}
    if(input.name==='product_samples'&&(files.length<3||files.length>6)){input.setCustomValidity('Choose between 3 and 6 product photographs.');return false;}
    return true;
  }
  function validatePanel(step){
    var panel=root.querySelector('[data-step-panel="'+step+'"]');if(!panel)return true;
    panel.querySelectorAll('input[type="file"]:not(:disabled)').forEach(validateFile);
    if(step===2){var account=form.elements.bank_account;var confirm=form.elements.bank_confirm;if(account&&confirm)confirm.setCustomValidity(account.value===confirm.value?'':'Account numbers do not match.');}
    var invalid=panel.querySelector(':invalid');if(invalid){invalid.focus();invalid.reportValidity();toast('Complete the highlighted required information.','error');return false;}return true;
  }
  function showPending(state){
    form.hidden=true;root.querySelector('.vo-steps').hidden=true;root.querySelector('.vo-intro').hidden=true;pending.hidden=false;
    pending.querySelector('[data-pending-type]').textContent=state.type;
    pending.querySelector('[data-application-reference]').textContent=state.reference||'VND-APP-2026-000';
    pending.scrollIntoView({behavior:'smooth',block:'start'});
  }

  var stored=readState();
  var queryType=new URLSearchParams(location.search).get('type');
  setRole(queryType==='brand'||queryType==='artisan'?queryType:stored.type||type);
  current=stored.step;completed=Math.max(0,current-1);
  if(stored.status==='submitted'){showPending(stored);}else{showStep(current,true);}

  stepButtons.forEach(function(button){button.addEventListener('click',function(){showStep(button.dataset.onboardingStep,false);});});
  root.querySelectorAll('[data-step-next]').forEach(function(button){button.addEventListener('click',function(){if(!validatePanel(current))return;completed=Math.max(completed,current);showStep(current+1,true);toast('Section validated in this frontend preview.','success');});});
  root.querySelectorAll('[data-step-back]').forEach(function(button){button.addEventListener('click',function(){showStep(current-1,true);});});
  root.querySelectorAll('[data-go-step]').forEach(function(button){button.addEventListener('click',function(){showStep(button.dataset.goStep,true);});});
  root.querySelector('[data-change-type]')?.addEventListener('click',function(){var next=type==='brand'?'artisan':'brand';if(window.confirm('Switch to the '+next+' onboarding path? Information entered in the other path will not be submitted.')){setRole(next);completed=0;showStep(1,true);}});
  root.querySelectorAll('[data-uppercase]').forEach(function(input){input.addEventListener('input',function(){input.value=input.value.toUpperCase().replace(/\s/g,'');});});
  root.querySelectorAll('[data-pincode],[data-indian-phone]').forEach(function(input){input.addEventListener('input',function(){input.value=input.value.replace(/\D/g,'').slice(0,input.hasAttribute('data-pincode')?6:10);});});
  var gstStatus=root.querySelector('[data-gst-status]');
  function updateGst(){var registered=gstStatus&&gstStatus.value==='registered';root.querySelectorAll('[data-gst-field]').forEach(function(field){field.hidden=!registered;field.querySelectorAll('input').forEach(function(input){input.required=registered;input.disabled=!registered;});});}
  gstStatus?.addEventListener('change',updateGst);updateGst();
  root.querySelectorAll('input[type="file"]').forEach(function(input){input.addEventListener('change',function(){validateFile(input);if(input.checkValidity()&&input.files.length)toast(input.files.length+' file'+(input.files.length===1?'':'s')+' selected and validated.','info');});});
  root.querySelector('[data-final-submit]')?.addEventListener('click',function(){
    for(var step=1;step<=4;step+=1){if(!validatePanel(step)){showStep(step,true);return;}}
    if(!validatePanel(5))return;
    var reference='VND-'+(type==='brand'?'BR':'AR')+'-'+new Date().getFullYear()+'-'+String(Math.floor(1000+Math.random()*9000));
    var submittedAt=new Intl.DateTimeFormat('en-IN',{day:'numeric',month:'long',year:'numeric'}).format(new Date());
    var state={type:type,status:'submitted',step:5,reference:reference,submittedAt:submittedAt};saveState(state);showPending(state);toast('Application submitted for verification.','success');
  });
})();
