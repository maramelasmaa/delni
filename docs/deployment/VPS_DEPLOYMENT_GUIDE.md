# Linux VPS Deployment Guide

Deploy Delni to Ubuntu 22.04 VPS

---

## Prerequisites

- Ubuntu 22.04 LTS VPS
- SSH root access
- Domain name with DNS pointing to VPS IP
- GitHub access token (for private repos if needed)

---

## 1. System Setup

### SSH into VPS
```bash
ssh root@your-vps-ip
```

### Update system
```bash
apt update && apt upgrade -y
```

### Install dependencies
```bash
apt install -y \
  nginx \
  mysql-server \
  php8.3-cli \
  php8.3-fpm \
  php8.3-mysql \
  php8.3-intl \
  php8.3-zip \
  php8.3-mbstring \
  php8.3-curl \
  php8.3-dom \
  php8.3-fileinfo \
  php8.3-gd \
  php8.3-xml \
  php8.3-opcache \
  composer \
  nodejs \
  npm \
  git \
  curl \
  wget \
  supervisor \
  certbot \
  python3-certbot-nginx
```

### Start services
```bash
systemctl start nginx mysql-server php8.3-fpm
systemctl enable nginx mysql-server php8.3-fpm
```

---

## 2. Clone Repository

```bash
cd /var/www
git clone https://github.com/maramelasmaa/delni.git
cd delni
```

---

## 3. Install Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

## 4. Install NPM Dependencies & Build

```bash
npm ci
npm run build
```

---

## 5. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### Edit .env with production values
```bash
nano .env
```

Set these values:

```
APP_NAME=دلني
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delni
DB_USERNAME=delni
DB_PASSWORD=<generate-strong-password>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=resend
MAIL_PASSWORD=<your-resend-api-key>
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=دلني

GOOGLE_CLIENT_ID=131851047813-ri2kkjman6tuqn5b33nd2bfa0i60637k.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT_URL=https://your-domain.com/auth/google/callback

ADMIN_EMAIL=admin@your-domain.com
ADMIN_PASSWORD=<generate-strong-password>

TRUSTED_PROXIES=127.0.0.1
CORS_ALLOWED_ORIGINS=https://your-domain.com
```

---

## 6. Setup Database

### Create database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE delni CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'delni'@'localhost' IDENTIFIED BY '<same-password-as-env>';
GRANT ALL PRIVILEGES ON delni.* TO 'delni'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Run migrations
```bash
php artisan migrate --force
php artisan storage:link
php artisan delni:setup-admin
```

---

## 7. Setup File Permissions

```bash
chown -R www-data:www-data /var/www/delni
chmod -R 755 /var/www/delni
chmod -R 775 /var/www/delni/storage /var/www/delni/bootstrap/cache /var/www/delni/public
```

---

## 8. Configure Nginx

### Create Nginx config
```bash
nano /etc/nginx/sites-available/delni
```

Paste:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/delni/public;

    index index.php;

    charset utf-8;

    # Logs
    access_log /var/log/nginx/delni_access.log;
    error_log /var/log/nginx/delni_error.log;

    # Laravel rewrite
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to dot files
    location ~ /\. {
        deny all;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static assets (cache long-term)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### Enable site
```bash
ln -s /etc/nginx/sites-available/delni /etc/nginx/sites-enabled/delni
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

---

## 9. Setup HTTPS with Certbot

```bash
certbot certonly --nginx -d your-domain.com -d www.your-domain.com
```

Follow prompts. Then update Nginx config:

```bash
nano /etc/nginx/sites-available/delni
```

Replace `server { listen 80; ... }` with:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/delni/public;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    index index.php;
    charset utf-8;

    access_log /var/log/nginx/delni_access.log;
    error_log /var/log/nginx/delni_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ /\. {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### Reload Nginx
```bash
nginx -t
systemctl reload nginx
```

### Auto-renew SSL
```bash
systemctl enable certbot.timer
systemctl start certbot.timer
```

---

## 10. Setup Queue Worker (Supervisor)

Create Supervisor config:

```bash
nano /etc/supervisor/conf.d/delni-queue.conf
```

Paste:

```ini
[program:delni-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/delni/artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/delni-queue.log
user=www-data
```

Start:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start delni-queue:*
```

Monitor:

```bash
supervisorctl status delni-queue:*
```

---

## 11. Setup Scheduler (Cron)

```bash
crontab -e -u www-data
```

Add:

```
* * * * * cd /var/www/delni && php artisan schedule:run >> /dev/null 2>&1
```

This runs every minute and executes scheduled commands (migrations expiry, subscription checks, etc.)

---

## 12. Configure PHP

Edit PHP config:

```bash
nano /etc/php/8.3/fpm/php.ini
```

Recommended settings for production:

```
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
opcache.enable = 1
opcache.memory_consumption = 256
```

Reload PHP:

```bash
systemctl reload php8.3-fpm
```

---

## 13. Configure MySQL for Production

```bash
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add under `[mysqld]`:

```
# Performance
max_connections = 100
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# InnoDB
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
```

Restart:

```bash
systemctl restart mysql-server
```

---

## 14. Firewall Setup

```bash
# UFW firewall
ufw enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
```

---

## 15. Monitoring & Logs

### Check app logs
```bash
tail -f /var/www/delni/storage/logs/laravel.log
```

### Check Nginx
```bash
tail -f /var/log/nginx/delni_error.log
tail -f /var/log/nginx/delni_access.log
```

### Check queue
```bash
supervisorctl tail delni-queue:0
```

### Check PHP
```bash
tail -f /var/log/php8.3-fpm.log
```

---

## 16. Backup & Maintenance

### Database backup (daily)
```bash
nano /usr/local/bin/backup-delni.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/delni"
mkdir -p $BACKUP_DIR
mysqldump -u delni -p$DB_PASSWORD delni > $BACKUP_DIR/delni_$(date +%Y%m%d_%H%M%S).sql.gz
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

Make executable:
```bash
chmod +x /usr/local/bin/backup-delni.sh
```

Add to crontab:
```bash
0 2 * * * /usr/local/bin/backup-delni.sh
```

### Laravel cache clearing
```bash
php artisan optimize:clear
```

---

## 17. Deployment Workflow

### Update from GitHub
```bash
cd /var/www/delni
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear
systemctl reload nginx
```

Or create a deploy script:

```bash
nano /usr/local/bin/deploy-delni.sh
```

```bash
#!/bin/bash
cd /var/www/delni
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize:clear
systemctl reload php8.3-fpm
systemctl reload nginx
echo "✓ Deployment complete"
```

Make executable:
```bash
chmod +x /usr/local/bin/deploy-delni.sh
```

Run:
```bash
sudo /usr/local/bin/deploy-delni.sh
```

---

## 18. Post-Deploy Testing

```bash
# Check health
curl https://your-domain.com/

# Check API
curl https://your-domain.com/api/health

# Check admin panel
curl https://your-domain.com/cp/admin

# Check queue
supervisorctl status delni-queue:*

# Check cron
ps aux | grep artisan | grep schedule
```

---

## Troubleshooting

### Queue not processing jobs
```bash
supervisorctl restart delni-queue:*
tail -f /var/log/delni-queue.log
```

### Laravel 500 error
```bash
php artisan storage:link
chown -R www-data:www-data /var/www/delni/storage
php artisan optimize:clear
```

### Database connection refused
```bash
mysql -u delni -p -h 127.0.0.1
# Verify credentials in .env match
```

### Nginx 502 Bad Gateway
```bash
systemctl status php8.3-fpm
systemctl restart php8.3-fpm
```

---

## Security Hardening

### SSH key auth only (no passwords)
```bash
nano /etc/ssh/sshd_config
# Set: PasswordAuthentication no
systemctl restart sshd
```

### Fail2ban
```bash
apt install fail2ban
systemctl enable fail2ban
```

### Hide server info
```nginx
# In /etc/nginx/nginx.conf
server_tokens off;
```

---

## Performance Optimization

### Enable gzip compression
```nginx
# In /etc/nginx/nginx.conf
gzip on;
gzip_types text/plain text/css text/javascript application/json;
gzip_min_length 1000;
```

### Redis caching (optional, for high traffic)
```bash
apt install redis-server
systemctl start redis-server

# Update .env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
```

---

## Final Checklist

- [ ] System packages installed
- [ ] Code cloned from GitHub
- [ ] Composer & npm dependencies installed
- [ ] .env configured with production values
- [ ] Database created and migrations run
- [ ] File permissions set correctly
- [ ] Nginx configured and reloaded
- [ ] SSL certificate installed
- [ ] Queue worker running via Supervisor
- [ ] Scheduler cron configured
- [ ] Admin user created via `delni:setup-admin`
- [ ] Domain accessible via HTTPS
- [ ] Login works
- [ ] Search/marketplace works
- [ ] Emails send
- [ ] Queue jobs process
- [ ] Backups scheduled

✅ **Deployment Complete**
