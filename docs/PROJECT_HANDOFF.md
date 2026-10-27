# Conscious India - Project handoff

Last prepared: 24 October 2026. This is a navigation and work-order document, **not a certification that every workflow is production-ready**. A new developer must inspect current code and run tests before marking an item complete. Older plans and module READMEs describe different points in development and may be stale.

## 1. What this project is

Conscious India is an India-wide eco-conscious marketplace and events platform. Its actors are customers, brand/artisan vendors, influencers/creators, and administrators. The public site lives at the repository root; maintainable role-specific PHP frontend modules live under `portals/modules/`; PHP APIs, database migrations, and jobs live under `api/`. The local XAMPP URL base is `http://localhost/conscious/`.

Key business rules from the owner:

- Delivery is **entirely manual by each vendor**: serviceable states/cities/PIN codes, shipping prices, courier choice, dispatch, tracking and delivery milestones. Do not add Shiprocket or another automatic fulfilment dependency.
- Customers can pay by Razorpay or cash on delivery (COD), subject to actual server-side eligibility rules. Do not render COD enabled if the quote/checkout API rejects it.
- Products, creators, customers, orders, and operational metrics must come from the database in live mode, not hardcoded dashboard/sample data. Local fixtures may exist only in explicit local/demo modes.
- Brand and artisan vendors have distinct registration/onboarding/document requirements and admin review before selling.
- Public website styling should remain consistent with customer, vendor, creator and admin surfaces. Do not replace the existing public design without owner approval.
- Security and validation must be enforced on the backend as well as shown clearly in the frontend.

## 2. Where to look first

| Area | Paths / entry points |
| --- | --- |
| Public site | `index.html`, `shop.php`, `creators.html`, `influencer-profile.html` and other root pages |
| Customer | `portals/modules/customer/` and `api/auth/`, `api/user/` |
| Vendor | `portals/modules/vendor/` and `api/vendor/` |
| Creator | `portals/modules/influencer/` and `api/influencer/` |
| Admin | `portals/modules/admin/` and `api/admin/` |
| Events | `portals/modules/events/` plus relevant customer/admin/event APIs |
| Shared portal UI | `portals/shared/`; read `docs/architecture/PORTALS.md` but verify its older status claims |
| Database | `api/schema.sql`, `api/database/migrations/`, `api/bin/migrate.php` |
| Tests | `tests/`, plus module-specific browser tests |
| Deployment | `README.md`, `docs/deployment/`, and `config/environments/` |
| Prior plan | `BACKEND_IMPLEMENTATION_PLAN.md` (roadmap, **not** current completion report) |

## 3. What is implemented in code, with limits

- Customer registration/login/OTP, profile, catalogue/cart/checkout/order-related endpoints and pages exist. COD and Razorpay paths are present. **End-to-end production payment, stock, email, and order tests still must be run**; existence of a page or endpoint is not proof of a completed business flow.
- Vendor brand/artisan auth, onboarding, catalogue, inventory, order/manual-delivery, returns, finance, support and other screens/endpoints exist. Verify each page against its corresponding API and permissions; some `pages/*` variants are historical/demo implementations.
- Creator auth/onboarding, profile, campaign/referral, earnings, curated products, inquiries and package draft/review/discovery code exists. `portals/modules/influencer/README.md` describes the flow. **Creator package purchase is not live**: the current integration provides vendor discovery/inquiry, not a completed Razorpay purchase and admin coordination workflow.
- Admin authentication/MFA is connected. The admin module README explicitly says that **most operational forms and dashboards are still sample-data or frontend-only**; audit and connect each workflow before calling admin complete.
- Database schema and versioned migrations exist. Run the migration/test process against a backup or isolated database, not blindly against real user data.
- Email transport integrates Resend and local log mode. The owner reports `conscious.drowdigital.in` is verified in Resend; this has **not** yet been verified by a real OTP inbox-delivery test in this handoff.

## 4. Immediate state and important cautions

- Checked-out branch at handoff: `main`, most recent inspected commit `31dacf1` (`Fix repeat COD checkout and restore random email verification codes`). Always run `git status` and `git log` again: these facts can change.
- The workspace may contain unrelated or uncommitted files. Preserve them; never reset, delete, or commit them without identifying ownership.
- Local `api/.env` is private and ignored. During this session the placeholder `APP_KEY` was replaced with a valid local key and XAMPP DB credentials were corrected to `root`/empty password; a CLI connection to `conscious_indi` succeeded. **Never use those DB credentials on Hostinger. Never disclose or commit API keys, app keys, passwords, OTPs or private uploads.**
- Local `APP_ENV` currently reads `production` and `MAIL_TRANSPORT` reads `resend`; make a deliberate choice for testing. Never relax production checks just to make a demo work.
- Contributor branches must be reviewed selectively. Never merge authentication bypasses, unscanned upload handling, incomplete payment stubs, or incompatible schema changes.

## 5. Remaining work — recommended order

1. **Baseline and environment:** inspect current Git state, install dependencies, run PHP syntax/migration tests and a safe local HTTP smoke test. Check runtime errors. Keep local and production secrets separate.
2. **Transactional email:** with verified Resend domain and private sender/API key configured, test actual delivery of customer, vendor and creator OTP plus password resets to external inboxes. Improve branded responsive HTML/plain-text templates, error handling and delivery monitoring. Never log plaintext OTP in production.
3. **Customer purchase path:** validate product listing/images, search/filter, add-to-cart, cart persistence, serviceability/shipping, server quote, Razorpay and COD, duplicate-submit/idempotency, stock, order confirmation, cancellations/returns and clear field-level errors end-to-end. Confirm no public-facing hardcoded products.
4. **Vendor lifecycle:** run brand and artisan registration through document scanning, admin review, approval, onboarding, product approval, India-wide shipping rates, manual fulfilment/tracking, returns, settlement and support. Check ownership/IDOR, status transitions, audit and responsive UI.
5. **Admin real operations:** replace sample data and no-op actions screen by screen with authorized API calls. Prioritize vendor/product review, orders and delivery exceptions, customer returns/refunds, payout maker/checker, creator review/packages, events, support, audit and reporting. Test MFA and role permissions.
6. **Creator package purchase:** build real vendor package selection, server-owned quote, Razorpay order, verified callback/webhook, purchase/ledger records, admin contact-and-coordination workflow, cancellation/refund and notifications. Keep inquiry-only until the full payment path is safe; do not substitute an unpaid-order placeholder.
7. **Events:** audit discovery, booking, ticket capacity, payment/COD applicability, cancellation/refund, exhibitor/vendor flows and admin approvals against real DB state.
8. **Production hardening:** private upload malware scanner and scheduled jobs, backup/restore drill, HTTPS/cookies/CSRF, secrets and RBAC review, monitoring, accessibility/mobile checks, and deployment smoke tests.

For each item, provide evidence: route/API names, database changes, screenshots where useful, test commands/results, and an explicit list of remaining gaps. Do not call the whole project “100% complete” because a route exists.

## 6. Verification commands and safety

Start with `git status --short`, `git log -5 --oneline`, `php -v`, then follow `api/README.md`. Existing tests include `tests/migration-test.php`, `tests/pdf-smoke.php`, `tests/backend-e2e.php`, `tests/integration-e2e.php`, and `tests/influencer-auth-onboarding-e2e.php`. HTTP/E2E tests must use an **isolated test database**, never the owner's development or production DB. Check each test's setup instructions before executing it. Preserve all user data and uncommitted files.

## 7. Development handoff checklist

Before starting a change, inspect the current repository and `git status`. Treat this file and older plans as context, not as proof of completion. Verify the affected role and workflow, preserve the public UI and manual-vendor-delivery rule, use database-backed live data, enforce server-side security and validation, and run tests against an isolated database. Record exact evidence for what works and what remains. Never commit or reveal `.env` secrets or private uploads. Coordinate before destructive operations or a full branch merge.
