# Tarun branch integration review — 31 October 2026

Compared `origin/tarun-influencer` at `e8e5898` with `main` at `79be9ec` after fetching both branches. The incoming branch contains 14 commits that are not on `main` and diverged before the production reorganization. Its single text conflict was `api/config.php`.

## Integrated

- Kept the production configuration, session, authentication, payment and portal implementations already on `main` when resolving the merge.
- Applied the catalogue search fix from Tarun's branch. Search predicates now use distinct PDO parameters. The same fix was applied to category predicates, which also repeated one named parameter.

## Incoming changes excluded during conflict resolution

- `api/config.php`, `api/lib/helpers.php`, `api/lib/session.php`, authentication and many user APIs: the incoming versions replace the current session schema and protections. They remove revoked-session checks, use nonsecure cookies, and expose internal error text.
- Payment and package checkout files: `api/vendor/create-order.php` creates a payment before checking idempotency and references an `idempotency_key` column absent from its migration. The incoming webhook skips signature verification locally and uses a fallback secret. The current `influencer_service_orders` implementation on `main` remains authoritative.
- Incoming `0036_influencer_service_orders.sql`: it defines a second, incompatible service-order schema beside the existing `0039_influencer_service_orders.sql`.
- Customer and creator portal rewrites: incoming code contains hardcoded `http://localhost/conscious/...` redirects, debug logging, incomplete views and unescaped HTML from API content. These conflict with the production URL and portal structure on `main`.
- `api/user/impact.php`: reported water, waste and livelihood figures are multiplied from order count without measured source data. Publishing those values as verified impact would mislead customers.
- `api/seed-demo.php`, `api/seed-campaigns.php`, `test-email.php`, demo-password seed code and files under `storage/private_uploads/`: these are development or private runtime data and do not belong in a production merge.
- Creator photo code: it queries `profile_image_id`, `cover_image_id` and `portfolio_json` fields that have no corresponding migration on `main`, and it does not check file visibility, scan status or deletion before returning URLs.

The branch's safe catalogue fix is integrated as a clean commit. Its Git ancestry is not merged into `main`, because that would carry the excluded private uploads and development scripts in history even if the final file tree omitted them. A later implementation can add the missing creator photo schema and UI with a migration and secure file checks.
