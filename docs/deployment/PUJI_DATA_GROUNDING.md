# Puji database answers

Puji's existing `api/ai/chat.php` endpoint now routes current product, event, site-navigation, and customer-order questions before the general Cloudflare AI call. The public answers are assembled from database records on every request; no product, event, price, stock, or order fixtures are embedded in JavaScript.

## Data boundaries

- Products follow the public catalog's visibility rule: published snapshot present, no suspended or archived product, active vendor, approved vendor in production. The shown name and price come from the published snapshot. Stock is active-variant quantity minus reserved quantity. Image IDs come only from clean, public, non-deleted file assets.
- Events must be published and not ended. Ticket availability checks the event booking window, ticket sales window, active ticket type, and remaining capacity. The event page remains the final booking source.
- Order questions use the existing customer session and CSRF validation. Queries include `orders.user_id = authenticated_customer_id`; only that customer's recent orders and shipment status are returned. No order, address, email, phone, or tracking data is sent to Cloudflare.
- General conversation continues through the existing configured AI provider. Database text is never inserted into the model prompt, preventing vendor or event descriptions from acting as instructions to the model.
- Site guidance links to current public pages. Public Puji does not expose vendor, influencer, or administrator private records.

Responses include a short `reply` plus a bounded list of same-origin `results` cards or links. The browser uses `textContent` for record names and validates same-origin URLs before rendering links and images. Every message retains the existing 20-per-hour IP rate limit. Exact checkout prices and availability are recalculated by the commerce flow.

## Verify before deployment

```powershell
php tests/ai-grounding-smoke.php
php -l api/lib/ai-grounding.php
php -l api/ai/chat.php
node --check assets/js/ai-assistant.js
php scripts/build-production.php --hostinger-fixed-root
php scripts/verify-production-release.php
```

Test the public widget with **Find products**, **Upcoming events**, **Track an order** while signed out, a signed-in customer's own order, and a different customer's order number. Press Enter in the input and verify that no unrelated required-field toast appears. An empty production catalog or event list correctly returns a browse link; it does not invent listings.

The Hostinger update needs the normalized release's public `assets/js/ai-assistant.js` and `includes/public-header.php`, plus private `backend/ai/chat.php` and the new `backend/lib/ai-grounding.php`. Existing Cloudflare `.env` settings remain unchanged. Back up the files being replaced before extracting the update.
