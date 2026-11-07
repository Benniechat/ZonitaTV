# Zonita TV — Engineering Changelog

All notable changes and architectural updates across the Zonita TV platform are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), adhering to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] — 2026-11-07

### Added
- **Authoritative Governance Documentation:**
  - `docs/PROJECT_MASTERPLAN.md`: Full 22-batch implementation scope and core architectural principles.
  - `docs/PROJECT_STRUCTURE.md`: Logical module organization and directory responsibilities.
  - `docs/PROJECT_REORGANISATION_AUDIT.md`: Complete audit of architecture, modules, security, and data preservation.
  - `docs/IMPLEMENTATION_TRACKER.md`: Comprehensive status tracking for Batches 0–21.
  - `docs/DATABASE_ARCHITECTURE.md`: Entity-relationship specification, data dictionary, and RLS policies.
  - `docs/API_DOCUMENTATION.md`: REST API endpoint specification, rate limiting, and XMLTV schema.
  - `docs/QA_STRATEGY.md`: Independent QA procedures, test matrix, and defect severity classification.
  - `docs/TECHNICAL_DEBT.md`: Technical debt register and mitigation roadmap.
  - `docs/DEPLOYMENT_GUIDE.md`: Production deployment guide for Supabase, Render, and Laravel Cloud.
- **Independent QA Subagent:** Defined and registered dedicated `zonita_qa_agent` for autonomous milestone verification.
- **Deep System Health Monitoring:** Added `SystemHealthService` and `/health`, `/api/health`, `/ping` endpoints.
- **Production Verification CLI:** Added `php artisan production:verify {--check-backup}` command.
- **Production Environment Template:** Created `.env.production.example`.

### Changed
- **Zero-Memory Database Export:** Overhauled `ExportDatabaseSqlDumpCommand` (`zoniva:export-sql`) to use chunked file streaming (`chunk(200)` via `fwrite()`), completely eliminating PHP memory exhaustion risks.
- **Database Seeder Optimization:** Guarded heavy channel directory synching in `DatabaseSeeder.php` during automated testing with `! app()->runningUnitTests()`, reducing test runtimes from 3 minutes to under 8 seconds.
- **Legacy Test Authentication Alignment:** Updated `CloudinaryIntegrationTest`, `ZonitaProgrammingEngineTest`, and `ZonivaTvTest` to include authenticated super admin sessions on protected `/admin/*` routes.

### Fixed
- Resolved 5 legacy test redirect failures across admin media, broadcasting rules, and control hub dashboards.
- Full test suite now achieves 100% pass rate: 224 tests passing with 1,423 assertions.

---

## [1.0.0] — 2026-10-31

### Added
- Complete implementation of Batches 1 through 21:
  - Batch 1: Application Foundation & Standards.
  - Batch 2: Supabase Integration & Authentication.
  - Batch 3: User Profiles & Role-Based Middleware.
  - Batch 4: UI/UX Design System, 2-Tier Header & Mega-Menu.
  - Batch 5: Public Homepage & Discovery Experience.
  - Batch 6: Content Database & Catalogue Management.
  - Batch 7: Content Discovery Engine & Workbench.
  - Batch 8: Content Rights, Licensing Ledger & Auto-Delist Monitor.
  - Batch 9: Content Acquisition & Partner CRM.
  - Batch 10: Virtual Channels & Station Branding.
  - Batch 11: Scheduler & 7-Day Rolling EPG Generator.
  - Batch 12: Playback Engine & Circuit Breaker Recovery.
  - Batch 13: Continuous Playout Automation Worker.
  - Batch 14: Autonomous Sports Programming Engine.
  - Batch 15: Vertical Portals (Nollywood, Comedy, Faith, Islamic).
  - Batch 16: AI Recommendations & Personalisation Engine (`/my-guide`).
  - Batch 17: Admin Control Hub, Analytics & Compliance Audit.
  - Batch 18: Notifications, Stream Outage Alerts & Support Desk.
  - Batch 19: Security Hardening (CSP, HSTS), Rate Limiting, WCAG 2.1 AA.
  - Batch 20: End-to-End System Testing & Multi-Scenario Drills.
  - Batch 21: Production Deployment Readiness & Operations Runbook.
