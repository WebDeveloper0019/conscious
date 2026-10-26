<?php
$pageTitle = 'Verify administrator access';
$authStep = 'Step 2 of 2 · Multi-factor authentication';
require __DIR__ . '/../layouts/auth-head.php';
?>
<h2>Confirm it is you</h2>
<p class="ac-auth-intro">Enter the six-digit code from your authenticator app. Never approve a request you did not initiate.</p>
<form class="ac-auth-form" data-admin-form="admin-mfa" data-auth-next="../index.php">
    <div class="ac-field">
        <label for="mfa-code">Authenticator code *</label>
        <input class="ac-input ac-otp" id="mfa-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="000000" required aria-describedby="mfa-help">
        <small id="mfa-help">Codes expire quickly and can be used only once.</small>
    </div>
    <button class="ac-btn primary ac-auth-submit" type="submit">Verify and open dashboard</button>
</form>
<div class="ac-auth-links"><button type="button" class="ac-link-button" data-demo-action="Request a new one-time code">Use another verification method</button><a href="login.php">Back to sign in</a></div>
<div class="ac-callout mt-3"><strong>Lost your authenticator?</strong> Use an unused recovery code or contact the security administrator for identity-checked recovery.</div>
<?php require __DIR__ . '/../layouts/auth-foot.php'; ?>
