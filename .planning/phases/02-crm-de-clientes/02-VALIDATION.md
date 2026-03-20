---
phase: 2
slug: crm-de-clientes
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-20
---

# Phase 2 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (Laravel 12, same as Phase 1) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter=Client` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~12 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter=Client`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 15 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 2-01-01 | 01 | 0 | CLIE-01 | feature | `php artisan test --filter=ClientTest` | ✅ W0 | ⬜ pending |
| 2-01-02 | 01 | 0 | CLIE-01 | migration | `php artisan migrate --env=testing && php artisan test --filter=ClientTest` | ✅ W0 | ⬜ pending |
| 2-02-01 | 02 | 1 | CLIE-01,CLIE-02 | feature | `php artisan test --filter=ClientCrudTest` | ✅ W0 | ⬜ pending |
| 2-02-02 | 02 | 1 | CLIE-03,CLIE-04 | feature | `php artisan test --filter=ClientCrudTest` | ✅ W0 | ⬜ pending |
| 2-03-01 | 03 | 2 | CLIE-01 | feature | `php artisan test --filter=ClientInvitationTest` | ✅ W0 | ⬜ pending |
| 2-03-02 | 03 | 2 | CLIE-02 | feature | `php artisan test --filter=ClientCrudTest` | ✅ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/Clients/ClientTest.php` — stubs for CLIE-01 (create client with all fields)
- [ ] `tests/Feature/Clients/ClientCrudTest.php` — stubs for CLIE-02, CLIE-03, CLIE-04 (list, edit, delete)
- [ ] `tests/Feature/Clients/ClientInvitationTest.php` — stubs for "Invitar al portal" flow

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Tabla de clientes se ve correctamente en el browser con filtro de estado | CLIE-02 | Requiere inspección visual del UI Inertia | 1. Login admin 2. Ir a /clients 3. Verificar tabla con columnas + dropdown filtro |
| Modal de confirmación aparece antes de eliminar | CLIE-04 | Requiere interacción visual con Vue component | 1. Click "Eliminar" en un cliente 2. Verificar modal con nombre del cliente 3. Confirmar y verificar desaparece |
| Botón "Invitar al portal" en detalle del cliente genera link | CLIE-01 | Flujo UI complejo con estado de respuesta | 1. Crear cliente 2. Ir a /clients/{id} 3. Click "Invitar al portal" 4. Verificar link aparece |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 15s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
