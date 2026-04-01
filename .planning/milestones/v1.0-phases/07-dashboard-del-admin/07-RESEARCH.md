# Phase 7: Dashboard del Admin - Research

**Researched:** 2026-03-31
**Domain:** Laravel + Inertia.js + Vue 3 — Admin Dashboard (data aggregation, layout redesign, inline actions)
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

#### Rediseño del AdminLayout — Sidebar
- Cambiar `AdminLayout.vue` de navbar horizontal a **sidebar fijo a la izquierda**
- Sidebar fijo, siempre visible, ~220px de ancho, con íconos + texto en cada ítem
- Sin toggle/colapso en v1 — sidebar estático
- Afecta TODAS las páginas del admin (`Admin/Dashboard.vue`, `Admin/Clients/`, `Admin/Tasks/Index.vue`, `Admin/Billing/`, `Admin/Quotes/`, `Admin/Invitations/`)
- Items del sidebar: Dashboard / Clientes / Tareas / Facturación / Presupuestos / Invitar Cliente

#### Definición de "Próximas a vencer"
- Ventana de tiempo: **7 días** hacia adelante (incluyendo hoy)
- Estados incluidos: `backlog`, `en_progreso`, `en_revision` — **excluye `finalizado`**
- Tareas con `fecha_limite` NULL **no aparecen** en esta sección
- Una tarea que está `en_progreso` Y vence en 7 días aparece **solo en "Vencen pronto"** (sin duplicar en "En progreso")

#### Layout del Dashboard
- Dos secciones **verticales**: "En progreso" arriba → "Vencen pronto" abajo
- Cada sección usa **lista compacta** (no cards estilo Kanban, no tabla formal)
- Sin stats/métricas en header — solo las dos listas

#### Información por tarea (lista compacta)
- Cada fila muestra: **Título + Nombre de cliente + Badge de prioridad + Fecha límite**
- Badge de prioridad: rojo=alta, amarillo=media, verde=baja (consistente con Kanban Phase 3)
- En la sección "Vencen pronto", la fecha se colorea según urgencia:
  - Rojo: ≤1 día restante
  - Naranja: 2-3 días restantes
  - Amarillo: 4-7 días restantes
- Empty state por sección: mensaje simple sin acciones
  - "No hay tareas en progreso"
  - "Nada vence en los próximos 7 días"

#### Navegación y acciones inline
- Cada tarea en la lista es **clickeable** → navega a `/tasks?cliente={client_id}` (Kanban filtrado)
- Cada fila tiene un **dropdown inline de cambio de estado completo** (los 4 estados del enum)
- El dropdown reutiliza el endpoint `PUT /tasks/{id}/status` existente (Phase 3)
- La lista se actualiza automáticamente al cambiar estado (tarea desaparece si pasa a Finalizado o ya no cumple el criterio)

### Claude's Discretion
- Diseño visual exacto del sidebar (colores, active state, hover)
- Estética del dropdown de estado inline (trigger: badge clickeable, botón pequeño, etc.)
- Ancho máximo y padding del layout con sidebar
- Orden dentro de cada sección (recomendado: por fecha_limite ASC para "Vencen pronto", por created_at DESC para "En progreso")

### Deferred Ideas (OUT OF SCOPE)
- Sidebar colapsable (iconos/iconos+texto toggle) — v2
- Stats/métricas resumidas en el dashboard (N tareas activas, N vencen pronto) — v2
- Notificaciones visuales (badge en sidebar para tareas urgentes) — v2
- Filtros en el dashboard (por cliente, por prioridad) — v2
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| DASH-01 | La vista principal del admin muestra las tareas activas (en progreso) y las próximas a vencer | DashboardController with two eager-loaded queries + `with('client')` + date window logic; AdminLayout sidebar redesign propagates to all admin pages via `defineOptions({ layout: AdminLayout })` |
</phase_requirements>

---

## Summary

Phase 7 has two distinct deliverables: (1) redesigning `AdminLayout.vue` from horizontal nav to a fixed left sidebar, and (2) implementing `Admin/Dashboard.vue` as a real data-driven page with two task lists. Both are well-scoped, with all data models, enums, routes, and reusable endpoints already in place from Phases 1–6.

The only net-new backend work is a `DashboardController` with an `index()` method that runs two queries: tasks in `en_progreso` excluding those that also qualify for "vencen pronto", and tasks vencen pronto (non-finalizado, non-null `fecha_limite`, within today..today+7 days). Both queries must use `with('client')` to avoid N+1. The existing `PUT /tasks/{id}/status` endpoint handles status changes from the inline dropdown with zero new backend code.

The frontend sidebar redesign is a structural change to a single file (`AdminLayout.vue`) that propagates automatically to all admin pages because every page already uses `defineOptions({ layout: AdminLayout })`. No per-page changes required for the sidebar. The dashboard page itself replaces the current placeholder with two section components, compact task rows, urgency date coloring, and an inline `<select>` for status changes.

**Primary recommendation:** Split into two plans — Plan 01: DashboardController + AdminLayout sidebar; Plan 02: Dashboard.vue UI. The sidebar redesign belongs in Plan 01 because it is a structural dependency for any visual verification.

---

## Standard Stack

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 12.x (installed) | DashboardController, query logic, routing | Project stack — non-negotiable |
| Inertia.js (Laravel) | 2.x | Server → Vue prop passing | Project stack — non-negotiable |
| Vue 3 | 3.x | Dashboard.vue + AdminLayout.vue | Project stack — non-negotiable |
| Tailwind CSS | 3.x | All styling — no component library | Established project pattern |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Carbon (via Laravel) | bundled | Date arithmetic for `today()->addDays(7)` | In DashboardController queries |
| Inertia `<Link>` | bundled | Sidebar nav links with SPA routing | Sidebar nav items |
| Inertia `router` | bundled | `router.put()` for status update, `router.get()` for task row click | Dashboard.vue interactions |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Native `<select>` for status dropdown | Custom dropdown component | Native `<select>` is already decided (CONTEXT.md "sin librerías adicionales") — simpler, zero dependencies |
| Two separate queries (controller) | Single query + PHP grouping | Two queries are cleaner and more explicit; PHP grouping would require in-memory filtering similar to TaskController — acceptable either way, but explicit queries are easier to understand |

**Installation:** No new packages — all dependencies are already in the project.

---

## Architecture Patterns

### Recommended Project Structure

No new directories needed. Files to create/modify:

```
app/Http/Controllers/
└── DashboardController.php        (new)

resources/js/
├── Layouts/
│   └── AdminLayout.vue            (modify — sidebar redesign)
└── Pages/Admin/
    └── Dashboard.vue              (modify — full implementation)

routes/web.php                     (modify — replace closure with DashboardController)

tests/Feature/Dashboard/
└── DashboardTest.php              (new)
```

### Pattern 1: Two-Query Dashboard Controller

**What:** DashboardController runs two explicit Eloquent queries and passes both as Inertia props. Priority exclusion rule (en_progreso tasks that qualify for "vencen pronto" appear ONLY in "vencen pronto") is implemented at query level, not PHP filtering.

**When to use:** Any time dashboard requires two non-overlapping data sets with a priority rule.

**Example:**
```php
// Source: established project pattern from BillingController + TaskController
public function index()
{
    $ventana = [today(), today()->addDays(7)];

    $vencenProonto = Task::with('client')
        ->whereNotIn('estado', [TaskStatus::Finalizado->value])
        ->whereNotNull('fecha_limite')
        ->whereBetween('fecha_limite', $ventana)
        ->orderBy('fecha_limite', 'asc')
        ->get();

    $vencenProntoIds = $vencenProonto->pluck('id');

    $enProgreso = Task::with('client')
        ->where('estado', TaskStatus::EnProgreso)
        ->whereNotIn('id', $vencenProntoIds)
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Admin/Dashboard', [
        'enProgreso'   => $enProgreso,
        'vencenProonto' => $vencenProonto,
    ]);
}
```

**Key:** `with('client')` on BOTH queries is mandatory — each task row displays `task.client.nombre`.

### Pattern 2: AdminLayout Sidebar Redesign

**What:** Replace `<nav>` (horizontal) with `<aside>` (fixed left, 220px). The outer wrapper gains `flex` and the `<main>` gains `ml-[220px]`. No other admin pages need to change — the layout propagates via `defineOptions({ layout: AdminLayout })`.

**When to use:** Layout file is the single source of truth for all admin page shells.

**Active state detection:** Reuse existing `$page.url.startsWith(path)` pattern already in `AdminLayout.vue`. For sidebar, apply `bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold` to the active item.

**Example (structure):**
```html
<!-- Source: 07-UI-SPEC.md Layout Contract -->
<div class="min-h-screen bg-gray-100 flex">
  <aside class="w-[220px] bg-white border-r border-gray-200 fixed inset-y-0 left-0 flex flex-col">
    <!-- Logo, nav items, logout -->
  </aside>
  <div class="ml-[220px] flex-1 flex flex-col">
    <main class="p-6 flex-1">
      <slot />
    </main>
  </div>
</div>
```

### Pattern 3: Inline Status Dropdown with Reactive Disappearance

**What:** A native `<select>` triggers `router.put()` with `preserveState: true, preserveScroll: true`. Because Inertia re-renders props from the server response, any task that no longer qualifies (estado changed to `finalizado`, or transitions out of `en_progreso` into "vencen pronto") simply disappears from the list — no client-side filtering needed.

**When to use:** Any Inertia page where a list item can remove itself from the list via a status change.

**Example:**
```javascript
// Source: existing router.put pattern from Tasks/Index.vue
function updateStatus(taskId, newStatus) {
    router.put(route('tasks.updateStatus', taskId), { estado: newStatus }, {
        preserveState: true,
        preserveScroll: true,
    })
}
```

Stop propagation is required on the `<select>` `@change` to prevent the row click handler from firing simultaneously.

### Pattern 4: Task Row Click Navigation

**What:** Row click navigates to Kanban filtered by client. The `<select>` must stop event propagation. The entire `<div>` row gets `@click` but the dropdown gets `@change.stop`.

**Example:**
```javascript
// Source: established router.get pattern from Tasks/Index.vue + CONTEXT.md
function goToClient(clientId) {
    router.get('/tasks', { cliente: clientId }, { preserveState: true })
}
```

### Anti-Patterns to Avoid

- **Loading both sections with a single query then filtering in PHP**: Works but couples the "vencen pronto" exclusion logic to PHP array manipulation. Two explicit DB queries are clearer and match the established pattern in this codebase.
- **Not using `with('client')`**: Every task row renders the client name. Without eager loading, 20 tasks = 20 extra queries — the success criterion explicitly calls out "sin errores N+1 perceptibles".
- **Modifying per-page Vue files for the sidebar**: The sidebar redesign only touches `AdminLayout.vue`. Do NOT touch `Dashboard.vue`, `Tasks/Index.vue`, `Clients/*.vue`, etc. for the layout change.
- **Using `whereBetween` with Carbon objects directly**: Pass string dates or use `->toDateString()` to avoid timezone edge cases with date-only comparisons. `today()` returns a Carbon instance; `today()->addDays(7)` also works with `whereBetween` in Laravel but should be tested explicitly.
- **Duplicating tasks across sections**: The priority rule is "appears only in vencen pronto" — implement via `whereNotIn('id', $vencenProntoIds)` on the `enProgreso` query, not via PHP `reject()`.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Date window query | Manual date string construction | Laravel `today()->addDays(7)` with `whereBetween` | Carbon handles month-end, leap year edge cases |
| Active nav state | Custom URL matching logic | `$page.url.startsWith(path)` — already established | Pattern already in `AdminLayout.vue`, consistent |
| Status update | New endpoint | `PUT /tasks/{id}/status` — already exists (Phase 3) | Reuse; no new backend code needed for dropdown |
| Inertia re-render after status change | Manual Vue reactivity / `v-model` array splice | `preserveState: true` on `router.put()` — Inertia re-renders props | Server is source of truth; client-side splicing creates inconsistency |

**Key insight:** Inertia's prop re-render after mutation is the correct reactivity model for this stack — don't fight it with client-side state management.

---

## Common Pitfalls

### Pitfall 1: N+1 on Client Name
**What goes wrong:** `$task->client->nombre` inside a loop fires one query per task.
**Why it happens:** Forgetting `with('client')` on the query.
**How to avoid:** Both Eloquent queries in `DashboardController` must chain `->with('client')`.
**Warning signs:** Laravel Debugbar or logs show repeated `SELECT * FROM clients WHERE id = ?` queries.

### Pitfall 2: Task Duplication Between Sections
**What goes wrong:** A task in `en_progreso` with a nearby deadline appears in both sections.
**Why it happens:** Building the two lists independently without the exclusion rule.
**How to avoid:** Build the `$vencenProonto` collection first, pluck its IDs, then apply `whereNotIn('id', ...)` to the `$enProgreso` query. The CONTEXT.md locked decision is explicit: "solo en Vencen pronto".
**Warning signs:** A test that creates a task in `en_progreso` with `fecha_limite = today()` and asserts it appears in only one section fails.

### Pitfall 3: `ml-[220px]` Missing on Main Content Area
**What goes wrong:** Content slides under the fixed sidebar after the layout redesign.
**Why it happens:** `<aside>` is `position: fixed` — it's removed from normal document flow. The sibling element must compensate with a left margin equal to the sidebar width.
**How to avoid:** The `<div class="ml-[220px] flex-1 flex flex-col">` wrapper is mandatory. Tailwind's `ml-[220px]` uses an arbitrary value — this is correct; there's no standard `ml-220` utility.
**Warning signs:** All admin pages show content starting from left edge, obscured by sidebar.

### Pitfall 4: Row Click Fires on Status Change
**What goes wrong:** Selecting a new status in the dropdown also triggers the row's click handler, navigating away before the status update completes.
**Why it happens:** `@change` on `<select>` bubbles up to the parent `<div>`'s `@click`.
**How to avoid:** Use `@change.stop` on the `<select>` element to stop event propagation. The `@click` handler on the row div should also check the event target to avoid edge cases.
**Warning signs:** Status change immediately navigates to `/tasks?cliente=X` instead of staying on the dashboard.

### Pitfall 5: `whereBetween` Timezone Mismatch
**What goes wrong:** `today()` uses the application's timezone; if the server timezone differs from the database, date comparisons can be off by a day.
**Why it happens:** `today()` in Laravel respects `config('app.timezone')` but MySQL DATE columns store without timezone.
**How to avoid:** For a development/freelance tool, this is acceptable risk. Use `->toDateString()` explicitly if the project timezone is non-UTC: `today()->toDateString()`. Verify app timezone in `config/app.php`.
**Warning signs:** Tasks due "today" appear or disappear based on the time of day rather than the date.

---

## Code Examples

Verified patterns from the existing codebase:

### Existing `updateStatus` endpoint (Phase 3 — reuse as-is)
```php
// Source: app/Http/Controllers/TaskController.php (line 60–69)
public function updateStatus(Request $request, Task $task)
{
    $request->validate([
        'estado' => ['required', 'in:backlog,en_progreso,en_revision,finalizado'],
    ]);

    $task->update(['estado' => $request->estado]);

    return back();
}
```
Route name: `tasks.updateStatus`. No changes needed to this endpoint.

### Existing Inertia router patterns (established in project)
```javascript
// Row click — navigate to Kanban filtered by client
// Source: CONTEXT.md + TaskController pattern
router.get('/tasks', { cliente: task.client_id }, { preserveState: true })

// Status change dropdown
// Source: Tasks/Index.vue router.put pattern
router.put(route('tasks.updateStatus', task.id), { estado: newValue }, {
    preserveState: true,
    preserveScroll: true,
})
```

### Priority badge classes (Phase 3 — reuse verbatim)
```javascript
// Source: Admin/Tasks/Index.vue prioridadBadgeClass function (established pattern)
const prioridadBadgeClass = {
    alta:  'bg-red-100 text-red-800',
    media: 'bg-yellow-100 text-yellow-800',
    baja:  'bg-green-100 text-green-800',
}
```

### Urgency date color (new for Phase 7 — from UI-SPEC)
```javascript
// Source: 07-UI-SPEC.md Color section
function urgencyClass(fechaLimite) {
    const days = Math.ceil((new Date(fechaLimite) - new Date()) / (1000 * 60 * 60 * 24))
    if (days <= 1)  return 'text-red-600 font-semibold'
    if (days <= 3)  return 'text-orange-500 font-medium'
    if (days <= 7)  return 'text-yellow-600 font-medium'
    return 'text-gray-400'
}
```

### Sidebar active state (from UI-SPEC + existing AdminLayout pattern)
```html
<!-- Source: 07-UI-SPEC.md + AdminLayout.vue existing $page.url.startsWith pattern -->
<Link
    href="/dashboard"
    class="flex items-center gap-3 px-4 py-3 text-sm transition"
    :class="$page.url.startsWith('/dashboard')
        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
>
    Dashboard
</Link>
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Placeholder Dashboard.vue ("Bienvenido") | Real data-driven page with two task lists | Phase 7 | Fulfills DASH-01 |
| Horizontal `<nav>` in AdminLayout | Fixed left sidebar | Phase 7 | Better navigation affordance, more horizontal space |
| Dashboard route as closure | DashboardController | Phase 7 | Enables testing, cleaner separation |

---

## Open Questions

1. **`today()` timezone behavior in test environment**
   - What we know: `phpunit.xml` sets `DB_CONNECTION=sqlite` (in-memory). Carbon `today()` uses `config('app.timezone')`.
   - What's unclear: Project timezone — need to verify `config/app.php` `timezone` key. If it's `UTC`, there's no issue for Argentine developer context.
   - Recommendation: Tests for "vencen pronto" should use `Carbon::setTestNow()` or `Travel::to()` to freeze time, avoiding flaky date-boundary failures regardless of timezone.

2. **`whereNotIn` with empty collection**
   - What we know: If `$vencenProntoIds` is empty, `whereNotIn('id', [])` is a no-op — Laravel handles this correctly.
   - What's unclear: Nothing — this is safe.
   - Recommendation: No action needed; document in test cases for the empty-state path.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | PHPUnit (Laravel Feature Tests) |
| Config file | `phpunit.xml` |
| Quick run command | `php artisan test --filter DashboardTest` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| DASH-01 | Dashboard shows `enProgreso` tasks as Inertia prop | Feature | `php artisan test --filter DashboardTest::test_dashboard_shows_en_progreso_tasks` | ❌ Wave 0 |
| DASH-01 | Dashboard shows `vencenProonto` tasks as Inertia prop | Feature | `php artisan test --filter DashboardTest::test_dashboard_shows_vencen_pronto_tasks` | ❌ Wave 0 |
| DASH-01 | En progreso task that also qualifies for vencen pronto appears ONLY in vencen pronto | Feature | `php artisan test --filter DashboardTest::test_en_progreso_task_due_soon_appears_only_in_vencen_pronto` | ❌ Wave 0 |
| DASH-01 | Tasks with NULL fecha_limite excluded from vencen pronto | Feature | `php artisan test --filter DashboardTest::test_tasks_with_null_deadline_excluded_from_vencen_pronto` | ❌ Wave 0 |
| DASH-01 | Finalizado tasks excluded from both sections | Feature | `php artisan test --filter DashboardTest::test_finalizado_tasks_excluded_from_dashboard` | ❌ Wave 0 |
| DASH-01 | Dashboard eager loads client (N+1 check) | Feature | `php artisan test --filter DashboardTest::test_dashboard_eager_loads_client` | ❌ Wave 0 |
| DASH-01 | Dashboard requires admin auth | Feature | `php artisan test --filter DashboardTest::test_dashboard_requires_admin` | ❌ Wave 0 |
| DASH-01 | PUT /tasks/{id}/status reused for inline dropdown | Feature | Already covered by `TaskKanbanTest::test_update_status_changes_task_estado` | ✅ existing |

### Sampling Rate
- **Per task commit:** `php artisan test --filter DashboardTest`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Dashboard/DashboardTest.php` — covers all DASH-01 behaviors above
- [ ] No new fixtures needed — `User::factory()` with `Role::Admin`, `Client::factory()`, `Task::factory()` already exist

---

## Sources

### Primary (HIGH confidence)
- Direct code inspection: `app/Http/Controllers/TaskController.php` — updateStatus endpoint, query patterns
- Direct code inspection: `app/Enums/TaskStatus.php`, `TaskPriority.php` — enum values verified
- Direct code inspection: `app/Models/Task.php` — field names, casts, relationships
- Direct code inspection: `resources/js/Layouts/AdminLayout.vue` — current structure, active state pattern
- Direct code inspection: `resources/js/Pages/Admin/Dashboard.vue` — current placeholder
- Direct code inspection: `routes/web.php` — current route structure, named routes
- Direct code inspection: `phpunit.xml` — test runner config, sqlite in-memory
- `.planning/phases/07-dashboard-del-admin/07-CONTEXT.md` — locked decisions
- `.planning/phases/07-dashboard-del-admin/07-UI-SPEC.md` — visual contract
- Existing test files (`BillingDashboardTest.php`, `TaskKanbanTest.php`) — testing patterns

### Secondary (MEDIUM confidence)
- `.planning/STATE.md` — accumulated decisions from Phases 1–6 (confirmed enums, patterns, FK decisions)

### Tertiary (LOW confidence)
- None — all findings are based on direct code inspection of the project.

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — all packages verified via direct codebase inspection
- Architecture: HIGH — patterns extracted directly from existing controllers and tests in the project
- Pitfalls: HIGH — derived from project-specific STATE.md decisions and direct code inspection of existing patterns
- UI/Styling: HIGH — verified against 07-UI-SPEC.md which was generated from actual codebase inspection

**Research date:** 2026-03-31
**Valid until:** Stable — no external dependencies introduced in this phase; all libraries are already installed and in use.
