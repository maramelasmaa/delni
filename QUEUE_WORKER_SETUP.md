# Queue Worker Setup Guide

## Overview

The Delni application uses a database queue to process emails asynchronously:
- **SetPasswordMail** — Sent when providers are created or onboarding links are resent
- **PasswordResetMail** — Sent when users request password reset

Both mailables implement `ShouldQueue` and use `afterCommit()` to ensure jobs are only created after database transactions complete.

**Without a queue worker, emails are queued but never sent.**

---

## Development Setup

### Start the Queue Worker

```bash
php artisan queue:work
```

This will:
- Poll the `jobs` table every 3 seconds
- Process queued emails one at a time
- Show verbose output as jobs are processed
- Handle failures with logging

**Keep this running in a separate terminal while developing.**

### Process One Job (Testing)

```bash
php artisan queue:work --once
```

Useful for testing email sending without running a long-lived worker.

### Monitor Failed Jobs

```bash
php artisan queue:failed
```

Shows any jobs that failed after exhausting retries. Failed jobs are logged with full exception details.

### Clear Queue

```bash
# Delete all jobs
php artisan queue:flush

# Delete failed jobs
php artisan queue:flush --failed
```

---

## Production Deployment

For production, the queue worker must run as a background service. Use one of these approaches:

### Option A: Supervisor (Recommended)

Create `/etc/supervisor/conf.d/delni-queue.conf`:

```ini
[program:delni-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/delni/artisan queue:work --queue=default --tries=3 --timeout=60
autostart=true
autorestart=true
numprocs=1
user=www-data
environment=PATH=/usr/local/bin:/usr/bin:/bin,LARAVEL_ENV=production
redirect_stderr=true
stdout_logfile=/path/to/delni/storage/logs/queue.log
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start delni-queue:*
```

### Option B: Systemd Service

Create `/etc/systemd/system/delni-queue.service`:

```ini
[Unit]
Description=Delni Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/delni
ExecStart=/usr/bin/php artisan queue:work --queue=default --tries=3 --timeout=60
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable delni-queue
sudo systemctl start delni-queue
```

### Option C: Laravel Cloud

If using [Laravel Cloud](https://cloud.laravel.com/), the queue worker is managed automatically.

---

## Configuration

### Queue Connection

Currently configured for database queue (`QUEUE_CONNECTION=database`):

```php
// config/queue.php
'database' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'default',
    'retry_after' => 90,      // Retry failed jobs after 90 seconds
    'after_commit' => true,   // Wait for transaction commit before processing
],
```

**retry_after > timeout** is correctly configured (90 > 60), preventing duplicate processing.

### Job Retention

Jobs are deleted from the `jobs` table after being successfully processed. Failed jobs are moved to `failed_jobs` table and logged.

### Timeout and Retries

Email jobs have:
- **timeout:** 60 seconds (default for all jobs)
- **tries:** 1 (configured on queue:work via `--tries=1`)
- **backoff:** Exponential (jobs fail immediately if there's an issue)

Adjust with:
```bash
php artisan queue:work --tries=3 --timeout=120
```

---

## Monitoring & Logging

### Job Processing Logs

Enable detailed logging by setting `LOG_LEVEL=debug` in `.env`:

```
LOG_LEVEL=debug
```

Queue logs appear in `storage/logs/laravel.log`:

```
[2026-06-11 15:30:00] local.DEBUG: Processing job [uuid]
[2026-06-11 15:30:02] local.DEBUG: Processed job [uuid] successfully
```

### Failed Job Logging

When an email fails to send, the `failed()` method in the Mailable logs:

```php
// SetPasswordMail::failed()
Log::error('SetPasswordMail failed to send', [
    'email' => $this->email,
    'userName' => $this->userName,
    'exception' => $exception?->getMessage(),
]);
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i "mail\|error"
```

### Queue Metrics

Check queue depth:
```bash
php artisan tinker
> DB::table('jobs')->count()
```

Check failed jobs:
```bash
php artisan queue:failed
```

---

## Testing Queue Email Sending

### 1. Create a Provider (Triggers SetPasswordMail)

```bash
php artisan tinker

# Create provider and queue email
$user = User::create([
    'name' => 'Test Provider',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);
$user->assignRole('provider');

# In another terminal:
php artisan queue:work --once
# Check: did email send to test@example.com?
```

### 2. Test Password Reset (Triggers PasswordResetMail)

```bash
# Manually send password reset email:
$user = User::find(1);
$token = Password::createToken($user);
Mail::queue(new PasswordResetMail(
    email: $user->email,
    resetLink: 'http://example.com/reset?token=' . $token,
    userName: $user->name,
));

# In another terminal:
php artisan queue:work --once
```

### 3. Verify Delivery

Check Resend API logs at https://resend.com/emails (if using Resend).

For development, emails are sent to `noreply@delni.ly` (configured in `.env`).

---

## Troubleshooting

### Emails Not Being Sent

1. **Check queue has jobs:**
   ```bash
   php artisan tinker
   > DB::table('jobs')->count()
   ```
   If 0, emails are sending synchronously (this shouldn't happen with ShouldQueue).

2. **Check queue worker is running:**
   ```bash
   ps aux | grep "queue:work"
   ```
   If not running, start it: `php artisan queue:work`

3. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   ```
   If jobs are in `failed_jobs` table, see the error details.

4. **Check Resend API key:**
   ```bash
   php artisan config:show services.resend.key
   ```
   Must not be empty.

5. **Check mail configuration:**
   ```bash
   php artisan config:show mail.default
   ```
   Must be `resend`.

### Jobs Stuck in Queue

If jobs are not being processed even with queue:work running:

1. **Check for exceptions in logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

2. **Check database connection:**
   ```bash
   php artisan config:show database.default
   ```

3. **Check queue table exists:**
   ```bash
   php artisan tinker
   > DB::table('jobs')->first()
   ```

4. **Force process one job:**
   ```bash
   php artisan queue:work --once --verbose
   ```

### High Memory Usage

Queue worker accumulates memory over time. Restart periodically:

```bash
# With Supervisor, add:
stopasgroup=true
stopwaitsecs=30
```

Or use `--max-jobs` and `--max-time`:
```bash
php artisan queue:work --max-jobs=100 --max-time=3600
```

---

## Best Practices

1. **Always have a queue worker running in production**
2. **Monitor queue depth** — if jobs accumulate, add more workers
3. **Log failed jobs** — check logs regularly for email delivery issues
4. **Test email content** — use `Mail::fake()` for integration tests
5. **Set realistic timeouts** — email sending rarely exceeds 60 seconds
6. **Use exponential backoff** — retries wait progressively longer

---

## References

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Resend Email API](https://resend.com/docs)
- [Queue Worker Deployment](https://laravel.com/docs/deployment#supervisor-configuration)
