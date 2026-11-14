# Conscious India — Playwright E2E Test Suite

End-to-end UI tests for [conscious.drowdigital.in](https://conscious.drowdigital.in) using [Playwright](https://playwright.dev).

## What is tested

| Suite | File | Coverage |
|---|---|---|
| Homepage & static pages | `public/homepage.spec.ts` | Homepage render, nav, mobile drawer, announce bar, static pages, 404, health API |
| Shop & catalogue | `shop/shop.spec.ts` | Shop page, filters, category pages, events, blog, product detail |
| Auth flows | `auth/auth.spec.ts` | Customer login/register/forgot-password, partner register (artisan/brand/creator), partner login, creator login, admin login |
| Portal access | `portals/portals.spec.ts` | Unauthenticated access to all portal routes, legacy URL redirects |
| Cart, checkout, security | `utils/cart-checkout-security.spec.ts` | Cart, checkout, wishlist, blocked private files, HTTP security headers, accessibility basics, performance |

Tests tagged `@smoke` form the critical path — run those first.

## Prerequisites

- Node.js 20+
- The site must be reachable (live site by default)

## Setup

```bash
cd tests/e2e
npm install
npx playwright install --with-deps chromium
```

## Running tests

```bash
# All tests against the live site
npm test

# Smoke tests only (fastest, ~2 min)
npm run test:smoke

# Specific suite
npm run test:auth
npm run test:public
npm run test:shop
npm run test:portals

# With browser visible (useful for debugging)
npm run test:headed

# Interactive UI mode
npm run test:ui

# Against local XAMPP dev server
BASE_URL=http://localhost/conscious npm test
```

## CI

```bash
npm test -- --reporter=github
```

Set `BASE_URL` to your staging or production URL in your CI environment variables.

## Viewing results

```bash
npm run test:report
```

Opens the HTML report with screenshots and traces for any failures.

## Test structure

```
tests/e2e/
  playwright.config.ts       # Browsers, base URL, timeouts, reporters
  package.json
  utils/
    fixtures.ts              # Shared routes, helpers, custom test fixture
    cart-checkout-security.spec.ts
  public/
    homepage.spec.ts
  shop/
    shop.spec.ts
  auth/
    auth.spec.ts
  portals/
    portals.spec.ts
```

## Notes

- These tests run against the **live** site by default. They make no writes (no real registrations go through — invalid credential tests are read-only).
- All tests are read-only and safe to run against production at any time.
- The `@smoke` tag marks the critical-path tests. Run the full suite in CI, and smoke tests as a post-deploy check.
