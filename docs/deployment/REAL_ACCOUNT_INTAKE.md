# Production account intake

The production database can hold working customer and vendor accounts, but an account belongs to an actual person only when that person supplies or approves their own contact details. Temporary email is suitable only if that person controls the mailbox for verification and future password recovery. Do not import invented people as verified customers or sellers.

## Ten customers

For each person collect: full name, unique email they control, unique Indian mobile number, and consent to create the account. The person should choose their own password through the registration or reset flow. Address, birthday, marketing consent, wishlist, and order history are optional and should not be invented. Do not create orders or reviews merely to make the site appear active.

## Five artisans and five brands

For each seller collect: account owner name, seller type, public store name, unique owner email and mobile, legal name, registered/pickup address, city, state, PIN, craft or product category, maker/brand story, support contact, fulfilment contact, and approved store photos/logo. Each owner should control the email and choose a password.

The normal onboarding flow also requests the type-specific identity/business documents, tax details, bank details, shipping coverage and rates, return terms, and sustainability claims or certificates where relevant. These must come from the owner. Uploaded documents remain pending until an administrator reviews the real documents. A vendor must not be marked approved from a name, stock photo, or temporary mailbox alone.

## Products for each approved seller

For every listing obtain the exact product name and description; category and materials; origin; seller-owned or licensed image files and permission to publish; SKU and variants; selling price and MRP; GST rate; actual available stock; packed weight and dimensions; dispatch time; India-wide or restricted shipping coverage and charge; returns policy; and the seller's ability to fulfil an order. The seller submits the product, and an administrator approves its published version. Stock and price are checked at checkout.

## Credentials and handoff

Never send passwords in chat or commit them to Git. Use unique temporary passwords only for a controlled handoff, then require each owner to replace theirs. Keep any one-time credential list outside the public web root with restricted access. Rotate the administrator password immediately if it has been shared in chat. The private `backend/bin/rotate-admin-password.php` command accepts a new password through standard input, revokes existing sessions, and invalidates outstanding reset and MFA challenges.
