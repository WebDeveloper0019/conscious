# Conscious Indi frontend architecture

The `frontend` directory is the maintainable frontend application. It contains no database, payment execution, API implementation, production authentication, or server-side business persistence.

## Modules

- `modules/storefront`
- `modules/customer`
- `modules/vendor`
- `modules/influencer`
- `modules/admin`
- `modules/events`

Each module owns its pages, components, styles, scripts, non-sensitive mock data, and documentation. Vendor is implemented first; the remaining modules will follow the same conventions.

## Shared frontend

`shared` contains cross-module design tokens, accessible components, browser validation helpers, utilities, and non-sensitive mock data. Module-specific code must not be placed in shared folders.

## Future backend boundary

The backend must remain a separate application or directory and expose authenticated APIs. It must repeat every authorization, validation, pricing, upload, status-transition, and audit check. Browser validation is for user experience and is never a security boundary.

## Security rules

- Never store passwords, KYC documents, bank data, customer addresses, full customer phone numbers, tokens, payment details, or production permissions in local storage.
- Render user-provided text with `textContent`, not unsafe HTML.
- Restrict and validate file type, size, and count in the frontend; the backend must validate again.
- Use allowlisted statuses and transitions.
- Do not expose admin-only or cross-vendor data.
- All production mutations will require authenticated API calls and server-issued anti-forgery protection.
