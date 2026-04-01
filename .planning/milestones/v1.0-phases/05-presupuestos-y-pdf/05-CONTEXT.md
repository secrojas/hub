# Phase 5: Presupuestos y PDF - Context

**Gathered:** 2026-03-30
**Status:** Ready for planning

<domain>
## Phase Boundary

El admin puede crear presupuestos vinculados a un cliente con múltiples ítems (descripción + precio ARS), gestionar su estado (Borrador / Enviado / Aceptado / Rechazado) y descargar un PDF generado en el servidor al marcarlos como Enviado. No incluye envío automático por email, historial filtrable avanzado ni link público compartible — esos son v2.

</domain>

<decisions>
## Implementation Decisions

### Construcción de ítems
- Filas dinámicas en el browser: botón "+ Agregar ítem" agrega filas sin recargar la página
- Al guardar se envían todos los ítems en un solo submit
- El total se calcula de forma reactiva en el browser con Vue computed property — se actualiza en tiempo real mientras el admin escribe precios
- Mínimo 1 ítem requerido para guardar; sin máximo de ítems
- Campos del presupuesto: `client_id` (dropdown), `titulo` (string), `notas` (text, opcional), más la tabla de ítems
- Estructura de ítem: `descripcion` (string) + `precio` (decimal ARS)

### Transiciones de estado
- El admin cambia el estado desde acciones en la tabla de `/quotes` (botones/dropdown inline por fila)
- Transiciones: cualquier estado → cualquier estado (flexible, sin restricciones de flujo)
- El contenido del presupuesto (ítems, título, notas) solo es editable cuando el estado es **Borrador**
- Post-Borrador: la página de edición muestra los ítems en modo lectura y solo permite cambiar el estado
- Un presupuesto solo se puede **eliminar** cuando está en estado Borrador

### Generación del PDF
- **Sync — descarga directa**: `GET /quotes/{id}/pdf` → dompdf genera el PDF → respuesta de descarga inmediata
- Sin almacenamiento: el PDF se regenera en cada descarga (no hay columna `pdf_path`)
- El botón "Descargar PDF" solo aparece cuando el estado **no es Borrador** (Enviado, Aceptado, Rechazado)
- Library: `barryvdh/laravel-dompdf` (decisión previa, STATE.md — pure PHP, sin dependencias de binarios)

### Contenido del PDF
- **Encabezado**: nombre "srojasweb" hardcoded en el template Blade + fecha de creación del presupuesto
- **Datos del cliente**: nombre, empresa, email del cliente
- **Título del presupuesto** + estado actual
- **Tabla de ítems**: descripción + precio ARS (formato `$ 50.000,00`)
- **Total** en ARS en negrita destacado al pie de la tabla
- **Notas** en bloque al pie si el campo `notas` no está vacío
- **Nombre del archivo descargado**: `presupuesto-{id}-{slug-titulo}.pdf` (ej: `presupuesto-7-landing-page-empresa-xyz.pdf`)

### Claude's Discretion
- Diseño visual del PDF (colores, tipografía, layout exacto del template Blade)
- Cómo se genera el slug del título para el nombre del archivo
- Estructura de la tabla migrations (`quote_items` separada o `items` JSON — preferentemente tabla separada por normalización)
- Orden de las columnas en la tabla `/quotes`

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Requirements
- `.planning/REQUIREMENTS.md` §Presupuestos — QUOT-01, QUOT-02, QUOT-03 (los únicos reqs de esta fase)
- `.planning/ROADMAP.md` §Phase 5 — Success criteria (4 criterios, incluyendo caracteres españoles y montos ARS sin corrupción en el PDF)

### Decisiones previas relevantes
- `.planning/STATE.md` §Accumulated Context — PDF generation: `barryvdh/laravel-dompdf`; nullOnDelete pattern para FKs de registros financieros

No hay ADRs externos ni specs adicionales — los requisitos están completamente capturados en las decisiones arriba.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `app/Enums/BillingStatus.php` — Patrón de enum de estado; `QuoteStatus` seguirá la misma estructura (string-backed enum)
- `app/Models/Billing.php` — Patrón de modelo: `$fillable`, `casts()` con enum y fechas, `belongsTo(Client::class)`
- `resources/js/Pages/Admin/Billing/Index.vue` — Patrón de lista con filtros, badges de estado, modal de confirmación de borrado (ref(null) sentinel), `formatMonto()` para ARS
- `resources/js/Pages/Admin/Billing/Create.vue` / `Edit.vue` — Patrón de form con `useForm`, validación, redirect on success

### Established Patterns
- Resource controllers con Form Requests para validación
- Filtros: `router.get` con `preserveState + preserveScroll + replace: true`
- Pages bajo `resources/js/Pages/Admin/`
- `defineOptions({ layout: AdminLayout })` en cada page
- Modal de confirmación de borrado: `ref(null)` como sentinel (null = oculto, valor = item a eliminar)
- `formatMonto()` con `Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' })`
- nullOnDelete en FK de registros financieros (billing lo tiene → quotes deben hacer lo mismo)

### Integration Points
- `quotes.client_id` → `clients.id` con `nullOnDelete` — Phase 6 (Portal del Cliente) necesitará listar presupuestos por cliente
- No hay vínculo con `billings` ni con `tasks`
- PDF accesible vía `GET /quotes/{id}/pdf` — ruta protegida por middleware `auth` + `admin`
- La nav de AdminLayout necesitará una entrada "Presupuestos" → `/quotes`

</code_context>

<specifics>
## Specific Ideas

- El flujo visual del formulario de creación: cliente + título + notas en la parte superior, tabla de ítems dinámica abajo, total reactivo al pie de la tabla
- El estado se cambia directamente desde la lista `/quotes` con botones de acción por fila (no desde una página de detalle separada)
- La edición de un presupuesto post-Borrador debe mostrar los ítems como texto de solo lectura y solo permitir cambiar el estado — no un form deshabilitado sino una vista clara
- Nombre de archivo del PDF: `presupuesto-7-landing-page-empresa-xyz.pdf`

</specifics>

<deferred>
## Deferred Ideas

- QUOT-04: Historial de presupuestos filtrable por cliente — v2
- QUOT-05: Vista de presupuesto compartible por link público sin login — v2
- NOTF-01: Email al cliente cuando llega un nuevo presupuesto — v2
- NOTF-03: Email al admin cuando cliente acepta/rechaza — v2

</deferred>

---

*Phase: 05-presupuestos-y-pdf*
*Context gathered: 2026-03-30*
