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
    await assertNobrokenImages(page);
  });

  test('has no horizontal scroll on desktop', async ({ page }) => {
    await assertNoHorizontalScroll(page);
  });

  test('preloader disappears after load', async ({ page }) => {
    await expect(page.locator('#bc-preloader')).toBeHidden();
  });

  test('header is visible with logo and actions', async ({ page }) => {
    await expect(page.locator('.bc-header')).toBeVisible();
    await expect(page.locator('.bc-header img[alt*="Conscious"]').first()).toBeVisible();
    await expect(page.locator('a[aria-label="Cart (0 items)"], a[aria-label*="Cart"]')).toBeVisible();
    await expect(page.locator('a[aria-label*="Account"]')).toBeVisible();
  });

  test('announce bar shows Register As Partner link', async ({ page }) => {
    const bar = page.locator('.bc-announce-bar');
    await expect(bar).toBeVisible();
    await expect(bar.locator('a[href*="/join"]')).toBeVisible();
  });

  test('main navigation has all top-level items', async ({ page }) => {
    const nav = page.locator('.bc-nav-list');
    await expect(nav).toBeVisible();
    // At least 4 top-level nav items
    await expect(nav.locator('.bc-nav-item')).toHaveCountGreaterThan(3);
  });

  test('search input is interactive', async ({ page }) => {
    const input = page.locator('#bc-search-input');
    await expect(input).toBeVisible();
    await input.fill('pottery');
    await expect(input).toHaveValue('pottery');
  });

  test('hero section is rendered', async ({ page }) => {
    // At least one above-the-fold section visible
    const hero = page.locator('section').first();
    await expect(hero).toBeVisible();
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

    await expect(drawer).toHaveAttribute('aria-hidden', 'true');
    await hamburger.click();
    await expect(drawer).toHaveAttribute('aria-hidden', 'false');

    await page.locator('#bc-drawer-close').click();
    await expect(drawer).toHaveAttribute('aria-hidden', 'true');
  });

  test('mobile drawer contains navigation links', async ({ page }) => {
    await page.locator('#bc-hamburger').click();
    const drawer = page.locator('#bc-mobile-drawer');
    await expect(drawer).toBeVisible();
    // Should have multiple links
    await expect(drawer.locator('a')).toHaveCountGreaterThan(4);
  });
});

test.describe('Static public pages', () => {
  const pages = [
    { name: 'About', route: ROUTES.about, titlePattern: /About/i },
    { name: 'Contact', route: ROUTES.contact, titlePattern: /Contact/i },
    { name: 'Sustainability', route: ROUTES.sustainability, titlePattern: /Sustain/i },
    { name: 'Mission', route: ROUTES.mission, titlePattern: /Mission/i },
    { name: 'Blog', route: ROUTES.blog, titlePattern: /Blog/i },
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
    const footer = page.locator('footer');
    await expect(footer).toBeVisible();
  });

  test('footer contains useful links', async ({ page }) => {
    const footer = page.locator('footer');
    await expect(footer.locator('a')).toHaveCountGreaterThan(3);
  });
});

test.describe('404 page', () => {
  test('returns 404 for unknown routes', async ({ page }) => {
    const response = await page.goto('/this-page-definitely-does-not-exist-xyz');
    expect(response?.status()).toBe(404);
  });

  test('404 page renders without PHP errors', async ({ goto, page }) => {
    // Navigate via goto (ignores status) so we can inspect the page
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
    expect(json.database).toBe('ok');
  });
});
