---
phase: 4
slug: facturacion
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-20
---

# Phase 4 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (Laravel 12) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter=Billing` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~15 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter=Billing`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 15 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 4-01-01 | 01 | 0 | BILL-01 | migration | `php artisan migrate --env=testing && php artisan test --filter=BillingTest` | ✅ W0 | ⬜ pending |
| 4-01-02 | 01 | 0 | BILL-01 | feature | `php artisan test --filter=BillingTest` | ✅ W0 | ⬜ pending |
| 4-02-01 | 02 | 1 | BILL-01,BILL-02 | feature | `php artisan test --filter=BillingCrudTest` | ✅ W0 | ⬜ pending |
| 4-02-02 | 02 | 1 | BILL-03,BILL-04 | feature | `php artisan test --filter=BillingDashboardTest` | ✅ W0 | ⬜ pending |
| 4-03-01 | 03 | 2 | BILL-02,BILL-03 | feature | `php artisan test --filter=BillingTest` | ✅ W0 | ⬜ pending |
| 4-03-02 | 03 | 2 | BILL-04 | build | `npm run build && php artisan test` | ✅ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/Billing/BillingTest.php` — stubs para BILL-01 (create billing, fields validation)
- [ ] `tests/Feature/Billing/BillingCrudTest.php` — stubs para BILL-01, BILL-02 (index, store, update, destroy)
- [ ] `tests/Feature/Billing/BillingDashboardTest.php` — stubs para BILL-03, BILL-04 (resumen mensual, deuda pendiente)

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Formato de monto ARS en tabla (punto/coma) | BILL-01 | Requiere inspección visual del formato numérico | 1. Crear cobro con monto 1500.50 2. Verificar display en tabla |
| Resumen mensual muestra valores correctos | BILL-03 | Requiere verificación visual del dashboard | 1. Crear cobros en mes actual 2. Verificar totales en tarjetas del dashboard |
| Modal de confirmación antes de borrar cobro | BILL-02 | Requiere interacción visual | 1. Ir a /billing/{id}/edit 2. Click Eliminar 3. Verificar modal aparece |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 15s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
