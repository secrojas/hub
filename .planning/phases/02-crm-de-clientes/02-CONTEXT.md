# Phase 2: CRM de Clientes - Context

**Gathered:** 2026-03-20
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase entrega el CRUD completo de clientes: el admin puede crear, ver, editar y eliminar clientes con todos sus campos operativos. Incluye una tabla `clients` separada de `users`, vinculada via `client_id` en la tabla `users`. También integra el flujo de invitación de Phase 1 directamente desde la página de detalle del cliente. No incluye tareas, facturación ni presupuestos.

</domain>

<decisions>
## Implementation Decisions

### Campos y Validación
- Campos **obligatorios**: `nombre`, `email`
- Campos **opcionales**: `empresa`, `telefono`, `stack_tecnologico` (text libre), `estado`, `notas`, `fecha_inicio`
- `email` único en tabla `clients` — validación `unique:clients,email`
- Estado por defecto al crear: `activo`
- `stack_tecnologico` como campo texto libre (textarea) — sin tags ni multiselect

### UI y Navegación
- Listado de clientes: tabla con columnas nombre, empresa, estado, fecha inicio + botones Ver/Editar/Eliminar
- Filtro por estado (activo/potencial/pausado) — dropdown simple, sin búsqueda de texto
- Confirmación de eliminación via modal simple ("¿Eliminar a {nombre}?")
- Vista de detalle: página `/clients/{id}` con todos los campos en modo lectura + botón Editar
- Formulario de creación/edición en páginas separadas (`/clients/create`, `/clients/{id}/edit`)

### Relación Clientes-Usuarios
- Tabla `clients` separada de `users` — `clients` tiene los datos de negocio, `users` solo maneja auth
- Columna `client_id` nullable en tabla `users` — FK a `clients.id`
- Al aceptar invitación, el `users.client_id` se asocia automáticamente al cliente CRM que generó la invitación
- Tabla `invitations` recibe columna `client_id` — vincula la invitación con el cliente CRM
- Botón "Invitar al portal" en la página de detalle del cliente → reutiliza `InvitationController`
- Si el cliente ya tiene un user activo, mostrar error "Este cliente ya tiene una cuenta activa" (sin generar link)

### Claude's Discretion
- Paginación del listado — si/no y cantidad de items (criterio de Claude)
- Estilo de la tabla y el formulario — seguir convenciones del AdminLayout existente
- Nombres de rutas y resource controller — criterio de Claude
- Manejo de soft deletes — a criterio (simples o con SoftDeletes trait)

</decisions>

<code_context>
## Existing Code Insights

### Reusable Assets
- `AdminLayout.vue` — layout base para todas las páginas del admin
- `EnsureIsAdmin` middleware — ya registrado con alias `'admin'`
- `InvitationController@store` — reutilizable desde la página de detalle del cliente
- `app/Enums/Role.php` — enum `admin`/`client` ya existe
- `app/Models/User.php` — tiene `client_id` que hay que agregar (nullable FK)
- `app/Models/Invitation.php` — necesita columna `client_id`

### Established Patterns
- Inertia pages bajo `resources/js/Pages/Admin/` para páginas del admin
- `defineOptions({ layout: AdminLayout })` para asignar layout a cada página
- Inertia `useForm` para formularios con manejo de errores
- `router.delete()` + confirmación modal para eliminar recursos
- Shared data via `HandleInertiaRequests` — `auth.user` disponible en todas las páginas

### Integration Points
- `users.client_id` → `clients.id`: relación que habilita el portal del cliente en Phase 6
- `invitations.client_id`: necesario para que al aceptar la invitación el user quede asociado al cliente CRM
- La tabla `clients` es referenciada por tareas (Phase 3), facturación (Phase 4) y presupuestos (Phase 5)

</code_context>

<specifics>
## Specific Ideas

- El `ClientController` debe usar un resource controller estándar de Laravel
- El filtro por estado puede implementarse como query param (`?estado=activo`) sin JS reactivo — Inertia router GET
- La migración debe agregar `client_id` a `users` y `client_id` a `invitations` como parte de esta fase
- Al hacer `DELETE /clients/{id}`, verificar si el cliente tiene un user asociado y si tiene datos en otras tablas (tareas, facturas) — considerar si bloquear o cascade
- El formulario de edición debe pre-cargar todos los campos del cliente via Inertia props

</specifics>

<deferred>
## Deferred Ideas

- Búsqueda de texto (full-text search) en el listado — se agrega si se necesita en v2
- Historial de cambios del cliente (audit log) — fuera de scope
- Exportar listado de clientes a CSV — fuera de scope v1
- Foto/avatar del cliente — fuera de scope
- Múltiples contactos por cliente — fuera de scope, un solo email

</deferred>
