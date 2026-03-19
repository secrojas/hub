# Roadmap: Hub

## Overview

Hub se construye en siete fases ordenadas por dependencias duras: la autenticación y los roles de acceso abren el camino; el CRUD de clientes habilita todos los módulos de datos; tareas/Kanban entrega el valor central; facturación y presupuestos completan la gestión operativa; el portal del cliente agrega todos los módulos en una vista de solo lectura; y el dashboard del admin corona la plataforma con visibilidad inmediata de lo que está activo y lo que vence pronto.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Fundación y Auth** - Scaffolding de Laravel + Inertia + Vue 3, autenticación, roles admin/cliente e invitación por email
- [ ] **Phase 2: CRM de Clientes** - CRUD completo de clientes con todos sus campos, lista y vista de detalle
- [ ] **Phase 3: Tareas y Kanban** - Creación y edición de tareas, tablero Kanban por cliente con drag-and-drop y vista global
- [ ] **Phase 4: Facturación** - Registro manual de cobros en ARS, estados y dashboard mensual de facturación
- [ ] **Phase 5: Presupuestos y PDF** - Constructor de presupuestos con ítems, estados y generación de PDF al marcar como Enviado
- [ ] **Phase 6: Portal del Cliente** - Vistas de solo lectura para el cliente: tareas, presupuestos, facturación y resumen personal
- [ ] **Phase 7: Dashboard del Admin** - Vista principal del admin con tareas activas y próximas a vencer

## Phase Details

### Phase 1: Fundación y Auth
**Goal**: El sistema tiene autenticación funcional con dos roles separados y flujo de invitación — base segura sobre la que construir todos los módulos
**Depends on**: Nothing (first phase)
**Requirements**: AUTH-01, AUTH-02, AUTH-03, AUTH-04, AUTH-05
**Success Criteria** (what must be TRUE):
  1. El admin puede iniciar sesión con email y contraseña y la sesión persiste al recargar el navegador
  2. El admin puede enviar un link de invitación a un email y el cliente puede usarlo para definir su contraseña y acceder al portal
  3. El admin ve el layout de admin y el cliente ve el layout de portal — nunca se intercambian
  4. El usuario puede cerrar sesión desde cualquier página y queda completamente desautenticado
  5. Una URL de invitación usada o expirada es rechazada con mensaje de error
**Plans:** 4 plans
Plans:
- [ ] 01-01-PLAN.md — Laravel scaffold + Breeze + DB config + Role enum + migrations + seeder + test stubs
- [ ] 01-02-PLAN.md — Admin auth flow (middleware, layouts, role redirect, shared data, tests)
- [ ] 01-03-PLAN.md — Invitation system (controller, signed URLs, accept form, client registration)
- [ ] 01-04-PLAN.md — Final wiring (disable registration, nav links, visual verification)

### Phase 2: CRM de Clientes
**Goal**: El admin puede crear, ver, editar y eliminar clientes con todos sus campos operativos
**Depends on**: Phase 1
**Requirements**: CLIE-01, CLIE-02, CLIE-03, CLIE-04
**Success Criteria** (what must be TRUE):
  1. El admin puede crear un cliente con nombre, empresa, email, teléfono, stack tecnológico, estado, notas internas y fecha de inicio
  2. El admin puede ver la lista completa de clientes con su estado visible
  3. El admin puede abrir la página de detalle de un cliente y ver todos sus campos
  4. El admin puede editar cualquier campo de un cliente y los cambios persisten
  5. El admin puede eliminar un cliente y este desaparece de la lista
**Plans**: TBD

### Phase 3: Tareas y Kanban
**Goal**: El admin puede gestionar tareas vinculadas a clientes y ver el estado de todo el trabajo en curso en un tablero Kanban
**Depends on**: Phase 2
**Requirements**: TASK-01, TASK-02, TASK-03, TASK-04, TASK-05
**Success Criteria** (what must be TRUE):
  1. El admin puede crear una tarea vinculada a un cliente con título, descripción, prioridad y fecha límite opcional
  2. El admin puede arrastrar una tarea entre columnas (Backlog / En progreso / En revisión / Finalizado) en el Kanban por cliente y el cambio persiste
  3. Si el drag-and-drop falla, la tarjeta vuelve a su posición original y aparece un mensaje de error
  4. El admin puede ver el Kanban global con tareas de todos los clientes en una sola vista
  5. El admin puede filtrar o buscar tareas por título, estado o prioridad
**Plans**: TBD

### Phase 4: Facturación
**Goal**: El admin puede registrar cobros manualmente y tener visibilidad inmediata de la deuda pendiente y lo cobrado en el mes
**Depends on**: Phase 2
**Requirements**: BILL-01, BILL-02, BILL-03, BILL-04
**Success Criteria** (what must be TRUE):
  1. El admin puede registrar un cobro con cliente, concepto, monto ARS, fecha de emisión, fecha de pago y estado
  2. Un cobro puede tener estado pendiente, pagado o vencido, y el admin puede cambiar ese estado
  3. El dashboard de facturación muestra el total cobrado en el mes actual y la deuda pendiente total en ARS
  4. El admin puede filtrar cobros por cliente o por estado y los resultados se actualizan correctamente
**Plans**: TBD

### Phase 5: Presupuestos y PDF
**Goal**: El admin puede crear presupuestos con ítems y generar un PDF descargable al enviarlos al cliente
**Depends on**: Phase 2
**Requirements**: QUOT-01, QUOT-02, QUOT-03
**Success Criteria** (what must be TRUE):
  1. El admin puede crear un presupuesto vinculado a un cliente con múltiples ítems (descripción + precio) y el total se calcula automáticamente
  2. Un presupuesto puede pasar por los estados Borrador, Enviado, Aceptado y Rechazado — solo transiciones válidas son permitidas
  3. Al marcar un presupuesto como "Enviado" se genera un PDF descargable con los ítems, precios y total en ARS
  4. El PDF se descarga correctamente con caracteres en español (ñ, tildes) y montos ARS sin corrupción
**Plans**: TBD

### Phase 6: Portal del Cliente
**Goal**: El cliente autenticado puede ver el estado de su trabajo, presupuestos y facturación — solo lectura, sin acceso a datos de otros clientes
**Depends on**: Phase 3, Phase 4, Phase 5
**Requirements**: PORT-01, PORT-02, PORT-03, PORT-04
**Success Criteria** (what must be TRUE):
  1. El cliente ve la lista de sus tareas activas con título, estado y fecha límite — no puede modificarlas
  2. El cliente ve sus presupuestos y el estado de cada uno — no puede modificarlos
  3. El cliente ve su estado de facturación (qué debe o ha pagado) en ARS — no puede modificarlo
  4. El portal del cliente nunca muestra notas internas, datos de otros clientes ni montos globales del admin
  5. El cliente ve un dashboard personal con resumen de sus tareas, presupuestos y facturación
**Plans**: TBD

### Phase 7: Dashboard del Admin
**Goal**: El admin tiene una vista central donde de un vistazo puede ver qué tareas están activas y cuáles vencen pronto
**Depends on**: Phase 3, Phase 4, Phase 5
**Requirements**: DASH-01
**Success Criteria** (what must be TRUE):
  1. La vista principal del admin muestra las tareas actualmente en estado "En progreso" agrupadas o listadas visiblemente
  2. Las tareas con fecha límite próxima aparecen destacadas o separadas del resto
  3. El dashboard carga con datos reales de todos los módulos sin errores N+1 perceptibles
**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6 → 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Fundación y Auth | 0/4 | Not started | - |
| 2. CRM de Clientes | 0/TBD | Not started | - |
| 3. Tareas y Kanban | 0/TBD | Not started | - |
| 4. Facturación | 0/TBD | Not started | - |
| 5. Presupuestos y PDF | 0/TBD | Not started | - |
| 6. Portal del Cliente | 0/TBD | Not started | - |
| 7. Dashboard del Admin | 0/TBD | Not started | - |
