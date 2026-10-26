# Backend deployment

Use PHP 8.2 or newer, MySQL/MariaDB, and the PHP cURL, mbstring, PDO MySQL and zlib extensions.

1. Configure `api/.env` using `config/environments/local.env.example`. Production uses `config/environments/production.env.example` as a template and requires its own application key, database, mail and Razorpay credentials.
2. From `api/`, run `composer install --no-dev --no-scripts --prefer-dist`. Upload the generated `api/dependencies/` directory when deploying without Composer on the server. Keep `composer.lock` with the release.
3. Back up the database, then run `php api/bin/migrate.php` from the project root. Applied migrations must not be edited; add a new migration for schema changes. SQL line endings are normalized for Windows/Linux checksum compatibility.
4. Configure the existing upload scanner and scheduled workers for the features enabled in production. Delivery remains manually managed by vendors.

PDF invoice and credit-note downloads use the Composer runtime, including its fonts. The copied, incomplete TCPDF development tree is excluded from the release.

Local fixture seeders refuse production. Database-backed demo listings are visible only in the local environment. Never deploy local database credentials or private storage backups to public folders.

## Verification

- `php tests/migration-test.php` checks installed schema, idempotent migrations and a fresh schema.
- `php tests/pdf-smoke.php` checks both PDF renderers.
- `tests/backend-e2e.php` uses real HTTP endpoints and requires an isolated `conscious_backend_test` or `conscious_backend_merge_<digits>` database. Set `DB_NAME` and `BACKEND_TEST_URL` for both the PHP test server and runner.

Example isolated test server: `php -S 127.0.0.1:8112 -t .` with `DB_NAME` set to the dedicated test database. Set `BACKEND_TEST_URL=http://127.0.0.1:8112/api` for the runner. Do not run the fixture tests against the development or production database.
