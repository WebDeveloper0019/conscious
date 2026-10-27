<?php
$pageTitle = 'Recover administrator access';
$authStep = 'Account recovery · Security reviewed';
require __DIR__ . '/../layouts/auth-head.php';
?>
<h2>Recover access</h2>
<p class="ac-auth-intro">Enter the assigned work email. The interface always shows the same result so it does not reveal whether an account exists.</p>
<form class="ac-auth-form" data-admin-form="admin-recovery">
    <div class="ac-field">
        <label for="recovery-email">Work email *</label>
        <input class="ac-input" id="recovery-email" name="email" type="email" autocomplete="email" inputmode="email" maxlength="254" required>
    </div>
    <button class="ac-btn primary ac-auth-submit" type="submit">Request secure recovery link</button>
</form>
<div class="ac-auth-links"><a href="login.php">Return to sign in</a></div>
<div class="ac-callout warning mt-3">Recovery links should be single-use, time-limited and followed by MFA. Privileged-account recovery must generate an audit alert.</div>
<?php require __DIR__ . '/../layouts/auth-foot.php'; ?>
