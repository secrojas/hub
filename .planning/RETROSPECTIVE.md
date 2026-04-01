# Retrospective: Hub

---

## Milestone: v1.0 — MVP

**Shipped:** 2026-04-01
**Phases:** 7 | **Plans:** 20 | **Timeline:** 13 days

### What Was Built

1. Auth completa con roles admin/cliente, invitación por link firmado, registro público deshabilitado
2. CRM de clientes con CRUD completo y vista de detalle integrada (tareas + presupuestos + facturación)
3. Tablero Kanban global con drag & drop (vue-draggable-plus), filtros por 4 dimensiones con debounce
4. Facturación manual en ARS con dashboard mensual (cobrado / pendiente / vencidos)
5. Presupuestos con ítems, estados con transiciones válidas, PDF con UTF-8 y formato ARS correcto
6. Portal del cliente de solo lectura con dashboard personal + 3 secciones de datos
7. Dashboard del admin con tareas activas + vencen pronto, coloring por urgencia, estado inline

### What Worked

- **Stack familiar acelera ejecución**: Laravel + Inertia + Vue 3 sin fricción — el desarrollador lo conoce y eso se nota en la velocidad de cada plan
- **Inertia patterns son consistentes**: `router.get/put` con `preserveState/Scroll`, `useForm`, `back()` — una vez establecidos, se replicaron en todos los módulos sin sorpresas
- **Server-side filtering**: usar query params + `$request->only()` + `filtros` prop devueltos — patrón limpio y sin estado client-side extra
- **PDF server-side**: `streamDownload` con barryvdh/laravel-dompdf — evitó completamente dependencias de JS del cliente

### What Was Inefficient

- **vue-draggable-plus API no documentada claramente**: se asumió API de vue-draggable v2 (`@change` + `event.added.element`) — la API real es `@add` + `event.data`. Costó una sesión de debug. Lección: siempre verificar el source de librerías menos conocidas antes de escribir el handler
- **Phase 6 ROADMAP desactualizado**: la fase quedó marcada como 1/2 en el roadmap aunque ambos planes estaban completos — pequeño overhead de limpieza al hacer el milestone

### Patterns Established

- **Sentinel pattern para modals**: `ref(null)` — null = cerrado, valor = abierto con datos pre-cargados
- **Collection grouping con enum comparison**: `$t->estado === TaskStatus::Backlog` (no string) — los cast enums no matchean strings en Collection::filter
- **Two-query dashboard**: `vencenProonto` primero con ventana temporal, `enProgreso` usa `whereNotIn` para excluir esos IDs
- **fecha_limite display**: siempre formatear con UTC methods (`getUTCDate/Month/FullYear`) — el cast `'date'` de Laravel serializa como ISO 8601 con timezone
- **nullOnDelete en FKs financieros**: billings/quotes usan `nullOnDelete` en `client_id` — registros financieros sobreviven eliminación de cliente
- **DejaVu Sans obligatorio en dompdf**: el font por defecto silently drops ñ/á/é

### Key Lessons

- Verificar el API real de librerías menos populares (vue-draggable-plus vs vuedraggable) antes de usarlas — no asumir compatibilidad con versiones anteriores
- Laravel 12 es compatible con los patterns de Breeze/Inertia de Laravel 11 — sin cambios reales en el workflow
- `(float)` cast en `sum()` de Eloquent — evita comparación estricta int/float en tests cuando el resultado es un número redondo
- `fecha_pago: null` (no string vacío) en `useForm` para que `required_if` valide correctamente

### Cost Observations

- Sessions: ~20 (una por plan aproximadamente)
- Notable: uso de yolo mode aceleró significativamente la ejecución de planes sin gates de confirmación

---

## Cross-Milestone Trends

| Milestone | Phases | Plans | Timeline | LOC |
|-----------|--------|-------|----------|-----|
| v1.0 MVP  | 7      | 20    | 13 days  | ~6,100 PHP+Vue |
