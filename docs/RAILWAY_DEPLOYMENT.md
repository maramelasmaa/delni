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
SUPER_ADMIN_NAME=<your-admin-name>
SUPER_ADMIN_EMAIL=<your-admin@delni.ly>
SUPER_ADMIN_PASSWORD=<your-generated-strong-password>
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
php artisan delni:ensure-super-admin    # Creates super admin user from env
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 4. Verify Admin User Created

After deploy succeeds, check Rails logs:

```
✓ Super admin configured from SUPER_ADMIN_EMAIL
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
   - After first successful login, keep `SUPER_ADMIN_PASSWORD` stored securely or rotate it intentionally
   - You'll never deploy with same admin password again

---

## Troubleshooting

### Super admin user not created?

Check Railway logs:

```
ERROR: SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL, or SUPER_ADMIN_PASSWORD is not set
```

**Fix:** Ensure both variables are set in Railway dashboard.

### Can't login?

1. Verify email/password in Railway Variables
2. Check spelling exactly
3. Confirm `php artisan delni:ensure-super-admin` succeeded in release logs

### Lost admin password?

1. Set new `SUPER_ADMIN_PASSWORD` in Railway Variables
2. Trigger manual deploy or run:
   ```bash
   railway run php artisan delni:ensure-super-admin --force
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
SUPER_ADMIN_NAME=<staging-admin-name>
SUPER_ADMIN_EMAIL=<staging-admin-email>
SUPER_ADMIN_PASSWORD=<staging-admin-password>
```

**Production:**
```
SUPER_ADMIN_NAME=<production-admin-name>
SUPER_ADMIN_EMAIL=<production-admin-email>
SUPER_ADMIN_PASSWORD=<production-admin-password>
```

Each Railway project has separate Variables, so secrets stay isolated.
