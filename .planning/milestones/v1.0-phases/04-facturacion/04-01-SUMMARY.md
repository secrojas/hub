---
phase: 04-facturacion
plan: 01
subsystem: database
tags: [laravel, eloquent, migration, enum, factory, billing]

# Dependency graph
requires:
  - phase: 02-crm-de-clientes
    provides: Client model with nullOnDelete pattern on FK relationships
  - phase: 03-tareas-y-kanban
    provides: TaskStatus enum and TaskFactory patterns to mirror
provides:
  - billings table with nullOnDelete FK, decimal(12,2) monto, enum estado
  - BillingStatus string-backed enum (Pendiente/Pagado/Vencido)
  - Billing Eloquent model with decimal:2 cast and BillingStatus enum cast
  - Client::billings() hasMany relationship
  - BillingFactory with pagado/vencido/pendiente state methods
  - 15 test stubs across 3 test files (Nyquist Wave 0)
affects: [04-02-billing-controller, 04-03-billing-ui]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - String-backed PHP enum for billing status (mirrors TaskStatus pattern)
    - nullOnDelete on billing.client_id FK — financial records survive client deletion
    - BillingFactory state methods (pagado/vencido/pendiente) for predictable test data
    - Test stubs with markTestIncomplete for Nyquist Wave 0 compliance

key-files:
  created:
    - database/migrations/2026_03_20_000005_create_billings_table.php
    - app/Enums/BillingStatus.php
    - app/Models/Billing.php
    - database/factories/BillingFactory.php
    - tests/Feature/Billing/BillingCrudTest.php
    - tests/Feature/Billing/BillingValidationTest.php
    - tests/Feature/Billing/BillingDashboardTest.php
  modified:
    - app/Models/Client.php

key-decisions:
  - "nullOnDelete on billings.client_id — financial records must survive client deletion (unlike tasks which use cascadeOnDelete)"
  - "BillingFactory defaults estado to pendiente (not random) — predictable initial state for controller tests"
  - "fecha_pago is null by default; pagado() state method sets it to today — explicit state rather than conditional logic"

patterns-established:
  - "BillingFactory state methods: ->pagado(), ->vencido(), ->pendiente() — call instead of passing raw estado string"
  - "Test stubs use markTestIncomplete with 'Pending BillingController' message — consistent with TaskStatus pattern from phase 03"

requirements-completed: [BILL-01, BILL-02]

# Metrics
duration: 4min
completed: 2026-03-25
---

# Phase 4 Plan 1: Billing Data Foundation Summary

**Billings table with nullOnDelete FK + BillingStatus enum + Billing model (decimal:2 cast) + BillingFactory state methods + 15 test stubs across 3 files**

## Performance

- **Duration:** 4 min
- **Started:** 2026-03-25T12:01:19Z
- **Completed:** 2026-03-25T12:03:53Z
- **Tasks:** 2
- **Files modified:** 8

## Accomplishments

- billings table migrated with nullOnDelete FK (financial records survive client deletion), decimal(12,2) monto, enum estado default pendiente
- Billing Eloquent model with decimal:2 cast, BillingStatus enum cast, date casts for fecha_emision/fecha_pago, client() BelongsTo
- Client::billings() hasMany relationship added; BillingFactory with pagado/vencido/pendiente state methods
- 15 Nyquist Wave 0 test stubs (5 CRUD + 4 Validation + 6 Dashboard) — 0 failures, full suite still 65 passed

## Task Commits

Each task was committed atomically:

1. **Task 1: Migration + BillingStatus enum + Billing model + Client::billings()** - `e4fe496` (feat)
2. **Task 2: BillingFactory + test stubs (Nyquist Wave 0)** - `4a3399a` (feat)

## Files Created/Modified

- `database/migrations/2026_03_20_000005_create_billings_table.php` - billings table schema with nullOnDelete FK
- `app/Enums/BillingStatus.php` - string-backed enum: Pendiente/Pagado/Vencido
- `app/Models/Billing.php` - Eloquent model with decimal:2, BillingStatus, date casts
- `app/Models/Client.php` - added billings() hasMany relationship and Billing import
- `database/factories/BillingFactory.php` - factory with pagado/vencido/pendiente state methods
- `tests/Feature/Billing/BillingCrudTest.php` - 5 CRUD test stubs
- `tests/Feature/Billing/BillingValidationTest.php` - 4 validation test stubs
- `tests/Feature/Billing/BillingDashboardTest.php` - 6 dashboard test stubs

## Decisions Made

- **nullOnDelete on billings.client_id**: Financial records must survive client deletion. Unlike tasks (which use cascadeOnDelete), billing history is a financial audit trail that cannot be destroyed when a client is removed.
- **BillingFactory defaults to pendiente**: Predictable initial state matches TaskFactory defaulting to backlog. Tests can use ->pagado() or ->vencido() state methods explicitly.
- **fecha_pago null by default**: The pagado() state method sets fecha_pago=today explicitly, keeping conditional logic out of the definition() method.

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Billing data foundation complete: migration applied, model fully cast, factory ready for seeding test data
- Plan 02 (BillingController) can now build CRUD routes against stable Billing model contracts
- Plan 03 (Billing UI) can reference BillingStatus enum cases for dropdown/badge components
- All 15 test stubs waiting for BillingController implementation in Plan 02

---
*Phase: 04-facturacion*
*Completed: 2026-03-25*

## Self-Check: PASSED

- All 8 files found on disk
- Commits e4fe496 and 4a3399a verified in git log
