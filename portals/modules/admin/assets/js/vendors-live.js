(() => {
  'use strict';
  const byId=id=>document.getElementById(id);
  const state={page:1,limit:10,total:0,pages:1,busy:false};
  async function request(path){const response=await fetch('/conscious/api/admin/'+path,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw Error(data.message||'Unable to load vendor applications.');return data;}
  const number=value=>new Intl.NumberFormat('en-IN').format(Number(value)||0);
  const readable=value=>String(value||'unknown').replaceAll('_',' ');
  const date=value=>value?new Intl.DateTimeFormat('en-IN',{dateStyle:'medium',timeStyle:'short'}).format(new Date(String(value).replace(' ','T')+'Z')):'Not submitted';
  function show(message,error=false){byId('av-message').textContent=message;byId('av-message').classList.toggle('error',error);}
  function metric(id,value){byId(id).textContent=number(value);}
  function renderSummary(counts={}){metric('av-count-pending',counts.submitted);metric('av-count-review',counts.under_review);metric('av-count-changes',counts.changes_requested);metric('av-count-approved',counts.approved);}
  function badge(value,extra=''){const node=document.createElement('span');node.className='av-badge '+extra+' '+String(value||'unknown').replaceAll('_','-');node.textContent=readable(value);return node;}
  function textCell(){const cell=document.createElement('td');return cell;}
  function actionLink(application,label,icon,kind,action=''){const link=document.createElement('a');link.className='av-action-button '+kind;link.href='/conscious/admin/vendors/'+encodeURIComponent(application.id)+(action?'?action='+encodeURIComponent(action):'');link.title=label;link.setAttribute('aria-label',label+' '+application.display_name);link.innerHTML='<i class="fa-solid '+icon+'" aria-hidden="true"></i><span></span>';link.querySelector('span').textContent=label;return link;}
  function disabledAction(label,icon,kind,title){const button=document.createElement('button');button.type='button';button.className='av-action-button '+kind;button.disabled=true;button.title=title;button.setAttribute('aria-label',label+': '+title);button.innerHTML='<i class="fa-solid '+icon+'" aria-hidden="true"></i><span></span>';button.querySelector('span').textContent=label;return button;}
  function inactiveReason(status){if(status==='changes_requested')return'Waiting for the vendor to correct and resubmit the application.';if(status==='approved')return'This application is already approved.';if(status==='rejected')return'This application was permanently rejected.';return'This action is unavailable for the current application status.';}
  function row(application){
    const item=document.createElement('tr');
    const vendor=textCell();const vendorWrap=document.createElement('div');vendorWrap.className='av-vendor-cell';const avatar=document.createElement('span');avatar.textContent=(application.display_name||application.owner_name||'V').trim().slice(0,2).toUpperCase();const copy=document.createElement('div');const name=document.createElement('b');name.textContent=application.display_name||'Unnamed vendor';const owner=document.createElement('small');owner.textContent=application.owner_name+' · '+application.email;copy.append(name,owner);vendorWrap.append(avatar,copy);vendor.append(vendorWrap);
    const reference=textCell();const ref=document.createElement('b');ref.textContent=application.reference;const type=badge(application.vendor_type,'type');reference.append(ref,document.createElement('br'),type);
    const submitted=textCell();submitted.textContent=date(application.submitted_at||application.updated_at);
    const completion=textCell();const completionTop=document.createElement('div');completionTop.className='av-table-progress-label';completionTop.textContent=number(application.completion_percent)+'%';const track=document.createElement('div');track.className='av-progress';const fill=document.createElement('span');fill.style.width=Math.max(0,Math.min(100,Number(application.completion_percent)||0))+'%';track.append(fill);completion.append(completionTop,track);
    const status=textCell();status.append(badge(application.status));
    const actions=textCell();actions.className='av-table-actions';actions.append(actionLink(application,'View','fa-eye','view'));if(['submitted','under_review'].includes(application.status)){actions.append(actionLink(application,'Approve','fa-check','approve','approve'),actionLink(application,'Correct / reject','fa-rotate-left','reject','reject'));}else{const why=inactiveReason(application.status);actions.append(disabledAction('Approve','fa-check','approve',why),disabledAction(application.status==='changes_requested'?'Correction sent':'Correct / reject','fa-rotate-left','reject',why));}
    item.append(vendor,reference,submitted,completion,status,actions);return item;
  }
  function renderPagination(){state.pages=Math.max(1,Math.ceil(state.total/state.limit));state.page=Math.min(state.page,state.pages);byId('av-prev').disabled=state.page<=1||state.busy;byId('av-next').disabled=state.page>=state.pages||state.busy;byId('av-page-label').textContent='Page '+state.page+' of '+state.pages;const start=state.total?(state.page-1)*state.limit+1:0,end=Math.min(state.page*state.limit,state.total);byId('av-result-count').textContent=state.total?number(start)+'–'+number(end)+' of '+number(state.total):'0 results';}
  async function load(){
    if(state.busy)return;state.busy=true;renderPagination();show('Loading applications…');
    try{state.limit=Number(byId('av-limit').value)||10;const params=new URLSearchParams({limit:String(state.limit),offset:String((state.page-1)*state.limit)});const q=byId('av-search').value.trim(),status=byId('av-status').value,type=byId('av-type').value;if(q)params.set('q',q);if(status)params.set('status',status);if(type)params.set('vendor_type',type);const data=await request('vendor-applications.php?'+params);state.total=Number(data.total)||0;renderSummary(data.status_counts);const body=byId('av-rows');body.replaceChildren();if(!data.applications.length){const empty=document.createElement('td');empty.colSpan=6;empty.className='av-table-empty';empty.innerHTML='<i class="fa-regular fa-folder-open"></i><b>No applications found</b><span>Adjust the filters and try again.</span>';const emptyRow=document.createElement('tr');emptyRow.append(empty);body.append(emptyRow);}else data.applications.forEach(application=>body.append(row(application)));show(number(state.total)+' application'+(state.total===1?'':'s')+' found.');}catch(error){show(error.message,true);}finally{state.busy=false;renderPagination();}
  }
  function resetAndLoad(){state.page=1;load();}
  byId('av-refresh').addEventListener('click',load);byId('av-status').addEventListener('change',resetAndLoad);byId('av-type').addEventListener('change',resetAndLoad);byId('av-limit').addEventListener('change',resetAndLoad);
  let timer;byId('av-search').addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(resetAndLoad,300);});
  byId('av-clear').addEventListener('click',()=>{byId('av-search').value='';byId('av-status').value='';byId('av-type').value='';byId('av-limit').value='10';resetAndLoad();});
  byId('av-prev').addEventListener('click',()=>{if(state.page>1){state.page--;load();}});byId('av-next').addEventListener('click',()=>{if(state.page<state.pages){state.page++;load();}});
  load();
})();
