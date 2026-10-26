# Conscious India backend implementation plan

## Scope and non-negotiable rules

The backend covers the existing customer, brand vendor, artisan vendor, admin, event and influencer experiences. Existing public and dashboard design remains intact. Browser validation is repeated server-side. All sensitive operations use authenticated, HTTP-only sessions, CSRF protection, ownership checks, rate limits and immutable audit records.

Delivery is fully manual. There is no Shiprocket or automatic fulfilment dependency. Vendors define India-wide/state/city/PIN coverage and charges, choose couriers themselves, and manually record tracking and every delivery milestone.

Money, stock, commission, settlement, payout, product approval, vendor approval, refunds and permissions are always server-owned values.

## Current baseline

- Customer registration, email OTP, login and basic customer endpoints exist.
- Vendor, admin, events and influencer modules are frontend-only.
- Current database schema is not sufficient for the complete marketplace.
- Existing mock data must remain available only in explicit demo mode until each screen is connected.

## Delivery sequence

### 1. Platform foundation

- Environment/config validation and production-safe error handling.
- Versioned database migrations and migration runner.
- Opaque revocable sessions for customer, vendor, admin and influencer identities.
- CSRF, rate limiting, validation, authorization and role/permission middleware.
- Secure upload service with private storage, MIME/size/dimension rules and authorized downloads.
- Notifications, audit log, status history and idempotency primitives.
- Automated database, authorization, validation and status-transition tests.

Exit criteria: no privileged endpoint can run without an authenticated principal, permission and CSRF check; sessions can be revoked; sensitive files are not publicly addressable.

### 2. Vendor application, approval and activation

- Brand and artisan registration/application APIs.
- Type-specific fields, consent versions and duplicate prevention.
- PAN/GST, identity, artisan/entity, authorization and address documents.
- Admin application queue, document verification, notes, change requests, rejection and approval.
- Approval creates an activatable vendor account; it never activates an unverified applicant automatically.
- Email/contact verification, password recovery and account-status enforcement.

Exit criteria: both brand and artisan flows work from application through admin approval and first secure login.

### 3. Vendor onboarding, store and India-wide manual delivery settings

- Draft autosave and section completion rules.
- Legal/profile, location, category, bank, policies and impact declarations.
- Unique store handle/slug, public profile and branding assets.
- Dispatch address, processing time, courier list, India/state/city/PIN coverage, exclusions, free-shipping threshold, flat/weight/order-value rate slabs and COD rules.
- Admin-controlled onboarding review and selling capability gate.

Exit criteria: an approved vendor cannot sell until all required type-specific onboarding sections are complete and reviewed.

### 4. Catalogue, sustainability approval and inventory

- Categories/subcategories and admin catalogue controls.
- Product, variants, images, attributes, package data and SEO fields.
- Draft/version/review/changes-requested/approved lifecycle.
- Evidence-backed sustainability/GI/certificate claims with expiry and review history.
- Inventory stock, reserved, available, low-stock threshold and append-only adjustments.
- Transaction locks prevent overselling and negative stock.

Exit criteria: only approved active products from active vendors appear publicly; material edits require a new review version.

### 5. Customer commerce, checkout and multi-vendor orders

- Public catalogue/search/filter/vendor profile APIs.
- Server-owned cart, wishlist and saved vendors.
- Address serviceability and vendor shipping-price calculation for all India rules.
- Coupon validation, tax/price calculation and immutable checkout quote.
- Payment-order boundary with idempotency and webhook-ready state handling.
- One customer order split into vendor order groups and vendor-owned items/packages.
- Inventory reserve/confirm/release transactions.

Exit criteria: one checkout can safely contain several vendors while each vendor sees and controls only its own items.

### 6. Manual fulfilment, delivery tracking and customer visibility

- Item acceptance, processing, quality check, packing and ready-to-dispatch states.
- Manual courier, AWB/reference, shipping charge, package and evidence entry by vendor.
- Dispatched, picked up, in transit, destination, out for delivery, delivered, attempt, failed and return-to-vendor milestones.
- Every transition validates its allowed predecessor and records actor, timestamp, location, note and evidence.
- Customer tracking timeline and admin delivery-exception oversight.

Exit criteria: delivery works end-to-end without any aggregator and invalid/backdated/cross-vendor transitions are rejected.

### 7. Cancellations, returns, replacements and refunds

- Eligibility and policy snapshots from purchase time.
- Customer request, vendor review, admin/payment decision and evidence threads.
- Partial item quantities, return shipment, inspection, dispute and replacement order flows.
- Refund recommendation separated from payment execution.
- Inventory and ledger reversal rules are transactional and auditable.

Exit criteria: every partial and full outcome reconciles inventory, order status, vendor earnings and customer totals.

### 8. Commission, earnings, bank accounts and payouts

- Admin-controlled category/vendor commission rules with effective dates.
- Immutable order-item commission and tax/deduction snapshots.
- Double-entry-style vendor ledger for pending, eligible, held, paid, reversed and refunded values.
- Encrypted/protected bank details, masked responses and verification states.
- Vendor payout requests plus maker/checker admin approval and reconciliation.

Exit criteria: balances are derived from ledger entries, never accepted from browser values, and payout totals reconcile exactly.

### 9. Coupons, reviews, certificates, notifications and support

- Vendor-owned coupon CRUD and restrictions.
- Verified-purchase reviews, vendor replies and admin moderation.
- Vendor certificates and admin verification/expiry.
- Persistent read/unread notifications for all workflow events.
- Customer/vendor support tickets, messages, attachments, assignment, SLA, priority and status.

Exit criteria: ownership, visibility and moderation boundaries are enforced for every record.

### 10. Events and bookings

- Event host/application, venue/session, ticket type, capacity and media management.
- Admin approval and publishing.
- Customer discovery, transactional seat reservation, server-side pricing, booking and cancellation.
- Vendor/exhibitor event applications, payment-pending/confirmed/completed flow.

Exit criteria: capacity cannot oversell and duplicate submissions/payments are prevented with idempotency.

### 11. Influencer programme

- Influencer application, verification, profile and secure authentication.
- Campaign/product eligibility, disclosure requirements and referral links.
- Click and conversion attribution with fraud signals and configurable windows.
- Commission ledger, withdrawals, admin review and reports.

Exit criteria: conversions are deduplicated, self-referrals/fraud are flagged and earnings reconcile to eligible orders.

### 12. Admin operations, content and reporting

- RBAC roles/permissions, MFA-ready sensitive actions and active-session controls.
- Unified work queue for vendor/product/claim/event/payout/refund/support approvals.
- Customer/vendor/influencer management and purpose-limited sensitive-data access.
- CMS pages, banners, blog, announcements and platform settings.
- Real dashboards, analytics, CSV exports and reconciliation reports.
- Searchable immutable audit records.

Exit criteria: every existing admin workspace is backed by real authorized data and no operational card retains mock values.

### 13. Frontend integration and complete QA

- Replace each demo adapter with a versioned API adapter without changing visual design.
- Field-level validation/error mapping, loading, empty, permission and failure states.
- Desktop/mobile regression tests and route/link checks.
- End-to-end role matrix, IDOR, CSRF, upload, injection, XSS and status-transition tests.
- Fresh-install and upgrade migrations, backups, deployment runbook and Hostinger configuration.

Exit criteria: brand, artisan, customer, admin, event and influencer workflows pass end-to-end tests; no mock operational data remains outside explicit demo mode.

## Shared status ownership

- Vendor application: `draft → submitted → under_review → changes_requested/resubmitted → approved/rejected`.
- Vendor account: `pending_activation → active → restricted/suspended/blocked/inactive`.
- Product: `draft → submitted → under_review → changes_requested/approved/rejected → active/paused/out_of_stock/suspended/archived`.
- Fulfilment and manual delivery use item/package-level transitions from awaiting acceptance through delivered, delivery exception or returned-to-vendor.
- Refund and payout execution always require admin/payment authority; vendors can only recommend or request.

## Test gates applied to every milestone

1. Happy path.
2. Required-field and boundary validation.
3. Authentication and CSRF.
4. Role and ownership/IDOR denial.
5. Invalid status transition denial.
6. Duplicate/idempotency handling.
7. Transaction rollback and concurrency where stock or money changes.
8. Audit and notification creation.
9. No sensitive data in response, URL, log or browser storage.
10. PHP syntax, database migration and browser integration regression.
