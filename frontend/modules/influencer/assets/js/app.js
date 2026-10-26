(function(){
  'use strict';
  var api='../../../api/influencer/';var state={profile:null,dashboard:null};var msg=document.getElementById('if-message');
  var money=new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'});
  function node(tag,cls,value){var el=document.createElement(tag);if(cls)el.className=cls;if(value!==undefined)el.textContent=value;return el;}
  function show(value,error){msg.textContent=value;msg.classList.toggle('error',!!error);msg.hidden=false;msg.scrollIntoView({block:'nearest'});}
  function token(){var cookie=document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);return sessionStorage.getItem('ci_influencer_csrf')||(cookie?decodeURIComponent(cookie[1]):'');}
  async function request(path,method,body,extra){var headers={Accept:'application/json'};if(method!=='GET')headers['X-CSRF-Token']=token();if(body&&! (body instanceof FormData))headers['Content-Type']='application/json';if(extra)Object.assign(headers,extra);var response=await fetch(api+path,{method:method,credentials:'same-origin',cache:'no-store',headers:headers,body:body instanceof FormData?body:body?JSON.stringify(body):undefined});var data=await response.json().catch(function(){return{};});if(!response.ok)throw new Error(data.message||'The action could not be completed.');return data;}
  function formData(form){return Object.fromEntries(new FormData(form).entries());}
  function showFieldState(input){var field=input.closest('label,.if-field');if(!field)return;var error=field.querySelector('.if-field-error');if(input.validity.valid){input.removeAttribute('aria-invalid');if(error)error.remove();return;}if(!error){error=node('small','if-field-error');error.setAttribute('role','alert');field.append(error);}error.textContent=input.validity.valueMissing?'This field is required.':input.validationMessage;input.setAttribute('aria-invalid','true');}
  document.addEventListener('invalid',function(event){showFieldState(event.target);},true);document.addEventListener('input',function(event){if(event.target.matches('input,select,textarea'))showFieldState(event.target);});
  function bindForm(id,handler){var form=document.getElementById(id);form.addEventListener('submit',async function(event){event.preventDefault();form.classList.add('if-validated');if(!form.checkValidity()){form.reportValidity();return;}var button=form.querySelector('[type=submit]');var label=button.textContent;button.disabled=true;button.setAttribute('aria-busy','true');button.textContent='Saving…';try{await handler(form);}catch(error){show(error.message,true);}finally{button.disabled=false;button.removeAttribute('aria-busy');button.textContent=label;}});}
  function selectSection()
  {

    var key=location.hash.slice(1)||'overview';

    if(!document.getElementById(key)||!document.getElementById(key).classList.contains('if-section'))key='overview';document.querySelectorAll('.if-section').forEach(function(el){el.classList.toggle('active',el.id===key);});
    document.querySelectorAll('[data-nav]').forEach(function(el){var active=el.dataset.nav===key;el.classList.toggle('active',active);if(active)el.setAttribute('aria-current','page');else el.removeAttribute('aria-current');});
  document.getElementById('if-title').textContent=({overview:'Overview',application:'Profile & verification',packages:'Service Packages',campaigns:'Campaign links',curations:'Curated products',analytics:'Analytics',earnings:'Earnings',withdrawals:'Withdrawals',inquiries:'Partnership inquiries',notifications:'Notifications',settings:'Settings'})[key];
    closeNavigation();



  }

  var sidebar=document.getElementById('if-sidebar'),menu=document.getElementById('if-menu'),overlay=document.getElementById('if-sidebar-overlay');
  function closeNavigation(){sidebar.classList.remove('open');overlay.classList.remove('show');menu.setAttribute('aria-expanded','false');menu.setAttribute('aria-label','Open navigation');document.body.classList.remove('if-nav-open');}
  addEventListener('hashchange',selectSection);selectSection();menu.addEventListener('click',function(){var open=sidebar.classList.toggle('open');overlay.classList.toggle('show',open);menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Close navigation':'Open navigation');document.body.classList.toggle('if-nav-open',open);});overlay.addEventListener('click',closeNavigation);document.addEventListener('keydown',function(event){if(event.key==='Escape')closeNavigation();});
  document.getElementById('if-logout').addEventListener('click',async function(event){event.preventDefault();try{await request('auth/logout.php','POST');}finally{sessionStorage.removeItem('ci_influencer_csrf');location.replace('auth/login.php');}});
  async function loadIdentity(){var result=await request('auth/me.php','GET');if(result.csrf_token)sessionStorage.setItem('ci_influencer_csrf',result.csrf_token);var creator=result.influencer||{};document.getElementById('if-identity').textContent=(creator.full_name||'Creator')+' · '+(creator.email||'');document.getElementById('if-account-status').textContent=String(creator.status||'').replaceAll('_',' ');document.getElementById('if-visibility').textContent=creator.status==='active'?'Your approved profile is eligible for public listing.':'Your profile remains private until admin approval.';return creator;}
  function platformField(label,name,value,type){var field=node('label','if-field',label);var input=node('input');input.name=name;input.required=true;input.value=value||'';input.maxLength=name==='platform'?50:255;if(type)input.type=type;if(name==='followers'){input.inputMode='numeric';input.pattern='[0-9]{1,12}';input.maxLength=12;}if(name==='platform_url')input.placeholder='https://…';field.append(input);return field;}
  function addPlatform(platform){var list=document.getElementById('if-platforms');if(list.children.length>=5)return;var row=node('div','if-platform-row');row.append(platformField('Social platform','platform',platform.name||''),platformField('Profile URL','platform_url',platform.url||'','url'),platformField('Follower count','followers',platform.followers===undefined?'':String(platform.followers)));var remove=node('button','if-text-button','Remove');remove.type='button';remove.setAttribute('aria-label','Remove this social platform');remove.addEventListener('click',function(){row.remove();if(!list.children.length)addPlatform({});document.getElementById('if-add-platform').disabled=list.children.length>=5;});row.append(remove);list.append(row);document.getElementById('if-add-platform').disabled=list.children.length>=5;}
  document.getElementById('if-add-platform').addEventListener('click',function(){addPlatform({});});
  function ensureAudienceFields(states){
    var form=document.getElementById('if-profile-form');if(form.elements.niches)return;
    var anchor=form.elements.audience_region.closest('label');
    function inputField(title,name,placeholder,required){var field=node('label','if-field',title);var input=node('input');input.name=name;input.placeholder=placeholder;input.maxLength=255;input.required=required;field.append(input);anchor.after(field);anchor=field;return input;}
    inputField('Creator niches (1–5, comma separated)','niches','Sustainable living, Indian crafts',true);
    inputField('Languages (1–5, comma separated)','languages','Hindi, English',true);
    var city=inputField('Your city in India','city','Mumbai',false);city.maxLength=100;
    var stateField=node('label','if-field','Your state or union territory');var select=node('select');select.name='state';select.append(node('option','','Choose a state or UT'));(states||[]).forEach(function(name){var option=node('option','',name);option.value=name;select.append(option);});stateField.append(select);anchor.after(stateField);
  }
  async function loadApplication(){
    var data=await request('application.php','GET');state.profile=data.profile;
    var form=document.getElementById('if-profile-form');form.elements.bio.value=data.profile.bio||'';
    var platforms=JSON.parse(data.profile.platforms_json||'[]');var audience=JSON.parse(data.profile.audience_json||'{}');
    ensureAudienceFields(data.states);form.elements.niches.value=(audience.niches||[]).join(', ');form.elements.languages.value=(audience.languages||[]).join(', ');form.elements.city.value=audience.city||'';form.elements.state.value=audience.state||'';
    document.getElementById('if-platforms').replaceChildren();(platforms.length?platforms:[{}]).slice(0,5).forEach(addPlatform);
    form.elements.audience_region.value=audience.region||'';
    var status=document.getElementById('if-application-status');status.textContent='Application: '+String(data.profile.status||'draft').replaceAll('_',' ');
    var review=document.getElementById('if-review-feedback');if(!review){review=node('p','if-notice');review.id='if-review-feedback';status.after(review);}
    var needsChanges=['changes_requested','rejected','suspended'].includes(data.profile.status);
    review.hidden=!(needsChanges&&data.review&&data.review.reason);
    review.textContent=review.hidden?'':'Admin feedback: '+data.review.reason;
    var docs=document.getElementById('if-documents');docs.replaceChildren();
    if(!data.documents.length)docs.append(node('p','if-muted','No documents uploaded yet.'));
    var latest={};data.documents.forEach(function(item){if(!latest[item.document_type])latest[item.document_type]=item;});
    data.documents.forEach(function(item){var copy=item.document_type.replaceAll('_',' ')+' — '+(latest[item.document_type]===item?'current · ':'')+'scan: '+item.scan_status+', review: '+item.verification_status;if(item.rejection_reason)copy+=' · Admin feedback: '+item.rejection_reason;docs.append(node('p','',copy));});
    var payout=document.getElementById('if-payout-status');payout.textContent=data.payout_profile?'Bank account ending '+data.payout_profile.account_last4+' · '+data.payout_profile.verification_status:'No bank details saved yet.';
    var checklist=document.getElementById('if-application-checklist');if(!checklist){checklist=node('p','if-muted');checklist.id='if-application-checklist';document.getElementById('if-submit-application').before(checklist);}
    var missing=[];if((data.profile.bio||'').trim().length<40||!platforms.length||!(audience.niches||[]).length||!(audience.languages||[]).length)missing.push('save your bio, niches, languages and social profile');
    ['identity','pan','social_ownership'].forEach(function(type){var item=latest[type];if(!item)missing.push('upload '+type.replaceAll('_',' '));else if(item.scan_status!=='clean')missing.push('wait for '+type.replaceAll('_',' ')+' security scan');else if(item.verification_status==='rejected')missing.push('replace rejected '+type.replaceAll('_',' '));});
    checklist.textContent=missing.length?'Before submission: '+missing.join('; ')+'.':'Your profile and required documents are ready for submission.';
    document.getElementById('if-submit-application').disabled=!['application_draft','changes_requested'].includes(data.profile.status)||missing.length>0;
  }
  bindForm('if-profile-form',async function(form){var d=formData(form);var platforms=Array.from(document.querySelectorAll('#if-platforms .if-platform-row')).map(function(row){return{name:row.querySelector('[name=platform]').value.trim(),url:row.querySelector('[name=platform_url]').value.trim(),followers:Number(row.querySelector('[name=followers]').value)};});var split=function(value){return value.split(',').map(function(item){return item.trim();}).filter(Boolean);};var result=await request('application.php','PATCH',{bio:d.bio.trim(),platforms:platforms,audience:{region:d.audience_region.trim(),city:d.city.trim(),state:d.state,niches:split(d.niches),languages:split(d.languages)}});show(result.message||'Profile saved.');await loadApplication();await loadIdentity();});
  bindForm('if-document-form',async function(form){var input=form.elements.document;var file=input.files[0];if(!file||file.size>5*1024*1024)throw new Error('Choose a file smaller than 5 MB.');if(!['application/pdf','image/jpeg','image/png'].includes(file.type))throw new Error('Use PDF, JPEG or PNG.');var body=new FormData(form);await request('application.php?action=upload','POST',body);show('Document uploaded. Security scanning must finish before application submission.');form.reset();await loadApplication();});
  bindForm('if-payout-form',async function(form){var d=formData(form);await request('application.php?action=payout','POST',d);form.reset();show('Payout details saved for verification.');await loadApplication();});
  document.getElementById('if-submit-application').addEventListener('click',async function(){this.disabled=true;try{await request('application.php?action=submit','POST',{});show('Application submitted for admin review.');await loadApplication();}catch(error){show(error.message,true);this.disabled=false;}});
  async function loadDashboard(){var data=await request('dashboard.php','GET');state.dashboard=data;var stats=data.stats||{};[['if-clicks',stats.clicks||0],['if-conversions',stats.conversions||0],['if-revenue',money.format(Number(stats.revenue)||0)],['if-commission',money.format(Number(stats.commission)||0)],['if-total-commission',money.format(Number(stats.commission)||0)]].forEach(function(pair){document.getElementById(pair[0]).textContent=pair[1];});var balances=Object.fromEntries((data.balances||[]).map(function(item){return[item.status,Number(item.amount)||0];}));document.getElementById('if-eligible').textContent=money.format(balances.eligible||0);document.getElementById('if-pending').textContent=money.format(balances.pending||0);document.getElementById('if-held').textContent=money.format((balances.held||0)+(balances.flagged||0));var next=document.getElementById('if-next-step');next.textContent=state.profile&&state.profile.status==='active'?'Share an assigned referral link with its required disclosure.':'Complete your profile and verification, then submit your application for review.';var campaignList=document.getElementById('if-campaign-list');var overview=document.getElementById('if-overview-campaigns');campaignList.replaceChildren();overview.replaceChildren();if(!data.campaigns.length){campaignList.append(node('p','if-empty','No campaigns assigned yet.'));overview.append(node('p','if-empty','No campaigns assigned yet.'));}data.campaigns.forEach(function(campaign){var row=node('article','if-card');row.append(node('strong','',campaign.name),node('p','if-muted',campaign.description||''));row.append(node('p','',campaign.status+' · '+campaign.link_status));var link=new URL('../../../api/public/referral.php?code='+encodeURIComponent(campaign.code),location.href).href;var input=node('input');input.value=link;input.readOnly=true;input.setAttribute('aria-label','Referral API link');row.append(input);row.append(node('p','if-muted','Disclosure: '+campaign.disclosure_text));var copy=node('button','if-button','Copy link');copy.type='button';copy.addEventListener('click',function(){navigator.clipboard.writeText(link).then(function(){show('Link copied. Include the disclosure when sharing.');}).catch(function(){show('Select and copy the link shown above.');});});row.append(copy);campaignList.append(row);overview.append(node('p','',campaign.name+' · '+campaign.link_status));});}
  async function loadAnalytics(){var days=document.getElementById('if-period').value;var data=await request('analytics.php?days='+encodeURIComponent(days),'GET');var clicks=(data.clicks||[]).reduce(function(sum,row){return sum+Number(row.clicks||0);},0);var conversions=(data.conversions||[]).reduce(function(sum,row){return sum+Number(row.conversions||0);},0);var box=document.getElementById('if-analytics');box.replaceChildren(node('p','',clicks+' clicks · '+conversions+' conversions in the selected period'));if(!data.conversions.length)box.append(node('p','if-muted','No attributed orders in this period.'));data.conversions.forEach(function(row){box.append(node('p','',row.day+' · '+row.status+' · '+row.conversions+' orders · '+money.format(Number(row.commission))))});var ledger=document.getElementById('if-ledger');ledger.replaceChildren();if(!data.ledger.length)ledger.append(node('p','if-empty','No commission entries yet.'));data.ledger.forEach(function(row){ledger.append(node('p','',row.created_at+' · '+row.entry_type+' · '+money.format(Number(row.amount))+' · '+row.status));});}
  document.getElementById('if-period').addEventListener('change',function(){loadAnalytics().catch(function(error){show(error.message,true);});});
  async function loadWithdrawals(){var data=await request('withdrawals.php','GET');var box=document.getElementById('if-withdraw-list');box.replaceChildren();if(!data.withdrawals.length)box.append(node('p','if-empty','No withdrawal requests yet.'));data.withdrawals.forEach(function(row){box.append(node('p','',row.requested_at+' · '+money.format(Number(row.amount))+' · '+row.status));});}
  bindForm('if-withdraw-form',async function(form){var amount=Number(form.elements.amount.value);await request('withdrawals.php','POST',{amount:amount},{'Idempotency-Key':crypto.randomUUID()});form.reset();show('Withdrawal requested.');await Promise.all([loadWithdrawals(),loadDashboard()]);document.getElementById('if-eligible').textContent=money.format(Number(state.dashboard.available_to_withdraw)||0);});
  async function loadNotifications(){var data=await request('notifications.php','GET');var box=document.getElementById('if-notification-list');box.replaceChildren();if(!data.notifications.length)box.append(node('p','if-empty','No updates yet.'));data.notifications.forEach(function(item){var row=node('article','if-card');row.append(node('strong','',item.title),node('p','',item.message),node('small','',item.created_at));if(!item.read_at){var button=node('button','if-text-button','Mark read');button.type='button';button.addEventListener('click',async function(){try{await request('notifications.php','PATCH',{id:item.id});button.remove();}catch(error){show(error.message,true);}});row.append(button);}box.append(row);});}
  async function loadInquiries(){var data=await request('inquiries.php','GET');var box=document.getElementById('if-inquiry-list');box.replaceChildren();if(!data.inquiries.length){box.append(node('p','if-empty','No partnership inquiries yet.'));return;}data.inquiries.forEach(function(item){var row=node('article','if-card');row.append(node('strong','',item.sender_name+' · '+item.package_name),node('p','if-muted',item.created_at+' · '+item.status),node('p','',item.message||'No message supplied.'));var email=node('a','','Email '+item.sender_name);email.href='mailto:'+item.sender_email;row.append(email,node('p','',item.sender_phone));if(item.status!=='closed'){var action=node('button','if-text-button',item.status==='new'?'Mark read':'Close inquiry');action.type='button';action.addEventListener('click',async function(){action.disabled=true;try{await request('inquiries.php','PATCH',{id:item.id,status:item.status==='new'?'read':'closed'});await loadInquiries();}catch(error){show(error.message,true);action.disabled=false;}});row.append(action);}box.append(row);});}
  function productRow(product,featured){var row=node('article','if-product-row');if(product.image_url){var image=node('img');image.src=new URL('../../../api/public/'+product.image_url,location.href).href;image.alt=product.name;image.loading='lazy';row.append(image);}var copy=node('div');copy.append(node('strong','',product.name),node('p','if-muted',money.format(Number(product.selling_price||product.price||0))));row.append(copy);var action=node('button','if-button',featured?'Remove':'Feature');action.type='button';action.addEventListener('click',async function(){action.disabled=true;try{var result=await request('curations.php',featured?'DELETE':'POST',{product_id:product.product_id||product.id});show(result.message);await loadCurations();}catch(error){show(error.message,true);}finally{action.disabled=false;}});row.append(action);return row;}
  async function loadCurations(){var data=await request('curations.php','GET');var box=document.getElementById('if-curated-products');box.replaceChildren();if(!data.products.length)box.append(node('p','if-empty','No products featured yet. Search the marketplace above.'));data.products.forEach(function(product){box.append(productRow(product,true));});}
  bindForm('if-product-search',async function(form){var query=form.elements.query.value.trim();var response=await fetch('../../../api/public/catalog.php?q='+encodeURIComponent(query)+'&limit=12',{headers:{Accept:'application/json'},cache:'no-store'});var data=await response.json().catch(function(){return{};});if(!response.ok)throw new Error(data.message||'Product search failed.');var box=document.getElementById('if-product-results');box.replaceChildren();if(!data.products.length)box.append(node('p','if-empty','No approved products match this search.'));data.products.forEach(function(product){box.append(productRow(product,false));});});
  bindForm('if-password-form',async function(form){var data=formData(form);await request('account.php','PATCH',data);form.reset();show('Password changed. Other sessions have been signed out.');});
  // --- PACKAGES LOGIC ---
  async function loadPackages() {
    try {
      var data = await request('packages.php', 'GET');
      var box = document.getElementById('if-package-list');
      if (!box) return;
      box.replaceChildren();

      if (!data.packages || !data.packages.length) {
        box.append(node('p', 'if-empty', 'No packages created yet. Create your first package below.'));
        return;
      }

      data.packages.forEach(function(pkg) {
        var card = node('article', 'if-card if-package-card');

        var head = node('div', 'if-package-head');

        var title = node('h3', '', pkg.title);
        head.append(title);

        var badge = node('span', 'if-package-status '+pkg.status, pkg.status.replaceAll('_', ' '));

        head.append(badge);
        card.append(head);

        card.append(node('p', 'if-muted', '₹' + pkg.price_inr + ' · ' + pkg.turnaround_days + ' days turnaround · ' + pkg.capacity + ' slots/month'));
        card.append(node('p', '', pkg.description));
        if (pkg.review_note) card.append(node('p', 'if-notice', 'Review note: ' + pkg.review_note));

        var actions = node('div', 'if-actions');

        function createActionBtn(text, action, id, isSecondary) {
          var btn = node('button', isSecondary ? 'if-text-button' : 'if-button', text);
          btn.type = 'button';
          btn.addEventListener('click', async function() {
            btn.disabled = true;
            try {
              await request('packages.php', 'PATCH', { id: id, action: action });
              show('Package status updated.');
              await loadPackages();
            } catch(e) {
              show(e.message, true);
              btn.disabled = false;
            }
          });
          return btn;
        }

        if (pkg.status === 'draft') {
          actions.append(createActionBtn('Submit for Approval', 'submit', pkg.id, false));
        }
        if (pkg.status === 'active') {
          actions.append(createActionBtn('Pause', 'pause', pkg.id, false));
        }
        if (pkg.status === 'paused') {
          actions.append(createActionBtn('Resume', 'resume', pkg.id, false));
        }
        if (pkg.status !== 'archived' && pkg.status !== 'pending_approval') {
          actions.append(createActionBtn('Archive', 'archive', pkg.id, true));
        }

        card.append(actions);
        box.append(card);
      });
    } catch (e) { show(e.message || 'Packages could not be loaded.', true); }
  }

  bindForm('if-package-form', async function(form) {
    var d = formData(form);
    var payload = {
      title: d.title,
      description: d.description,
      price_inr: Number(d.price_inr),
      turnaround_days: Number(d.turnaround_days),
      revisions: Number(d.revisions),
      capacity: Number(d.capacity),
      deliverables_json: d.deliverables.split(',').map(s => s.trim()).filter(Boolean),
      platforms_json: d.platforms.split(',').map(s => s.trim()).filter(Boolean),
      usage_rights: d.usage_rights,
      vendor_requirements: d.vendor_requirements,
      tax_treatment: d.tax_treatment,
      cancellation_terms: d.cancellation_terms
    };

    await request('packages.php', 'POST', payload);
    form.reset();
    show('Package saved as Draft.');
    await loadPackages();
  });


  async function boot(){try{await loadIdentity();await loadApplication();await Promise.all([loadDashboard(),loadAnalytics(),loadWithdrawals(),loadNotifications(),loadInquiries(),loadCurations(),loadPackages()]);document.getElementById('if-eligible').textContent=money.format(Number(state.dashboard.available_to_withdraw)||0);document.getElementById('if-eligible').previousElementSibling.textContent='Available to withdraw';}catch(error){if(/session|sign in|permission|expired/i.test(error.message))location.replace('auth/login.php');else show(error.message,true);}}
  boot();
})();
