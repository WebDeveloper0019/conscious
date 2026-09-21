# Customer events integration contract

The customer Events & Bookings frontend includes discovery filters, event cards, ticket selection, attendee/accessibility fields, live price calculation, validation, demo confirmation and booking management UI.

## Enable the booking API

In `assets/js/experience-config.js`, change `mode` from `demo` to `api` and set `apiBaseUrl`. The booking screen will then submit to `endpoints.eventBooking`.

## Create booking

`POST /api/v1/customer/event-bookings`

```json
{
  "event_title": "Blue Pottery: Shape, Paint & Glaze",
  "ticket_type": "standard",
  "quantity": 2,
  "attendee_name": "Rahul Sharma",
  "phone": "9876543210",
  "accessibility_request": "Optional request",
  "booking_policy": true
}
```

Successful response:

```json
{
  "booking": {
    "reference": "EV-29018",
    "status": "confirmed",
    "total": 1398
  },
  "message": "Booking confirmed"
}
```

Return HTTP `422` with `message` and `errors` for validation failures, `409` when seats are no longer available and `401` when the customer session has expired.

## Event listing

`GET /api/v1/events` should support `q`, `city`, `date`, `type`, `page` and `per_page`. Each event should provide its id, slug, title, type, location/online mode, timezone-aware sessions, ticket types, remaining capacity, host, accessibility, inclusions, cancellation policy and approved media.

The current cards are non-sensitive mock data. Backend integration can replace the card collection with server-rendered PHP data or hydrate it from this endpoint while keeping the completed card and booking components.

## Security and pricing

- The backend must calculate ticket availability, price, taxes, discounts and final payable total again.
- Do not trust the frontend event title, price or capacity.
- Use secure authenticated cookies and CSRF protection.
- Create payment orders only after the backend reserves capacity.
- Use idempotency keys to prevent duplicate bookings.
- Do not store attendee phone numbers or payment details in browser storage.
