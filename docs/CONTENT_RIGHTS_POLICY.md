# Zonita TV — Official Content Rights, Licensing & Copyright Policy

**Document Version:** 2.0.0-governance  
**Effective Date:** 2026-11-10  
**Operating Entity:** BennieChat TechWealth Solutions  
**Compliance Lead:** General Counsel & Broadcast Rights Compliance Desk  

---

## 1. Statutory Mandate & Legal Axiom

Zonita TV operates under a strict, non-negotiable legal axiom:

> **Automated discovery, indexing, or web scraping is NOT broadcast permission.**  
> A publicly accessible video URL on YouTube, Vimeo, or any open web host does NOT grant Zonita TV the legal right to rebroadcast, restream, rehost, or monetize that content without explicit authorization.

Zonita TV strictly adheres to international copyright conventions (Berne Convention, WIPO Copyright Treaty), the Nigerian Copyright Act 2022, and relevant territorial distribution statutes.

---

## 2. Permitted Modes of Content Utilization

Every content asset catalogued in the Zonita TV database (`videos`, `programmes`, `channels`) must be tied to a verified record in `content_rights` and classified under one of the following permissible categories:

| Tier | Rights Classification | Operational Scope | Technical Delivery |
|:---:|---|---|---|
| **Tier 1** | **Direct Licensed Distribution** | Full bilateral distribution contract executed with copyright holder. Commercial monetization and ad insertion permitted. | HLS / DASH stream via secured CDN or Cloudinary. |
| **Tier 2** | **Authorized Embed Syndication** | Official embed code utilized via platform developer APIs (e.g. YouTube IFrame Player API) in strict compliance with host TOS. | Standard IFrame embed (`embed_player`), retaining broadcaster watermarks, links, and native ad attributes. |
| **Tier 3** | **Zonita Original Productions** | 100% proprietary content commissioned, produced, or owned directly by BennieChat TechWealth Solutions. | Direct stream (`zoniva_original`), unrestricted global distribution. |
| **Tier 4** | **Verified Public Domain / Creative Commons** | Archival, educational, or historical material with verified public domain status (CC0 or CC-BY with attribution). | Direct stream or embed with clear attribution ledger. |

---

## 3. Strict Prohibitions & Anti-Piracy Safeguards

The engineering, content curation, and operational teams are bound by the following prohibitions:

1. **NO DRM Circumvention:** Zonita TV systems shall never decrypt, bypass, or crack Widevine, FairPlay, PlayReady, or encrypted HLS streams.
2. **NO Content Re-Hosting without Written Rights:** Discovered third-party YouTube videos must never be downloaded and re-uploaded to Zonita TV servers or third-party storage buckets without a signed digital licensing agreement.
3. **NO Stream Scraping of Restricted Feeds:** Feeds requiring authentication tokens or regional geo-restrictions must not be scraped or proxy-tunneled.
4. **NO Misrepresentation:** Curated content must always credit the original creator, studio, or broadcaster. A discovered video must never be branded as a "Zonita Original".

---

## 4. Rights Lifecycle & Verification State Machine

Every discovered content item enters a formal verification state machine in `discovery_candidates` before it can ever be scheduled:

```
[DISCOVERED]
      │
      ▼
[UNDER REVIEW] ──────► [REJECTED] (Insufficient quality, unverified source)
      │
      ▼
[RIGHTS EVALUATION]
      ├──► [PERMISSION REQUESTED] ──► [LICENSED / APPROVED]
      ├──► [EMBED-ONLY AUTHORISED] ──► [DIRECTORY APPROVED]
      └──► [RIGHTS UNKNOWN] ─────────► [EXCLUDED FROM BROADCAST]
```

---

## 5. DMCA & Notice-and-Takedown Procedure

Zonita TV maintains an automated, rapid-response Notice-and-Takedown desk:

- **Contact Address:** `compliance@zonivatv.com` / `abuse@benniechat.com`
- **Takedown SLA:** Verified copyright infringement notifications are processed and delisted within **4 hours** of receipt.
- **Circuit Breaker Integration:** When an administrator marks a `content_right` or `video` as `revoked` or `disputed`, the backend scheduler immediately purges it from rolling EPG schedules, replaces active playout blocks with emergency station ident slates, and logs the action in the immutable audit ledger (`audit_logs`).
