# Vendor frontend validation and security boundary

## Implemented frontend checks

- Required fields, length limits, number ranges, URL/email patterns, Indian mobile format, six-digit PIN format, GSTIN/IFSC patterns, date/file inputs, explicit button types, unique element IDs, named controls, and accessible labels.
- Product selling price cannot exceed MRP.
- Image/document inputs restrict visible file types and client-side size.
- Delivery rate amounts, payout ranges, stock changes, COD amounts, package counts, and processing times use bounded numeric controls.
- User-facing notifications use text content rather than interpolated HTML.
- Browser demo storage is allowlisted and excludes passwords, KYC files, bank details, customer addresses, full customer phone numbers, payment information, permissions, and authentication state.

## Mandatory backend repetition

The separate backend must repeat and strengthen every validation. It must authenticate the vendor, authorize ownership and staff permissions, issue anti-forgery protection, enforce rate limits and state transitions, sanitize and scan uploads, recalculate prices and taxes, mask personal data, write immutable audits, and reject client-controlled financial or permission values.

## Upload requirements for backend integration

Verify MIME signature and extension, randomize stored filenames, keep private documents outside the public web root, scan files, limit dimensions/pages/count/size, strip dangerous metadata where appropriate, require authorization for every download, and log all access.

## Personal-data rule

Only the minimum information required for fulfilment may be shown. Customer data must be permission-scoped, masked when possible, hidden after retention periods, and never placed in local storage, URLs, analytics payloads, or downloadable frontend caches.
