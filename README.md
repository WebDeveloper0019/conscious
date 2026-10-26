# Conscious India

Conscious India is a PHP 8.2 marketplace and events platform for customers, vendors, creators, and administrators.

## Repository layout

```text
api/                    HTTP endpoints and backend services
assets/                 Public styles, scripts, fonts, and images
config/environments/    Versioned, secret-free environment templates
portals/
  modules/              Customer, vendor, creator, and admin applications
  shared/               Shared portal UI foundations
includes/               Shared public-site layouts
storage/                Private runtime storage (web access denied)
tests/                  Automated and smoke tests
scripts/                Build, deployment, and maintenance commands
docs/                   Architecture, operations, and handoff notes
```

The repository root remains the XAMPP-compatible development document root. Production is never deployed directly from the working tree. Run the release builder to create a clean document root and a separate private backend.

## Local setup

1. Copy `config/environments/local.env.example` to `api/.env` and configure local database values.
2. Run `composer install --working-dir=api`.
3. Apply migrations with `php api/bin/migrate.php`.
4. Serve the repository through Apache at `/conscious`.

## Production build

```bash
php scripts/build-production.php
php scripts/verify-production-release.php
php scripts/production-preflight.php --env=build/release/.env
```

The builder produces:

```text
build/release/
  public_html/           Web-server document root
  private/backend/       Configuration, libraries, dependencies, jobs, and migrations
  private/storage/       Uploads and application runtime data
  .env.example           Production configuration template
```

See [`docs/deployment/PRODUCTION.md`](docs/deployment/PRODUCTION.md) for deployment, verification, and rollback procedures.

## Security

- Never commit `.env`, passwords, payment keys, customer exports, or private uploads.
- Point the production web server only at `build/release/public_html`.
- Run migrations and maintenance commands from `private/backend/bin`, not from the public document root.
- Use an isolated test database when running integration tests.
