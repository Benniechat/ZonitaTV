# Zonita TV — System Architecture Specification

## 1. High-Level Architecture Overview
Zonita TV follows a modern, service-oriented monolithic architecture powered by Laravel 11.x on PHP 8.3. It separates responsibilities into four core tiers:

```
[ Web Clients / Viewers ] <--> [ Cloudflare CDN & SSL ]
                                       |
                                       v
                           [ Nginx Reverse Proxy ]
                                       |
                     +-----------------+-----------------+
                     |                                   |
           [ Web Application ]                 [ Background Workers ]
         (Blade + Alpine + Vite)               (Scheduler + Queues)
                     |                                   |
                     +-----------------+-----------------+
                                       |
                   +-------------------+-------------------+
                   |                   |                   |
                   v                   v                   v
          [ PostgreSQL DB ]     [ Cloudinary CDN ]  [ YouTube Embeds / APIs ]
         (Supabase Managed)      (Banners & Logos)     (Streaming Sources)
```

---

## 2. Component Design & Responsibilities

### 2.1 Web Presentation Tier (Blade + Alpine.js + Tailwind CSS)
- **Design Philosophy:** Fast, server-rendered HTML for maximum SEO indexing and instant first paint, enriched with Alpine.js for lightweight interactive components (modals, dropdowns, schedule grids).
- **Responsive Layout:** Mobile drawer and desktop top-tier header, eliminating viewport clipping.

### 2.2 Application & Business Logic Tier (Laravel Controllers & Services)
- **Domain Controllers:**
  - `HomeController`, `GuideController`, `ChannelController`: Public viewer discovery.
  - `SportsController`, `MovieController`, `NollywoodController`, `ComedyController`, `GospelFilmController`, `IslamicChannelController`: Vertical discovery hubs.
  - `AuthController`, `GoogleAuthController`: Identity and session lifecycle.
  - `Admin\*Controller`: Platform governance, playout monitoring, and rights management.
- **Service Layer:**
  - `CloudinaryService`: Asset transformations, direct client upload signatures, fallback handling.
  - `LinearScheduleService`: Schedule block calculation, conflict prevention, rolling window generation.

### 2.3 Data Storage & Persistence Tier
- **Relational Database:** PostgreSQL (Production on Supabase) / SQLite (Local CI/CD).
- **Session & Cache Driver:** Cookie/database sessions, file/database caching.

### 2.4 Asynchronous Worker & Playout Tier
- **Scheduled Commands:**
  - `schedule:run`: Evaluates linear broadcast channels every minute.
  - `broadcasting:check-streams`: Pings channel stream health and flags dead feeds.
  - `broadcasting:replenish-schedules`: Extends 7-day rolling schedule blocks for 24/7 channels.
