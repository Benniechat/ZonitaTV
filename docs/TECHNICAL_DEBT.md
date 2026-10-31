# Zonita TV — Technical Debt & Risk Register

## 1. Identified Technical Debt Items

| ID | Category | Description | Severity | Mitigation Plan | Scheduled Batch |
|---|---|---|---|---|---|
| **TD-01** | Frontend / UX | Navigation bar clutters horizontally on laptops (1024px–1280px) | Medium | Implement 2-tier header with 3-column Explore mega-menu | Batch 4 |
| **TD-02** | Database | SQLite used for tests, PostgreSQL on production; subtle dialect differences | Medium | Strictly use ANSI SQL in migrations; avoid SQLite-specific raw strings | Batch 1 |
| **TD-03** | Compliance | Need formal rights approval workflow before new YouTube channels broadcast | High | Enforce `rights_status = 'approved'` check in schedule generator | Batch 8 |
| **TD-04** | Streaming | YouTube embeds can throw error 150/101 (playback blocked in iframe) | High | Implement automatic JS player fallback to next scheduled item | Batch 12 |
| **TD-05** | Auth / Secrets | Google OAuth credentials absent in local .env; must degrade gracefully | Low | Graceful fallback implemented; document keys in production runbook | Batch 2 |

---

## 2. Risk Matrix & Contingency Strategies

### Risk 1: Upstream YouTube Stream Discontinuation / Geo-blocking
- **Probability:** High
- **Impact:** Medium (Black screen for viewers)
- **Contingency:** Automated continuous stream health checker flags dead streams within 60 seconds; playout engine instantly injects fallback slate or pre-approved offline video loop.

### Risk 2: Copyright Takedown Requests
- **Probability:** Medium
- **Impact:** High (Legal liability)
- **Contingency:** Public "Report Content / Takedown" portal active in footer and admin desk; immediate 1-click channel suspension with audit trail.
