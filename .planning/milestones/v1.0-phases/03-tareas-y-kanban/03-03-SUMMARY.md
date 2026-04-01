---
phase: 03-tareas-y-kanban
plan: "03"
subsystem: ui
tags: [vue3, inertia, kanban, filters, testing]

# Dependency graph
requires:
  - phase: 03-02
    provides: TaskController with filter query params, Index.vue Kanban board with columns prop, clients prop, filtros prop

provides:
  - Filter bar on Kanban page (client dropdown, estado dropdown, prioridad dropdown, titulo search with debounce)
  - router.get with preserveState + preserveScroll + replace for filter navigation
  - Ver Kanban link in Clients/Show.vue navigating to /tasks?cliente={id}
  - Tareas nav link in AdminLayout.vue with active highlighting
  - 4 passing filter tests covering TASK-05 (by cliente, estado, titulo, prioridad)

affects: [04-portal-cliente, 07-reportes-y-dashboard]

# Tech tracking
tech-stack:
  added: []
  patterns: [router.get with preserveState/replace for filter navigation, debounced input with clearTimeout/setTimeout pattern]

key-files:
  created:
    - tests/Feature/Tasks/TaskFilterTest.php
  modified:
    - resources/js/Pages/Admin/Tasks/Index.vue
    - resources/js/Pages/Admin/Clients/Show.vue
    - resources/js/Layouts/AdminLayout.vue

key-decisions:
  - "Filter navigation uses router.get with replace:true to prevent browser history pollution from typing in search"
  - "Titulo input debounced 300ms to avoid excessive Inertia requests on each keystroke"
  - "Filter tests use collect()->flatMap()->pluck('id') to merge all columns for presence/absence assertions"

patterns-established:
  - "Filter bar pattern: initialize ref from props.filtros, applyFilters strips empties, onTituloInput debounces"
  - "Test presence/absence across Inertia columns: collect($columns)->flatMap(fn($col) => collect($col)->pluck('id'))"

requirements-completed: [TASK-04, TASK-05]

# Metrics
duration: 2min
completed: 2026-03-20
---

# Phase 03 Plan 03: Kanban Filter Bar and Navigation Summary

**Filterable Kanban board with 4-input filter bar (client/estado/prioridad/titulo), Ver Kanban deep-link from client detail, Tareas nav item, and 4 passing filter tests covering all TASK-05 acceptance criteria**

## Performance

- **Duration:** 2 min
- **Started:** 2026-03-20T18:51:03Z
- **Completed:** 2026-03-20T18:52:57Z
- **Tasks:** 2
- **Files modified:** 4

## Accomplishments
- Filter bar with client dropdown, estado dropdown, prioridad dropdown, and debounced titulo search added above Kanban board
- "Ver Kanban" button on client detail page navigates to /tasks?cliente={id} for instant filtered view
- "Tareas" nav link added to AdminLayout with active state highlighting on /tasks routes
- 4 filter tests pass (65 total suite, 266 assertions — all green)

## Task Commits

Each task was committed atomically:

1. **Task 1: Add filter bar, Ver Kanban link, Tareas nav** - `dadfcd6` (feat)
2. **Task 2: Implement 4 filter tests** - `c0078e3` (feat)

**Plan metadata:** (docs commit follows)

## Files Created/Modified
- `resources/js/Pages/Admin/Tasks/Index.vue` - Filter bar with 4 inputs, applyFilters function, debounced onTituloInput
- `resources/js/Pages/Admin/Clients/Show.vue` - Ver Kanban link navigating to /tasks?cliente={id}
- `resources/js/Layouts/AdminLayout.vue` - Tareas nav link with active state
- `tests/Feature/Tasks/TaskFilterTest.php` - 4 filter tests (removed markTestIncomplete stubs)

## Decisions Made
- Filter navigation uses `router.get` with `replace: true` to prevent history pollution when user types in search
- Titulo input debounced at 300ms — prevents flood of Inertia requests on rapid typing
- Filter tests use `collect()->flatMap()->pluck('id')` pattern to merge all 4 Kanban columns into a flat ID list for presence/absence assertions

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness
- Kanban board is fully filterable and navigable from client detail pages
- TASK-04 and TASK-05 requirements satisfied
- Phase 03 complete — all 3 plans done (migration+models, Kanban board, filters+nav)
- Ready for Phase 04 (portal cliente) or Phase 05 (tareas adicionales)

---
*Phase: 03-tareas-y-kanban*
*Completed: 2026-03-20*
