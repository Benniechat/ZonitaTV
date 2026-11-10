# Zonita TV 2.0 — Permanent Operating Skill & Engineering Guide

## 1. Project Identity & Purpose
- **Project Name:** Zonita TV
- **Tagline:** Discover More. Watch Smarter.
- **Founder:** Wada Oluwapelumi Benedict
- **Operating Entity:** BennieChat TechWealth Solutions
- **Primary Market:** Nigeria (NGA)
- **Expansion Market:** Africa and the Global African Entertainment Audience
- **Primary Objective:** Build, operate, and maintain an automated, intelligent virtual television network platform delivering continuous linear broadcasting, metadata-driven content discovery, rights tracking, original productions, and scheduled entertainment across web, mobile, and connected devices.

---

## 2. Architecture & Technology Stack
- **Framework & Runtime:** PHP 8.3 / Laravel 12 (latest 12.x core)
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

## 3. Priority Launch Channels (8 Flagship Stations)
1. **Zonita Comedy:** Stand-up arena specials, viral comedy skits, sitcoms, parodies, clean family humor.
2. **Zonita Nollywood:** Blockbuster African cinema, indie short films, Yoruba/Igbo/Hausa cinema, premieres.
3. **Zonita Gospel:** Gospel movies, Christian dramas, inspirational talks, choral worship, family entertainment.
4. **Zonita Music:** 24/7 Afrobeats, live acoustic sessions, chart countdowns, artist interviews.
5. **Zonita Family:** Wholesome family entertainment, lifestyle, relationships, parenting, clean cinema.
6. **Zonita Documentaries:** African history, wildlife expeditions, technology breakthroughs, biographies.
7. **Zonita Kids:** Safe moral cartoons, traditional folktales, early education, Superbook Bible stories (strict child-safety controls).
8. **Zonita Live:** Authorized sports tactical discussions, live concerts, press events, licensed cultural broadcasts.

---

## 4. Content Licensing & Platform Compliance
- **Core Principle:** Discovery does **not** grant broadcast authorization.
- **Hierarchy of Permitted Use:**
  1. *Public Discovery & Curation:* Metadata tracking only; queued for review in `discovery_candidates`.
  2. *Official Embeds:* Legitimate iframe embedding adhering to YouTube API terms of service.
  3. *Direct Licensed Distribution:* Bilateral content agreements logged in `content_rights`.
  4. *Original Productions:* Proprietary Zonita TV media.
- **Strict Prohibition:** Never bypass DRM, download unauthorized media streams, or circumvent platform access controls.

---

## 5. Execution Workflow & User Approval Gates
- **Sequence:** AUDIT → RECONCILE → ARCHITECT → PRIORITISE → IMPLEMENT → TEST → INDEPENDENT QA → DOCUMENT → COMMIT → REPORT → REQUEST APPROVAL.
- **Batch Isolation:** Complete one batch at a time. Never advance to the next batch without explicit user sign-off.
- **Non-Destructive Guarantee:** Never delete working features, reset production databases, or drop test coverage.
