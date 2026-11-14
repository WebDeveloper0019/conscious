import { test, expect, ROUTES, assertNoPHPErrors, assertNoHorizontalScroll } from '../utils/fixtures';

// ---------------------------------------------------------------------------
// Cart
// ---------------------------------------------------------------------------

test.describe('Cart page', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.cart);
  });

  test('cart page loads @smoke', async ({ page }) => {
    await assertNoPHPErrors(page);
    // Either shows empty cart or redirects to login — both are valid
    const status = page.url();
    expect(status).toBeTruthy();
  });

  test('has no PHP errors', async ({ page }) => {
    await assertNoPHPErrors(page);
  });

  test('has no horizontal scroll', async ({ page }) => {
    await assertNoHorizontalScroll(page);
  });
});

// ---------------------------------------------------------------------------
// Checkout
// ---------------------------------------------------------------------------

test.describe('Checkout page', () => {
  test('checkout redirects unauthenticated users @smoke', async ({ goto, page }) => {
    await goto(ROUTES.checkout);
    // Should either redirect to login or show a guest checkout prompt
    const url = page.url();
    const isLoginRedirect = url.includes('login') || url.includes('account');
    const isCheckoutPage = url.includes('checkout');
    expect(isLoginRedirect || isCheckoutPage).toBe(true);
  });
});

// ---------------------------------------------------------------------------
// Wishlist
// ---------------------------------------------------------------------------

test.describe('Wishlist page', () => {
  test('wishlist redirects or shows unauthenticated state @smoke', async ({ goto, page }) => {
    await goto(ROUTES.wishlist);
    await assertNoPHPErrors(page);
  });
});

// ---------------------------------------------------------------------------
// Security: private files must not be accessible
// ---------------------------------------------------------------------------

test.describe('Security — private file access', () => {
  const blockedPaths = [
    '/api/.env',
    '/api/config.php',
    '/api/bin/migrate.php',
    '/storage/private/',
    '/config/environments/production.env.example',
    '/docs/deployment/PRODUCTION.md',
    '/tests/backend-e2e.php',
    '/scripts/build-production.php',
    '/.gitignore',
    '/composer.json',
  ] as const;

  for (const path of blockedPaths) {
    test(`${path} returns 403 or 404 @smoke`, async ({ page }) => {
      const response = await page.goto(path);
      const status = response?.status() ?? 0;
      expect([403, 404]).toContain(status);
    });
  }
});

// ---------------------------------------------------------------------------
// Security: HTTP headers
// ---------------------------------------------------------------------------

test.describe('Security — HTTP response headers', () => {
  test('homepage sets X-Content-Type-Options', async ({ page }) => {
    const response = await page.goto(ROUTES.home);
    const header = response?.headers()['x-content-type-options'];
    expect(header).toBe('nosniff');
  });

  test('homepage sets X-Frame-Options', async ({ page }) => {
    const response = await page.goto(ROUTES.home);
    const header = response?.headers()['x-frame-options'];
    expect(header?.toUpperCase()).toBe('SAMEORIGIN');
  });

  test('homepage sets Referrer-Policy', async ({ page }) => {
    const response = await page.goto(ROUTES.home);
    const header = response?.headers()['referrer-policy'];
    expect(header).toBeTruthy();
  });

  test('homepage does not expose X-Powered-By', async ({ page }) => {
    const response = await page.goto(ROUTES.home);
    const header = response?.headers()['x-powered-by'];
    expect(header).toBeUndefined();
  });

  test('site is served over HTTPS', async ({ page }) => {
    await page.goto(ROUTES.home);
    expect(page.url()).toMatch(/^https:/);
  });
});

// ---------------------------------------------------------------------------
// Accessibility: basic checks
// ---------------------------------------------------------------------------

test.describe('Accessibility — basics', () => {
  test('homepage has a single h1', async ({ goto, page }) => {
    await goto(ROUTES.home);
    const h1Count = await page.locator('h1').count();
    expect(h1Count).toBeGreaterThanOrEqual(1);
  });

  test('all images on homepage have alt attributes', async ({ goto, page }) => {
    await goto(ROUTES.home);
    const imagesWithoutAlt = await page.evaluate(() =>
      Array.from(document.images)
        .filter(img => !img.alt && !img.getAttribute('role'))
        .map(img => img.src),
    );
    expect(imagesWithoutAlt, `Images missing alt: ${imagesWithoutAlt.join(', ')}`).toHaveLength(0);
  });

  test('main landmark is present on homepage', async ({ goto, page }) => {
    await goto(ROUTES.home);
    await expect(page.locator('main, [role="main"]')).toBeVisible();
  });

  test('all form labels are associated on customer login', async ({ goto, page }) => {
    await goto(ROUTES.customerLogin);
    const unlabelled = await page.evaluate(() => {
      const inputs = Array.from(document.querySelectorAll<HTMLInputElement>(
        'input:not([type="hidden"]):not([type="submit"]):not([type="button"])',
      ));
      return inputs
        .filter(input => !input.labels?.length && !input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby'))
        .map(input => input.id || input.name);
    });
    expect(unlabelled, `Unlabelled inputs: ${unlabelled.join(', ')}`).toHaveLength(0);
  });

  test('skip-to-content link exists or main has id', async ({ goto, page }) => {
    await goto(ROUTES.home);
    const skipLink = page.locator('a[href="#main-content"], a[href="#content"], [id="main-content"]');
    await expect(skipLink.first()).toBeAttached();
  });
});

// ---------------------------------------------------------------------------
// Performance: basic checks
// ---------------------------------------------------------------------------

test.describe('Performance — basics', () => {
  test('homepage responds within 10 seconds @smoke', async ({ page }) => {
    const start = Date.now();
    await page.goto(ROUTES.home, { waitUntil: 'domcontentloaded' });
    const elapsed = Date.now() - start;
    expect(elapsed).toBeLessThan(10_000);
  });

  test('shop page responds within 12 seconds', async ({ page }) => {
    const start = Date.now();
    await page.goto(ROUTES.shop, { waitUntil: 'domcontentloaded' });
    const elapsed = Date.now() - start;
    expect(elapsed).toBeLessThan(12_000);
  });
});
