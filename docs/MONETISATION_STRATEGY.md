# Zonita TV — Monetisation & Commercial Strategy Blueprint

**Document Version:** 2.0.0-commercial  
**Operating Entity:** BennieChat TechWealth Solutions  
**Lead Strategist:** Chief Commercial Officer & Monetisation Lead  

---

## 1. Commercial Philosophy: Sustainable, Respectful Monetisation

Zonita TV rejects aggressive, viewer-hostile advertising. The user experience is our paramount asset. Monetisation is structured as a balanced, multi-tier ecosystem designed to support high-quality African entertainment while providing advertisers and premium subscribers with measurable value.

---

## 2. The Three Monetisation Pillars

```
+-------------------------------------------------------------------------------------------------+
|                                 ZONITA TV COMMERCIAL TIERS                                     |
+------------------------------------+----------------------------------+-------------------------+
| 1. ZONITA FREE (FAST)              | 2. ZONITA PLUS (SVOD/HYBRID)     | 3. ZONITA PARTNERS (B2B)|
+------------------------------------+----------------------------------+-------------------------+
| - 100% Free access to all launch   | - Monthly/Annual subscription    | - Direct Brand Deals    |
|   virtual television channels      | - Ad-free linear watching        | - Video Pre-roll/Mid-roll|
| - Guest viewing supported          | - Exclusive Zonita Originals     | - Sponsored Channels    |
| - Non-intrusive standard ad breaks | - 1080p/4K bitrate priority      | - Event Sponsorship     |
| - Basic watchlist and history      | - Offline download / PWA cache   | - Sponsored EPG Banners |
+------------------------------------+----------------------------------+-------------------------+
```

---

## 3. Advertising Architecture & Placement Policy

Zonita TV incorporates an in-house advertising management engine (`advertising_campaigns`, `advertisers`, `ads`):

### 1. Ad Placements:
- **Linear Break Replacement:** Short 15s to 30s ad pods placed between scheduled programme transitions.
- **Display & Sponsored Tiles:** Non-disruptive sponsor banners featured on the Public Homepage, Channel Directory, and EPG Guide.
- **Channel Branding & Watermarks:** "Brought to you by [Brand]" station ident overlays for corporate sponsors.

### 2. Frequency Capping & Child Protection:
- Maximum ad load: **no more than 3 minutes of advertisements per viewing hour**.
- Frequency capping: Maximum 2 exposures per creative per viewer per session.
- **Strict Child-Safety Exemption:** No commercial ads, alcohol promotions, betting/gambling, or predatory marketing permitted on **Zonita Kids**.

---

## 4. Subscription Management Architecture (Zonita Plus)

Zonita Plus is architected for phased activation following platform stability:
- **Billing Providers:** Paystack (Nigeria/Ghana), Flutterwave (Pan-Africa/Kenya/South Africa), Stripe (Global African Diaspora).
- **Entitlement Checks:** Middleware `EnsureActiveSubscription` enforces access to premium movies, high-definition streams, and ad-free feeds.
- **Grace Periods & Retry Handling:** 3-day billing retry cycle with automated email alerts before revoking premium entitlements.

---

## 5. Creator Revenue Sharing & Settlement

- **AVOD Split:** Verified transparent allocation of 50% to 70% of gross ad impressions generated during partner content windows.
- **Monthly Settlement Cycle:** Payments reconciled on the 1st of each month for the preceding calendar month.
- **Minimum Payout Threshold:** NGN 10,000 / USD $25 equivalent.
