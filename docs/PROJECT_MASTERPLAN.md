# Zonita TV — Official Project Masterplan

**Document Version:** 2.0.0-master  
**Status:** Authoritative Baseline  
**Lead Architect:** Principal Software Architect & QA Coordinator  
**Operating Framework:** Laravel 12 (PHP 8.3), Tailwind CSS, Alpine.js, Vite, Supabase / PostgreSQL.

---

## 1. Project Mission & Core Principles

Zonita TV is an enterprise-grade digital broadcast and virtual linear television platform designed to discover, curate, license, schedule, and broadcast verified television channels and continuous video programming across Africa and global diaspora audiences.

### Core Architecture Axioms:
1. **Discovery is NOT Broadcast Permission:** Automated discovery indexes metadata into a staging intake pool; content cannot air on linear channels without explicit, verified rights clearance.
2. **Authoritative Backend Timing:** Programme timing, daypart rotations, and EPG schedules are deterministically computed on the server; client players follow server time.
3. **Continuous Resilient Playback:** The viewer player operates an autonomous state machine with circuit breakers that detect playback failures (e.g. YouTube error 150/101) and immediately switch to emergency station fallback reels.
4. **Zero Data Loss & Safe Refactoring:** All reorganization, module restructuring, and migrations must be non-destructive, preserving existing channels, users, and playout history.
5. **Independent QA Enforcement:** No milestone can be marked approved without independent QA assessment, regression test validation, and explicit user sign-off.

---

## 2. Approved 22-Milestone Implementation Scope (Batches 0–21)

| Batch | Milestone Name | Scope & Core Objectives |
|---|---|---|
| **0** | **Project Discovery, Audit & Planning** | Full codebase audit, architectural review, database schema mapping, test harness baseline, and establishing authoritative project governance documentation. |
| **1** | **Foundation & Standards** | PSR-12/Pint standards, environment configuration templates, unified API error/response contracts, health check infrastructure. |
| **2** | **Authentication & Persistence** | Dual identifier login (email/username), Google OAuth federation, Supabase session handling, secure cookie domains. |
| **3** | **Profiles, Roles & Permissions** | Role-based authorization middleware (`RequireRole`), user profile preferences, account security, deactivation workflows. |
| **4** | **UI/UX Design System** | Two-tier responsive top navigation, 4-column Explore mega-menu, accessible mobile drawer, zero viewport clipping. |
| **5** | **Public Homepage & Discovery** | Hero carousel, live & trending channels, programmatic showcase rows (football, gospel, islamic, comedy), search accessibility. |
| **6** | **Content Database & Catalogue** | Unified metadata model for movies, series, episodes, videos; relational hierarchy; administrative catalogue management. |
| **7** | **Content Discovery Engine** | External ingestion pipeline, deduplication hashing, discovery candidates queue (`DiscoveryCandidate`), review & approval workbench. |
| **8** | **Content Rights & Licensing** | Centralized rights ledger (`ContentRight`), territorial restrictions (`permitted_territories`), automated expiration monitor, DMCA takedown desk. |
| **9** | **Acquisition & Partner Management** | Broadcaster CRM (`ContentPartner`), bilateral distribution contracts (`DistributionAgreement`), financial deal modeling, rights mirroring. |
| **10** | **Virtual Channel Management** | 24/7 channel creator, station branding (LCN dial numbers, watermarks, idents, emergency slates), playout rotation rules (`ChannelSourceRule`). |
| **11** | **Scheduler & EPG Engine** | 7-to-30 day rolling EPG generator, automated gap filler & conflict resolver, multi-timezone engine, RFC-compliant XMLTV feed. |
| **12** | **Playback Engine & Viewer Player** | Multi-mode player (HLS, YouTube embed, Zonita Original), state machine, error 150 circuit breaker, verified fallback deployment. |
| **13** | **Playout Automation Worker** | Playout progression worker (`playout:advance`), channel schedule health monitoring (`MonitorScheduleHealth`), heartbeat telemetry. |
| **14** | **Live Events & Sports Programming** | Fixture discovery (`FixtureDiscoveryService`), statutory broadcast rights verification, session state machine, emergency mode killswitch. |
| **15** | **Vertical Portals** | Curated vertical landing pages (Nollywood, Comedy, Gospel Cinema, Islamic TV), genre tags, dedicated 24/7 linear stations. |
| **16** | **AI Recommendations & Personalisation** | Weighted recommendation formula ($R = 0.40I + 0.25V + 0.15F + 0.10L + 0.10N$), explainable badges, personalized `/my-guide`. |
| **17** | **Admin Control Hub & Analytics** | Executive control hub (`DashboardController`), audience analytics reporting, compliance audit log viewer, real-time MCR monitor. |
| **18** | **Alerts, Notifications & Support** | Outage notifications, stream failure alerts, viewer problem report desk (`SupportTicketAdminController`), WhatsApp community broadcasts. |
| **19** | **Security, Performance & A11y** | Security headers (strict CSP, HSTS, X-Frame-Options), granular rate limiting, XMLTV caching, WCAG 2.1 AA accessibility. |
| **20** | **End-to-End System Testing & QA** | Comprehensive multi-scenario lifecycle validation drills covering viewer journeys, failure drills, rights audits, and MCR operations. |
| **21** | **Production Deployment & Handover** | Cloud production release readiness, deep health probes (`/health`, `/api/health`), zero-memory streamed backup generator, operations runbook. |
