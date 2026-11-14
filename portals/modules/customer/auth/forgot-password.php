<?php
$pageTitle='Recover customer account';
$authPage='forgot';
require __DIR__.'/../layouts/auth-head-v2.php';
?>
<article class="ca-card" data-auth-card>
  <header class="ca-card-head"><span class="ca-card-icon"><i class="fa-solid fa-key" aria-hidden="true"></i></span><div><p class="ca-overline">Secure account recovery</p><h2>Reset your password</h2></div></header>
  <p class="ca-card-copy">Enter the email used for your account. We will send a secure reset link if it matches an account.</p>
  <form id="customer-auth-form" class="ca-auth-form" data-password-flow="forgot" novalidate>
    <div class="ca-form-alert" role="alert" aria-live="assertive" tabindex="-1" hidden data-form-alert></div>
    <div class="cc-field ca-field"><label for="recover-email">Email address</label><div class="ca-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input class="cc-input" id="recover-email" name="email" type="email" required maxlength="190" autocomplete="email" placeholder="you@example.com"></div><small class="ca-field-error" data-error-for="email"></small></div>
    <button class="cc-btn primary ca-submit" type="submit" data-submit-button><span>Send reset link</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
  </form>
  <p class="ca-switch"><a href="/conscious/account/login">Back to sign in</a></p>
</article>
<script src="/conscious/account/assets/js/password-flow.js?v=20261114" defer></script>
<?php require __DIR__.'/../layouts/auth-foot-v2.php'; ?>
