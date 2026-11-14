import { test, expect, ROUTES, assertNoPHPErrors, assertNobrokenImages, assertNoHorizontalScroll } from '../utils/fixtures';

test.describe('Homepage', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.home);
  });

  test('loads with correct title @smoke', async ({ page }) => {
    await expect(page).toHaveTitle(/Conscious India/i);
  });

  test('has no PHP errors @smoke', async ({ page }) => {
    await assertNoPHPErrors(page);
  });

  test('has no broken images', async ({ page }) => {
    // KNOWN SITE ISSUE: ~20 broken images including home-kind-words.png,
    // home-kinder-tomorrow.png, and assets/images/user.png (4 instances).
    // Tracked as a bug — test intentionally fails to surface this for the dev.
    await assertNobrokenImages(page);
  });

  test('has no horizontal scroll on desktop', async ({ page }) => {
    await assertNoHorizontalScroll(page);
  });

  test('preloader disappears after load', async ({ page }) => {
    await expect(page.locator('#bc-preloader')).toBeHidden();
  });

  test('header is visible with logo and actions', async ({ page }) => {
    await expect(page.locator('.bc-header').first()).toBeVisible();
    await expect(page.locator('.bc-header img[alt*="Conscious"]').first()).toBeVisible();
    // Use .first() — desktop header and mobile header both render cart links
    await expect(page.locator('a[aria-label*="Cart"]').first()).toBeVisible();
    await expect(page.locator('a[aria-label*="Account"]').first()).toBeVisible();
  });

  test('announce bar shows partner registration link', async ({ page }) => {
    const bar = page.locator('.bc-announce-bar');
    await expect(bar).toBeVisible();
    await expect(bar.locator('a[href*="/join"]')).toBeVisible();
  });

  test('main navigation has all top-level items', async ({ page }) => {
    const nav = page.locator('.bc-nav-list').first();
    await expect(nav).toBeVisible();
    const count = await nav.locator('.bc-nav-item').count();
    expect(count).toBeGreaterThan(3);
  });

  test('search input is interactive', async ({ page }) => {
    const input = page.locator('#bc-search-input');
    await expect(input).toBeVisible();
    await input.fill('pottery');
    await expect(input).toHaveValue('pottery');
  });

  test('hero section is rendered', async ({ page }) => {
    const hero = page.locator('main section, .bc-hero, [class*="hero"]').first();
    await expect(hero).toBeVisible({ timeout: 10_000 });
  });
});

test.describe('Homepage — mobile', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.home);
  });

  test('has no horizontal scroll on mobile @smoke', async ({ page }) => {
    await assertNoHorizontalScroll(page);
  });

  test('hamburger button is visible on mobile', async ({ page }) => {
    await expect(page.locator('#bc-hamburger')).toBeVisible();
  });

  test('mobile drawer opens and closes', async ({ page }) => {
    const hamburger = page.locator('#bc-hamburger');
    const drawer = page.locator('#bc-mobile-drawer');

    // Drawer starts translated off-screen (closed), CSS class "open" is toggled by JS
    await expect(drawer).toBeAttached();
    await hamburger.click();
    // After click: drawer gets the "open" class which slides it into view via CSS transform
    await expect(drawer).toHaveClass(/open/, { timeout: 3_000 });

    await page.locator('#bc-drawer-close').click();
    await expect(drawer).not.toHaveClass(/open/, { timeout: 3_000 });
  });

  test('mobile drawer contains navigation links', async ({ page }) => {
    await page.locator('#bc-hamburger').click();
    const drawer = page.locator('#bc-mobile-drawer');
    await expect(drawer).toBeVisible();
    const linkCount = await drawer.locator('a').count();
    expect(linkCount).toBeGreaterThan(4);
  });
});

test.describe('Static public pages', () => {
  const pages = [
    { name: 'About', route: ROUTES.about, titlePattern: /About/i },
    { name: 'Contact', route: ROUTES.contact, titlePattern: /Contact/i },
    { name: 'Sustainability', route: ROUTES.sustainability, titlePattern: /Sustain/i },
    { name: 'Mission', route: ROUTES.mission, titlePattern: /Mission/i },
    // Blog page title is "Stories — Conscious India"
    { name: 'Blog', route: ROUTES.blog, titlePattern: /Blog|Stories/i },
    { name: 'Careers', route: ROUTES.careers, titlePattern: /Career/i },
    { name: 'Partnerships', route: ROUTES.partnerships, titlePattern: /Partner/i },
  ] as const;

  for (const { name, route, titlePattern } of pages) {
    test(`${name} page loads with correct title @smoke`, async ({ goto, page }) => {
      await goto(route);
      await expect(page).toHaveTitle(titlePattern);
      await assertNoPHPErrors(page);
    });
  }
});

test.describe('Footer', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.home);
  });

  test('footer is present', async ({ page }) => {
    // .bc-footer is the visible footer; the 7 native <footer> elements are hidden utility wrappers
    await expect(page.locator('.bc-footer')).toBeVisible();
  });

  test('footer contains useful links', async ({ page }) => {
    const footer = page.locator('.bc-footer');
    const linkCount = await footer.locator('a').count();
    expect(linkCount).toBeGreaterThan(3);
  });
});

test.describe('404 page', () => {
  test('returns 404 for unknown routes', async ({ page }) => {
    const response = await page.goto('/this-page-definitely-does-not-exist-xyz');
    expect(response?.status()).toBe(404);
  });

  test('404 page renders without PHP errors', async ({ page }) => {
    await page.goto('/this-page-definitely-does-not-exist-xyz', { waitUntil: 'domcontentloaded' });
    await assertNoPHPErrors(page);
  });
});

test.describe('Health check API', () => {
  test('health endpoint returns 200 and success @smoke', async ({ page }) => {
    const response = await page.goto(ROUTES.healthCheck);
    expect(response?.status()).toBe(200);

    const body = await page.locator('body').textContent();
    const json = JSON.parse(body ?? '{}');
    expect(json.success).toBe(true);
  });

  test('health endpoint reports database as ok @smoke', async ({ page }) => {
    await page.goto(ROUTES.healthCheck);
    const body = await page.locator('body').textContent();
    const json = JSON.parse(body ?? '{}');
    // Response shape: { checks: { database: "ok" } }
    expect(json.checks?.database ?? json.database).toBe('ok');
  });
});
