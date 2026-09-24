# Sign In / Sign Up — Setup Guide

This adds a working, database-backed Sign In / Sign Up system to your existing
site. Your other pages and design are untouched — only `login.html`,
`register.html`, a new `assets/js/auth.js`, and a new `api/` folder were added.

## What was built
- **`api/`** — a small PHP backend (no Composer/dependencies needed):
  - `api/auth/register.php` — create account
  - `api/auth/login.php` — sign in (returns a JWT token)
  - `api/auth/me.php` — get the logged-in user from a token
  - `api/auth/logout.php` — stateless logout endpoint
  - `api/schema.sql` — the MySQL table this backend needs
  - `api/config.php` — **edit this** with your DB credentials
- **`assets/js/auth.js`** — connects the login/register forms to the API,
  handles validation errors, loading states, and redirects to
  `customer/dashboard.html` on success.
- **`login.html` / `register.html`** — same design as before, now wrapped in
  real `<form>`s with working submit handling.

## 1. Create the database (via phpMyAdmin)
1. Open phpMyAdmin.
2. Go to the **Import** tab.
3. Choose the file `api/schema.sql` from this project.
4. Click **Go**.

This creates a database called `conscious_indi` with one `users` table
(id, first_name, last_name, email, mobile, password_hash, created_at).

*(Prefer the command line? `mysql -u root -p < api/schema.sql`)*

## 2. Configure database credentials
Open `api/config.php` and set these to match your MySQL/phpMyAdmin setup:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'conscious_indi');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
```

Also change `JWT_SECRET` in the same file to a long random string —
this is what signs login tokens, so keep it private and don't reuse the
default in production.

## 3. Run it
Any standard PHP hosting (Apache/Nginx + PHP 7.4+) works, since there are
no Composer dependencies. For local testing you can also use PHP's built-in
server from the project root:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/login.html` or `.../register.html`.

If your API ever ends up on a **different domain** than the frontend, open
`assets/js/auth.js` and change:
```js
const API_BASE = "/api";
```
to your API's full URL, and set `CORS_ORIGIN` in `api/config.php` to your
frontend's URL.

## How it works
- Passwords are hashed with bcrypt (`password_hash`/`password_verify`) —
  never stored in plain text.
- On successful login/register, the API returns a JWT token. The frontend
  stores it in `localStorage` (`ci_token`) and sends it as
  `Authorization: Bearer <token>` on any request that needs to know who's
  logged in (see `api/auth/me.php`).
- "Keep me signed in" on the login form issues a 30-day token instead of
  the default 1-day token (see `JWT_TTL` / `JWT_TTL_REMEMBER` in
  `api/config.php`).
- Login is rate-limited per email (10 attempts / 15 minutes) to slow down
  brute-force attempts.

## Protecting other pages (optional next step)
Right now, pages like `customer/dashboard.html` are static HTML and don't
check login state. To require login, add this near the top of any page you
want to protect:

```html
<script src="assets/js/auth.js"></script>
<script>
  if (!window.ConsciousAuth.isLoggedIn()) {
    window.location.href = "login.html";
  }
</script>
```

And to add a "Log out" button anywhere:
```html
<button onclick="ConsciousAuth.logout()">Log Out</button>
```

## Tested
Registration, duplicate-email rejection, wrong-password rejection, correct
login + token issuance, `/me` with a valid/invalid/missing token, and
logout were all verified end-to-end against a real MySQL database before
delivery.
