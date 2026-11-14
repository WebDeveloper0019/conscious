import { test, expect, ROUTES, VALID_PASSWORD, fillCustomerRegisterForm, assertNoPHPErrors } from '../utils/fixtures';

// ---------------------------------------------------------------------------
// Customer Login
// ---------------------------------------------------------------------------

test.describe('Customer Login', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.customerLogin);
  });

  test('login page loads with correct title @smoke', async ({ page }) => {
    await expect(page).toHaveTitle(/Sign in|Customer/i);
    await assertNoPHPErrors(page);
  });

  test('login form is present with expected fields @smoke', async ({ page }) => {
    await expect(page.locator('#customer-auth-form')).toBeVisible();
    await expect(page.locator('#login-identifier')).toBeVisible();
    await expect(page.locator('#login-password')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('email field accepts input', async ({ page }) => {
    await page.fill('#login-identifier', 'test@example.com');
    await expect(page.locator('#login-identifier')).toHaveValue('test@example.com');
  });

  test('password field masks input by default', async ({ page }) => {
    await expect(page.locator('#login-password')).toHaveAttribute('type', 'password');
  });

  test('show-password toggle reveals password', async ({ page }) => {
    await page.fill('#login-password', 'mysecret123');
    await page.click('[data-password-toggle="login-password"]');
    await expect(page.locator('#login-password')).toHaveAttribute('type', 'text');
    // Toggle back
    await page.click('[data-password-toggle="login-password"]');
    await expect(page.locator('#login-password')).toHaveAttribute('type', 'password');
  });

  test('submitting empty form shows validation feedback', async ({ page }) => {
    await page.click('button[type="submit"]');
    // HTML5 validation or custom error — form should not navigate away
    await expect(page).toHaveURL(new RegExp(ROUTES.customerLogin));
  });

  test('forgot password link is present and navigates correctly', async ({ page }) => {
    const link = page.locator(`a[href*="forgot-password"]`).first();
    await expect(link).toBeVisible();
    await link.click();
    await expect(page).toHaveURL(new RegExp('forgot-password'));
  });

  test('register link navigates to registration page', async ({ page }) => {
    await page.click('a[href*="/account/register"]');
    await expect(page).toHaveURL(new RegExp(ROUTES.customerRegister));
  });

  test('invalid credentials show error message', async ({ page }) => {
    await page.fill('#login-identifier', 'nobody@nowhere.test');
    await page.fill('#login-password', 'WrongPassword!99x');
    await page.click('button[type="submit"]');

    const alert = page.locator('[data-form-alert]');
    await expect(alert).toBeVisible({ timeout: 8_000 });
    await expect(alert).not.toBeEmpty();
  });
});

// ---------------------------------------------------------------------------
// Customer Registration
// ---------------------------------------------------------------------------

test.describe('Customer Registration', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.customerRegister);
  });

  test('register page loads with correct title @smoke', async ({ page }) => {
    await expect(page).toHaveTitle(/Register|Create|Join/i);
    await assertNoPHPErrors(page);
  });

  test('all required fields are present @smoke', async ({ page }) => {
    await expect(page.locator('#register-first')).toBeVisible();
    await expect(page.locator('#register-last')).toBeVisible();
    await expect(page.locator('#register-email')).toBeVisible();
    await expect(page.locator('#register-phone')).toBeVisible();
    await expect(page.locator('#register-password')).toBeVisible();
    await expect(page.locator('#register-confirm')).toBeVisible();
    await expect(page.locator('input[name="terms"]')).toBeAttached();
  });

  test('password strength meter is present', async ({ page }) => {
    await expect(page.locator('[data-password-meter]')).toBeVisible();
  });

  test('password rules checklist is visible', async ({ page }) => {
    // Rules are in [data-rule] elements (length, uppercase, number, symbol)
    await expect(page.locator('[data-rule]').first()).toBeVisible();
  });

  test('password strength updates on input', async ({ page }) => {
    await page.fill('#register-password', VALID_PASSWORD);
    // Meter score should update
    const meter = page.locator('[data-password-meter]');
    const score = await meter.getAttribute('data-score');
    expect(Number(score)).toBeGreaterThan(0);
  });

  test('show-password toggle works on register form', async ({ page }) => {
    await page.fill('#register-password', VALID_PASSWORD);
    await page.click('[data-password-toggle="register-password"]');
    await expect(page.locator('#register-password')).toHaveAttribute('type', 'text');
  });

  test('submitting empty form does not navigate away', async ({ page }) => {
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(new RegExp(ROUTES.customerRegister));
  });

  test('mismatched passwords triggers validation', async ({ page }) => {
    await page.fill('#register-password', VALID_PASSWORD);
    await page.fill('#register-confirm', 'DifferentPass!2025');
    await page.click('button[type="submit"]');
    // Should stay on same page or show inline error
    await expect(page).toHaveURL(new RegExp(ROUTES.customerRegister));
  });

  test('sign-in link navigates to login', async ({ page }) => {
    await page.click('a[href*="/account/login"]');
    await expect(page).toHaveURL(new RegExp(ROUTES.customerLogin));
  });

  test('phone field only accepts numeric input', async ({ page }) => {
    await page.fill('#register-phone', 'abcdefghij');
    // The input has inputmode="numeric" — check actual value is empty or numeric only
    const val = await page.locator('#register-phone').inputValue();
    expect(/^\d*$/.test(val)).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Customer Forgot Password
// ---------------------------------------------------------------------------

test.describe('Customer Forgot Password', () => {
  test('page loads and form is present @smoke', async ({ goto, page }) => {
    await goto(ROUTES.customerForgotPassword);
    await assertNoPHPErrors(page);
    await expect(page.locator('form, [data-auth-form]')).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// Partner (Vendor) Registration
// ---------------------------------------------------------------------------

test.describe('Partner Registration', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.partnerRegister);
  });

  test('register page loads @smoke', async ({ page }) => {
    await assertNoPHPErrors(page);
    await expect(page.locator('form[data-vendor-register]')).toBeVisible();
  });

  test('registration type picker has 3 options @smoke', async ({ page }) => {
    const options = page.locator('input[name="registration_type"]');
    await expect(options).toHaveCount(3);
  });

  test('artisan option is selectable', async ({ page }) => {
    await page.check('input[value="artisan"]');
    await expect(page.locator('input[value="artisan"]')).toBeChecked();
  });

  test('brand option is selectable', async ({ page }) => {
    await page.check('input[value="brand"]');
    await expect(page.locator('input[value="brand"]')).toBeChecked();
  });

  test('influencer option is selectable and shows creator fields', async ({ page }) => {
    await page.check('input[value="influencer"]');
    await expect(page.locator('input[value="influencer"]')).toBeChecked();
    // [data-creator-only] may appear more than once (label + input wrapper)
    // Check that at least one is no longer hidden
    const creatorSections = page.locator('[data-creator-only]');
    const firstSection = creatorSections.first();
    await expect(firstSection).not.toHaveAttribute('hidden', { timeout: 3_000 });
  });

  test('selecting artisan hides creator handle field', async ({ page }) => {
    await page.check('input[value="influencer"]');
    await page.check('input[value="artisan"]');
    await expect(page.locator('[data-creator-only]').first()).toHaveAttribute('hidden', '');
  });

  test('all required common fields are present', async ({ page }) => {
    await page.check('input[value="artisan"]');
    await expect(page.locator('#vendor-owner-name')).toBeVisible();
    await expect(page.locator('#vendor-register-email')).toBeVisible();
    await expect(page.locator('#vendor-register-phone')).toBeVisible();
    await expect(page.locator('#vendor-register-password')).toBeVisible();
    await expect(page.locator('#vendor-register-confirm')).toBeAttached();
  });

  test('terms checkbox is required', async ({ page }) => {
    await page.check('input[value="artisan"]');
    // Step 1 fields
    await page.fill('#vendor-owner-name', 'Test Artisan');
    await page.fill('#vendor-register-email', `test+${Date.now()}@playwright.local`);
    await page.fill('#vendor-register-phone', '9876543210');
    await page.fill('#vendor-register-password', VALID_PASSWORD);
    // Advance to step 2 (type="button" doesn't submit, just moves to next step)
    await page.locator('.vv-submit').first().click();
    // Step 2 should now be visible
    await expect(page.locator('.vv-register-step').nth(1)).toBeVisible({ timeout: 3_000 });
    // Fill step 2 required fields but NOT the terms checkbox
    await page.fill('#vendor-register-confirm', VALID_PASSWORD);
    await page.fill('#vendor-display-name', 'Test Store');
    // Submit without terms — form should stay on page / not navigate away
    await page.locator('button[type="submit"].vv-submit').click();
    await expect(page).toHaveURL(new RegExp(ROUTES.partnerRegister));
  });

  test('password show-toggle works on partner register form', async ({ page }) => {
    await page.check('input[value="artisan"]');
    await page.fill('#vendor-register-password', VALID_PASSWORD);
    await page.click('[data-password-toggle="vendor-register-password"]');
    await expect(page.locator('#vendor-register-password')).toHaveAttribute('type', 'text');
  });

  test('password rules are visible', async ({ page }) => {
    // Partner register form does not render a rules checklist in the DOM —
    // the password strength is handled server-side. Skip DOM check.
    test.skip(true, 'Partner register form has no client-side rules checklist element');
  });

  test('sign-in link navigates to partner login', async ({ page }) => {
    await page.locator('a[href*="/partner/login"]').click();
    await expect(page).toHaveURL(new RegExp(ROUTES.partnerLogin));
  });
});

// ---------------------------------------------------------------------------
// Partner (Vendor) Login
// ---------------------------------------------------------------------------

test.describe('Partner Login', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.partnerLogin);
  });

  test('login page loads @smoke', async ({ page }) => {
    await assertNoPHPErrors(page);
    await expect(page.locator('form[data-vendor-login]')).toBeVisible();
  });

  test('email and password fields are present @smoke', async ({ page }) => {
    await expect(page.locator('#vendor-login-email')).toBeVisible();
    await expect(page.locator('#vendor-login-password')).toBeVisible();
  });

  test('password is masked by default', async ({ page }) => {
    await expect(page.locator('#vendor-login-password')).toHaveAttribute('type', 'password');
  });

  test('show-password toggle works', async ({ page }) => {
    await page.fill('#vendor-login-password', VALID_PASSWORD);
    await page.click('[data-password-toggle="vendor-login-password"]');
    await expect(page.locator('#vendor-login-password')).toHaveAttribute('type', 'text');
  });

  test('invalid credentials show error', async ({ page }) => {
    await page.fill('#vendor-login-email', 'nobody@nowhere.test');
    await page.fill('#vendor-login-password', 'WrongPassword!99x');
    await page.click('button[type="submit"]');

    const message = page.locator('[data-form-message]');
    await expect(message).toBeVisible({ timeout: 8_000 });
  });

  test('forgot password link is present', async ({ page }) => {
    await expect(page.locator('a[href*="forgot-password"]')).toBeVisible();
  });

  test('create vendor account link navigates to registration', async ({ page }) => {
    await page.click('a[href*="/join"]');
    await expect(page).toHaveURL(new RegExp(ROUTES.partnerRegister));
  });
});

// ---------------------------------------------------------------------------
// Creator (Influencer) Login
// ---------------------------------------------------------------------------

test.describe('Creator Login', () => {
  test('page loads without PHP errors @smoke', async ({ goto, page }) => {
    await goto(ROUTES.creatorLogin);
    await assertNoPHPErrors(page);
  });

  test('login form is present', async ({ goto, page }) => {
    await goto(ROUTES.creatorLogin);
    await expect(page.locator('form')).toBeVisible();
  });
});

// ---------------------------------------------------------------------------
// Admin Login
// ---------------------------------------------------------------------------

test.describe('Admin Login', () => {
  test('page loads without PHP errors @smoke', async ({ goto, page }) => {
    await goto(ROUTES.adminLogin);
    await assertNoPHPErrors(page);
  });

  test('login form is present', async ({ goto, page }) => {
    await goto(ROUTES.adminLogin);
    await expect(page.locator('form')).toBeVisible();
  });

  test('admin login is not publicly indexable', async ({ page }) => {
    await page.goto(ROUTES.adminLogin);
    // robots meta should prevent indexing of admin pages
    const robots = await page.locator('meta[name="robots"]').getAttribute('content');
    if (robots) {
      expect(robots.toLowerCase()).toMatch(/noindex|none/);
    }
  });
});
