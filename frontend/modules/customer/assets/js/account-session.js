(function(){
    'use strict';
    var demo=null; try{demo=JSON.parse(sessionStorage.getItem('ci_demo_customer')||localStorage.getItem('ci_demo_customer')||'null');}catch(_){demo=null;}
    function fullName(user){return [user.first_name||user.firstName,user.last_name||user.lastName].filter(Boolean).join(' ')||user.email||'Customer';}
    function initials(user){return fullName(user).split(/\s+/).slice(0,2).map(function(part){return part.charAt(0);}).join('').toUpperCase();}
    function render(user){var name=fullName(user),first=(user.first_name||user.firstName||'Customer');document.querySelectorAll('.cc-user b').forEach(function(el){el.textContent=name;});document.querySelectorAll('.cc-avatar').forEach(function(el){if(/^[A-Z]{1,3}$/.test(el.textContent.trim()))el.textContent=initials(user);});document.querySelectorAll('h2').forEach(function(el){el.childNodes.forEach(function(node){if(node.nodeType===3&&/Rahul/.test(node.nodeValue))node.nodeValue=node.nodeValue.replace(/Rahul/g,first);});});}
    async function current(){var response=await fetch('../../../api/auth/me.php',{credentials:'include',headers:{'Accept':'application/json'},cache:'no-store'});var data=await response.json().catch(function(){return{};});if(!response.ok||!data.user)throw new Error('unauthenticated');if(data.csrf_token)sessionStorage.setItem('ci_csrf_token',data.csrf_token);return data.user;}
    async function logout(event){event.preventDefault();var csrf=sessionStorage.getItem('ci_csrf_token')||'';try{await fetch('../../../api/auth/logout.php',{method:'POST',credentials:'include',headers:{'Accept':'application/json','X-CSRF-Token':csrf}});}finally{sessionStorage.removeItem('ci_csrf_token');sessionStorage.removeItem('ci_demo_customer');localStorage.removeItem('ci_demo_customer');location.replace('auth/login.php');}}
    document.querySelectorAll('a').forEach(function(link){if(/sign out/i.test(link.textContent||''))link.addEventListener('click',logout);});
    if(demo){render(demo);return;}
    current().then(render).catch(function(){location.replace('auth/login.php');});
})();
