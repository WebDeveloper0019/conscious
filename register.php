<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Join Conscious Indi free. Shop handcrafted products from verified Indian artisans and sustainable brands." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Create Account — Conscious Indi" />
  <meta property="og:description" content="Join Conscious Indi free. Shop handcrafted products from verified Indian artisans and sustainable brands." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Create Account — Conscious Indi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
</head>
<body>

<!-- PRELOADER -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious Indi">
  <img src="assets/images/symbol.png" alt="Conscious Indi" class="bc-preloader-symbol" width="90" height="90" aria-hidden="true" />
  <div class="bc-preloader-brand">Conscious Indi</div>
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

<section class="section-py" style="background:var(--bg-cream);" aria-label="Register">
  <div class="container">
    <div class="bc-auth-card bc-reveal">
      <div style="text-align:center;margin-bottom:28px;">
        <a href="/conscious/"><img src="assets/images/symbol.png" alt="Conscious Indi" width="60" height="60" style="margin-bottom:12px;" /></a>
        <h1 style="font-family:var(--font-display);font-size:1.625rem;font-weight:800;color:var(--text-primary);margin-bottom:6px;">Create Your Account</h1>
        <p style="font-size:0.9375rem;color:var(--text-muted);">Join 2M+ conscious shoppers on Conscious Indi</p>
      </div>
    <form id="ci-register-form" novalidate>
  <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px;">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div>
        <label class="bc-auth-label" for="ci-register-first">First Name *</label>
        <input id="ci-register-first" name="first_name" type="text" class="bc-auth-input" placeholder="Priya" required minlength="2" maxlength="60" />
      </div>

      <div>
        <label class="bc-auth-label" for="ci-register-last">Last Name *</label>
        <input id="ci-register-last" name="last_name" type="text" class="bc-auth-input" placeholder="Sharma" required minlength="2" maxlength="60" />
      </div>
    </div>

    <div>
      <label class="bc-auth-label" for="ci-register-email">Email Address *</label>
      <input id="ci-register-email" name="email" type="email" class="bc-auth-input" placeholder="you@email.com" required maxlength="254" />
    </div>

    <div>
      <label class="bc-auth-label" for="ci-register-phone">Mobile Number *</label>
      <input id="ci-register-phone" name="phone" type="tel" class="bc-auth-input" placeholder="98765 43210" required maxlength="10" inputmode="numeric" />
    </div>

    <div>
      <label class="bc-auth-label" for="ci-register-password">Password *</label>
      <input id="ci-register-password" name="password" type="password" class="bc-auth-input" placeholder="Create a strong password" required minlength="12" maxlength="128" />
    </div>

  </div>

 <div id="ci-register-message" role="alert" aria-live="polite" style="margin-bottom:12px;"></div>

<button type="submit" class="bc-btn bc-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:14px;">
    <i class="fa-solid fa-user-plus"></i> Create Account
</button>

<div style="text-align:center;margin-top:14px;font-size:0.875rem;color:var(--text-secondary);white-space:nowrap;">
    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
        <input
            name="terms"
            type="checkbox"
            required
            style="accent-color:var(--saffron);margin:0;"
        />
        <span>
            I agree to Conscious Indi's
            <a href="/conscious/terms" style="color:var(--saffron-dark);">Terms of Use</a>
            and
            <a href="/conscious/privacy" style="color:var(--saffron-dark);">Privacy Policy</a>
        </span>
    </label>
</div>

</form>

<div style="text-align:center;margin-top:22px;font-size:0.9375rem;color:var(--text-muted);">
    Already have an account?
    <a href="frontend/modules/customer/auth/login.php" style="color:var(--saffron-dark);font-weight:600;">Sign in</a>
</div>
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
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ci-register-form');
    if (!form) return;

    const msg = document.getElementById('ci-register-message');
    const button = form.querySelector('button[type="submit"]');

    function show(text, ok = false) {
        if (msg) {
            msg.textContent = text;
            msg.style.color = ok ? '#287a46' : '#b42318';
        }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const first_name = form.querySelector('[name="first_name"]')?.value.trim() || '';
        const last_name = form.querySelector('[name="last_name"]')?.value.trim() || '';
        const email = form.querySelector('[name="email"]')?.value.trim() || '';
        let phone = form.querySelector('[name="phone"]')?.value || '';
        const password = form.querySelector('[name="password"]')?.value || '';
        const terms = !!form.querySelector('[name="terms"]')?.checked;

        phone = phone.replace(/\D/g, '');

        if (!first_name || !last_name || !email || !phone || !password) {
            show('Please fill in all required fields correctly.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            show('Please enter a valid email address.');
            return;
        }

        if (!/^[6-9]\d{9}$/.test(phone)) {
            show('Enter a valid 10-digit Indian mobile number.');
            return;
        }

        if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{12,}/.test(password)) {
            show('Password must be 12+ characters with upper, lower, number and symbol.');
            return;
        }


        if (!terms) {
            show('Please accept the Terms and Privacy Policy.');
            return;
        }

        if (button) button.disabled = true;

        show('Creating your account...', true);

        try {
            const response = await fetch('./api/auth/register.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    first_name: first_name,
                    last_name: last_name,
                    email: email,
                    phone: phone,
                    password: password,

                    marketing: false,
                    terms: true
                })
            });

            const raw = await response.text();

            let data = {};
            try {
                data = JSON.parse(raw);
            } catch (_) {}

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    raw ||
                    'Registration failed. Please try again.'
                );
            }

            sessionStorage.setItem(
                'ci_pending_email',
                data.email || email
            );

            show(
                data.message || 'Account created. Verify your email.',
                true
            );

            setTimeout(function () {
                window.location.href =
                    'frontend/modules/customer/auth/verify-email.php';
            }, 700);

        } catch (err) {
            show(
                err.message || 'Registration failed. Please try again.'
            );
        } finally {
            if (button) button.disabled = false;
        }
    });
});
</script>
</body>
</html>
