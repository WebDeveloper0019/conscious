import { test, expect, ROUTES, assertNoPHPErrors, assertNobrokenImages, assertNoHorizontalScroll } from '../utils/fixtures';

test.describe('Shop page', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.shop);
  });

  test('loads with correct title @smoke', async ({ page }) => {
    await expect(page).toHaveTitle(/Shop/i);
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

  test('filter sidebar is present', async ({ page }) => {
    await expect(page.locator('.bc-shop-sidebar')).toBeVisible();
  });

  test('product grid renders cards', async ({ page }) => {
    // Wait for products to load (API-driven)
    const productGrid = page.locator('.bc-product-grid, [data-product-grid], .bc-shop-grid');
    await expect(productGrid.first()).toBeVisible({ timeout: 12_000 });
  });

  test('sort bar is visible', async ({ page }) => {
    await expect(page.locator('.bc-shop-sortbar')).toBeVisible();
  });

  test('breadcrumb is rendered', async ({ page }) => {
    await expect(page.locator('.bc-breadcrumb-wrap')).toBeVisible();
  });
});

test.describe('Shop page — mobile', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.shop);
  });

  test('has no horizontal scroll on mobile', async ({ page }) => {
    await assertNoHorizontalScroll(page);
  });

  test('has no PHP errors on mobile', async ({ page }) => {
    await assertNoPHPErrors(page);
  });
});

test.describe('Product detail page', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.home);
  });

  test('product detail page loads from direct route @smoke', async ({ goto, page }) => {
    // Navigate directly — product slug unknown, so we check the page structure
    // that gets rendered even without a valid product (loading state)
    await goto('/product-detail');
    await assertNoPHPErrors(page);
    await expect(page.locator('#main-content')).toBeVisible();
  });
});

test.describe('Category pages', () => {
  const categories = [
    { name: 'Artisans', route: ROUTES.artisans },
    { name: 'Brands', route: ROUTES.brands },
  ] as const;

  for (const { name, route } of categories) {
    test(`${name} listing page loads @smoke`, async ({ goto, page }) => {
      await goto(route);
      await assertNoPHPErrors(page);
      await expect(page).toHaveTitle(new RegExp(name, 'i'));
    });
  }

  const shopCategories = [
    '/pottery',
    '/handloom',
    '/jewelry',
    '/paintings',
    '/fashion',
    '/home-decor',
  ] as const;

  for (const route of shopCategories) {
    test(`${route} category page loads without PHP error`, async ({ goto, page }) => {
      await goto(route);
      await assertNoPHPErrors(page);
    });
  }
});

test.describe('Events page', () => {
  test.beforeEach(async ({ goto }) => {
    await goto(ROUTES.events);
  });

  test('events page loads @smoke', async ({ page }) => {
    await expect(page).toHaveTitle(/Event/i);
    await assertNoPHPErrors(page);
  });

  test('has no broken images', async ({ page }) => {
    await assertNobrokenImages(page);
  });
});

test.describe('Blog', () => {
  test('blog listing loads', async ({ goto, page }) => {
    await goto(ROUTES.blog);
    await expect(page).toHaveTitle(/Blog/i);
    await assertNoPHPErrors(page);
  });
});
