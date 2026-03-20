# Phase 3: Tareas y Kanban - Context

**Gathered:** 2026-03-20
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase entrega el módulo de tareas con tablero Kanban interactivo. El admin puede crear, editar y eliminar tareas vinculadas a clientes, visualizarlas en un tablero Kanban con drag & drop (cambia de estado al soltar), y ver una vista global de todas las tareas o filtrada por cliente. No incluye portal del cliente (Phase 6), facturación ni presupuestos.

</domain>

<decisions>
## Implementation Decisions

### Librería Drag & Drop
- Usar **`@dnd-kit/core` + `@dnd-kit/sortable`** — activamente mantenida, compatible con Vue 3
- `vue.draggable.next` descartada — mantenimiento incierto
- Al soltar una tarea en nueva columna: Inertia hace `PUT /tasks/{id}/status` para persistir el estado
- Sin reordenamiento interno dentro de una columna — solo cambio de columna (estado)

### Campos de Tarea
- **Obligatorios**: `titulo`, `client_id`
- **Opcionales**: `descripcion` (textarea nullable), `prioridad` (enum), `fecha_limite` (date nullable)
- `prioridad` enum: `baja` / `media` / `alta` — default `media`
- `estado` enum: `backlog` / `en_progreso` / `en_revision` / `finalizado` — default `backlog` (siempre empieza en backlog, no seleccionable al crear)
- FK `client_id` en `tasks` — relación con tabla `clients`

### UX del Kanban
- **Crear tarea**: Modal inline en el Kanban (botón "+" en la columna Backlog o botón general)
- **Editar tarea**: Click en la card abre modal de edición inline
- **Eliminar tarea**: Botón "Eliminar" dentro del modal de edición con confirmación
- **Card en el Kanban muestra**: Título, nombre del cliente (en vista global), badge de prioridad con color (rojo=alta, amarillo=media, verde=baja), fecha límite si existe
- Vista global: `/tasks` — todas las tareas de todos los clientes, filtrable por cliente via `?cliente={id}`
- Link "Ver Kanban" en `/clients/{id}` que lleva a `/tasks?cliente={id}`

### Columnas del Kanban
- 4 columnas fijas: **Backlog** / **En progreso** / **En revisión** / **Finalizado**
- Sin columnas configurables en v1

### Extras
- Descripción: textarea libre, nullable — incluida en el modal de creación/edición
- Borrar tarea: desde el modal de edición (no página separada)
- Sin ordenamiento interno de columna — solo drag between columns
- Las tareas serán visibles en el portal del cliente (Phase 6) — diseñar la relación teniendo esto en cuenta

### Claude's Discretion
- Estructura interna de los componentes Vue del Kanban (KanbanBoard, KanbanColumn, TaskCard)
- Implementación del wrapper de @dnd-kit para Vue 3 — criterio de Claude
- Colores exactos de los badges de prioridad
- Paginación o carga completa en el Kanban — criterio de Claude (recomendado: carga completa para vista global, máximo razonable)

</decisions>

<code_context>
## Existing Code Insights

### Reusable Assets
- `AdminLayout.vue` — layout base, ya tiene nav con Dashboard / Clientes / Invitar Cliente
- `app/Models/Client.php` — modelo existente, `tasks()` hasMany se agrega aquí
- `resources/js/Pages/Admin/Clients/Show.vue` — recibe botón "Ver Kanban" apuntando a `/tasks?cliente={id}`
- Patrón de modales ya establecido en `Index.vue` de clientes (ref sentinel + v-if)

### Established Patterns
- Inertia pages bajo `resources/js/Pages/Admin/` para páginas del admin
- `defineOptions({ layout: AdminLayout })` para layout persistente
- `useForm` de Inertia para formularios con manejo de errores
- `router.put()` / `router.delete()` para acciones sin formulario completo
- Filtros via query params `router.get(route, params, { preserveState: true })`

### Integration Points
- `tasks.client_id` → `clients.id`: relación que alimenta el portal del cliente en Phase 6
- `tasks.estado` enum values deben coincidir con lo que mostrará el portal del cliente
- La tabla `tasks` es referenciada por el dashboard del admin (Phase 7) para "tareas activas y próximas a vencer"
- `Client::tasks()` hasMany — agregar en el modelo Client

</code_context>

<specifics>
## Specific Ideas

- El `TaskController` puede tener un método extra `updateStatus` para el drag & drop (`PUT /tasks/{id}/status`) además del resource estándar
- El modal de creación puede tener un campo oculto `estado` = `backlog` por defecto (no visible para el admin al crear)
- En la vista global `/tasks`, mostrar el nombre del cliente en cada card es clave para orientación
- @dnd-kit no tiene binding Vue oficial — usar `@dnd-kit/core` directamente con composables de Vue 3 o buscar `vue-dndkit` wrapper. El investigador debe verificar la mejor opción actual.
- Para el filtro por cliente en `/tasks?cliente={id}`, pasar los clientes como prop al componente para el dropdown

</specifics>

<deferred>
## Deferred Ideas

- Ordenamiento interno de tareas dentro de una columna (requiere campo `order` en DB) — v2
- Notificaciones al cliente cuando una tarea cambia de estado — out of scope v1
- Comentarios o actividad en tareas — fuera de scope
- Asignación de tareas a personas — solo hay un admin en v1
- Subtareas — fuera de scope v1
- Etiquetas/tags en tareas — fuera de scope v1

</deferred>
