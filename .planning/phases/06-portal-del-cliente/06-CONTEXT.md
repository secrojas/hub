# Phase 6: Portal del Cliente - Context

**Gathered:** 2026-03-30
**Status:** Ready for planning

<domain>
## Phase Boundary

El cliente autenticado puede ver el estado de su trabajo en un portal de solo lectura: todas sus tareas, sus presupuestos (con descarga de PDF), su facturación y un dashboard personal con resumen. No puede modificar nada, no ve datos de otros clientes, no ve notas internas ni montos globales del admin.

</domain>

<decisions>
## Implementation Decisions

### Estructura de navegación
- Dashboard único en `/portal` — una sola página que combina el resumen y todas las secciones
- Sin páginas separadas (/portal/tasks, /portal/quotes, /portal/billing) — todo en un solo scroll
- El `PortalLayout.vue` existente no necesita links adicionales en la nav
- Las secciones se organizan verticalmente en la página: dashboard de resumen arriba, luego tareas, presupuestos y facturación

### Acceso al PDF de presupuestos
- El cliente puede descargar el PDF de sus propios presupuestos
- Nueva ruta: `GET /portal/quotes/{id}/pdf` protegida por `EnsureIsClient` + ownership check (verifica que `quote.client_id === auth user's client_id`)
- Link de descarga visible en la sección de presupuestos del portal — sin restricción por estado (visible para todos los estados)
- El cliente nunca puede acceder al PDF de otro cliente

### Dashboard personal (PORT-04)
- **Bloque de tareas**: cards de conteo por estado — muestra cuántas tareas tiene en cada estado (Backlog, En progreso, En revisión, Finalizado)
- **Bloque de presupuestos**: cards de conteo por estado (Borrador, Enviado, Aceptado, Rechazado)
- **Bloque de facturación**: dos cifras en ARS — monto total pendiente + monto total pagado, usando `formatMonto()` con `Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' })`
- Los tres bloques se muestran como cards/sections de resumen al tope de `/portal`

### Alcance de datos — Tareas (PORT-01)
- "Tareas activas" = todas las tareas del cliente, sin importar el estado
- El cliente ve: Backlog, En progreso, En revisión y Finalizado — vista completa de su trabajo
- Lista con título, estado y fecha límite (si existe)

### Listas — Presupuestos (PORT-02) y Facturación (PORT-03)
- Listas puras sin filtros — el volumen de datos de un cliente freelance no justifica filtros en v1
- Presupuestos: título, estado, monto total ARS, link de descarga PDF
- Facturación (cobros): concepto, monto ARS, fecha de emisión, estado (pendiente/pagado/vencido)
- Todos los cobros del cliente, sin filtro por estado

### Seguridad / Isolation
- Todas las queries del portal se filtran por `client_id` del usuario autenticado
- Las rutas del portal son inaccesibles para el admin (protegidas por `EnsureIsClient`)
- El admin no puede acceder a `/portal` ni a `/portal/quotes/{id}/pdf`
- Notas internas del cliente (`clients.notas`) nunca se exponen en las props Inertia del portal

### Claude's Discretion
- Diseño visual de los bloques del dashboard (colores de estado, iconos, layout de cards)
- Ordenamiento por defecto de las listas (fecha de creación descendente es razonable)
- Manejo del estado vacío por sección (mensaje "No tenés tareas aún", etc.)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Requirements
- `.planning/REQUIREMENTS.md` §Portal del Cliente — PORT-01, PORT-02, PORT-03, PORT-04
- `.planning/ROADMAP.md` §Phase 6 — Success criteria (5 criterios, incluyendo isolation de datos)

### Contexto de fases previas
- `.planning/phases/01-fundaci-n-y-auth/01-CONTEXT.md` — Routing: cliente redirige a `/portal`, middlewares `EnsureIsAdmin` / `EnsureIsClient`
- `.planning/phases/05-presupuestos-y-pdf/05-CONTEXT.md` — PDF: ruta existente `GET /quotes/{id}/pdf` es admin-only; `formatMonto()` para ARS; patrón de nullOnDelete en FKs

No hay ADRs externos — los requisitos están completamente capturados en las decisiones arriba.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `resources/js/Layouts/PortalLayout.vue` — Layout con navbar (Hub Portal link, nombre del usuario, logout). Ya funcional, no necesita cambios.
- `resources/js/Pages/Portal/Index.vue` — Placeholder actual de `/portal`. Esta es la página a implementar.
- `app/Http/Middleware/EnsureIsClient.php` — Middleware de protección de rutas del portal.
- `app/Enums/TaskStatus.php`, `BillingStatus.php`, `QuoteStatus.php` — Enums de estado para las queries de conteo.
- `formatMonto()` pattern (`Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' })`) — definido en fases anteriores, reutilizar para mostrar montos.

### Established Patterns
- `defineOptions({ layout: PortalLayout })` en el Portal/Index.vue (ya presente)
- Resource controllers con Form Requests para validación (no aplica aquí — solo lectura)
- Props Inertia desde el controlador: pasar datos como props al componente Vue
- Queries filtradas por `client_id`: `Auth::user()->client_id` o `Auth::user()->client->id` según el modelo

### Integration Points
- `tasks` table: `client_id` FK → los datos del cliente se obtienen filtrando por su `client_id`
- `billings` table: `client_id` FK → misma lógica
- `quotes` table: `client_id` FK (nullOnDelete) → misma lógica; además expone ruta PDF
- `users` table: campo `role` (enum admin/client) — el user autenticado tiene role `client`
- `clients` table: relaciona `users` con sus datos de negocio — `users.client_id` o relación `user->client`
- Ruta existente `GET /portal` en `routes/web.php` — actualmente renderiza el placeholder, debe actualizarse al controlador real

</code_context>

<specifics>
## Specific Ideas

No se mencionaron referencias específicas de diseño — el portal puede seguir la estética del AdminLayout (mismo proyecto, consistencia visual con PortalLayout.vue de fondo `bg-blue-50`).

</specifics>

<deferred>
## Deferred Ideas

- Filtros por estado en tareas/presupuestos/cobros — se puede agregar en v2 si el volumen lo justifica
- Vista de detalle individual de presupuesto en el portal (página separada con todos los ítems)
- Notificaciones al cliente cuando llega un nuevo presupuesto (NOTF-01 — v2)
- Kanban interactivo para el cliente — explícitamente out of scope (PROJECT.md)

</deferred>

---

*Phase: 06-portal-del-cliente*
*Context gathered: 2026-03-30*
