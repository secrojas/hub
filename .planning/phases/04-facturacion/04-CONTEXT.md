# Phase 4: Facturación - Context

**Gathered:** 2026-03-20
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase entrega el módulo de facturación manual: el admin puede registrar cobros (cliente, concepto, monto ARS, fechas, estado) y ver un dashboard con resumen mensual y deuda pendiente total. Incluye CRUD completo de cobros y sección de facturación en la página del cliente. No incluye integración con AFIP ni pasarelas de pago — todo es registro manual.

</domain>

<decisions>
## Implementation Decisions

### Campos y Estados de Cobros
- Campos: `client_id` (FK), `concepto` (string), `monto` (decimal 12,2 ARS), `fecha_emision` (date), `fecha_pago` (date nullable), `estado` (enum)
- Estados enum: `pendiente` / `pagado` / `vencido`
- Estado **manual** — el admin cambia el estado explícitamente, no hay automatización
- `monto` como `decimal(12,2)` — permite centavos en ARS

### Dashboard de Facturación
- Página dedicada `/billing` — resumen arriba + tabla de cobros abajo
- Resumen muestra: total cobrado este mes (sum donde estado=pagado y fecha_pago en mes actual) + deuda pendiente total (sum donde estado=pendiente) + count de cobros vencidos
- Filtros en tabla: por estado (dropdown) + por cliente (dropdown)
- Sin exportación CSV en v1

### Creación y Edición de Cobros
- Páginas separadas: `/billing/create` y `/billing/{id}/edit`
- Validación: si `estado` = `pagado`, entonces `fecha_pago` es requerida
- Borrar cobro con confirmación modal (en el formulario de edición o en la tabla)
- Sección "Facturación" en `/clients/{id}` — muestra cobros de ese cliente en tabla compacta (read-only, sin crear desde ahí)

### Claude's Discretion
- Paginación de la tabla de cobros principal
- Formato de display del monto (punto decimal, separador de miles)
- Estructura de la sección de facturación en la página del cliente
- Nombre del modelo: `Billing` o `Payment` — criterio de Claude

</decisions>

<code_context>
## Existing Code Insights

### Reusable Assets
- `AdminLayout.vue` — layout base, necesita nav "Facturación"
- `app/Models/Client.php` — necesita `billings()` hasMany
- `resources/js/Pages/Admin/Clients/Show.vue` — necesita sección de facturación compacta
- Patrón de filtros via query params ya establecido en Phase 3 (TaskController + Index.vue)
- Patrón de confirmación de borrado via modal ya establecido en Phase 2 (ClientController)

### Established Patterns
- Resource controllers con Form Requests para validación
- Filtros: `router.get` con `preserveState + preserveScroll`
- Pages bajo `resources/js/Pages/Admin/`
- `defineOptions({ layout: AdminLayout })` en cada page

### Integration Points
- `billings.client_id` → `clients.id` — relación que el portal del cliente (Phase 6) necesitará para mostrar estado de facturación
- El dashboard del admin (Phase 7) NO necesita facturación según el ROADMAP — solo tareas activas y próximas a vencer
- La sección en `/clients/{id}` debe ser read-only — el portal del cliente en Phase 6 tendrá su propio endpoint

</code_context>

<specifics>
## Specific Ideas

- El resumen mensual puede usar `whereMonth` + `whereYear` de Eloquent para filtrar cobros del mes actual
- El campo `monto` en el formulario debe aceptar punto decimal (formato argentino usa coma, pero HTML input type=number usa punto — usar input text con validación)
- La sección de facturación en `/clients/{id}` puede pasar los cobros del cliente como prop adicional desde `ClientController@show`

</specifics>

<deferred>
## Deferred Ideas

- Exportar listado de cobros a CSV — v2
- Marcado automático de "vencido" al superar fecha — requiere scheduler, v2
- Número de factura o referencia interna — v2
- Múltiples monedas — out of scope (solo ARS)
- Integración con AFIP o pasarelas de pago — out of scope v1

</deferred>
