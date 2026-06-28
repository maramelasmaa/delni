#!/usr/bin/env sh
set -e

# One-shot release tasks (delni-deploy).
#
# Runs in a SINGLE container before the app container(s) start serving traffic:
#   - docker compose: the one-shot `migrate` service (app depends on its completion)
#   - Coolify: optionally set this as the "Pre-deployment Command" (`delni-deploy`)
#
# Migrations intentionally run here — NOT on every app boot — so that rolling
# deploys never have two app containers migrating the schema concurrently.
#
# Pick ONE release path in production. Do not enable both the compose `migrate`
# service and a Coolify pre-deployment command at the same time, or deploy-time
# tasks will run twice.
#
# Safe to re-run: migrations are tracked, the role seeder is guarded, and
# delni:ensure-super-admin is idempotent.

# Wait for MySQL to accept TCP connections. On a fresh data volume the mysql image
# briefly reports "healthy" via its local socket before the networked server is up,
# so depends_on alone can let us race in and hit "Connection refused". Retry the real
# PDO connection (pdo_mysql is already installed) until it succeeds.
echo "Waiting for database to accept connections..."
ATTEMPTS=0
until php -r '
    $host = getenv("DB_HOST") ?: "127.0.0.1";
    $port = getenv("DB_PORT") ?: "3306";
    $db   = getenv("DB_DATABASE");
    $user = getenv("DB_USERNAME");
    $pass = getenv("DB_PASSWORD");
    new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
' 2>/dev/null; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ "$ATTEMPTS" -ge 60 ]; then
        echo "Database did not become reachable after 60 attempts; aborting." >&2
        exit 1
    fi
    echo "  database not ready (attempt ${ATTEMPTS}), retrying in 3s..."
    sleep 3
done
echo "Database is up."

php artisan migrate --force --no-interaction

ROLE_COUNT=$(php artisan tinker --execute "echo \Spatie\Permission\Models\Role::count();" 2>/dev/null | tail -n1)
if [ "${ROLE_COUNT:-0}" = "0" ]; then
    php artisan db:seed --class=RoleSeeder --force --no-interaction
fi

# Sync bundled SVG icons onto the icons volume + upsert their rows (idempotent).
php artisan db:seed --class=IconSeeder --force --no-interaction

php artisan delni:ensure-super-admin --no-interaction
