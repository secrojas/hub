---
phase: 05-presupuestos-y-pdf
plan: 02
subsystem: ui
tags: [laravel, inertia, vue3, quotes, crud, form-requests]

# Dependency graph
requires:
  - phase: 05-01-presupuestos-y-pdf
    provides: Quote, QuoteItem models, migrations, factories, QuoteStatus enum

provides:
  - QuoteController with full CRUD + updateEstado + pdf route stub
  - StoreQuoteRequest and UpdateQuoteRequest with nested items validation
  - Admin/Quotes/Index.vue with estado badges, inline estado change, delete modal, PDF link
  - Admin/Quotes/Create.vue with dynamic items, computed total, formatMonto
  - Admin/Quotes/Edit.vue with conditional borrador/post-borrador rendering
  - quotes routes registered in web.php (resource + updateEstado + pdf)
  - Presupuestos nav link in AdminLayout
  - 10 passing feature tests for QUOT-01 and QUOT-02

affects: [05-03-presupuestos-y-pdf]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - abort_if guard on update/destroy for estado-based access control
    - Nested items validation with items.*.descripcion pattern in Form Requests
    - Re-insert pattern for items update (delete all then re-create)
    - v-if borrador/post-borrador conditional rendering in Edit.vue
    - computed() total from dynamic items array in Vue form

key-files:
  created:
    - app/Http/Controllers/QuoteController.php
    - app/Http/Requests/StoreQuoteRequest.php
    - app/Http/Requests/UpdateQuoteRequest.php
    - resources/js/Pages/Admin/Quotes/Index.vue
    - resources/js/Pages/Admin/Quotes/Create.vue
    - resources/js/Pages/Admin/Quotes/Edit.vue
  modified:
    - routes/web.php
    - resources/js/Layouts/AdminLayout.vue
    - tests/Feature/QuoteTest.php

key-decisions:
  - "Re-insert pattern for quote items update: delete all + re-create is simpler and correct for this use case"
  - "QuoteController index uses ->get()->map() (not paginate) to append computed total per quote — avoids N+1 on paginated result"
  - "pdf route registered now for URL generation even though method is not yet implemented (Plan 03)"

patterns-established:
  - "Nested items validation: items => [required, array, min:1] + items.*.field rules"
  - "abort_if(estado !== Borrador, 403) as single-line guard for estado-locked operations"
  - "Edit.vue dual mode: v-if borrador renders full form, v-else renders read-only with estado change and PDF link"

requirements-completed: [QUOT-01, QUOT-02]

# Metrics
duration: 4min
completed: 2026-03-30
---

# Phase 05 Plan 02: Quotes CRUD and State Management Summary

**Full QuoteController (CRUD + updateEstado), nested items Form Requests, three Vue pages with dynamic items and conditional read-only mode, all QUOT-01 and QUOT-02 feature tests passing (10 tests, 3 PDF stubs remain for Plan 03)**

## Performance

- **Duration:** 4 min
- **Started:** 2026-03-30T16:17:30Z
- **Completed:** 2026-03-30T16:17:54Z
- **Tasks:** 2
- **Files modified:** 9

## Accomplishments

- QuoteController with index, create, store, edit, update, destroy, updateEstado — abort_if guards enforce borrador-only editing and deletion
- Three Vue pages: Index (badges, inline estado change, delete modal, PDF link), Create (dynamic items with addItem/removeItem + computed total), Edit (conditional borrador/post-borrador modes)
- 10 feature tests passing for QUOT-01 (CRUD) and QUOT-02 (state management), 3 PDF stubs remain as markTestIncomplete for Plan 03
- Presupuestos nav link added to AdminLayout sidebar

## Task Commits

Each task was committed atomically:

1. **Task 1: QuoteController, Form Requests, routes, and nav link** - `434bd5b` (feat)
2. **Task 2: Vue pages and QuoteTest** - `00b02df` (feat)

**Plan metadata:** _(pending final commit)_

## Files Created/Modified

- `app/Http/Controllers/QuoteController.php` - Resource controller with CRUD, updateEstado, pdf route stub; abort_if guards on update/destroy
- `app/Http/Requests/StoreQuoteRequest.php` - Nested items validation (items, items.*.descripcion, items.*.precio)
- `app/Http/Requests/UpdateQuoteRequest.php` - Same rules as Store
- `resources/js/Pages/Admin/Quotes/Index.vue` - Table with estado badges, inline estado change select, delete modal (ref null sentinel), PDF link for non-borrador
- `resources/js/Pages/Admin/Quotes/Create.vue` - Dynamic items form with addItem/removeItem, computed total, formatMonto
- `resources/js/Pages/Admin/Quotes/Edit.vue` - v-if borrador (editable) / v-else (read-only items + estado change + PDF link)
- `routes/web.php` - quotes resource + quotes.updateEstado (PATCH) + quotes.pdf (GET stub)
- `resources/js/Layouts/AdminLayout.vue` - Presupuestos nav link after Facturación
- `tests/Feature/QuoteTest.php` - 10 tests implemented, 3 PDF stubs kept as incomplete

## Decisions Made

- Re-insert pattern for items update: delete all existing items then re-create from request — simpler than diffing, correct for quotes use case
- QuoteController index uses `->get()->map()` to append computed total per quote (not paginate) — keeps the total computation straightforward
- `pdf` route registered now for URL generation in templates, even though `pdf()` method is implemented in Plan 03

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

- Pre-existing BillingDashboardTest failure (`cobrado_mes excludes other months`) was present before these changes — confirmed by git stash verification. Not caused by this plan.

## Next Phase Readiness

- Plan 03 (PDF generation) can start immediately — routes are registered, 3 test stubs are ready, QuoteController::pdf() method just needs implementation
- All QUOT-01 and QUOT-02 requirements satisfied

---
*Phase: 05-presupuestos-y-pdf*
*Completed: 2026-03-30*
