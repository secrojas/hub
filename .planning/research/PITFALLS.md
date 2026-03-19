# Pitfalls Research: Hub — Plataforma de gestión de clientes freelance

**Domain:** Freelance client management platform (Laravel 11 + Inertia.js + Vue 3 + MySQL)
**Generated:** 2026-03-19

---

## Critical Pitfalls

### 1. Role-bypass via shared route namespace (data leak between clients)
**Warning signs:** Client routes share the same controller as admin routes; no Policy/Gate checks on resource ownership.
**Prevention:** Use Laravel Policies on every resource. Always scope queries to `auth()->user()->client_id` for client-role users. Never trust the route parameter alone.
**Phase:** Auth & Authorization setup (Phase 1)

### 2. Inertia page props leaking sensitive data
**Warning signs:** Passing full Eloquent models (with `internal_notes`, admin metrics, billing totals) to Inertia pages that clients can access.
**Prevention:** Use dedicated API Resources or explicit `only()` selectors when sharing props with client-facing pages. Never pass raw models.
**Phase:** Client Portal (portal phase)

### 3. PDF generation blocking the web process
**Warning signs:** DomPDF rendering with external CSS or complex layouts causes request timeouts (>30s) in production.
**Prevention:** Use `barryvdh/laravel-dompdf` with inlined styles only. For anything complex, queue the PDF generation and serve via signed URL. Test with realistic budget data early.
**Phase:** Presupuestos (budget phase)

### 4. Kanban state managed only in Vue (no rollback on API failure)
**Warning signs:** Drag-and-drop updates the UI immediately but API call fails silently — state diverges from DB.
**Prevention:** Implement optimistic UI updates with explicit rollback on API error. Show toast errors. Use `axios` interceptors to catch failures globally.
**Phase:** Tareas/Kanban (tasks phase)

### 5. Invitation system built incorrectly (custom token vs signed routes)
**Warning signs:** Rolling a custom invitation token system with manual expiry logic.
**Prevention:** Use Laravel's built-in `URL::temporarySignedRoute()` for invitation links. Handles expiry, signature validation, and revocation natively.
**Phase:** Auth & Authorization setup (Phase 1)

---

## Moderate Pitfalls

### 6. ARS currency formatting inconsistency
**Warning signs:** Amounts displayed differently across modules (1000 vs $1.000 vs $1,000.00).
**Prevention:** Centralize currency formatting in a single Vue composable (`useCurrency()`) and a Laravel helper. Use `decimal(15,2)` in MySQL — never `float`.
**Phase:** Facturación (billing phase)

### 7. Global Kanban N+1 + performance without filtering
**Warning signs:** Global Kanban view loads all tasks for all clients with all relationships in a single query.
**Prevention:** Eager load `with(['client', 'assignee'])`. Paginate or filter by default (exclude "Finalizado" from global view unless explicitly requested). Add indexes on `status` and `client_id`.
**Phase:** Tareas/Kanban (tasks phase)

### 8. Inertia shared data over-broadcasting to client users
**Warning signs:** `HandleInertiaRequests::share()` sends admin-only data (all clients list, billing totals) on every page load regardless of role.
**Prevention:** Conditionally share data based on role in the middleware. Client users should only receive their own data in shared props.
**Phase:** Auth & Authorization setup (Phase 1)

### 9. Budget status transitions without guard rails
**Warning signs:** Budget can go from "Rechazado" back to "Borrador" or skip states entirely via direct API calls.
**Prevention:** Implement a state machine for budget status (e.g., using a simple transition map or a package like `asantibanez/laravel-eloquent-state-machines`). Validate transitions server-side.
**Phase:** Presupuestos (budget phase)

---

## Minor Pitfalls

### 10. Vue component naming collision between admin and portal
**Warning signs:** `TaskCard.vue` used in both admin Kanban and client portal with different behavior.
**Prevention:** Use clear namespacing: `Admin/TaskKanbanCard.vue` vs `Portal/TaskListItem.vue`. Never share components that have role-conditional rendering.
**Phase:** Frontend structure (early phases)

### 11. MySQL `float` vs `decimal` for monetary amounts
**Warning signs:** Storing ARS amounts as `float` — rounding errors on large amounts (e.g., $1,500,000.50 stored incorrectly).
**Prevention:** Always use `decimal(15,2)` for any monetary column. Define this in migrations from day one.
**Phase:** Database setup (Phase 1)

### 12. Soft-delete scoping on dashboard queries
**Warning signs:** Dashboard totals include soft-deleted clients or invoices.
**Prevention:** Be explicit with `withTrashed()` vs default scopes. Dashboard queries should always use default (non-trashed) scopes.
**Phase:** Facturación / Dashboard (billing phase)

### 13. DomPDF character encoding with Spanish content
**Warning signs:** Budget PDFs show `?` instead of `ñ`, `á`, `é`, `ó`, `ú`.
**Prevention:** Set UTF-8 encoding in DomPDF config (`'default_charset' => 'UTF-8'`) and use `<meta charset="utf-8">` in the PDF Blade template. Test with Spanish content from the start.
**Phase:** Presupuestos (budget phase)

---

## Summary

| Severity | Count | Key theme |
|----------|-------|-----------|
| Critical | 5 | Auth/data isolation, PDF blocking, Kanban state |
| Moderate | 4 | Currency consistency, performance, state machines |
| Minor | 4 | Naming, decimal types, encoding |

**Top 3 to address first:**
1. Role isolation + Inertia prop scoping (auth phase)
2. `decimal(15,2)` for all monetary columns (schema design)
3. Invitation via `temporarySignedRoute()` (auth phase)
