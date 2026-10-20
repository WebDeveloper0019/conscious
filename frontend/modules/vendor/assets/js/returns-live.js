(() => {
  'use strict';
  const api='../../../api/vendor/';
  const el=id=>document.getElementById(id);
  const actions={requested:[['approve','Approve return'],['request_evidence','Request more evidence'],['reject','Reject return']],evidence_requested:[['approve','Approve return'],['reject','Reject return']],approved:[['mark_received','Mark item received']],received:[['recommend_refund','Recommend refund'],['approve_replacement','Approve replacement']]};
  const state={returns:[],flags:[],csrf:'',selected:null};
  async function call(path,method='GET',body){const headers={Accept:'application/json'};if(method!=='GET')headers['X-CSRF-Token']=state.csrf;if(body!==undefined)headers['Content-Type']='application/json';const response=await fetch(api+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:JSON.stringify(body)});const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw new Error(data.message||'Return request failed.');return data;}
  function cell(row,value){const td=document.createElement('td');td.textContent=String(value??'—');row.append(td);return td;}
  function statusText(value){return String(value||'').replaceAll('_',' ');}
  function render(){
    const list=state.returns,body=el('returns-rows');body.replaceChildren();
    el('returns-review-count').textContent=list.filter(item=>['requested','evidence_requested'].includes(item.status)).length;
    el('returns-approved-count').textContent=list.filter(item=>item.status==='approved').length;
    el('returns-received-count').textContent=list.filter(item=>item.status==='received').length;
    el('returns-resolution-count').textContent=list.filter(item=>['refund_recommended','replacement_approved'].includes(item.status)).length;
    if(!list.length){const row=document.createElement('tr');const td=cell(row,'No real customer return requests for your store yet.');td.colSpan=7;body.append(row);return;}
    list.forEach(item=>{const row=document.createElement('tr');cell(row,item.order_number);cell(row,item.product_name);cell(row,statusText(item.request_type)+' · '+item.quantity);cell(row,statusText(item.reason_code));cell(row,item.requested_at);cell(row,statusText(item.status));const action=cell(row,'');const button=document.createElement('button');button.type='button';button.className='vc-btn small';button.textContent='View';button.addEventListener('click',()=>open(item));action.append(button);body.append(row);});
  }
  function renderFlags(){const wrap=el('returns-flags');wrap.replaceChildren();if(!state.flags.length){wrap.textContent='No repeated-return review flags for your store.';return;}state.flags.forEach(flag=>{const row=document.createElement('div');row.className='vc-list-item';const copy=document.createElement('div');copy.className='vc-list-copy';const title=document.createElement('strong');title.textContent=flag.request_count+' requests in '+flag.window_days+' days · '+statusText(flag.status);const description=document.createElement('p');description.textContent='Customer reference: '+flag.customer_id+(flag.vendor_note?' · Your note: '+flag.vendor_note:'');copy.append(title,description);const button=document.createElement('button');button.type='button';button.className='vc-btn small';button.textContent=flag.vendor_note?'Update note':'Record concern';button.addEventListener('click',()=>{const form=el('returns-flag-form');form.elements.namedItem('flag_id').value=flag.id;form.elements.namedItem('note').value=flag.vendor_note||'';el('returns-flag-dialog').showModal();});row.append(copy,button);wrap.append(row);});}
  function open(item){
    state.selected=item;
    el('returns-review-title').textContent='Review return · '+item.order_number;
    el('returns-detail-order').textContent=item.order_number;
    el('returns-detail-product').textContent=item.product_name;
    el('returns-detail-type').textContent=statusText(item.request_type)+' · '+item.quantity+' unit(s)';
    el('returns-detail-reason').textContent=statusText(item.reason_code);
    el('returns-detail-description').textContent=item.description || 'No description supplied.';
    const evidence=el('returns-evidence');evidence.replaceChildren();
    if(!item.evidence?.length)evidence.textContent='No evidence supplied.';
    else item.evidence.forEach((file,index)=>{const line=document.createElement('div');if(file.url){const link=document.createElement('a');link.href=file.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent='View scanned evidence '+(index+1)+(file.caption?' · '+file.caption:'');line.append(link);}else line.textContent='Evidence '+(index+1)+' is awaiting a clean security scan.';evidence.append(line);});
    const select=el('returns-decision');select.replaceChildren();
    (actions[item.status]||[]).forEach(([value,label])=>{const option=document.createElement('option');option.value=value;option.textContent=label;select.append(option);});
    select.disabled=!select.options.length;
    el('returns-note').value='';
    el('returns-decision-form').querySelector('[type="submit"]').disabled=!select.options.length;
    el('returns-review-dialog').showModal();
  }
  async function load(){try{const [identity,data,flags]=await Promise.all([call('auth/me.php'),call('returns.php'),call('return-review-flags.php')]);state.csrf=identity.csrf_token||'';state.returns=data.returns||[];state.flags=flags.flags||[];el('returns-message').textContent='Showing '+state.returns.length+' return request(s) belonging to your store. All decisions are saved to the backend.';el('returns-message').classList.remove('warning');render();renderFlags();}catch(error){el('returns-message').textContent=error.message;el('returns-message').classList.add('warning');}}
  el('returns-refresh').addEventListener('click',load);
  el('returns-close').addEventListener('click',()=>el('returns-review-dialog').close());
  el('returns-decision-form').addEventListener('submit',async event=>{event.preventDefault();if(!state.selected)return;const button=event.submitter;button.disabled=true;try{await call('returns.php','POST',{return_id:state.selected.id,decision:el('returns-decision').value,note:el('returns-note').value.trim()});el('returns-review-dialog').close();await load();}catch(error){el('returns-message').textContent=error.message;el('returns-message').classList.add('warning');}finally{button.disabled=false;}});
  el('returns-flag-close').addEventListener('click',()=>el('returns-flag-dialog').close());
  el('returns-flag-form').addEventListener('submit',async event=>{event.preventDefault();if(!event.currentTarget.reportValidity())return;const button=event.submitter;button.disabled=true;try{await call('return-review-flags.php','POST',Object.fromEntries(new FormData(event.currentTarget)));el('returns-flag-dialog').close();await load();}catch(error){el('returns-message').textContent=error.message;el('returns-message').classList.add('warning');}finally{button.disabled=false;}});
  load();
})();
