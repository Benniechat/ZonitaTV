# Zonita TV — Production Deployment & Cloud Hosting Guide

**Document Version:** 1.0.0-master  
**Target Environments:** Supabase PostgreSQL, Render.com, Laravel Cloud, Docker/VPS  
**PHP Runtime:** PHP 8.3 FPM with OPcache & Nginx

---

## 1. Cloud Infrastructure Overview

```
[ Viewers / Client Web Players ]
              │
              ▼
    [ Cloudflare CDN & WAF ] (SSL/TLS, DDoS, Full Strict)
              │
              ▼
    [ Application Host ] (Render.com Web Service / Docker / Laravel Cloud)
    - PHP 8.3 FPM + Nginx
    - Vite Compiled Assets in public/build/
    - Cron Daemon running "php artisan schedule:run"
              │
              ├──────────────────────────────┐
              ▼                              ▼
    [ Supabase PostgreSQL ]        [ Cloudinary Enterprise ]
    (Transaction Pooler Port 6543)  (Logos, Banners, Watermarks)
```

---

## 2. Environment Variables Configuration

Copy `.env.production.example` to `.env` on your deployment server and configure the following required secrets:

```env
APP_NAME="Zonita TV"
APP_ENV=production
APP_KEY=base64:...             # Generated via php artisan key:generate
APP_DEBUG=false
APP_URL=https://zonitatv.com

# PostgreSQL / Supabase Database Configuration
DB_CONNECTION=pgsql
DB_HOST=aws-0-eu-central-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_REF
DB_PASSWORD=YOUR_STRONG_PASSWORD
DB_SSLMODE=require

# Session, Cache & Queue
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

# Cloudinary CDN Media Delivery
CLOUDINARY_CLOUD_NAME=YOUR_CLOUD_NAME
CLOUDINARY_API_KEY=YOUR_API_KEY
CLOUDINARY_API_SECRET=YOUR_API_SECRET

# Google OAuth Federation
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET
GOOGLE_REDIRECT_URI="https://zonitatv.com/auth/google/callback"
```

---

## 3. Production Deployment Commands

Run the following command sequence on release:

```bash
# 1. Update source code
git pull origin main

# 2. Install production PHP packages without development tools
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Build frontend assets
npm ci && npm run build

# 4. Run database migrations safely
php artisan migrate --force --no-interaction

# 5. Flush and warm up production caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Verify production readiness audit
php artisan production:verify --check-backup

# 7. Restart background queues
php artisan queue:restart
```

---

## 4. Crontab Daemon Setup

Zonita TV requires a single cron entry to execute continuous playout progression, schedule replenishment, health monitoring, and nightly backups:

```bash
* * * * * cd /var/www/zonitatv && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Health Check & Cloud Monitoring Probes

Configure your cloud provider's health check monitoring to query:

- **Path:** `/health` or `/api/health`
- **Expected Status:** `200 OK`
- **Interval:** 30 seconds
- **Timeout:** 5 seconds
- **Failure Threshold:** 3 consecutive failures triggers alert.
