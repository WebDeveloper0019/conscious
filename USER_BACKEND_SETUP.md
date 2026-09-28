# Conscious Indi customer backend setup

This is the cleaned customer backend integrated with the current PHP frontend. It does not include Tarun's duplicate `(2)` files or overwrite the new UI.

## What is included

- Customer registration with email OTP verification and resend limits
- Login by email or Indian mobile number
- Secure HTTP-only authentication cookie; tokens are not stored in browser storage
- CSRF protection on authenticated changes
- Profile and password APIs
- Saved addresses, preferences and wishlist APIs
- Read-only customer order, order-detail and dashboard APIs
- Prepared SQL statements and per-user record ownership checks

## Local setup

1. Start Apache and MySQL in XAMPP.
2. Import `api/schema.sql` into MySQL. It contains the complete fresh schema.
3. Copy `api/.env.example` to `api/.env`.
4. Use `APP_ENV=local` locally and add your database values.
5. Add a Resend API key and a verified sender address to test real OTP email.
6. Open `frontend/modules/customer/assets/js/auth-config.js` and change `mode: 'demo'` to `mode: 'api'` when the database and email settings are ready.

Local login URL:

`http://localhost/conscious/frontend/modules/customer/auth/login.php`

The URLs are relative, so no folder name is hard-coded. The same code works when the project is uploaded to a Hostinger domain or subfolder.

## Production checklist

- Set `APP_ENV=production`.
- Generate a unique random `JWT_SECRET` of at least 32 characters.
- Use a dedicated database user rather than MySQL root.
- Keep `api/.env` outside Git; it is ignored.
- Use HTTPS. Secure cookies are mandatory in production mode.
- Leave `CORS_ORIGIN` blank for same-origin hosting. If the API is on a different domain, set one exact HTTPS origin—never `*`.
- Use a Resend sender verified for the production domain.

## API endpoints

- `POST api/auth/register.php`
- `POST api/auth/verify-otp.php`
- `POST api/auth/resend-otp.php`
- `POST api/auth/login.php`
- `GET api/auth/me.php`
- `POST api/auth/logout.php`
- `GET/PUT api/user/profile.php`
- `POST api/user/password.php`
- `GET/POST/PATCH/DELETE api/user/addresses.php`
- `GET/PUT api/user/preferences.php`
- `GET/POST/DELETE api/user/wishlist.php`
- `GET api/user/orders.php`
- `GET api/user/order-details.php`
- `GET api/user/dashboard.php`

Order creation/payment is intentionally not implemented here. The order endpoints expose only orders already created by the future checkout/payment workflow.
