# Conscious India — security testing and penetration-test status

**Date:** 4 November 2026

**System tested:** local PHP release candidate, using an isolated MariaDB test database

**Live domain:** `https://conscious.drowdigital.in`

**Overall result:** local checks passed after one confirmed development-server exposure was fixed. Production security approval is **pending**.

## What this report means

This is a record of the security checks actually performed. It includes targeted penetration-style probes, automated integration tests, release packaging checks, and a dependency advisory check. It is **not** a full independent penetration test, a claim of zero vulnerabilities, or approval to accept real customer payments. Tests used the isolated `conscious_backend_merge_20261104_1730` database and a loopback test server; they did not modify the live database.

## Test results

| Area | What was checked | Result |
| --- | --- | --- |
| PHP syntax | 520 PHP files in the earlier full pass; changed test files, build scripts, and development router checked again | Passed |
| Database migrations | Fresh install, required schema, unique constraints, repeat migrations and checksums | **154 passed, 0 failed** |
| Customer/vendor HTTP journey | Login, CSRF, account ownership, saved vendors, notifications, addresses, stock limits, quote invalidation, coupons, server-side pricing, payment confirmation, manual shipment tracking, audit records | **68 passed** (`BACKEND_E2E_OK`) |
| Influencer authentication and onboarding | Password rules, duplicate registration, email verification, wrong code and resend limits, sessions, CSRF, documents, review gates, payout validation, password reset, logout | **51 passed** (`INFLUENCER_AUTH_ONBOARDING_OK`) |
| Commerce integration | Returns, refunds, invoices and credit notes, rewards ledger, partial cancellation | **20 passed, 0 failed** |
| Influencer service orders | Quote/GST calculation, verified payment, admin task, idempotency, earnings, refund and clawback | **8 checks passed** |
| Unauthenticated access | Protected customer, vendor and admin APIs returned 401; sensitive Apache paths returned 403 | Passed for sampled routes |
| Development-server private paths | `.env`, PHP config, backend libraries, migration command and private storage | **Finding fixed; retest returned 403** |
| CORS origin | Request from an untrusted origin | Rejected with 403 in the sampled check |
| SQL injection smoke probes | Catalogue search inputs including quote/OR and UNION patterns | No unexpected products or SQL errors; **limited coverage** |
| Script-input smoke probe | Catalogue query containing a script tag | No products returned; **not a complete XSS test** |
| Composer dependencies | `composer audit --working-dir=api --no-interaction` on 4 November 2026 | No reported security advisories |
| Production package | Public/private split, `.htaccess`, no `.env` or development files in public archive, Hostinger bridge paths | Release verifier passed |

The payment journey above used the **local mock gateway**. It did not contact Razorpay or charge money.

## Confirmed security problem and resolution

### PHP development server exposed `api/.env`

**Finding:** Starting the site with raw `php -S 127.0.0.1:8112 -t .` served `api/.env` with HTTP 200. PHP's built-in server ignores the Apache `.htaccess` deny rules. The tested server was bound to loopback and was stopped. We have **not** established that this file was exposed on the live Hostinger site; Apache's sampled local path check returned 403.

**Fix:** Added `dev-router.php` to deny hidden files, configuration, migration commands, backend libraries, build/test folders and private storage. Updated `api/README.md` to require the router for local PHP-server testing.

**Retest:** `api/.env`, `api/config.php`, `api/lib/helpers.php`, `api/bin/migrate.php` and `storage/private/` all returned **403**; the public health route still returned **200**.

## Other corrections made while testing

- Repaired the HTTP test client's cookie handling so it actually keeps the authenticated session between requests. The application session and CSRF checks then passed.
- Updated the backend test's old cash-on-delivery scenario to use the current online payment simulation. Cash on delivery is disabled by the project's current payment choice. The full backend HTTP suite then passed 68 checks.
- Updated the rewards integration test fixture to add points through the ledger, matching the application's ledger-based balance calculation. The commerce integration suite then passed 20 checks.
- Built a Hostinger-specific release for the confirmed `public_html/conscious` document root. Its public PHP bridges point to `private/ci-backend/backend` **outside** `public_html`; release verification passed. At the time of the 4 November audit, this release had not yet been uploaded to Hostinger.

The test-fixture corrections are not claims of security vulnerabilities in the production application.

## Remaining security work before accepting live orders

| Priority | Required check | Why it matters |
| --- | --- | --- |
| **Blocker** | Deploy to the confirmed document root, then verify `/api/public/health.php`, `.htaccess`, private-file 403 responses and error logs on Hostinger | Local results do not prove the server configuration is safe |
| **Blocker** | Configure a new 32+ character `APP_KEY`, production HTTPS URL, dedicated non-root DB user, strong DB password and private `.env` outside `public_html` | Protects sessions, data and credentials |
| **Blocker** | Configure Razorpay test/live keys and webhook secret; test signature rejection, replay protection, successful payment, refund and settlement on staging | The local gateway is a simulation; no real payment flow has been verified |
| **Blocker** | Configure and schedule ClamAV or equivalent for private uploads, then verify clean/infected/error paths | Uploaded documents stay pending until scanned; approval must not bypass scanning |
| **Blocker** | Confirm Hostinger PHP, MariaDB and dependencies are on maintained versions; local PHP 8.2.12 and MariaDB 10.4.32 are old | Security fixes depend on maintained runtimes |
| **High** | Run an authorized staging penetration test across all role endpoints for SQL injection, stored/reflected XSS, IDOR, CSRF, file-upload bypass, SSRF, session fixation, brute force, business logic and payment abuse | Current probes cover selected routes, not every input or workflow |
| **High** | Check TLS, cookies, response headers, CORS, backups, restore, least-privilege file permissions and logs on Hostinger | These are deployment-specific and cannot be validated from the local code alone |
| **High** | Run controlled load/rate-limit and availability checks on staging, with agreed limits | Avoid damaging the live site while verifying abuse resistance |

On 4 November 2026 the public homepage returned **403** and `/api/public/health.php` returned **404** before upload. A temporary root-check file proved `public_html/conscious` is the domain document root. Remove that temporary file. The Hostinger release archives are prepared locally, but the application is not live yet.

## Deployment follow-up: 5 November 2026

The paragraph above records the pre-upload state on 4 November. The subsequent guided deployment established the domain base path as `/home/u990067962/domains/drowdigital.in` and uploaded the public release. Sampled live requests returned 200 for the homepage, shop, and all four role login pages. `/api/public/health.php` reported the database and private storage as `ok`; `/api/.env`, `/api/config.php`, `/api/bin/migrate.php`, and `/storage/private/` returned 403. These checks are not a complete live penetration test or a visual/functional test of those pages.

The SSH CLI reported PHP 8.3.36 with the required extensions; MariaDB's exact server version is still unverified. All migrations finished after fixing a generated-column compatibility failure in `0036`. Resend accepted a test to its simulated delivery address; real inbox delivery remains untested. The administrator CLI now supports passwords through standard input; production administrator creation is still awaiting valid input and a successful login test.

Razorpay remains unconfigured. Neither `clamscan` nor `clamdscan` was found in the SSH path, and the user deferred scanner setup. Document scan/approval gates must stay in place. Production payment acceptance and full security approval remain pending.

Before the subsequent source-control push, a further review found that repeated slashes could bypass the development router's private-directory checks. The router now normalizes path separators and rejects hidden path segments and control characters. It is also excluded from the production build, and the release verifier rejects it if present publicly. Local HTTP verification passed 23 probes: 21 protected path variations returned 403 and two permitted static paths returned 200.

An isolated local database check passed 13 assertions covering fresh migrations, repeat migrations, recognized legacy checksums, rejection of an unknown checksum, one-active-cart uniqueness, repeated completed purchases, admin creation through standard input, hashed password storage, admin role assignment, and rejection of invalid email/weak password without inserting an account. This did not create a production administrator or change the Hostinger database.

The Hostinger-layout release was rebuilt from the updated source and passed the release verifier, including the new check that `dev-router.php` is absent from public files. Changed PHP files passed syntax checks. Environment files and release archives remain ignored by Git; dated ZIP archives are not automatically refreshed by the source-control push.

## Release decision

**Do not accept live orders or payments yet.** Finish the blocker items, retest the deployed staging/site configuration, and record the results. A full third-party penetration test may still find issues that these targeted checks did not detect.
