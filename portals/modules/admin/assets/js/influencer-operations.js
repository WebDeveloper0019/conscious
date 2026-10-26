(function(){
  'use strict';
  const root='../../../api/admin/';
  const campaignForm=document.getElementById('if-admin-campaign-form');
  if(!campaignForm)return;
  const installPath=location.pathname.replace(/\/portals\/modules\/admin\/[^/]+$/,'');
  document.querySelector('#if-admin-assign-form [name="destination_url"]').value=installPath+'/shop.html';
  const campaignReason=document.createElement('input');campaignReason.className='ac-input';campaignReason.placeholder='Reason for pausing or resuming a campaign (10+ characters)';campaignReason.maxLength=500;campaignReason.setAttribute('aria-label','Campaign status change reason');document.getElementById('if-admin-campaign-list').before(campaignReason);
  const $=id=>document.getElementById(id);
  const cell=(tag,value)=>{const item=document.createElement(tag);item.textContent=value==null?'—':String(value);return item;};
  const csrf=()=>sessionStorage.getItem('ci_admin_csrf_token')||decodeURIComponent((document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/)||[])[1]||'');
  async function api(path,method='GET',payload){
    const response=await fetch(root+path,{method,credentials:'same-origin',cache:'no-store',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-Token':csrf()},body:payload?JSON.stringify(payload):undefined});
    const data=await response.json().catch(()=>({}));
    if(!response.ok)throw Error(data.message||'Action failed.');
    return data;
  }
  function notice(message,error=false){const box=$('if-admin-message');box.hidden=false;box.textContent=message;box.className='ac-body'+(error?' error':'');box.scrollIntoView({behavior:'smooth',block:'nearest'});}
  function option(value,label){const node=document.createElement('option');node.value=value;node.textContent=label;return node;}
  async function campaigns(){
    const [campaignData,creatorData]=await Promise.all([api('campaigns.php'),api('influencers.php')]);
    const campaignSelect=$('if-admin-campaign-select'),creatorSelect=$('if-admin-creator-select');
    campaignSelect.replaceChildren(option('','Select campaign'));
    creatorSelect.replaceChildren(option('','Select approved creator'));
    campaignData.campaigns.forEach(item=>campaignSelect.append(option(item.id,item.name+' · '+item.status)));
    creatorData.influencers.filter(item=>item.status==='active').forEach(item=>creatorSelect.append(option(item.id,item.full_name+' · @'+item.handle)));
    const host=$('if-admin-campaign-list');host.replaceChildren();
    if(!campaignData.campaigns.length){host.append(cell('p','No campaigns created yet.'));return;}
    const table=document.createElement('table');table.className='ac-table';
    const head=document.createElement('thead'),header=document.createElement('tr');
    ['Campaign','Commission','Dates','Status','Creators','Action'].forEach(label=>header.append(cell('th',label)));head.append(header);table.append(head);
    const body=document.createElement('tbody');
    campaignData.campaigns.forEach(item=>{const row=document.createElement('tr');[item.name,item.commission_value+(item.commission_type==='percent'?'%':' ₹'),String(item.starts_at)+' → '+String(item.ends_at),item.status,item.influencer_count].forEach(value=>row.append(cell('td',value)));const actionCell=document.createElement('td');if(['active','paused'].includes(item.status)){const control=cell('button',item.status==='active'?'Pause':'Resume');control.type='button';control.className='ac-btn small';control.addEventListener('click',async()=>{const reason=campaignReason.value.trim();if(reason.length<10){notice('Enter a detailed reason for the campaign change.',true);return;}control.disabled=true;try{await api('campaigns.php','PATCH',{campaign_id:item.id,action:item.status==='active'?'pause':'resume',reason});notice('Campaign status updated.');campaignReason.value='';await campaigns();}catch(error){notice(error.message,true);}finally{control.disabled=false;}});actionCell.append(control);}row.append(actionCell);body.append(row);});
    table.append(body);host.append(table);
  }
  campaignForm.addEventListener('submit',async event=>{
    event.preventDefault();const button=campaignForm.querySelector('[type=submit]');button.disabled=true;
    try{const data=Object.fromEntries(new FormData(campaignForm));data.starts_at=new Date(data.starts_at).toISOString();data.ends_at=new Date(data.ends_at).toISOString();await api('campaigns.php','POST',data);notice('Campaign created. Assign an approved creator below.');campaignForm.reset();await campaigns();}
    catch(error){notice(error.message,true);}finally{button.disabled=false;}
  });
  $('if-admin-assign-form').addEventListener('submit',async event=>{
    event.preventDefault();const form=event.currentTarget,button=form.querySelector('[type=submit]');button.disabled=true;
    try{const data=Object.fromEntries(new FormData(form));data.action='assign';await api('campaigns.php','POST',data);notice('Creator assigned. Their referral link is now available in Creator Studio.');await campaigns();}
    catch(error){notice(error.message,true);}finally{button.disabled=false;}
  });
  function button(label,action,id,disabled=false){const item=cell('button',label);item.type='button';item.className='ac-btn small';item.disabled=disabled;item.addEventListener('click',()=>decide(action,id,item));return item;}
  async function payouts(){
    const data=await api('influencer-payouts.php');
    const profiles=$('if-admin-payout-profiles');profiles.replaceChildren();
    if(!data.profiles.length){const row=document.createElement('tr'),empty=cell('td','No payout profiles yet.');empty.colSpan=5;row.append(empty);profiles.append(row);}
    data.profiles.forEach(item=>{const row=document.createElement('tr');[item.full_name+' · @'+item.handle,item.beneficiary_name,'Account ••••'+item.account_last4+' / PAN ••••'+item.pan_last4,item.verification_status].forEach(value=>row.append(cell('td',value)));const actions=document.createElement('td');if(item.verification_status==='pending'){actions.append(button('Verify','verify_profile',item.influencer_id),button('Reject','reject_profile',item.influencer_id));}row.append(actions);profiles.append(row);});
    const withdrawals=$('if-admin-withdrawals');withdrawals.replaceChildren();
    if(!data.withdrawals.length){const row=document.createElement('tr'),empty=cell('td','No withdrawal requests yet.');empty.colSpan=6;row.append(empty);withdrawals.append(row);}
    data.withdrawals.forEach(item=>{const row=document.createElement('tr');[item.full_name+' · @'+item.handle,'₹'+Number(item.amount).toFixed(2),item.status,item.requested_at,item.payment_reference||'—'].forEach(value=>row.append(cell('td',value)));const actions=document.createElement('td');if(item.status==='requested'){actions.append(button('Approve','approve_withdrawal',item.id),button('Reject','reject_withdrawal',item.id));}if(['approved','processing'].includes(item.status))actions.append(button('Mark paid','mark_paid',item.id));row.append(actions);withdrawals.append(row);});
  }
  async function decide(action,id,buttonNode){
    const reason=$('if-admin-payout-reason').value.trim(),payment_reference=$('if-admin-bank-reference').value.trim();
    if(action.startsWith('reject')&&reason.length<10){notice('Enter a detailed rejection reason first.',true);return;}
    if(action==='mark_paid'&&payment_reference.length<6){notice('Enter the actual bank transfer reference after payment.',true);return;}
    if(action==='verify_profile'&&!confirm('Confirm that you checked this creator’s bank and PAN details outside this system?'))return;
    if(action==='mark_paid'&&!confirm('Confirm the money was sent and this bank reference is correct?'))return;
    buttonNode.disabled=true;
    try{await api('influencer-payouts.php','POST',{action,id,reason,payment_reference});notice('Creator payout record updated.');await payouts();}
    catch(error){notice(error.message,true);}finally{buttonNode.disabled=false;}
  }
  Promise.all([campaigns(),payouts()]).catch(error=>notice(error.message,true));
})();
