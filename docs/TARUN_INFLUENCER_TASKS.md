# Tarun: finish the influencer marketplace

Start a new branch from the latest `main`, keep the existing Git history, and submit a PR. Work in the PHP `frontend/` modules and the existing `api/` structure. Keep the public look of `creators.html` and `influencer-profile.html` and the shared Conscious Indi visual language. Do not create another login, signup, session, or onboarding implementation. Those flows already have an HTTP regression suite in `tests/influencer-auth-onboarding-e2e.php`; run it and only fix regressions you discover.

## Creator Studio and public profiles

- Finish the existing Creator Studio dashboard with live database values for overview, campaigns, curated products, analytics, earnings, withdrawals, notifications, inquiries and settings. Make every section reachable on desktop and mobile, with understandable loading, empty, error and success states.
- Add moderated profile photo, cover and portfolio media, services and packages without undoing the existing bio, niches, languages, Indian city/state, social accounts, private documents, application review and payout details. Keep private files private, scan uploads, and show only approved public media.
- Bind `creators.html` and `influencer-profile.html` to approved creators. Remove static people, product and package data from the real listing. Show valid unavailable states for unknown handles and hide withdrawn products and paused profiles.
- Keep partnership inquiries tied to the correct creator. The creator can read and close them; admin can see contact history and resolve abuse or disputes.

## Influencer service packages

- Let an approved influencer create, edit, pause and archive package drafts in Creator Studio. A package needs a clear title, category/niche, target platforms, description, deliverables and quantity (for example reels, posts, stories, videos or event coverage), expected turnaround, revision count, usage rights, what the vendor must provide, availability/capacity, price in INR, tax treatment and cancellation/refund terms. Validate all fields on both frontend and backend.
- Send a new or materially changed package for admin review. Only approved, active packages from approved influencers appear publicly or to vendors. Keep a versioned snapshot of price, deliverables and terms when a vendor purchases, so later edits do not rewrite an existing order.
- Show real packages on the creator's public profile and in a vendor discovery page within `frontend/modules/vendor/`. Vendors should be able to search/filter by niche, platform, location, audience and price; compare packages; see inclusions, exclusions, creator credibility and current availability; then open a detailed package page.

## Vendor purchase and admin coordination

- Only an authenticated, eligible vendor may buy a package. Show a clear review screen with the influencer, deliverables, timeline, total price, taxes, cancellation terms and any vendor brief before payment.
- Use Razorpay for this service purchase and verify payment server-side. COD is for physical product delivery, so it should not be offered for an influencer service. Protect against duplicate payment callbacks and double purchases with idempotency and capacity checks. Do not mark a booking confirmed from a frontend success screen alone.
- Save the vendor's brand/store, contact person, campaign brief, target product(s), requested dates, selected package version, amount, payment, invoice and order status in the database. Never store only a browser-side booking or hardcoded package.
- After verified payment, create an admin task and notifications so admin can contact the influencer and vendor. Admin must see both parties' details, package terms and brief; record outreach, response, agreed schedule and notes; and move the job through paid, contact pending, accepted/scheduled, in progress, delivered, revision requested, completed, cancelled or disputed as appropriate.
- Give the vendor a collaboration/order page showing status, timeline, admin updates, deliverables, messages or notes allowed by the business flow, invoice, and support/refund request. Give the influencer a matching assigned-work view. Admin remains responsible for contact and coordination; do not silently auto-accept work for the influencer.
- Enforce cancellation/refund rules, failed payments, missed deadlines and disputes. Calculate the influencer's eligible earnings only from the correct completed paid amount, after any hold/refund rules; reconcile admin records and vendor-facing totals.

## Campaign referrals and finance

- Complete controlled campaign create/assign/pause/resume and edits to dates or commercial terms. Preserve historical terms for clicks, conversions and commissions that already occurred.
- Verify referral links, destinations, copy actions, disclosures and attribution for Razorpay and COD product orders, manual vendor delivery, unpaid/failed orders, cancellations, returns and partial/full refunds. Prevent self-referrals, duplicate conversions and duplicate ledger entries.
- Reconcile creator balances across pending, eligible, held, requested, rejected, failed and paid states. Keep payout details masked. Admin records real manual transfer references and audit history; the app does not claim to send bank transfers automatically.
- Complete date/campaign analytics and exports from database records. Show the same numbers in creator and admin views.

## Security, tests and handoff

- Enforce creator/vendor/admin ownership and permissions on every route. Apply CSRF to mutations, rate limits where appropriate, safe upload rules, server-side amount calculations, idempotency, and clear validation messages. Never use client-submitted price or package ownership as authority.
- Add migrations for package catalogue, version snapshots, service orders, payments, admin contact workflow, deliverables, disputes and ledger links. Do not alter previously applied migration files.
- Add end-to-end tests for package approval, vendor purchase, Razorpay test payment, duplicate callback, admin contact, completion, cancellation/refund and wrong-account access. Run the existing auth/onboarding suite as a regression check.
- Provide 10 distinct local-only demo creators and realistic database packages so we can demonstrate discovery and purchase. Keep demo credentials out of public pages and production seed paths.
- Verify installation-relative URLs under `/conscious/` and a Hostinger subfolder. Supply migration/configuration notes, exact test commands, a step-by-step demo walkthrough, screenshots of desktop/mobile states, and a PR summary of remaining limitations.

Acceptance: a verified vendor can choose and pay for a real creator package, both parties can see the same database-backed job, admin receives a clear contact task and can coordinate the work, and payment, refund and creator earnings remain consistent. A visible button or static card alone is not complete.
