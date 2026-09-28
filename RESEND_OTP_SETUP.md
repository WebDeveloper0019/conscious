# Resend Email OTP Setup

## 1. Database
Import `api/migration-email-otp.sql` in phpMyAdmin after your existing `conscious_indi` database is selected.

## 2. API key
Open `api/.env` and replace:
`RESEND_API_KEY=PUT_YOUR_RESEND_API_KEY_HERE`
with your real Resend key. Do NOT commit `api/.env`. It is ignored by Git.

For initial Resend testing, keep:
`RESEND_FROM=onboarding@resend.dev`

When Resend has verified your sending domain/address, change `RESEND_FROM` to the approved sender.

## 3. Local URL
Use:
`http://localhost/conscious-tarun-development1/register.html`

Registration now creates an unverified account and sends a 6-digit OTP. The user must verify it before login.

## 4. Flow
Register -> Resend OTP email -> `otp-verification.html` -> verify -> login/dashboard.

OTP expires after 10 minutes, is stored as a hash, allows 5 incorrect attempts, and resend is limited to once per 60 seconds.

## 5. Important
Never put the Resend API key in JavaScript, HTML, GitHub, or a public repository. Keep it server-side in `api/.env`.
