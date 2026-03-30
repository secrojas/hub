# Phase 5: Presupuestos y PDF - Research

**Researched:** 2026-03-30
**Domain:** Laravel resource controller + hasManyThrough items + barryvdh/laravel-dompdf + Vue 3 dynamic rows
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- Filas dinámicas en el browser: botón "+ Agregar ítem" agrega filas sin recargar la página
- Al guardar se envían todos los ítems en un solo submit
- El total se calcula de forma reactiva en el browser con Vue computed property — se actualiza en tiempo real mientras el admin escribe precios
- Mínimo 1 ítem requerido para guardar; sin máximo de ítems
- Campos del presupuesto: `client_id` (dropdown), `titulo` (string), `notas` (text, opcional), más la tabla de ítems
- Estructura de ítem: `descripcion` (string) + `precio` (decimal ARS)
- El admin cambia el estado desde acciones en la tabla de `/quotes` (botones/dropdown inline por fila)
- Transiciones: cualquier estado → cualquier estado (flexible, sin restricciones de flujo)
- El contenido del presupuesto (ítems, título, notas) solo es editable cuando el estado es **Borrador**
- Post-Borrador: la página de edición muestra los ítems en modo lectura y solo permite cambiar el estado
- Un presupuesto solo se puede **eliminar** cuando está en estado Borrador
- PDF generation: **Sync — descarga directa**: `GET /quotes/{id}/pdf` → dompdf genera el PDF → respuesta de descarga inmediata
- Sin almacenamiento: el PDF se regenera en cada descarga (no hay columna `pdf_path`)
- El botón "Descargar PDF" solo aparece cuando el estado **no es Borrador** (Enviado, Aceptado, Rechazado)
- Library: `barryvdh/laravel-dompdf` (decisión previa, STATE.md — pure PHP, sin dependencias de binarios)
- Encabezado: nombre "srojasweb" hardcoded en el template Blade + fecha de creación del presupuesto
- Datos del cliente: nombre, empresa, email del cliente
- Título del presupuesto + estado actual
- Tabla de ítems: descripción + precio ARS (formato `$ 50.000,00`)
- Total en ARS en negrita destacado al pie de la tabla
- Notas en bloque al pie si el campo `notas` no está vacío
- Nombre del archivo descargado: `presupuesto-{id}-{slug-titulo}.pdf`
- nullOnDelete en FK `quotes.client_id` (misma decisión que billings)

### Claude's Discretion

- Diseño visual del PDF (colores, tipografía, layout exacto del template Blade)
- Cómo se genera el slug del título para el nombre del archivo
- Estructura de la tabla migrations (`quote_items` separada o `items` JSON — preferentemente tabla separada por normalización)
- Orden de las columnas en la tabla `/quotes`

### Deferred Ideas (OUT OF SCOPE)

- QUOT-04: Historial de presupuestos filtrable por cliente — v2
- QUOT-05: Vista de presupuesto compartible por link público sin login — v2
- NOTF-01: Email al cliente cuando llega un nuevo presupuesto — v2
- NOTF-03: Email al admin cuando cliente acepta/rechaza — v2
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| QUOT-01 | El admin puede crear presupuestos con ítems (descripción + precio) vinculados a un cliente, con total calculado automáticamente | Schema `quotes` + `quote_items`, Vue dynamic rows with `computed` total, `StoreQuoteRequest` with nested items validation |
| QUOT-02 | Los presupuestos tienen estados: Borrador / Enviado / Aceptado / Rechazado | `QuoteStatus` enum (string-backed, same pattern as `BillingStatus`), state change via inline action in Index table, edit page conditional read-only mode |
| QUOT-03 | Al marcar un presupuesto como "Enviado" se genera un PDF descargable | `GET /quotes/{id}/pdf` route → `QuoteController@pdf` → `barryvdh/laravel-dompdf` v3.1 → `streamDownload()` response, Blade template with UTF-8 + ARS formatting |
</phase_requirements>

---

## Summary

Phase 5 builds a quotes module (presupuestos) with dynamic line-items, state management, and server-side PDF generation. The stack is entirely consistent with Phases 2–4: a Laravel resource controller with Form Requests, an Eloquent model with a string-backed enum, Inertia pages under `resources/js/Pages/Admin/Quotes/`, and the established `useForm` / `ref(null)` sentinel / `formatMonto()` patterns.

The two genuinely new technical surfaces are: (1) nested items in a single form submit — an array of objects sent as `items[]` from Vue to Laravel, validated with `items.*.descripcion` and `items.*.precio` rules; and (2) PDF generation via `barryvdh/laravel-dompdf` v3.1, which is not yet installed and requires installation plus a Blade template with proper UTF-8 charset declaration to prevent Spanish character corruption.

The most important implementation detail for correctness is the UTF-8 + ARS formatting in the PDF template: the Blade view MUST declare `<meta charset="utf-8">` and format prices with `number_format($precio, 2, ',', '.')` prefixed with `$` to match the `Intl.NumberFormat('es-AR')` output shown in the browser.

**Primary recommendation:** Install `barryvdh/laravel-dompdf ^3.1`, model `quote_items` as a normalized table (not JSON), send items as `items[0][descripcion]` array from Vue, and validate with nested rules in `StoreQuoteRequest`.

---

## Standard Stack

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| barryvdh/laravel-dompdf | ^3.1.2 | PDF generation from Blade templates | Pure PHP, no binary deps, auto-discovered in Laravel, ships with `Pdf` facade |
| dompdf/dompdf | ^3.1.5 | Underlying HTML-to-PDF engine | Pulled automatically as dependency of barryvdh/laravel-dompdf |
| Laravel resource controller | built-in | CRUD + custom pdf action | Established project pattern — all prior modules use this |
| Inertia.js + Vue 3 | ^2.0 / ^3.x | SPA-style pages | Non-negotiable stack decision |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Illuminate\Support\Str::slug() | built-in | Generate `presupuesto-{id}-{slug}.pdf` filename | PDF download action in QuoteController |
| Vue `computed()` | built-in | Reactive total from items array | Create/Edit form for real-time total display |
| Inertia `useForm` | built-in | Form state + errors + submit | Same pattern as BillingController forms |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Normalized `quote_items` table | JSON column on `quotes` | JSON is simpler to migrate but kills queryability, indexing, and clean eager-loading — Claude's discretion says prefer normalized |
| `barryvdh/laravel-dompdf` | `spatie/laravel-pdf` (Chromium-based) | Chromium requires binary installation; dompdf is pure PHP and already decided |
| `Str::slug()` for filename | Custom regex slug | Str::slug() handles accents and special chars correctly — no hand-rolling needed |

**Installation:**
```bash
composer require barryvdh/laravel-dompdf
```

**Version verification:** Dry-run confirmed `barryvdh/laravel-dompdf v3.1.2` and `dompdf/dompdf v3.1.5` resolve against Laravel 12 / PHP 8.2. The package is NOT yet installed in the project.

---

## Architecture Patterns

### Recommended Project Structure

```
app/
├── Enums/QuoteStatus.php           # string-backed enum: borrador|enviado|aceptado|rechazado
├── Models/
│   ├── Quote.php                   # belongsTo(Client), hasMany(QuoteItem)
│   └── QuoteItem.php               # belongsTo(Quote), decimal precio
├── Http/
│   ├── Controllers/QuoteController.php   # resource + pdf() action
│   └── Requests/
│       ├── StoreQuoteRequest.php         # nested items validation
│       └── UpdateQuoteRequest.php        # items validation only when estado=borrador
database/
├── migrations/
│   ├── ..._create_quotes_table.php
│   └── ..._create_quote_items_table.php
resources/
├── js/Pages/Admin/Quotes/
│   ├── Index.vue          # list table, inline state change, PDF download button
│   ├── Create.vue         # dynamic items form, computed total
│   └── Edit.vue           # conditional read-only (borrador vs post-borrador)
└── views/pdf/
    └── quote.blade.php    # HTML template for dompdf
tests/Feature/Quotes/
├── QuoteCrudTest.php
├── QuoteStatusTest.php
└── QuotePdfTest.php
```

### Pattern 1: Nested Items in a Single Form Submit

**What:** Items are an array of objects in `useForm`. Vue adds/removes rows reactively. Laravel receives `items[0][descripcion]`, `items[0][precio]`, etc.

**When to use:** Any time a form has a variable number of child records that are created atomically with the parent.

**Example (Vue side):**
```typescript
// Source: Established project pattern + Vue 3 reactivity docs
const form = useForm({
    client_id: '',
    titulo:    '',
    notas:     '',
    items:     [{ descripcion: '', precio: '' }],
})

const total = computed(() =>
    form.items.reduce((sum, item) => sum + (parseFloat(item.precio) || 0), 0)
)

function addItem() {
    form.items.push({ descripcion: '', precio: '' })
}

function removeItem(index) {
    form.items.splice(index, 1)
}
```

**Example (Laravel validation):**
```php
// Source: Laravel 12 docs — nested array validation
public function rules(): array
{
    return [
        'client_id' => ['required', 'exists:clients,id'],
        'titulo'    => ['required', 'string', 'max:255'],
        'notas'     => ['nullable', 'string'],
        'items'     => ['required', 'array', 'min:1'],
        'items.*.descripcion' => ['required', 'string', 'max:500'],
        'items.*.precio'      => ['required', 'numeric', 'min:0.01'],
    ];
}
```

**Example (Controller store):**
```php
public function store(StoreQuoteRequest $request)
{
    $quote = Quote::create($request->safe()->except('items'));

    foreach ($request->validated()['items'] as $item) {
        $quote->items()->create($item);
    }

    return redirect()->route('quotes.index');
}
```

### Pattern 2: State Change via Inline Table Action

**What:** A select or set of buttons per row in Index.vue sends a PATCH to update only the `estado` field. No separate edit page navigation required.

**When to use:** When state transitions are simple and the admin should not leave the list view.

**Example:**
```typescript
// Source: Established project pattern (TaskController@updateStatus precedent)
function changeEstado(quote, newEstado) {
    useForm({ estado: newEstado }).patch(`/quotes/${quote.id}/estado`, {
        preserveScroll: true,
    })
}
```

```php
// Dedicated route: PATCH /quotes/{quote}/estado
public function updateEstado(Request $request, Quote $quote): RedirectResponse
{
    $request->validate(['estado' => ['required', Rule::enum(QuoteStatus::class)]]);
    $quote->update(['estado' => $request->estado]);
    return back();
}
```

### Pattern 3: PDF Sync Download

**What:** GET route → controller loads Quote with items and client → Pdf facade renders Blade view → `streamDownload()` response.

**When to use:** PDF must be available immediately, no queue, no file storage.

**Example:**
```php
// Source: barryvdh/laravel-dompdf v3 docs
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

public function pdf(Quote $quote): \Symfony\Component\HttpFoundation\StreamedResponse
{
    abort_if($quote->estado === QuoteStatus::Borrador, 403);

    $quote->load(['client', 'items']);

    $pdf = Pdf::loadView('pdf.quote', ['quote' => $quote]);

    $slug     = Str::slug($quote->titulo);
    $filename = "presupuesto-{$quote->id}-{$slug}.pdf";

    return response()->streamDownload(
        fn () => print($pdf->output()),
        $filename,
        ['Content-Type' => 'application/pdf']
    );
}
```

### Pattern 4: Conditional Read-Only Edit Page

**What:** Edit.vue checks `quote.estado === 'borrador'`. When true → editable form with items. When false → read-only display of items + state-change dropdown only.

**When to use:** Post-Borrador presupuestos must show content but block editing per locked decision.

**Example:**
```html
<!-- Source: Vue 3 conditional rendering pattern -->
<template v-if="quote.estado === 'borrador'">
    <!-- full editable form with items rows -->
</template>
<template v-else>
    <!-- read-only item list as plain text -->
    <div v-for="item in quote.items" :key="item.id" class="...">
        <span>{{ item.descripcion }}</span>
        <span>{{ formatMonto(item.precio) }}</span>
    </div>
    <!-- estado change select only -->
</template>
```

### Anti-Patterns to Avoid

- **Storing items as JSON in `quotes.items`:** Kills eager-loading clarity, requires manual JSON decode, and makes future queries on items impossible. Use `quote_items` table.
- **Generating PDF client-side (html2canvas, jsPDF):** Requires shipping a JS PDF library, produces inconsistent output. Server-side Blade + dompdf is the decided approach.
- **Sending total from Vue to server:** The total is a computed display value only. Never store or validate it server-side — recompute from items on the server if needed.
- **Forgetting charset in Blade PDF template:** Missing `<meta charset="utf-8">` causes Spanish characters (ñ, á, é) to render as garbage. This is the #1 dompdf gotcha.
- **Using `disabled` attribute on the edit form for post-Borrador mode:** Per CONTEXT.md, post-Borrador should be a "clear read-only view," not a disabled form. Use `v-if/v-else` to switch rendering.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| HTML → PDF conversion | Custom wkhtmltopdf wrapper, Puppeteer integration | `barryvdh/laravel-dompdf` | Pure PHP, no binary deps, battle-tested on Laravel |
| Slug generation for filename | Custom regex replace | `Str::slug($titulo)` | Handles accents, spaces, special chars correctly |
| ARS number formatting in PHP | Custom `sprintf` or `number_format` wrapper | `number_format($precio, 2, ',', '.')` prefixed with `$ ` | Matches `Intl.NumberFormat('es-AR')` output exactly |
| Nested validation messages | Manual loop in controller | `items.*.descripcion` nested array rules in Form Request | Laravel built-in, returns field-keyed errors Inertia handles |

**Key insight:** The items array pattern (nested validation + bulk insert) is standard Laravel — there's nothing to invent here, just apply `items.*` validation rules and loop in the controller.

---

## Common Pitfalls

### Pitfall 1: Spanish Characters Corrupted in PDF

**What goes wrong:** Characters like ñ, á, é, ó, ú render as `?` or boxes in the downloaded PDF.

**Why it happens:** dompdf's default charset assumption or missing `<meta charset="utf-8">` in the Blade template.

**How to avoid:** The PDF Blade template MUST start with:
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
```
`DejaVu Sans` is bundled with dompdf and has full UTF-8/Latin support. The system default font does NOT.

**Warning signs:** Any accented character appearing as `?` or a box in test downloads.

### Pitfall 2: ARS Decimal Separator Mismatch Between PHP and Vue

**What goes wrong:** Vue displays `$ 50.000,00` (es-AR format), but PHP `number_format` with wrong args produces `50,000.00` (en-US format).

**Why it happens:** `number_format($n, 2)` uses `.` as decimal and `,` as thousands by default (en-US).

**How to avoid:** In the Blade PDF template always use:
```php
'$ ' . number_format($item->precio, 2, ',', '.')
```
This produces `$ 50.000,00` matching `Intl.NumberFormat('es-AR')` output.

**Warning signs:** PDF shows different format than browser UI for the same amount.

### Pitfall 3: Items Not Persisted on Update When Quote Is Borrador

**What goes wrong:** Update controller only calls `$quote->update(...)` without syncing items, leaving stale items in DB.

**Why it happens:** Forgetting that items are a separate table — they don't update via `$fillable` on Quote.

**How to avoid:** In `QuoteController@update` (when estado === borrador), delete and re-insert items:
```php
$quote->update($request->safe()->except('items'));
$quote->items()->delete();
foreach ($request->validated()['items'] as $item) {
    $quote->items()->create($item);
}
```

**Warning signs:** Item count in DB doesn't match what was submitted.

### Pitfall 4: `decimal:2` Cast Returns String, Not Float, in Inertia Props

**What goes wrong:** `assertInertia` test fails with strict comparison — `$quote->items[0]->precio` is `"1500.00"` (string) not `1500.0` (float).

**Why it happens:** Same gotcha documented in Phase 4 (BillingController): PHP `json_encode` of a decimal:2 cast can produce a string representation.

**How to avoid:** In tests, cast expected values: `(float) $item->precio`. In Inertia props, the `decimal:2` cast is fine — JSON serialization handles it — but test assertions must account for it.

**Warning signs:** Test assertions like `->where('items.0.precio', 1500)` failing when value is `"1500.00"`.

### Pitfall 5: PDF Route Accessible for Borrador Quotes

**What goes wrong:** Admin GETs `/quotes/{id}/pdf` on a Borrador quote and gets an empty or incomplete PDF.

**Why it happens:** No guard on the pdf() controller method.

**How to avoid:** `abort_if($quote->estado === QuoteStatus::Borrador, 403)` at the top of `QuoteController@pdf()`. Test this explicitly.

### Pitfall 6: `useForm` items Array Reactivity Issue on Splice

**What goes wrong:** Removing an item with `form.items.splice(index, 1)` doesn't trigger Vue reactivity in some edge cases.

**Why it happens:** Direct array mutation via index assignment (`form.items[0] = ...`) breaks Vue 3 reactivity — `splice` is fine but must be on the reactive ref, not a local copy.

**How to avoid:** Always mutate `form.items` directly (not a `.value` copy). `form.items.splice(index, 1)` works correctly since `useForm` wraps data in a reactive proxy.

---

## Code Examples

Verified patterns from official sources and established project patterns:

### QuoteStatus Enum (mirrors BillingStatus pattern)
```php
// Source: app/Enums/BillingStatus.php (established pattern)
namespace App\Enums;

enum QuoteStatus: string
{
    case Borrador  = 'borrador';
    case Enviado   = 'enviado';
    case Aceptado  = 'aceptado';
    case Rechazado = 'rechazado';
}
```

### Quote Migration
```php
// Source: database/migrations/..._create_billings_table.php (established pattern)
Schema::create('quotes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
    $table->string('titulo');
    $table->text('notas')->nullable();
    $table->enum('estado', ['borrador', 'enviado', 'aceptado', 'rechazado'])->default('borrador');
    $table->timestamps();
});

Schema::create('quote_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
    $table->string('descripcion');
    $table->decimal('precio', 12, 2);
    $table->timestamps();
});
```

Note: `quote_items.quote_id` uses `cascadeOnDelete` — items have no value without their parent quote, consistent with the `tasks.client_id` precedent from Phase 3.

### PDF Blade Template (UTF-8 safe)
```html
{{-- resources/views/pdf/quote.blade.php --}}
{{-- Source: barryvdh/laravel-dompdf docs + dompdf DejaVu font guidance --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th    { background: #f3f4f6; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; }
        td    { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .total { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <h1>srojasweb</h1>
    <p>Fecha: {{ $quote->created_at->format('d/m/Y') }}</p>

    <h2>{{ $quote->titulo }}</h2>
    <p>Estado: {{ ucfirst($quote->estado->value) }}</p>

    <p><strong>Cliente:</strong> {{ $quote->client->nombre }} — {{ $quote->client->empresa }}<br>
    {{ $quote->client->email }}</p>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="text-align:right">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td style="text-align:right">$ {{ number_format($item->precio, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="total" style="text-align:right" colspan="1">Total</td>
                <td class="total" style="text-align:right">
                    $ {{ number_format($quote->items->sum('precio'), 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if($quote->notas)
    <div style="margin-top:24px; padding:12px; background:#f9fafb; border-left:4px solid #d1d5db;">
        <strong>Notas:</strong><br>{{ $quote->notas }}
    </div>
    @endif
</body>
</html>
```

### Index.vue State Badge Classes
```typescript
// Source: Established pattern from Billing/Index.vue (estadoBadgeClass)
function estadoBadgeClass(estado: string): string {
    if (estado === 'enviado')   return 'bg-blue-100 text-blue-800'
    if (estado === 'aceptado')  return 'bg-green-100 text-green-800'
    if (estado === 'rechazado') return 'bg-red-100 text-red-800'
    return 'bg-gray-100 text-gray-800'  // borrador
}
```

### Routes Registration
```php
// Source: routes/web.php established pattern
Route::resource('quotes', QuoteController::class)->except(['show']);
Route::patch('quotes/{quote}/estado', [QuoteController::class, 'updateEstado'])->name('quotes.updateEstado');
Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| wkhtmltopdf / Puppeteer | barryvdh/laravel-dompdf (pure PHP) | Already decided in STATE.md | No binary dep on server |
| Storing generated PDFs in storage | Regenerate on every request | Decided in CONTEXT.md | No `pdf_path` column needed |
| Monolithic form with a single item | Dynamic rows with computed total | Standard pattern for quotes/invoices | Requires Vue reactivity pattern for items array |

**Deprecated/outdated:**
- `dompdf/dompdf` used directly (without the Laravel wrapper): The `barryvdh/laravel-dompdf` facade and `loadView()` method make Blade integration clean — don't instantiate Dompdf directly.

---

## Open Questions

1. **Quote list pagination vs. full load**
   - What we know: `BillingController@index` paginates at 20. Quotes will likely have similar or lower volume.
   - What's unclear: Phase 6 (Portal del Cliente) will list quotes per client — same pagination or full `get()`?
   - Recommendation: Use `paginate(20)` on `/quotes` index consistent with billing, and use `->get()` for the client show section (same as `billings` in `ClientController@show`).

2. **QuoteFactory `estado` default state methods**
   - What we know: `BillingFactory` has `.pagado()`, `.vencido()`, `.pendiente()` state methods — this was explicitly called out as a good pattern in STATE.md.
   - What's unclear: Which states are most needed for tests?
   - Recommendation: Default to `borrador`, add `.enviado()`, `.aceptado()`, `.rechazado()` state methods — all four will be needed for PDF and state tests.

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | PHPUnit (via `php artisan test`) |
| Config file | `phpunit.xml` (exists, SQLite in-memory) |
| Quick run command | `php artisan test --filter Quotes` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| QUOT-01 | Admin can create quote with items and client | Feature | `php artisan test --filter QuoteCrudTest::test_admin_can_create_quote` | ❌ Wave 0 |
| QUOT-01 | Items are stored in quote_items table | Feature | `php artisan test --filter QuoteCrudTest::test_items_are_persisted` | ❌ Wave 0 |
| QUOT-01 | Minimum 1 item required | Feature | `php artisan test --filter QuoteValidationTest::test_requires_at_least_one_item` | ❌ Wave 0 |
| QUOT-01 | Items update correctly when quote is Borrador | Feature | `php artisan test --filter QuoteCrudTest::test_admin_can_update_quote_items` | ❌ Wave 0 |
| QUOT-02 | Quote has Borrador/Enviado/Aceptado/Rechazado states | Feature | `php artisan test --filter QuoteStatusTest::test_estado_transitions` | ❌ Wave 0 |
| QUOT-02 | Delete only allowed in Borrador | Feature | `php artisan test --filter QuoteStatusTest::test_cannot_delete_non_borrador_quote` | ❌ Wave 0 |
| QUOT-02 | Content edit blocked post-Borrador | Feature | `php artisan test --filter QuoteStatusTest::test_cannot_edit_items_post_borrador` | ❌ Wave 0 |
| QUOT-03 | PDF downloads for non-Borrador quotes | Feature | `php artisan test --filter QuotePdfTest::test_pdf_downloads_for_enviado_quote` | ❌ Wave 0 |
| QUOT-03 | PDF blocked for Borrador quotes | Feature | `php artisan test --filter QuotePdfTest::test_pdf_forbidden_for_borrador_quote` | ❌ Wave 0 |
| QUOT-03 | PDF response has correct content-type and filename | Feature | `php artisan test --filter QuotePdfTest::test_pdf_response_headers` | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `php artisan test --filter Quotes`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Quotes/QuoteCrudTest.php` — covers QUOT-01 (create, read, update, delete)
- [ ] `tests/Feature/Quotes/QuoteStatusTest.php` — covers QUOT-02 (state changes, borrador guards)
- [ ] `tests/Feature/Quotes/QuotePdfTest.php` — covers QUOT-03 (PDF download, 403 on borrador, headers)
- [ ] `tests/Feature/Quotes/QuoteValidationTest.php` — covers QUOT-01 item validation (min:1, required fields)
- [ ] `database/factories/QuoteFactory.php` — with borrador/enviado/aceptado/rechazado state methods
- [ ] `database/factories/QuoteItemFactory.php` — for seeding quote items in tests

---

## Sources

### Primary (HIGH confidence)
- `app/Enums/BillingStatus.php` — QuoteStatus enum structure directly mirrored
- `app/Models/Billing.php` — Quote model pattern (fillable, casts, belongsTo)
- `app/Http/Controllers/BillingController.php` — QuoteController structure including index filters, store, update, destroy
- `app/Http/Requests/StoreBillingRequest.php` — Form Request pattern for StoreQuoteRequest
- `database/migrations/2026_03_20_000005_create_billings_table.php` — Migration pattern including `nullOnDelete`
- `resources/js/Pages/Admin/Billing/Index.vue` — Index.vue pattern (filters, badges, sentinel modal, formatMonto)
- `resources/js/Pages/Admin/Billing/Create.vue` — Create.vue useForm pattern
- `resources/js/Layouts/AdminLayout.vue` — Nav link addition pattern
- `routes/web.php` — Route registration pattern
- `phpunit.xml` — Test framework config (SQLite in-memory)
- `.planning/phases/05-presupuestos-y-pdf/05-CONTEXT.md` — All locked implementation decisions
- Composer dry-run: `barryvdh/laravel-dompdf v3.1.2`, `dompdf/dompdf v3.1.5` confirmed for PHP 8.2 / Laravel 12

### Secondary (MEDIUM confidence)
- barryvdh/laravel-dompdf README / GitHub (v3.x): `Pdf::loadView()`, `streamDownload()`, DejaVu Sans font for UTF-8
- Laravel 12 docs: nested array validation rules (`items.*.campo`), `Rule::enum()` in validation

### Tertiary (LOW confidence)
- None — all critical claims are grounded in project code or official library API

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — dompdf version confirmed via composer dry-run; all other libraries are project-installed
- Architecture: HIGH — all patterns directly mirror Phase 4 code that is live and tested
- Pitfalls: HIGH — UTF-8/charset pitfall is documented in official dompdf guidance; decimal cast issue is a known project pattern from Phase 4 STATE.md

**Research date:** 2026-03-30
**Valid until:** 2026-04-30 (dompdf is stable; Laravel/Vue patterns are project-internal)
