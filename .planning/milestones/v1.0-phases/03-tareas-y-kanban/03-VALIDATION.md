---
phase: 3
slug: tareas-y-kanban
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-20
---

# Phase 3 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (Laravel 12) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter=Task` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~15 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter=Task`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 15 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 3-01-01 | 01 | 0 | TASK-01 | migration | `php artisan migrate --env=testing && php artisan test --filter=TaskTest` | ✅ W0 | ⬜ pending |
| 3-01-02 | 01 | 0 | TASK-01 | feature | `php artisan test --filter=TaskTest` | ✅ W0 | ⬜ pending |
| 3-02-01 | 02 | 1 | TASK-01,TASK-02 | feature | `php artisan test --filter=TaskCrudTest` | ✅ W0 | ⬜ pending |
| 3-02-02 | 02 | 1 | TASK-03,TASK-04 | feature | `php artisan test --filter=TaskKanbanTest` | ✅ W0 | ⬜ pending |
| 3-03-01 | 03 | 2 | TASK-04,TASK-05 | feature | `php artisan test --filter=TaskFilterTest` | ✅ W0 | ⬜ pending |
| 3-03-02 | 03 | 2 | TASK-02,TASK-03 | build | `npm run build && php artisan test` | ✅ W0 | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/Tasks/TaskTest.php` — stubs para TASK-01 (crear tarea con todos los campos)
- [ ] `tests/Feature/Tasks/TaskCrudTest.php` — stubs para TASK-01, TASK-02 (CRUD completo)
- [ ] `tests/Feature/Tasks/TaskKanbanTest.php` — stubs para TASK-02, TASK-03 (drag & drop, updateStatus, rollback)
- [ ] `tests/Feature/Tasks/TaskFilterTest.php` — stubs para TASK-04, TASK-05 (filtros, vista global)

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| Drag & drop entre columnas persiste visualmente | TASK-02 | Requiere interacción UI con vue-draggable-plus | 1. Abrir /tasks 2. Arrastrar tarea de Backlog a En Progreso 3. Recargar y verificar estado |
| Rollback visual si PUT falla | TASK-03 | Requiere simular error de red o desconexión | 1. Desconectar red 2. Arrastrar tarea 3. Verificar que vuelve a su columna original |
| Modal de crear/editar tarea abre y cierra correctamente | TASK-01 | Requiere inspección visual de Vue modal | 1. Click "Nueva tarea" 2. Verificar modal con todos los campos 3. Guardar y verificar card aparece |
| Badge de prioridad muestra colores correctos | TASK-04 | Visual — no hay assertion automatizable | Verificar: alta=rojo, media=amarillo, baja=verde |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 15s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
