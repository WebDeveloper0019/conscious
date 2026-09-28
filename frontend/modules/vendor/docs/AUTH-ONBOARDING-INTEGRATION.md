# Vendor authentication and onboarding integration

The PHP module is frontend-only. Keep the current public URLs when connecting the separate backend:

- `/frontend/modules/vendor/auth/login.php`
- `/frontend/modules/vendor/auth/register.php`
- `/frontend/modules/vendor/auth/status.php`
- `/frontend/modules/vendor/onboarding.php?type=artisan|brand`

## Account and application states

Use server-owned states. The browser must never decide approval or store activation.

1. `account_created`
2. `email_or_phone_verification_required`
3. `onboarding_draft`
4. `submitted`
5. `under_review`
6. `changes_requested`
7. `resubmitted`
8. `approved`
9. `rejected`
10. `suspended`

After registration, redirect authenticated vendors to onboarding. After final submission, redirect them to the status page. Only `approved` vendors may access live catalogue, order, finance, delivery and event operations.

## Recommended API surface

### Authentication

- `POST /api/vendor/auth/register`
- `POST /api/vendor/auth/verify-contact`
- `POST /api/vendor/auth/login`
- `POST /api/vendor/auth/logout`
- `POST /api/vendor/auth/forgot-password`
- `POST /api/vendor/auth/reset-password`
- `POST /api/vendor/auth/mfa/challenge`
- `POST /api/vendor/auth/mfa/verify`

Registration payload: `vendor_type`, `owner_name`, `display_name`, normalized email, Indian mobile, password, mandatory consent versions and optional communication consent. Never accept approval, role, permissions or application status from the browser.

### Onboarding draft and submission

- `GET /api/vendor/onboarding`
- `PATCH /api/vendor/onboarding/profile`
- `PATCH /api/vendor/onboarding/verification`
- `PATCH /api/vendor/onboarding/store-delivery`
- `PATCH /api/vendor/onboarding/policies-impact`
- `POST /api/vendor/onboarding/documents`
- `DELETE /api/vendor/onboarding/documents/{documentId}`
- `GET /api/vendor/onboarding/review`
- `POST /api/vendor/onboarding/submit`
- `GET /api/vendor/onboarding/status`
- `POST /api/vendor/onboarding/resubmit`

Return field-level errors with stable field names. The backend should return completion status for each section, missing requirements, review locks, requested corrections and the application reference.

## Type-specific requirements

### Artisan

Required profile information includes legal name, public artisan/group name, operating model, craft tradition, product category, years practising, maker count, preferred language, craft story and workshop location.

Required proof includes identity, artisan/craft/collective proof and workshop address proof. GI authorization becomes required only when the seller makes a protected GI claim.

### Brand

Required profile information includes registered legal name, public brand name, legal structure, registration number, authorized representative, designation, establishment year, team size, public social profile, brand story and registered location.

Required proof includes entity registration, representative identity and authorization to operate the account. Trademark or licensing proof becomes required when the seller claims registered or licensed brand rights.

### Both types

Both paths require PAN, GST status, conditional GSTIN/certificate, beneficiary and bank proof, store assets, product samples, support contact, dispatch location, preparation time, India coverage, manual courier partners, delivery charges, exclusions, customer policies, material/sourcing information, packaging approach and signed declarations.

## Upload security

Client `accept` and file-size checks are usability controls only. The backend must:

- authenticate the vendor and authorize the current application;
- generate random object keys and keep private files outside the public web root;
- verify MIME signature, extension, size, file count, image dimensions and PDF page limits;
- scan files for malware and reject active content;
- strip unsafe metadata where appropriate;
- encrypt sensitive files and use short-lived authorized download URLs;
- log upload, replacement, view and deletion events;
- never expose PAN, bank account or identity numbers in logs, analytics or URLs.

## Authentication security

Use secure, HTTP-only, same-site cookies; CSRF protection; strong password hashing; generic login errors; rate limits; progressive lockout; MFA for sensitive operations; session rotation after login; device/session management; verified email/mobile changes; and immutable security audit events.

The frontend demo uses `sessionStorage` only for `type`, `status`, `step`, `reference` and `submittedAt`. Remove that demo adapter when the API is connected. Passwords, identity details, tax numbers, bank fields and selected documents are never persisted by the frontend.

## Manual delivery boundary

There is no Shiprocket or automatic fulfilment integration. Vendors configure India-wide coverage and charges, select couriers themselves, and manually record dispatch, tracking reference, movement, attempts, exceptions, delivery, return and refund milestones. The backend must validate allowed status transitions and write an audit event for every change.
