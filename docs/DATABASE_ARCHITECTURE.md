# Zonita TV — Database Architecture & Entity-Relationship Specification

**Document Version:** 1.0.0-master  
**RDBMS Targets:** PostgreSQL 15+ (Production on Supabase) / SQLite 3.35+ (Local & CI/CD)  
**Schema Count:** 32 Core Application Tables

---

## 1. High-Level Entity Relationship Topology

The Zonita TV database is structured around five central domain clusters:

```
    [ Identity & Access ]             [ Content Catalogue ]
         ┌────────┐                        ┌────────┐
         │ users  │                        │ videos │
         └───┬────┘                        └───┬────┘
             │                                 │
             ▼                                 ▼
   [ user_preferences ] <──────────────> [ programmes ]
             │                                 ▲
             ▼                                 │
       [ favourites ]                          │
             │                                 │
             ▼                                 │
       [ channels ] ───────────────────────────┘
         ▲        ▲
         │        └────────────────────┐
         ▼                             ▼
  [ content_rights ]        [ schedule_entries ]
         ▲                             ▲
         │                             │
 [ distribution_agreements ]    [ channel_source_rules ]
         ▲
         │
 [ content_partners ]
```

---

## 2. Comprehensive Table Inventory & Data Dictionary

### 2.1 Identity & Access Cluster

#### `users`
- **Purpose:** Primary authoritative authentication and identity table.
- **Key Columns:**
  - `id` (bigint, PK, autoincrement)
  - `name` (varchar 255)
  - `email` (varchar 255, unique, indexed)
  - `username` (varchar 100, unique, nullable, indexed)
  - `password` (varchar 255, bcrypt hashed)
  - `role` (varchar 50, enum: `user`, `content_manager`, `rights_reviewer`, `advertising_manager`, `super_admin`)
  - `google_id` (varchar 255, unique, nullable)
  - `avatar_url` (varchar 500, nullable)
  - `email_verified_at` (timestamp, nullable)
  - `created_at`, `updated_at`, `deleted_at` (soft deletes)

#### `user_preferences`
- **Purpose:** User personalization, preferred genres, languages, and UI settings.
- **Key Columns:**
  - `id` (bigint, PK)
  - `user_id` (bigint, FK -> `users.id`, unique, indexed)
  - `country_code` (varchar 3, default: 'NGA')
  - `preferred_categories` (json, array of category IDs)
  - `preferred_languages` (json, array of language codes)
  - `enable_recommendations` (boolean, default: true)
  - `created_at`, `updated_at`

#### `favourites`
- **Purpose:** Viewer channel bookmarks and quick dial shortcuts.
- **Key Columns:**
  - `id` (bigint, PK)
  - `user_id` (bigint, FK -> `users.id`, indexed)
  - `channel_id` (bigint, FK -> `channels.id`, indexed)
  - `created_at`, `updated_at`
  - *Unique Constraint:* `['user_id', 'channel_id']` prevents duplicate bookmarks.

#### `viewing_events`
- **Purpose:** Telemetry audit log for viewership interactions and recommendation weighting.
- **Key Columns:**
  - `id` (bigint, PK)
  - `user_id` (bigint, FK -> `users.id`, nullable, indexed)
  - `channel_id` (bigint, FK -> `channels.id`, indexed)
  - `duration_seconds` (integer, default: 0)
  - `completed` (boolean, default: false)
  - `ip_country` (varchar 3, default: 'NGA')
  - `created_at`

---

### 2.2 Content Catalogue & Taxonomy Cluster

#### `categories`
- **Purpose:** Hierarchical genres and vertical navigation taxonomy.
- **Key Columns:**
  - `id` (bigint, PK)
  - `name` (varchar 100)
  - `slug` (varchar 120, unique, indexed)
  - `icon_name` (varchar 50, nullable)
  - `description` (text, nullable)
  - `sort_order` (integer, default: 0)
  - `is_nav_visible` (boolean, default: true)

#### `videos`
- **Purpose:** Master repository of standalone video media assets and programme episodes.
- **Key Columns:**
  - `id` (bigint, PK)
  - `title` (varchar 255)
  - `slug` (varchar 255, unique, indexed)
  - `youtube_id` (varchar 32, unique, indexed)
  - `description` (text, nullable)
  - `duration_seconds` (integer)
  - `aspect_ratio` (varchar 10, default: '16:9')
  - `resolution` (varchar 10, default: '1080p')
  - `content_rating` (varchar 10, default: 'PG')
  - `is_active` (boolean, default: true)
  - `created_at`, `updated_at`

#### `movies` & `series` & `seasons` & `episodes`
- **Purpose:** Structured episodic and feature film metadata for Nollywood, African cinema, and serialized drama.
- **Relationships:**
  - `series` (1) &rarr; (N) `seasons` &rarr; (N) `episodes` &rarr; (1) `videos`
  - `movies` (1) &rarr; (1) `videos`

---

### 2.3 Virtual Channels & Playout Automation Cluster

#### `channels`
- **Purpose:** 24/7 linear virtual stations and syndicated television feeds.
- **Key Columns:**
  - `id` (bigint, PK)
  - `name` (varchar 150)
  - `slug` (varchar 160, unique, indexed)
  - `channel_number` (varchar 10, nullable, indexed)
  - `tagline` (varchar 255, nullable)
  - `description` (text, nullable)
  - `logo_path` (varchar 500, nullable)
  - `banner_path` (varchar 500, nullable)
  - `brand_color` (varchar 20, default: '#E50914')
  - `watermark_logo_path` (varchar 500, nullable)
  - `official_destination_url` (varchar 500)
  - `country_code` (varchar 3, default: 'NGA', indexed)
  - `language_code` (varchar 10, default: 'en', indexed)
  - `playback_mode` (varchar 30, enum: `direct_stream`, `embed_player`, `external_destination`, `zoniva_original`)
  - `is_virtual` (boolean, default: false, indexed)
  - `is_featured` (boolean, default: false, indexed)
  - `is_active` (boolean, default: true, indexed)
  - `is_published` (boolean, default: false, indexed)
  - `fallback_video_id` (varchar 32, nullable)
  - `created_at`, `updated_at`, `deleted_at` (soft deletes)

#### `channel_source_rules`
- **Purpose:** Configuration rules governing automated 24/7 channel rotation and scheduling.
- **Key Columns:**
  - `id` (bigint, PK)
  - `channel_id` (bigint, FK -> `channels.id`, unique, indexed)
  - `rotation_tier` (varchar 30, enum: `premier_heavy`, `balanced`, `deep_catalog`)
  - `scheduling_mode` (varchar 30, enum: `sequential`, `shuffle`, `weighted`)
  - `cooldown_hours` (integer, default: 4)
  - `min_reserve_buffer` (integer, default: 5)
  - `is_active` (boolean, default: true)

#### `schedule_entries`
- **Purpose:** Discrete programmed broadcast slots with deterministic start and end timestamps.
- **Key Columns:**
  - `id` (bigint, PK)
  - `channel_id` (bigint, FK -> `channels.id`, indexed)
  - `video_id` (bigint, FK -> `videos.id`, indexed)
  - `title` (varchar 255)
  - `start_time` (timestamp, indexed)
  - `end_time` (timestamp, indexed)
  - `duration_seconds` (integer)
  - `status` (varchar 20, enum: `scheduled`, `playing`, `completed`, `failed`)
  - `is_fallback` (boolean, default: false)

---

### 2.4 Content Rights & Regulatory Compliance Cluster

#### `content_rights`
- **Purpose:** Legal licensing ledger verifying broadcast permissions and geographic authorizations.
- **Key Columns:**
  - `id` (bigint, PK)
  - `channel_id` (bigint, FK -> `channels.id`, nullable, indexed)
  - `video_id` (bigint, FK -> `videos.id`, nullable, indexed)
  - `rights_holder_name` (varchar 200)
  - `classification` (varchar 50, enum: `public_domain`, `creative_commons`, `direct_license`, `platform_embed`, `statutory_original`)
  - `effective_from` (timestamp)
  - `expires_at` (timestamp, nullable, indexed)
  - `permitted_territories` (json, e.g. `["NGA", "GHA", "KEN"]` or `["*"]`)
  - `allow_directory_listing` (boolean, default: true)
  - `allow_web_embedding` (boolean, default: true)
  - `allow_direct_hls` (boolean, default: false)
  - `status` (varchar 30, enum: `pending_review`, `cleared`, `restricted`, `expired`, `rejected`)

#### `content_complaints`
- **Purpose:** Formal DMCA copyright notices and platform dispute intake records.
- **Key Columns:**
  - `id` (bigint, PK)
  - `reference_number` (varchar 40, unique, indexed)
  - `claimant_name` (varchar 150)
  - `claimant_email` (varchar 200)
  - `infringing_url` (varchar 500)
  - `status` (varchar 30, enum: `new`, `investigating`, `action_taken`, `rejected`)
  - `created_at`, `updated_at`

---

## 3. Row-Level Security (RLS) & Production Supabase Policies

For production deployment on Supabase PostgreSQL:

1. **`users` Table:**
   - `SELECT`: Authenticated users can view their own profile; Super Admins can view all.
   - `UPDATE`: Authenticated users can update their non-privileged fields (`name`, `avatar_url`). Role elevation requires `super_admin`.
2. **`channels` & `schedule_entries` Tables:**
   - `SELECT`: Public access where `is_active = true` AND `is_published = true`.
   - `INSERT / UPDATE / DELETE`: Restricted strictly to roles `super_admin`, `content_manager`.
3. **`content_rights` & `distribution_agreements` Tables:**
   - `SELECT / INSERT / UPDATE`: Restricted to `super_admin` and `rights_reviewer`.
4. **`favourites` & `user_preferences` Tables:**
   - Full read/write restricted to the owning `user_id` matching `auth.uid()`.
