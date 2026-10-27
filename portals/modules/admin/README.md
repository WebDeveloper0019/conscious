# Conscious India admin frontend coverage

This module is still largely a sample-data administrative workspace. Sign-in and MFA now call the backend, and workspace pages require an active admin session. Most operational forms and buttons are not yet connected to their backend APIs; do not treat displayed approvals, refunds, payouts, or reports as live data. Every sensitive action must be connected to an authorised endpoint with server-side validation, access control, CSRF protection, rate limiting and audit records.

## Legacy screen coverage

All 30 legacy admin screen areas remain represented, grouped into maintainable operational workspaces:

| New workspace | Legacy areas covered |
|---|---|
| Dashboard | `dashboard` |
| Work queue | Cross-functional approvals and escalations added as a missing control |
| Vendors | `vendors`, `vendor-verification`, `certification-management` |
| Catalog | `product-approval`, `categories`, `sub-categories` |
| Orders | `orders` plus manual-delivery oversight |
| Returns | `returns`, `refunds` |
| Customers | `customers` |
| Influencers | `influencers` |
| Events | `all-events`, `add-event`, `event-management`, `manage-events`, `booking-requests` |
| Marketing | `coupons` |
| Finance | `revenue`, `payouts`, `commissions` |
| Reports | `reports` |
| Content | `cms-pages`, `blog-management`, `banners` |
| Moderation | `reviews` |
| Support | `support` |
| Access | `role-permissions` |
| Audit | `audit-logs` |
| Settings | `settings` |

## Added missing frontend coverage

- Secure administrator sign-in, MFA verification and account-recovery screens.
- Unified priority/SLA/ownership work queue.
- Maker/checker financial approvals and reconciliation visibility.
- Manual vendor-delivery exception oversight without Shiprocket or automatic courier booking.
- Sustainability evidence, expiry, public-claim and greenwashing review controls.
- Masked sensitive customer/vendor data and purpose-limited access messaging.
- Multi-vendor order visibility, disputes, policy exceptions and partial refunds.
- Influencer attribution, disclosure, fraud and earnings visibility.
- Maintenance/emergency controls, access review and session governance concepts.

## Routes

Operational entry: `index.php`

Authentication entry: `auth/login.php`

The UI uses Conscious India's existing typography and colour system without changing the public website. Authentication is live; most other forms currently validate in the browser and stop at a backend-ready confirmation.
