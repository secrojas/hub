# Project Research Summary

**Project:** Hub — Plataforma de gestión de clientes freelance
**Domain:** Freelance client management platform (CRM + Tasks + Billing + Quotes)
**Researched:** 2026-03-19
**Confidence:** HIGH

## Executive Summary

Hub is a single-admin freelance management tool combining CRM, task management (Kanban), invoicing, and quote generation — all behind a two-role access model (admin + client portal). The established approach for this stack (Laravel 11 + Inertia.js + Vue 3) is a unified monolith with no REST API: Laravel owns routing and data, Inertia bridges to Vue as a server-driven SPA. This eliminates token auth complexity, keeps the codebase in one place, and fits the development environment constraints (Laragon on Windows).

The recommended build strategy starts with foundations (auth, roles, invitation flow, client CRUD) before layering value-adding modules (Kanban, billing, quotes). The client portal is the last module because it depends on every other module being operational. The feature scope is deliberately narrow for v1 — no payment gateways, no real-time features, no AFIP integration — which is the correct call given the solo-freelance use case.

The critical risks center on three areas: data isolation between roles (client portal must never expose admin-only data), PDF generation blocking the web process (DomPDF must use inlined styles and optionally queue for complex layouts), and Kanban state divergence when drag-and-drop updates fail silently. All three are addressable with standard Laravel and Vue patterns if they are designed in from the beginning rather than retrofitted.

---

## Key Findings

### Recommended Stack

The stack is non-negotiable per project constraints: Laravel 11, Inertia.js v2, Vue 3 (Composition API with `<script setup>`), MySQL 8.0+, Vite 5. Within those constraints, the scaffolding choice is Laravel Breeze (Vue + Inertia preset) — not Jetstream — because Breeze installs Inertia + Vue 3 + Tailwind automatically without the teams/Livewire overhead that Jetstream adds.

PDF generation uses `barryvdh/laravel-dompdf` exclusively because it is pure PHP with no system binary dependency, which is essential for Laragon (Windows) to Linux production parity. Snappy and Browsershot require `wkhtmltopdf` and are excluded. For drag-and-drop Kanban, `vuedraggable` (vue.draggable.next) wraps SortableJS and handles cross-column drag natively, though its maintenance history warrants a version check before use.

**Core technologies:**
- Laravel 11: backend framework — current LTS, owns all routing and business logic
- Inertia.js v2: rendering bridge — server-driven SPA without REST API overhead
- Vue 3 (Composition API): frontend rendering — `<script setup>` throughout
- MySQL 8.0+: database — `decimal(15,2)` required for all ARS monetary columns
- Laravel Breeze: auth scaffold — installs the full Inertia + Vue 3 + Tailwind stack
- barryvdh/laravel-dompdf ^3.0: PDF generation — pure PHP, cross-environment safe
- Tailwind CSS v3: UI styling — installed by Breeze (NOT v4, still unstable)
- Pinia: global state management — auth user, notifications
- Inertia `useForm()`: all form handling — replaces VeeValidate

### Expected Features

The feature set is clearly scoped and dependency-ordered. Authentication gates everything; client CRUD is the foreign key for all other data; tasks and Kanban are the core value proposition; billing and quotes extend it; the portal and admin dashboard are aggregation layers that only add value once all modules have real data.

**Must have (table stakes):**
- Auth: email/password login, persistent sessions, two-role system (admin/client)
- Client invitation via email with temporary signed link (no public registration)
- Client CRUD with status (activo/potencial/pausado), internal notes, tech stack field
- Tasks: create/edit linked to client, four statuses (Backlog / En progreso / En revisión / Finalizado)
- Kanban board per client with drag-and-drop column management
- Invoice registration (manual), three statuses, ARS amounts, monthly dashboard summary
- Quote builder with line items, four statuses, PDF generation on send
- Client portal: read-only views of their tasks, quotes, and billing status

**Should have (differentiators for this project):**
- Global Kanban across all clients in a single board view
- ARS currency formatting unified via `useCurrency()` composable
- Dashboard centered on active tasks, not financials
- Internal notes strictly hidden from client portal

**Defer to v2+:**
- Automatic client notifications on state changes
- Advanced reporting / analytics
- Multiple admins / team mode
- Payment gateway integration (MercadoPago, Stripe)
- AFIP / electronic invoice integration
- Mobile app / PWA
- Time tracking
- Chat / real-time messaging

### Architecture Approach

The architecture is a Laravel monolith with two clearly separated route namespaces (`Admin/` and `Portal/`) and two corresponding Vue layout shells (`AdminLayout.vue`, `PortalLayout.vue`). All mutations follow the Inertia form pattern: `useForm().post/patch()` → Laravel controller → Form Request validation → Service layer → Model save → redirect with flash. There is no client-side data fetching — all data flows from server-rendered Inertia props. Policies guard every resource for ownership; middleware enforces role separation at the routing level.

**Major components:**
1. Auth system (Breeze + middleware + invitation flow) — gates access to everything
2. Admin controllers (`Admin/ClientController`, `TaskController`, `InvoiceController`, `QuoteController`, `DashboardController`) — business logic delegated to Services
3. Portal controller (`Portal/PortalController`) — scoped read-only views for client role
4. PdfService — DomPDF wrapper, called from QuoteController on status transition to "Enviado"
5. Vue pages under `Pages/Admin/` and `Pages/Portal/` — role-segregated, never shared
6. Components under `Components/Admin/` and `Components/Portal/` — no cross-role component sharing

### Critical Pitfalls

1. **Role bypass / data leak between clients** — use Laravel Policies on every resource; always scope portal queries to `auth()->user()->client_id`; never trust route parameters alone. Applies from Phase 1.
2. **Inertia props leaking sensitive data to client pages** — use API Resources or explicit `only()` selectors; never pass raw Eloquent models (which carry `internal_notes`, billing totals) to portal pages.
3. **PDF generation blocking the web process** — DomPDF with inlined styles only; queue generation for complex layouts; test with realistic ARS amounts and Spanish characters (`ñ`, accents) from day one.
4. **Kanban state diverging from DB on API failure** — implement optimistic UI with explicit rollback; show toast on error; never let silent failures leave the board in a stale state.
5. **Invitation system built with custom tokens** — use `URL::temporarySignedRoute()` exclusively; it handles expiry, signature validation, and revocation without custom code.

---

## Implications for Roadmap

All four research files converge on the same build order. The dependency tree is unambiguous: auth before clients, clients before tasks/invoices/quotes, all modules before portal and dashboard.

### Phase 1: Foundation + Auth + Roles
**Rationale:** Every subsequent module depends on auth and the client model. Pitfalls #1, #3 (decimal types), and #5 must be addressed here before any feature work begins.
**Delivers:** Working Laravel + Inertia + Vue 3 wiring; Breeze auth; two-role middleware; client invitation via signed routes; `AdminLayout` and `PortalLayout` shells.
**Addresses (from FEATURES.md):** Auth, sessions, two roles, email invitation.
**Avoids (from PITFALLS.md):** Role bypass (Policies from day one), custom invitation tokens, `float` monetary columns (use `decimal(15,2)` in first migrations), Inertia shared data over-broadcasting.
**Research flag:** Standard patterns — skip deeper research phase.

### Phase 2: Client CRM
**Rationale:** Client is the foreign key for tasks, invoices, and quotes. Cannot build any other module without it.
**Delivers:** Full client CRUD (name, company, email, phone, status, tech stack, internal notes); client list and detail views in admin.
**Addresses (from FEATURES.md):** Client CRUD, status field, internal notes, tech stack field (differentiator).
**Avoids (from PITFALLS.md):** Component naming collisions — use `Admin/` namespace from the start.
**Research flag:** Standard patterns — skip deeper research phase.

### Phase 3: Tasks + Kanban
**Rationale:** This is the core value of the product. Per FEATURES.md, the primary user need is "ver qué tareas están activas."
**Delivers:** Task CRUD linked to client; per-client Kanban board with drag-and-drop (vuedraggable); global Kanban across all clients.
**Addresses (from FEATURES.md):** Task creation/editing, Kanban board per client, four task statuses, global Kanban view (differentiator).
**Avoids (from PITFALLS.md):** Kanban state divergence (optimistic UI + rollback), N+1 on global Kanban (eager load `with(['client'])`, index on `status` + `client_id`, exclude "Finalizado" by default).
**Research flag:** Verify `vuedraggable` (vue.draggable.next) latest release and maintenance status before starting. May need a brief research pass.

### Phase 4: Invoicing + Billing Dashboard
**Rationale:** Revenue visibility is the second most important operational need after task tracking.
**Delivers:** Manual invoice registration; three statuses (pendiente/pagado/vencido); monthly summary; total debt view.
**Addresses (from FEATURES.md):** Invoice registration, invoice statuses, ARS amounts, billing dashboard.
**Avoids (from PITFALLS.md):** ARS formatting inconsistency (build `useCurrency()` composable here, used by all subsequent modules), soft-delete scoping on dashboard queries.
**Research flag:** Standard patterns — skip deeper research phase.

### Phase 5: Quotes + PDF Generation
**Rationale:** Presupuestos depend on client (Phase 2) and billing concepts (Phase 4). PDF is the most technically risky feature and should be implemented with real data available.
**Delivers:** Quote builder with line items; four statuses with server-side state machine guards; PDF generation on "Enviado" transition; signed URL download.
**Addresses (from FEATURES.md):** Quote builder, quote statuses, PDF generation on send.
**Avoids (from PITFALLS.md):** PDF blocking web process (inlined styles, consider queuing), Spanish character encoding in DomPDF (configure UTF-8 + `<meta charset="utf-8">` immediately), illegal status transitions (implement transition map server-side).
**Research flag:** DomPDF queue integration is moderately complex — consider a focused research pass if queuing is chosen over synchronous generation.

### Phase 6: Client Portal
**Rationale:** The portal is a read-only aggregation layer. It cannot be built until tasks, quotes, and invoices exist with real data. This is the last new module.
**Delivers:** `PortalLayout` views for tasks (read-only list), quotes (read-only), invoices (read-only); scoped to the authenticated client's data.
**Addresses (from FEATURES.md):** Client portal — tasks view, quotes view, billing status view.
**Avoids (from PITFALLS.md):** Inertia props leaking admin data to portal (use API Resources with explicit field selection), portal queries not scoped to `client_id` (enforce at every query in `PortalController`).
**Research flag:** Standard patterns — skip deeper research phase. Role-scoping pattern is already established in Phase 1.

### Phase 7: Admin Dashboard
**Rationale:** The dashboard aggregates data from all modules. Per FEATURES.md, it delivers maximum value only when real data exists across all modules. Building it last avoids placeholder views.
**Delivers:** Aggregated admin view — active tasks summary, billing totals, recent client activity, outstanding quotes.
**Addresses (from FEATURES.md):** Admin dashboard (depends on all modules).
**Avoids (from PITFALLS.md):** Soft-delete scoping errors, N+1 on aggregation queries (eager load and cache selectively).
**Research flag:** Standard patterns — skip deeper research phase.

### Phase Ordering Rationale

- Auth before everything: Laravel Policies, middleware, and invitation flow must be in place before any resource is created. Retrofitting auth scoping is expensive.
- Client before tasks/invoices/quotes: All three modules have `client_id` as a required foreign key. The dependency is hard.
- Tasks before portal: Client portal's task view requires the task model and statuses to exist.
- PDF in Phase 5 (not later): PDF is the highest-risk technical feature. Building it in Phase 5 gives time to discover DomPDF edge cases (encoding, layout timeouts) before the project is "done."
- Dashboard last: Deliberately last because it is most valuable with real data and least valuable as a placeholder.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 3 (Tasks + Kanban):** Verify `vuedraggable` (vue.draggable.next) is actively maintained and compatible with Vue 3 + Inertia v2. If unmaintained, evaluate `@dnd-kit/core` (Vue port) or `Shopify/draggable` as alternatives.
- **Phase 5 (Quotes + PDF):** If PDF generation will be queued (recommended for production), verify Laravel Queue + DomPDF integration and signed URL serving from storage. This is a moderate integration with multiple moving parts.

Phases with standard patterns (skip research-phase):
- **Phase 1:** Laravel Breeze + Inertia preset is a single `artisan` command. Invitation via `temporarySignedRoute` is documented.
- **Phase 2:** Standard CRUD with Form Requests — no novel patterns.
- **Phase 4:** Manual invoice registration is straightforward CRUD with status enum.
- **Phase 6:** Portal is scoped CRUD reads — the scoping pattern is established in Phase 1.
- **Phase 7:** Dashboard is aggregation queries — standard Eloquent with eager loading.

---

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Technologies are project-defined and non-negotiable. Package recommendations (Breeze, dompdf, vuedraggable) are well-established for this combination. Only uncertainty is vuedraggable maintenance status. |
| Features | HIGH | Feature scope is clear and explicitly bounded. Dependency tree is unambiguous. Anti-features are explicitly reasoned, not assumed. |
| Architecture | MEDIUM | Monolith + Inertia patterns are well-established. Inertia v2 `share()` API may have changed from v1 — verify against current docs before implementation. Policy + Portal scoping patterns are standard Laravel. |
| Pitfalls | HIGH | All 13 pitfalls are concrete, actionable, and grounded in the specific stack. Not theoretical. Top 5 (critical) all have clear prevention strategies that fit the chosen architecture. |

**Overall confidence:** HIGH

### Gaps to Address

- **vuedraggable maintenance status:** Before Phase 3 begins, confirm the latest release of `vue.draggable.next` is compatible with Vue 3.x and Inertia.js v2. If maintenance is stale, evaluate `@formkit/drag-and-drop` or a lightweight SortableJS wrapper as alternatives.
- **Inertia.js v2 `share()` API:** The architecture assumes `HandleInertiaRequests::share()` supports conditional sharing by role. Verify the exact API signature against Inertia v2 docs before the auth phase implementation.
- **DomPDF queue vs synchronous:** The research recommends queuing PDF generation for production but does not prescribe a specific queue driver or job structure. This decision (sync for simplicity vs. async for reliability) should be made in Phase 5 planning based on expected quote complexity.
- **ARS large amounts in MySQL decimal(15,2):** Confirm that `decimal(15,2)` accommodates the highest expected ARS amounts (multi-million peso invoices). 15 digits total, 2 decimal = max ~$999,999,999,999.99 ARS — sufficient for foreseeable use.

---

## Sources

### Primary (HIGH confidence)
- STACK.md — stack constraints, package rationale, exclusion decisions
- FEATURES.md — feature table stakes, differentiators, dependency tree, anti-features
- ARCHITECTURE.md — component boundaries, data flow patterns, build order
- PITFALLS.md — 13 concrete pitfalls with prevention strategies and phase assignments

### Secondary (MEDIUM confidence)
- `vuedraggable` (vue.draggable.next) — maintenance history flagged; verify before use
- Inertia.js v2 `share()` API — patterns established in ARCHITECTURE.md; verify against current docs

### Tertiary (LOW confidence)
- DomPDF queue integration specifics — general pattern known, implementation details need validation in Phase 5

---

*Research completed: 2026-03-19*
*Ready for roadmap: yes*
