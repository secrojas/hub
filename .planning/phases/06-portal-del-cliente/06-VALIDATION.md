---
phase: 6
slug: portal-del-cliente
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-30
---

# Phase 6 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (via Laravel) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter PortalTest` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~10 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter PortalTest`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** ~10 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 6-01-01 | 01 | 0 | PORT-01..04, Security | Feature | `php artisan test --filter PortalTest` | ❌ W0 | ⬜ pending |
| 6-02-01 | 02 | 1 | PORT-01 | Feature | `php artisan test --filter PortalTest::test_client_sees_own_tasks` | ❌ W0 | ⬜ pending |
| 6-02-02 | 02 | 1 | PORT-01 | Feature | `php artisan test --filter PortalTest::test_client_cannot_see_other_tasks` | ❌ W0 | ⬜ pending |
| 6-02-03 | 02 | 1 | PORT-02 | Feature | `php artisan test --filter PortalTest::test_client_sees_own_quotes` | ❌ W0 | ⬜ pending |
| 6-02-04 | 02 | 1 | PORT-02 | Feature | `php artisan test --filter PortalTest::test_client_can_download_own_pdf` | ❌ W0 | ⬜ pending |
| 6-02-05 | 02 | 1 | PORT-02 | Feature | `php artisan test --filter PortalTest::test_client_cannot_download_other_pdf` | ❌ W0 | ⬜ pending |
| 6-02-06 | 02 | 1 | PORT-03 | Feature | `php artisan test --filter PortalTest::test_client_sees_own_billings` | ❌ W0 | ⬜ pending |
| 6-02-07 | 02 | 1 | PORT-03 | Feature | `php artisan test --filter PortalTest::test_client_cannot_see_other_billings` | ❌ W0 | ⬜ pending |
| 6-02-08 | 02 | 1 | PORT-04 | Feature | `php artisan test --filter PortalTest::test_dashboard_has_task_counts` | ❌ W0 | ⬜ pending |
| 6-02-09 | 02 | 1 | PORT-04 | Feature | `php artisan test --filter PortalTest::test_dashboard_has_quote_counts` | ❌ W0 | ⬜ pending |
| 6-02-10 | 02 | 1 | PORT-04 | Feature | `php artisan test --filter PortalTest::test_dashboard_has_billing_totals` | ❌ W0 | ⬜ pending |
| 6-02-11 | 02 | 1 | Security | Feature | `php artisan test --filter PortalTest::test_admin_cannot_access_portal` | ❌ W0 | ⬜ pending |
| 6-02-12 | 02 | 1 | Security | Feature | `php artisan test --filter PortalTest::test_guest_cannot_access_portal` | ❌ W0 | ⬜ pending |
| 6-02-13 | 02 | 1 | Security | Feature | `php artisan test --filter PortalTest::test_client_notas_not_in_props` | ❌ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/Portal/` — directory (create)
- [ ] `tests/Feature/Portal/PortalTest.php` — stubs for PORT-01, PORT-02, PORT-03, PORT-04, Security (13 tests)

*PHPUnit + RefreshDatabase already established — no framework gaps.*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Portal visual layout — dashboard cards, list sections, empty states | PORT-04 | Visual rendering cannot be asserted by PHPUnit | Log in as a client user; navigate to `/portal`; verify three summary cards appear at top; verify task, quote, billing sections render below |
| PDF download in browser | PORT-02 | File download behavior not fully assertable in feature tests | Log in as client; click PDF download link on a quote; verify browser downloads the file without error |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 10s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
