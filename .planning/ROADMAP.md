# Roadmap: Hub

## Overview

Hub se construye en siete fases ordenadas por dependencias duras: la autenticación y los roles de acceso abren el camino; el CRUD de clientes habilita todos los módulos de datos; tareas/Kanban entrega el valor central; facturación y presupuestos completan la gestión operativa; el portal del cliente agrega todos los módulos en una vista de solo lectura; y el dashboard del admin corona la plataforma con visibilidad inmediata de lo que está activo y lo que vence pronto.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Fundacion y Auth** - Scaffolding de Laravel + Inertia + Vue 3, autenticacion, roles admin/cliente e invitacion por email (completed 2026-03-25)
- [x] **Phase 2: CRM de Clientes** - CRUD completo de clientes con todos sus campos, lista y vista de detalle (completed 2026-03-25)
- [x] **Phase 3: Tareas y Kanban** - Creacion y edicion de tareas, tablero Kanban por cliente con drag-and-drop y vista global (completed 2026-03-20)
- [x] **Phase 4: Facturacion** - Registro manual de cobros en ARS, estados y dashboard mensual de facturacion (completed 2026-03-25)
- [ ] **Phase 5: Presupuestos y PDF** - Constructor de presupuestos con items, estados y generacion de PDF al marcar como Enviado
- [ ] **Phase 6: Portal del Cliente** - Vistas de solo lectura para el cliente: tareas, presupuestos, facturacion y resumen personal
- [ ] **Phase 7: Dashboard del Admin** - Vista principal del admin con tareas activas y proximas a vencer

## Phase Details

### Phase 1: Fundacion y Auth
**Goal**: El sistema tiene autenticacion funcional con dos roles separados y flujo de invitacion -- base segura sobre la que construir todos los modulos
**Depends on**: Nothing (first phase)
**Requirements**: AUTH-01, AUTH-02, AUTH-03, AUTH-04, AUTH-05
**Success Criteria** (what must be TRUE):
  1. El admin puede iniciar sesion con email y contrasena y la sesion persiste al recargar el navegador
  2. El admin puede enviar un link de invitacion a un email y el cliente puede usarlo para definir su contrasena y acceder al portal
  3. El admin ve el layout de admin y el cliente ve el layout de portal -- nunca se intercambian
  4. El usuario puede cerrar sesion desde cualquier pagina y queda completamente desautenticado
  5. Una URL de invitacion usada o expirada es rechazada con mensaje de error
**Plans:** 4/4 plans complete
Plans:
- [x] 01-01-PLAN.md -- Laravel scaffold + Breeze + DB config + Role enum + migrations + seeder + test stubs
- [x] 01-02-PLAN.md -- Admin auth flow (middleware, layouts, role redirect, shared data, tests)
- [x] 01-03-PLAN.md -- Invitation system (controller, signed URLs, accept form, client registration)
- [x] 01-04-PLAN.md -- Final wiring (disable registration, nav links, visual verification)

### Phase 2: CRM de Clientes
**Goal**: El admin puede crear, ver, editar y eliminar clientes con todos sus campos operativos
**Depends on**: Phase 1
**Requirements**: CLIE-01, CLIE-02, CLIE-03, CLIE-04
**Success Criteria** (what must be TRUE):
  1. El admin puede crear un cliente con nombre, empresa, email, telefono, stack tecnologico, estado, notas internas y fecha de inicio
  2. El admin puede ver la lista completa de clientes con su estado visible
  3. El admin puede abrir la pagina de detalle de un cliente y ver todos sus campos
  4. El admin puede editar cualquier campo de un cliente y los cambios persisten
  5. El admin puede eliminar un cliente y este desaparece de la lista
**Plans:** 3/3 plans complete
Plans:
- [x] 02-01-PLAN.md -- Migrations (clients table + client_id FKs) + Client model + User/Invitation model surgery + test stubs
- [x] 02-02-PLAN.md -- ClientController CRUD + Index/Create/Edit/Show Vue pages + 10 feature tests
- [x] 02-03-PLAN.md -- "Invitar al portal" wiring + InvitationController client_id propagation + AdminLayout nav + invitation tests

### Phase 3: Tareas y Kanban
**Goal**: El admin puede gestionar tareas vinculadas a clientes y ver el estado de todo el trabajo en curso en un tablero Kanban
**Depends on**: Phase 2
**Requirements**: TASK-01, TASK-02, TASK-03, TASK-04, TASK-05
**Success Criteria** (what must be TRUE):
  1. El admin puede crear una tarea vinculada a un cliente con titulo, descripcion, prioridad y fecha limite opcional
  2. El admin puede arrastrar una tarea entre columnas (Backlog / En progreso / En revision / Finalizado) en el Kanban por cliente y el cambio persiste
  3. Si el drag-and-drop falla, la tarjeta vuelve a su posicion original y aparece un mensaje de error
  4. El admin puede ver el Kanban global con tareas de todos los clientes en una sola vista
  5. El admin puede filtrar o buscar tareas por titulo, estado o prioridad
**Plans:** 3/3 plans complete
Plans:
- [ ] 03-01-PLAN.md -- Tasks migration + Task model + enums + factory + test stubs
- [ ] 03-02-PLAN.md -- TaskController (CRUD + updateStatus) + Kanban Vue page with drag-drop + modals
- [ ] 03-03-PLAN.md -- Filter bar + search + Ver Kanban link + Tareas nav + filter tests

### Phase 4: Facturacion
**Goal**: El admin puede registrar cobros manualmente y tener visibilidad inmediata de la deuda pendiente y lo cobrado en el mes
**Depends on**: Phase 2
**Requirements**: BILL-01, BILL-02, BILL-03, BILL-04
**Success Criteria** (what must be TRUE):
  1. El admin puede registrar un cobro con cliente, concepto, monto ARS, fecha de emision, fecha de pago y estado
  2. Un cobro puede tener estado pendiente, pagado o vencido, y el admin puede cambiar ese estado
  3. El dashboard de facturacion muestra el total cobrado en el mes actual y la deuda pendiente total en ARS
  4. El admin puede filtrar cobros por cliente o por estado y los resultados se actualizan correctamente
**Plans:** 3/3 plans complete
Plans:
- [x] 04-01-PLAN.md -- billings migration + BillingStatus enum + Billing model + BillingFactory + test stubs
- [x] 04-02-PLAN.md -- BillingController CRUD + Form Requests (required_if) + Index/Create/Edit Vue pages + 9 tests
- [x] 04-03-PLAN.md -- Dashboard summary cards + Facturacion nav + client billing section + 6 dashboard tests

### Phase 5: Presupuestos y PDF
**Goal**: El admin puede crear presupuestos con items y generar un PDF descargable al enviarlos al cliente
**Depends on**: Phase 2
**Requirements**: QUOT-01, QUOT-02, QUOT-03
**Success Criteria** (what must be TRUE):
  1. El admin puede crear un presupuesto vinculado a un cliente con multiples items (descripcion + precio) y el total se calcula automaticamente
  2. Un presupuesto puede pasar por los estados Borrador, Enviado, Aceptado y Rechazado -- solo transiciones validas son permitidas
  3. Al marcar un presupuesto como "Enviado" se genera un PDF descargable con los items, precios y total en ARS
  4. El PDF se descarga correctamente con caracteres en espanol y montos ARS sin corrupcion
**Plans:** 2/3 plans executed
Plans:
- [ ] 05-01-PLAN.md -- Install dompdf, Quote/QuoteItem migrations + models + enum + factories + test stubs
- [ ] 05-02-PLAN.md -- QuoteController CRUD + Form Requests + Index/Create/Edit Vue pages + nav link + 10 tests
- [ ] 05-03-PLAN.md -- PDF Blade template + QuoteController@pdf + 3 PDF tests + visual verification

### Phase 6: Portal del Cliente
**Goal**: El cliente autenticado puede ver el estado de su trabajo, presupuestos y facturacion -- solo lectura, sin acceso a datos de otros clientes
**Depends on**: Phase 3, Phase 4, Phase 5
**Requirements**: PORT-01, PORT-02, PORT-03, PORT-04
**Success Criteria** (what must be TRUE):
  1. El cliente ve la lista de sus tareas activas con titulo, estado y fecha limite -- no puede modificarlas
  2. El cliente ve sus presupuestos y el estado de cada uno -- no puede modificarlos
  3. El cliente ve su estado de facturacion (que debe o ha pagado) en ARS -- no puede modificarlo
  4. El portal del cliente nunca muestra notas internas, datos de otros clientes ni montos globales del admin
  5. El cliente ve un dashboard personal con resumen de sus tareas, presupuestos y facturacion
**Plans**: TBD

### Phase 7: Dashboard del Admin
**Goal**: El admin tiene una vista central donde de un vistazo puede ver que tareas estan activas y cuales vencen pronto
**Depends on**: Phase 3, Phase 4, Phase 5
**Requirements**: DASH-01
**Success Criteria** (what must be TRUE):
  1. La vista principal del admin muestra las tareas actualmente en estado "En progreso" agrupadas o listadas visiblemente
  2. Las tareas con fecha limite proxima aparecen destacadas o separadas del resto
  3. El dashboard carga con datos reales de todos los modulos sin errores N+1 perceptibles
**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Fundacion y Auth | 4/4 | Complete   | 2026-03-25 |
| 2. CRM de Clientes | 3/3 | Complete   | 2026-03-25 |
| 3. Tareas y Kanban | 3/3 | Complete   | 2026-03-20 |
| 4. Facturacion | 3/3 | Complete   | 2026-03-25 |
| 5. Presupuestos y PDF | 2/3 | In Progress|  |
| 6. Portal del Cliente | 0/TBD | Not started | - |
| 7. Dashboard del Admin | 0/TBD | Not started | - |
