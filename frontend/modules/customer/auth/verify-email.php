<?php
$pageTitle='Verify customer email'; $authPage='verify'; require __DIR__.'/../layouts/auth-head-v2.php';
?>
<article class="ca-card" data-auth-card>
<header class="ca-card-head"><span class="ca-card-icon"><i class="fa-solid fa-envelope-circle-check"></i></span><div><p class="ca-overline">Secure verification</p><h2>Check your email</h2></div></header>
<p class="ca-card-copy">Enter the six-digit code sent to your email. It remains valid for 10 minutes.</p>
<form id="verify-email-form" class="ca-auth-form" novalidate>
<div class="ca-form-alert" role="alert" aria-live="assertive" tabindex="-1" hidden data-form-alert></div>
<div class="cc-field ca-field"><label for="verify-email">Email address</label><div class="ca-input-wrap"><i class="fa-regular fa-envelope"></i><input class="cc-input" id="verify-email" name="email" type="email" required autocomplete="email" placeholder="you@example.com"></div></div>
<div class="cc-field ca-field"><label for="verify-otp">Six-digit code</label><div class="ca-input-wrap"><i class="fa-solid fa-shield-halved"></i><input class="cc-input" id="verify-otp" name="otp" type="text" required minlength="6" maxlength="6" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" placeholder="000000"></div></div>
<button class="cc-btn primary ca-submit" type="submit" data-submit-button><span>Verify and continue</span><i class="fa-solid fa-arrow-right"></i><i class="fa-solid fa-circle-notch fa-spin ca-spinner"></i></button>
</form>
<p class="ca-switch">Code not received? <button class="ca-text-btn" type="button" data-resend-otp>Send a new code</button></p>
<p class="ca-switch"><a href="login.php"><i class="fa-solid fa-arrow-left"></i> Back to sign in</a></p>
</article>
<script src="../assets/js/verify-email.js" defer></script>
<?php require __DIR__.'/../layouts/auth-foot-v2.php'; ?>
