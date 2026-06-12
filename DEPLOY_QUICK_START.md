# Railway Deployment - Quick Start Guide

## 5-Minute Setup

### Step 1: Prepare Repository
```bash
# Make sure everything is committed
git status

# If there are uncommitted changes
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

### Step 2: Create Railway Project
1. Go to [railway.app](https://railway.app)
2. Click **New Project**
3. Connect your GitHub repository
4. Select the repository and branch to deploy

### Step 3: Add Database
1. Click **+ New**
2. Select **Add Plugin**
3. Choose **PostgreSQL**
4. Click **Provision**
5. Confirm connection to your service

### Step 4: Set Environment Variables
1. Click **Variables** tab
2. Copy everything from `.env.railway` file
3. Fill in the blanks:
   - `APP_URL` = your Railway app URL (you'll get it after first deploy)
   - `APP_KEY` = Run locally: `php artisan key:generate --show`, copy the `base64:...` part
   - `MAIL_USERNAME` = Get from Mailtrap or your email service
   - `MAIL_PASSWORD` = Get from Mailtrap or your email service
   - `GEMINI_API_KEY` = Get from Google AI Studio
   - Database variables auto-fill from PostgreSQL add-on

### Step 5: Deploy
1. Click **Deploy** button
2. Wait for deployment to complete (2-3 minutes)
3. Once complete, you'll see your app URL

### Step 6: Verify
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Connect to your project
railway link

# Check logs
railway logs

# Test database
railway run php artisan tinker --execute "DB::connection()->getPdo();"

# Done! Your app is live
```

## Environment Variables Reference

| Variable | Value | Notes |
|----------|-------|-------|
| APP_KEY | base64:... | Generate with `php artisan key:generate --show` |
| DB_* | Auto-filled | PostgreSQL add-on fills these |
| REDIS_* | Auto-filled | If you add Redis plugin |
| MAIL_* | Your email | Use Mailtrap or Gmail |
| GEMINI_* | API keys | Get from Google AI Studio |

## Troubleshooting

### "502 Bad Gateway"
```bash
# Check what's wrong
railway logs

# Common causes:
# 1. Database not connected
railway run php artisan migrate

# 2. Storage folder not writable
railway run chmod -R 775 storage bootstrap/cache

# 3. Out of memory - increase dyno size in dashboard
```

### "Database connection failed"
```bash
# Verify connection
railway run php artisan tinker

# In tinker:
DB::connection()->getPdo();
// Should not error
```

### "Composer dependency error"
```bash
# Clear and reinstall
railway run composer install --no-interaction
railway run composer dump-autoload -o
```

## After First Deploy

### 1. Generate App Key
```bash
railway run php artisan key:generate
```
Update `APP_KEY` in Variables with the output.

### 2. Verify All Services
```bash
# Database
railway run php artisan tinker --execute "DB::connection()->getPdo();"

# Redis (if using)
railway run php artisan tinker --execute "Cache::set('test', 'ok'); echo Cache::get('test');"

# Mail (optional test)
railway run php artisan tinker
# In tinker: Mail::raw('Test', fn($m) => $m->to('your@email.com'));
```

### 3. Monitor Logs
```bash
# Follow logs in real-time
railway logs --follow

# Or from dashboard: Logs tab
```

## Deployment Checklist

- [ ] Repository pushed to GitHub
- [ ] Railway project created
- [ ] PostgreSQL database provisioned
- [ ] Environment variables set
- [ ] APP_KEY generated and set
- [ ] Mail credentials configured
- [ ] First deployment successful
- [ ] Database migration ran
- [ ] App responds (no 502 error)
- [ ] Check logs for errors

## What Happens During Deploy

1. **Build** (2-3 min)
   - Code cloned from GitHub
   - Composer installs dependencies
   - Node packages installed (if applicable)

2. **Release Phase** (30-60 sec)
   - Runs `php artisan migrate --force`
   - Caches config and routes
   - Caches views

3. **Start** (15-30 sec)
   - Apache + PHP boots
   - App listens on port 8080
   - Railway routes to your domain

## Common Errors & Fixes

### "target release: Exited with status 1"
Database migrations failed. Check:
```bash
railway logs
# Look for migration errors

# Fix and redeploy
git push origin main
```

### "Memory limit exceeded"
Too much data in single operation:
```bash
# Increase dyno size in dashboard Settings
# Then restart: railway restart
```

### "Service restarted unexpectedly"
App crashed. Check:
```bash
railway logs --limit 100

# Common causes:
# - Out of memory: upgrade dyno
# - Database disconnect: check connection
# - Missing env variable: check Variables tab
```

## Scaling Up

When you're ready for more users:

1. **Increase Dyno Size**
   - Dashboard â†’ Settings â†’ Dyno Size
   - Choose Medium or Large

2. **Add More Instances** (if on paid plan)
   - Dashboard â†’ Deployments â†’ Scale

3. **Enable Caching**
   - Add Redis plugin
   - Set `CACHE_DRIVER=redis`

4. **Optimize Queries**
   - Use `with()` for eager loading
   - Add database indexes
   - Use `redis` for sessions/queues

## Next Steps

- [ ] Read [RAILWAY_DEPLOYMENT.md](./RAILWAY_DEPLOYMENT.md) for advanced config
- [ ] Set up error tracking (Sentry)
- [ ] Configure email service (Mailtrap â†’ SendGrid)
- [ ] Set up monitoring and alerts
- [ ] Configure backup strategy

## Getting Help

- Railway Docs: [docs.railway.app](https://docs.railway.app)
- Laravel Docs: [laravel.com](https://laravel.com)
- Delni Deployment: See [RAILWAY_DEPLOYMENT.md](./RAILWAY_DEPLOYMENT.md)

---

**You're all set! Your Delni app is deployed on Railway. ðŸš€**
