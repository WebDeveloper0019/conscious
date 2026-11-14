<?php
$pageTitle='Set a new password';
$authPage='reset';
require __DIR__.'/../layouts/auth-head-v2.php';
?>
<article class="ca-card" data-auth-card>
  <header class="ca-card-head"><span class="ca-card-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></span><div><p class="ca-overline">Secure account recovery</p><h2>Choose a new password</h2></div></header>
  <p class="ca-card-copy">Use at least 12 characters, including uppercase, lowercase, a number and a symbol.</p>
  <form id="customer-auth-form" class="ca-auth-form" data-password-flow="reset" novalidate>
    <div class="ca-form-alert" role="alert" aria-live="assertive" tabindex="-1" hidden data-form-alert></div>
    <div class="cc-field ca-field"><label for="new-password">New password</label><div class="ca-input-wrap"><i class="fa-solid fa-key" aria-hidden="true"></i><input class="cc-input" id="new-password" name="password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Create a strong password"></div><small class="ca-field-error" data-error-for="password"></small></div>
    <div class="cc-field ca-field"><label for="confirm-password">Confirm password</label><div class="ca-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input class="cc-input" id="confirm-password" name="password_confirmation" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Re-enter your new password"></div><small class="ca-field-error" data-error-for="password_confirmation"></small></div>
    <button class="cc-btn primary ca-submit" type="submit" data-submit-button><span>Save new password</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
  </form>
  <p class="ca-switch"><a href="/conscious/account/login">Back to sign in</a></p>
</article>
<script src="/conscious/account/assets/js/password-flow.js?v=20261114" defer></script>
<?php require __DIR__.'/../layouts/auth-foot-v2.php'; ?>
