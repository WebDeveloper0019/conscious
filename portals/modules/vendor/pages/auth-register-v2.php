<div class="vv-auth-heading">
  <span class="vv-eyebrow" data-register-eyebrow>Partner registration</span>
  <h2 data-register-heading>Choose how you join</h2>
  <p data-register-intro>Select your role. The fields below will change to match your application.</p>
</div>

<form class="vv-auth-form" data-vendor-register novalidate>
  <div class="vv-form-message" data-form-message role="alert" hidden></div>
  <fieldset class="vv-type-picker">
    <legend>I am registering as</legend>
    <label>
      <input type="radio" name="registration_type" value="artisan" required>
      <span class="vv-type-icon"><i class="fa-solid fa-hands" aria-hidden="true"></i></span>
      <span><b>Artisan or maker</b><small>Handmade products or artisan group</small><em>Craft details after signup</em></span>
      <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    </label>
    <label>
      <input type="radio" name="registration_type" value="brand" required>
      <span class="vv-type-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
      <span><b>Brand or business</b><small>Registered business or social enterprise</small><em>Business details after signup</em></span>
      <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    </label>
    <label>
      <input type="radio" name="registration_type" value="influencer" required>
      <span class="vv-type-icon"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>
      <span><b>Influencer or creator</b><small>Content and maker partnerships</small><em>Audience details after signup</em></span>
      <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    </label>
  </fieldset>
  <small class="vv-error" data-error-for="registration_type"></small>

  <div class="vv-register-fields">
    <div class="vc-field">
      <label for="vendor-owner-name">Your full legal name</label>
      <div class="vv-input-wrap"><i class="fa-regular fa-user" aria-hidden="true"></i><input class="vc-input" id="vendor-owner-name" name="owner_name" required minlength="2" maxlength="80" autocomplete="name" placeholder="As shown on your ID"></div>
      <small class="vv-error" data-error-for="owner_name"></small>
    </div>
    <div class="vc-field" data-vendor-only>
      <label for="vendor-display-name"><span data-name-label>Store or public name</span></label>
      <div class="vv-input-wrap"><i class="fa-solid fa-store" aria-hidden="true"></i><input class="vc-input" id="vendor-display-name" name="display_name" required minlength="2" maxlength="100" placeholder="Shown to customers"></div>
      <small class="vv-error" data-error-for="display_name"></small>
    </div>
    <div class="vc-field" data-creator-only hidden>
      <label for="creator-handle">Public creator handle</label>
      <div class="vv-input-wrap"><i class="fa-solid fa-at" aria-hidden="true"></i><input class="vc-input" id="creator-handle" name="handle" minlength="3" maxlength="100" pattern="[A-Za-z0-9._-]{3,100}" autocomplete="off" placeholder="e.g. mindfulmaker" disabled></div>
      <small class="vv-error" data-error-for="handle"></small>
      <small class="vv-field-hint">3–100 letters, numbers, dots, underscores or hyphens. You can complete your creator profile after verification.</small>
    </div>
    <div class="vc-field">
      <label for="vendor-register-email"><span data-email-label>Business email</span></label>
      <div class="vv-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input class="vc-input" id="vendor-register-email" name="email" type="email" required maxlength="190" autocomplete="email" placeholder="you@example.com"></div>
      <small class="vv-error" data-error-for="email"></small>
    </div>
    <div class="vc-field">
      <label for="vendor-register-phone">Indian mobile number</label>
      <div class="vv-input-wrap vv-phone"><span>+91</span><input class="vc-input" id="vendor-register-phone" name="phone" type="tel" required pattern="[6-9][0-9]{9}" maxlength="10" inputmode="numeric" autocomplete="tel-national" placeholder="9876543210"></div>
      <small class="vv-error" data-error-for="phone"></small>
    </div>
    <div class="vc-field">
      <label for="vendor-register-password">Create password</label>
      <div class="vv-input-wrap"><i class="fa-solid fa-key" aria-hidden="true"></i><input class="vc-input" id="vendor-register-password" name="password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Minimum 12 characters"><button type="button" data-password-toggle="vendor-register-password" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>
      <small class="vv-error" data-error-for="password"></small>
    </div>
    <div class="vc-field">
      <label for="vendor-register-confirm">Confirm password</label>
      <div class="vv-input-wrap"><i class="fa-solid fa-lock" aria-hidden="true"></i><input class="vc-input" id="vendor-register-confirm" name="confirm_password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" placeholder="Enter the same password"><button type="button" data-password-toggle="vendor-register-confirm" aria-label="Show password"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>
      <small class="vv-error" data-error-for="confirm_password"></small>
    </div>
  </div>

  <div class="vv-password-rules" data-password-rules><span data-rule="length"><i class="fa-solid fa-circle"></i> 12+ characters</span><span data-rule="upper"><i class="fa-solid fa-circle"></i> Uppercase</span><span data-rule="lower"><i class="fa-solid fa-circle"></i> Lowercase</span><span data-rule="number"><i class="fa-solid fa-circle"></i> Number</span><span data-rule="special"><i class="fa-solid fa-circle"></i> Special character</span></div>
  <label class="vv-checkbox"><input type="checkbox" name="terms" required><span data-terms-copy>I agree to the vendor application terms, privacy notice, prohibited-products rules and truthful-claims policy.</span></label>
  <label class="vv-checkbox optional" data-vendor-only><input type="checkbox" name="updates"><span>Send onboarding reminders and seller education updates.</span></label>
  <button class="vc-btn primary vv-submit" type="submit"><span data-submit-copy>Create account and start onboarding</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
</form>
<p class="vv-auth-link">Already registered? <a href="/conscious/partner/login" data-register-login>Sign in</a></p>
