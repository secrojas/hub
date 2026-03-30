---
phase: 02-crm-de-clientes
plan: 03
subsystem: crm
tags: [inertia, vue3, laravel, invitation, client-user-link]

# Dependency graph
requires:
  - phase: 02-crm-de-clientes
    provides: ClientController.show() passes hasActiveUser, Client model, Show.vue skeleton
  - phase: 01-fundaci-n-y-auth
    provides: InvitationController store/accept, signed URL invitation flow, Role enum
provides:
  - "Invitar al portal button on Show.vue POSTs to /invitations with client_id"
  - "InvitationController.store() validates and stores nullable client_id"
  - "InvitationController.accept() propagates client_id from invitation to user"
  - "AdminLayout Clientes nav link highlighting on /clients/* pages"
  - "3 ClientInvitationTest + 1 InvitationTest tests green (client-to-user association loop complete)"
affects:
  - 03-gestion-de-proyectos
  - portal phase

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "useForm.post() from Show.vue to /invitations preserving client context"
    - "Flash invitation_url displayed with readonly input click-to-select pattern"
    - "hasActiveUser computed server-side in controller — single source of truth"
    - "client_id nullable FK propagated: invitation -> user on accept"

key-files:
  created:
    - tests/Feature/Clients/ClientInvitationTest.php
  modified:
    - app/Http/Controllers/InvitationController.php
    - resources/js/Pages/Admin/Clients/Show.vue
    - resources/js/Layouts/AdminLayout.vue
    - tests/Feature/Auth/InvitationTest.php

key-decisions:
  - "Show.vue uses flash prop (usePage().props.flash.invitation_url) to display generated link — same pattern as Invitations/Create.vue"
  - "client_id propagation in accept() uses $user->update() after User::create() — allows nullable client_id without conditional User::create() branching"
  - "Clientes nav link placed between Dashboard and Invitar Cliente — logical left-to-right workflow order"

patterns-established:
  - "Portal invitation from client detail page: useForm with pre-filled email/client_name/client_id, POST /invitations, flash display"
  - "Active user guard: hasActiveUser prop from server controls button visibility, not client-side check"

requirements-completed: [CLIE-01, CLIE-04]

# Metrics
duration: 2min
completed: 2026-03-20
---

# Phase 02 Plan 03: CRM Invitation Wiring Summary

**Client-to-user association loop complete: Show.vue Invitar al portal button wires invitation with client_id, InvitationController propagates client_id to user on accept, AdminLayout gains Clientes nav link, 53 tests green**

## Performance

- **Duration:** ~2 min
- **Started:** 2026-03-20T13:13:26Z
- **Completed:** 2026-03-20T13:14:57Z
- **Tasks:** 2
- **Files modified:** 5

## Accomplishments
- InvitationController.store() now accepts nullable client_id (validation + storage), closing the CRM-to-invitation gap
- InvitationController.accept() propagates invitation.client_id to the created user, completing the client association loop
- Show.vue gains a full "Invitar al portal" section: active-user guard message, invite button with processing state, invitation_url flash display
- AdminLayout now shows Clientes nav link with active highlight when URL starts with /clients
- 3 ClientInvitationTest + 1 new InvitationTest passing; full suite 53/53 green

## Task Commits

Each task was committed atomically:

1. **Task 1: Wire invitation from Show page + update InvitationController + AdminLayout nav** - `713cf6c` (feat)
2. **Task 2: Complete invitation integration tests + full suite verification** - `3b47423` (feat)

**Plan metadata:** (docs commit — pending)

## Files Created/Modified
- `app/Http/Controllers/InvitationController.php` - store() validates/stores client_id; accept() propagates client_id to user
- `resources/js/Pages/Admin/Clients/Show.vue` - Invitar al portal section with active-user guard, invite button, invitation_url flash
- `resources/js/Layouts/AdminLayout.vue` - Clientes nav link between Dashboard and Invitar Cliente
- `tests/Feature/Clients/ClientInvitationTest.php` - 3 tests: invite wires client_id, active user blocks, accept propagates
- `tests/Feature/Auth/InvitationTest.php` - added test_accept_sets_user_client_id

## Decisions Made
- Show.vue reads flash via `usePage().props.flash?.invitation_url` computed — consistent with Invitations/Create.vue pattern
- client_id propagation uses a separate `$user->update()` call after `User::create()` rather than including client_id in the create payload — keeps the create call clean and the propagation logic isolated and readable
- Nav link positioned between Dashboard and Invitar Cliente to follow the natural admin workflow: view clients, then invite them

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered
None.

## User Setup Required
None - no external service configuration required.

## Next Phase Readiness
- The client-to-user association loop is fully wired and tested
- Any phase that reads `user.client_id` or `invitation.client_id` can now rely on these being correctly populated
- Phase 3 (Gestion de Proyectos) can link projects to clients via the established client model

## Self-Check: PASSED

All files confirmed present on disk. All task commits (713cf6c, 3b47423) verified in git log.

---
*Phase: 02-crm-de-clientes*
*Completed: 2026-03-20*
