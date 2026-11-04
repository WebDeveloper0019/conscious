# Hostinger deployment: conscious.drowdigital.in

The document root was confirmed by a temporary file at `https://conscious.drowdigital.in/ci-root-check.txt`. In this domain's Hostinger File Manager it is `public_html/conscious`.

SSH confirmed the domain base directory is `/home/u990067962/domains/drowdigital.in`. It is not the SSH home directory; `~/public_html` points to a different domain. All paths below are relative to this domain base directory unless described as local paths.

## Prepared package

Run `php scripts/build-production.php --hostinger-fixed-root` and `php scripts/verify-production-release.php`. The build output is split into public and private directories. Never upload the repository itself.

| Local archive | Extract contents into the Hostinger folder |
| --- | --- |
| `build/conscious-private-hostinger-20261104.zip` | `private/ci-backend/` (outside `public_html`) |
| `build/conscious-public-hostinger-20261104.zip` | `public_html/conscious/` (the confirmed domain document root) |

The public PHP bridges expect `private/ci-backend/backend` to be under the same domain base directory as `public_html`. Upload the private archive first. The archives contain no `.env` or local database credentials. Preserve hidden `.htaccess` files when extracting. These dated ZIP files are snapshots; rebuilding `build/release` does not update an existing ZIP. Repackage changed source before another archive deployment.

## Before uploading public files

1. Delete the temporary `ci-root-check.txt` file.
2. Check the domain's absolute base path and PHP version using Hostinger's control panel or SSH. Use a maintained PHP release with required extensions: `curl`, `fileinfo`, `json`, `mbstring`, `openssl`, and `pdo_mysql`.
3. Back up any existing database, private uploads, and public files.
4. Create a dedicated MySQL database and a non-root database user for this application. Record connection details only in Hostinger's private environment file.
5. Extract the private archive into `private/ci-backend` outside `public_html`.
6. Create `private/ci-backend/backend/.env` from `build/release/.env.example`. Set `APP_ENV=production`, `APP_URL=https://conscious.drowdigital.in`, a new random `APP_KEY` of at least 32 characters, Hostinger database credentials, the verified Resend sender and API key, and `PRIVATE_STORAGE_PATH` as the absolute path to `private/ci-backend/storage/private`. Keep `CORS_ORIGIN` empty for same-origin access. Set real Razorpay and webhook credentials before enabling checkout. Do not paste secrets into chat, upload the example file into the public folder, or use the local XAMPP database password.
7. Ensure the PHP user can write to `private/ci-backend/storage` and `private/ci-backend/backend/data`. Keep all application source outside these runtime folders read-only where the host allows it.
8. Run `php scripts/production-preflight.php --env=PATH_TO_PRIVATE_ENV` locally against the generated release using a securely held production environment file, and resolve every failure before accepting orders. This script validates the local build and is not packaged as a server command. On Hostinger, separately verify PHP extensions, database connectivity, writable private storage, mail delivery, and payment/scanner configuration. Run `php /home/u990067962/domains/drowdigital.in/private/ci-backend/backend/bin/migrate.php` using SSH. Do not run migrations through a public URL.
9. Configure and schedule the upload malware scanner (`CLAMAV_BINARY` and `private/ci-backend/backend/bin/scan-files.php`). Onboarding document approval will remain blocked while files are pending scans.

## Public cutover and checks

After the private backend, database, mail, payment, and scanner checks are ready, extract the public archive into `public_html/conscious`. Check that `.htaccess` exists. Visit `/api/public/health.php`; it must return HTTP 200 and show both database and private storage as `ok`. Check that `/api/.env`, `/api/config.php`, `/api/bin/migrate.php`, and `/storage/private/` do not expose content. Then verify the homepage, shop, customer login, vendor login, influencer login, admin login, and a test checkout using Razorpay test credentials. Check server error logs before taking real orders.

If any check fails, stop taking orders and restore the previous public release. Do not restore a database backup over new customer data without a reviewed rollback plan.

The PHP local payment simulation is unavailable in production. If Razorpay is not configured, live checkout must remain disabled; a pending order is not a paid order.

## Guided deployment checkpoint: 5 November 2026

- The public release was uploaded, and the homepage, shop, and four role login pages returned HTTP 200 in sampled checks.
- The health endpoint returned `success: true`, with database and private storage both `ok`. Requests to `/api/.env`, `/api/config.php`, `/api/bin/migrate.php`, and `/storage/private/` returned 403.
- The server reported PHP 8.3.36 and all six required PHP extensions. This does not verify the PHP version used by every web request or establish that all software is patched.
- All database migrations completed after correcting migration `0036` to use `RTRIM(user_id)` in its generated column. Keep the corrected migration file on subsequent deployments; do not reset the migration table.
- Resend accepted a message to its simulated delivery recipient. Real customer inbox delivery still needs a signup test.
- Razorpay remains unconfigured. `clamscan` and `clamdscan` were not found in the SSH path. Scanner setup was deferred at the user's request; files must remain pending until scanned.
- The updated `backend/bin/create-admin.php` was uploaded separately. It supports `--password-stdin` so the password need not appear in shell history or process arguments. Administrator creation has not succeeded yet: input validation returned the usage message. The current step is to validate the privately entered password, then create the administrator and test login.

Do not store the password in this document. In the same Hostinger SSH session, after setting the admin email/name and entering a strong password with `read -r -s`, use:

```sh
printf '%s\n' "$CI_ADMIN_PASSWORD" | php /home/u990067962/domains/drowdigital.in/private/ci-backend/backend/bin/create-admin.php --password-stdin "$CI_ADMIN_EMAIL" "$CI_ADMIN_NAME"
unset CI_ADMIN_PASSWORD
```

Run interactive `read` commands one at a time. These are Bash commands for the connected SSH shell, not Windows PowerShell commands. A GitHub push updates source control; it does not update Hostinger files automatically.
