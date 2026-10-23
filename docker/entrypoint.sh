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

# SQLite setup if DB_CONNECTION is sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ ! -f "$DB_PATH" ]; then
        echo "==> Creating SQLite database at ${DB_PATH}..."
        touch "$DB_PATH"
        if [ -f "/var/www/html/database/zoniva_master_schema_and_data.sql" ]; then
            echo "==> Seeding initial master catalogue from database/zoniva_master_schema_and_data.sql..."
            sqlite3 "$DB_PATH" < /var/www/html/database/zoniva_master_schema_and_data.sql || true
        fi
    fi
    chown -R www-data:www-data "$(dirname "$DB_PATH")"
    chmod 664 "$DB_PATH" || true
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure storage symlink exists
php artisan storage:link --no-interaction || true

# Run migrations
echo "==> Running database migrations..."
php artisan migrate --force --no-interaction || true

# If database has no channels (e.g. fresh Supabase PostgreSQL), seed initial catalogue
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
