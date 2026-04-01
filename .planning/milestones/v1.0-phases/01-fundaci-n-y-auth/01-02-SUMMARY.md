---
phase: 01-fundaci-n-y-auth
plan: 02
subsystem: auth
tags: [laravel, inertia, vue3, middleware, role-based-access, tailwind]

# Dependency graph
requires:
  - phase: 01-fundaci-n-y-auth
    plan: 01
    provides: Laravel 12 + Breeze scaffold, Role enum, User model with role column, DB schema, seeded admin user

provides:
  - EnsureIsAdmin middleware (abort 403 if not admin)
  - EnsureIsClient middleware (abort 403 if not client)
  - Middleware aliases 'admin' and 'client' registered in bootstrap/app.php
  - Inertia exception handler rendering Error.vue for 403/404/500/503
  - Role-based login redirect (Admin -> /dashboard, Client -> /portal)
  - Inertia shared auth data (id, name, email, role) + flash data on every request
  - Protected /dashboard route (auth + admin middleware)
  - Protected /portal route (auth + client middleware)
  - AdminLayout.vue with nav, user display, logout
  - PortalLayout.vue with distinct blue-50 styling, logout
  - Admin/Dashboard.vue page using AdminLayout as persistent layout
  - Portal/Index.vue page using PortalLayout as persistent layout
  - Error.vue standalone page for error status codes
  - LoginTest (3 tests) and MiddlewareTest (5 tests) all passing

affects:
  - all future phases (role-based access control is the security boundary)
  - Phase 02 (invitations) needs /portal and /dashboard routes established
  - Phase 03 (tasks/projects) uses AdminLayout as base
  - Any new admin routes must use middleware('admin') group pattern

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Middleware aliases: 'admin' and 'client' registered in bootstrap/app.php withMiddleware"
    - "Role-based routing: Route::middleware(['auth', 'admin']) groups for /dashboard, ['auth', 'client'] for /portal"
    - "Inertia error handling: withExceptions respond() renders Inertia Error page for 4xx/5xx"
    - "Persistent layouts: defineOptions({ layout: XLayout }) in Vue page script setup"
    - "Shared auth data: HandleInertiaRequests.share() returns auth.user with role via lazy closure"

key-files:
  created:
    - app/Http/Middleware/EnsureIsAdmin.php
    - app/Http/Middleware/EnsureIsClient.php
    - resources/js/Layouts/AdminLayout.vue
    - resources/js/Layouts/PortalLayout.vue
    - resources/js/Pages/Admin/Dashboard.vue
    - resources/js/Pages/Portal/Index.vue
    - resources/js/Pages/Error.vue
  modified:
    - bootstrap/app.php
    - app/Http/Controllers/Auth/AuthenticatedSessionController.php
    - app/Http/Middleware/HandleInertiaRequests.php
    - routes/web.php
    - database/factories/UserFactory.php
    - tests/Feature/Auth/LoginTest.php
    - tests/Feature/Auth/MiddlewareTest.php

key-decisions:
  - "Did not use 'verified' middleware on /dashboard and /portal — email verification not used in this project (admin is seeded with email_verified_at set)"
  - "Replaced Breeze default unprotected /dashboard route with role-guarded groups for both /dashboard (admin) and /portal (client)"
  - "Inertia share() uses lazy closures (fn () =>) so auth data is only resolved on actual requests, not during middleware boot"
  - "Error.vue has no layout — it is a full-page standalone component for error states"

patterns-established:
  - "Middleware pair pattern: EnsureIsAdmin / EnsureIsClient check role enum directly, abort(403) on mismatch"
  - "Route group pattern: Route::middleware(['auth', 'admin'])->group() wraps all admin routes"
  - "Persistent layout pattern: defineOptions({ layout: XLayout }) in page <script setup>"
  - "Logout pattern: router.post('/logout') called from layout component (not a Link with method=post)"

requirements-completed: [AUTH-01, AUTH-04, AUTH-05]

# Metrics
duration: 4min
completed: 2026-03-19
---

# Phase 01 Plan 02: Auth Flow Summary

**Role-based login redirect, admin/client middleware pair, two Inertia layouts with logout, Inertia shared auth data, and 8 passing tests enforcing the access boundary**

## Performance

- **Duration:** 4 min
- **Started:** 2026-03-19T22:10:23Z
- **Completed:** 2026-03-19T22:14:09Z
- **Tasks:** 2
- **Files modified:** 13

## Accomplishments

- Created EnsureIsAdmin / EnsureIsClient middleware pair registered as 'admin' / 'client' aliases — enforces role boundary via abort(403)
- Updated login redirect to route by role (Admin -> /dashboard, Client -> /portal) and added Inertia exception handler for 403/404/500/503 -> Error.vue
- Built AdminLayout and PortalLayout with logout, and Admin/Dashboard, Portal/Index, Error pages using Inertia persistent layout pattern
- 8 tests pass: LoginTest (3) verifies redirect and auth, MiddlewareTest (5) verifies role enforcement for both protected routes

## Task Commits

Each task was committed atomically:

1. **Task 1: Middleware pair, aliases, login redirect, Inertia shared data, error handler** - `01d6d69` (feat)
2. **Task 2: Layouts, pages, error page, and auth tests** - `d2ef0a0` (feat)

## Files Created/Modified

- `app/Http/Middleware/EnsureIsAdmin.php` - Checks Role::Admin, aborts 403 otherwise
- `app/Http/Middleware/EnsureIsClient.php` - Checks Role::Client, aborts 403 otherwise
- `bootstrap/app.php` - Added 'admin'/'client' aliases + Inertia exception respond() handler
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Role-based redirect after login
- `app/Http/Middleware/HandleInertiaRequests.php` - share() returns auth.user (id,name,email,role) + flash data
- `routes/web.php` - Replaced unprotected /dashboard with ['auth','admin'] and new ['auth','client'] /portal groups
- `resources/js/Layouts/AdminLayout.vue` - Admin nav with Hub branding, logout via router.post
- `resources/js/Layouts/PortalLayout.vue` - Portal nav with blue-50 background, logout via router.post
- `resources/js/Pages/Admin/Dashboard.vue` - Uses defineOptions({ layout: AdminLayout })
- `resources/js/Pages/Portal/Index.vue` - Uses defineOptions({ layout: PortalLayout })
- `resources/js/Pages/Error.vue` - Standalone full-page error with 403/404/500/503 messages in Spanish
- `database/factories/UserFactory.php` - Added 'role' => Role::Client default
- `tests/Feature/Auth/LoginTest.php` - 3 real tests replacing markTestIncomplete stubs
- `tests/Feature/Auth/MiddlewareTest.php` - 5 real tests replacing markTestIncomplete stubs

## Decisions Made

- Not using 'verified' middleware on protected routes — email verification is disabled in this project (admin seeded with email_verified_at set, clients invited directly)
- Replaced the Breeze default unprotected `/dashboard` route entirely; new route groups handle both admin and portal areas
- Error.vue intentionally has no layout to serve as a full-page standalone error display

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Ran npm run build before tests to populate Vite manifest**
- **Found during:** Task 2 verification (MiddlewareTest)
- **Issue:** MiddlewareTest failures showed "Unable to locate file in Vite manifest: resources/js/Pages/Error.vue" — Error.vue existed but hadn't been built into the manifest yet, causing 403 responses to throw a ViteException instead of rendering the error page
- **Fix:** Ran `npm run build` to update the Vite manifest with all new Vue files including Error.vue
- **Files modified:** public/build/manifest.json and compiled assets
- **Verification:** All 5 MiddlewareTest tests passed after build
- **Committed in:** d2ef0a0 (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (Rule 3 - blocking)
**Impact on plan:** Required step that plan noted as action item but did not require before running tests. Build is necessary for Inertia error rendering to work in test environment.

## Issues Encountered

None beyond the Vite manifest rebuild described above.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- Role-based access control is fully operational and tested
- AdminLayout and PortalLayout are ready for feature pages to slot into
- The 'admin' and 'client' middleware aliases are available for all future route groups
- Ready for Plan 03 (client invitation flow) and Plan 04 (tasks/projects)

---
*Phase: 01-fundaci-n-y-auth*
*Completed: 2026-03-19*

## Self-Check: PASSED

- FOUND: app/Http/Middleware/EnsureIsAdmin.php
- FOUND: app/Http/Middleware/EnsureIsClient.php
- FOUND: resources/js/Layouts/AdminLayout.vue
- FOUND: resources/js/Layouts/PortalLayout.vue
- FOUND: resources/js/Pages/Admin/Dashboard.vue
- FOUND: resources/js/Pages/Portal/Index.vue
- FOUND: resources/js/Pages/Error.vue
- FOUND: .planning/phases/01-fundaci-n-y-auth/01-02-SUMMARY.md
- FOUND commit: 01d6d69 (Task 1)
- FOUND commit: d2ef0a0 (Task 2)
- FOUND commit: dbe8a1e (metadata)
