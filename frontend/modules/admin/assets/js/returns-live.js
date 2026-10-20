(() => {
  'use strict';
  const table=document.getElementById('ar-flags'),status=document.getElementById('ar-status'),dialog=document.getElementById('ar-dialog'),form=document.getElementById('ar-form');
  if(!table||!status||!dialog||!form)return;
  let selected=null;
  const endpoint='../../../api/admin/return-review-flags.php';
  const cell=(row,value)=>{const item=document.createElement('td');item.textContent=value??'—';row.append(item);return item;};
  const date=value=>value?new Date(value.replace(' ','T')+'Z').toLocaleDateString('en-IN'):'—';
  async function request(method='GET',body){const response=await fetch(endpoint,{method,credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json',...(method==='GET'?{}:{'Content-Type':'application/json','X-CSRF-Token':sessionStorage.getItem('ci_admin_csrf_token')||''})},body:body?JSON.stringify(body):undefined});const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw Error(data.message||'Unable to load return reviews.');return data;}
  async function load(){status.textContent='Loading review flags…';table.replaceChildren();try{const data=await request();const flags=data.flags||[];status.textContent=flags.length?flags.length+' customer review flag'+(flags.length===1?'':'s'):'No repeated-return review flags.';flags.forEach(flag=>{const row=document.createElement('tr');cell(row,flag.customer_id);cell(row,flag.vendor_name||'Platform-wide');cell(row,flag.request_count+' in '+flag.window_days+' days');cell(row,date(flag.first_flagged_at));cell(row,flag.vendor_note||'No vendor note');cell(row,(flag.status||'open')+(flag.admin_note?' · '+flag.admin_note:''));const action=cell(row,'');const button=document.createElement('button');button.className='ac-btn small';button.type='button';button.textContent='Review';button.addEventListener('click',()=>{selected=flag;document.getElementById('ar-customer').textContent='Customer '+flag.customer_id+' · '+(flag.vendor_name||'Platform-wide')+' · '+flag.request_count+' requests';form.elements.status.value=flag.status||'open';form.elements.note.value=flag.admin_note||'';dialog.showModal();});action.append(button);table.append(row);});}catch(error){status.textContent=error.message;}}
  form.addEventListener('submit',async event=>{event.preventDefault();if(!selected||!form.reportValidity())return;const button=form.querySelector('[type=submit]');button.disabled=true;try{const data=await request('PATCH',{flag_id:selected.id,status:form.elements.status.value,note:form.elements.note.value.trim()});dialog.close();await load();status.textContent=data.message;}catch(error){status.textContent=error.message;}finally{button.disabled=false;}});
  document.getElementById('ar-close').addEventListener('click',()=>dialog.close());
  document.getElementById('ar-refresh').addEventListener('click',load);
  load();
})();
