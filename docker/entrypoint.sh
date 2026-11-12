#!/bin/sh
set -e

# Port configuration for Render ($PORT is provided dynamically by Render, defaults to 10000)
PORT="${PORT:-10000}"
sed -i "s/LISTEN_PORT/${PORT}/g" /etc/nginx/http.d/default.conf

echo "==> Starting Zonita TV Playout Network on port ${PORT}..."

# 1. Ensure required framework directories and SQLite database exist with write permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/database

# Ensure default SQLite database file exists immediately
DB_SQLITE_PATH="/var/www/html/database/database.sqlite"
if [ ! -f "$DB_SQLITE_PATH" ]; then
    echo "==> Creating default SQLite database file at ${DB_SQLITE_PATH}..."
    touch "$DB_SQLITE_PATH"
fi

# Set directory permissions for www-data and CLI
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 664 "$DB_SQLITE_PATH" || true

# Clear stale caches
rm -f /var/www/html/bootstrap/cache/*.php

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "==> No APP_KEY provided in environment. Generating application key..."
    APP_KEY=$(php artisan key:generate --show --no-interaction 2>/dev/null || echo "base64:4k8p5Q7vF3s1w9Z2x8L4b6N8m0Q2r4T6v8X0z2B4d6F=")
    export APP_KEY="$APP_KEY"
    echo "==> Generated APP_KEY=${APP_KEY}"
fi

# Ensure storage symlink exists
php artisan storage:link --no-interaction || true

# Enforce HTTPS scheme on APP_URL for production
if [ -n "$APP_URL" ]; then
    if [ "${APP_URL#http://}" != "$APP_URL" ]; then
        APP_URL="https://${APP_URL#http://}"
    elif [ "${APP_URL#https://}" = "$APP_URL" ]; then
        APP_URL="https://${APP_URL}"
    fi
fi
APP_URL="${APP_URL:-https://zonitatv.onrender.com}"

# Function to generate runtime .env file
write_env() {
    cat <<EOF > /var/www/html/.env
APP_NAME="${APP_NAME:-Zonita TV}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL}"

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION="${DB_CONNECTION}"
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_SSLMODE="${DB_SSLMODE:-require}"
$( [ -n "$DATABASE_URL" ] && echo "DATABASE_URL=\"${DATABASE_URL}\"" )

GOOGLE_CLIENT_ID="${GOOGLE_CLIENT_ID:-}"
GOOGLE_CLIENT_SECRET="${GOOGLE_CLIENT_SECRET:-}"

SESSION_DRIVER="${SESSION_DRIVER:-file}"
QUEUE_CONNECTION=sync
CACHE_STORE=file
MAIL_MAILER="${MAIL_MAILER:-log}"
EOF
}

# 2. Detect remote database vs SQLite configuration
IS_REMOTE_DB=false
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ] || [ "${DB_CONNECTION}" = "pgsql" ] || [ "${DB_CONNECTION}" = "mysql" ]; then
    IS_REMOTE_DB=true
    if [ -z "$DB_CONNECTION" ]; then
        if echo "${DATABASE_URL:-}" | grep -q "^mysql" || [ "${DB_PORT:-}" = "3306" ]; then
            DB_CONNECTION="mysql"
        else
            DB_CONNECTION="pgsql"
        fi
    fi
    if [ -z "$DB_DATABASE" ] || [ "$DB_DATABASE" = "$DB_SQLITE_PATH" ]; then
        if [ "$DB_CONNECTION" = "pgsql" ]; then
            DB_DATABASE="postgres"
        else
            DB_DATABASE="forge"
        fi
    fi
else
    DB_CONNECTION="sqlite"
    DB_DATABASE="$DB_SQLITE_PATH"
fi

# Write initial .env file
write_env

# 3. Test database connection
if [ "$IS_REMOTE_DB" = "true" ]; then
    echo "==> Testing remote database connection (${DB_CONNECTION}: ${DB_HOST:-$DATABASE_URL})..."
    DB_STATUS=$(php -r '
    try {
        require __DIR__."/vendor/autoload.php";
        $app = require_once __DIR__."/bootstrap/app.php";
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "OK";
    } catch (\Throwable $e) {
        echo "FAIL: " . $e->getMessage();
    }
    ' 2>/dev/null || echo "FAIL: Unknown error")

    echo "==> Database check result: ${DB_STATUS}"

    if echo "$DB_STATUS" | grep -q "FAIL"; then
        echo "==> WARNING: Remote database connection failed: ${DB_STATUS}"
        if echo "${DB_HOST:-}" | grep -q "supabase\.co"; then
            echo "==> Note: Direct Supabase hostname (db.*.supabase.co) is IPv6-only."
            echo "==> If your host does not support IPv6, switch to the Supabase Connection Pooler: aws-0-[region].pooler.supabase.com:5432"
        fi
        echo "==> Activating embedded SQLite fail-safe to prevent server outage..."
        DB_CONNECTION="sqlite"
        DB_DATABASE="$DB_SQLITE_PATH"
        DATABASE_URL=""
        write_env
    else
        echo "==> Successfully connected to remote database (${DB_CONNECTION})."
    fi
else
    echo "==> Using local SQLite database at ${DB_SQLITE_PATH}."
fi

# 4. If using SQLite, populate schema/data if fresh
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -s "$DB_SQLITE_PATH" ] && [ -f "/var/www/html/database/zoniva_master_schema_and_data.sql" ]; then
        echo "==> Fresh SQLite database detected. Populating master catalogue from database/zoniva_master_schema_and_data.sql..."
        sqlite3 "$DB_SQLITE_PATH" < /var/www/html/database/zoniva_master_schema_and_data.sql || true
    fi
    chmod 664 "$DB_SQLITE_PATH" || true
    chown www-data:www-data "$DB_SQLITE_PATH" || true
fi

# 5. Run migrations
echo "==> Running database migrations..."
php artisan migrate --force --no-interaction

# 6. Seed database if channels table is empty
CHANNEL_COUNT=$(php artisan tinker --execute 'echo \App\Models\Channel::count();' 2>/dev/null || echo "0")
if [ "$CHANNEL_COUNT" = "0" ]; then
    echo "==> Fresh database detected. Seeding full channel catalogue and 24/7 schedules..."
    php artisan db:seed --force --no-interaction || true
fi

# 7. Production optimizations caching
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Optimizing configuration, route, and view caches..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Broadcast Server initialized. Handing over to supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
