<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Sign in to your Conscious India account to track orders, manage wishlist and shop." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Sign In — Conscious India" />
  <meta property="og:description" content="Sign in to your Conscious India account to track orders, manage wishlist and shop." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Sign In — Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
</head>
<body>

<!-- PRELOADER -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious India">
  <img src="assets/images/symbol.png" alt="Conscious India" class="bc-preloader-symbol" width="90" height="90" aria-hidden="true" />
  <div class="bc-preloader-brand">Conscious India</div>
  <div class="bc-preloader-chakra"></div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>
<main id="main-content">
<style>
.bc-auth-card{background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:40px;max-width:460px;margin:0 auto;box-shadow:var(--shadow-card);}
.bc-auth-input{width:100%;padding:12px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:1rem;background:var(--bg-cream);color:var(--text-primary);transition:border var(--t);}
.bc-auth-input:focus{outline:none;border-color:var(--saffron);}
.bc-auth-label{font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;}
.bc-auth-divider{display:flex;align-items:center;gap:12px;color:var(--text-muted);font-size:0.875rem;margin:20px 0;}
.bc-auth-divider::before,.bc-auth-divider::after{content:'';flex:1;height:1px;background:var(--border-warm);}
.bc-social-login-btn{width:100%;padding:11px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);background:white;display:flex;align-items:center;justify-content:center;gap:10px;font-size:0.9375rem;font-weight:500;color:var(--text-secondary);cursor:pointer;transition:all var(--t);}
.bc-social-login-btn:hover{border-color:var(--saffron);background:var(--saffron-pale);}</style>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Login">
  <div class="container">
    <div class="bc-auth-card bc-reveal">
      <div style="text-align:center;margin-bottom:28px;">
        <a href="/conscious/"><img src="assets/images/symbol.png" alt="Conscious India" width="60" height="60" style="margin-bottom:12px;" /></a>
        <h1 style="font-family:var(--font-display);font-size:1.625rem;font-weight:800;color:var(--text-primary);margin-bottom:6px;">Welcome Back</h1>
        <p style="font-size:0.9375rem;color:var(--text-muted);">Sign in to your Conscious India account</p>
      </div>
      <form id="ci-login-form" novalidate><div style="display:flex;flex-direction:column;gap:16px;margin-bottom:20px;">
        <div><label class="bc-auth-label" for="ci-login-identifier">Email Address or Mobile</label><input id="ci-login-identifier" name="identifier" type="text" class="bc-auth-input" placeholder="you@email.com or 9876543210" required /></div>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><label class="bc-auth-label" style="margin:0;">Password</label><a href="portals/modules/customer/auth/forgot-password.php" style="font-size:0.8125rem;color:var(--saffron-dark);">Forgot password?</a></div>
          <input id="ci-login-password" name="password" type="password" class="bc-auth-input" placeholder="••••••••" required minlength="12" maxlength="128" />
        </div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.875rem;color:var(--text-secondary);"><input id="ci-login-remember" name="remember" type="checkbox" style="accent-color:var(--saffron);" /> Keep me signed in</label>
      </div>
      <div id="ci-login-message" role="alert" aria-live="polite" style="margin-bottom:12px;"></div><button type="submit" class="bc-btn bc-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:14px;"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In</button></form>

      </div>
      <div style="text-align:center;font-size:0.9375rem;color:var(--text-muted);">Don't have an account? <a href="portals/modules/customer/auth/register.php" style="color:var(--saffron-dark);font-weight:600;">Create one free</a></div>
    </div>
  </div>
</section>
</main>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
<script>
(function(){
  const form=document.getElementById('ci-login-form'); if(!form)return;
  const msg=document.getElementById('ci-login-message');
  function show(text,ok){msg.textContent=text;msg.style.color=ok?'#287a46':'#b42318';}
  form.addEventListener('submit',async function(e){
    e.preventDefault();
    const d={identifier:form.elements.identifier.value.trim(),password:form.elements.password.value,remember:form.elements.remember.checked};
    const button=form.querySelector('button[type=submit]'); button.disabled=true; show('Signing in…',true);
    try{
      const r=await fetch('api/auth/login.php',{method:'POST',credentials:'include',headers:{'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(d)});
      const data=await r.json().catch(()=>({}));
      if(!r.ok)throw data;
      if(data.csrf_token)sessionStorage.setItem('ci_csrf_token',data.csrf_token);
      show(data.message||'Signed in successfully.',true);
      setTimeout(()=>location.href='/conscious/customer/dashboard',400);
    }catch(err){show(err.message||'Sign in failed. Please try again.',false);}
    finally{button.disabled=false;}
  });
})();
</script>
</body>
</html>