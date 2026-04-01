# Phase 4: Facturación - Research

**Researched:** 2026-03-20
**Domain:** Laravel 12 + Inertia.js v2 + Vue 3 — Billing CRUD with decimal money, conditional validation, aggregate dashboard
**Confidence:** HIGH (all findings from existing project code + Laravel docs patterns confirmed against codebase)

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- Campos: `client_id` (FK), `concepto` (string), `monto` (decimal 12,2 ARS), `fecha_emision` (date), `fecha_pago` (date nullable), `estado` (enum)
- Estados enum: `pendiente` / `pagado` / `vencido`
- Estado **manual** — el admin cambia el estado explícitamente, no hay automatización
- `monto` como `decimal(12,2)` — permite centavos en ARS
- Página dedicada `/billing` — resumen arriba + tabla de cobros abajo
- Resumen muestra: total cobrado este mes (sum donde estado=pagado y fecha_pago en mes actual) + deuda pendiente total (sum donde estado=pendiente) + count de cobros vencidos
- Filtros en tabla: por estado (dropdown) + por cliente (dropdown)
- Sin exportación CSV en v1
- Páginas separadas: `/billing/create` y `/billing/{id}/edit`
- Validación: si `estado` = `pagado`, entonces `fecha_pago` es requerida
- Borrar cobro con confirmación modal (en el formulario de edición o en la tabla)
- Sección "Facturación" en `/clients/{id}` — muestra cobros de ese cliente en tabla compacta (read-only, sin crear desde ahí)

### Claude's Discretion
- Paginación de la tabla de cobros principal
- Formato de display del monto (punto decimal, separador de miles)
- Estructura de la sección de facturación en la página del cliente
- Nombre del modelo: `Billing` o `Payment` — criterio de Claude

### Deferred Ideas (OUT OF SCOPE)
- Exportar listado de cobros a CSV — v2
- Marcado automático de "vencido" al superar fecha — requiere scheduler, v2
- Número de factura o referencia interna — v2
- Múltiples monedas — out of scope (solo ARS)
- Integración con AFIP o pasarelas de pago — out of scope v1
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| BILL-01 | El admin puede registrar manualmente un cobro (cliente, concepto, monto ARS, fecha de emisión, fecha de pago, estado) | Migration schema + BillingController store/update + Form Request with conditional validation |
| BILL-02 | Los cobros tienen estados: pendiente / pagado / vencido | BillingStatus enum (mirrors TaskStatus pattern), enum column in migration |
| BILL-03 | El admin puede ver un dashboard mensual de facturación (total cobrado en el mes, deuda pendiente total) | Eloquent whereMonth/whereYear/sum aggregates — passed as `summary` prop from BillingController@index |
| BILL-04 | El admin puede filtrar cobros por cliente o por estado | router.get preserveState pattern from TaskController@index — already established in Phase 3 |
</phase_requirements>

---

## Summary

Phase 4 is a straightforward CRUD module with two non-obvious technical areas: decimal money handling and conditional validation. Both are solved cleanly by Laravel's built-in tools with no extra packages needed.

The project already has all patterns in place. The billing module follows the same controller/Form Request/Inertia page structure as Tasks (Phase 3). The main new elements are: (1) `decimal` cast on `monto` to avoid float precision loss, (2) `required_if` rule for `fecha_pago`, (3) three Eloquent aggregate queries for the dashboard summary, and (4) a `billings` prop injected into the existing `ClientController@show` response without touching its existing test assertions.

**Primary recommendation:** Use model name `Billing` (matches the route prefix `/billing`, the table name `billings`, and the requirement IDs BILL-XX). Mirror TaskController's filter pattern exactly. Pass dashboard summary as a dedicated `summary` array prop from `BillingController@index`.

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel (existing) | 12.x | ORM, validation, routing | Already installed — no addition needed |
| Inertia.js (existing) | v2 | Server-driven SPA pages | Already installed |
| Vue 3 (existing) | 3.x | UI components | Already installed |

### No New Packages Required
This phase adds zero new Composer or npm dependencies. All capabilities needed (decimal casts, conditional validation, date aggregates, enum columns, pagination) are native to the existing stack.

**Installation:** none needed.

---

## Architecture Patterns

### Recommended Project Structure

```
app/
├── Enums/
│   └── BillingStatus.php          # new — pendiente/pagado/vencido
├── Http/
│   ├── Controllers/
│   │   └── BillingController.php  # new — index, create, store, edit, update, destroy
│   └── Requests/
│       ├── StoreBillingRequest.php  # new
│       └── UpdateBillingRequest.php # new
├── Models/
│   ├── Billing.php                # new
│   └── Client.php                 # add billings() hasMany

database/
└── migrations/
    └── 2026_03_20_000005_create_billings_table.php  # new

resources/js/Pages/Admin/
├── Billing/
│   ├── Index.vue    # new — dashboard + table
│   ├── Create.vue   # new
│   └── Edit.vue     # new
└── Clients/
    └── Show.vue     # modify — add billing section at bottom

resources/js/Layouts/
└── AdminLayout.vue  # modify — add Facturación nav link

routes/
└── web.php          # modify — add billing routes under admin middleware

tests/Feature/
└── Billing/
    ├── BillingCrudTest.php       # new
    ├── BillingDashboardTest.php  # new
    └── BillingValidationTest.php # new
```

### Pattern 1: BillingStatus Enum (mirrors TaskStatus exactly)

**What:** PHP 8.1 backed enum for the three billing states.
**When to use:** Same pattern as `App\Enums\TaskStatus` — cast in model, validated as `in:` rule in Form Requests.

```php
// app/Enums/BillingStatus.php
namespace App\Enums;

enum BillingStatus: string
{
    case Pendiente = 'pendiente';
    case Pagado    = 'pagado';
    case Vencido   = 'vencido';
}
```

### Pattern 2: Migration Schema (exact)

**What:** Billings table with decimal(12,2), date columns, and enum estado.
**Key detail:** `$table->foreignId('client_id')->constrained()->nullOnDelete()` — matches the existing Phase 2 decision for users and invitations. A deleted client should NOT cascade-delete billing records (financial history is valuable). Use `nullOnDelete` to preserve rows.

> WARNING: If `cascadeOnDelete` is used (like tasks), deleting a client silently destroys billing history. Tasks have no standalone value without a client; billing records do. Use `nullOnDelete`.

```php
// Source: existing migration patterns in this project
Schema::create('billings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
    $table->string('concepto');
    $table->decimal('monto', 12, 2);
    $table->date('fecha_emision');
    $table->date('fecha_pago')->nullable();
    $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
    $table->timestamps();
});
```

### Pattern 3: Decimal Cast — Critical for Money

**What:** Casting `monto` as `'decimal:2'` in the model ensures Laravel returns a string with exactly 2 decimal places, not a PHP float (which has binary precision issues).
**When to use:** Always for money columns. PHP floats cannot represent 0.1 + 0.2 exactly.

```php
// app/Models/Billing.php
protected function casts(): array
{
    return [
        'estado'        => BillingStatus::class,
        'fecha_emision' => 'date',
        'fecha_pago'    => 'date',
        'monto'         => 'decimal:2',  // returns "1500.00" string, not float
    ];
}
```

**Frontend note:** The decimal cast serializes to a string like `"1500.00"` in the Inertia prop. Use `parseFloat()` only for arithmetic, display as-is or format with `Intl.NumberFormat`. Do NOT use `<input type="number">` — use `<input type="text">` with pattern validation to accept Argentine format (users may type `1500,50` — normalize the comma to a dot before submitting via Inertia form).

### Pattern 4: Conditional Validation — required_if

**What:** `fecha_pago` is required only when `estado` equals `pagado`.
**Laravel built-in:** `required_if:estado,pagado` rule handles this without custom logic.

```php
// app/Http/Requests/StoreBillingRequest.php
public function rules(): array
{
    return [
        'client_id'     => ['required', 'exists:clients,id'],
        'concepto'      => ['required', 'string', 'max:255'],
        'monto'         => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        'fecha_emision' => ['required', 'date'],
        'fecha_pago'    => ['nullable', 'date', 'required_if:estado,pagado'],
        'estado'        => ['required', 'in:pendiente,pagado,vencido'],
    ];
}
```

**Gotcha:** `required_if` on a field that is also `nullable` works correctly — `nullable` means "allow null in DB", `required_if` means "must be present when condition met". Both rules can coexist.

**UpdateBillingRequest** uses identical rules. No difference between store/update for this model (unlike clients where update needs `unique:...,{id}` exclusion — billing has no unique constraint).

### Pattern 5: Dashboard Aggregates — Exact Eloquent Queries

**What:** Three aggregate queries for the summary card. All use Carbon's `now()` internally via `whereMonth`/`whereYear`.

```php
// app/Http/Controllers/BillingController.php  index()
use Illuminate\Support\Facades\DB;

$summary = [
    'cobrado_mes'      => Billing::where('estado', 'pagado')
                            ->whereMonth('fecha_pago', now()->month)
                            ->whereYear('fecha_pago', now()->year)
                            ->sum('monto'),   // returns float — cast to string for display

    'pendiente_total'  => Billing::where('estado', 'pendiente')
                            ->sum('monto'),

    'vencidos_count'   => Billing::where('estado', 'vencido')
                            ->count(),
];
```

**Gotcha:** `sum('monto')` returns a PHP float (or `"0"` string when no rows exist). Wrap with `number_format((float) $value, 2)` server-side OR handle `null`/`"0"` on the Vue side. Consistent approach: cast to float server-side so Vue always gets a number.

**Gotcha:** `whereMonth` uses MySQL's `MONTH()` function. Works correctly with `date` columns (not `datetime`). Our `fecha_pago` is `date` — no issue.

### Pattern 6: BillingController@index — Full Prop Structure

**What:** Mirrors TaskController@index — query + filters + summary as separate props.

```php
public function index(Request $request)
{
    $query = Billing::with('client')
        ->when($request->filled('cliente'), fn ($q) => $q->where('client_id', $request->cliente))
        ->when($request->filled('estado'),  fn ($q) => $q->where('estado', $request->estado))
        ->latest()
        ->paginate(20)
        ->withQueryString();

    $summary = [
        'cobrado_mes'     => (float) Billing::where('estado', 'pagado')
                                ->whereMonth('fecha_pago', now()->month)
                                ->whereYear('fecha_pago', now()->year)
                                ->sum('monto'),
        'pendiente_total' => (float) Billing::where('estado', 'pendiente')
                                ->sum('monto'),
        'vencidos_count'  => Billing::where('estado', 'vencido')
                                ->count(),
    ];

    return Inertia::render('Admin/Billing/Index', [
        'billings' => $query,
        'clients'  => Client::orderBy('nombre')->get(['id', 'nombre']),
        'filtros'  => $request->only(['cliente', 'estado']),
        'summary'  => $summary,
    ]);
}
```

**Note on pagination:** Using `paginate(20)` (same as ClientController) rather than `->get()` (like TaskController). The billing list is a flat table, not a Kanban — pagination is appropriate. Vue side uses `billings.data` (not `billings` directly) and renders pagination links.

### Pattern 7: Adding billings to ClientController@show Without Breaking Tests

**What:** The existing `test_admin_can_view_client_detail` test asserts `->has('hasActiveUser')` but does NOT assert the full prop list. Adding a new prop is additive and safe.

```php
// app/Http/Controllers/ClientController.php  show()
public function show(Client $client)
{
    $hasActiveUser = $client->user()->exists();

    return Inertia::render('Admin/Clients/Show', [
        'client'        => $client,
        'hasActiveUser' => $hasActiveUser,
        'billings'      => $client->billings()        // new prop — additive, safe
                            ->latest()
                            ->get(['id', 'concepto', 'monto', 'fecha_emision', 'estado']),
    ]);
}
```

**Why it's safe:** `assertInertia` uses `->has('key')` and `->where('key', value)` — it only checks what you assert. Adding `billings` to the props does NOT invalidate any existing assertion. The existing 4 tests in `ClientCrudTest.php` will still pass.

**Column selection:** Pass only the 5 columns needed for the read-only table — avoids over-fetching. No need to eager-load `client` (we already have the client in scope).

**No pagination on the client's billing section:** The context specifies "tabla compacta". Use `->get()`, not `->paginate()`. If a client ever has hundreds of billings, that's a v2 concern.

### Pattern 8: Route Structure

```php
// routes/web.php — add inside ['auth', 'admin'] group
Route::resource('billing', BillingController::class)->except(['show']);
```

`Route::resource('billing', ...)` generates:
- `GET /billing` → index (dashboard)
- `GET /billing/create` → create
- `POST /billing` → store
- `GET /billing/{billing}/edit` → edit
- `PUT /billing/{billing}` → update
- `DELETE /billing/{billing}` → destroy

No `show` route needed (the context specifies separate create/edit pages, no standalone detail page).

### Pattern 9: AdminLayout Nav Addition

```vue
<!-- resources/js/Layouts/AdminLayout.vue — add after Tareas link -->
<Link
    href="/billing"
    class="text-sm font-medium text-gray-600 hover:text-gray-900 transition"
    :class="{ 'text-gray-900 font-semibold': $page.url.startsWith('/billing') }"
>
    Facturación
</Link>
```

Identical pattern to existing nav links. The `startsWith('/billing')` active class covers `/billing`, `/billing/create`, and `/billing/123/edit`.

### Anti-Patterns to Avoid

- **Float for money:** Do NOT use `'monto' => 'float'` cast. Use `'decimal:2'`. Float cannot represent currency accurately.
- **`<input type="number">` for monto:** HTML number inputs use the browser locale, which may display commas. Use `type="text"` with a `numeric` validation hint.
- **`cascadeOnDelete` on billings.client_id:** Financial history must survive client deletion. Use `nullOnDelete` (matching the Phase 2 decision for users/invitations).
- **Inline validation in controller:** Use Form Request classes (StoreBillingRequest / UpdateBillingRequest) to match the established pattern from Phase 3.
- **`->get()` on billing index:** Billing list should be paginated. Use `->paginate(20)->withQueryString()` to preserve filter params across pages.
- **Enum without backed type:** PHP enums for casting MUST be string-backed (`enum BillingStatus: string`). Non-backed enums cannot be used as Eloquent casts.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Conditional field requirement | Custom `withValidator` logic | `required_if:estado,pagado` rule | Native Laravel rule, handles null correctly |
| Month/year filtering | Raw SQL `MONTH()` / `YEAR()` calls | `whereMonth()` / `whereYear()` | Eloquent wrappers — same SQL, cleaner code |
| Money precision | Float arithmetic + rounding | `decimal:2` cast | Avoids binary float errors at the ORM boundary |
| Decimal formatting | Custom PHP/JS number formatter | `Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' })` | Browser-native, handles locale correctly |
| Delete confirmation modal | Custom modal component | Same `ref(null)` sentinel pattern from Phase 2 ClientController | Already proven in this codebase |

**Key insight:** Every problem in this phase has a Laravel-native or project-established solution. No new patterns are needed.

---

## Common Pitfalls

### Pitfall 1: `sum()` Returns `"0"` Not `0` on Empty Set
**What goes wrong:** `Billing::where(...)->sum('monto')` returns the string `"0"` (not float `0.0`) when no rows match. Vue template `{{ summary.cobrado_mes.toFixed(2) }}` throws `TypeError: summary.cobrado_mes.toFixed is not a function`.
**Why it happens:** Laravel's `sum()` uses PDO which returns strings for aggregate results when there are no matching rows.
**How to avoid:** Cast server-side: `(float) Billing::where(...)->sum('monto')`. This guarantees Vue always receives a number.
**Warning signs:** Dashboard shows `NaN` or throws a console error on fresh install with no billing data.

### Pitfall 2: `required_if` Not Firing on `null` vs Missing Field
**What goes wrong:** When `estado=pagado` and `fecha_pago` is submitted as empty string `""`, the `required_if` rule sees an empty string and considers it "present but empty" — passes `required_if`, fails `date`.
**Why it happens:** HTML forms submit empty text inputs as `""` not `null`. Inertia's `useForm` sends whatever is in the reactive object.
**How to avoid:** In the Vue form, initialize `fecha_pago` as `null` (not `''`). Set it to `null` when clearing. Inertia sends `null` which `required_if` correctly catches as absent.

```js
// Correct initialization in Create.vue / Edit.vue
const form = useForm({
    fecha_pago: null,   // not ''
    // ...
})
```

### Pitfall 3: Decimal Input Comma vs Dot
**What goes wrong:** Admin types `1500,50` (Argentine convention). Sent to Laravel as `"1500,50"`. Laravel's `numeric` rule fails because it's not a valid number. Database receives nothing.
**Why it happens:** Argentina uses comma as decimal separator; HTML and PHP expect dot.
**How to avoid:** On the form Vue page, normalize before submit:

```js
function submit() {
    const normalized = { ...form }
    normalized.monto = String(form.monto).replace(',', '.')
    // then post
}
```

Or: Display with comma for UX, store internal state with dot. The simpler approach: document that the input expects a dot (placeholder `"1500.50"`) and validate the pattern client-side.

**Recommendation:** Use dot internally, show clear placeholder. Don't auto-convert — it adds complexity and hides input errors.

### Pitfall 4: Billing Enum Cast Breaks Collection Filtering
**What goes wrong:** Replicating the TaskStatus enum cast causes the same issue that Phase 3 documented: `$collection->where('estado', 'pagado')` returns empty because enum-cast values don't match plain strings.
**Why it happens:** When `estado` is cast to `BillingStatus::class`, the model attribute becomes a `BillingStatus` enum instance. String comparison `=== 'pagado'` fails.
**How to avoid:** In BillingController, all filtering uses query builder (not collection filtering), so enum casting on the model is safe. We never filter a Collection of Billing objects — we always filter at the DB level. No issue here unlike TaskController which used collection grouping for Kanban columns.

### Pitfall 5: `nullOnDelete` vs `cascadeOnDelete` on billings FK
**What goes wrong:** Using `cascadeOnDelete` (copied from tasks migration) silently deletes all billing records when a client is deleted. Financial history is destroyed.
**Why it happens:** Copy-paste from TaskFactory migration without considering the semantic difference.
**How to avoid:** Explicitly use `->nullable()->constrained()->nullOnDelete()`. The `nullable()` call on the foreignId is required before `nullOnDelete` — without it, MySQL will reject the null on delete with a constraint violation.

### Pitfall 6: Pagination on Client Show Billing Section
**What goes wrong:** Using `->paginate()` in `ClientController@show` for the client billing section breaks the Inertia assertion `->has('billings', N)` in tests because paginated results come as `billings.data`.
**How to avoid:** Use `->get()` in `ClientController@show`. Only `BillingController@index` uses `paginate()`.

---

## Code Examples

### Complete Billing Model
```php
// app/Models/Billing.php
namespace App\Models;

use App\Enums\BillingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'concepto',
        'monto',
        'fecha_emision',
        'fecha_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado'        => BillingStatus::class,
            'fecha_emision' => 'date',
            'fecha_pago'    => 'date',
            'monto'         => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
```

### Client Model Addition
```php
// app/Models/Client.php — add this method
use App\Models\Billing;

public function billings(): HasMany
{
    return $this->hasMany(Billing::class);
}
```

### StoreBillingRequest with Conditional Validation
```php
// app/Http/Requests/StoreBillingRequest.php
public function rules(): array
{
    return [
        'client_id'     => ['required', 'exists:clients,id'],
        'concepto'      => ['required', 'string', 'max:255'],
        'monto'         => ['required', 'numeric', 'min:0.01'],
        'fecha_emision' => ['required', 'date'],
        'fecha_pago'    => ['nullable', 'date', 'required_if:estado,pagado'],
        'estado'        => ['required', 'in:pendiente,pagado,vencido'],
    ];
}
```

### ARS Currency Formatting in Vue
```js
// Utility function for Index.vue and Edit.vue
function formatARS(amount) {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2,
    }).format(amount)
}
// Output: "$ 1.500,00" (Argentine locale)
```

### BillingFactory
```php
// database/factories/BillingFactory.php
public function definition(): array
{
    $estado = fake()->randomElement(['pendiente', 'pagado', 'vencido']);
    return [
        'client_id'     => Client::factory(),
        'concepto'      => fake()->sentence(3),
        'monto'         => fake()->randomFloat(2, 100, 50000),
        'fecha_emision' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        'fecha_pago'    => $estado === 'pagado'
                            ? fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d')
                            : null,
        'estado'        => $estado,
    ];
}
```

### Delete Confirmation Pattern (from Phase 2)
```vue
<!-- Same ref(null) sentinel pattern already in Clients/Index.vue -->
<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const billingToDelete = ref(null)   // null = modal hidden; billing object = modal shown

function confirmDelete(billing) {
    billingToDelete.value = billing
}

function deleteBilling() {
    useForm({}).delete(`/billing/${billingToDelete.value.id}`, {
        onSuccess: () => { billingToDelete.value = null },
    })
}
</script>
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Manually writing `$casts = []` property | `protected function casts(): array {}` method | Laravel 10+ | Method form is recommended in L10+; array property still works |
| `Rule::in([...])` for enum validation | `'in:pendiente,pagado,vencido'` string rule | Always valid | Both work; string form is simpler for 3 fixed values |
| `whereRaw('MONTH(fecha_pago) = ?', [now()->month])` | `whereMonth('fecha_pago', now()->month)` | Laravel 4.2+ | Eloquent helper is more readable, same SQL output |

**No deprecated patterns apply to this phase.** All Laravel 12 APIs used here are stable.

---

## Open Questions

1. **`nullOnDelete` vs `cascadeOnDelete` on billings.client_id**
   - What we know: Phase 2 used `nullOnDelete` for users/invitations (financial/relational records). Phase 3 used `cascadeOnDelete` for tasks (orphaned tasks have no value).
   - What's unclear: Nothing — billing records have financial value independent of the client. Use `nullOnDelete`.
   - Recommendation: `nullOnDelete` — documented in pitfall 5 above.

2. **Pagination on billing Index**
   - What we know: ClientController uses `paginate(20)`, TaskController uses `->get()` (Kanban needs all records for column grouping).
   - What's unclear: Nothing — billing table is a flat list, not a Kanban. Pagination is appropriate.
   - Recommendation: `paginate(20)->withQueryString()` on BillingController@index.

3. **Model name: `Billing` vs `Payment`**
   - What we know: Claude's discretion. Route prefix decided as `/billing` in context.
   - Recommendation: Use `Billing` — matches the route prefix, table name `billings`, and requirement IDs `BILL-XX`. Using `Payment` would create an inconsistency (route `/billing`, model `Payment`, table `payments`).

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | PHPUnit (via Laravel's test suite) |
| Config file | `phpunit.xml` |
| Quick run command | `php artisan test --filter BillingCrudTest` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| BILL-01 | Admin can store a billing record | unit | `php artisan test --filter BillingCrudTest::test_admin_can_create_billing` | ❌ Wave 0 |
| BILL-01 | Store validates required fields | unit | `php artisan test --filter BillingValidationTest::test_store_requires_concepto_monto_fecha_emision_estado` | ❌ Wave 0 |
| BILL-01 | Store requires fecha_pago when estado=pagado | unit | `php artisan test --filter BillingValidationTest::test_fecha_pago_required_when_estado_is_pagado` | ❌ Wave 0 |
| BILL-01 | Admin can update a billing record | unit | `php artisan test --filter BillingCrudTest::test_admin_can_update_billing` | ❌ Wave 0 |
| BILL-01 | Admin can delete a billing record | unit | `php artisan test --filter BillingCrudTest::test_admin_can_delete_billing` | ❌ Wave 0 |
| BILL-02 | Estado column accepts only valid enum values | unit | `php artisan test --filter BillingValidationTest::test_estado_rejects_invalid_values` | ❌ Wave 0 |
| BILL-03 | Dashboard index returns summary prop with correct values | unit | `php artisan test --filter BillingDashboardTest::test_summary_shows_correct_cobrado_mes` | ❌ Wave 0 |
| BILL-03 | Summary cobrado_mes only counts current month pagado records | unit | `php artisan test --filter BillingDashboardTest::test_cobrado_mes_excludes_other_months` | ❌ Wave 0 |
| BILL-03 | Summary pendiente_total sums pendiente records | unit | `php artisan test --filter BillingDashboardTest::test_pendiente_total_sums_correctly` | ❌ Wave 0 |
| BILL-03 | Client Show page includes billings prop | unit | `php artisan test --filter BillingClientShowTest::test_client_show_includes_billings` | ❌ Wave 0 |
| BILL-04 | Filter by estado returns matching billings | unit | `php artisan test --filter BillingDashboardTest::test_filter_by_estado_returns_correct_billings` | ❌ Wave 0 |
| BILL-04 | Filter by cliente returns matching billings | unit | `php artisan test --filter BillingDashboardTest::test_filter_by_cliente_returns_correct_billings` | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `php artisan test --filter Billing`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Billing/BillingCrudTest.php` — covers BILL-01 CRUD operations
- [ ] `tests/Feature/Billing/BillingValidationTest.php` — covers BILL-01 conditional validation, BILL-02 enum values
- [ ] `tests/Feature/Billing/BillingDashboardTest.php` — covers BILL-03 aggregates, BILL-04 filters
- [ ] `tests/Feature/Billing/BillingClientShowTest.php` — covers billings prop in Client Show without breaking existing ClientCrudTest
- [ ] `database/factories/BillingFactory.php` — needed by all billing tests

---

## Sources

### Primary (HIGH confidence)
- Existing project code — `app/Http/Controllers/TaskController.php`, `ClientController.php`, `app/Models/Task.php`, `app/Enums/TaskStatus.php`, `tests/Feature/Clients/ClientCrudTest.php` — patterns verified directly from codebase
- `database/migrations/2026_03_20_000004_create_tasks_table.php` — migration pattern reference
- `app/Http/Requests/StoreTaskRequest.php` — Form Request pattern

### Secondary (MEDIUM confidence)
- Laravel documentation on `required_if` validation rule — standard since Laravel 5.x, no breaking changes in L12
- Laravel documentation on `whereMonth()` / `whereYear()` — Eloquent query builder helpers, stable API
- Laravel documentation on `decimal:N` cast — available since Laravel 6.x, recommended for money

### Tertiary (LOW confidence)
- None — all claims in this document are verifiable from the existing project codebase or stable Laravel APIs.

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — zero new dependencies, all existing project patterns
- Architecture: HIGH — directly derived from TaskController, ClientController, and existing test files in the project
- Pitfalls: HIGH — pitfalls 1, 4, 5, 6 directly reference documented Phase 2/3 decisions in STATE.md; pitfalls 2, 3 are well-known Laravel/HTML issues

**Research date:** 2026-03-20
**Valid until:** 2026-04-20 (stable Laravel 12 APIs — no fast-moving dependencies in this phase)
