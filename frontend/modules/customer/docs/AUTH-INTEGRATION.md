# Customer authentication integration contract

The login and registration screens are complete frontend implementations. Demo mode is enabled by default so the flows can be tested before a backend exists.

## Switch from demo to API

Edit `assets/js/auth-config.js`:

```js
mode: 'api',
apiBaseUrl: 'https://api.example.com'
```

Update the two endpoint paths in the same file if the backend uses different routes. No page markup or interaction code needs to be rewritten.

## Login request

`POST /api/v1/customer/auth/login`

```json
{
  "identifier": "customer@example.com",
  "password": "customer-entered-password",
  "remember": false
}
```

Successful response:

```json
{
  "user": {
    "id": "customer-id",
    "first_name": "Customer",
    "email": "customer@example.com"
  },
  "redirect": "/account"
}
```

## Registration request

`POST /api/v1/customer/auth/register`

```json
{
  "first_name": "Customer",
  "last_name": "Name",
  "email": "customer@example.com",
  "phone": "9876543210",
  "password": "customer-entered-password",
  "terms": true,
  "marketing": false
}
```

Successful response follows the login response shape. The frontend never sends `confirm_password`.

## Validation/error response

Use HTTP `422` for field validation errors and `401` for invalid login. Return generic login messages to avoid account enumeration.

```json
{
  "message": "Please correct the highlighted fields.",
  "errors": {
    "email": ["This email is already registered."],
    "phone": ["Enter a valid Indian mobile number."]
  }
}
```

The frontend automatically places these errors below fields whose keys match form field names.

## Security expectations

- Set authentication through secure, HTTP-only, same-site cookies; do not expose session tokens to browser storage.
- Populate the `csrf-token` meta value or provide an equivalent cookie/header flow.
- Apply server-side validation even though browser validation is present.
- Rate-limit login and registration; use generic authentication failures and audited abuse controls.
- Verify email/mobile ownership and enforce password/recovery/session policies on the backend.
- The demo stores only a non-sensitive example user profile. It never stores a password.
