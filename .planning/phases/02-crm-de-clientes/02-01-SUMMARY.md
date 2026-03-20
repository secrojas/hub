---
phase: 02-crm-de-clientes
plan: 01
subsystem: database
tags: [eloquent, migrations, mysql, factory, relationships]

# Dependency graph
requires:
  - phase: 01-fundacion-y-auth
    provides: User model, Invitation model, Role enum, auth middleware, migrations pattern
provides:
  - clients table with all CRM fields (nombre, email, empresa, telefono, stack_tecnologico, estado enum, notas, fecha_inicio)
  - FK client_id on users table (nullable, nullOnDelete)
  - FK client_id on invitations table (nullable, nullOnDelete)
  - Client Eloquent model with HasFactory, fillable, date cast, user() HasOne, invitations() HasMany
  - User model updated: client_id fillable + client() belongsTo
  - Invitation model updated: client_id fillable + client() belongsTo
  - ClientFactory for test data generation
  - 13 test stubs (ClientTest 6, ClientCrudTest 4, ClientInvitationTest 3) — all incomplete, 0 failures
affects: [02-02, 02-03, all subsequent CRM plans]

# Tech tracking
tech-stack:
  added: []
  patterns: [nullable FK with nullOnDelete for soft client associations, enum column for state machine (activo/potencial/pausado)]

key-files:
  created:
    - database/migrations/2026_03_20_000001_create_clients_table.php
    - database/migrations/2026_03_20_000002_add_client_id_to_users_table.php
    - database/migrations/2026_03_20_000003_add_client_id_to_invitations_table.php
    - app/Models/Client.php
    - database/factories/ClientFactory.php
    - tests/Feature/Clients/ClientTest.php
    - tests/Feature/Clients/ClientCrudTest.php
    - tests/Feature/Clients/ClientInvitationTest.php
  modified:
    - app/Models/User.php
    - app/Models/Invitation.php

key-decisions:
  - "nullOnDelete on FK client_id in users and invitations — clients can be deleted without cascade-deleting users or invitations"
  - "estado as enum column (activo/potencial/pausado default activo) — enforced at DB level, not just application"
  - "fecha_inicio as nullable date — optional onboarding start date, not required for client creation"

patterns-established:
  - "FK nullable pattern: foreignId()->nullable()->constrained()->nullOnDelete() — safe deletion without orphan errors"
  - "Test stubs with markTestIncomplete() — reserve test slots early, implement in later plans"

requirements-completed: [CLIE-01, CLIE-02]

# Metrics
duration: 8min
completed: 2026-03-20
---

# Phase 02 Plan 01: CRM Database Foundation Summary

**MySQL clients table with 8 CRM fields, nullable FK columns on users/invitations, bidirectional Eloquent relationships, and 13 test stubs covering all CRUD scenarios**

## Performance

- **Duration:** 8 min
- **Started:** 2026-03-20T14:04:38Z
- **Completed:** 2026-03-20T14:12:00Z
- **Tasks:** 2
- **Files modified:** 10

## Accomplishments
- Created clients table with all required CRM fields including estado enum with activo/potencial/pausado states
- Added nullable client_id FK columns to users and invitations tables with nullOnDelete semantics
- Built complete bidirectional relationships: Client hasOne User, Client hasMany Invitations, User belongsTo Client, Invitation belongsTo Client
- Created ClientFactory with realistic fake data for all 8 fields
- Planted 13 test stubs in 3 test classes for Plans 02-02 and 02-03 to implement — full suite green (39 passed, 13 incomplete, 0 failures)

## Task Commits

Each task was committed atomically:

1. **Task 1: Create migrations, Client model, and ClientFactory** - `88ec945` (feat)
2. **Task 2: Update User and Invitation models + create test stubs** - `016a05d` (feat)

**Plan metadata:** (docs commit — see below)

## Files Created/Modified
- `database/migrations/2026_03_20_000001_create_clients_table.php` — clients table with id, nombre, email unique, empresa, telefono, stack_tecnologico, estado enum, notas, fecha_inicio, timestamps
- `database/migrations/2026_03_20_000002_add_client_id_to_users_table.php` — nullable FK on users with nullOnDelete
- `database/migrations/2026_03_20_000003_add_client_id_to_invitations_table.php` — nullable FK on invitations with nullOnDelete
- `app/Models/Client.php` — Eloquent model with HasFactory, 8 fillable fields, fecha_inicio date cast, user() HasOne, invitations() HasMany
- `database/factories/ClientFactory.php` — fake data for all 8 fields using Faker
- `app/Models/User.php` — added client_id to fillable, BelongsTo import, client() relationship
- `app/Models/Invitation.php` — added client_id to fillable, BelongsTo import, client() relationship
- `tests/Feature/Clients/ClientTest.php` — 6 incomplete stubs (create, update, delete, all fields, validation, duplicate email)
- `tests/Feature/Clients/ClientCrudTest.php` — 4 incomplete stubs (list, filter, detail, active user indicator)
- `tests/Feature/Clients/ClientInvitationTest.php` — 3 incomplete stubs (invite wiring, active user blocks, accept sets client_id)

## Decisions Made
- nullOnDelete on both FK columns: deleting a client nullifies the association on users and invitations rather than cascading deletes — preserves historical data
- estado as MySQL enum enforced at DB level, not just application layer — prevents invalid states even if app code has bugs
- fecha_inicio is nullable date column — clients can be created without a start date, added when known

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness
- Database schema complete and all 3 migrations have been run
- Client model fully wired with relationships to User and Invitation
- 13 test stubs ready for Plans 02-02 (CRUD controllers/views) and 02-03 (invitation wiring)
- No blockers for Phase 02 Plan 02

---
*Phase: 02-crm-de-clientes*
*Completed: 2026-03-20*
