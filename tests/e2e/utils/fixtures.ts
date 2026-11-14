import { test as base, expect, Page } from '@playwright/test';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

export const ROUTES = {
  home: '/',
  shop: '/shop',
  about: '/about',
  contact: '/contact',
  cart: '/cart',
  checkout: '/checkout',
  wishlist: '/account/wishlist',
  events: '/events',
  artisans: '/artisans',
  brands: '/brands',
  blog: '/blog',
  sustainability: '/sustainability',
  mission: '/mission',
  careers: '/careers',
  partnerships: '/partnerships',
  // Auth
  customerLogin: '/account/login',
  customerRegister: '/account/register',
  customerForgotPassword: '/account/forgot-password',
  // Partner portal
  partnerRegister: '/join',
  partnerLogin: '/partner/login',
  partnerForgotPassword: '/partner/forgot-password',
  // Creator portal
  creatorLogin: '/creator/login',
  // Admin portal
  adminLogin: '/admin/login',
  // API
  healthCheck: '/api/public/health.php',
} as const;

// Strong password that passes all rules (12+ chars, upper, lower, number, symbol)
export const VALID_PASSWORD = 'TestPass!2025x';

// ---------------------------------------------------------------------------
// Custom fixtures
// ---------------------------------------------------------------------------

type ConscientFixtures = {
  /** Navigates to a page and waits for the preloader to disappear. */
  goto: (route: string) => Promise<void>;
};

export const test = base.extend<ConscientFixtures>({
  goto: async ({ page }, use) => {
    await use(async (route: string) => {
      await page.goto(route);
      // Dismiss the preloader overlay if present
      const preloader = page.locator('#bc-preloader');
      if (await preloader.isVisible()) {
        await preloader.waitFor({ state: 'hidden', timeout: 8_000 });
      }
    });
  },
});

export { expect };

// ---------------------------------------------------------------------------
// Shared helpers
// ---------------------------------------------------------------------------

/** Assert the page has no PHP errors printed in the body. */
export async function assertNoPHPErrors(page: Page): Promise<void> {
  const body = await page.locator('body').textContent() ?? '';
  const phpError = /(?:Fatal error|Parse error|Warning:|Notice:|Uncaught Error)/i.test(body);
  expect(phpError, 'PHP error found on page').toBe(false);
}

/** Assert there are no broken images on the page. */
export async function assertNobrokenImages(page: Page): Promise<void> {
  const broken = await page.evaluate(() =>
    Array.from(document.images)
      .filter(img => !img.complete || img.naturalWidth === 0)
      .map(img => img.src),
  );
  expect(broken, `Broken images found: ${broken.join(', ')}`).toHaveLength(0);
}

/** Assert the page does not have horizontal scroll. */
export async function assertNoHorizontalScroll(page: Page): Promise<void> {
  const hasOverflow = await page.evaluate(
    () => document.documentElement.scrollWidth > document.documentElement.clientWidth + 2,
  );
  expect(hasOverflow, 'Page has horizontal overflow').toBe(false);
}

/** Fill the customer registration form with valid test data. */
export async function fillCustomerRegisterForm(page: Page, opts: {
  firstName?: string;
  lastName?: string;
  email?: string;
  phone?: string;
  password?: string;
} = {}): Promise<void> {
  const suffix = Date.now();
  await page.fill('#register-first', opts.firstName ?? 'Test');
  await page.fill('#register-last', opts.lastName ?? 'User');
  await page.fill('#register-email', opts.email ?? `test+${suffix}@playwright.local`);
  await page.fill('#register-phone', opts.phone ?? '9876543210');
  await page.fill('#register-password', opts.password ?? VALID_PASSWORD);
  await page.fill('#register-confirm', opts.password ?? VALID_PASSWORD);
  await page.check('input[name="terms"]');
}
