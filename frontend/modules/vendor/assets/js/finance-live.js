(() => {
  'use strict';
  const api='../../../api/vendor/';
  const el=id=>document.getElementById(id);
  const money=value=>new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:2}).format(Number(value)||0);
  const state={csrf:'',available:0,banks:[]};
  async function call(path,method='GET',body){const headers={Accept:'application/json'};if(method!=='GET')headers['X-CSRF-Token']=state.csrf;if(body!==undefined)headers['Content-Type']='application/json';const response=await fetch(api+path,{method,credentials:'same-origin',cache:'no-store',headers,body:body===undefined?undefined:JSON.stringify(body)});const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw new Error(data.message||'Finance request failed.');return data;}
  function cell(row,value){const td=document.createElement('td');td.textContent=String(value??'—');row.append(td);}
  function empty(body,columns,message){const row=document.createElement('tr');const td=document.createElement('td');td.colSpan=columns;td.textContent=message;row.append(td);body.append(row);}
  function rows(id,items,columns,render,emptyMessage){const body=el(id);body.replaceChildren();if(!items.length)empty(body,columns,emptyMessage);else items.forEach(item=>{const row=document.createElement('tr');render(row,item);body.append(row);});}
  async function load(){
    try{
      const [identity,payouts,summary,cod]=await Promise.all([call('auth/me.php'),call('payouts.php'),call('finance-summary.php'),call('cod-collections.php')]);
      state.csrf=identity.csrf_token||'';state.available=Number(payouts.available)||0;state.banks=(summary.bank_accounts||[]).filter(bank=>bank.verification_status==='verified');
      const balance=payouts.balance||{};
      el('finance-available').textContent=money(state.available);el('finance-pending').textContent=money(balance.pending);
      el('finance-payout-count').textContent=(payouts.payouts||[]).length;el('finance-cod-count').textContent=(cod.collections||[]).length;
      el('finance-eligible').textContent=money(balance.eligible);el('finance-pending-detail').textContent=money(balance.pending);el('finance-held').textContent=money(balance.held);el('finance-paid').textContent=money(balance.paid);
      const select=el('finance-bank');select.replaceChildren();
      state.banks.forEach(bank=>{const option=document.createElement('option');option.value=bank.id;option.textContent=(bank.bank_name||'Bank')+' ···· '+bank.account_last4+' · '+bank.beneficiary_name;select.append(option);});
      const canRequest=state.available>=100&&state.banks.length>0;el('finance-submit').disabled=!canRequest;el('finance-amount').max=String(state.available);
      rows('finance-payout-rows',payouts.payouts||[],5,(row,item)=>{cell(row,item.requested_at);cell(row,money(item.amount));cell(row,(item.bank_name||'Bank')+' ···· '+item.account_last4);cell(row,item.status.replaceAll('_',' '));cell(row,item.payment_reference||'—');},'No payout requests yet.');
      rows('finance-ledger-rows',summary.ledger||[],6,(row,item)=>{cell(row,item.created_at);cell(row,item.entry_type.replaceAll('_',' '));cell(row,item.description);cell(row,money(item.amount));cell(row,item.balance_bucket);cell(row,item.reference_type+' · '+item.reference_id);},'No ledger entries yet.');
      rows('finance-cod-rows',cod.collections||[],6,(row,item)=>{cell(row,item.group_number);cell(row,money(item.amount));cell(row,item.collection_method||'—');cell(row,item.collection_reference||'—');cell(row,item.status.replaceAll('_',' '));cell(row,item.collected_at||item.created_at);},'No COD collections recorded.');
      el('finance-message').textContent=canRequest?'Your live finance records are ready.':'No payout can be requested yet. You need at least ₹100 available and a verified bank account.';
      el('finance-message').classList.remove('warning');
    }catch(error){el('finance-message').textContent=error.message;el('finance-message').classList.add('warning');}
  }
  el('finance-payout-form').addEventListener('submit',async event=>{event.preventDefault();const amount=Number(el('finance-amount').value);if(!Number.isFinite(amount)||amount<100||amount>state.available){el('finance-message').textContent='Enter an amount from ₹100 up to your available balance.';el('finance-message').classList.add('warning');return;}const button=event.submitter;button.disabled=true;try{await call('payouts.php','POST',{amount,bank_account_id:el('finance-bank').value});el('finance-amount').value='';await load();}catch(error){el('finance-message').textContent=error.message;el('finance-message').classList.add('warning');}finally{button.disabled=false;}});
  load();
})();
