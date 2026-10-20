(() => {
  'use strict';
  const byId=id=>document.getElementById(id);
  const count=value=>new Intl.NumberFormat('en-IN').format(Number(value)||0);
  const money=value=>new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(Number(value)||0);
  async function read(path){const response=await fetch('../../../api/admin/'+path,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});const data=await response.json().catch(()=>({}));if(!response.ok||!data.success)throw Error(data.message||'Unable to load live platform data.');return data;}
  async function load(){try{
    const [dashboard,work]=await Promise.all([read('dashboard.php'),read('work-queue.php')]);const m=dashboard.metrics||{};
    const values={customers:m.customers,vendors:m.active_vendors,orders:m.orders_today,applications:m.pending_vendor_applications,products:m.pending_products,refunds:m.pending_refunds,payouts:m.pending_payouts,delivery:m.delivery_exceptions,support:m.open_support,influencers:m.active_influencers,events:m.upcoming_events};
    Object.entries(values).forEach(([key,value])=>{const node=byId('ad-'+key);if(node)node.textContent=count(value);});byId('ad-sales').textContent=money(m.gross_sales_today);
    const queues=[['Vendor applications','vendor_applications','vendors.php'],['Products to approve','products','catalog.php'],['Sustainability claims','claims','catalog.php'],['Refund requests','refunds','finance.php'],['Payout requests','payouts','finance.php'],['Support tickets','support','support.php'],['Creator applications','influencers','influencers.php']];
    const box=byId('ad-priority');box.replaceChildren();queues.forEach(([title,key,href])=>{const length=(work.queue?.[key]||[]).length;if(!length)return;const link=document.createElement('a');link.className='ac-dashboard-row';link.href=href;const label=document.createElement('span');label.textContent=title;const number=document.createElement('strong');number.textContent=count(length);link.append(label,number);box.append(link);});
    if(!box.childElementCount){const p=document.createElement('p');p.textContent='No items in the current review queues.';box.append(p);}
    byId('ac-dashboard-message').textContent='Current counts from marketplace records. No sample revenue or activity is shown.';
  }catch(error){byId('ac-dashboard-message').textContent=error.message;byId('ac-dashboard-message').classList.add('warning');byId('ad-priority').textContent='The work queue could not be loaded. Refresh to retry.';}}
  load();
})();
