# Admin Email Actions Guide - Brevo Integration

## Overview

The Delni admin panel now has three email actions connected to Brevo:

1. **Resend Set Password Email** - Onboarding email with password setup link
2. **Send Password Reset Email** - Password reset link for existing providers
3. **Automatic on Provider Creation** - Sent automatically when creating new provider

All emails are queued through Laravel's queue system and sent via Brevo SMTP.

## Email Actions in Admin

### Where to Find

1. Go to **Admin Panel** → **Providers**
2. Click **Edit** on any provider
3. See action buttons in the top-right:
   - 📧 **Resend Set Password Email**
   - 🔑 **Send Password Reset Email**
   - 🗑️ **Delete**

### Resend Set Password Email

**Purpose:** Send the initial onboarding email again
- Creates a new password setup token
- Link valid for 72 hours
- Provider can set their own password
- **When to use:** Provider didn't receive first email, link expired, or they need it resent

**What Happens:**
```
1. Click "Resend Set Password Email"
2. Confirm the action
3. Email queued to Brevo
4. Brevo sends via: noreply@delni.ly
5. Provider receives onboarding email
6. Provider clicks setup link → sets password → logs in
```

### Send Password Reset Email

**Purpose:** Let provider reset their forgotten password
- Creates a Laravel password reset token
- Link valid for 60 minutes (default)
- Standard Laravel password reset flow
- **When to use:** Provider forgot their password

**What Happens:**
```
1. Click "Send Password Reset Email"
2. Confirm the action
3. Email queued to Brevo
4. Brevo sends via: noreply@delni.ly
5. Provider clicks link → enters new password → can login
```

### Automatic on Provider Creation

**Purpose:** Send setup email automatically when admin creates new provider
- Happens in background using queue
- No extra click needed
- Same as "Resend Set Password Email"
- **When:** Every time you create a provider in admin

## How It Works: Architecture

```
Admin Panel (Filament)
    ↓
Mail::send(SetPasswordMail)  ← Queued
    ↓
Queue (Database/Redis)
    ↓
Queue Worker Process
    ↓
Brevo SMTP
    ↓
Provider's Email
```

### Step-by-Step Flow

1. **Admin clicks "Resend Set Password Email"**
   - Action triggered in EditProvider.php
   - Creates new OnboardingToken
   - Calls `Mail::send(new SetPasswordMail(...))`

2. **Email Queued**
   - SetPasswordMail implements `ShouldQueue`
   - Goes to queue: `config/queue.php`
   - Default: database queue
   - Production: Redis queue

3. **Queue Worker Processes**
   - `php artisan queue:work` (in Procfile)
   - Picks up email from queue
   - Uses MAIL_DRIVER (smtp-relay.brevo.com)

4. **Brevo Sends**
   - Uses credentials:
     - MAIL_HOST: `smtp-relay.brevo.com`
     - MAIL_PORT: `587`
     - MAIL_USERNAME: `noreply@delni.ly`
     - MAIL_PASSWORD: API key
   - Sends to provider email

5. **Provider Receives**
   - Beautiful HTML email
   - Setup link: `delni.app/onboarding/{token}`
   - Provider clicks → sets password

## Configuration Verification

### 1. Mail Configuration

**Check `.env.railway` or local `.env`:**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=noreply@delni.ly
MAIL_PASSWORD=xsmtpsib-...
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni
```

**Test:**
```bash
php artisan tinker
Mail::raw('Test', fn($m) => $m->to('your@email.com')->subject('Test'));
```

### 2. Queue Configuration

**Check `config/queue.php`:**
```php
'default' => env('QUEUE_CONNECTION', 'database'),

// Database queue setup
'database' => [
    'driver' => 'database',
    'table' => 'jobs',
    'retry_after' => 90,
],

// Redis queue (production)
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => 'default',
    'retry_after' => 90,
],
```

**Production (.env.railway):**
```
QUEUE_CONNECTION=redis
```

### 3. Email Classes

**SetPasswordMail** (`app/Mail/SetPasswordMail.php`)
- Implements: `ShouldQueue` ✅
- Uses: `afterCommit()` (waits for DB transaction) ✅
- Template: `emails.set-password` ✅
- Error Handling: `failed()` method ✅

**PasswordResetMail** (`app/Mail/PasswordResetMail.php`)
- Implements: `ShouldQueue` ✅
- Uses: `onQueue('default')` ✅
- Template: `emails.password-reset` ✅
- Error Handling: `failed()` method ✅

### 4. Procfile (Railway)

```
release: php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --queue=default --max-jobs=100 --max-time=3600
```

**worker** line: Starts queue worker to process emails ✅

## Testing Locally

### 1. Test in Development

**Option A: Queue in Background (Async)**
```bash
# Update .env
QUEUE_CONNECTION=database
MAIL_DRIVER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=noreply@delni.ly
MAIL_PASSWORD=xsmtpsib-...

# Terminal 1: Start queue worker
php artisan queue:work

# Terminal 2: Create provider in admin
# Go to admin → create provider → email queued

# Terminal 1 output: Shows email sent to Brevo
# Check inbox: Email received from noreply@delni.ly
```

**Option B: Immediate Send (Sync)**
```bash
# .env
QUEUE_CONNECTION=sync  # No queue, send immediately

# Create provider → email sent instantly
```

### 2. Verify Email Was Sent

**Check Queue Jobs:**
```bash
php artisan queue:failed     # See failed jobs
php artisan queue:retry all  # Retry failed jobs
```

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

**Check Brevo Dashboard:**
1. Login to brevo.com
2. Go: Reporting → Overview
3. See: Sent count increased
4. Click email → see delivery status

### 3. Test with Real Provider

```bash
1. Go to Admin → Providers → Create
2. Fill form, save
3. Check console: Should see "SetPasswordMail sent"
4. Check email: Should receive onboarding email
5. Go back to provider → Click "Resend Set Password Email"
6. Check email again: Should receive new email with fresh link
7. Go back to provider → Click "Send Password Reset Email"
8. Check email: Should receive password reset email
```

## Production Deployment

### 1. Railway Setup

**Add Redis Plugin:**
```
Dashboard → + New → Add Plugin → Redis
```

**Environment Variables:**
```
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_DRIVER=redis
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=noreply@delni.ly
MAIL_PASSWORD=xsmtpsib-...
```

**Procfile (already configured):**
- `web:` process runs the app
- `worker:` process handles queued emails

### 2. Deploy

```bash
git push origin main
# Railway auto-deploys
# Both web and worker processes start
# Emails flow through queue → Brevo
```

### 3. Monitor Production

**Check Logs:**
```bash
railway logs --follow
# Look for: "SetPasswordMail", "PasswordResetMail", "Mail sent"
```

**Test Email:**
```bash
railway run php artisan tinker
Mail::raw('Test', fn($m) => $m->to('test@email.com')->subject('Test'));

# Or use admin: create provider → verify email sent
```

**Brevo Dashboard:**
- Real-time delivery status
- Open rates, click rates
- Bounce/complaint reports

## Troubleshooting

### Email Not Sent

**Check 1: Is queue worker running?**
```bash
# Development
# Terminal 1: php artisan queue:work
# Terminal 2: Create provider
# Terminal 1 should show: "Processed SetPasswordMail"

# Production
railway logs --follow
# Look for: "Processed", "Failed", or errors
```

**Check 2: Is queue connection configured?**
```bash
php artisan config:show queue.default
# Should show: redis or database
```

**Check 3: Are Brevo credentials correct?**
```bash
php artisan config:show mail.mailers.smtp
# Verify all values match Brevo
```

### Email Sent But Not Received

**Check 1: SMTP Authentication**
```bash
# Verify credentials in Brevo dashboard
# MAIL_PASSWORD might be wrong or expired
```

**Check 2: Sender Not Verified**
```bash
# In Brevo: Settings → Senders
# Add noreply@delni.ly if missing
# Confirm verification email
```

**Check 3: In Spam**
```bash
# Check spam folder
# Add noreply@delni.ly to contacts
# Improve email content (no spam triggers)
```

**Check 4: Email Provider Block**
```bash
# Some email providers (Gmail, etc.) may block
# Check Brevo dashboard for bounces
# May need to warm up sender gradually
```

### Queue Jobs Failing

**View Failed Jobs:**
```bash
php artisan queue:failed
```

**Retry Failed:**
```bash
php artisan queue:retry all
```

**Clear Failed:**
```bash
php artisan queue:flush
```

**Check Logs:**
```bash
tail -f storage/logs/laravel.log
# Look for: "SetPasswordMail failed"
# Error message will explain the issue
```

## Email Templates

### Set Password Email
- **File:** `resources/views/emails/set-password.blade.php`
- **Subject:** "Set your password" (in language files)
- **Contains:** Setup link, welcome message
- **Expires:** 72 hours

### Password Reset Email
- **File:** `resources/views/emails/password-reset.blade.php`
- **Subject:** "Reset your password" (in language files)
- **Contains:** Reset link, instructions
- **Expires:** 60 minutes

## Best Practices

### For Admins

1. ✅ Use "Resend Set Password" for onboarding issues
2. ✅ Use "Send Password Reset" when provider forgets password
3. ✅ Verify emails sent by checking Brevo dashboard
4. ✅ Monitor queue processing in logs
5. ❌ Don't create duplicate providers (email conflict)

### For Developers

1. ✅ Always implement `ShouldQueue` for emails
2. ✅ Use `afterCommit()` to prevent half-sent emails
3. ✅ Implement `failed()` for error logging
4. ✅ Test with real Brevo before production
5. ✅ Monitor queue health: `queue:failed`, logs
6. ❌ Don't send synchronously without testing

## Monitoring & Alerts

### What to Monitor

1. **Queue Length**
   ```bash
   php artisan queue:work
   # Shows jobs processed in real-time
   ```

2. **Failed Jobs**
   ```bash
   php artisan queue:failed
   # Should be empty or low
   ```

3. **Email Delivery**
   - Brevo dashboard → Reporting → Overview
   - Look for: Sent, Delivered, Opened, Bounced

4. **Logs**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "mail\|queue"
   ```

### Set Up Alerts

In production, consider:
- Alert if queue jobs > 100 (backlog)
- Alert if bounce rate > 5%
- Alert if failed jobs > 0 (should retry)

## Performance

### Current Setup

- **Database Queue** (development)
  - Simple, no external dependency
  - Slower, but fine for < 1000 emails/day

- **Redis Queue** (production, configured)
  - Fast, handles 10k+ emails/day
  - Recommended for scalability
  - Already configured in `.env.railway`

### Expected Performance

- Email queued: < 100ms
- Email processed: < 1 second
- Email delivered: 10-30 seconds
- Brevo delivery: 5-10 minutes

---

## Quick Reference

### Development

```bash
# Terminal 1: Queue worker
php artisan queue:work

# Terminal 2: Create/test emails
php artisan tinker
Mail::send(new SetPasswordMail('user@example.com', 'link', 'Name'));

# Check results
php artisan queue:failed      # Check failures
tail -f storage/logs/laravel.log
```

### Production

```bash
# Already running in Procfile
# worker: php artisan queue:work --queue=default --max-jobs=100 --max-time=3600

# Monitor
railway logs --follow

# Verify
railway run php artisan queue:failed
```

### Admin Panel

1. **Resend Set Password** - One-click resend onboarding email
2. **Send Password Reset** - One-click send reset email
3. **Create Provider** - Auto-sends setup email

All connected to Brevo via SMTP. ✅

---

**Status: ✅ Email actions are fully integrated with Brevo and ready to use!**
