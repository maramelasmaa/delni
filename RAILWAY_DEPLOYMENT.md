# Railway Deployment Guide for Delni

## Quick Start

### 1. Prerequisites
- Railway CLI installed (`npm install -g @railway/cli`)
- GitHub repository connected to Railway
- PostgreSQL database provisioned in Railway

### 2. Environment Variables

Set these variables in Railway dashboard under **Variables**:

```
APP_NAME=Delni
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app
APP_KEY=<generate-with-php-artisan-key-generate>

DB_CONNECTION=pgsql
DB_HOST=${{ PGHOST }}
DB_PORT=${{ PGPORT }}
DB_DATABASE=${{ PGDATABASE }}
DB_USERNAME=${{ PGUSER }}
DB_PASSWORD=${{ PGPASSWORD }}

REDIS_HOST=${{ REDIS_PUBLIC_URL }}  # If using Redis add-on
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your-username
MAIL_PASSWORD=<mail-provider-secret>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@delni.app
MAIL_FROM_NAME=Delni

GEMINI_ENABLED=true
GEMINI_API_KEY=<gemini-api-key>
GEMINI_BASE_URL=https://generativelanguage.googleapis.com
GEMINI_MODEL=gemini-2.5-flash
GEMINI_TIMEOUT=15

QUEUE_CONNECTION=redis  # Use Redis for queues in production
SESSION_DRIVER=redis
CACHE_DRIVER=redis
```

### 3. Database Add-on

1. In Railway Dashboard, click **+ New**
2. Select **Add Plugin**
3. Choose **PostgreSQL**
4. Connect to your main service
5. Railway will auto-populate DB_* variables

### 4. Redis Add-on (Recommended)

1. In Railway Dashboard, click **+ New**
2. Select **Add Plugin**
3. Choose **Redis**
4. Connect to your main service
5. Database variables auto-populated

### 5. Deploy

```bash
# Login to Railway
railway login

# Connect to your project
railway link <project-id>

# Deploy (automatic on git push also works)
railway up
```

### 6. Verify Deployment

```bash
# Check logs
railway logs

# Run artisan commands
railway run php artisan tinker

# View environment
railway env
```

## Deployment Flow

1. **Release Phase** (Procfile `release` line)
   - Runs database migrations
   - Caches configuration and routes
   - Caches views

2. **Web Dyno** (Procfile `web` line)
   - Starts Apache with PHP
   - Serves your application

## Troubleshooting

### 502 Bad Gateway
- Check logs: `railway logs`
- Verify database is connected: `railway run php artisan tinker`
- Check storage permissions

### Database Connection Errors
- Verify DB_* environment variables
- Ensure PostgreSQL add-on is connected
- Check firewall rules allow Railway IP

### Out of Memory
- Increase dyno size in Railway dashboard
- Optimize queries using eager loading
- Clear caches: `railway run php artisan cache:clear`

### Queue Jobs Not Processing
- Set QUEUE_CONNECTION=redis (requires Redis add-on)
- Restart app: `railway restart`
- Check queue failures: `railway run php artisan queue:failed`

## Performance Optimization

### Caching Strategy
```bash
railway run php artisan config:cache
railway run php artisan route:cache
railway run php artisan view:cache
```

### Database
- Use indexes on frequently queried columns
- Enable query logging in development only
- Use eager loading (`with()`) to prevent N+1 queries

### Assets
- Compile and minify CSS/JS: `npm run build`
- Use CDN for images and static files

## Rollback

```bash
# View deployment history
railway deployments

# Rollback to previous deployment
railway rollback <deployment-id>
```

## Monitoring

### Logs
```bash
railway logs --follow
```

### Metrics
- View CPU, Memory, Network in Railway Dashboard
- Set up alerts for high usage

### Error Tracking (Optional)
Add to `.env`:
```
SENTRY_LARAVEL_DSN=your-sentry-dsn
```

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] APP_KEY is set and unique
- [ ] HTTPS enforced (automatic with railway.app domain)
- [ ] Database is PostgreSQL with strong password
- [ ] Redis password is strong
- [ ] Mail credentials are secure
- [ ] API keys (Gemini, etc.) are stored as variables
- [ ] .env file is NOT committed to git
- [ ] Database backups enabled

## CI/CD Integration

Railway auto-deploys on git push. Disable auto-deploy and use manual deployments:

1. Go to **Settings** â†’ **Deployments**
2. Toggle **Auto-deploy on push** to OFF
3. Deploy manually when ready: `railway up`

## Cost Optimization

- Use PostgreSQL (cheaper than other databases)
- Start with small dyno, scale up as needed
- Use Redis only if queue/cache really needed
- Monitor usage in Railway dashboard
- Set deployment limits to prevent runaway costs
