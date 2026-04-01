---
phase: 03-tareas-y-kanban
plan: 01
subsystem: database
tags: [laravel, eloquent, mysql, enums, factory, testing]

# Dependency graph
requires:
  - phase: 02-crm-de-clientes
    provides: Client model with HasMany/HasOne relations pattern established
provides:
  - TaskStatus backed enum (backlog, en_progreso, en_revision, finalizado)
  - TaskPriority backed enum (baja, media, alta)
  - tasks migration with client_id FK cascadeOnDelete
  - Task Eloquent model with casts and client() belongsTo
  - Client model updated with tasks() hasMany
  - TaskFactory for test data generation
  - 12 test stubs across 3 test files (all incomplete pending TaskController)
affects: [03-02-task-controller, 03-03-kanban-ui, 05-pdf-reports]

# Tech tracking
tech-stack:
  added: []
  patterns: [PHP string-backed enum with Eloquent cast, cascadeOnDelete on task-client FK]

key-files:
  created:
    - app/Enums/TaskStatus.php
    - app/Enums/TaskPriority.php
    - database/migrations/2026_03_20_000004_create_tasks_table.php
    - app/Models/Task.php
    - database/factories/TaskFactory.php
    - tests/Feature/Tasks/TaskCrudTest.php
    - tests/Feature/Tasks/TaskKanbanTest.php
    - tests/Feature/Tasks/TaskFilterTest.php
  modified:
    - app/Models/Client.php

key-decisions:
  - "cascadeOnDelete on client_id in tasks — orphaned tasks have no business value without a client"
  - "TaskFactory defaults estado to backlog (not random) — tests need predictable initial state"
  - "Test stubs use markTestIncomplete pending TaskController — ensures Nyquist compliance from plan 01"

patterns-established:
  - "Enum pattern: string-backed PHP enum matching DB enum values exactly (same as Role.php)"
  - "Casts pattern: protected function casts() returning enum class references (same as Client model)"
  - "Test stub pattern: markTestIncomplete('Pending X') for future-plan dependencies"

requirements-completed: [TASK-01]

# Metrics
duration: 3min
completed: 2026-03-20
---

# Phase 03 Plan 01: Task Data Foundation Summary

**TaskStatus/TaskPriority enums, tasks migration with cascadeOnDelete, Task Eloquent model with casts, Client tasks() hasMany, TaskFactory, and 12 test stubs across 3 files**

## Performance

- **Duration:** 3 min
- **Started:** 2026-03-20T18:40:27Z
- **Completed:** 2026-03-20T18:43:15Z
- **Tasks:** 2
- **Files modified:** 9

## Accomplishments
- Tasks table created with all 7 columns (id, client_id, titulo, descripcion, prioridad, estado, fecha_limite) + timestamps
- Task model with Eloquent casts for TaskStatus, TaskPriority, and date; client() belongsTo relationship
- Client model updated with tasks() hasMany relationship
- TaskFactory generates valid tasks with nested Client::factory() association
- 12 test stubs across TaskCrudTest, TaskKanbanTest, TaskFilterTest — all incomplete, 53 existing tests green

## Task Commits

Each task was committed atomically:

1. **Task 1: Create enums, migration, Task model, update Client** - `95166aa` (feat)
2. **Task 2: Create TaskFactory and test stubs** - `784ef65` (feat)

**Plan metadata:** (docs commit follows)

## Files Created/Modified
- `app/Enums/TaskStatus.php` - String-backed PHP enum: backlog, en_progreso, en_revision, finalizado
- `app/Enums/TaskPriority.php` - String-backed PHP enum: baja, media, alta
- `database/migrations/2026_03_20_000004_create_tasks_table.php` - tasks table with FK cascadeOnDelete
- `app/Models/Task.php` - Eloquent model with fillable, casts, client() belongsTo
- `app/Models/Client.php` - Added tasks() hasMany relationship
- `database/factories/TaskFactory.php` - Factory with Client::factory() association, defaults estado to backlog
- `tests/Feature/Tasks/TaskCrudTest.php` - 4 stubs: create, validation, update, delete
- `tests/Feature/Tasks/TaskKanbanTest.php` - 4 stubs: index scoped, update status, invalid status, global view
- `tests/Feature/Tasks/TaskFilterTest.php` - 4 stubs: filter by cliente, estado, titulo, prioridad

## Decisions Made
- cascadeOnDelete on client_id: orphaned tasks have no business value without a client
- TaskFactory defaults estado to 'backlog': gives predictable initial state for Kanban tests
- Test stubs marked incomplete rather than skipped: markTestIncomplete gives clearer output + reason

## Deviations from Plan
None - plan executed exactly as written.

## Issues Encountered
None.

## User Setup Required
None - no external service configuration required.

## Next Phase Readiness
- Task data layer complete; plan 03-02 can implement TaskController with CRUD + status update endpoints
- TaskFactory ready for use in all subsequent test files
- 12 test stubs waiting to be fulfilled in plans 02 and 03

## Self-Check: PASSED

- All 9 files verified present on disk
- Commits 95166aa and 784ef65 confirmed in git log
- Migration 2026_03_20_000004_create_tasks_table confirmed Ran status
- 12 test stubs confirmed incomplete (0 failures), 53 existing tests green

---
*Phase: 03-tareas-y-kanban*
*Completed: 2026-03-20*
