# Features Research: Hub — Plataforma de gestión de clientes freelance

**Domain:** Freelance client management platform
**Generated:** 2026-03-19

---

## Table Stakes (Must Have — Users Expect These)

### Authentication & Access Control
- Login con email/contraseña (HIGH complexity: 2)
- Sistema de invitación por email para clientes (complexity: 2)
- Sesiones persistentes (complexity: 1)
- Dos roles: admin / cliente (complexity: 2)

### Client CRM
- CRUD completo de clientes (complexity: 2)
- Campos estándar: nombre, empresa, email, teléfono (complexity: 1)
- Campo de estado del cliente: activo / potencial / pausado (complexity: 1)
- Notas internas (visibles solo para admin) (complexity: 1)

### Task Management
- Creación y edición de tareas vinculadas a cliente (complexity: 2)
- Tablero Kanban por cliente (columnas configuradas) (complexity: 3)
- Estados de tarea: Backlog / En progreso / En revisión / Finalizado (complexity: 2)
- Campos de tarea: título, descripción, prioridad, fecha límite (complexity: 1)

### Invoicing / Billing
- Registro manual de cobros (complexity: 2)
- Estados: pendiente / pagado / vencido (complexity: 1)
- Moneda única (ARS) (complexity: 1)
- Dashboard con resumen mensual y deuda total (complexity: 2)

### Quotes / Budgets
- Constructor de presupuestos con ítems (descripción + precio) (complexity: 3)
- Estados: Borrador / Enviado / Aceptado / Rechazado (complexity: 2)
- Generación de PDF al enviar (complexity: 3)

### Client Portal
- Vista de tareas activas del cliente (lista, solo lectura) (complexity: 2)
- Vista de presupuestos del cliente (complexity: 2)
- Vista de estado de facturación (complexity: 2)

---

## Differentiators (Competitive Advantage — Specific to This Project)

- **Campo "stack tecnológico" en cliente** — útil para un dev freelance, raro en CRMs genéricos (complexity: 1)
- **Vista global del Kanban** — todas las tareas de todos los clientes en un solo tablero (complexity: 3)
- **Formato ARS nativo** — no tener que configurar moneda extraña, formato local desde el inicio (complexity: 1)
- **Onboarding por invitación** — flujo simple, sin registro público (complexity: 2)
- **Notas internas por cliente** — separación clara entre lo que ve el cliente y lo que no (complexity: 1)
- **Dashboard centrado en tareas activas** — no finanzas primero, sino flujo de trabajo (complexity: 2)

---

## Anti-Features (Deliberate Exclusions)

| Feature | Reason for Exclusion |
|---------|---------------------|
| Integración AFIP / factura electrónica | Alta complejidad legal/técnica, fuera del scope de un CRM de gestión |
| Pasarelas de pago (MercadoPago, Stripe) | Registro manual es suficiente para el volumen freelance |
| Time tracking / timesheets | Módulo independiente, no solicitado |
| Facturas recurrentes / suscripciones | Complejidad innecesaria para v1 |
| Chat / mensajería interna | Agrega complejidad de tiempo real sin valor core |
| Notificaciones automáticas al cliente | Diferido a v2 para simplificar v1 |
| App móvil / PWA | Web-first, scope futuro |
| Múltiples monedas | Solo ARS en v1 |
| Múltiples admins / equipo | Un solo admin en v1 |
| Kanban interactivo en portal del cliente | El cliente no necesita arrastrar columnas, solo ver estado |
| Integración con GitHub / Jira / Trello | Complejidad de integración no solicitada |
| CRM avanzado (leads, pipeline de ventas) | Fuera del modelo freelance de este usuario |
| Reportes / analytics avanzados | Diferido a v2 |

---

## Feature Dependency Tree (Critical Path)

```
Auth & Roles
    └── Client CRUD
            ├── Tasks / Kanban (por cliente)
            │       └── Vista global del Kanban
            ├── Invoicing (por cliente)
            │       └── Dashboard de facturación
            ├── Quotes / Presupuestos (por cliente)
            │       └── PDF generation
            └── Client Portal
                    ├── (depende de Tasks)
                    ├── (depende de Quotes)
                    └── (depende de Invoicing)

Admin Dashboard (depende de todos los módulos anteriores)
```

---

## MVP Recommendation

Build order aligned with core value ("ver qué tareas están activas"):

1. Auth + roles + invitación
2. Clientes CRUD
3. Tareas + Kanban (por cliente → global)
4. Facturación + dashboard
5. Presupuestos + PDF
6. Portal del cliente
7. Dashboard admin (agrega valor solo cuando hay datos de todos los módulos)
