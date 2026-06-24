#!/usr/bin/env sh
set -e

# One-shot release tasks (delni-deploy).
#
# Runs in a SINGLE container before the app container(s) start serving traffic:
#   - docker compose: the one-shot `migrate` service (app depends on its completion)
#   - Coolify: set this as the "Pre-deployment Command" (`delni-deploy`)
#
# Migrations intentionally run here — NOT on every app boot — so that rolling
# deploys never have two app containers migrating the schema concurrently.
#
# Safe to re-run: migrations are tracked, the role seeder is guarded, and
# delni:ensure-super-admin is idempotent.

php artisan migrate --force --no-interaction

ROLE_COUNT=$(php artisan tinker --execute "echo \Spatie\Permission\Models\Role::count();" 2>/dev/null | tail -n1)
if [ "${ROLE_COUNT:-0}" = "0" ]; then
    php artisan db:seed --class=RoleSeeder --force --no-interaction
fi

php artisan delni:ensure-super-admin --no-interaction
