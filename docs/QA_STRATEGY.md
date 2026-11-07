# Zonita TV — Independent Quality Assurance (QA) Strategy

**Document Version:** 1.0.0-master  
**Author:** Software Quality Assurance Coordinator  
**Target:** Continuous Milestone Verification & Automated Regression Enforcement

---

## 1. Quality Assurance Mission & Independence

The Zonita TV QA workflow enforces rigorous, independent verification of all software components before any milestone is declared complete. 

### Mandatory Independence Rule:
The implementation agent and QA agent are strictly segregated. An autonomous dedicated subagent (`zonita_qa_agent`) conducts independent verification, audits source code, inspects database migrations, runs automated test harnesses, and produces structured assessment reports.

---

## 2. QA Assessment Defect Severity Scale

Every issue identified during QA is classified into one of four objective tiers:

| Severity | Definition | Milestone Impact |
|---|---|:---:|
| **Critical** | Security vulnerability, unhandled data loss, database corruption, or fatal application crash. | **Blocks Milestone Sign-off** |
| **High** | Core functionality is broken or inaccessible; major user journey failure; authentication breakdown. | **Blocks Milestone Sign-off** |
| **Medium** | Noticeable defect with an available workaround; minor performance degradation; non-blocking API defect. | Requires resolution plan |
| **Low** | Cosmetic imperfection, styling anomaly, non-blocking typo, minor documentation discrepancy. | Logged in backlog |

### Mandatory QA Approval Criteria:
A milestone can only achieve `QA PASSED` and `APPROVED` status if:
1. Zero **Critical** defects exist.
2. Zero **High** defects remain unresolved.
3. 100% of the regression test suite passes (`php artisan test`).
4. All new features are covered by dedicated automated feature tests.
5. All documentation (`docs/`, `skill.md`) accurately reflects the actual implementation.

---

## 3. Test Suite Inventory & Test Execution Matrix

The platform maintains 35 automated test specifications comprising **224 passing tests and 1,423 assertions**:

### Core Milestone Feature Suites (Batches 1–21)
1. `tests/Feature/FoundationStandardsTest.php` (Batch 1)
2. `tests/Feature/AuthenticationTest.php` (Batch 2)
3. `tests/Feature/UserProfileAndRolesTest.php` (Batch 3)
4. `tests/Feature/NavigationAndDesignSystemTest.php` (Batch 4)
5. `tests/Feature/PublicHomepageAndViewerExperienceTest.php` (Batch 5)
6. `tests/Feature/ContentCatalogueManagementTest.php` (Batch 6)
7. `tests/Feature/ContentDiscoveryAndWorkbenchTest.php` (Batch 7)
8. `tests/Feature/ContentRightsAndComplianceTest.php` (Batch 8)
9. `tests/Feature/ContentAcquisitionAndPartnerManagementTest.php` (Batch 9)
10. `tests/Feature/VirtualChannelsAndChannelManagementTest.php` (Batch 10)
11. `tests/Feature/SchedulerAndEpgManagementTest.php` (Batch 11)
12. `tests/Feature/PlaybackEngineAndViewerPlayerTest.php` (Batch 12)
13. `tests/Feature/ContinuousChannelPlayoutAutomationTest.php` (Batch 13)
14. `tests/Feature/LiveEventsAndSportsProgrammingTest.php` (Batch 14)
15. `tests/Feature/VerticalPortalsAndShowcasesTest.php` (Batch 15)
16. `tests/Feature/RecommendationsAndPersonalisationTest.php` (Batch 16)
17. `tests/Feature/AdminControlHubAnalyticsAndReportingTest.php` (Batch 17)
18. `tests/Feature/NotificationsAlertsAndSupportWorkflowsTest.php` (Batch 18)
19. `tests/Feature/SecurityPerformanceAndAccessibilityTest.php` (Batch 19)
20. `tests/Feature/EndToEndSystemTestingAndQATest.php` (Batch 20)
21. `tests/Feature/ProductionDeploymentAndHandoverTest.php` (Batch 21)

### Domain & Integration Feature Suites
22. `tests/Feature/CloudinaryIntegrationTest.php` (Media delivery & signatures)
23. `tests/Feature/ZonitaProgrammingEngineTest.php` (Playout rotation & schedule rules)
24. `tests/Feature/ZonivaTvTest.php` (Directory, search, IPTV ingest, EPG sync)
25. `tests/Feature/SportsBroadcastingEngineTest.php` (Autonomous sports MCR)
26. `tests/Feature/ComedyDirectoryTest.php` (Comedy curation & creator channels)
27. `tests/Feature/NollywoodDirectoryTest.php` (Nollywood cinema showcase)
28. `tests/Feature/GospelFilmDirectoryTest.php` (Christian faith directory)
29. `tests/Feature/GospelDramaAndKidsPlayoutTest.php` (Faith playout schedules)
30. `tests/Feature/IslamicBroadcastingEngineTest.php` (Islamic programming & EPG)
31. `tests/Feature/MovieDiscoveryAndRightsTest.php` (Feature films & licensing)
32. `tests/Feature/LegalComplianceTest.php` (NDPA, DMCA, disclaimer, takedown)
33. `tests/Feature/GoogleSiteVerificationTest.php` (SEO & webmaster tags)
34. `tests/Feature/ChannelThumbnailRepairTest.php` (Thumbnail integrity)
35. `tests/Feature/ExampleTest.php` (Baseline application sanity)

---

## 4. Independent QA Execution Workflow

For every milestone:
1. **Implementation Delivery:** The engineer completes code changes and runs preliminary tests.
2. **Subagent Invocation:** `zonita_qa_agent` is launched with explicit instructions detailing the milestone scope.
3. **Automated Audit:** The QA subagent executes `php artisan test` and verifies assertion counts.
4. **Code & Security Review:** The subagent inspects routes, role authorization, and database integrity.
5. **QA Assessment Report:** The subagent issues a formal report with defect classifications and a definitive verdict (`QA PASSED` / `QA FAILED`).
6. **User Sign-Off:** The milestone report is presented to the user for formal approval before progressing.
