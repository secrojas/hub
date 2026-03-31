---
phase: 7
slug: dashboard-del-admin
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-31
---

# Phase 7 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest (PHP) / Vitest (Vue) |
| **Config file** | `phpunit.xml` / `vitest.config.js` |
| **Quick run command** | `php artisan test --filter DashboardTest` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~15 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter DashboardTest`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 15 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 7-01-01 | 01 | 1 | DASH-01 | unit | `php artisan test --filter DashboardControllerTest` | ❌ W0 | ⬜ pending |
| 7-01-02 | 01 | 1 | DASH-01 | unit | `php artisan test --filter DashboardControllerTest` | ❌ W0 | ⬜ pending |
| 7-02-01 | 02 | 2 | DASH-01 | e2e/manual | N/A — visual | N/A | ⬜ pending |
| 7-02-02 | 02 | 2 | DASH-01 | e2e/manual | N/A — visual | N/A | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/DashboardControllerTest.php` — stubs for DASH-01 (en progreso query, vencen pronto query, N+1 guards)
- [ ] `tests/Feature/DashboardControllerTest.php` — fixtures: Tasks in various states, clients, due dates

*Existing infrastructure covers test framework (Pest already installed).*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Sidebar fixed layout renders correctly | DASH-01 | Visual layout — not automatable | Load `/admin/dashboard` and verify sidebar is fixed on scroll |
| "Vencen pronto" highlighting is visually distinct | DASH-01 | CSS visual check | Verify highlighted row/card has distinct color vs. normal tasks |
| Inline status change causes task to disappear from list | DASH-01 | Reactive UI behavior | Change a task from "En progreso" to another status; verify it disappears |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 15s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
