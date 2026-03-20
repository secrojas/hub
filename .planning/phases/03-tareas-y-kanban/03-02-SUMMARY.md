---
phase: 03-tareas-y-kanban
plan: 02
subsystem: ui
tags: [vue3, inertia, kanban, drag-drop, vue-draggable-plus, laravel, eloquent]

# Dependency graph
requires:
  - phase: 03-tareas-y-kanban-plan-01
    provides: Task model, TaskStatus enum, TaskPriority enum, TaskFactory, test stubs

provides:
  - TaskController with index/store/update/destroy/updateStatus endpoints
  - StoreTaskRequest and UpdateTaskRequest form requests with validation
  - tasks resource routes + tasks.updateStatus PUT route
  - Admin/Tasks/Index.vue Kanban board with vue-draggable-plus cross-column drag
  - Optimistic update + rollback on drag failure; watch() re-sync on prop update
  - Create modal (useForm), edit modal (sentinel pattern), delete from edit modal
  - 8 passing tests (4 CRUD + 4 Kanban)

affects:
  - 03-tareas-y-kanban-plan-03 (TaskFilterTest stubs become real assertions)

# Tech tracking
tech-stack:
  added: [vue-draggable-plus@0.6.1]
  patterns:
    - VueDraggable group sharing with event.added guard for cross-column status updates
    - Optimistic update via JSON.parse/stringify snapshot + onError rollback
    - watch() deep on props.columns to re-sync local state after Inertia server update
    - Sentinel ref pattern (editingTask = ref(null)) for edit modal
    - Collection filter with enum case comparison (filter fn($t) => $t->estado === TaskStatus::Backlog)

key-files:
  created:
    - app/Http/Controllers/TaskController.php
    - app/Http/Requests/StoreTaskRequest.php
    - app/Http/Requests/UpdateTaskRequest.php
    - resources/js/Pages/Admin/Tasks/Index.vue
  modified:
    - routes/web.php
    - tests/Feature/Tasks/TaskCrudTest.php
    - tests/Feature/Tasks/TaskKanbanTest.php
    - package.json

key-decisions:
  - "Collection grouping uses enum case comparison (TaskStatus::Backlog) not string comparison — cast enum values don't match plain strings in Collection::where"
  - "test_update_status_rejects_invalid_estado uses assertSessionHasErrors(['estado']) not assertStatus(422) — controller uses redirect-back pattern, not JSON API"
  - "updateStatus validates inline with $request->validate() — no separate form request, estado not in standard update form"

patterns-established:
  - "Kanban drag-drop: VueDraggable group prop + @change event with event.added guard prevents double-firing"
  - "Optimistic rollback: capture previousColumns snapshot before PUT, restore in onError callback"
  - "fecha_limite display: task.fecha_limite?.substring(0, 10) for ISO date truncation in Vue"

requirements-completed: [TASK-01, TASK-02, TASK-03, TASK-04]

# Metrics
duration: 2min
completed: 2026-03-20
---

# Phase 03 Plan 02: Tareas y Kanban Controller + Board Summary

**TaskController (CRUD + updateStatus) + vue-draggable-plus Kanban board with cross-column drag, optimistic rollback, and inline create/edit/delete modals — 8 tests green, full suite 61 passed**

## Performance

- **Duration:** 2 min
- **Started:** 2026-03-20T18:45:24Z
- **Completed:** 2026-03-20T18:47:44Z
- **Tasks:** 2
- **Files modified:** 8

## Accomplishments

- TaskController with 5 endpoints (index, store, update, destroy, updateStatus) + routes registered
- Admin/Tasks/Index.vue Kanban board with VueDraggable cross-column drag-drop, optimistic update + rollback
- Create modal, edit modal (sentinel pattern), delete-from-edit-modal all inline — no page navigation
- 8 Task tests passing (4 CRUD + 4 Kanban); full suite 61 passed, 4 incomplete (TaskFilterTest plan 03)

## Task Commits

Each task was committed atomically:

1. **Task 1: TaskController, form requests, routes, vue-draggable-plus** - `f233331` (feat)
2. **Task 2: Kanban Index.vue and real test assertions** - `e2319af` (feat)

**Plan metadata:** _(docs commit follows)_

## Files Created/Modified

- `app/Http/Controllers/TaskController.php` - Resource controller with 5 methods, enum-aware collection grouping
- `app/Http/Requests/StoreTaskRequest.php` - Validation: titulo required, client_id exists, prioridad enum, fecha_limite date
- `app/Http/Requests/UpdateTaskRequest.php` - Same validation as Store; no estado field
- `routes/web.php` - Added TaskController import, resource routes (except show/create/edit), updateStatus route
- `resources/js/Pages/Admin/Tasks/Index.vue` - Full Kanban board: VueDraggable, 4 columns, create/edit modals
- `tests/Feature/Tasks/TaskCrudTest.php` - Replaced 4 markTestIncomplete stubs with real assertions
- `tests/Feature/Tasks/TaskKanbanTest.php` - Replaced 4 markTestIncomplete stubs; fixed validation test to use assertSessionHasErrors
- `package.json` / `package-lock.json` - Added vue-draggable-plus@0.6.1

## Decisions Made

- **Collection grouping uses enum case comparison**: `$t->estado === TaskStatus::Backlog` not string `'backlog'` — after Eloquent casts, collection items hold enum instances, not strings. Plan noted this risk and recommended the filter approach.
- **Validation test uses assertSessionHasErrors not 422**: Controller uses `return back()` (redirect pattern); invalid estado redirects with session errors. Plan explicitly allowed "assertSessionHasErrors or assert 422" — chose the approach matching actual behavior.
- **updateStatus validates inline**: No form request class for status change — validation is a single `in:` rule, inline validation appropriate.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Fixed assertStatus(422) to assertSessionHasErrors in TaskKanbanTest**
- **Found during:** Task 2 (running `php artisan test --filter=Task`)
- **Issue:** `test_update_status_rejects_invalid_estado` expected HTTP 422 but controller returns 302 redirect with session errors (standard Laravel form redirect, not JSON API)
- **Fix:** Changed `$response->assertStatus(422)` to `$response->assertSessionHasErrors(['estado'])`
- **Files modified:** tests/Feature/Tasks/TaskKanbanTest.php
- **Verification:** Test passes, 8/8 Task tests green
- **Committed in:** e2319af (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (Rule 1 - bug in test assertion)
**Impact on plan:** Minor test assertion correction. Plan explicitly stated "assertSessionHasErrors or assert 422" so this was within intended scope.

## Issues Encountered

None beyond the test assertion fix above.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- TaskController and Kanban board fully operational at /tasks
- TASK-01 through TASK-04 requirements satisfied
- Phase 03 Plan 03 (TaskFilterTest real assertions + filter UI) ready to execute
- TaskFilterTest has 4 stubs marked `markTestIncomplete('Pending TaskController')` — these can now be implemented

---
*Phase: 03-tareas-y-kanban*
*Completed: 2026-03-20*

## Self-Check: PASSED

- FOUND: app/Http/Controllers/TaskController.php
- FOUND: app/Http/Requests/StoreTaskRequest.php
- FOUND: app/Http/Requests/UpdateTaskRequest.php
- FOUND: resources/js/Pages/Admin/Tasks/Index.vue
- FOUND: .planning/phases/03-tareas-y-kanban/03-02-SUMMARY.md
- FOUND commit f233331: feat(03-02): TaskController, form requests, routes, vue-draggable-plus
- FOUND commit e2319af: feat(03-02): Kanban Index.vue with drag-drop and modals, 8 tests passing
