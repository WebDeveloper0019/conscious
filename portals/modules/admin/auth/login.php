<?php
$pageTitle = 'Administrator sign in';
$authStep = 'Step 1 of 2 · Identity verification';
require __DIR__ . '/../layouts/auth-head.php';
?>
<h2>Administrator sign in</h2>
<p class="ac-auth-intro">Use your assigned work account. Successful password validation must be followed by MFA.</p>
<form class="ac-auth-form" data-admin-form="admin-login" data-auth-next="verify.php">
    <div class="ac-field">
        <label for="admin-email">Work email *</label>
        <input class="ac-input" id="admin-email" name="email" type="email" autocomplete="username" inputmode="email" maxlength="254" required aria-describedby="email-help">
        <small id="email-help">Personal email addresses must not be used.</small>
    </div>
    <div class="ac-field">
        <label for="admin-password">Password *</label>
        <div class="ac-password-wrap">
            <input class="ac-input" id="admin-password" name="password" type="password" autocomplete="current-password" minlength="12" maxlength="128" required>
            <button class="ac-password-toggle" type="button" data-password-toggle="admin-password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
        </div>
        <small>Minimum 12 characters. Credentials are sent only to the secure sign-in endpoint.</small>
    </div>
    <label class="ac-check"><input type="checkbox" name="remember_device" value="yes"> Trust this device only after secure device verification</label>
    <button class="ac-btn primary ac-auth-submit" type="submit">Continue securely <i class="fa-solid fa-arrow-right"></i></button>
</form>
<div class="ac-auth-links"><a href="forgot-password.php">Forgot password?</a><a href="../../../../">Return to website</a></div>
<div class="ac-callout warning mt-3"><strong>Security:</strong> repeated failed attempts should be rate-limited and reviewed by the backend security service.</div>
<?php require __DIR__ . '/../layouts/auth-foot.php'; ?>
