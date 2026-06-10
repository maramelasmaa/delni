# Delni Admin Setup Guide

## Admin User Seeding (Secure by Default)

⚠️ **IMPORTANT:** No default admin is created automatically. You must explicitly configure environment variables.

### How It Works

The `AdminUserSeeder` requires **all three** environment variables to be set:
- `SUPER_ADMIN_EMAIL`
- `SUPER_ADMIN_NAME`
- `SUPER_ADMIN_PASSWORD`

**If any are missing, the seeder skips silently.** This prevents accidental default admin creation in production.

### No Default Credentials

There are **no hardcoded default admin credentials**. Every admin must be explicitly created with custom credentials.

### How to Seed the Admin

#### Option 1: Full Database Seed (Recommended)
```bash
php artisan migrate:fresh --seed
```

This will:
- ✅ Run all migrations
- ✅ Seed all required data (roles, plans, categories, cities, etc.)
- ✅ Create the admin user
- ✅ Optionally seed marketplace placement test data

#### Option 2: Seed Only Existing Tables
```bash
php artisan db:seed
```

This will seed all seeders into an existing database (no migrations).

#### Option 3: Seed Only the Admin
```bash
php artisan db:seed --class=AdminUserSeeder
```

This will create/update only the admin user.

#### Option 4: Ensure Admin Exists (Artisan Command)
```bash
php artisan delni:ensure-super-admin
```

This command checks if admin exists and creates if missing (useful for production deployments).

---

## Customize Admin Credentials

You can customize the admin credentials via **environment variables**:

### In `.env` file:

```env
SUPER_ADMIN_NAME=Your Name
SUPER_ADMIN_EMAIL=your-email@example.com
SUPER_ADMIN_PASSWORD=your-secure-password
```

Then seed:
```bash
php artisan db:seed --class=AdminUserSeeder
```

---

## After First Login

### ⚠️ Security Steps

1. **Change Your Password**
   - Go to Admin Panel → Profile
   - Update password from the default

2. **Verify Email** (if not already)
   - The seeder marks email as verified
   - But check in case you need to send verification link

3. **Review Activity Logs**
   - Admin Panel → Activity Logs
   - Track all admin actions

---

## Login URLs

| Panel | URL | Role |
|-------|-----|------|
| **Admin Panel** | `http://localhost/cp/admin/login` | `super_admin` |
| **Provider Panel** | `http://localhost/provider/login` | `provider` |
| **Public Site** | `http://localhost/` | `user` |

---

## Roles in System

```
super_admin  → Full access to admin panel
provider     → Can manage their own profiles/credentials
user         → Can write reviews, view marketplace
```

---

## Troubleshooting

### Admin User Not Created

**Problem:** You don't see the admin user after seeding.

**Solution:**
```bash
# Check if admin exists
php artisan tinker
> User::role('super_admin')->count()

# If 0, reseed
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder
```

### Can't Log In to Admin Panel

**Problem:** Login fails with default credentials.

**Solution:**
```bash
# Reset admin password to default
php artisan db:seed --class=AdminUserSeeder
# Password will be: ChangeMe123!
```

### Forgot Admin Password

**Reset via Tinker:**
```bash
php artisan tinker
> $admin = User::where('email', 'admin@delni.ly')->first();
> $admin->password = Hash::make('NewPassword123!');
> $admin->save();
```

Then log in with `NewPassword123!`

---

## Testing Admin Access

```bash
# Quick test via artisan
php artisan tinker

# Get admin
> $admin = User::role('super_admin')->first();
> $admin->email
> $admin->hasRole('super_admin')

# Test auth
> auth()->loginUsingId($admin->id);
> auth()->check()
```

---

## Database Structure

The admin seeder uses:

**Model:** `App\Models\User`  
**Seeder:** `database/seeders/AdminUserSeeder.php`  
**Role:** `super_admin` (from `Spatie\Permission`)  

The seeder is **idempotent** — it won't duplicate admins if run multiple times.

---

## Security Reminders

✅ **Do:**
- Change default password after first login
- Use strong passwords
- Monitor activity logs
- Review user suspensions regularly

❌ **Don't:**
- Share admin credentials
- Use same password across environments
- Leave default password in production
- Trust unverified activity logs

---

## Related Commands

```bash
# View all artisan commands
php artisan list

# Check admin user
php artisan tinker
> User::role('super_admin')->get()

# Reset database
php artisan migrate:fresh --seed

# View roles
php artisan tinker
> Spatie\Permission\Models\Role::all()
```

