# Conscious Indi — User-Side Backend

This update keeps the existing frontend structure and extends the existing PHP + MySQL + JWT backend for the customer/user portal.

## Included

### Authentication
- Existing registration/login/logout/me endpoints retained.
- JWT authentication protects customer APIs.
- Passwords use `password_hash()` / `password_verify()`.

### Customer APIs
- `GET/PUT api/user/profile.php` — profile data
- `POST api/user/password.php` — change password
- `GET/POST/PATCH/DELETE api/user/addresses.php` — saved addresses
- `GET/PUT api/user/preferences.php` — notification preferences
- `GET/POST/DELETE api/user/wishlist.php` — wishlist persistence
- `GET api/user/orders.php` — authenticated user's orders
- `GET api/user/order-details.php` — authenticated order details
- `GET api/user/dashboard.php` — dashboard counters

## Database

For a new database, import:

`api/schema.sql`

If the project is already using the original `users` table, run once:

`api/migration-user-backend.sql`

The migration adds customer profile fields and creates the user-side tables.

## Frontend integration

`assets/js/customer-backend.js` connects the customer profile, password form, address form/list, settings preferences, orders table and dashboard counters to the APIs.

The existing customer UI/design is retained.

## Local XAMPP setup

1. Start Apache and MySQL.
2. Open phpMyAdmin.
3. Import `api/schema.sql` for a fresh database, or `api/migration-user-backend.sql` for an existing project database.
4. Check `api/config.php`:
   - DB_HOST
   - DB_PORT
   - DB_NAME
   - DB_USER
   - DB_PASS
   - JWT_SECRET
5. Change `JWT_SECRET` before production deployment.
6. Open the site through Apache, not by double-clicking HTML files.

Example local URL:

`http://localhost/conscious-tarun-development1/login`

## Important

The order API is read-only at this stage because this project does not yet contain a complete checkout/order-creation backend. Orders can be populated by the future checkout/payment backend or by the admin/order workflow.
