# Zonita TV — Permanent Operating Skill & Engineering Guide

## 1. Project Identity & Purpose
- **Project Name:** Zonita TV
- **Tagline:** Discover More. Watch Smarter.
- **Founder:** Wada Oluwapelumi Benedict
- **Operating Entity:** BennieChat TechWealth Solutions
- **Primary Objective:** Build, operate, and maintain an automated, intelligent virtual television network platform delivering continuous linear broadcasting, metadata-driven content discovery, rights tracking, and scheduled entertainment across web, mobile, and connected devices.

---

## 2. Architecture & Technology Stack
- **Framework & Runtime:** PHP 8.3 / Laravel 11.x (currently 13.37.x core)
- **Database Architecture:**
  - Local / Staging: SQLite (`database/database.sqlite`)
  - Production (Cloud): PostgreSQL / Supabase Session Pooler (`pdo_pgsql`) with SSL (`sslmode=require`)
  - Full Eloquent ORM with strict typed models, relationships, and migrations.
- **Frontend Stack:**
  - Blade Templates with Tailwind CSS v3
  - Alpine.js for lightweight, reactive UI components
  - Vite asset bundling pipeline
- **Storage & Media:**
  - Cloudinary integration (`CloudinaryService`, upload widgets, signed URLs)
  - Native fallback local disk storage
- **Asynchronous Execution & Cron:**
  - Laravel Queues (`jobs` table database driver)
  - Scheduled Artisan commands (`ScheduleBlock`, auto-replenishment, live health monitoring)

---

## 3. Database Schema & Conventions
- **Naming Conventions:** Snake_case table and column names, singular PascalCase Eloquent models.
- **Key Tables:**
  - `users`: Core identity, roles (`user`, `content_manager`, `superadmin`), Google OAuth sub, avatar.
  - `channels`: Broadcast channels, slug, tvg_id, status, linear streamer flags, logo, banner.
  - `categories`: Broadcast categories, sort orders, visibility flags.
  - `programmes`: Schedule items, broadcast start/end timestamps, duration, video mappings.
  - `videos`: Media assets, YouTube/Direct embeds, duration, thumbnail URLs, Cloudinary IDs.
  - `content_rights`: Licensing register, rights holders, usage restrictions, validity dates.
  - `sports_competitions`, `sports_teams`, `sports_fixtures`: Sports broadcasting engine.
  - `gospel_film_productions`, `islamic_content_providers`, `nollywood_channels`, `comedy_channels`: Vertical directory tables.
- **Data Protection Rules:**
  - Never run destructive migrations (`migrate:fresh`) on production without explicit written authorization.
  - All foreign keys require explicit cascade or null-on-delete policies.

---

## 4. Authentication, Authorization & Roles
- **Dual Identifier Auth:** Users can authenticate via case-trimmed email or case-trimmed name/username.
- **Role Hierarchy:**
  - `user`: Public viewing, bookmarking favourites, personal guide.
  - `content_manager`: Content catalog editing, channel curation, metadata verification.
  - `superadmin`: Infrastructure control hub, rights approval, system audit logs, user management.
- **OAuth:** Google OAuth 2.0 Web Client integration with automatic account linking and fallback to manual credentials when OAuth credentials are absent.

---

## 5. Content Licensing & Platform Compliance
- **Core Principle:** Discovery does **not** grant broadcast authorization.
- **Hierarchy of Permitted Use:**
  1. *Public Discovery & Curation:* Metadata tracking only; queued for review.
  2. *Official Embeds:* Legitimate iframe embedding adhering to YouTube API terms of service.
  3. *Direct Licensed Distribution:* Bilateral content agreements logged in `content_rights`.
  4. *Original Productions:* Proprietary Zonita TV media.
- **Strict Prohibition:** Never bypass DRM, download unauthorized media streams, or circumvent platform access controls.

---

## 6. Implementation Batch Status & Roadmap
- **Batch 0 (Audit & Planning):** ✅ Completed. Full audit, architecture, risk register, and QA plan established.
- **Batch 1 (Foundation & Standards):** ✅ Completed. Linting rules, environment templates, standardized API responses, and health test harness deployed.
- **Batch 2 (Authentication & User Persistence):** ✅ Completed. Resilient dual login, profile preference isolation, Google OAuth, and Supabase connection verified across 18 tests.
- **Batch 3 (Profiles & Role Permissions):** ✅ Completed. Profile updates, secure password changes, preference editing, account deactivation, and role middleware authorization.
- **Batch 4 (UI/UX Design System & Layout):** ⏳ Next Up.
- **Batch 5 through 21:** Sequenced per master specification.
- **Batch 5 through 21:** Sequenced per master specification.

---

## 7. QA & Testing Rules
- Every batch must be audited by an independent QA testing workflow.
- Critical and high-priority defects must be resolved before batch sign-off.
- The 99 existing automated tests (`php artisan test`) must maintain 100% pass rate as a regression baseline.
