# Creator module

This module owns the authenticated creator experience and its data-binding scripts. The original public `creators.html` and `influencer-profile.html` keep their visual layout. The directory script replaces sample cards with approved database creators; `influencer-profile.html?handle=creator-handle` binds an approved creator's public details. Opening the profile without `handle` still shows the original design preview.

## Local routes

- `/conscious/portals/modules/influencer/auth/register.php` — register a creator account.
- `/conscious/portals/modules/influencer/auth/verify.php` — verify the six-digit email code.
- `/conscious/portals/modules/influencer/auth/login.php` — sign in.
- `/conscious/portals/modules/influencer/auth/forgot-password.php` — request a reset link.
- `/conscious/portals/modules/influencer/auth/reset-password.php?token=…` — use a one-time reset link.
- `/conscious/portals/modules/influencer/index.php` — authenticated Creator Studio.
- `/conscious/portals/modules/admin/influencers.php` — admin application review, campaign assignment, payout review and manual withdrawal recording.

## End-to-end flow

1. Register and verify email. In `APP_ENV=local`, the code is written to `api/data/mail-outbox/`; production requires Resend configuration.
2. In Creator Studio, save a detailed bio, one to five HTTPS social profiles, creator niches, languages, and optional Indian city/state, then upload identity, PAN and social-ownership evidence. The latest version of each required document must pass its security scan before submission. Editing a submitted application returns it to a draft requiring resubmission; editing an active profile pauses public visibility for re-review.
3. A configured malware scanner must process private uploads (`CLAMAV_BINARY`, then `php api/bin/scan-files.php`). The UI correctly keeps files pending until the scan succeeds. Do not mark files clean manually in production.
4. Admin opens the creator review page, checks and approves the latest clean version of each required private document, then approves the creator application. Review reasons and rejected document feedback appear in Creator Studio.
5. Admin creates an active campaign and assigns the approved creator to it. Creator Studio then shows the referral link and required disclosure.
6. Referral visits, attributed paid orders, delivered-order eligibility, analytics and commission history use the backend APIs. The creator can request a withdrawal only against eligible, unreserved funds and a verified payout profile.
7. Admin verifies payout details through the business's separate bank/PAN checks, reviews the withdrawal, sends money manually, then records the actual bank transfer reference. The application does not initiate a transfer.
8. The creator can search approved catalog products and feature up to 24 of them. Their public profile shows real published product names, prices and clean product images; withdrawn products are filtered out automatically. When an active campaign is assigned, product clicks go through a validated creator referral link before opening the product page.
9. A visitor can open an approved creator's public profile and send a validated partnership inquiry. The creator reads and closes it in Creator Studio.

## Local UI walkthrough

Run `php api/bin/seed-influencer-demo.php` in the local environment to create the safe draft-only demo account. Sign in as `creator.demo@conscious.test` with `DemoCreator#2026!`. This account has no fabricated approval, documents, sales or payout; it exists only for reviewing the form and empty states. Use the sidebar (or Menu on narrow screens) to visit Profile & verification, Campaign links, Curated products, Analytics, Earnings, Withdrawals, Partnership inquiries, Notifications and Settings. The demo seeder refuses non-local environments.

For an automated HTTP check, run the PHP server against an isolated `conscious_backend_merge_*` database and then run `php tests/influencer-auth-onboarding-e2e.php` with the same `DB_NAME` and `TEST_BASE_URL`. The test creates local-only accounts, exercises signup/login/reset, CSRF, upload, review feedback and resubmission, and simulates a clean malware-scan result only inside that isolated database.

## Deployment requirements and current limitations

- Set production `APP_URL`, `APP_KEY`, database credentials, Resend settings, and `CLAMAV_BINARY` to a trusted installed scanner. Schedule `api/bin/scan-files.php`; without this, document approval and creator onboarding intentionally stop.
- Razorpay/COD checkout and order delivery events must be exercised in a populated environment to validate real referral commissions. No live financial transaction was performed during development.
- Public creator photos, portfolios and granular niches still require creator data fields and upload moderation. The dynamic public profile hides sample portfolio content when opened with a real creator handle rather than misrepresenting it.
- Admin campaign tools create, assign, pause and resume campaigns. Editing campaign commercial terms or extending their dates still needs a controlled follow-up workflow; existing terms are not silently rewritten after creators share links.
