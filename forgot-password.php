<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Reset your Conscious India account password securely." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Forgot Password — Conscious India" />
  <meta property="og:description" content="Reset your Conscious India account password securely." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Forgot Password — Conscious India</title>
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

<section class="section-py" style="background:var(--bg-cream);" aria-label="Forgot password">
  <div class="container">
    <div class="bc-auth-card bc-reveal">
      <div style="text-align:center;margin-bottom:28px;">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--saffron-pale);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fa-solid fa-lock-open" style="font-size:1.75rem;color:var(--saffron-dark);"></i></div>
        <h1 style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:6px;">Forgot Your Password?</h1>
        <p style="font-size:0.9375rem;color:var(--text-muted);">No worries. Enter your email and we'll send a reset link.</p>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:20px;">
        <div><label class="bc-auth-label">Registered Email Address</label><input type="email" class="bc-auth-input" placeholder="you@email.com" /></div>
      </div>
      <button class="bc-btn bc-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:14px;"><i class="fa-solid fa-paper-plane"></i> Send Reset Link</button>
      <div style="text-align:center;margin-top:20px;font-size:0.9375rem;color:var(--text-muted);">Remember it? <a href="portals/modules/customer/auth/login.php" style="color:var(--saffron-dark);font-weight:600;">Back to Sign In</a></div>
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
</body>
</html>