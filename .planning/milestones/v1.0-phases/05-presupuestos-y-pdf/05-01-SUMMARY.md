---
phase: 05-presupuestos-y-pdf
plan: 01
subsystem: database
tags: [eloquent, migrations, enum, factory, dompdf, quotes]

# Dependency graph
requires:
  - phase: 04-facturacion
    provides: BillingStatus enum and nullOnDelete FK pattern; BillingFactory state methods pattern

provides:
  - QuoteStatus string-backed enum (borrador/enviado/aceptado/rechazado)
  - Quote model with client() and items() relationships
  - QuoteItem model with quote() relationship and decimal:2 cast
  - quotes migration with nullOnDelete on client_id
  - quote_items migration with cascadeOnDelete on quote_id
  - QuoteFactory with borrador/enviado/aceptado/rechazado state methods
  - QuoteItemFactory with Quote::factory() default
  - barryvdh/laravel-dompdf v3.1.2 installed
  - QuoteTest stubs covering QUOT-01, QUOT-02, QUOT-03

affects:
  - 05-02 (QuoteController and Vue CRUD)
  - 05-03 (PDF generation and download)

# Tech tracking
tech-stack:
  added:
    - barryvdh/laravel-dompdf v3.1.2
  patterns:
    - String-backed enum mirroring BillingStatus pattern
    - nullOnDelete on financial record FK (survives client deletion)
    - cascadeOnDelete on quote_items.quote_id (items have no value without parent)
    - Factory state methods one-per-enum-case
    - markTestIncomplete stubs per requirement group

key-files:
  created:
    - app/Enums/QuoteStatus.php
    - app/Models/Quote.php
    - app/Models/QuoteItem.php
    - database/migrations/2026_03_25_000006_create_quotes_table.php
    - database/migrations/2026_03_25_000007_create_quote_items_table.php
    - database/factories/QuoteFactory.php
    - database/factories/QuoteItemFactory.php
    - tests/Feature/QuoteTest.php
  modified:
    - app/Models/Client.php (added quotes() hasMany)
    - composer.json (added barryvdh/laravel-dompdf)

key-decisions:
  - "nullOnDelete on quotes.client_id — quote as financial record survives client deletion (consistent with billings)"
  - "cascadeOnDelete on quote_items.quote_id — items have no standalone value without parent quote"
  - "QuoteFactory defaults to QuoteStatus::Borrador — predictable initial state for test setup"
  - "barryvdh/laravel-dompdf already installed at v3.1.2 — no version conflict"

patterns-established:
  - "Quote state transitions: borrador → enviado → aceptado|rechazado"
  - "Factory state methods match all enum cases one-to-one"

requirements-completed:
  - QUOT-01
  - QUOT-02
  - QUOT-03

# Metrics
duration: 5min
completed: 2026-03-30
---

# Phase 05 Plan 01: Quotes Data Layer Summary

**QuoteStatus enum, Quote/QuoteItem models with FK constraints, factories with state methods, dompdf installed, and 13 test stubs scaffolding QUOT-01/02/03**

## Performance

- **Duration:** 5 min
- **Started:** 2026-03-30T16:06:59Z
- **Completed:** 2026-03-30T16:11:45Z
- **Tasks:** 2
- **Files modified:** 10

## Accomplishments

- Installed barryvdh/laravel-dompdf v3.1.2 (was already present, composer confirmed)
- Created full data layer: QuoteStatus enum, Quote + QuoteItem models, 2 migrations, 2 factories
- Added Client::quotes() hasMany relationship; both tables migrated to DB
- Created 13 markTestIncomplete test stubs covering all QUOT requirement groups

## Task Commits

Each task was committed atomically:

1. **Task 1: Install dompdf, create migrations, enum, models, factories** - `635bf0b` (feat)
2. **Task 2: Create QuoteTest stub file** - `74c6a5e` (test)

**Plan metadata:** (docs commit follows)

## Files Created/Modified

- `app/Enums/QuoteStatus.php` - String-backed enum with borrador/enviado/aceptado/rechazado cases
- `app/Models/Quote.php` - Eloquent model with client() + items() relationships, QuoteStatus cast
- `app/Models/QuoteItem.php` - Eloquent model with quote() relationship, decimal:2 precio cast
- `database/migrations/2026_03_25_000006_create_quotes_table.php` - quotes schema with nullOnDelete
- `database/migrations/2026_03_25_000007_create_quote_items_table.php` - quote_items schema with cascadeOnDelete
- `database/factories/QuoteFactory.php` - Factory with borrador/enviado/aceptado/rechazado state methods
- `database/factories/QuoteItemFactory.php` - Factory with Quote::factory() and randomFloat precio
- `tests/Feature/QuoteTest.php` - 13 markTestIncomplete stubs for QUOT-01/02/03
- `app/Models/Client.php` - Added quotes() hasMany relationship
- `composer.json` / `composer.lock` - Added barryvdh/laravel-dompdf

## Decisions Made

- nullOnDelete on quotes.client_id — financial record must survive client deletion (consistent with Phase 04 billings pattern)
- cascadeOnDelete on quote_items.quote_id — items are meaningless without parent quote
- QuoteFactory defaults to QuoteStatus::Borrador — gives predictable initial state for test setup
- barryvdh/laravel-dompdf was already installed at v3.1.2 — no conflict, confirmed via composer show

## Deviations from Plan

None - plan executed exactly as written.

Note: BillingDashboardTest::cobrado_mes_excludes_other_months was already failing before this plan (pre-existing issue, out of scope). Confirmed by git stash + retest. Logged to deferred items.

## Issues Encountered

Pre-existing BillingDashboardTest failure (1 test, `cobrado_mes_excludes_other_months`) present before this plan's changes. Not introduced by this work. Out of scope per deviation rules boundary.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Data layer complete: Quote and QuoteItem models fully wired with relationships and casts
- Both tables migrated and verified (migrate:status Ran)
- 13 test stubs ready to be filled in by 05-02 (QuoteController) and 05-03 (PDF)
- dompdf installed and available for 05-03

---
*Phase: 05-presupuestos-y-pdf*
*Completed: 2026-03-30*
