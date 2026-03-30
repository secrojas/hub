# Phase 6: Portal del Cliente - Research

**Researched:** 2026-03-30
**Domain:** Laravel 12 + Inertia.js v2 + Vue 3 — read-only client portal with data isolation
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **Navegación**: Dashboard único en `/portal` — una sola página que combina el resumen y todas las secciones. Sin páginas separadas. Todo en un solo scroll.
- **PDF del cliente**: Nueva ruta `GET /portal/quotes/{id}/pdf` protegida por `EnsureIsClient` + ownership check. Link visible para todos los estados de presupuesto.
- **Dashboard PORT-04**: Bloque de tareas (conteo por estado), bloque de presupuestos (conteo por estado), bloque de facturación (monto pendiente + pagado en ARS). Tres bloques como cards al tope de `/portal`.
- **Scope de tareas PORT-01**: "Tareas activas" = TODAS las tareas del cliente (todos los estados). Lista con título, estado y fecha límite.
- **Listas PORT-02 y PORT-03**: Sin filtros en v1. Presupuestos: título, estado, monto total ARS, link PDF. Facturación: concepto, monto ARS, fecha emisión, estado.
- **Seguridad**: Queries filtradas por `client_id` del usuario autenticado. Admin no puede acceder a `/portal`. `clients.notas` nunca en props Inertia del portal.
- **Formato ARS**: `formatMonto()` con `Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' })` — patrón ya establecido.

### Claude's Discretion

- Diseño visual de los bloques del dashboard (colores de estado, iconos, layout de cards)
- Ordenamiento por defecto de las listas (fecha de creación descendente es razonable)
- Manejo del estado vacío por sección ("No tenés tareas aún", etc.)

### Deferred Ideas (OUT OF SCOPE)

- Filtros por estado en tareas/presupuestos/cobros
- Vista de detalle individual de presupuesto en el portal
- Notificaciones al cliente cuando llega un nuevo presupuesto (NOTF-01 — v2)
- Kanban interactivo para el cliente
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| PORT-01 | El cliente puede ver la lista de sus tareas activas (título, estado, fecha límite) — solo lectura | Verified: `tasks` table has `client_id` FK with `cascadeOnDelete`, `titulo`, `estado` (TaskStatus enum), `fecha_limite` (date cast). Query: `Task::where('client_id', $clientId)->latest()->get(['titulo','estado','fecha_limite'])` |
| PORT-02 | El cliente puede ver sus presupuestos y el estado de cada uno | Verified: `quotes` table has `client_id` FK (nullOnDelete), `titulo`, `estado` (QuoteStatus enum). Total calculado sumando `quote_items.precio`. PDF via `GET /portal/quotes/{id}/pdf` with ownership check. |
| PORT-03 | El cliente puede ver su estado de facturación (qué debe o ha pagado) | Verified: `billings` table has `client_id` FK (nullOnDelete), `concepto`, `monto` (decimal:2), `fecha_emision` (date cast), `estado` (BillingStatus enum). |
| PORT-04 | El portal tiene un dashboard personal con resumen de tareas, presupuestos y facturación | Established pattern from `BillingController::index()` — same aggregate approach applied per-client: task count by status, quote count by status, billing totals (pendiente + pagado). |
</phase_requirements>

---

## Summary

Phase 6 is a read-only aggregation phase — no new database migrations, no form validation, no write operations. The entire implementation fits in a single controller method (or a dedicated `PortalController`), a single Vue page, and one new route for PDF download.

All the data access patterns are established: queries filtered by `client_id` follow the exact same shape as `BillingController`'s `$summary` block. The `Quote::pdf()` method already exists — the portal PDF route is a thin wrapper that adds an ownership check before delegating to the same dompdf logic.

The main implementation risk is **data isolation**: every query in the portal must scope to `Auth::user()->client_id`. The secondary concern is the **PDF ownership check** — the new portal PDF route must reject requests where `$quote->client_id !== Auth::user()->client_id`, not just check that the user is a client.

**Primary recommendation:** Implement as `PortalController@index` (single method) + `PortalController@pdf` (PDF proxy with ownership check). One controller, one Vue page, two routes. Wave 0 creates the test file; Wave 1 implements backend + frontend together since the page has no sub-views.

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel (Inertia) | 12.x (installed) | Server-side data delivery via `Inertia::render()` | Established in all previous phases |
| Vue 3 (Composition API) | ^3.x | Portal/Index.vue component | Established — all pages use `<script setup>` |
| Inertia.js | v2.x | SPA navigation, props contract | Established — `usePage()`, `defineOptions({ layout })` |
| Tailwind CSS | v3.x | Utility styling | Established — AdminLayout and PortalLayout use it |
| barryvdh/laravel-dompdf | ^3.1 | PDF generation for portal PDF route | Already installed, used in Phase 5 |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `Intl.NumberFormat` | Browser native | ARS formatting via `formatMonto()` | Every monto display in portal |
| `Str::slug()` | Laravel | PDF filename generation | In `PortalController@pdf` (same as admin) |

### No New Installations Required
This phase introduces no new dependencies. All required packages are already installed.

---

## Architecture Patterns

### Recommended Project Structure

```
app/Http/Controllers/
└── PortalController.php        # New: index() + pdf()

resources/js/Pages/Portal/
└── Index.vue                   # Existing placeholder — implement fully

routes/web.php                  # Replace closure with PortalController@index
                                # Add GET /portal/quotes/{id}/pdf

tests/Feature/Portal/
└── PortalTest.php              # New: all PORT-xx tests
```

### Pattern 1: Single-Controller Portal

**What:** `PortalController` with two public methods — `index()` for the page, `pdf()` for download.
**When to use:** Portal is a single page with no sub-navigation. No reason for multiple controllers.

```php
// Source: established pattern from QuoteController::pdf() + BillingController::index()
class PortalController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;

        $tasks = Task::where('client_id', $clientId)
            ->latest()
            ->get(['id', 'titulo', 'estado', 'fecha_limite']);

        $quotes = Quote::where('client_id', $clientId)
            ->with('items')
            ->latest()
            ->get()
            ->map(fn ($q) => [
                'id'     => $q->id,
                'titulo' => $q->titulo,
                'estado' => $q->estado,
                'total'  => $q->items->sum('precio'),
            ]);

        $billings = Billing::where('client_id', $clientId)
            ->latest()
            ->get(['id', 'concepto', 'monto', 'fecha_emision', 'estado']);

        // Dashboard summary (PORT-04)
        $dashboard = [
            'tareas'        => $this->taskCounts($clientId),
            'presupuestos'  => $this->quoteCounts($clientId),
            'facturacion'   => $this->billingTotals($clientId),
        ];

        return Inertia::render('Portal/Index', compact('tasks', 'quotes', 'billings', 'dashboard'));
    }

    public function pdf(Quote $quote)
    {
        // Ownership check — client must own this quote
        abort_if($quote->client_id !== auth()->user()->client_id, 403);

        $quote->load(['client', 'items']);

        $pdf = Pdf::loadView('pdf.quote', ['quote' => $quote]);
        $slug = Str::slug($quote->titulo);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "presupuesto-{$quote->id}-{$slug}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
```

### Pattern 2: Dashboard Aggregate Counts

**What:** Private helper methods on the controller compute per-client counts/totals. Same technique as `BillingController`'s `$summary` block.
**When to use:** Any time you need aggregated data for a dashboard widget without a separate API endpoint.

```php
// Source: established pattern from BillingController::index() $summary block
private function taskCounts(int $clientId): array
{
    return Task::where('client_id', $clientId)
        ->get(['estado'])
        ->groupBy(fn ($t) => $t->estado->value)
        ->map->count()
        ->all();
}

private function quoteCounts(int $clientId): array
{
    return Quote::where('client_id', $clientId)
        ->get(['estado'])
        ->groupBy(fn ($q) => $q->estado->value)
        ->map->count()
        ->all();
}

private function billingTotals(int $clientId): array
{
    return [
        'pendiente' => (float) Billing::where('client_id', $clientId)
            ->where('estado', BillingStatus::Pendiente)
            ->sum('monto'),
        'pagado'    => (float) Billing::where('client_id', $clientId)
            ->where('estado', BillingStatus::Pagado)
            ->sum('monto'),
    ];
}
```

### Pattern 3: Route Registration

**What:** Replace the existing inline closure for `/portal` with a controller, add portal PDF route.
**When to use:** Routes belong inside the `['auth', 'client']` middleware group — already established.

```php
// routes/web.php — inside ['auth', 'client'] group
Route::get('/portal', [PortalController::class, 'index'])->name('portal');
Route::get('/portal/quotes/{quote}/pdf', [PortalController::class, 'pdf'])->name('portal.quotes.pdf');
```

### Pattern 4: Portal/Index.vue Structure

**What:** Single-page scroll with dashboard summary at top, then three read-only list sections.
**When to use:** This is the locked navigation decision from CONTEXT.md.

```vue
<!-- defineOptions({ layout: PortalLayout }) already present -->
<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { defineOptions } from 'vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
    tasks:     Array,
    quotes:    Array,
    billings:  Array,
    dashboard: Object,
})

function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}
</script>
```

### Anti-Patterns to Avoid

- **Exposing `clients.notas`:** Never include the `notas` field in Inertia props from portal routes. Only expose what the client needs.
- **Missing ownership check on PDF:** `EnsureIsClient` middleware only confirms role. It does NOT check that the quote belongs to the authenticated client. The `abort_if($quote->client_id !== auth()->user()->client_id, 403)` check in `pdf()` is mandatory.
- **Using `auth()->user()->client` relationship for filtering:** Prefer `auth()->user()->client_id` directly — it avoids an extra JOIN and is already available on the `users` table.
- **Paginating portal lists:** The CONTEXT.md decision is simple lists, no pagination. Client data volume for a freelancer does not justify it in v1.
- **Counting with PHP collection vs DB aggregate for `billingTotals`:** Use `sum('monto')` at the DB level (not `->get()->sum()`), consistent with `BillingController`'s established pattern.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| PDF generation | Custom HTML-to-PDF | `barryvdh/laravel-dompdf` (already installed) | Already in production for admin PDF. Same `Pdf::loadView()` + `streamDownload` pattern. |
| ARS formatting | Custom number formatter | `Intl.NumberFormat('es-AR', ...)` | Already used in `Billing/Index.vue`. Handles edge cases (decimals, currency symbol). |
| Role/access check | Custom role guard | `EnsureIsClient` middleware (already exists) | Already registered and applied to `['auth', 'client']` group. |
| Client-scoped queries | Custom authorization check | Direct `where('client_id', auth()->user()->client_id)` | Simple FK filter — established in `BillingController` and `TaskController`. |

**Key insight:** This phase is pure assembly — every building block already exists. The risk is omission (forgetting the ownership check on PDF, or accidentally exposing `notas`) rather than implementation complexity.

---

## Common Pitfalls

### Pitfall 1: Missing Ownership Check on Portal PDF
**What goes wrong:** A client with ID 2 downloads `GET /portal/quotes/5/pdf` — quote 5 belongs to client 1. The `EnsureIsClient` middleware only confirms they're a client, not that they own the quote.
**Why it happens:** Middleware checks role, not ownership. Without the explicit `abort_if($quote->client_id !== auth()->user()->client_id, 403)`, any client can download any quote PDF.
**How to avoid:** The ownership check is the FIRST line inside `PortalController::pdf()` after route-model binding resolves the quote.
**Warning signs:** Tests that only test "client can download their own PDF" — add a test that asserts a client CANNOT download another client's PDF.

### Pitfall 2: `client_id` null When User Has No Linked Client
**What goes wrong:** `auth()->user()->client_id` returns `null` if the user somehow has no linked client. All queries would return all records (WHERE client_id = null matches nothing, but if null is mishandled it could fallback to unscoped).
**Why it happens:** Phase 2 uses `nullOnDelete` on `users.client_id` — if a client is deleted, the user's `client_id` becomes null.
**How to avoid:** In `PortalController::index()`, add an early guard: `abort_unless(auth()->user()->client_id, 403)`. This is an edge case (admin portal is protected by `EnsureIsClient`) but good defensive practice.
**Warning signs:** Any query returning unexpected results in tests when using a user with null `client_id`.

### Pitfall 3: Enum Comparison in Collection Grouping
**What goes wrong:** `$tasks->groupBy('estado')` groups by the enum object, not its string value. Keys become `App\Enums\TaskStatus` instances, not `'backlog'`, `'en_progreso'` strings.
**Why it happens:** `estado` is cast to `TaskStatus` enum — the cast returns enum instances, not strings.
**How to avoid:** Use `->groupBy(fn ($t) => $t->estado->value)` — `.value` extracts the string. Established in Phase 3 decisions: "Collection grouping uses enum case comparison (TaskStatus::Backlog) not string."
**Warning signs:** Dashboard count array has numeric keys instead of string status keys.

### Pitfall 4: `notas` Leaking into Inertia Props
**What goes wrong:** Controller fetches full client model and passes it to props, inadvertently exposing `clients.notas` (internal notes).
**Why it happens:** Lazy use of `Auth::user()->client` eager-loaded and serialized.
**How to avoid:** The portal controller does NOT load or pass the client model. It only uses `auth()->user()->client_id` as a filter. No client data model is passed as a prop.
**Warning signs:** Any prop in Portal/Index.vue that contains `notas`, `stack_tecnologico`, or other internal client fields.

### Pitfall 5: Quote Total Calculation
**What goes wrong:** Quote total passed to portal includes un-loaded items, resulting in `$q->items->sum('precio')` throwing an error or returning 0.
**Why it happens:** `Quote::where('client_id', ...)->get()` does not eager-load items by default.
**How to avoid:** `->with('items')` is mandatory when calculating totals. See the established pattern in `QuoteController::index()` which does `Quote::with('client', 'items')`.

---

## Code Examples

Verified patterns from existing codebase:

### Enum Collection Grouping (established Phase 3)
```php
// CORRECT — uses ->value to get string key
$tasks->groupBy(fn ($t) => $t->estado->value)->map->count()->all();

// WRONG — keys are enum instances, not strings
$tasks->groupBy('estado')->map->count()->all();
```

### DB-level Sum (established BillingController)
```php
// CORRECT — database aggregate, (float) cast handles empty result
(float) Billing::where('client_id', $clientId)
    ->where('estado', BillingStatus::Pendiente)
    ->sum('monto');

// WRONG — loads all rows into memory before summing
Billing::where('client_id', $clientId)->get()->where('estado', 'pendiente')->sum('monto');
```

### formatMonto (established Billing/Index.vue)
```javascript
// Source: resources/js/Pages/Admin/Billing/Index.vue line 53-55
function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}
```

### streamDownload for PDF (established QuoteController::pdf)
```php
// Source: app/Http/Controllers/QuoteController.php lines 107-111
return response()->streamDownload(
    fn () => print($pdf->output()),
    $filename,
    ['Content-Type' => 'application/pdf']
);
```

### EnsureIsClient middleware check
```php
// Source: app/Http/Middleware/EnsureIsClient.php
// Checks Role::Client — does NOT check ownership of resources
if (! $request->user() || $request->user()->role !== Role::Client) {
    abort(403);
}
// Therefore: ownership checks must be explicit in the controller
```

### Inertia assertInertia test pattern (established across phases)
```php
$response->assertInertia(fn ($page) => $page
    ->component('Portal/Index')
    ->has('tasks', 2)
    ->has('quotes', 1)
    ->has('billings', 1)
    ->has('dashboard')
);
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Inline closure in routes/web.php for `/portal` | `PortalController@index` | Phase 6 (this phase) | Enables testing and data injection |
| Placeholder Portal/Index.vue | Fully implemented portal with props | Phase 6 (this phase) | None — layout already correct |

**Not deprecated for this phase:** All patterns from Phases 1-5 are current and apply directly.

---

## Open Questions

1. **Quote PDF access — should Borrador estado be blocked in portal?**
   - What we know: Admin PDF route blocks Borrador (`abort_if($quote->estado === QuoteStatus::Borrador, 403)`). CONTEXT.md says portal PDF is "visible para todos los estados."
   - What's unclear: Should a client be able to download a Borrador PDF? It seems odd — the admin hasn't sent it yet.
   - Recommendation: CONTEXT.md explicitly says "sin restricción por estado" — honor the locked decision. If needed, this is a v2 refinement. Tests should verify a Borrador quote IS downloadable by the owner.

2. **Column selection for `tasks` query**
   - What we know: Portal needs `titulo`, `estado`, `fecha_limite`. The `id` is needed for the Vue `key` attribute.
   - What's unclear: Whether to pass `descripcion` or `prioridad`.
   - Recommendation: Portal only shows title, status, and deadline per CONTEXT.md. Select `['id', 'titulo', 'estado', 'fecha_limite']` — no `descripcion`, no `prioridad` (those are admin-only views).

---

## Validation Architecture

> `nyquist_validation: true` in `.planning/config.json` — section included.

### Test Framework
| Property | Value |
|----------|-------|
| Framework | PHPUnit (via Laravel) |
| Config file | `phpunit.xml` |
| Quick run command | `php artisan test --filter PortalTest` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| PORT-01 | Client sees their own tasks (all statuses) | Feature | `php artisan test --filter PortalTest::test_client_sees_own_tasks` | ❌ Wave 0 |
| PORT-01 | Client cannot see another client's tasks | Feature | `php artisan test --filter PortalTest::test_client_cannot_see_other_tasks` | ❌ Wave 0 |
| PORT-02 | Client sees their own quotes with total | Feature | `php artisan test --filter PortalTest::test_client_sees_own_quotes` | ❌ Wave 0 |
| PORT-02 | Client can download their own quote PDF | Feature | `php artisan test --filter PortalTest::test_client_can_download_own_pdf` | ❌ Wave 0 |
| PORT-02 | Client cannot download another client's PDF | Feature | `php artisan test --filter PortalTest::test_client_cannot_download_other_pdf` | ❌ Wave 0 |
| PORT-03 | Client sees their own billings | Feature | `php artisan test --filter PortalTest::test_client_sees_own_billings` | ❌ Wave 0 |
| PORT-03 | Client cannot see another client's billings | Feature | `php artisan test --filter PortalTest::test_client_cannot_see_other_billings` | ❌ Wave 0 |
| PORT-04 | Dashboard has task counts per status | Feature | `php artisan test --filter PortalTest::test_dashboard_has_task_counts` | ❌ Wave 0 |
| PORT-04 | Dashboard has quote counts per status | Feature | `php artisan test --filter PortalTest::test_dashboard_has_quote_counts` | ❌ Wave 0 |
| PORT-04 | Dashboard has billing totals (pendiente + pagado) | Feature | `php artisan test --filter PortalTest::test_dashboard_has_billing_totals` | ❌ Wave 0 |
| Security | Admin cannot access /portal | Feature | `php artisan test --filter PortalTest::test_admin_cannot_access_portal` | ❌ Wave 0 |
| Security | Unauthenticated user cannot access /portal | Feature | `php artisan test --filter PortalTest::test_guest_cannot_access_portal` | ❌ Wave 0 |
| Security | notas field not exposed in Inertia props | Feature | `php artisan test --filter PortalTest::test_client_notas_not_in_props` | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `php artisan test --filter PortalTest`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Portal/PortalTest.php` — covers PORT-01, PORT-02, PORT-03, PORT-04, Security
- [ ] `tests/Feature/Portal/` directory (create)

*(No framework gaps — PHPUnit + RefreshDatabase already established)*

---

## Sources

### Primary (HIGH confidence)
- Direct codebase inspection — `app/Http/Middleware/EnsureIsClient.php`, `app/Http/Controllers/QuoteController.php`, `app/Http/Controllers/BillingController.php`
- Direct codebase inspection — `app/Models/{Task,Quote,Billing,Client,User}.php`
- Direct codebase inspection — `app/Enums/{TaskStatus,QuoteStatus,BillingStatus}.php`
- Direct codebase inspection — `resources/js/Pages/Portal/Index.vue`, `resources/js/Layouts/PortalLayout.vue`
- Direct codebase inspection — `routes/web.php`
- `.planning/phases/06-portal-del-cliente/06-CONTEXT.md` — locked decisions
- `.planning/STATE.md` — accumulated decisions from Phases 1–5

### Secondary (MEDIUM confidence)
- Phase 5 pattern for `streamDownload` — confirmed in `QuoteController::pdf()`
- Phase 3 pattern for enum collection grouping — confirmed in STATE.md decisions
- Phase 4 pattern for BillingController `$summary` block — confirmed in `BillingController::index()`

### Tertiary (LOW confidence)
- None — all findings are HIGH confidence from direct codebase inspection.

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — entire stack verified from existing code; no new dependencies
- Architecture: HIGH — all patterns verified from Phases 1–5 implementations
- Pitfalls: HIGH — ownership check gap verified from code inspection (`EnsureIsClient` does not check resource ownership); enum grouping pitfall confirmed from Phase 3 STATE.md entry
- Test map: HIGH — PHPUnit infrastructure confirmed, test structure matches existing test files

**Research date:** 2026-03-30
**Valid until:** 2026-05-30 (stable stack — no fast-moving dependencies)
