(function(){
    'use strict';
    var form=document.getElementById('verify-email-form'); if(!form)return;
    var email=form.elements.email, otp=form.elements.otp, alertBox=form.querySelector('[data-form-alert]'), submit=form.querySelector('[data-submit-button]'), resend=document.querySelector('[data-resend-otp]');
    email.value=sessionStorage.getItem('ci_pending_email')||'';
    otp.addEventListener('input',function(){otp.value=otp.value.replace(/\D/g,'').slice(0,6);});
    function message(text,error){alertBox.textContent=text;alertBox.hidden=false;alertBox.classList.toggle('success',!error);if(error)alertBox.focus();}
    async function request(path,body){var response=await fetch('../../../../api/auth/'+path,{method:'POST',credentials:'include',headers:{'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(body)});var data=await response.json().catch(function(){return{};});if(!response.ok)throw new Error(data.message||'Unable to complete this request.');return data;}
    form.addEventListener('submit',async function(event){event.preventDefault();if(!form.checkValidity()){form.reportValidity();return;}submit.disabled=true;try{var data=await request('verify-otp.php',{email:email.value.trim(),otp:otp.value});sessionStorage.removeItem('ci_pending_email');if(data.csrf_token)sessionStorage.setItem('ci_csrf_token',data.csrf_token);message(data.message||'Email verified.',false);setTimeout(function(){location.href=data.redirect||'../index.php';},650);}catch(error){message(error.message,true);}finally{submit.disabled=false;}});
    resend.addEventListener('click',async function(){if(!email.checkValidity()){email.reportValidity();return;}resend.disabled=true;try{var data=await request('resend-otp.php',{email:email.value.trim()});message(data.message,false);}catch(error){message(error.message,true);}finally{setTimeout(function(){resend.disabled=false;},60000);}});
})();
