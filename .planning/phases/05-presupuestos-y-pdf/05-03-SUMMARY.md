---
phase: 05-presupuestos-y-pdf
plan: 03
subsystem: pdf
tags: [dompdf, barryvdh, blade, pdf, utf8, dejavusans, ars]

# Dependency graph
requires:
  - phase: 05-01
    provides: Quote model, QuoteItem model, QuoteStatus enum, QuoteFactory with state methods
  - phase: 05-02
    provides: QuoteController CRUD + updateEstado, routes including quotes.pdf, 10 passing tests

provides:
  - PDF download endpoint at GET /quotes/{id}/pdf via QuoteController@pdf
  - Blade template resources/views/pdf/quote.blade.php with DejaVu Sans, UTF-8, ARS formatting
  - 403 guard for borrador quotes on PDF endpoint
  - Filename pattern presupuesto-{id}-{slug}.pdf via Str::slug
  - 3 QUOT-03 tests: pdf_downloads, pdf_forbidden_borrador, pdf_correct_filename

affects: [06-portal-cliente, any feature using Quote PDF generation]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "PDF generation via Pdf::loadView + response()->streamDownload for sync download"
    - "DejaVu Sans mandatory for dompdf UTF-8/Latin character support"
    - "ARS currency format: number_format($val, 2, ',', '.') — comma decimal, period thousands"
    - "Filename slug: Str::slug($quote->titulo) — URL-safe, lowercase, hyphenated"

key-files:
  created:
    - resources/views/pdf/quote.blade.php
  modified:
    - app/Http/Controllers/QuoteController.php
    - tests/Feature/QuoteTest.php

key-decisions:
  - "DejaVu Sans font used in Blade PDF template — bundled with dompdf, only font with full UTF-8/Latin support; default font fails on ñ, á, é"
  - "streamDownload used instead of download() for PDF — avoids file system write, memory-efficient for generated content"

patterns-established:
  - "Blade PDF template: always include meta charset=utf-8 and font-family DejaVu Sans for Spanish char support"
  - "PDF guard pattern: abort_if($quote->estado === QuoteStatus::Borrador, 403) before any PDF render"

requirements-completed: [QUOT-03]

# Metrics
duration: 3min
completed: 2026-03-30
---

# Phase 05 Plan 03: PDF Generation Summary

**PDF download for presupuestos via barryvdh/laravel-dompdf — sync streamDownload with DejaVu Sans, UTF-8 charset, ARS number_format, and 403 guard on borrador quotes**

## Performance

- **Duration:** 3 min
- **Started:** 2026-03-30T16:22:16Z
- **Completed:** 2026-03-30T16:25:00Z
- **Tasks:** 1 (+ 1 auto-approved checkpoint)
- **Files modified:** 3

## Accomplishments
- Created Blade PDF template with DejaVu Sans font (UTF-8/Latin support), srojasweb header, client data, items table with ARS currency format, total, notas section
- Added QuoteController@pdf with borrador guard (403), Pdf::loadView, streamDownload, and slug-based filename
- Implemented all 3 QUOT-03 test stubs — 13/13 QuoteTest pass, 0 incomplete

## Task Commits

Each task was committed atomically:

1. **Task 1: Blade PDF template, QuoteController@pdf method, and QUOT-03 tests** - `78fd68a` (feat)
2. **Task 2: Visual verification checkpoint** - auto-approved (no commit — no code changes)

**Plan metadata:** (docs commit — see below)

## Files Created/Modified
- `resources/views/pdf/quote.blade.php` - HTML template for dompdf: DejaVu Sans, UTF-8, client info, items table, ARS formatting, total, notas
- `app/Http/Controllers/QuoteController.php` - Added pdf() method: borrador guard, Pdf::loadView, streamDownload, presupuesto-{id}-{slug} filename
- `tests/Feature/QuoteTest.php` - Replaced 3 markTestIncomplete stubs with full QUOT-03 implementations

## Decisions Made
- DejaVu Sans is mandatory for dompdf — it is the only bundled font with full UTF-8/Latin coverage. The default font silently drops ñ, á, é characters.
- `response()->streamDownload()` used rather than writing a temp file — cleaner, no disk I/O for PDF generation.

## Deviations from Plan

None — plan executed exactly as written.

### Deferred Issues (out of scope)

**BillingDashboardTest::cobrado_mes_excludes_other_months** — pre-existing failure in Phase 04 code, unrelated to this plan. Not caused by any changes here (verified by stash test). Logged for future investigation.

## Issues Encountered

None — all acceptance criteria passed on first run.

## User Setup Required

None — no external service configuration required.

## Next Phase Readiness
- Phase 05 complete: Quote CRUD, state management, PDF download all functional
- All 13 QuoteTest pass; QUOT-01, QUOT-02, QUOT-03 requirements satisfied
- Phase 06 (portal-cliente) can proceed — it will need Quote model and PDF endpoint

## Self-Check: PASSED

- FOUND: resources/views/pdf/quote.blade.php
- FOUND: app/Http/Controllers/QuoteController.php (pdf method)
- FOUND: tests/Feature/QuoteTest.php (3 QUOT-03 tests implemented)
- FOUND: .planning/phases/05-presupuestos-y-pdf/05-03-SUMMARY.md
- FOUND commit: 78fd68a (feat: PDF generation)
- FOUND commit: 5a523dd (docs: plan metadata)

---
*Phase: 05-presupuestos-y-pdf*
*Completed: 2026-03-30*
