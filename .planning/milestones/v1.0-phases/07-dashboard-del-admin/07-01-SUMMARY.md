---
phase: 07-dashboard-del-admin
plan: "01"
subsystem: dashboard
tags: [controller, tdd, layout, sidebar, inertia]
dependency_graph:
  requires: []
  provides: [DashboardController, AdminLayout-sidebar, dashboard-route]
  affects: [all-admin-pages, dashboard-ui]
tech_stack:
  added: []
  patterns: [two-query-aggregation, eager-loading, TDD-red-green, fixed-sidebar]
key_files:
  created:
    - app/Http/Controllers/DashboardController.php
    - tests/Feature/Dashboard/DashboardTest.php
  modified:
    - routes/web.php
    - resources/js/Layouts/AdminLayout.vue
decisions:
  - Two-query pattern for dashboard data: vencenProonto first, then enProgreso excludes those IDs via whereNotIn
  - vencenProonto window is today to today+7 (inclusive), excludes finalizado and null fecha_limite
  - Dashboard exact URL match ($page.url === '/dashboard') to avoid sidebar highlight bleeding to sub-routes
metrics:
  duration: 12
  completed_date: "2026-03-31"
  tasks_completed: 2
  files_modified: 4
---

# Phase 07 Plan 01: DashboardController + Sidebar Layout Summary

DashboardController with two-query data aggregation (enProgreso/vencenProonto) plus AdminLayout redesigned from horizontal nav to fixed 220px left sidebar.

## Objective

Backend data layer and layout shell required before Dashboard.vue UI (Plan 02) can be implemented. Both deliver independently verifiable outputs: 7 green tests for the controller, sidebar visible on all admin pages.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | DashboardController + feature tests + route wiring | 71d6927 | DashboardController.php, DashboardTest.php, routes/web.php |
| 2 | AdminLayout sidebar redesign | 85c4b47 | AdminLayout.vue |

## Verification Results

- `php artisan test --filter DashboardTest` — 7/7 pass
- `php artisan test` — 111/113 pass (2 pre-existing failures unrelated to this plan)

## Deviations from Plan

None — plan executed exactly as written.

## Deferred Issues (out of scope, pre-existing)

1. **BillingDashboardTest::cobrado_mes_excludes_other_months** — fails because `now()->subMonth()` billing is included in cobrado_mes. Pre-existing issue present before this plan. Root cause is likely a timezone or date-boundary issue in BillingController's date filter. Filed for investigation.

2. **QuoteTest::admin_can_view_quotes_index** — renders Error component instead of Admin/Quotes/Index. Pre-existing issue unrelated to this plan.

## Self-Check: PASSED

Files created/modified:
- FOUND: app/Http/Controllers/DashboardController.php
- FOUND: tests/Feature/Dashboard/DashboardTest.php
- FOUND: routes/web.php (modified — contains DashboardController::class)
- FOUND: resources/js/Layouts/AdminLayout.vue (modified — contains w-[220px], ml-[220px])

Commits:
- FOUND: 71d6927 — feat(07-01): DashboardController with two-query pattern and 7 feature tests
- FOUND: 85c4b47 — feat(07-01): redesign AdminLayout from horizontal nav to fixed left sidebar
