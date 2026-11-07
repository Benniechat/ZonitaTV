# Zonita TV — Technical Debt & Architectural Risk Register

**Document Version:** 1.0.0-master  
**Last Updated:** 2026-11-07  
**Lead Architect:** Principal Software Architect

---

## 1. Technical Debt Inventory & Resolution Status

| Debt Item | Affected Subsystem | Severity | Current Status | Remediation Description |
|---|---|:---:|:---:|---|
| **SQL Export Memory Spikes** | Database CLI Commands | High | **RESOLVED** | Refactored `ExportDatabaseSqlDumpCommand` from in-memory string accumulation to chunked stream buffered file writing (`chunk(200)` via `fwrite()`). |
| **Legacy Test Auth Desynchronization** | Testing Harness | Medium | **RESOLVED** | Updated `CloudinaryIntegrationTest`, `ZonitaProgrammingEngineTest`, and `ZonivaTvTest` with proper `actingAs($admin)` role mocks to align with Batch 3/17 role-based access control. |
| **DatabaseSeeder Test Overhead** | Automated Testing | Medium | **RESOLVED** | Guarded heavy directory syncs in `DatabaseSeeder.php` with `! app()->runningUnitTests()`, reducing test runtimes from 3 minutes to under 8 seconds. |
| **Dual Database SQLite/PostgreSQL Parity** | Database Layer | Medium | **MANAGED** | Strict ANSI SQL compatibility enforced across all queries. Raw driver-specific SQL is strictly prohibited. |
| **EPG XMLTV Generation Latency** | XMLTV Feed | Medium | **RESOLVED** | Cached XMLTV feed for 300 seconds with HTTP `Cache-Control: public, max-age=300`. |
| **Intermediate Viewport Navigation Clipping** | Frontend UI | Low | **RESOLVED** | Restructured top header into a 2-tier responsive navigation bar with a consolidated 4-column mega-menu. |

---

## 2. Ongoing Monitoring & Risk Mitigation

1. **YouTube Embed Error Code 150/101:** Upstream YouTube videos may become unplayable due to regional copyright restrictions or creator takedowns. Mitigated by `PlaybackContinuityService` and channel fallback emergency slates.
2. **PostgreSQL Connection Pool Saturation:** In high-traffic scenarios on Supabase, direct connections can exhaust limits. Mitigated by enforcing port 6543 pooler mode with `DB_SSLMODE=require`.
3. **Storage Disk Usage:** Video thumbnails and temporary dumps are managed via Cloudinary CDN and automatic temporary file purging.
