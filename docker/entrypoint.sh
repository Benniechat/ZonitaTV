#!/bin/sh
set -e

# Port configuration for Render ($PORT is provided dynamically by Render, defaults to 10000)
PORT="${PORT:-10000}"
sed -i "s/LISTEN_PORT/${PORT}/g" /etc/nginx/http.d/default.conf

echo "==> Starting Zonita TV Playout Network on port ${PORT}..."

# Ensure required framework directories exist with appropriate permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Clear stale caches
rm -f /var/www/html/bootstrap/cache/*.php

# Validate APP_KEY format for AES-256-CBC (must be 32 bytes or base64 decoding to 32 bytes)
VALID_KEY=$(php -r '
    $key = getenv("APP_KEY");
    if ($key && str_starts_with($key, "base64:")) {
        $decoded = base64_decode(substr($key, 7), true);
        if ($decoded !== false && strlen($decoded) === 32) {
            echo $key;
            exit(0);
        }
    } elseif ($key && strlen($key) === 32) {
        echo $key;
        exit(0);
    }
    echo "";
' 2>/dev/null || true)

if [ -z "$VALID_KEY" ]; then
    echo "==> APP_KEY is missing or invalid for AES-256-CBC. Generating new valid application key..."
    APP_KEY=$(php artisan key:generate --show --no-interaction 2>/dev/null || echo "base64:4k8p5Q7vF3s1w9Z2x8L4b6N8m0Q2r4T6v8X0z2B4d6F=")
    export APP_KEY="$APP_KEY"
    echo "==> Generated APP_KEY=${APP_KEY}"
else
    export APP_KEY="$VALID_KEY"
fi

# Write initial runtime .env file so Artisan commands and PHP-FPM workers read valid config
write_env_file() {
    cat <<EOF > /var/www/html/.env
APP_NAME="${APP_NAME:-Zonita TV}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-}"
DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_SSLMODE="${DB_SSLMODE:-require}"
DATABASE_URL="${DATABASE_URL:-}"

SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
CACHE_STORE=file
EOF
    chown www-data:www-data /var/www/html/.env
    chmod 664 /var/www/html/.env
}

write_env_file

# Initialize SQLite database if configured
init_sqlite() {
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ ! -f "$DB_PATH" ] || [ ! -s "$DB_PATH" ]; then
        echo "==> Initializing SQLite database at ${DB_PATH}..."
        touch "$DB_PATH"
        if [ -f "/var/www/html/database/zoniva_master_schema_and_data.sql" ]; then
            echo "==> Populating master catalogue from database/zoniva_master_schema_and_data.sql..."
            sqlite3 "$DB_PATH" < /var/www/html/database/zoniva_master_schema_and_data.sql || true
        fi
    fi
    chown -R www-data:www-data "$(dirname "$DB_PATH")"
    chmod -R 775 "$(dirname "$DB_PATH")"
    chmod 664 "$DB_PATH" || true
}

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    init_sqlite
fi

# Ensure storage symlink exists
php artisan storage:link --no-interaction || true

# Test database connection
echo "==> Testing database connection (${DB_CONNECTION:-sqlite})..."
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
    if [ "${DB_CONNECTION}" = "pgsql" ]; then
        echo "==> Note: Direct Supabase hostname (db.*.supabase.co) is IPv6-only."
        echo "==> If your Render instance does not support IPv6, switch to the Supabase Connection Pooler: aws-0-[region].pooler.supabase.com:5432"
        echo "==> Activating embedded SQLite fail-safe to prevent 500 error..."
        export DB_CONNECTION=sqlite
        export DB_DATABASE=/var/www/html/database/database.sqlite
        export DATABASE_URL=""
        init_sqlite
        write_env_file
    fi
fi

# Run migrations
echo "==> Running database migrations..."
php artisan migrate --force --no-interaction || true

# Seed database if channels table is empty
CHANNEL_COUNT=$(php artisan tinker --execute 'echo \App\Models\Channel::count();' 2>/dev/null || echo "0")
if [ "$CHANNEL_COUNT" = "0" ]; then
    echo "==> Fresh database detected. Seeding full channel catalogue and 24/7 schedules..."
    php artisan db:seed --force --no-interaction || true
fi

# Production optimizations caching
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Optimizing configuration, route, and view caches..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Broadcast Server initialized. Handing over to supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
