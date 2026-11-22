<div class="vv-auth-heading vv-register-heading">
  <span class="vv-eyebrow" data-register-eyebrow>Partner registration</span>
  <h2 data-register-heading>Create your seller account</h2>
  <p data-register-intro>Create your account in two steps.</p>
</div>

<form class="vv-auth-form vv-register-form" data-vendor-register data-inline-managed novalidate>
  <div class="vv-form-message" data-form-message role="alert" tabindex="-1" hidden></div>
  <ol class="vv-step-progress vv-visually-hidden" aria-label="Registration progress">
    <li class="active" data-progress-step="1" aria-current="step"><span>1</span> Account</li>
    <li data-progress-step="2"><span>2</span> Finish</li>
  </ol>

  <section class="vv-register-step" data-register-step="1" aria-label="Account details">
    <fieldset class="vv-type-picker">
      <legend class="vv-visually-hidden">Choose account type</legend>
      <label>
        <input type="radio" name="registration_type" value="artisan" required>
        <span class="vv-type-icon"><i class="fa-solid fa-hands" aria-hidden="true"></i></span>
        <span><b>Artisan</b></span>
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
      </label>
      <label>
        <input type="radio" name="registration_type" value="brand" required>
        <span class="vv-type-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
        <span><b>Business</b></span>
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
      </label>
      <label>
        <input type="radio" name="registration_type" value="influencer" required>
        <span class="vv-type-icon"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>
        <span><b>Creator</b></span>
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
      </label>
    </fieldset>
    <small class="vv-error" data-error-for="registration_type" hidden></small>

    <div class="vv-register-fields">
      <div class="vc-field">
        <label for="vendor-owner-name">Full legal name</label>
        <div class="vv-input-wrap"><i class="fa-regular fa-user" aria-hidden="true"></i><input class="vc-input" id="vendor-owner-name" name="owner_name" required minlength="2" maxlength="80" autocomplete="name" placeholder="As shown on your ID"></div>
        <small class="vv-error" data-error-for="owner_name" hidden></small>
      </div>
      <div class="vc-field">
        <label for="vendor-register-phone">Mobile number</label>
        <div class="vv-input-wrap vv-phone"><span>+91</span><input class="vc-input" id="vendor-register-phone" name="phone" type="tel" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="9876543210"></div>
        <small class="vv-error" data-error-for="phone" hidden></small>
      </div>
      <div class="vc-field full">
        <label for="vendor-register-email">Email address</label>
        <div class="vv-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input class="vc-input" id="vendor-register-email" name="email" type="email" required maxlength="190" autocomplete="email" placeholder="you@example.com"></div>
        <small class="vv-error" data-error-for="email" hidden></small>
      </div>
      <div class="vc-field">
        <label for="vendor-register-password">Password</label>
        <div class="vv-input-wrap"><i class="fa-solid fa-key" aria-hidden="true"></i><input class="vc-input" id="vendor-register-password" name="password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Create a strong password"><button type="button" data-password-toggle="vendor-register-password" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>
        <small class="vv-error" data-error-for="password" hidden></small>
      </div>
    </div>
    <p class="vv-password-summary"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> 12+ characters with upper and lowercase, a number, and a symbol.</p>
    <button class="vc-btn primary vv-submit" type="button" data-register-next>Continue <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
  </section>

  <section class="vv-register-step" data-register-step="2" aria-label="Finish account setup" hidden>
    <div class="vv-step-two-intro"><span class="vv-step-two-icon"><i class="fa-solid fa-store" data-step-two-icon aria-hidden="true"></i></span><div><h3 data-step-two-title>Your public name</h3><p data-step-two-description>Choose the name customers will see.</p></div></div>
    <div class="vc-field"><label for="vendor-register-confirm">Confirm password</label><div class="vv-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input class="vc-input" id="vendor-register-confirm" name="confirm_password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Enter your password again"><button type="button" data-password-toggle="vendor-register-confirm" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div><small class="vv-error" data-error-for="confirm_password" hidden></small></div>
    <div class="vc-field" data-vendor-only>
      <label for="vendor-display-name"><span data-name-label>Store or public name</span></label>
      <div class="vv-input-wrap"><i class="fa-solid fa-store" aria-hidden="true"></i><input class="vc-input" id="vendor-display-name" name="display_name" required minlength="2" maxlength="100" placeholder="e.g. Earth &amp; Loom"></div>
      <small class="vv-error" data-error-for="display_name" hidden></small>
    </div>
    <div class="vc-field" data-creator-only hidden>
      <label for="creator-handle">Creator handle</label>
      <div class="vv-input-wrap"><i class="fa-solid fa-at" aria-hidden="true"></i><input class="vc-input" id="creator-handle" name="handle" minlength="3" maxlength="100" pattern="[A-Za-z0-9._-]{3,100}" autocomplete="off" placeholder="e.g. mindfulmaker" disabled></div>
      <small class="vv-error" data-error-for="handle" hidden></small>
      <small class="vv-field-hint">Letters, numbers, dots, underscores or hyphens.</small>
    </div>
    <label class="vv-checkbox vv-terms"><input type="checkbox" name="terms" required><span><span data-terms-copy>I agree to the</span> <a href="/conscious/terms" target="_blank" rel="noopener">Terms</a> and <a href="/conscious/privacy" target="_blank" rel="noopener">Privacy Policy</a><span data-creator-only hidden>, and will disclose paid partnerships</span>.</span></label>
    <div class="vv-step-actions"><button class="vc-btn vv-back" type="button" data-register-back><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back</button><button class="vc-btn primary vv-submit" type="submit"><span data-submit-copy>Create account</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div>
  </section>
</form>
<p class="vv-auth-link">Already registered? <a href="/conscious/partner/login" data-register-login>Sign in</a></p>
