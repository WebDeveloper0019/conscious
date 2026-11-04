# Initial database content and account setup

## What database creation does today

Run the private `backend/bin/migrate.php` command after creating the Hostinger database. It creates the schema, roles, permissions, platform settings, and reference data. It does **not** create real customers, vendors, creators, products, stock, or an administrator.

The existing `seed-admin-demo.php`, `seed-marketplace-showcase.php`, `seed-influencer-showcase.php`, and `seed-shop-demo.php` commands are deliberately **local-only**. They use fictional names and `.test` emails. Production excludes `demo_fixture` vendors and creators from public listings and vendor access. Do not change their environment check or mark these accounts `approved` without real onboarding and verification.

## Existing product images

Eight product photographs are bundled in `portals/modules/customer/assets/images/products/`: blue pottery vase, Dhokra musician, Kantha cushion, Kundan necklace, Madhubani painting, Pashmina shawl, sandalwood box, and Warli wall art. The local shop seeder creates 15 product records by reusing these eight photos. The photographs are real image files, but the repository alone does not prove product ownership, current price, stock, or permission to sell each item.

## Production-safe order of work

1. Create the Hostinger database and a dedicated non-root database user. Configure the private `.env` and run `backend/bin/migrate.php`.
2. Create one administrator with a **unique** email and strong, unique password using `backend/bin/create-admin.php --password-stdin EMAIL NAME`. Supply the password through standard input after entering it privately in the SSH shell; do not place it in command arguments, Git, or a public folder. Never use the hard-coded local demo administrator password on production.
3. Create the actual customer, vendor, and influencer accounts through their registration/onboarding flows, or a one-time private import using their real emails and owner-approved details. Keep temporary accounts pending until their email and required documents are verified. Each account needs a unique password and must change it on handoff.
4. Import products only after the selling vendor confirms the image rights, exact item, selling price, tax, available stock, shipping coverage, returns policy, and ability to fulfil paid orders. Complete the normal product approval workflow before publication.
5. Configure Razorpay and verify signed payments and refunds before accepting real orders. The local mock payment confirmation is unavailable in production.

## Why changing only emails is insufficient

The current administrator APIs list accounts and change some statuses, but they do **not** provide a general form to change customer, vendor, or influencer emails. Changing an email also requires resetting email verification and revoking sessions until the new address is confirmed. Real vendors and influencers additionally need onboarding documents and review; stock and product rights cannot be derived from a random name or photograph.

For a client demonstration, the local-only seeders can create 10 fictional vendors, 10 fictional customers, 10 fictional creators, one demo admin, and 15 catalogue records with actual project photographs in an isolated **local** database. They must remain clearly identified as sample data and cannot be converted into real verified production partners by changing emails alone.
