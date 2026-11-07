# Zonita TV — Project Directory & Module Structure

**Document Version:** 1.0.0-master  
**Status:** Authoritative Architectural Standard  
**Operating Framework:** Laravel 12 (PHP 8.3)

---

## 1. High-Level Architectural Organization

Zonita TV is structured into 16 logical modules with clean separation of concerns:

```
[ Zonita TV Platform ]
├── 1. Core Platform           (app/Http/Middleware, bootstrap/app.php, config/)
├── 2. Identity & Access       (app/Http/Controllers/Auth, app/Models/User.php, app/Http/Middleware/RequireRole.php)
├── 3. Viewer Discovery        (app/Http/Controllers/HomeController.php, ChannelController.php, SearchController.php)
├── 4. Content Catalogue       (app/Models/Video.php, Movie.php, Series.php, ContentCatalogueController.php)
├── 5. Content Discovery       (app/Models/DiscoveryCandidate.php, DiscoveryWorkbenchController.php)
├── 6. Rights & Licensing      (app/Models/ContentRight.php, RightsEligibilityService.php, RightsAuditController.php)
├── 7. Partner & Acquisition   (app/Models/ContentPartner.php, DistributionAgreement.php, PartnerAdminController.php)
├── 8. Virtual Channels        (app/Models/Channel.php, VirtualChannelManagerService.php, ChannelAdminController.php)
├── 9. Scheduling & EPG        (app/Services/Zonita/ProgrammeScheduleService.php, XmltvExportService.php, GuideController.php)
├── 10. Playback & Playout     (app/Services/Zonita/PlaybackContinuityService.php, PlaybackController.php)
├── 11. Live Sports Engine     (app/Services/Zonita/FixtureDiscoveryService.php, SportsController.php, SportsAdminController.php)
├── 12. Vertical Portals       (app/Http/Controllers/ComedyController.php, NollywoodController.php, GospelFilmController.php, IslamicChannelController.php)
├── 13. AI & Personalisation   (app/Services/RecommendationService.php, RecommendationController.php, UserPreference.php)
├── 14. Admin Operations       (app/Http/Controllers/Admin/DashboardController.php, AnalyticsAdminController.php)
├── 15. Alerts & Support       (app/Services/AlertNotificationService.php, SupportTicketAdminController.php, SystemAlertsAdminController.php)
└── 16. SRE & Handover         (app/Services/SystemHealthService.php, HealthCheckController.php, ProductionVerifyCommand.php)
```

---

## 2. Directory Mapping & File Responsibilities

### 2.1 Backend Application (`app/`)

#### Controllers (`app/Http/Controllers/`)
- **Viewer-Facing:**
  - `HomeController.php`: Homepage hero carousel, live channels showcase, vertical discovery rows.
  - `ChannelController.php`: Channel detail view, linear viewer player, favourite toggle, problem report intake.
  - `GuideController.php`: 7-day interactive EPG grid, personalized watch guide (`/my-guide`).
  - `SearchController.php`: Global predictive search across channels, genres, and programs.
  - `SportsController.php`: Sports viewing stage, live matchday fixtures, upcoming broadcast calendar.
  - `ComedyController.php`: Comedy vertical portal (stand-up, skits, web series).
  - `NollywoodController.php`: Nollywood cinema showcase (Yoruba, Igbo, Hausa, English).
  - `GospelFilmController.php`: Christian faith cinema and worship television portal.
  - `IslamicChannelController.php`: Islamic educational programming and Quranic broadcasting hub.
  - `LegalComplianceController.php`: Legal disclaimer, DMCA copyright policy, takedown intake (`/report-content`).
- **Administrative Hub (`app/Http/Controllers/Admin/`):**
  - `DashboardController.php`: Executive MCR control hub, live stream uptime %, on-air monitor.
  - `ChannelAdminController.php`: 24/7 channel creation, dial numbers, branding, station idents.
  - `ContentCatalogueController.php`: Unified catalogue CRUD (movies, series, episodes, standalone videos).
  - `DiscoveryWorkbenchController.php`: Automated intake candidate review, deduplication, promotion/rejection.
  - `RightsAdminController.php`: Bilateral licensing records, permitted territory matrices.
  - `RightsAuditController.php`: Statutory compliance audit, license expiration radar.
  - `PartnerAdminController.php`: Content partner CRM, distribution agreement deal tracking.
  - `BroadcastingAdminController.php`: 24/7 virtual broadcasting rules (`ChannelSourceRule`).
  - `SportsAdminController.php`: Live match fixture management, statutory rights gates, emergency killswitch.
  - `AnalyticsAdminController.php`: Viewership analytics, geographic viewer distribution, event exports.
  - `SupportTicketAdminController.php`: Viewer problem report desk, ticket lifecycle (`new` -> `resolved`).
  - `SystemAlertsAdminController.php`: Master system outage alerts and broadcast alerts.
  - `CloudinaryMediaController.php`: Direct CDN media uploads and cryptographic signature generation.
- **REST APIs (`app/Http/Controllers/Api/`):**
  - `PlaybackController.php`: Live playback status telemetry, EPG JSON endpoints, failure reporting.
  - `RecommendationController.php`: AI-driven channel recommendations, similar channels, personalized guide feed.
  - `HealthCheckController.php`: Deep system diagnostics (`/health`, `/api/health`) and ping probes (`/ping`).

#### Services Layer (`app/Services/`)
- `SystemHealthService.php`: Database latency, cache ping, storage disk, and MCR playout health diagnostic checks.
- `RightsEligibilityService.php`: Evaluates listing and watch permissions against territory and expiry rules.
- `RecommendationService.php`: Computes personalized channel scores using the 5-parameter formula.
- `CloudinaryService.php`: Enterprise media transformations, secure signed URLs, and fallback assets.
- `IptvPlaylistIngestService.php`: IPTV M3U parser, channel sync, and TVG identifier binding.
- `EpgSyncService.php`: Electronic Programme Guide sync and rolling schedule generation.
- **Playout Automation Engine (`app/Services/Zonita/`):**
  - `PlaybackContinuityService.php`: Playback failure recovery state machine and emergency slate deployment.
  - `ProgrammeScheduleService.php`: Rolling EPG schedule generator and playout conflict resolution.
  - `ChannelProgrammingEngine.php`: Master virtual playout orchestration and channel deployment.
  - `ChannelReserveService.php`: Emergency backup video pool and fallback selection.
  - `VideoAvailabilityService.php`: YouTube availability verification and circuit breaker recording.
  - `ContentRotationService.php`: Sequential, shuffle, and weighted content rotation logic.
  - `ChannelRelevanceService.php`: Channel tagging, genre classification, and relevance matching.
  - `FixtureDiscoveryService.php`: Autonomous matchday sports fixture ingestion.
  - `BroadcastVerificationService.php`: Statutory rights verification for live sporting events.

#### Asynchronous Queue Jobs (`app/Jobs/Zonita/`)
- `MonitorScheduleHealth.php`: Periodic inspection to guarantee zero empty playout queues.
- `RefreshChannelQueue.php`: Background replenishment of 48-hour rolling schedule buffers.
- `UpdateVideoEligibility.php`: Batched reverification of video stream health.
- `GenerateChannelReports.php`: Nightly computation of uptime %, viewer counts, and SLA metrics.

#### Artisan Console Commands (`app/Console/Commands/`)
- `AdvanceContinuousPlayoutCommand.php` (`playout:advance`): Advances linear channels, detects dead streams.
- `GenerateRollingScheduleCommand.php` (`epg:generate-rolling`): Generates rolling 7-to-30 day schedules.
- `MonitorRightsExpirationsCommand.php` (`rights:monitor-expirations`): Audits license expirations and auto-delists.
- `ExportDatabaseSqlDumpCommand.php` (`zoniva:export-sql`): Zero-memory stream-buffered ANSI SQL database backup.
- `ProductionVerifyCommand.php` (`production:verify`): Automated production deployment readiness audit.

---

## 3. Frontend Architecture (`resources/`)

- `resources/views/layouts/app.blade.php`: Master viewer layout with WCAG 2.1 AA "Skip to main content" link, two-tier navigation, 4-column mega-menu, and footer compliance disclosures.
- `resources/views/layouts/admin.blade.php`: Dedicated administrative layout with sidebar navigation, system health badges, and role-aware navigation links.
- `resources/views/channels/show.blade.php`: Resilient viewer player stage with station branding, channel number dial, live program details, and auto-fallback state machine.
- `resources/views/guide/index.blade.php`: Interactive 7-day EPG television guide with dayparts and timezone selector.
- `resources/views/guide/personalized.blade.php`: Viewer-facing `/my-guide` displaying personalized recommendations and bookmarked channels.
- `resources/views/admin/dashboard.blade.php`: Master Control Room executive dashboard with uptime gauges, playout monitor, and quick shortcuts.
