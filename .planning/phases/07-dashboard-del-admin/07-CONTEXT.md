# Phase 7: Dashboard del Admin - Context

**Gathered:** 2026-03-31
**Status:** Ready for planning

<domain>
## Phase Boundary

Vista central del admin en `/dashboard` — muestra de un vistazo qué tareas están activas (en progreso) y cuáles vencen pronto. Como parte de esta fase también se rediseña `AdminLayout.vue` para usar sidebar en lugar de navbar horizontal, aplicando a todas las páginas del admin. No incluye nueva funcionalidad de facturación, presupuestos ni clientes — solo mejora de la vista principal y el layout global.

</domain>

<decisions>
## Implementation Decisions

### Rediseño del AdminLayout — Sidebar
- Cambiar `AdminLayout.vue` de navbar horizontal a **sidebar fijo a la izquierda**
- Sidebar fijo, siempre visible, ~220px de ancho, con íconos + texto en cada ítem
- Sin toggle/colapso en v1 — sidebar estático
- Afecta TODAS las páginas del admin (`Admin/Dashboard.vue`, `Admin/Clients/`, `Admin/Tasks/Index.vue`, `Admin/Billing/`, `Admin/Quotes/`, `Admin/Invitations/`)
- Items del sidebar: Dashboard / Clientes / Tareas / Facturación / Presupuestos / Invitar Cliente

### Definición de "Próximas a vencer"
- Ventana de tiempo: **7 días** hacia adelante (incluyendo hoy)
- Estados incluidos: `backlog`, `en_progreso`, `en_revision` — **excluye `finalizado`**
- Tareas con `fecha_limite` NULL **no aparecen** en esta sección
- Una tarea que está `en_progreso` Y vence en 7 días aparece **solo en "Vencen pronto"** (sin duplicar en la sección "En progreso")

### Layout del Dashboard
- Dos secciones **verticales**: "En progreso" arriba → "Vencen pronto" abajo
- Cada sección usa **lista compacta** (no cards estilo Kanban, no tabla formal)
- Sin stats/métricas en header — solo las dos listas

### Información por tarea (lista compacta)
- Cada fila muestra: **Título + Nombre de cliente + Badge de prioridad + Fecha límite**
- Badge de prioridad: rojo=alta, amarillo=media, verde=baja (consistente con Kanban Phase 3)
- En la sección "Vencen pronto", la fecha se colorea según urgencia:
  - Rojo: ≤1 día restante
  - Naranja: 2-3 días restantes
  - Amarillo: 4-7 días restantes
- Empty state por sección: mensaje simple sin acciones
  - "No hay tareas en progreso"
  - "Nada vence en los próximos 7 días"

### Navegación y acciones inline
- Cada tarea en la lista es **clickeable** → navega a `/tasks?cliente={client_id}` (Kanban filtrado)
- Cada fila tiene un **dropdown inline de cambio de estado completo** (los 4 estados del enum)
- El dropdown reutiliza el endpoint `PUT /tasks/{id}/status` existente (Phase 3)
- La lista se actualiza automáticamente al cambiar estado (tarea desaparece si pasa a Finalizado o ya no cumple el criterio)

### Claude's Discretion
- Diseño visual exacto del sidebar (colores, active state, hover)
- Estética del dropdown de estado inline (trigger: badge clickeable, botón pequeño, etc.)
- Ancho máximo y padding del layout con sidebar
- Orden dentro de cada sección (recomendado: por fecha_limite ASC para "Vencen pronto", por created_at DESC para "En progreso")

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Requirements
- `.planning/REQUIREMENTS.md` §Dashboard del Admin — DASH-01
- `.planning/ROADMAP.md` §Phase 7 — Success criteria (3 criterios)

### Contexto de fases previas
- `.planning/phases/03-tareas-y-kanban/03-CONTEXT.md` — TaskStatus enum values, TaskPriority enum + colores de badge, endpoint `PUT /tasks/{id}/status`, estructura de Task model
- `.planning/phases/01-fundaci-n-y-auth/01-CONTEXT.md` — Middlewares `EnsureIsAdmin`, routing `/dashboard` para admin

No hay ADRs externos — los requisitos están completamente capturados en las decisiones arriba.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `resources/js/Layouts/AdminLayout.vue` — Layout a rediseñar con sidebar. Actualmente top nav horizontal; mantiene el mismo slot `<main>` pero con layout de dos columnas (sidebar + contenido)
- `resources/js/Pages/Admin/Dashboard.vue` — Placeholder actual, solo dice "Bienvenido". Esta es la página a implementar completamente
- `app/Enums/TaskStatus.php` — `Backlog`, `EnProgreso`, `EnRevision`, `Finalizado` — usar para las queries del dashboard
- `app/Enums/TaskPriority.php` — `Baja`, `Media`, `Alta` — para badges en la lista
- `app/Models/Task.php` — `estado` cast a `TaskStatus`, `fecha_limite` cast a `date`, `client()` belongsTo
- `PUT /tasks/{id}/status` endpoint — ya existente de Phase 3, el dropdown de estado inline lo reutiliza

### Established Patterns
- `defineOptions({ layout: AdminLayout })` en cada página del admin — el cambio de AdminLayout.vue se propaga automáticamente
- Filtros via query params: `router.get('/tasks', { cliente: id }, { preserveState: true })` — patrón establecido
- `router.put(route('tasks.updateStatus', id), { estado: nuevoEstado })` — patrón de actualización sin form
- Props Inertia desde controller: el `DashboardController` (a crear) pasa las dos listas como props

### Integration Points
- `DashboardController` (nuevo) → `resources/js/Pages/Admin/Dashboard.vue`
- Ruta `/dashboard` en `routes/web.php` actualmente apunta al placeholder — actualizar al nuevo controller
- `Task::where('estado', TaskStatus::EnProgreso)` para la primera sección
- `Task::where('estado', '!=', TaskStatus::Finalizado)->whereNotNull('fecha_limite')->whereBetween('fecha_limite', [today(), today()->addDays(7)])` para la segunda sección — con exclusión de las `en_progreso` que ya están en "vencen pronto"
- `with('client')` eager loading obligatorio para evitar N+1 al mostrar nombre del cliente

</code_context>

<specifics>
## Specific Ideas

- El sidebar debe tener un estilo acorde al proyecto (fondo claro, active state con color de acento) — coherente con el `bg-gray-100` del layout actual
- El dropdown de cambio de estado puede implementarse como un `<select>` o un componente lightweight — sin librerías adicionales, el proyecto no usa una librería de UI components

</specifics>

<deferred>
## Deferred Ideas

- Sidebar colapsable (iconos/iconos+texto toggle) — v2 si se necesita más espacio horizontal
- Stats/métricas resumidas en el dashboard (N tareas activas, N vencen pronto) — v2
- Notificaciones visuales (badge en sidebar para tareas urgentes) — v2
- Filtros en el dashboard (por cliente, por prioridad) — v2

</deferred>

---

*Phase: 07-dashboard-del-admin*
*Context gathered: 2026-03-31*
