---
phase: 5
slug: presupuestos-y-pdf
status: draft
nyquist_compliant: false
wave_0_complete: false
created: 2026-03-30
---

# Phase 5 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit (via Pest) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter QuoteTest` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~30 seconds |

---

## Sampling Rate

- **After every task commit:** Run `php artisan test --filter QuoteTest`
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 30 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------|-------------------|-------------|--------|
| 5-01-01 | 01 | 0 | QUOT-01 | stub | `php artisan test --filter QuoteTest` | ❌ W0 | ⬜ pending |
| 5-01-02 | 01 | 1 | QUOT-01 | feature | `php artisan test --filter QuoteTest` | ✅ | ⬜ pending |
| 5-01-03 | 01 | 1 | QUOT-01 | feature | `php artisan test --filter QuoteTest` | ✅ | ⬜ pending |
| 5-02-01 | 02 | 1 | QUOT-02 | feature | `php artisan test --filter QuoteTest` | ✅ | ⬜ pending |
| 5-02-02 | 02 | 1 | QUOT-02 | feature | `php artisan test --filter QuoteTest` | ✅ | ⬜ pending |
| 5-03-01 | 03 | 2 | QUOT-03 | feature | `php artisan test --filter QuoteTest` | ✅ | ⬜ pending |
| 5-03-02 | 03 | 2 | QUOT-03 | manual | See manual verifications | — | ⬜ pending |

*Status: ⬜ pending · ✅ green · ❌ red · ⚠️ flaky*

---

## Wave 0 Requirements

- [ ] `tests/Feature/QuoteTest.php` — stubs for QUOT-01, QUOT-02, QUOT-03
- [ ] `database/factories/QuoteFactory.php` — with state methods: `borrador()`, `enviado()`, `aceptado()`, `rechazado()`
- [ ] `composer require barryvdh/laravel-dompdf` — PDF library install

*Wave 0 must be complete before any feature tasks run.*

---

## Manual-Only Verifications

| Behavior | Requirement | Why Manual | Test Instructions |
|----------|-------------|------------|-------------------|
| PDF renders Spanish characters (ñ, á, é) without corruption | QUOT-03 / SC-4 | Character encoding in browser PDF viewer cannot be asserted via PHPUnit | 1. Create presupuesto with título "Diseño Landing Page" 2. Mark as Enviado 3. Download PDF 4. Open in PDF viewer 5. Verify ñ, á, é characters appear correctly |
| PDF ARS amounts display correctly ($ 50.000,00 format) | QUOT-03 | Format rendering requires visual inspection | 1. Create presupuesto with item precio=50000 2. Download PDF 3. Verify format shows "$ 50.000,00" with comma as decimal and period as thousands separator |

---

## Validation Sign-Off

- [ ] All tasks have `<automated>` verify or Wave 0 dependencies
- [ ] Sampling continuity: no 3 consecutive tasks without automated verify
- [ ] Wave 0 covers all MISSING references
- [ ] No watch-mode flags
- [ ] Feedback latency < 30s
- [ ] `nyquist_compliant: true` set in frontmatter

**Approval:** pending
