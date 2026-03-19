# Hub

## What This Is

Hub es una plataforma interna de gestión de clientes para un negocio freelance de desarrollo web. Permite al admin (el propio desarrollador) gestionar clientes, tareas, facturación y presupuestos desde un único lugar, y ofrece a cada cliente un portal restringido donde puede ver el estado de su trabajo. Vive en hub.srojasweb.dev.

## Core Value

El admin puede ver de un vistazo qué tareas están activas en este momento y qué vence pronto — todo lo demás es soporte a esa claridad operativa.

## Requirements

### Validated

(None yet — ship to validate)

### Active

**Autenticación y usuarios**
- [ ] El admin puede iniciar sesión con email y contraseña
- [ ] El admin puede crear cuentas de clientes mediante invitación por email
- [ ] El cliente accede al portal con el link de invitación y define su contraseña
- [ ] Las sesiones persisten tras recargar la página

**Clientes (CRUD)**
- [ ] El admin puede crear, editar y eliminar clientes
- [ ] Cada cliente tiene: nombre, empresa, email, teléfono, stack tecnológico, estado (activo / potencial / pausado), notas internas y fecha de inicio

**Tareas (Kanban)**
- [ ] El admin puede crear tareas vinculadas a un cliente con: título, descripción, prioridad y fecha límite opcional
- [ ] Las tareas se visualizan en tablero Kanban por cliente (columnas: Backlog / En progreso / En revisión / Finalizado)
- [ ] Existe una vista global del Kanban con todas las tareas de todos los clientes
- [ ] Las tareas no se vinculan con facturación

**Facturación**
- [ ] El admin puede registrar manualmente cobros en ARS: cliente, concepto, monto, fecha de emisión, fecha de pago, estado (pendiente / pagado / vencido)
- [ ] Dashboard de facturación muestra resumen mensual y deuda pendiente total

**Presupuestos**
- [ ] El admin puede crear presupuestos con ítems (descripción + precio) vinculados a un cliente
- [ ] Estados del presupuesto: Borrador / Enviado / Aceptado / Rechazado
- [ ] Al marcar como "Enviado" se genera un PDF descargable

**Portal del cliente**
- [ ] El cliente ve sus tareas activas en formato lista (título, estado, fecha límite)
- [ ] El cliente ve sus presupuestos y su estado
- [ ] El cliente ve su estado de facturación (qué debe o ha pagado)
- [ ] El cliente no puede modificar nada — solo lectura

**Dashboard del admin**
- [ ] Vista principal muestra tareas activas (en progreso) y las próximas a vencer

### Out of Scope

- Notificaciones automáticas al cliente (excepto email de invitación) — se agrega en v2 si se necesita
- App móvil — web-first
- Múltiples monedas — solo ARS
- Múltiples admins — un solo admin en v1
- Chat o mensajería interna — complejidad innecesaria
- Integración con sistemas de pago externos — registro manual es suficiente
- Vista Kanban interactiva para el cliente — solo lista de lectura

## Context

- Stack definido: Laravel 11 + Inertia.js + Vue 3 + MySQL
- Entorno: Laragon (desarrollo local en Windows), producción en hub.srojasweb.dev
- El proyecto es para uso personal del desarrollador — no hay equipo, no hay múltiples admins
- Moneda única: ARS (pesos argentinos) — sin conversión ni multi-currency
- El sistema de facturación es registro manual, no hay integración con AFIP ni pasarelas de pago
- Las tareas y la facturación son módulos independientes — no hay vínculo entre ellos
- Los presupuestos generan PDF (sin envío automático por email en v1)

## Constraints

- **Tech stack**: Laravel 11 + Inertia.js + Vue 3 + MySQL — no negociable, definido por el desarrollador
- **Moneda**: Solo ARS — sin soporte multi-currency
- **Roles**: Solo dos roles (admin / cliente) — RBAC simple, sin granularidad adicional
- **Escala**: Personal/freelance — no se diseña para multi-tenant ni alta concurrencia

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Inertia.js en lugar de API REST + SPA separada | El desarrollador ya conoce el stack y quiere velocidad de desarrollo | — Pending |
| PDF de presupuesto generado en servidor (Laravel) | Evita dependencias JS del cliente, más confiable | — Pending |
| Portal del cliente: lista simple (no Kanban) | Reduce complejidad del portal, el cliente no necesita arrastrar columnas | — Pending |
| Sin notificaciones automáticas en v1 | Evita configuración de queues/email en etapas tempranas | — Pending |

---
*Last updated: 2026-03-19 after initialization*
