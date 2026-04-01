# Phase 2: CRM de Clientes - Research

**Researched:** 2026-03-20
**Domain:** Laravel 12 + Inertia.js v2 + Vue 3 — CRUD resource controller, Eloquent relationships, migration strategy
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- Tabla `clients` separada de `users` — `clients` tiene los datos de negocio, `users` solo maneja auth
- Columna `client_id` nullable en tabla `users` — FK a `clients.id`
- Tabla `invitations` recibe columna `client_id` — vincula la invitación con el cliente CRM
- Al aceptar invitación, el `users.client_id` se asocia automáticamente al cliente CRM
- Campos obligatorios: `nombre`, `email` — email único en `clients`
- Campos opcionales: `empresa`, `telefono`, `stack_tecnologico` (text libre / textarea), `estado`, `notas`, `fecha_inicio`
- Estado por defecto al crear: `activo`; estados posibles: `activo`, `potencial`, `pausado`
- Botón "Invitar al portal" en detalle del cliente — reutiliza `InvitationController@store`
- Si el cliente ya tiene un user activo: error "Este cliente ya tiene una cuenta activa"
- Listado: tabla con columnas nombre, empresa, estado, fecha inicio + botones Ver/Editar/Eliminar
- Filtro por estado: dropdown simple (`?estado=` query param), sin búsqueda de texto
- Confirmación de eliminación: modal simple ("¿Eliminar a {nombre}?")
- Vista de detalle: `/clients/{id}` — todos los campos en modo lectura + botón Editar
- Formulario de creación/edición en páginas separadas (`/clients/create`, `/clients/{id}/edit`)

### Claude's Discretion
- Paginación del listado — si/no y cantidad de items
- Estilo de la tabla y el formulario — seguir convenciones del AdminLayout existente
- Nombres de rutas y resource controller
- Manejo de soft deletes — simples o con SoftDeletes trait

### Deferred Ideas (OUT OF SCOPE)
- Búsqueda de texto (full-text search) en el listado
- Historial de cambios del cliente (audit log)
- Exportar listado de clientes a CSV
- Foto/avatar del cliente
- Múltiples contactos por cliente
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| CLIE-01 | El admin puede crear, editar y eliminar clientes | ClientController resource (store, update, destroy) + migrations + validation |
| CLIE-02 | Cada cliente tiene: nombre, empresa, email, teléfono, stack tecnológico, estado, notas internas y fecha de inicio | `clients` table schema + Client model fillable + Inertia form pages |
| CLIE-03 | El admin puede ver la lista de todos los clientes | ClientController@index + Index.vue with estado filter via query param |
| CLIE-04 | El admin puede ver la página de detalle de un cliente individual | ClientController@show + Show.vue + "Invitar al portal" button wired to InvitationController |
</phase_requirements>

---

## Summary

Phase 2 builds a standard CRUD module on top of the Phase 1 auth foundation. The technical work splits into three areas: (1) database migrations to create the `clients` table and add `client_id` FK columns to `users` and `invitations`; (2) a Laravel resource controller with five methods wired to Inertia pages under `resources/js/Pages/Admin/Clients/`; (3) a "Invitar al portal" button on the Show page that POSTs to the existing `InvitationController@store` endpoint with a `client_id` payload.

All established Phase 1 patterns apply directly: `AdminLayout.vue` as layout, `defineOptions({ layout: AdminLayout })` in every page, `useForm` for form handling, `form.errors.field` for validation display, `router.delete()` for deletes. No new frontend libraries are needed. The Inertia query-param filter (`?estado=`) maps naturally to an Eloquent `when()` clause in the controller.

The most important gotcha is the `InvitationController@store` modification: it currently validates `unique:users,email` and stores no `client_id`. Both must change — add `client_id` to the invitation `store` method, and update `accept` to set `users.client_id` from `invitations.client_id`.

**Primary recommendation:** Implement in three sequential tasks — (1) migrations + Client model + ClientController skeleton, (2) all four Inertia CRUD pages, (3) InvitationController wiring + `accept` method update for client_id propagation.

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel Framework | 12.55.1 (installed) | Resource controller, Eloquent, migrations | Already installed |
| inertiajs/inertia-laravel | ^2.0 (installed) | Server-side Inertia responses | Already installed |
| @inertiajs/vue3 | ^2.0.0 (installed) | `useForm`, `router`, `Link` | Already installed |
| Vue 3 | ^3.4.0 (installed) | Component framework | Already installed |
| Tailwind CSS | ^3.2.1 (installed) | Styling — matches existing pages | Already installed |

No new packages required for this phase.

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @tailwindcss/forms | ^0.5.3 (installed) | Form input base styles | Already used in Create.vue pattern |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Inertia router GET for filter | Reactive fetch / axios | Query param GET keeps URL shareable, matches CONTEXT decision |
| Simple modal (inline Vue ref) | Headless UI / separate component | No headless UI installed; inline `ref(false)` is sufficient for single delete modal |
| No soft deletes | SoftDeletes trait | Soft deletes add complexity; hard delete is sufficient for v1 — this is Claude's discretion area |

---

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Http/Controllers/ClientController.php   # resource controller
├── Models/Client.php                        # Eloquent model
database/
├── migrations/
│   ├── 2026_03_20_XXXXXX_create_clients_table.php
│   ├── 2026_03_20_XXXXXX_add_client_id_to_users_table.php
│   └── 2026_03_20_XXXXXX_add_client_id_to_invitations_table.php
resources/js/Pages/Admin/Clients/
├── Index.vue    # list with estado filter
├── Create.vue   # creation form
├── Edit.vue     # edit form (pre-populated)
└── Show.vue     # read-only detail + "Invitar al portal"
tests/Feature/
└── ClientTest.php
```

### Pattern 1: Laravel Resource Controller (Only Needed Methods)

Since the project uses Inertia (not API), the resource controller uses `create`, `store`, `show`, `edit`, `update`, `destroy`. The `index` method also renders the list. The `create`/`edit` methods return Inertia pages; `store`/`update`/`destroy` redirect.

```php
// routes/web.php — inside ['auth', 'admin'] group
Route::resource('clients', ClientController::class);
```

This generates named routes: `clients.index`, `clients.create`, `clients.store`, `clients.show`, `clients.edit`, `clients.update`, `clients.destroy`.

```php
// app/Http/Controllers/ClientController.php
public function index(Request $request)
{
    $query = Client::query();

    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    $clients = $query->latest()->paginate(20)->withQueryString();

    return Inertia::render('Admin/Clients/Index', [
        'clients'       => $clients,
        'filtroEstado'  => $request->estado,
    ]);
}

public function store(Request $request)
{
    $data = $request->validate([
        'nombre'             => ['required', 'string', 'max:255'],
        'email'              => ['required', 'email', 'unique:clients,email'],
        'empresa'            => ['nullable', 'string', 'max:255'],
        'telefono'           => ['nullable', 'string', 'max:50'],
        'stack_tecnologico'  => ['nullable', 'string'],
        'estado'             => ['nullable', 'in:activo,potencial,pausado'],
        'notas'              => ['nullable', 'string'],
        'fecha_inicio'       => ['nullable', 'date'],
    ]);

    $data['estado'] = $data['estado'] ?? 'activo';

    Client::create($data);

    return redirect()->route('clients.index');
}

public function update(Request $request, Client $client)
{
    $data = $request->validate([
        'nombre'             => ['required', 'string', 'max:255'],
        'email'              => ['required', 'email', "unique:clients,email,{$client->id}"],
        // ... same optional fields
    ]);

    $client->update($data);

    return redirect()->route('clients.show', $client);
}

public function destroy(Client $client)
{
    $client->delete();
    return redirect()->route('clients.index');
}
```

**Key:** `"unique:clients,email,{$client->id}"` in update ignores the current record's own email — prevents false unique violations on edit.

### Pattern 2: Client Model with Relationships

```php
// app/Models/Client.php
class Client extends Model
{
    protected $fillable = [
        'nombre', 'email', 'empresa', 'telefono',
        'stack_tecnologico', 'estado', 'notas', 'fecha_inicio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
```

### Pattern 3: Inertia useForm for Create/Edit Pages

This matches exactly how `Admin/Invitations/Create.vue` works in Phase 1.

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

// For Create.vue:
const form = useForm({
    nombre: '',
    email: '',
    empresa: '',
    telefono: '',
    stack_tecnologico: '',
    estado: 'activo',
    notas: '',
    fecha_inicio: '',
})

function submit() {
    form.post('/clients', { preserveScroll: true })
}
</script>

<template>
    <Head title="Nuevo Cliente" />
    <form @submit.prevent="submit">
        <input v-model="form.nombre" type="text" />
        <p v-if="form.errors.nombre" class="text-sm text-red-600">{{ form.errors.nombre }}</p>
        <!-- ... other fields ... -->
        <button type="submit" :disabled="form.processing">Guardar</button>
    </form>
</template>
```

For `Edit.vue`, the controller passes the existing client as a prop:

```php
// ClientController@edit
public function edit(Client $client)
{
    return Inertia::render('Admin/Clients/Edit', ['client' => $client]);
}
```

```vue
// Edit.vue
const props = defineProps({ client: Object })

const form = useForm({
    nombre: props.client.nombre,
    email: props.client.email,
    // ... all fields pre-populated
})

function submit() {
    form.put(`/clients/${props.client.id}`, { preserveScroll: true })
}
```

### Pattern 4: Estado Filter via Query Param

The controller passes `filtroEstado` back to the Index page. The Vue component uses `router.get()` with `preserveState: true` to apply the filter without a full page reload — this is the Inertia-idiomatic approach.

```vue
// Index.vue
import { router } from '@inertiajs/vue3'

const props = defineProps({
    clients: Object,       // paginated
    filtroEstado: String,
})

function filtrar(estado) {
    router.get('/clients', { estado: estado || undefined }, {
        preserveState: true,
        preserveScroll: true,
    })
}
```

`withQueryString()` on the paginator automatically appends `?estado=X` to pagination links.

### Pattern 5: Delete Confirmation Modal (Inline Vue ref)

No external modal library needed. Use a local `ref` for the modal state.

```vue
// Inside Index.vue <script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const clienteAEliminar = ref(null)

function confirmarEliminar(client) {
    clienteAEliminar.value = client
}

function eliminar() {
    router.delete(`/clients/${clienteAEliminar.value.id}`, {
        onFinish: () => { clienteAEliminar.value = null }
    })
}
```

```vue
<!-- Modal overlay in template -->
<div v-if="clienteAEliminar" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full">
        <p class="text-gray-900 mb-4">¿Eliminar a {{ clienteAEliminar.nombre }}?</p>
        <div class="flex justify-end gap-3">
            <button @click="clienteAEliminar = null" class="px-4 py-2 text-sm border rounded">
                Cancelar
            </button>
            <button @click="eliminar" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                Eliminar
            </button>
        </div>
    </div>
</div>
```

**Note:** `router.delete()` handles the CSRF token automatically — no manual `_method` spoofing needed.

### Pattern 6: "Invitar al portal" Button from Show Page

The existing `InvitationController@store` accepts `email` and `client_name`. To wire it from the Show page without a new controller, use a small `useForm` that POSTs to `/invitations` with the pre-filled client data — plus the `client_id`.

```vue
// Show.vue
const props = defineProps({ client: Object, hasActiveUser: Boolean })

const inviteForm = useForm({
    email: props.client.email,
    client_name: props.client.nombre,
    client_id: props.client.id,
})

function invitar() {
    inviteForm.post('/invitations', { preserveScroll: true })
}
```

```vue
<button
    v-if="!hasActiveUser"
    @click="invitar"
    :disabled="inviteForm.processing"
    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
>
    Invitar al portal
</button>
<p v-else class="text-sm text-amber-600">Este cliente ya tiene una cuenta activa.</p>
```

The `hasActiveUser` boolean is computed in the controller:

```php
// ClientController@show
public function show(Client $client)
{
    return Inertia::render('Admin/Clients/Show', [
        'client'        => $client,
        'hasActiveUser' => $client->user()->exists(),
    ]);
}
```

### Anti-Patterns to Avoid

- **Passing the entire User model in `hasActiveUser`**: Pass a boolean — no need to expose user data on the client detail page.
- **Reactive JS filter without Inertia router**: Don't use `watch` + `axios.get()` for the estado filter. Use `router.get()` with `preserveState: true` — this keeps the browser URL correct and the back button working.
- **`form.submit()` vs method-specific calls**: Use `form.post()`, `form.put()`, `form.delete()` — not the generic `form.submit()`. This ensures the correct HTTP method is spoofed in the hidden `_method` field Inertia adds.
- **Missing `withQueryString()` on paginator**: Without this, pagination links lose the `?estado=X` param when navigating between pages.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Form state + validation errors | Custom reactive state + error tracking | `useForm` from `@inertiajs/vue3` | Handles `processing`, `errors`, `reset()`, `transform()` automatically |
| Query param preservation in pagination | Custom link builder | `->withQueryString()` on paginator | Laravel built-in — one method call |
| CSRF token in DELETE requests | Manual header injection | `router.delete()` from Inertia | Inertia handles X-CSRF-TOKEN header automatically |
| Route model binding | Manual `Client::findOrFail($id)` | Type-hint `Client $client` in controller methods | Laravel resolves and 404s automatically |
| Unique email skip-self on update | Manual query | `unique:clients,email,{$client->id}` validation rule | Laravel rule syntax handles this |

**Key insight:** The entire CRUD can be built without any custom JS state management or manual HTTP — `useForm` + `router` from Inertia covers all cases.

---

## Common Pitfalls

### Pitfall 1: InvitationController@store Doesn't Know About client_id Yet

**What goes wrong:** The current `store` method creates an invitation with no `client_id`. When the user is created in `accept`, `users.client_id` stays null — the Phase 6 portal association never works.

**Why it happens:** `InvitationController` was written before the `clients` table existed.

**How to avoid:**
1. Add `client_id` to `Invitation::$fillable`.
2. Add `'client_id' => ['nullable', 'exists:clients,id']` to `store` validation.
3. Pass `client_id` in the `Invitation::create()` call.
4. In `accept`, after `User::create()`, add `$user->update(['client_id' => $invitation->client_id])` — this requires `client_id` in `User::$fillable`.

**Warning signs:** `users.client_id` is null for clients who registered via invitation from the detail page.

### Pitfall 2: `User::$fillable` Missing `client_id`

**What goes wrong:** `User::create([..., 'client_id' => $x])` silently drops `client_id` due to mass assignment protection.

**Why it happens:** The current `User` model `$fillable` only contains `['name', 'email', 'password', 'role']`.

**How to avoid:** Add `'client_id'` to `User::$fillable` as part of this phase's migration task.

**Warning signs:** `$user->client_id` is null immediately after create even though the invitation had a `client_id`.

### Pitfall 3: `unique:clients,email` Fails on Edit for Own Email

**What goes wrong:** Admin edits a client and saves without changing the email. Validation throws "The email has already been taken."

**Why it happens:** The unique rule checks all rows without excluding the current record.

**How to avoid:** Use `"unique:clients,email,{$client->id}"` in the `update` validation — the third segment tells Laravel to ignore the row with that ID.

### Pitfall 4: DELETE Route Blocked by Missing CSRF Token in Modal

**What goes wrong:** `router.delete('/clients/1')` returns 419 Page Expired.

**Why it happens:** Inertia uses axios under the hood and includes CSRF token automatically, but only if the `meta[name=csrf-token]` tag exists in the HTML head. Laravel Breeze scaffolds this in `app.blade.php` — it should already be present.

**How to avoid:** Verify `app.blade.php` has `<meta name="csrf-token" content="{{ csrf_token() }}">`. This is included by default in Breeze.

### Pitfall 5: Inertia Pagination Object Shape in Vue

**What goes wrong:** Trying to use `clients.data` but the paginator shape is unfamiliar — iterating over `clients` directly fails.

**Why it happens:** Laravel's `paginate()` returns a `LengthAwarePaginator` which Inertia serializes as `{ data: [...], links: [...], meta: {...} }`.

**How to avoid:** In Index.vue, always iterate `clients.data` and use `clients.links` for the pagination nav. The prop type is `Object`, not `Array`.

### Pitfall 6: Migration Order — `clients` Must Exist Before FKs

**What goes wrong:** Migration that adds `client_id` FK to `users` runs before `clients` table exists.

**Why it happens:** Timestamp-based migration ordering.

**How to avoid:** Use three migrations with ascending timestamps in this order:
1. `create_clients_table`
2. `add_client_id_to_users_table` (FK references `clients.id`)
3. `add_client_id_to_invitations_table` (FK references `clients.id`)

All three run in this phase. Use `$table->foreignId('client_id')->nullable()->constrained()->nullOnDelete()` — `nullOnDelete()` means deleting a client won't cascade-delete the user, it just nulls the FK.

---

## Code Examples

### Migration: clients table
```php
// Source: Laravel 12 official docs — migrations
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('email')->unique();
    $table->string('empresa')->nullable();
    $table->string('telefono')->nullable();
    $table->text('stack_tecnologico')->nullable();
    $table->enum('estado', ['activo', 'potencial', 'pausado'])->default('activo');
    $table->text('notas')->nullable();
    $table->date('fecha_inicio')->nullable();
    $table->timestamps();
});
```

### Migration: add client_id to users
```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('client_id')
          ->nullable()
          ->after('role')
          ->constrained()
          ->nullOnDelete();
});
```

### Migration: add client_id to invitations
```php
Schema::table('invitations', function (Blueprint $table) {
    $table->foreignId('client_id')
          ->nullable()
          ->after('client_name')
          ->constrained()
          ->nullOnDelete();
});
```

### InvitationController@accept — add client_id propagation
```php
// After User::create():
if ($invitation->client_id) {
    $user->update(['client_id' => $invitation->client_id]);
}
$invitation->update(['used_at' => now()]);
```

### Inertia Pagination Links Component (inline, no external library)
```vue
<template v-for="link in clients.links" :key="link.label">
    <Link
        v-if="link.url"
        :href="link.url"
        class="px-3 py-1 border rounded text-sm"
        :class="{ 'bg-blue-600 text-white': link.active }"
        v-html="link.label"
        preserve-state
    />
    <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
</template>
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| `Inertia::share()` in `AppServiceProvider` | `HandleInertiaRequests::share()` middleware | Inertia Laravel v1+ | Already done in Phase 1 — `auth.user` shared there |
| `form.errors` via session flash | `form.errors` via Inertia props | Inertia v1+ | `useForm` populates `form.errors` from Laravel validation — no manual session reading |
| `router.visit('/path', { method: 'delete' })` | `router.delete('/path')` | @inertiajs/vue3 v1+ | Method shorthand is cleaner; both work |

**Deprecated/outdated:**
- `Inertia::share()` static call in service provider: Replaced by `HandleInertiaRequests` middleware (already in this project).
- `$page.props.errors` direct access: Replaced by `form.errors` on the useForm instance — more ergonomic.

---

## Open Questions

1. **Soft deletes vs hard deletes**
   - What we know: CONTEXT.md leaves this to Claude's discretion; Phase 3 (tasks) references clients, Phase 4 (billing) references clients.
   - What's unclear: Whether deleting a client with associated tasks/invoices should be blocked or cascade.
   - Recommendation: Hard delete with `nullOnDelete()` on the FK for this phase — no tasks or invoices exist yet. Add a guard in `destroy`: if `$client->user()->exists()`, return back with an error. Tasks/billing can add their own FK constraints in their phases.

2. **Pagination count for client list**
   - What we know: This is Claude's discretion per CONTEXT.md.
   - What's unclear: Expected number of clients in production.
   - Recommendation: `paginate(20)` — standard default, easy to change later.

3. **`InvitationController@store` currently validates `unique:users,email`**
   - What we know: This prevents inviting a client whose email already exists in `users`.
   - What's unclear: Should it also validate `unique:clients,email`? (Prevents re-inviting a client who is already in the CRM but has no user yet.)
   - Recommendation: Keep `unique:users,email` as-is. If the client already has an active user, the Show page will show the "ya tiene cuenta activa" message and hide the invite button before the admin even reaches the form.

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | PHPUnit (Laravel 12 default) |
| Config file | `phpunit.xml` |
| Quick run command | `php artisan test --filter ClientTest` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| CLIE-01 | Admin creates client with valid data → stored in DB | Feature | `php artisan test --filter ClientTest::test_admin_can_create_client` | Wave 0 |
| CLIE-01 | Admin edits client → changes persist | Feature | `php artisan test --filter ClientTest::test_admin_can_update_client` | Wave 0 |
| CLIE-01 | Admin deletes client → removed from DB | Feature | `php artisan test --filter ClientTest::test_admin_can_delete_client` | Wave 0 |
| CLIE-02 | All fields stored and retrieved correctly | Feature | `php artisan test --filter ClientTest::test_client_stores_all_fields` | Wave 0 |
| CLIE-03 | Index page returns clients list | Feature | `php artisan test --filter ClientTest::test_admin_can_view_clients_list` | Wave 0 |
| CLIE-03 | Estado filter returns only matching clients | Feature | `php artisan test --filter ClientTest::test_estado_filter_returns_correct_clients` | Wave 0 |
| CLIE-04 | Show page returns client detail | Feature | `php artisan test --filter ClientTest::test_admin_can_view_client_detail` | Wave 0 |
| CLIE-01 | Client invite button POSTs to /invitations with client_id | Feature | `php artisan test --filter ClientTest::test_invite_button_wires_invitation_with_client_id` | Wave 0 |
| CLIE-01 | Client with active user shows "ya tiene cuenta activa" — no invite sent | Feature | `php artisan test --filter ClientTest::test_client_with_active_user_blocks_invite` | Wave 0 |
| CLIE-01 | `users.client_id` is set after accepting invitation | Feature | `php artisan test --filter InvitationTest::test_accept_sets_user_client_id` | Wave 0 |

### Sampling Rate
- **Per task commit:** `php artisan test --filter ClientTest`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/ClientTest.php` — covers all CLIE-01 through CLIE-04 behaviors listed above
- [ ] `tests/Feature/Auth/InvitationTest.php` — needs one additional test: `test_accept_sets_user_client_id` (add to existing file)

---

## Sources

### Primary (HIGH confidence)
- Codebase direct inspection — `app/Models/User.php`, `app/Models/Invitation.php`, `app/Http/Controllers/InvitationController.php`, `routes/web.php`, `bootstrap/app.php`, `resources/js/Pages/Admin/Invitations/Create.vue`, `resources/js/Layouts/AdminLayout.vue`
- `php artisan --version` — confirmed Laravel 12.55.1
- `package.json` — confirmed @inertiajs/vue3 ^2.0.0, Vue ^3.4.0, Tailwind ^3.2.1
- `composer.json` — confirmed inertiajs/inertia-laravel ^2.0
- Existing migrations — confirmed `users` and `invitations` table schemas

### Secondary (MEDIUM confidence)
- Laravel 12 resource controller docs (route/method mapping follows long-stable convention — no breaking changes from Laravel 11)
- Inertia.js v2 `useForm` API — established pattern, matches exactly what Phase 1 already uses in Create.vue

### Tertiary (LOW confidence)
- None — all claims are verifiable from installed codebase or stable Laravel/Inertia conventions

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — confirmed from installed packages in package.json and composer.json
- Architecture: HIGH — all patterns replicated from existing Phase 1 code in this codebase
- Pitfalls: HIGH — derived from direct inspection of existing model fillables, migration schemas, and controller logic
- Migration strategy: HIGH — standard Laravel FK migration ordering, confirmed `nullOnDelete()` as correct choice

**Research date:** 2026-03-20
**Valid until:** 2026-04-20 (stable framework versions, no fast-moving dependencies in this phase)
