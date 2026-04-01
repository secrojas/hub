# Hub

## What This Is

Hub es una plataforma interna de gestión de clientes para un negocio freelance de desarrollo web. Permite al admin (el propio desarrollador) gestionar clientes, tareas, facturación y presupuestos desde un único lugar, y ofrece a cada cliente un portal restringido donde puede ver el estado de su trabajo. Vive en hub.srojasweb.dev.

## Core Value

El admin puede ver de un vistazo qué tareas están activas en este momento y qué vence pronto — todo lo demás es soporte a esa claridad operativa.

## Requirements

### Validated

*(v1.0 MVP — shipped 2026-04-01)*

**Autenticación y usuarios**
- ✓ El admin puede iniciar sesión con email y contraseña — v1.0 (AUTH-01)
- ✓ El admin puede crear cuentas de clientes mediante invitación por email — v1.0 (AUTH-02)
- ✓ El cliente accede al portal con el link de invitación y define su contraseña — v1.0 (AUTH-03)
- ✓ Las sesiones persisten tras recargar la página — v1.0 (AUTH-04)
- ✓ El usuario puede hacer logout desde cualquier página — v1.0 (AUTH-05)

**Clientes (CRUD)**
- ✓ El admin puede crear, editar y eliminar clientes — v1.0 (CLIE-01)
- ✓ Cada cliente tiene: nombre, empresa, email, teléfono, stack tecnológico, estado, notas internas y fecha de inicio — v1.0 (CLIE-02)
- ✓ El admin puede ver la lista completa de clientes — v1.0 (CLIE-03)
- ✓ El admin puede ver la página de detalle de un cliente individual — v1.0 (CLIE-04)

**Tareas (Kanban)**
- ✓ El admin puede crear tareas vinculadas a un cliente con: título, descripción, prioridad y fecha límite opcional — v1.0 (TASK-01)
- ✓ El admin puede editar y eliminar tareas — v1.0 (TASK-02)
- ✓ Las tareas se visualizan en tablero Kanban con drag & drop (columnas: Backlog / En progreso / En revisión / Finalizado) — v1.0 (TASK-03)
- ✓ Existe una vista global del Kanban con todas las tareas de todos los clientes — v1.0 (TASK-04)
- ✓ El admin puede filtrar tareas por título, estado o prioridad — v1.0 (TASK-05)

**Facturación**
- ✓ El admin puede registrar manualmente cobros en ARS: cliente, concepto, monto, fecha de emisión, fecha de pago, estado — v1.0 (BILL-01)
- ✓ Los cobros tienen estados: pendiente / pagado / vencido — v1.0 (BILL-02)
- ✓ Dashboard de facturación muestra resumen mensual y deuda pendiente total — v1.0 (BILL-03)
- ✓ El admin puede filtrar cobros por cliente o por estado — v1.0 (BILL-04)

**Presupuestos**
- ✓ El admin puede crear presupuestos con ítems (descripción + precio) vinculados a un cliente — v1.0 (QUOT-01)
- ✓ Estados del presupuesto: Borrador / Enviado / Aceptado / Rechazado — v1.0 (QUOT-02)
- ✓ Al marcar como "Enviado" se genera un PDF descargable — v1.0 (QUOT-03)

**Portal del cliente**
- ✓ El cliente ve sus tareas activas en formato lista (título, estado, fecha límite) — v1.0 (PORT-01)
- ✓ El cliente ve sus presupuestos y su estado — v1.0 (PORT-02)
- ✓ El cliente ve su estado de facturación (qué debe o ha pagado) — v1.0 (PORT-03)
- ✓ El cliente no puede modificar nada — solo lectura, con dashboard personal de resumen — v1.0 (PORT-04)

**Dashboard del admin**
- ✓ Vista principal muestra tareas activas (en progreso) y las próximas a vencer — v1.0 (DASH-01)

### Active

*(v2.0 — por definir)*

- [ ] Notificaciones por email al cliente cuando llega un nuevo presupuesto (NOTF-01)
- [ ] Notificaciones por email al cliente cuando una tarea cambia de estado (NOTF-02)
- [ ] El admin recibe email cuando un cliente acepta o rechaza un presupuesto (NOTF-03)
- [ ] Historial de presupuestos filtrable por cliente (QUOT-04)
- [ ] Vista de presupuesto compartible por link público sin login (QUOT-05)
- [ ] Múltiples admins / modo equipo (ADM-01)

### Out of Scope

- Integración AFIP / factura electrónica — complejidad legal/técnica fuera del scope de un CRM de gestión
- Pasarelas de pago (MercadoPago, Stripe) — registro manual suficiente para el volumen freelance
- Time tracking / timesheets — módulo independiente, no solicitado
- Facturas recurrentes / suscripciones — complejidad innecesaria
- Chat / mensajería interna — tiempo real agrega complejidad sin valor core
- App móvil / PWA — web-first, scope futuro
- Múltiples monedas — solo ARS en v1
- Kanban interactivo en portal del cliente — el cliente solo necesita ver estado, no arrastrar columnas
- Reportes / analytics avanzados — diferido a v2+

## Context

- Stack: Laravel 12 + Inertia.js v2 + Vue 3 + MySQL 8.0
- Entorno: Laragon (desarrollo local en Windows), producción en hub.srojasweb.dev
- El proyecto es para uso personal del desarrollador — no hay equipo, no hay múltiples admins
- Moneda única: ARS (pesos argentinos) — sin conversión ni multi-currency
- PDF generado server-side con barryvdh/laravel-dompdf v3.1.2, streamDownload, DejaVu Sans
- v1.0 MVP shipped: 7 fases, 20 planes, ~6,100 LOC (PHP + Vue), 13 días de desarrollo
- Módulos activos: Auth, Clientes, Tareas/Kanban, Facturación, Presupuestos/PDF, Portal del Cliente, Dashboard del Admin

## Constraints

- **Tech stack**: Laravel 12 + Inertia.js + Vue 3 + MySQL — no negociable
- **Moneda**: Solo ARS — sin soporte multi-currency
- **Roles**: Solo dos roles (admin / cliente) — RBAC simple
- **Escala**: Personal/freelance — no se diseña para multi-tenant ni alta concurrencia

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Inertia.js en lugar de API REST + SPA separada | El desarrollador ya conoce el stack y quiere velocidad de desarrollo | ✓ Bueno — desarrollo fluido, SSR-ready si se necesita |
| PDF de presupuesto generado en servidor (Laravel) | Evita dependencias JS del cliente, más confiable | ✓ Bueno — barryvdh/laravel-dompdf v3.1.2, streamDownload, DejaVu Sans para UTF-8 |
| Portal del cliente: lista simple (no Kanban) | Reduce complejidad del portal, el cliente no necesita arrastrar columnas | ✓ Bueno — portal limpio, sin complejidad extra |
| Sin notificaciones automáticas en v1 | Evita configuración de queues/email en etapas tempranas | ✓ Bueno — se puede agregar en v2 con queues |
| nullOnDelete en billings/quotes client_id | Registros financieros deben sobrevivir eliminación del cliente | ✓ Bueno — integridad de datos sin cascade destructivo |
| vue-draggable-plus para Kanban | Vue 3 compatible, activamente mantenido | ⚠️ Revisit — API difiere del Vue 2 vuedraggable: usar @add + event.data, NO @change + event.added.element |
| DejaVu Sans en dompdf | Único font bundled con soporte completo UTF-8/Latin | ✓ Bueno — resuelve ñ/á/é silently dropping con font default |
| Two-query pattern en Dashboard | vencenProonto primero, enProgreso excluye esos IDs con whereNotIn | ✓ Bueno — queries limpias, sin duplicados entre secciones |

---
*Last updated: 2026-04-01 after v1.0 MVP milestone*
