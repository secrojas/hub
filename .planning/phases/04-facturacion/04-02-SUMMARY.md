---
phase: 04-facturacion
plan: 02
subsystem: billing-crud
tags: [laravel, inertia, vue3, billing, crud, form-requests, validation]

# Dependency graph
requires:
  - phase: 04-01
    provides: Billing model, BillingStatus enum, BillingFactory, 15 test stubs
  - phase: 02-crm-de-clientes
    provides: Client model for FK and dropdown data
provides:
  - BillingController with 6 resource methods (index/create/store/edit/update/destroy)
  - StoreBillingRequest and UpdateBillingRequest with required_if:estado,pagado
  - Billing resource routes under auth+admin middleware
  - Admin/Billing/Index.vue with filter bar and delete modal
  - Admin/Billing/Create.vue with fecha_pago: null initialization
  - Admin/Billing/Edit.vue with delete confirmation modal (ref(null) sentinel pattern)
  - 9 green tests (5 CRUD + 4 validation)
affects: [04-03-billing-ui]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - Form Request with required_if:estado,pagado — conditional date validation
    - BillingController index builds summary array with (float) casts to avoid "0" string
    - Create.vue fecha_pago initialized as null (not empty string) for correct required_if behavior
    - Edit.vue uses useForm({}).delete() in sentinel-triggered modal — same as Clients/Index pattern
    - Index.vue uses router.get with preserveState/preserveScroll/replace — same as Tasks pattern

key-files:
  created:
    - app/Http/Controllers/BillingController.php
    - app/Http/Requests/StoreBillingRequest.php
    - app/Http/Requests/UpdateBillingRequest.php
    - resources/js/Pages/Admin/Billing/Index.vue
    - resources/js/Pages/Admin/Billing/Create.vue
    - resources/js/Pages/Admin/Billing/Edit.vue
  modified:
    - routes/web.php
    - tests/Feature/Billing/BillingCrudTest.php
    - tests/Feature/Billing/BillingValidationTest.php

key-decisions:
  - "fecha_pago: null in useForm (not empty string) — required_if:estado,pagado fires on null/missing but NOT on empty string in some Laravel versions; null is the correct sentinel"
  - "BillingController summary uses (float) cast on sum() result — Laravel sum() returns '0' string on empty result set, breaking JS comparisons"
  - "Edit.vue delete uses useForm({}).delete() called from modal confirm button — consistent with Clients pattern, avoids inline router.delete"

patterns-established:
  - "BillingController index returns paginated billings + clients + filtros + summary — all props the Index Vue page needs in one shot"
  - "required_if conditional validation on fecha_pago when estado=pagado — billing-specific pattern for conditional date requirement"

requirements-completed: [BILL-01, BILL-02]

# Metrics
duration: 8min
completed: 2026-03-25
---

# Phase 4 Plan 2: Billing CRUD Controller, Form Requests, Vue Pages Summary

**BillingController (6 methods) + StoreBillingRequest/UpdateBillingRequest (required_if:estado,pagado) + three Vue pages (Index/Create/Edit) + 9 green tests**

## Performance

- **Duration:** 8 min
- **Started:** 2026-03-25T12:06:16Z
- **Completed:** 2026-03-25T12:14:00Z
- **Tasks:** 2
- **Files modified:** 9

## Accomplishments

- BillingController: full CRUD (index paginate(20), create, store, edit, update, destroy), summary with (float) casts for JS safety
- StoreBillingRequest + UpdateBillingRequest: required_if:estado,pagado enforces fecha_pago when billing is marked paid
- Routes: `Route::resource('billing', BillingController::class)->except(['show'])` inside auth+admin middleware group
- Admin/Billing/Index.vue: billing table with estado+cliente filter bar (router.get preserveState), pagination, per-row delete modal (ref(null) sentinel pattern)
- Admin/Billing/Create.vue: form with `fecha_pago: null` initialization (not empty string) — critical for correct required_if behavior
- Admin/Billing/Edit.vue: form.put update + delete confirmation modal with sentinel pattern
- 9 tests green: 5 CRUD (index/create/update/delete/guest redirect) + 4 validation (required fields, fecha_pago required_if, pendiente no-error, invalid estado)
- Full suite: 74 passed, 6 incomplete (BillingDashboard stubs — expected), 0 failures

## Task Commits

Each task was committed atomically:

1. **Task 1: BillingController + Form Requests + routes + CRUD tests** - `01243af` (feat)
2. **Task 2: Index/Create/Edit Vue pages** - `f011ad7` (feat)

## Files Created/Modified

- `app/Http/Controllers/BillingController.php` - CRUD controller with 6 resource methods and summary aggregates
- `app/Http/Requests/StoreBillingRequest.php` - store validation with required_if:estado,pagado
- `app/Http/Requests/UpdateBillingRequest.php` - update validation (same rules as store)
- `routes/web.php` - billing resource route added inside auth+admin group
- `tests/Feature/Billing/BillingCrudTest.php` - 5 CRUD tests implemented (was stubs)
- `tests/Feature/Billing/BillingValidationTest.php` - 4 validation tests implemented (was stubs)
- `resources/js/Pages/Admin/Billing/Index.vue` - billing table with filters, pagination, delete modal
- `resources/js/Pages/Admin/Billing/Create.vue` - create form with fecha_pago: null
- `resources/js/Pages/Admin/Billing/Edit.vue` - edit form with delete confirmation modal

## Decisions Made

- **fecha_pago: null in useForm**: Empty string `''` in Inertia forms can behave unexpectedly with Laravel's `required_if` rule — null is the explicit "not provided" sentinel that ensures correct conditional validation.
- **BillingController summary (float) cast**: `DB::sum()` returns `'0'` (string) on empty result, not `0` (int/float). Casting ensures JavaScript comparisons (`summary.cobrado_mes > 0`) work correctly.
- **Edit.vue delete via useForm({}).delete()**: Consistent with Clients pattern; keeps delete action separate from the form.put() so both can coexist without form state conflicts.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocker] Vue pages created before Task 1 test verification**

- **Found during:** Task 1 verification (running BillingCrudTest)
- **Issue:** `test_admin_can_view_billing_index` asserts `assertInertia` with component `Admin/Billing/Index` — Inertia test helper checks that the component file exists on disk. Vue pages did not exist yet (Task 2 work), causing test failure with "Inertia page component file [Admin/Billing/Index] does not exist."
- **Fix:** Created all three Vue pages (Index/Create/Edit) before the Task 1 verification step. The pages were committed under Task 2's commit as planned.
- **Files modified:** resources/js/Pages/Admin/Billing/ (all three files)
- **Commit:** f011ad7

## Issues Encountered

None — plan executed with one forward deviation (Vue pages needed earlier for test verification).

## User Setup Required

None.

## Next Phase Readiness

- BillingController fully functional: CRUD operations working, required_if validation enforced, routes registered
- Plan 03 (Billing UI/Dashboard) can now add summary cards to Index.vue and implement the BillingDashboardTest stubs
- All 6 BillingDashboardTest stubs remain as markTestIncomplete — ready for Plan 03 implementation

---
*Phase: 04-facturacion*
*Completed: 2026-03-25*

## Self-Check: PASSED

- All 10 files found on disk (9 implementation + 1 SUMMARY)
- Commits 01243af and f011ad7 verified in git log
