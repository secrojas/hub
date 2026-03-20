# Requirements: Hub

**Defined:** 2026-03-19
**Core Value:** El admin puede ver de un vistazo qué tareas están activas en este momento y qué vence pronto — todo lo demás es soporte a esa claridad operativa.

---

## v1 Requirements

### Authentication

- [x] **AUTH-01**: El admin puede iniciar sesión con email y contraseña
- [x] **AUTH-02**: El admin puede invitar a un cliente mediante un link firmado temporario enviado por email
- [x] **AUTH-03**: El cliente puede aceptar la invitación, definir su contraseña y acceder al portal
- [x] **AUTH-04**: La sesión persiste al recargar el navegador
- [x] **AUTH-05**: El usuario puede hacer logout desde cualquier página

### Clientes

- [x] **CLIE-01**: El admin puede crear, editar y eliminar clientes
- [x] **CLIE-02**: Cada cliente tiene: nombre, empresa, email, teléfono, stack tecnológico, estado (activo / potencial / pausado), notas internas y fecha de inicio
- [x] **CLIE-03**: El admin puede ver la lista de todos los clientes
- [x] **CLIE-04**: El admin puede ver la página de detalle de un cliente individual

### Tareas

- [ ] **TASK-01**: El admin puede crear tareas vinculadas a un cliente con: título, descripción, prioridad y fecha límite opcional
- [ ] **TASK-02**: El admin puede editar y eliminar tareas
- [ ] **TASK-03**: El admin puede ver el tablero Kanban por cliente con drag-and-drop (columnas: Backlog / En progreso / En revisión / Finalizado)
- [ ] **TASK-04**: El admin puede ver una vista global del Kanban con tareas de todos los clientes
- [ ] **TASK-05**: El admin puede filtrar o buscar tareas por título, estado o prioridad

### Facturación

- [ ] **BILL-01**: El admin puede registrar manualmente un cobro (cliente, concepto, monto ARS, fecha de emisión, fecha de pago, estado)
- [ ] **BILL-02**: Los cobros tienen estados: pendiente / pagado / vencido
- [ ] **BILL-03**: El admin puede ver un dashboard mensual de facturación (total cobrado en el mes, deuda pendiente total)
- [ ] **BILL-04**: El admin puede filtrar cobros por cliente o por estado

### Presupuestos

- [ ] **QUOT-01**: El admin puede crear presupuestos con ítems (descripción + precio) vinculados a un cliente, con total calculado automáticamente
- [ ] **QUOT-02**: Los presupuestos tienen estados: Borrador / Enviado / Aceptado / Rechazado
- [ ] **QUOT-03**: Al marcar un presupuesto como "Enviado" se genera un PDF descargable

### Portal del Cliente

- [ ] **PORT-01**: El cliente puede ver la lista de sus tareas activas (título, estado, fecha límite) — solo lectura
- [ ] **PORT-02**: El cliente puede ver sus presupuestos y el estado de cada uno
- [ ] **PORT-03**: El cliente puede ver su estado de facturación (qué debe o ha pagado)
- [ ] **PORT-04**: El portal tiene un dashboard personal con resumen de tareas, presupuestos y facturación

### Dashboard del Admin

- [ ] **DASH-01**: La vista principal del admin muestra las tareas activas (en progreso) y las próximas a vencer

---

## v2 Requirements

### Presupuestos

- **QUOT-04**: Historial de presupuestos filtrable por cliente
- **QUOT-05**: Vista de presupuesto compartible por link público (para el cliente, sin login)

### Notificaciones

- **NOTF-01**: El cliente recibe email cuando le llega un nuevo presupuesto
- **NOTF-02**: El cliente recibe email cuando una tarea cambia de estado
- **NOTF-03**: El admin recibe email cuando un cliente acepta o rechaza un presupuesto

### Administración

- **ADM-01**: Múltiples admins / modo equipo
- **ADM-02**: Roles adicionales con permisos configurables

---

## Out of Scope

| Feature | Reason |
|---------|--------|
| Integración AFIP / factura electrónica | Complejidad legal/técnica fuera del scope de un CRM de gestión |
| Pasarelas de pago (MercadoPago, Stripe) | Registro manual suficiente para el volumen freelance |
| Time tracking / timesheets | Módulo independiente, no solicitado |
| Facturas recurrentes / suscripciones | Complejidad innecesaria para v1 |
| Chat / mensajería interna | Tiempo real agrega complejidad sin valor core |
| App móvil / PWA | Web-first, scope futuro |
| Múltiples monedas | Solo ARS en v1 |
| Kanban interactivo en portal del cliente | El cliente solo necesita ver estado, no arrastrar columnas |
| Reportes / analytics avanzados | Diferido a v2+ |

---

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| AUTH-01 | Phase 1 | Complete |
| AUTH-02 | Phase 1 | Complete |
| AUTH-03 | Phase 1 | Complete |
| AUTH-04 | Phase 1 | Complete |
| AUTH-05 | Phase 1 | Complete |
| CLIE-01 | Phase 2 | Complete |
| CLIE-02 | Phase 2 | Complete |
| CLIE-03 | Phase 2 | Complete |
| CLIE-04 | Phase 2 | Complete |
| TASK-01 | Phase 3 | Pending |
| TASK-02 | Phase 3 | Pending |
| TASK-03 | Phase 3 | Pending |
| TASK-04 | Phase 3 | Pending |
| TASK-05 | Phase 3 | Pending |
| BILL-01 | Phase 4 | Pending |
| BILL-02 | Phase 4 | Pending |
| BILL-03 | Phase 4 | Pending |
| BILL-04 | Phase 4 | Pending |
| QUOT-01 | Phase 5 | Pending |
| QUOT-02 | Phase 5 | Pending |
| QUOT-03 | Phase 5 | Pending |
| PORT-01 | Phase 6 | Pending |
| PORT-02 | Phase 6 | Pending |
| PORT-03 | Phase 6 | Pending |
| PORT-04 | Phase 6 | Pending |
| DASH-01 | Phase 7 | Pending |

**Coverage:**
- v1 requirements: 26 total
- Mapped to phases: 26
- Unmapped: 0 ✓

---
*Requirements defined: 2026-03-19*
*Last updated: 2026-03-19 — added DASH-01 (admin dashboard) during roadmap creation; mapped to Phase 7*
