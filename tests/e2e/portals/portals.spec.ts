import { test, expect, ROUTES, assertNoPHPErrors } from '../utils/fixtures';

// ---------------------------------------------------------------------------
// Customer portal — unauthenticated state
// These tests verify the portal behaves correctly for visitors who are not
// logged in: redirect to login, not a blank page or PHP error.
// ---------------------------------------------------------------------------

test.describe('Customer portal — unauthenticated', () => {
  const protectedRoutes = [
    { name: 'Dashboard', path: '/account' },
    { name: 'Orders', path: '/account/orders' },
    { name: 'Returns', path: '/account/returns' },
    { name: 'Rewards', path: '/account/rewards' },
    { name: 'Impact', path: '/account/impact' },
    { name: 'Wishlist', path: '/account/wishlist' },
    { name: 'Support', path: '/account/support' },
    { name: 'Communication', path: '/account/communication' },
  ] as const;

  for (const { name, path } of protectedRoutes) {
    test(`${name} redirects or shows login prompt @smoke`, async ({ page }) => {
      await page.goto(path);
      await assertNoPHPErrors(page);

      const url = page.url();
      // Acceptable outcomes: redirect to login, or stay on page with a login prompt
      const isLoginRedirect = url.includes('login') || url.includes('account/login');
      const hasLoginForm = await page.locator('form[data-auth-form], form[data-vendor-login]').isVisible();
      expect(isLoginRedirect || hasLoginForm).toBe(true);
    });
  }
});

// ---------------------------------------------------------------------------
// Partner portal — unauthenticated state
// ---------------------------------------------------------------------------

test.describe('Partner portal — unauthenticated', () => {
  const protectedRoutes = [
    { name: 'Dashboard', path: '/partner' },
    { name: 'Catalog', path: '/partner/catalog' },
    { name: 'Orders', path: '/partner/orders' },
    { name: 'Finance', path: '/partner/finance' },
    { name: 'Inventory', path: '/partner/inventory' },
    { name: 'Analytics', path: '/partner/analytics' },
    { name: 'Support', path: '/partner/support' },
    { name: 'Onboarding', path: '/partner/onboarding' },
  ] as const;

  for (const { name, path } of protectedRoutes) {
    test(`${name} redirects or shows login prompt @smoke`, async ({ page }) => {
      await page.goto(path);
      await assertNoPHPErrors(page);

      const url = page.url();
      const isLoginRedirect = url.includes('login');
      const hasLoginForm = await page.locator('form').isVisible();
      expect(isLoginRedirect || hasLoginForm).toBe(true);
    });
  }
});

// ---------------------------------------------------------------------------
// Admin portal — unauthenticated state
// ---------------------------------------------------------------------------

test.describe('Admin portal — unauthenticated', () => {
  const protectedRoutes = [
    { name: 'Dashboard', path: '/admin' },
    { name: 'Vendors', path: '/admin/vendors' },
    { name: 'Orders', path: '/admin/orders' },
    { name: 'Catalog', path: '/admin/catalog' },
    { name: 'Finance', path: '/admin/finance' },
    { name: 'Reports', path: '/admin/reports' },
    { name: 'Settings', path: '/admin/settings' },
  ] as const;

  for (const { name, path } of protectedRoutes) {
    test(`${name} redirects or shows login prompt @smoke`, async ({ page }) => {
      await page.goto(path);
      await assertNoPHPErrors(page);

      const url = page.url();
      const isLoginRedirect = url.includes('login');
      const hasLoginForm = await page.locator('form').isVisible();
      expect(isLoginRedirect || hasLoginForm).toBe(true);
    });
  }
});

// ---------------------------------------------------------------------------
// Creator portal — unauthenticated state
// ---------------------------------------------------------------------------

test.describe('Creator portal — unauthenticated', () => {
  test('creator dashboard redirects or shows login @smoke', async ({ page }) => {
    await page.goto('/creator');
    await assertNoPHPErrors(page);

    const url = page.url();
    const isLoginRedirect = url.includes('login');
    const hasLoginForm = await page.locator('form').isVisible();
    expect(isLoginRedirect || hasLoginForm).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Legacy redirect checks
// The .htaccess rewrites old /vendor, /customer, /influencer paths.
// ---------------------------------------------------------------------------

test.describe('Legacy URL redirects', () => {
  test('/vendor redirects to /partner', async ({ page }) => {
    const response = await page.goto('/vendor');
    // After redirect chain, URL should contain "partner"
    await page.waitForURL(/partner/, { timeout: 5_000 }).catch(() => {});
    expect(page.url()).toMatch(/partner/);
  });

  test('/customer redirects to /account', async ({ page }) => {
    await page.goto('/customer');
    await page.waitForURL(/account/, { timeout: 5_000 }).catch(() => {});
    expect(page.url()).toMatch(/account/);
  });

  test('/influencer redirects to /creator', async ({ page }) => {
    await page.goto('/influencer');
    await page.waitForURL(/creator/, { timeout: 5_000 }).catch(() => {});
    expect(page.url()).toMatch(/creator/);
  });
});
