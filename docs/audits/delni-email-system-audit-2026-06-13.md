# Delni Email System Audit - 2026-06-13

## Verdict

Email is not fully production-ready yet.

The Laravel mailables and views render successfully, the queue tables exist, and the queue is empty. The blocking failures are SMTP configuration and provider credentials/domain setup.

## Root Cause

Two real failures were found in `storage/logs/laravel.log`:

1. TLS/port mismatch:
   `ssl://smtp-relay.brevo.com:587` failed with OpenSSL `wrong version number`.
   Port `587` must use SMTP with STARTTLS, not implicit SSL.

2. SMTP authentication failure:
   Brevo returned `535 5.7.8 Authentication failed`.
   Recent failures used the sender address as the SMTP username. Brevo SMTP requires the active SMTP login/key pair from Brevo, not the From address unless Brevo explicitly issued that as the SMTP login.

Older logs also showed an already-resolved mailable hydration error:
`Undefined property: App\Mail\SetPasswordMail::$text`.

## Code Status

Mailables:

- `App\Mail\SetPasswordMail` implements `ShouldQueue`, uses `emails.set-password`, and has serializable constructor data.
- `App\Mail\PasswordResetMail` implements `ShouldQueue`, uses `emails.password-reset`, and has serializable constructor data.
- Both mailables rendered successfully through Laravel's `array` mailer.

Routes:

- `onboarding.show` exists.
- `onboarding.set-password` exists.
- `password.reset` exists.
- `password.email` exists.
- `password.update` exists.

Queue:

- `QUEUE_CONNECTION=database`
- `jobs` table exists and has `0` jobs.
- `failed_jobs` table exists and has `0` jobs.
- `job_batches` table exists and has `0` batches.
- `php artisan queue:failed` reported no failed jobs.
- `php artisan queue:work --once --tries=1 -vvv` exited cleanly with no pending job.

## Files Changed

- `config/mail.php`
  - Added safe compatibility for legacy `MAIL_ENCRYPTION=ssl` by mapping it to `smtps`.
  - `MAIL_ENCRYPTION=tls` now leaves Laravel on normal `smtp` scheme for port `587`.

- `.env.example`
  - Replaced local placeholder mail config with Brevo-compatible placeholders.
  - Removed `MAIL_SCHEME=null`.
  - Added `MAIL_ENCRYPTION=tls`.
  - Left `MAIL_USERNAME` and `MAIL_PASSWORD` empty.

- `.env` local only, ignored by git
  - Replaced `MAIL_SCHEME=smtp` with `MAIL_ENCRYPTION=tls`.
  - No credentials were printed or committed.

## Required Local Env

Use this shape locally when testing real SMTP:

```env
APP_URL=http://127.0.0.1:8080
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME="دلني"
QUEUE_CONNECTION=database
```

For non-sending local development, use:

```env
MAIL_MAILER=log
QUEUE_CONNECTION=sync
```

## Required Laravel Cloud Env

```env
APP_NAME=Delni
APP_ENV=production
APP_URL=https://delni.ly
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME="دلني"
QUEUE_CONNECTION=database
```

After changing Cloud env vars:

```bash
php artisan optimize:clear
php artisan queue:restart
```

Make sure a queue worker is enabled in Laravel Cloud.

## DNS / Sender Status

Checked DNS from local machine:

- Root TXT includes a Brevo verification code.
- `_dmarc.delni.ly` exists with `v=DMARC1; p=none`.
- Root TXT did not show an SPF record during this audit.
- Common DKIM selectors checked (`mail._domainkey`, `brevo._domainkey`) did not return TXT records.

Brevo dashboard still needs to confirm:

- `delni.ly` sender domain is fully verified.
- `noreply@delni.ly` is an allowed sender.
- SPF is present.
- DKIM is present using the selector Brevo provides.
- The SMTP key is active and not revoked.
- The account is not blocked and daily send limits are available.

## Verification Completed

```bash
php -l config/mail.php
php -l app/Mail/SetPasswordMail.php
php -l app/Mail/PasswordResetMail.php
php -l app/Services/OnboardingLinkService.php
php artisan optimize:clear
php artisan queue:restart
php artisan queue:failed
php artisan queue:work --once --tries=1 -vvv
```

Array-mailer render checks:

- `SetPasswordMail`: passed.
- `PasswordResetMail`: passed.

## Verification Still Needed

Do this only after installing a valid Brevo SMTP username/key:

1. Send a direct `Mail::raw` test to a real test inbox.
2. Send `SetPasswordMail` directly.
3. Queue `SetPasswordMail` and run `php artisan queue:work --tries=1 -vvv`.
4. Confirm the email From name renders as `دلني`.
5. Confirm links use `APP_URL=https://delni.ly` in production.
6. Confirm `php artisan queue:failed` stays empty.

## Security Status

- `.env` is ignored by git.
- `.env` has no git history.
- Real SMTP password was not printed or committed during this audit.
- Tracked docs contain placeholder SMTP examples, not a real key.
- If the current SMTP key was pasted into any chat or shared outside the project, rotate it in Brevo before production.
