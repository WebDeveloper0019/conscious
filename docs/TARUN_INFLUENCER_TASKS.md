# Influencer handoff

- Branch from the updated `main` as `tarun/influencer`. Preserve Git history and submit a pull request.
- Extend `frontend/modules/influencer/` and `api/influencer/`. Preserve the public visual design of `creators.html` and `influencer-profile.html`; bind their content to database records.
- Add profile photo, cover, niches, city/state, languages, social accounts, moderated portfolio media and service/package data. Public profiles without a valid handle must show a useful unavailable/directory state instead of static sample content.
- Complete profile editing and verification states: draft, pending review, changes requested, rejected and approved. Show admin feedback next to the affected fields/documents and preserve private document access checks.
- Finish campaign tools around the existing create/assign/pause/resume flow. Add controlled edits to dates and commercial terms without changing historical conversion calculations. Verify referral destinations, copy-link actions and required disclosures.
- Verify attribution and commissions for online and COD orders, unpaid/failed payments, manual delivery, cancellations, partial/full refunds and return holds. Prevent self-referrals, duplicate conversions and duplicate ledger entries.
- Reconcile pending, eligible, held, requested, rejected, failed and paid amounts. Keep payout details masked and preserve manual payout references and admin audit history.
- Complete date/campaign analytics filters and exports using database records. Provide responsive desktop/mobile layouts and visible field validation, loading, empty, error and success states.
- Add ownership, CSRF, session-expiry, upload, attribution and withdrawal regression tests. Use isolated test data and local-only demo accounts.
- Deliver new migrations, endpoint documentation, a test report and a step-by-step demo walkthrough. Use installation-relative URLs that work in a subfolder and on Hostinger.

Existing foundations already include creator authentication, application/document APIs, campaign links, curated products, inquiries, analytics, notifications and withdrawal APIs. Review and extend these implementations instead of creating parallel versions.
