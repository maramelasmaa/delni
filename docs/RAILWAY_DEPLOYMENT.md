# Railway Deployment Guide

## Setup Admin Credentials

### 1. Generate Strong Password

Use a secure password generator:

```bash
# Option 1: OpenSSL (terminal)
openssl rand -base64 32

# Option 2: Password manager (1Password, Bitwarden, etc.)
# Generate 32-char strong password
```

### 2. Add Secrets to Railway Dashboard

1. Go to your Railway project
2. Click **Variables** tab
3. Add environment variables:

```
ADMIN_EMAIL=your-admin@delni.ly
ADMIN_PASSWORD=<your-generated-strong-password>
```

**Important:** 
- Never commit these to git
- Use Railway's secrets management, not `.env` files
- Store password in password manager for first login

### 3. Deploy

Push code to GitHub:

```bash
git push origin main
```

Railway auto-deploys and runs the `release` phase in `Procfile`:

```
php artisan migrate --force
php artisan storage:link
php artisan delni:setup-admin    # ← Creates admin user
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 4. Verify Admin User Created

After deploy succeeds, check Rails logs:

```
✓ Admin user ready: your-admin@delni.ly
```

### 5. First Login

1. Go to your Railway domain (e.g., `https://delni.railway.app`)
2. Navigate to `/admin` (Filament panel)
3. Login with:
   - Email: `your-admin@delni.ly`
   - Password: `<the-password-you-generated>`

### 6. Post-Login (Recommended)

1. **Change Password:**
   - Profile → Change Password
   - Use a new strong password
   - Save to password manager

2. **Enable 2FA** (if available):
   - Security settings
   - Enable two-factor authentication

3. **Remove Deployment Secrets** (optional but safer):
   - After first successful login, you can remove `ADMIN_PASSWORD` from Railway
   - You'll never deploy with same admin password again

---

## Troubleshooting

### Admin user not created?

Check Railway logs:

```
ERROR: ADMIN_EMAIL and ADMIN_PASSWORD environment variables required
```

**Fix:** Ensure both variables are set in Railway dashboard.

### Can't login?

1. Verify email/password in Railway Variables
2. Check spelling exactly
3. Confirm `php artisan delni:setup-admin` succeeded in release logs

### Lost admin password?

1. Set new `ADMIN_PASSWORD` in Railway Variables
2. Trigger manual deploy or run:
   ```bash
   railway run php artisan delni:setup-admin --force
   ```
3. Use new password to login

---

## Security Best Practices

✅ **DO:**
- Generate strong random passwords (32+ chars)
- Store passwords in password manager
- Use Railway's secrets management (never git)
- Change password after first login
- Enable 2FA if available
- Use different passwords per environment

❌ **DON'T:**
- Hardcode credentials in code
- Share passwords in Slack/email
- Commit `.env` files
- Use same password for multiple environments
- Share admin credentials with non-admins

---

## Environment-Specific Admin

For multiple deployments (staging, production):

**Staging:**
```
ADMIN_EMAIL=admin@staging.delni.ly
ADMIN_PASSWORD=<staging-admin-password>
```

**Production:**
```
ADMIN_EMAIL=admin@delni.ly
ADMIN_PASSWORD=<production-admin-password>
```

Each Railway project has separate Variables, so secrets stay isolated.
