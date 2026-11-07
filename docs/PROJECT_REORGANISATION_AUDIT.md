# Zonita TV — Comprehensive Project Reorganisation & Architectural Audit

**Document Version:** 1.0.0-master  
**Audit Date:** 2026-11-07  
**Auditor:** Principal Software Architect & QA Lead  
**Scope:** Full repository audit across backend, frontend, database schemas, background workers, security controls, testing harnesses, and deployment configuration.

---

## 1. Executive Summary

Zonita TV is an enterprise-grade digital broadcast and virtual linear television platform built on Laravel 12 / PHP 8.3 with Vite, Tailwind CSS, and Alpine.js. The platform orchestrates 260+ channels across five core content verticals (Nollywood, Stand-up & Sitcom Comedy, Gospel Cinema, Islamic TV, and Continental Sports).

This audit establishes a complete architectural baseline before executing the systematic reorganisation defined in the Zonita TV Masterplan. Rather than discarding or rebuilding functional code, our strategy adopts a **safe, non-destructive reorganisation procedure** ensuring:
- Zero data loss across existing channels, playout schedules, and user preferences.
- 100% preservation of verified business logic and test coverage.
- Clear structural boundaries between discovery, catalogue curation, rights compliance, playout scheduling, and playback execution.

---

## 2. Current Architecture & Runtime Environment

### 2.1 Topology
- **Web & Application Tier:** PHP 8.3 FPM, Laravel 12 framework, Nginx HTTP reverse proxy with Cloudflare edge caching.
- **Frontend Presentation:** Blade templates styled with Tailwind CSS, reactive client state powered by Alpine.js, compiled via Vite.
- **Data Persistence:**
  - Production: Managed PostgreSQL via Supabase (connection pooler on port 6543, SSL mode required).
  - Development / CI/CD: SQLite 3.35+ with transactional database refresh during testing.
- **Asset Storage & CDN:** Cloudinary Enterprise media storage (channel logos, hero banners, watermarks, promo thumbnails).
- **Video Delivery:** Multi-mode playback architecture (Mode A: Direct HLS, Mode B: Official YouTube API embeds with fallback continuity, Mode C: Third-party licensed streams, Mode D: Zonita Originals).
- **Asynchronous Processing:** Database-backed queue workers for report generation, schedule health monitoring, and video availability re-verification.

### 2.2 Directory Structure
```
c:\Users\barns\OneDrive\Desktop\ZonivaTV
├── app/
│   ├── Console/Commands/        # Artisan CLI commands (playout, epg, export, health)
│   ├── Http/
│   │   ├── Controllers/         # Viewer & Admin domain controllers
│   │   │   ├── Admin/           # 15 Administrative controllers
│   │   │   ├── Api/             # REST API controllers (playback, recommendations)
│   │   │   └── Auth/            # Authentication controllers (standard + Google OAuth)
│   │   └── Middleware/          # Security headers, role guards, proxy trust
│   ├── Jobs/Zonita/             # Background queue jobs
│   ├── Models/                  # 41 Eloquent models
│   └── Services/                # Service layer (Zonita playout, rights, health, etc.)
├── bootstrap/                   # Application bootstrap & middleware pipeline
├── config/                      # Configuration files (database, cache, services)
├── database/
│   ├── factories/               # Model factories for testing
│   ├── migrations/              # 32 Schema migrations
│   └── seeders/                 # Database seeders (channel catalogues, categories)
├── docs/                        # Authoritative documentation repository
├── public/                      # Web root (Vite build assets, static images)
├── resources/
│   ├── css/ & js/               # Frontend source assets
│   └── views/                   # Blade templates (admin, channels, guide, portals)
├── routes/                      # Route definitions (web.php, console.php)
└── tests/                       # Automated test suite (Feature & Unit)
```

---

## 3. Existing Functional Modules & Implementation Status

| Functional Module | Components | Current State | Audit Finding |
|---|---|:---:|---|
| **Core Application** | Config, Middleware, Exception Handler | Implemented | Security headers, CSP, and rate limiting in place. |
| **Authentication & Users** | `AuthController`, `GoogleAuthController`, `RequireRole` | Implemented | Dual email/username login; 5 distinct user roles. |
| **Viewer Discovery** | `HomeController`, `ChannelController`, `SearchController` | Implemented | 2-tier navigation; 4-column mega-menu; category filtering. |
| **Content Catalogue** | `ContentCatalogueController`, `Video`, `Movie`, `Series` | Implemented | Unified hierarchy supporting movies, series, episodes. |
| **Content Discovery** | `DiscoveryWorkbenchController`, `DiscoveryCandidate` | Implemented | Deduplication hash; manual verification workflow. |
| **Rights & Compliance** | `RightsAdminController`, `RightsAuditController`, `ContentRight` | Implemented | Multi-tier licensing; 24h DMCA takedown intake. |
| **Partner Management** | `PartnerAdminController`, `ContentPartner`, `DistributionAgreement`| Implemented | CRM pipeline tracking and financial terms modeling. |
| **Virtual Channels** | `ChannelAdminController`, `VirtualChannelManagerService` | Implemented | 24/7 channel creator; station branding; fallback slates. |
| **EPG & Scheduling** | `GuideController`, `ProgrammeScheduleService`, `XmltvExportService`| Implemented | 7-day rolling schedule; multi-timezone; RFC XMLTV feed. |
| **Playback & Playout** | `PlaybackController`, `PlaybackContinuityService` | Implemented | Auto-fallback circuit breaker; live telemetry polling. |
| **Live Sports** | `SportsController`, `SportsAdminController`, `Fixture` | Implemented | Fixture discovery; statutory rights gating; killswitch. |
| **Vertical Portals** | `ComedyController`, `NollywoodController`, `FaithController` | Implemented | Curated showcases with 24/7 dedicated linear feeds. |
| **AI Personalisation** | `RecommendationService`, `UserPreference`, `/my-guide` | Implemented | Weighted ranking algorithm; explainable rationale tags. |
| **Admin Operations** | `DashboardController`, `AnalyticsAdminController` | Implemented | Executive dashboard; real-time MCR playout monitor. |
| **Alerts & Support** | `SupportTicketAdminController`, `AlertNotificationService` | Implemented | Automated stream failure alerts; support desk. |
| **Production Health** | `HealthCheckController`, `ProductionVerifyCommand` | Implemented | Deep diagnostics (/health, /api/health); zero-memory backup. |

---

## 4. Identified Organisational & Structural Issues

1. **Legacy Test Authentication Decoupling:** Three earlier integration test files (`CloudinaryIntegrationTest`, `ZonitaProgrammingEngineTest`, `ZonivaTvTest`) contained requests written prior to enforcing role-based middleware on `/admin/*`, causing unexpected 302 redirects.
2. **Database Seeder Weight in Testing:** `ZonivaTvTest` invokes `DatabaseSeeder` which populates 260 channels and schedules, resulting in lengthy execution times (up to 30 seconds) on SQLite test runs.
3. **Documentation Fragmentation:** Project documentation was split across scattered markdown files with varying degrees of completeness.
4. **Service Class Separation:** While `app/Services/Zonita/` cleanly encapsulates playout automation, general services (`RightsEligibilityService`, `RecommendationService`, `SystemHealthService`) reside in the root `app/Services` directory and require clear organizational categorization.

---

## 5. Security & Database Concerns

1. **Role Middleware Enforcement:** Verified that all `/admin/*` routes strictly require `role:super_admin,content_manager,rights_reviewer,advertising_manager`.
2. **CSP & Media Domains:** Content Security Policy in `SecurityHeadersMiddleware` must explicitly permit Cloudinary CDN (`res.cloudinary.com`) and YouTube embed frames (`www.youtube.com`, `www.youtube-nocookie.com`).
3. **Database Memory Exhaustion on SQL Dumps:** The legacy database export command previously concatenated rows into an in-memory string variable. This was remediated with chunked file streaming (`chunk(200)` via `fwrite()`), protecting production memory limits.
4. **Foreign Key Integrity on Channel Deletion:** Channels have foreign key relationships with `content_rights`, `schedule_entries`, and `favourites`. Soft deletion (`deleted_at`) is implemented on `Channel` to prevent cascading orphaned data.

---

## 6. Recommended Structural Changes & Target Modular Architecture

To align with Part 3 of the Master Specification, the project modules are mapped into the following logical architecture:

```
[ Zonita TV Platform Architecture ]
├── 1. Core Module            -> App Configuration, Middleware, Security Headers, Exceptions
├── 2. Identity & Access      -> Authentication, Roles, Permissions, User Profiles, Preferences
├── 3. Viewer Discovery       -> Public Homepage, 2-Tier Header, Mega-Menu, Channel Views, Search
├── 4. Content Catalogue      -> Unified Video/Movie/Series Hierarchy, Metadata & Curation
├── 5. Content Discovery      -> Ingestion Pipeline, Candidate Staging, Deduplication Engine
├── 6. Rights & Compliance    -> Licensing Ledger, Territorial Restrictions, DMCA Takedown Desk
├── 7. Acquisition & Partners -> Broadcaster CRM, Bilateral Distribution Contracts
├── 8. Virtual Stations       -> 24/7 Channel Management, Station Idents, Playout Rules
├── 9. Scheduler & EPG        -> Rolling Schedule Generator, Conflict Resolver, XMLTV Feeds
├── 10. Playback & Broadcast  -> Multi-Mode Player, Circuit Breaker Fallback, Health Heartbeats
├── 11. Live Sports Engine    -> Fixture Discovery, Mode A/B/C Switching, Emergency Killswitch
├── 12. Vertical Portals      -> Comedy, Nollywood, Gospel Cinema, Islamic TV Showcase Hubs
├── 13. AI & Personalisation  -> Recommendation Ranking Formula, Explainable Badges, /my-guide
├── 14. Admin Control Hub     -> Executive Operations, Audience Analytics, Compliance Audits
├── 15. Alerts & Support      -> Outage Notifications, Problem Report Desk, WhatsApp Broadcasts
└── 16. SRE & Handover        -> Deep Health Diagnostics, Streamed Backups, Operations Runbook
```

---

## 7. Data Preservation Requirements

- **Zero Data Loss:** Existing 260+ seeded channels, categories, videos, and schedule blocks must be retained.
- **No Schema Drops:** All schema changes must use forward-only migrations.
- **Configuration Parity:** Local SQLite and production PostgreSQL/Supabase configurations must remain fully functional.

---

## 8. Recommended Implementation Sequence

Following the Masterplan, the reorganization will be tracked and audited across the established 22 batches (Batches 0–21) with independent QA sign-off at each milestone.
