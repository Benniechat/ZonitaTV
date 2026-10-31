# Zonita TV — Quality Assurance & Testing Strategy

## 1. QA Objectives & Guiding Principles
The QA strategy guarantees that Zonita TV operates as a resilient, legal, and continuous virtual broadcasting platform. Every milestone must be validated through rigorous automated and manual test gates.

## 2. Testing Levels & Tooling
- **Unit & Feature Testing:** PHPUnit 12.x / Pest runner integrated with Laravel testing helpers.
- **Static Code Analysis & Style:** Laravel Pint (`vendor/bin/pint --dirty --format agent`).
- **Regression Policy:** Zero regression tolerance. All existing 99 tests must pass on every release gate.
- **Database Isolation:** In-memory SQLite / transactional database rollback (`RefreshDatabase` trait) for fast test execution.
- **Simulated External Services:** `Http::fake()` for third-party APIs (Google OAuth, YouTube Data API, Cloudinary).

## 3. Severity Classification & Release Gates
- **Critical (P0):** Data loss, authentication bypass, unhandled fatal 500 error, unauthorized playback of unvetted content. *Blocks release immediately.*
- **High (P1):** Core navigation broken, scheduled playback freeze without fallback, failed user registration. *Must be resolved before batch sign-off.*
- **Medium (P2):** Visual misalignment, secondary filter failure with workaround. *Can proceed with documented mitigation.*
- **Low (P3):** Minor typography or non-blocking cosmetic detail.

## 4. Milestone Verification Checklist
Before any batch can be declared complete:
1. `vendor/bin/pint --dirty --format agent` passes without unaddressed issues.
2. `php artisan test` reports 100% pass rate.
3. Relevant new tests covering the batch's features are added.
4. Independent QA findings are documented in the batch milestone report.
5. User provides explicit confirmation to proceed.
