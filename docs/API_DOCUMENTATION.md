# Zonita TV — REST API Integration & Endpoint Specification

**Document Version:** 1.0.0-master  
**Base URL:** `https://zonitatv.com` (Production) / `http://localhost:8000` (Local)  
**Standard Response Formats:** JSON / XML (EPG XMLTV)  
**Authentication Scheme:** Session Cookies (Web), Bearer Token / API Key (Integrations)

---

## 1. Rate Limiting Policy

| Route Group | Rate Limit | Header / Algorithm |
|---|---|---|
| `/search` | 30 requests / minute | IP-based rate limiting (`throttle:search`) |
| `/api/playback/*` | 120 requests / minute | Client session / IP telemetry limiter (`throttle:telemetry`) |
| `/api/recommendations/*` | 60 requests / minute | User / IP API limiter (`throttle:api`) |
| `/report-content`, `/channel/*/report` | 5 requests / 10 minutes | Strict abuse prevention limiter (`throttle:reports`) |

---

## 2. Playback & Telemetry Endpoints (`/api/playback`)

### 2.1 Live Playback Status Telemetry
- **Endpoint:** `GET /api/playback/status/{slug}`
- **Purpose:** Used by client video player to query live program status, current offset, and fallback status.
- **Response `200 OK`:**
```json
{
  "channel": {
    "id": 1,
    "name": "Zonita Nollywood Prime",
    "slug": "zonita-nollywood-prime",
    "channel_number": "101",
    "playback_mode": "zoniva_original",
    "brand_color": "#E50914"
  },
  "is_live": true,
  "current_programme": {
    "title": "Lagos Nights Feature Film",
    "video_id": "lagos_feat_77",
    "duration_seconds": 5400,
    "start_time": "2026-11-07T18:00:00Z",
    "end_time": "2026-11-07T19:30:00Z",
    "progress_seconds": 1200
  },
  "has_fallback": true
}
```

### 2.2 Report Player Failure (Circuit Breaker)
- **Endpoint:** `POST /api/playback/report-failure`
- **Headers:** `Content-Type: application/json`
- **Request Payload:**
```json
{
  "channel_id": 1,
  "video_id": "lagos_feat_77",
  "error_code": "150"
}
```
- **Response `200 OK` (Fallback Activated):**
```json
{
  "status": "recovered",
  "replacement": {
    "video_id": "emergency_slate_01",
    "title": "[Recovered] Emergency Backup Slate Reel",
    "duration_seconds": 1800,
    "start_seconds": 0
  },
  "channel_slug": "zonita-nollywood-prime"
}
```

### 2.3 Channel JSON EPG
- **Endpoint:** `GET /api/playback/epg/{slug}`
- **Response `200 OK`:** Array of scheduled programs for the current 24-hour cycle.

---

## 3. AI Recommendations API (`/api/recommendations`)

### 3.1 Personalized Channel Recommendations
- **Endpoint:** `GET /api/recommendations`
- **Query Parameters:** `limit` (int, default: 12)
- **Response `200 OK`:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Zonita Nollywood Prime",
      "slug": "zonita-nollywood-prime",
      "channel_number": "101",
      "score": 87.5,
      "recommendation_tag": "Saved Favourite",
      "recommendation_reason": "Because it is in your saved channels",
      "on_air_title": "Lagos Nights Feature Film"
    }
  ]
}
```

### 3.2 Similar Channels
- **Endpoint:** `GET /api/recommendations/similar/{slug}`
- **Purpose:** Returns channels with matching categories, languages, and viewer affinities.

---

## 4. Electronic Programme Guide XMLTV Feed (`/api/epg/xmltv`)

- **Endpoint:** `GET /api/epg/xmltv`
- **Query Parameters:** `channel` (optional slug to filter by specific channel)
- **Headers Returned:**
  - `Content-Type: text/xml; charset=UTF-8`
  - `Cache-Control: max-age=300, public` (Cached for 5 minutes)
- **Response Format:** RFC XMLTV format compatible with standard IPTV players, Kodi, VLC, and set-top boxes:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<tv generator-info-name="Zonita TV XMLTV Engine">
  <channel id="zonita-nollywood-prime">
    <display-name>Zonita Nollywood Prime</display-name>
    <icon src="https://zonitatv.com/images/logo/nollywood.png"/>
  </channel>
  <programme start="20261107180000 +0000" stop="20261107193000 +0000" channel="zonita-nollywood-prime">
    <title lang="en">Lagos Nights Feature Film</title>
    <desc lang="en">Full-length Nollywood cinema broadcast.</desc>
  </programme>
</tv>
```

---

## 5. System Health & Monitoring API (`/health`, `/api/health`, `/ping`)

### 5.1 Deep System Health Report
- **Endpoint:** `GET /health` or `GET /api/health`
- **Response `200 OK` (Healthy) or `503 Service Unavailable` (Degraded/Unhealthy):**
```json
{
  "status": "healthy",
  "app_name": "Zonita TV",
  "app_version": "1.0.0-production",
  "environment": "production",
  "timestamp": "2026-11-07T18:30:00Z",
  "response_time_ms": 14.2,
  "php_version": "8.3.33",
  "laravel_version": "12.x",
  "memory_usage_mb": 18.5,
  "services": {
    "database": {
      "status": "healthy",
      "latency_ms": 3.8,
      "connection": "pgsql"
    },
    "cache": {
      "status": "healthy",
      "latency_ms": 1.2,
      "driver": "database"
    },
    "storage": {
      "status": "healthy",
      "latency_ms": 4.1,
      "disk": "local"
    },
    "playout": {
      "status": "healthy",
      "active_channels": 260,
      "currently_playing_entries": 260,
      "message": "Linear playout operations operational."
    }
  }
}
```

### 5.2 Lightweight L4/L7 Ping Heartbeat
- **Endpoint:** `GET /ping` or `GET /api/ping`
- **Response `200 OK`:**
```json
{
  "status": "ok",
  "service": "Zonita TV",
  "timestamp": "2026-11-07T18:30:00Z"
}
```
