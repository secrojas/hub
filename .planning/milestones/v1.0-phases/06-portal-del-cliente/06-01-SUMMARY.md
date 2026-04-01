---
phase: 06-portal-del-cliente
plan: 01
subsystem: api
tags: [laravel, inertia, portal, pdf, dompdf, feature-tests]

# Dependency graph
requires:
  - phase: 05-presupuestos-y-pdf
    provides: QuoteController pdf() pattern, PDF rendering via barryvdh/dompdf
  - phase: 04-facturacion
    provides: Billing model, BillingStatus enum, billing aggregate patterns
  - phase: 03-tareas-y-kanban
    provides: Task model, TaskStatus enum
  - phase: 02-crm-de-clientes
    provides: Client model with notas field, EnsureIsClient middleware
  - phase: 01-fundaci-n-y-auth
    provides: Auth scaffold, Role enum, User model with client_id field
provides:
  - PortalController with index() and pdf() methods
  - 13 feature tests covering PORT-01..04 and security
  - Portal routes under ['auth', 'client'] middleware
affects:
  - 06-portal-del-cliente plan 02 (Vue portal page)

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Client data isolation: all queries scoped by auth()->user()->client_id"
    - "abort_unless($clientId, 403) — null client_id guard before any portal query"
    - "PDF ownership: abort_if($quote->client_id !== auth()->user()->client_id, 403)"
    - "Dashboard aggregates: taskCounts/quoteCounts use Collection groupBy on enum->value; billingTotals use (float) DB::sum()"

key-files:
  created:
    - app/Http/Controllers/PortalController.php
    - tests/Feature/Portal/PortalTest.php
  modified:
    - routes/web.php
    - tests/Feature/Auth/MiddlewareTest.php

key-decisions:
  - "Portal pdf() does NOT block Borrador estado — per CONTEXT.md locked decision: sin restriccion por estado"
  - "Portal pdf() ownership check uses client_id comparison, not admin-style global access"
  - "abort_unless($clientId, 403) guard on index() — defensive against null client_id edge case (admin or uninvited user bypassing middleware)"
  - "MiddlewareTest updated: test_client_can_access_portal now creates Client record — required by new client_id guard"

patterns-established:
  - "Portal data isolation: never expose global queries — always WHERE client_id = auth()->user()->client_id"
  - "Enum value grouping: ->groupBy(fn ($t) => $t->estado->value) not ->groupBy('estado') — cast enum doesn't match string in Collection::groupBy"

requirements-completed: [PORT-01, PORT-02, PORT-03, PORT-04]

# Metrics
duration: 15min
completed: 2026-03-30
---

# Phase 06 Plan 01: Portal del Cliente Backend Summary

**PortalController with client-scoped data isolation, PDF ownership check, dashboard aggregates, and 13 passing feature tests covering PORT-01..04 plus security**

## Performance

- **Duration:** ~15 min
- **Started:** 2026-03-30T22:04:00Z
- **Completed:** 2026-03-30T22:07:57Z
- **Tasks:** 2
- **Files modified:** 4

## Accomplishments

- Created PortalController with index() returning tasks, quotes, billings, and dashboard aggregate props — all scoped to authenticated client's client_id
- Implemented pdf() with ownership enforcement: 403 if quote.client_id != user.client_id; no Borrador restriction per locked architectural decision
- Registered both portal routes under ['auth', 'client'] middleware group, replacing old inline closure
- Created 13 feature tests: data isolation for tasks/quotes/billings, PDF ownership check, dashboard aggregates (task counts, quote counts, billing totals), admin rejection (403), guest redirect, and notas not exposed in any prop

## Task Commits

Each task was committed atomically:

1. **Task 1: Create PortalController with index() and pdf() + register routes** - `a0a8f43` (feat)
2. **Task 2: Create 13 feature tests + fix MiddlewareTest** - `8f6719b` (feat)

**Plan metadata:** (docs commit — see below)

## Files Created/Modified

- `app/Http/Controllers/PortalController.php` — Portal controller: index() queries tasks/quotes/billings/dashboard for client; pdf() with ownership check
- `tests/Feature/Portal/PortalTest.php` — 13 feature tests for PORT-01..04 and security
- `routes/web.php` — Added PortalController import; replaced portal closure with controller routes; added portal.quotes.pdf route
- `tests/Feature/Auth/MiddlewareTest.php` — Fixed test_client_can_access_portal to create Client record with client_id

## Decisions Made

- Portal pdf() does NOT check Borrador status — per locked architectural decision from CONTEXT.md, all quote estados visible to client portal
- Ownership check uses direct client_id comparison: `abort_if($quote->client_id !== auth()->user()->client_id, 403)` — same pattern as admin but inverted guard
- `abort_unless($clientId, 403)` on index() as defensive guard — EnsureIsClient only checks role, not that client_id is set

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Fixed MiddlewareTest::test_client_can_access_portal after controller added client_id guard**
- **Found during:** Task 2 (full test suite verification)
- **Issue:** MiddlewareTest created a client User without a Client record (null client_id). The old portal closure had no guard. The new PortalController::index() calls `abort_unless($clientId, 403)`, causing the middleware test to fail with 403 instead of 200.
- **Fix:** Updated test to create a Client record first and pass `client_id` to the User factory
- **Files modified:** `tests/Feature/Auth/MiddlewareTest.php`
- **Verification:** MiddlewareTest passes with 5/5 assertions
- **Committed in:** `8f6719b` (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (Rule 1 — bug in existing test caused by our new controller guard)
**Impact on plan:** Necessary correctness fix. The guard itself is correct behavior per plan spec.

## Issues Encountered

**Pre-existing (out of scope):** `BillingDashboardTest::test_cobrado_mes_excludes_other_months` was already failing before Plan 06-01 execution. The test leaks `fecha_pago: now()` data from the first test in the class, causing the second test's `cobrado_mes` query to include unexpected records. Documented in `deferred-items.md` — NOT caused by Portal changes.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Backend contract complete: PortalController, routes, and 13 tests all green
- Plan 06-02 (Vue portal page) can consume the exact Inertia props defined here: tasks, quotes, billings, dashboard (with tareas/presupuestos/facturacion sub-keys)
- No blockers

---
*Phase: 06-portal-del-cliente*
*Completed: 2026-03-30*
