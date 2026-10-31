# Zonita TV — Production Operations Runbook & System Handover Guide

> **Document Version:** 1.0.0-production  
> **Target Audience:** Site Reliability Engineers (SRE), Master Control Room (MCR) Directors, Rights Compliance Officers, and Platform Administrators.  
> **Jurisdiction Context:** Federal Republic of Nigeria (NDPA compliance) & International Copyright Safe Harbors (DMCA).

---

## 1. System Architecture & Topology

Zonita TV operates as a resilient, 24/7 digital broadcast and linear television guide platform built on Laravel 12 and PHP 8.3.

```
                           [ Cloudflare Edge CDN & WAF ]
                                         │
                   ┌─────────────────────┴─────────────────────┐
                   ▼                                           ▼
         [ Web / Player Requests ]                   [ Background Workers ]
                   │                                           │
         ┌─────────┴─────────┐                       ┌─────────┴─────────┐
         ▼                   ▼                       ▼                   ▼
    Nginx / PHP-FPM     REST APIs / XMLTV       Cron Scheduler      Queue Workers
         │                   │                       │                   │
         └─────────┬─────────┴───────────────────────┴─────────┬─────────┘
                   ▼                                           ▼
       [ PostgreSQL / Supabase ]                     [ Cloudinary Media CDN ]
       (Transactional & Playout)                     (Logos, Banners, Watermarks)
```

---

## 2. Pre-Flight Production Deployment Checklist

Before switching DNS or initiating a new production release:

- [ ] **Generate Environment Secrets:** Ensure `APP_KEY` is generated via `php artisan key:generate`.
- [ ] **Production Flags:** Ensure `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- [ ] **HTTPS Enforcement:** SSL/TLS certificate installed (Cloudflare Full Strict or Let's Encrypt).
- [ ] **Database Connection:** Verified pooling credentials for PostgreSQL / Supabase with `DB_SSLMODE=require`.
- [ ] **Storage Permissions:** Ensure `storage/` and `bootstrap/cache/` are owned by `www-data` (`chmod -R 775`).
- [ ] **Asset Compilation:** Run `npm run build` so Vite manifest (`public/build/manifest.json`) is populated.
- [ ] **Cloudinary CDN:** Verify valid `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, and `CLOUDINARY_API_SECRET`.
- [ ] **Production Readiness Audit:** Run `php artisan production:verify --check-backup` (Must exit `0`).

---

## 3. Standard Deployment Command Sequence

Execute the following pipeline on every production deployment:

```bash
# 1. Fetch latest verified commit from main branch
git fetch origin main && git checkout main && git pull origin main

# 2. Install production dependencies without dev tools
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Compile optimized frontend assets
npm ci && npm run build

# 4. Run database migrations safely
php artisan migrate --force --no-interaction

# 5. Flush and rebuild high-speed production caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Verify production readiness
php artisan production:verify

# 7. Restart queue workers (if supervisor/systemd daemonized)
php artisan queue:restart
```

---

## 4. Cron Scheduler & Daemon Configuration

Zonita TV requires a single standard cron entry on the server. The internal scheduler manages all recurring playout, rights, and backup jobs:

### Server Crontab
```bash
* * * * * cd /var/www/zonitatv && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Task Inventory

| Frequency | Command / Task | Function |
|---|---|---|
| **Every Minute** | `php artisan playout:advance --replenish` | Advances linear channels, detects dead streams, and rotates programs. |
| **Every 5 Minutes**| `php artisan sports:run-scheduler --once` | Synchronizes live sports fixture states and kick-off states. |
| **Every 15 Minutes**| `zonita:monitor-schedule-health` | Scans all channels to prevent empty playout queues. |
| **Every 15 Minutes**| `php artisan sports:verify-broadcasts` | Re-verifies broadcast rights for upcoming match fixtures. |
| **Every 30 Minutes**| `php artisan sports:sync-fixtures` | Ingests new match fixtures and tournament schedules. |
| **Hourly** | `zonita:update-video-eligibility` | Checks video availability status via YouTube API. |
| **Every 6 Hours** | `zonita:refresh-channel-queues` | Replenishes 48-hour rolling schedule buffers for virtual stations. |
| **Daily (00:05)** | `zonita:daily-broadcast-reports` | Calculates daily channel uptime %, viewer counts, and SLA metrics. |
| **Daily (01:00)** | `php artisan rights:monitor-expirations` | Audits license expirations and auto-delists non-compliant streams. |
| **Daily (02:00)** | `php artisan zoniva:export-sql` | Exports zero-memory streamed ANSI SQL backup dump. |

---

## 5. MCR Playout Continuity & Incident Triage Runbook

### Incident A: Video Stream Failure (Client reports Error 150 / Geo-block)
1. **Automated Recovery:** The client player instantly posts to `/api/playback/report-failure`. The `PlaybackContinuityService` replaces the broken item with the channel's designated fallback video slate and trips the circuit breaker on the broken video.
2. **Dashboard Verification:** Visit `/admin/dashboard`. The channel status will display whether fallback is active.
3. **Manual Override:** Navigate to `/admin/channels/{id}` &rarr; Edit Playout Pool &rarr; Replace video ID with a verified source.

### Incident B: Channel Running Out of Scheduled Programming
1. **Diagnosis:** Run `php artisan playout:advance --replenish`.
2. **Replenishment:** To regenerate a 7-day rolling schedule for all virtual stations:
   ```bash
   php artisan epg:generate-rolling --days=7
   ```

---

## 6. Content Rights & Takedown SLA Runbook

Zonita TV enforces a strict **24-hour SLA** for all copyright and DMCA inquiries:

1. **Intake Desk:** Takedowns arrive through `/report-content` or email to `copyright@zonitatv.com`.
2. **Review Console:** Navigate to `/admin/support` & `/admin/compliance-audit`.
3. **Takedown Execution:**
   - Change ticket status to `resolved` or `action_taken`.
   - The channel or video is immediately delisted from the public catalogue and EPG grid.
4. **Emergency Delist via CLI:**
   ```bash
   php artisan tinker --execute "App\Models\Channel::where('slug', 'violating-channel')->update(['is_published' => false, 'is_active' => false]);"
   ```

---

## 7. Disaster Recovery & Backup Restoration

### Automated Nightly Backups
- Generated daily at 02:00 UTC by `ExportDatabaseSqlDumpCommand` to `database/zoniva_master_schema_and_data.sql`.
- Employs zero-memory chunked streaming (`chunk(200)`), making it safe for arbitrarily large databases.

### Manual Backup Generation
```bash
php artisan zoniva:export-sql --target=database/backups/manual_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Database Restoration
```bash
# SQLite Restoration:
sqlite3 database/database.sqlite < database/zoniva_master_schema_and_data.sql

# PostgreSQL / Supabase Restoration:
psql "postgresql://user:pass@host:port/dbname?sslmode=require" < database/zoniva_master_schema_and_data.sql
```

---

## 8. Monitoring & Observability Endpoints

- **Live System Health JSON:** `GET https://zonitatv.com/health` and `GET https://zonitatv.com/api/health`
  - Returns `200 OK` with component breakdown (`database`, `cache`, `storage`, `playout`).
  - Returns `503 Service Unavailable` if core dependencies fail.
- **Lightweight Load Balancer Ping:** `GET https://zonitatv.com/ping` and `GET https://zonitatv.com/api/ping`
- **RFC-Compliant XMLTV Feed:** `GET https://zonitatv.com/api/epg/xmltv` (Cached 300s).
- **Admin Control Desk:** `https://zonitatv.com/admin/dashboard`
- **Analytics & Audience Radar:** `https://zonitatv.com/admin/analytics`
- **Statutory Rights Compliance Audit:** `https://zonitatv.com/admin/compliance-audit`
