/**
 * Conscious Indi — customer-side backend integration.
 * Uses the authenticated JWT created by /api/auth/*.php.
 */
(function () {
  'use strict';

  var TOKEN_KEY = 'ci_token';
  var API = '../api/user/';
  var token = function () { return localStorage.getItem(TOKEN_KEY) || ''; };

  function headers(json) {
    var h = { 'Authorization': 'Bearer ' + token(), 'Accept': 'application/json' };
    if (json) h['Content-Type'] = 'application/json';
    return h;
  }

  async function api(path, options) {
    options = options || {};
    options.headers = Object.assign(headers(!!options.body), options.headers || {});
    var res = await fetch(API + path, options);
    var data = await res.json().catch(function () { return {}; });
    if (!res.ok || data.success === false) throw new Error(data.message || 'Request failed.');
    return data;
  }

  function toast(message, ok) {
    var el = document.getElementById('ci-api-message');
    if (!el) {
      el = document.createElement('div');
      el.id = 'ci-api-message';
      el.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:9999;padding:12px 18px;border-radius:10px;background:#183b56;color:#fff;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.18);';
      document.body.appendChild(el);
    }
    el.style.background = ok === false ? '#b42318' : '#176b4d';
    el.textContent = message;
    clearTimeout(el._timer);
    el._timer = setTimeout(function () { el.remove(); }, 3000);
  }

  function setBusy(button, busy) {
    if (!button) return;
    button.disabled = busy;
    button.dataset.originalText = button.dataset.originalText || button.textContent.trim();
    button.textContent = busy ? 'Saving…' : button.dataset.originalText;
  }

  async function profile() {
    var data = await api('profile.php');
    var u = data.user;
    var map = {
      firstName: u.firstName || '',
      lastName: u.lastName || '',
      email: u.email || '',
      mobile: u.mobile || ''
    };
    Object.keys(map).forEach(function (key) {
      var el = document.querySelector('[data-user-field="' + key + '"]');
      if (el) el.value = map[key];
    });
    var dob=document.querySelector('[data-user-field="dateOfBirth"]');
    var gender=document.querySelector('[data-user-field="gender"]');
    var ann=document.querySelector('[data-user-field="anniversaryDate"]');
    if(dob) dob.value=u.dateOfBirth||'';
    if(gender) gender.value=u.gender||'';
    if(ann) ann.value=u.anniversaryDate||'';
  }

  function bindProfile() {
    var save = document.querySelector('[data-action="save-profile"]');
    if (save) save.addEventListener('click', async function () {
      var body = {};
      document.querySelectorAll('[data-user-field]').forEach(function (el) {
        body[el.dataset.userField] = el.value;
      });
      setBusy(save, true);
      try {
        var data=await api('profile.php',{method:'PUT',body:JSON.stringify(body)});
        localStorage.setItem('ci_user',JSON.stringify(data.user));
        toast('Profile updated successfully.');
        if (window.CI_PANEL_AUTH && window.CI_PANEL_AUTH.renderUser) window.CI_PANEL_AUTH.renderUser(data.user);
      } catch(e){ toast(e.message,false); }
      finally { setBusy(save,false); }
    });

    var password=document.querySelector('[data-action="change-password"]');
    if(password) password.addEventListener('click',async function(){
      var inputs=document.querySelectorAll('[data-password-field]');
      var body={};
      inputs.forEach(function(el){body[el.dataset.passwordField]=el.value;});
      setBusy(password,true);
      try {
        await api('password.php',{method:'POST',body:JSON.stringify(body)});
        inputs.forEach(function(el){el.value='';});
        toast('Password updated successfully.');
      } catch(e){toast(e.message,false);}
      finally{setBusy(password,false);}
    });
  }

  function bindSettings() {
    var save=document.querySelector('[data-action="save-preferences"]');
    if(!save)return;
    api('preferences.php').then(function(data){
      var p=data.preferences;
      [['orderUpdates','orderUpdates'],['offersPromotions','offersPromotions'],['wishlistAlerts','wishlistAlerts'],['newsletter','newsletter']].forEach(function(pair){
        var el=document.querySelector('[data-preference="'+pair[0]+'"]'); if(el) el.checked=!!p[pair[1]];
      });
    }).catch(function(e){toast(e.message,false);});
    save.addEventListener('click',async function(){
      var body={};
      document.querySelectorAll('[data-preference]').forEach(function(el){body[el.dataset.preference]=el.checked;});
      setBusy(save,true);
      try{await api('preferences.php',{method:'PUT',body:JSON.stringify(body)});toast('Preferences saved successfully.');}
      catch(e){toast(e.message,false);} finally{setBusy(save,false);}
    });
  }

  async function loadAddresses() {
    var container=document.querySelector('[data-address-list]'); if(!container)return;
    try {
      var data=await api('addresses.php');
      container.innerHTML='';
      if(!data.addresses.length){container.innerHTML='<div class="ci-alert">No saved addresses yet.</div>';return;}
      data.addresses.forEach(function(a){
        var card=document.createElement('div'); card.className='col-lg-4';
        card.innerHTML='<div style="padding:24px;border:1.5px solid var(--border-warm);border-radius:var(--r-xl);background:var(--bg-primary);">'+
          '<div style="font-size:.8125rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;"><i class="fa-solid fa-location-dot"></i> '+escapeHtml(a.label)+(a.isDefault?' · Default':'')+'</div>'+
          '<div style="font-size:.9rem;color:var(--text-primary);font-weight:600;margin-bottom:4px;">'+escapeHtml(a.fullName)+'</div>'+
          '<div style="font-size:.875rem;color:var(--text-secondary);line-height:1.7;">'+escapeHtml(a.addressLine1)+'<br>'+escapeHtml(a.addressLine2||'')+(a.addressLine2?'<br>':'')+escapeHtml(a.city)+', '+escapeHtml(a.state)+' '+escapeHtml(a.pincode)+'<br>'+escapeHtml(a.phone)+'</div>'+
          '<div class="d-flex gap-2 mt-3"><button class="bc-btn bc-btn-outline bc-btn-sm" data-edit-address="'+a.id+'"><i class="fa-solid fa-pen"></i> Edit</button><button class="bc-btn bc-btn-outline bc-btn-sm" style="color:#E53E3E;border-color:#E53E3E;" data-delete-address="'+a.id+'"><i class="fa-solid fa-trash-can"></i></button></div></div>';
        container.appendChild(card);
      });
      container.querySelectorAll('[data-delete-address]').forEach(function(btn){btn.onclick=async function(){
        if(!confirm('Delete this address?'))return;
        try{await api('addresses.php?id='+encodeURIComponent(btn.dataset.deleteAddress),{method:'DELETE'});toast('Address deleted.');loadAddresses();}
        catch(e){toast(e.message,false);}
      };});
    }catch(e){toast(e.message,false);}
  }
  function escapeHtml(v){return String(v??'').replace(/[&<>"']/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c];});}

  function bindAddressForm(){
    var save=document.querySelector('[data-action="save-address"]'); if(!save)return;
    save.addEventListener('click',async function(){
      var body={};
      document.querySelectorAll('[data-address-field]').forEach(function(el){body[el.dataset.addressField]=el.value;});
      setBusy(save,true);
      try{await api('addresses.php',{method:'POST',body:JSON.stringify(body)});toast('Address saved successfully.');document.querySelectorAll('[data-address-field]').forEach(function(el){el.value='';});loadAddresses();}
      catch(e){toast(e.message,false);} finally{setBusy(save,false);}
    });
  }

  async function loadOrders(){
    var tbody=document.querySelector('[data-orders-body]');if(!tbody)return;
    try{
      var data=await api('orders.php');
      tbody.innerHTML='';
      if(!data.orders.length){tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:30px;">No orders found.</td></tr>';return;}
      data.orders.forEach(function(o){
        var tr=document.createElement('tr');
        tr.innerHTML='<td><strong>#'+escapeHtml(o.orderNumber)+'</strong></td><td>'+new Date(o.createdAt.replace(' ','T')).toLocaleDateString()+'</td><td>—</td><td><strong>₹'+Number(o.totalAmount).toLocaleString('en-IN')+'</strong></td><td><span class="ci-status">'+escapeHtml(o.status)+'</span></td><td><a href="order-details?orderNumber='+encodeURIComponent(o.orderNumber)+'" class="ci-table-action-btn"><i class="fa-solid fa-eye"></i></a></td>';
        tbody.appendChild(tr);
      });
      var showing=document.querySelector('[data-orders-count]');if(showing)showing.textContent='Showing '+data.orders.length+' of '+data.total;
    }catch(e){toast(e.message,false);}
  }

  async function loadDashboard(){
    var box=document.querySelector('[data-dashboard-stats]');if(!box)return;
    try{
      var data=await api('dashboard.php');
      Object.keys(data.stats).forEach(function(k){var el=box.querySelector('[data-stat="'+k+'"]');if(el)el.textContent=k==='totalSpent'?'₹'+Number(data.stats[k]).toLocaleString('en-IN'):data.stats[k];});
    }catch(e){toast(e.message,false);}
  }

  async function init(){
    if(!token())return;
    if(document.querySelector('[data-user-field="firstName"]')){try{await profile();}catch(e){toast(e.message,false);}bindProfile();}
    bindSettings();
    if(document.querySelector('[data-address-list]')){loadAddresses();bindAddressForm();}
    if(document.querySelector('[data-orders-body]'))loadOrders();
    if(document.querySelector('[data-dashboard-stats]'))loadDashboard();
  }

  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
