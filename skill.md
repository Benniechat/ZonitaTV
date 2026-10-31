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
- **Batch 4 (UI/UX Design System & Layout):** ✅ Completed. Two-tier responsive header, 4-column consolidated Explore mega-menu, accessible mobile drawer, and zero horizontal viewport clipping.
- **Batch 5 (Public Homepage & Viewer Experience):** ✅ Completed. Hero carousel, live & trending channels, programmatic showcase rows (football/sports, gospel, islamic), responsive viewer experience, and search accessibility verified.
- **Batch 6 (Content Database, Metadata, & Catalogue Management):** ✅ Completed. Extended video & movie metadata (aspect ratios, resolutions, ratings, advisory tags, geocoding), relational series/seasons/episodes hierarchy, channel->programme->video relations, administrative catalogue management (index, create, show, edit, soft delete, restore), and search/filter verified across 7 feature tests.
- **Batch 7 (Content Discovery & Provider Research):** ✅ Completed. Ingestion pipeline with deduplication (content hash & source ID), discovery staging area (`DiscoveryCandidate`), Content Provider directory (`ContentProvider`), administrative Discovery Workbench (URL intake, review, promotion to active pool, documented rejection), and legal takedown exclusion verified across 8 feature tests.
- **Batch 8 (Content Rights, Licensing, & Compliance):** ✅ Completed. Enhanced Rights & Licensing Ledger (channels, videos, movies, series), multi-tier rights classifications, territorial rights enforcement (`isTerritoryPermitted`), automated license expiration monitor (`rights:monitor-expirations` & auto-delist engine), DMCA takedown ticket execution workflow, and administrative governance interface verified across 6 feature tests.
- **Batch 9 (Content Acquisition & Partner Management):** ✅ Completed. Content Partner CRM (`content_partners`), bilateral distribution agreements (`distribution_agreements`), deal pipeline tracking (`prospect` -> `contact_made` -> `negotiation` -> `agreement_signed` -> `content_received` -> `active`), financial terms modeling (revenue share, flat fee, hybrid, exclusivity windows), automated agreement activation with seamless rights mirroring to the `content_rights` ledger, delivered content catalog inventory, and admin management workbench verified across 6 feature tests.
- **Batch 10 (Virtual Channels & Channel Management):** ✅ Completed. 24/7 Virtual Linear Channel creation & management (`VirtualChannelManagerService`), station branding (LCN virtual dial number, brand accent color, watermark bug, ident/bumper clip, fallback emergency slate), playout priority rules (`ChannelSourceRule` rotation tiers `premier_heavy` / `balanced` / `deep_catalog`, cooldown, min reserve buffer, daypart scheduling modes), 360-degree channel profile & live playout monitor (`admin.channels.show`), quick On-Air/Off-Air toggle, and catalog management verified across 8 feature tests.
- **Batch 11 (Scheduler & Electronic Programme Guide - EPG):** ✅ Completed. 7-to-30 day rolling EPG generator (`generateRollingSchedule`), automated playout conflict resolver and gap filler (`resolveScheduleConflicts`), multi-timezone engine (`Africa/Lagos`, `UTC`, `America/New_York`, `Europe/London`, `Asia/Dubai`, etc.), XMLTV RFC-compliant export service (`XmltvExportService`), automated replenishment artisan command (`epg:generate-rolling`), and interactive 7-day viewer EPG grid (`/guide`) with dayparts verified across 6 feature tests.
- **Batch 12 (Playback Engine & Viewer Player):** ✅ Completed. Resilient multi-mode playback resolution (`RightsEligibilityService` supporting `zoniva_original`, `embed_player`, `direct_stream`, and external destination), automated playout circuit breaker and reserve fallback recovery (`PlaybackContinuityService`), channel emergency slate deployment (`fallback_video_id` / `station_ident_video_id`), real-time live telemetry API (`/api/playback/status/{slug}`), and interactive Alpine.js linear player (`resources/views/channels/show.blade.php`) with wall-clock drift lock, station branding overlays (dial number badge, brand accent color border, watermark logo), and branded standby state verified across 6 feature tests.
- **Batch 13 (Continuous Channel Playout Automation):** ✅ Completed. Real-time playout progression worker (`ContinuousPlayoutService`), automated expiration transitioning to `completed`, active time-window activation to `playing`, dead-stream auto-detection and continuity recovery, emergency playout gap-filling watchdog, schedule horizon monitoring & auto-replenishment, telemetry logging in `broadcast_history`, artisan command (`playout:advance` with alias `playout:tick`), playout health heartbeat telemetry, and console kernel scheduling (`everyMinute`) verified across 6 feature tests.
- **Batch 14 (Live Events & Sports Programming):** ✅ Completed. Automated matchday fixture discovery and ingestion (`FixtureDiscoveryService`), statutory broadcast rights verification and operating mode switching (Mode A official external link vs Mode B embed vs Mode C rebroadcast via `BroadcastVerificationService`), broadcast session state machine (`AutomatedBroadcastController` from `idle` to `pre_show`, `on_air`, `half_time`, and `completed`), Mode C rights authorization gates & emergency killswitch (`SportsAdminController::forceModeA`), matchday timetable sync to linear playout (`MatchSchedulingService`), and public sports viewing stage (`/sports`, `/sports/live`, `/sports/upcoming`, `/sports/schedule`, `/sports/match/{fixture}`) verified across 7 feature tests.
- **Batch 15 (Vertical Portals - Comedy, Nollywood, Faith):** ✅ Completed. Dedicated vertical entertainment and cultural hubs (`ComedyController`, `NollywoodController`, `GospelFilmController`, `IslamicChannelController`), genre taxonomy filtering (category, lead priority, sub-genres, language), verified content provider directories, 24/7 dedicated virtual station integration, live EPG schedule previews, and unified route aliases (`/comedy`, `/comedy-central`, `/nollywood`, `/nollywood-cinema`, `/gospel-cinema`, `/faith`, `/islamic`) verified across 6 feature tests.
- **Batch 16 (AI Recommendations & Personalisation):** ⏳ Next Up.
- **Batch 17 through 21:** Sequenced per master specification.

---

## 7. QA & Testing Rules
- Every batch must be audited by an independent QA testing workflow.
- Critical and high-priority defects must be resolved before batch sign-off.
- The 99 existing automated tests (`php artisan test`) must maintain 100% pass rate as a regression baseline.
