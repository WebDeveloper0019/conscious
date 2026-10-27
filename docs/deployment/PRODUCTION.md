# Production deployment

## Release architecture

Production must use a split layout so that only browser-accessible files are placed under the web-server document root.

```text
releases/<release-id>/
  public_html/             Apache document root
    assets/
    portals/
    api/                   Runtime HTTP endpoints only
    *.php                  Public entry points
  private/
    backend/               Configuration, libraries, dependencies, jobs, and migrations
    storage/               Uploads, logs, cache, mail outbox, and runtime data
  .env                     Server configuration and secrets
```

Do not upload the Git working tree directly to a public directory. The `docs`, `tests`, `scripts`, database migrations, development plans, and local exports must never be web-accessible.

The release builder also removes the local XAMPP `/conscious` URL prefix from public source files. Local development therefore keeps its subdirectory URL, while the production package uses clean domain-root URLs.

## Build

From a clean checkout:

```bash
composer install --working-dir=api --no-dev --classmap-authoritative
php scripts/build-production.php
php scripts/verify-production-release.php
cp build/release/.env.example build/release/.env
php scripts/production-preflight.php --env=build/release/.env
```

Set `PRIVATE_STORAGE_PATH` to the absolute path of `private/storage/private`. The web-server user needs write access only to `private/storage` and `private/backend/data`; application source and `public_html` should otherwise be read-only.

## Required server configuration

- PHP 8.2 or newer with `curl`, `fileinfo`, `json`, `mbstring`, `openssl`, and `pdo_mysql`.
- Apache with `mod_rewrite`, `mod_headers`, and `mod_expires` enabled.
- HTTPS enabled before customer traffic is accepted.
- A dedicated MySQL user with access only to the production database.
- Cron access for scheduled jobs and background retries.
- Daily database and private-storage backups with a tested restore procedure.

Point the domain document root to `public_html`. Keep `.env`, `private`, and previous releases above the public root.

## Deployment procedure

1. Back up the database and private storage.
2. Upload the new release to a versioned release directory.
3. Configure `.env` without printing secrets into terminal logs.
4. Run the preflight command and stop on any failure.
5. Put the application into maintenance mode if a migration requires it.
6. Run pending migrations once with `php private/backend/bin/migrate.php`.
7. Switch the domain's document-root symlink or release pointer atomically.
8. Verify `/api/public/health.php`, the homepage, authentication, catalogue, checkout quote, and each dashboard login.
9. Review PHP and Apache error logs before ending the maintenance window.

## Rollback

Keep the previous release unchanged. If health or smoke checks fail, switch the release pointer back immediately. Restore the database only when the migration documentation explicitly requires a data rollback; never overwrite current data automatically.

## Files that must remain private

- `.env` and all credentials
- database dumps, schema migration tooling, and maintenance commands
- logs, mail outbox files, rate-limit files, exports, and QA captures
- customer evidence, uploaded certificates, invoices, and return attachments
- test fixtures and internal implementation notes
