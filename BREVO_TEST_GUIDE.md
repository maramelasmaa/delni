# Brevo SMTP Test Guide

## Quick Setup

### 1. Update Your Local `.env`

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=noreply@delni.ly
MAIL_PASSWORD=xsmtpsib-your-api-key-here
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni
```

⚠️ **IMPORTANT:** 
- Never commit your actual `MAIL_PASSWORD` to git
- Add `.env` to `.gitignore` (should already be there)
- Only use the real password in local `.env`, Railway Variables, or 1Password

### 2. Test the Connection

```bash
# Quick test
php scripts/test-brevo.php your-email@example.com

# Or use tinker
php artisan tinker

# In tinker:
Mail::raw('Test email from Brevo', function ($m) { 
  $m->to('your-email@example.com')->subject('Brevo Test');
});
```

### 3. Verify

Check your email inbox in 10-30 seconds. If you received the email, Brevo is working! ✅

## Common Issues

### "SMTP Error: Could not authenticate"
- ❌ Wrong password
- ✅ Copy the `xsmtpsib-...` key from Brevo dashboard
- ✅ Paste into MAIL_PASSWORD

### "Connection refused"
- ❌ Wrong host or port
- ✅ Use: `smtp-relay.brevo.com` on port `587`
- ✅ Encryption: `tls`

### "Email sent but not received"
- ❌ Sender not verified in Brevo
- ✅ Go to Brevo dashboard
- ✅ Verify your sender email address
- ✅ Click confirmation link in verification email

### "530 Authentication required"
- ❌ MAIL_USERNAME or MAIL_PASSWORD not set
- ✅ Check both are in `.env`
- ✅ Restart your app: `php artisan serve`

## Brevo Dashboard Locations

**Get SMTP Credentials:**
1. Login to [brevo.com](https://brevo.com)
2. Go: Settings → SMTP & API → SMTP Login
3. Copy the credentials shown

**Verify Senders:**
1. Go: Settings → Senders & Contacts → Senders
2. Add email or domain
3. Click verification link in email

**Monitor Emails:**
1. Go: Reporting → Overview
2. See sent, delivered, opened, etc.

## Production Deployment

Once testing works locally:

### 1. Add to Railway Variables
```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=noreply@delni.ly
MAIL_PASSWORD=xsmtpsib-your-api-key-here
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni
```

### 2. Deploy
```bash
git push origin main
# Railway auto-deploys
```

### 3. Verify Production Email
```bash
railway run php artisan tinker

# In tinker:
Mail::raw('Prod test', function ($m) { 
  $m->to('test@example.com')->subject('Prod Test');
});
```

## Email Features

Once working, you can send:

### 1. Welcome Emails
```php
Mail::to($user)->send(new WelcomeEmail($user));
```

### 2. Password Resets
```php
$user->sendPasswordResetNotification();
```

### 3. Provider Confirmations
```php
Mail::to($provider->email)->send(new ProviderVerificationEmail($provider));
```

### 4. Contact Form
```php
Mail::to('support@delni.app')->send(new ContactFormEmail($data));
```

## Brevo Limits

| Plan | Emails/Day | Cost |
|------|-----------|------|
| Free | 300 | $0 |
| Pay-as-you-go | Unlimited | ~€0.02 each |
| Pro | 50,000+ | $25-100/month |

For Delni:
- Dev/testing: Free tier (300/day)
- Production: Free or Pro depending on volume

## Monitoring

### Check Email Status
```bash
# In Brevo dashboard:
1. Reporting → Overview (sent, delivered, opened)
2. Campaigns → List (if using drip campaigns)
3. Contacts → View bounces and complaints
```

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Look for:
- "Mail sent"
- "SMTP Error"
- Connection issues
```

## Advanced: Custom Domain

For production, use a custom domain:

1. In Brevo: Settings → Sender Domain
2. Add your domain (e.g., `mail.delni.app`)
3. Add DNS records (provided by Brevo)
4. Wait 24-48 hours for verification
5. Update `MAIL_FROM_ADDRESS=noreply@mail.delni.app`

This improves email deliverability!

## Testing Tools

- **Brevo Dashboard**: Reporting tab
- **Laravel Logs**: `storage/logs/laravel.log`
- **Mail Testing**: Use `php scripts/test-brevo.php`
- **Inbox Check**: Check spam folder if not in inbox

## Quick Commands

```bash
# Test SMTP
php scripts/test-brevo.php user@example.com

# Check config
php artisan config:show mail

# Send test in tinker
php artisan tinker
Mail::raw('Test', fn($m) => $m->to('test@email.com'));

# View logs
tail -f storage/logs/laravel.log | grep -i mail

# Clear cache (if config changed)
php artisan config:clear
```

## Need Help?

- **Brevo Docs**: [brevo.com/support](https://brevo.com/support)
- **Laravel Mail**: [laravel.com/docs/mail](https://laravel.com/docs/mail)
- **SMTP Issues**: Check host, port, username, password, encryption

---

**Status**: ✅ Ready to test Brevo SMTP!

Run: `php scripts/test-brevo.php your-email@example.com`
