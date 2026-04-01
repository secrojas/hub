# Phase 3: Tareas y Kanban - Research

**Researched:** 2026-03-20
**Domain:** Laravel 12 + Inertia v2 + Vue 3 — Kanban board with drag & drop, inline modals, multi-param filters
**Confidence:** HIGH (stack confirmed against installed packages; drag-drop library verified against npm registry)

---

## Summary

This phase adds a Task model with a `tasks` table linked to `clients`, a `TaskController` with a standard resource plus a separate `updateStatus` endpoint, a Kanban board page (`/tasks`) with four fixed columns, drag-and-drop between columns via a Vue 3 native library, and inline modals for create/edit/delete — all following the patterns already established in Phase 2.

The critical research finding is that **`@dnd-kit/core` is React-only** and has no official Vue binding. The CONTEXT.md decision to use it must be redirected to `vue-draggable-plus` (v0.6.1, built on SortableJS, 38k weekly downloads, actively maintained) or `@vue-dnd-kit/core` (v2.2.0, Vue-native composables, 209 GitHub stars, last release March 9 2026, explicit Kanban component). `@vue-dnd-kit/core` is newer and smaller; `vue-draggable-plus` is more battle-tested. Recommendation: use `vue-draggable-plus` because it is SortableJS-backed (proven), has the simpler `group` API for cross-column drag, and has a larger ecosystem footprint. The project does NOT have `@vueuse/core` installed; `@vue-dnd-kit/core` requires it as a peer dep, which adds install overhead.

The optimistic-update-with-rollback pattern for drag & drop is handled manually in Inertia v2 (v3 beta has native `.optimistic()`). The pattern is: clone local state before mutation, mutate reactively, fire `router.put()`, restore clone in `onError` callback.

**Primary recommendation:** Use `vue-draggable-plus` with the `group` prop for cross-column drag. Keep all Inertia calls as `router.put` with manual optimistic rollback. Inline modals follow the `ref(null)` sentinel pattern already used in Phase 2.

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- Drag & drop: use `@dnd-kit/core` + `@dnd-kit/sortable` — **OVERRIDE: verified React-only, no Vue binding. Use `vue-draggable-plus` instead (see research findings)**
- On drop to new column: Inertia `PUT /tasks/{id}/status` to persist
- No internal column reordering — drag between columns only (status change)
- Modales inline Vue para crear y editar (NO páginas separadas)
- 4 columnas fijas: `backlog` / `en_progreso` / `en_revision` / `finalizado`
- Card shows: título, nombre del cliente (vista global), badge prioridad (rojo=alta, amarillo=media, verde=baja), fecha límite si existe
- Vista global `/tasks` — filtrable por cliente via `?cliente={id}`, y también TASK-05 filtro por estado y búsqueda por título
- `tasks` table fields: `titulo` (req), `client_id` (req FK), `descripcion` (nullable text), `prioridad` (enum baja/media/alta default media), `estado` (enum backlog/en_progreso/en_revision/finalizado default backlog), `fecha_limite` (nullable date)
- `estado` no seleccionable al crear — siempre `backlog`
- Borrar tarea desde el modal de edición (confirmación inline, no página separada)
- Link "Ver Kanban" en `/clients/{id}` → `/tasks?cliente={id}`

### Claude's Discretion
- Estructura interna de los componentes Vue del Kanban (KanbanBoard, KanbanColumn, TaskCard)
- Implementación del wrapper de drag & drop para Vue 3
- Colores exactos de los badges de prioridad
- Paginación o carga completa en el Kanban — recomendado carga completa, máximo razonable

### Deferred Ideas (OUT OF SCOPE)
- Ordenamiento interno de tareas dentro de una columna (requiere campo `order`)
- Notificaciones al cliente cuando tarea cambia de estado
- Comentarios o actividad en tareas
- Asignación de tareas a personas
- Subtareas
- Etiquetas/tags
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| TASK-01 | El admin puede crear tareas vinculadas a un cliente con: título, descripción, prioridad y fecha límite opcional | `TaskController::store()` + `StoreTaskRequest` validation + inline create modal with `useForm` |
| TASK-02 | El admin puede editar y eliminar tareas | `TaskController::update()` + `destroy()` + inline edit modal with `useForm`, delete button with confirmation |
| TASK-03 | El admin puede ver el tablero Kanban por cliente con drag-and-drop | `vue-draggable-plus` `group` prop, `TaskController::index()` scoped to `?cliente=`, 4 fixed columns, `PUT /tasks/{id}/status` on drop |
| TASK-04 | El admin puede ver una vista global del Kanban con tareas de todos los clientes | `/tasks` without client filter — all tasks grouped by status column, client name shown on each card |
| TASK-05 | El admin puede filtrar o buscar tareas por título, estado o prioridad | `router.get('/tasks', filters, { preserveState: true, preserveScroll: true })` with `?cliente=`, `?estado=`, `?titulo=`, `?prioridad=` query params |
</phase_requirements>

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| vue-draggable-plus | 0.6.1 | Cross-column drag & drop (Kanban) | Built on SortableJS (battle-tested), `group` API is the simplest multi-list solution, 38k weekly downloads, actively maintained, no extra peer deps |
| @inertiajs/vue3 | 2.3.18 (installed) | Page navigation, `useForm`, `router.put/get` | Already installed, all patterns established |
| vue | 3.4.x (installed) | Reactivity, composables, `ref`, `computed` | Already installed |
| tailwindcss | 3.x (installed) | Styling for board layout, badges | Already installed |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @vue-dnd-kit/core | 2.2.0 (available) | Alternative Vue-native composable DnD | Only if vue-draggable-plus proves insufficient; requires @vueuse/core peer dep not currently installed |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| vue-draggable-plus | @vue-dnd-kit/core | @vue-dnd-kit is more "Vue-native" with composables but v2.2.0 has 209 stars vs established SortableJS ecosystem; also requires @vueuse/core peer dep |
| vue-draggable-plus | Native HTML5 DnD | Zero deps but no cross-browser touch support, no animation, significant boilerplate |
| vue-draggable-plus | @dnd-kit/core | **NOT COMPATIBLE**: React-only, no official Vue 3 binding |

**Installation:**
```bash
npm install vue-draggable-plus
```

**Version verification:** Confirmed `vue-draggable-plus@0.6.1` via `npm view vue-draggable-plus version` on 2026-03-20.

---

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Enums/
│   ├── Role.php              # existing
│   ├── TaskStatus.php        # NEW — backlog/en_progreso/en_revision/finalizado
│   └── TaskPriority.php      # NEW — baja/media/alta
├── Models/
│   ├── Client.php            # add tasks() hasMany
│   └── Task.php              # NEW
├── Http/Controllers/
│   └── TaskController.php    # NEW — resource + updateStatus
database/
└── migrations/
    └── 2026_03_20_000004_create_tasks_table.php  # NEW
resources/js/Pages/Admin/
└── Tasks/
    └── Index.vue             # Kanban board + create modal + edit modal
```

### Pattern 1: Tasks Migration — Exact Schema
**What:** The `tasks` table matching every locked field decision.
**When to use:** Single migration, run once.
```php
// Source: Locked decisions from CONTEXT.md + existing clients migration pattern
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained()->cascadeOnDelete();
    $table->string('titulo');
    $table->text('descripcion')->nullable();
    $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
    $table->enum('estado', ['backlog', 'en_progreso', 'en_revision', 'finalizado'])->default('backlog');
    $table->date('fecha_limite')->nullable();
    $table->timestamps();
});
```

**Note on FK cascade:** Phase 6 (portal del cliente) reads tasks. Use `cascadeOnDelete()` — if a client is deleted their tasks are deleted too. This is intentional (tasks without a client are orphaned data).

### Pattern 2: PHP Backed Enums for TaskStatus and TaskPriority
**What:** String-backed PHP enums replacing raw `in:` validation strings — consistent with existing `Role` enum pattern.
**When to use:** Everywhere validation, casting, and comparison happens.
```php
// app/Enums/TaskStatus.php
namespace App\Enums;

enum TaskStatus: string
{
    case Backlog     = 'backlog';
    case EnProgreso  = 'en_progreso';
    case EnRevision  = 'en_revision';
    case Finalizado  = 'finalizado';
}

// app/Enums/TaskPriority.php
namespace App\Enums;

enum TaskPriority: string
{
    case Baja  = 'baja';
    case Media = 'media';
    case Alta  = 'alta';
}
```

Model casts (consistent with Client.php `casts()` method pattern):
```php
protected function casts(): array
{
    return [
        'estado'      => TaskStatus::class,
        'prioridad'   => TaskPriority::class,
        'fecha_limite' => 'date',
    ];
}
```

### Pattern 3: TaskController — Resource + Separate updateStatus
**What:** Standard resource for CRUD, one extra method for drag & drop status change. Consistent with existing `ClientController` pattern.
**When to use:** This exact split — never handle status change in `update()` to keep the drag endpoint minimal and fast.

```php
// Route registration in web.php admin group
Route::resource('tasks', TaskController::class)->except(['show', 'create', 'edit']);
Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
```

`updateStatus` method:
```php
public function updateStatus(Request $request, Task $task)
{
    $request->validate([
        'estado' => ['required', 'in:backlog,en_progreso,en_revision,finalizado'],
    ]);

    $task->update(['estado' => $request->estado]);

    return back();
}
```

`index` method — scoped by optional client filter, grouped by status for the Kanban:
```php
public function index(Request $request)
{
    $query = Task::with('client')
        ->when($request->filled('cliente'), fn ($q) => $q->where('client_id', $request->cliente))
        ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
        ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->prioridad))
        ->when($request->filled('titulo'), fn ($q) => $q->where('titulo', 'like', "%{$request->titulo}%"))
        ->latest()
        ->get();

    // Group by status for Kanban columns
    $columns = [
        'backlog'      => $query->where('estado', 'backlog')->values(),
        'en_progreso'  => $query->where('estado', 'en_progreso')->values(),
        'en_revision'  => $query->where('estado', 'en_revision')->values(),
        'finalizado'   => $query->where('estado', 'finalizado')->values(),
    ];

    return Inertia::render('Admin/Tasks/Index', [
        'columns'        => $columns,
        'clients'        => Client::orderBy('nombre')->get(['id', 'nombre']),
        'filtros'        => $request->only(['cliente', 'estado', 'prioridad', 'titulo']),
    ]);
}
```

**Why load all tasks at once:** The Kanban board needs all tasks visible across 4 columns. Pagination would break the board layout. Load fully; use filters to reduce the set. At freelancer scale (< 200 tasks), a full load is fine.

### Pattern 4: Drag & Drop with vue-draggable-plus — Optimistic Update + Rollback
**What:** Cross-column drag using `vue-draggable-plus` `<VueDraggable>` component with `group` prop. Manual optimistic update + rollback since Inertia v2 has no built-in `.optimistic()`.
**When to use:** On every drop event that changes a task's column.

```vue
<script setup>
import { VueDraggable } from 'vue-draggable-plus'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ columns: Object, filtros: Object, clients: Array })

// Local reactive copy for optimistic UI
const localColumns = ref({
    backlog:     [...props.columns.backlog],
    en_progreso: [...props.columns.en_progreso],
    en_revision: [...props.columns.en_revision],
    finalizado:  [...props.columns.finalizado],
})

function onColumnChange(newStatus, event) {
    // event.added fires when an item lands in this column
    if (!event.added) return

    const task = event.added.element
    const previousColumns = JSON.parse(JSON.stringify(localColumns.value))

    // Optimistic: already moved by VueDraggable v-model mutation
    router.put(`/tasks/${task.id}/status`, { estado: newStatus }, {
        preserveState: true,
        preserveScroll: true,
        onError: () => {
            // Rollback to snapshot
            localColumns.value = previousColumns
        },
    })
}
</script>

<template>
    <div class="flex gap-4 overflow-x-auto">
        <div v-for="(tasks, status) in localColumns" :key="status" class="w-72 flex-shrink-0">
            <h3 class="font-semibold text-gray-700 mb-3">{{ columnLabel(status) }}</h3>
            <VueDraggable
                v-model="localColumns[status]"
                :group="{ name: 'tasks', pull: true, put: true }"
                item-key="id"
                class="min-h-16 space-y-2"
                @change="(e) => onColumnChange(status, e)"
            >
                <template #item="{ element: task }">
                    <TaskCard :task="task" @click="openEdit(task)" />
                </template>
            </VueDraggable>
        </div>
    </div>
</template>
```

**Key detail:** `VueDraggable` mutates the `v-model` array automatically when a card is dropped. The `@change` event fires after the mutation. Snapshot must be taken **before** the mutation fires — take it at `dragstart` instead, or accept that the snapshot-after approach requires an undo of the already-applied mutation (rollback by re-assigning the full `localColumns` from the pre-drag snapshot). The simplest approach: snapshot at the start of `onColumnChange` before calling `router.put`, since the mutation has already been applied visually — on rollback, replace the entire `localColumns`.

### Pattern 5: Inline Modals with useForm — Create + Edit in Same Page
**What:** Two modals on the same `Index.vue` page. Consistent with Phase 2 `ref(null)` sentinel pattern.
**When to use:** Both modals reuse the same `useForm` instance reset on open.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

// null = closed, Object = editing that task
const editingTask = ref(null)
const showCreateModal = ref(false)

const form = useForm({
    titulo:       '',
    client_id:    null,
    descripcion:  '',
    prioridad:    'media',
    fecha_limite: '',
    // estado is NOT in the form — backend always sets 'backlog' on create
})

function openCreate() {
    form.reset()
    form.clearErrors()
    showCreateModal.value = true
}

function openEdit(task) {
    form.titulo       = task.titulo
    form.client_id    = task.client_id
    form.descripcion  = task.descripcion ?? ''
    form.prioridad    = task.prioridad
    form.fecha_limite = task.fecha_limite ?? ''
    form.clearErrors()
    editingTask.value = task
}

function submitCreate() {
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => { showCreateModal.value = false; form.reset() },
    })
}

function submitEdit() {
    form.put(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { editingTask.value = null; form.reset() },
    })
}

function deleteTask() {
    if (!confirm('¿Eliminar esta tarea?')) return
    router.delete(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { editingTask.value = null },
    })
}
</script>
```

### Pattern 6: Multi-Param Filters with Inertia (TASK-05)
**What:** Filtering by `?titulo=`, `?cliente=`, `?estado=`, `?prioridad=` using `router.get` with `preserveState`.
**When to use:** Every filter input change. Consistent with ClientController filter pattern.

```javascript
// Source: established pattern from Phase 2 + Inertia v2 manual-visits docs
import { router } from '@inertiajs/vue3'

function applyFilters(newFilters) {
    router.get('/tasks', newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,  // avoids polluting browser history with each keystroke
    })
}
```

The controller receives `$request->only(['cliente', 'estado', 'prioridad', 'titulo'])` and passes `filtros` back as a prop so the Vue component can populate inputs reactively.

**`replace: true` is important** for text search filters that fire on every keystroke — prevents a history entry per keypress.

### Pattern 7: Adding tasks() to Client Model
```php
// app/Models/Client.php — add import and method
use Illuminate\Database\Eloquent\Relations\HasMany;

public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}
```

### Anti-Patterns to Avoid
- **Using @dnd-kit/core directly:** React-only library. Will not work with Vue 3.
- **Handling status change in `update()`:** The drag endpoint should be minimal. Mixing full-update validation with status-only change creates over-validation on every drag.
- **Storing `localColumns` computed from props without deep clone:** Vue reactivity means mutating `props.columns` directly causes Inertia to warn and breaks rollback. Always initialize `localColumns` from a deep copy.
- **Using `router.visit` instead of `router.put` for status update:** `router.visit` defaults to GET. Use `router.put` explicitly.
- **Using PHP `enum()` DB column type:** The existing `clients` table uses `$table->enum(...)` (MySQL ENUM). Stick to the same pattern for consistency with the codebase. Do NOT use `$table->string()` with a cast — the team has established MySQL-level ENUM constraint.
- **Creating separate Create/Edit pages for tasks:** CONTEXT.md explicitly locks these as inline modals.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Cross-list drag & drop | Custom pointer event handler | vue-draggable-plus | Touch support, accessibility, edge cases (nested scroll, ghost element), SortableJS battle-tested in production |
| Form state + error binding | Manual `reactive({})` + fetch | `useForm` from `@inertiajs/vue3` | Handles `processing`, `errors`, `reset()`, `clearErrors()` out of the box — already used in all Phase 1 & 2 forms |
| Status validation | Manual string comparison | PHP backed enum + `in:` rule | DB-level constraint + type-safe PHP access |

---

## Common Pitfalls

### Pitfall 1: @dnd-kit is React-Only
**What goes wrong:** Installing `@dnd-kit/core` and trying to use it in Vue 3 — it imports React hooks internally and will throw `Invalid hook call` or simply be undefined.
**Why it happens:** CONTEXT.md mentions `@dnd-kit` by name but it was proposed before verifying Vue compatibility. The original @dnd-kit GitHub issue #1221 explicitly confirms no Vue version is planned.
**How to avoid:** Use `vue-draggable-plus`. It wraps SortableJS which is framework-agnostic.
**Warning signs:** Any `useContext` or `React.createElement` error in Vite console.

### Pitfall 2: Inertia v2 Has No Built-in Optimistic Updates
**What goes wrong:** Expecting `.optimistic()` method to exist — it is a v3 beta feature. Calling it on a v2 router will throw `router.put(...).optimistic is not a function`.
**Why it happens:** Search results surface Inertia v3 beta docs prominently.
**How to avoid:** Use the manual snapshot pattern — clone `localColumns` before mutating, restore in `onError`.

### Pitfall 3: Inertia Full Page Reload After updateStatus Replaces localColumns
**What goes wrong:** After `router.put('/tasks/{id}/status')` succeeds, Inertia replaces the page props with a fresh server response. This means `props.columns` changes, and if `localColumns` is `ref([...props.columns])` initialized only once, it becomes stale.
**Why it happens:** `preserveState: true` prevents component destruction but props ARE updated. The `localColumns` ref needs to sync with fresh props after navigation.
**How to avoid:** Use a `watch(() => props.columns, ...)` to re-sync `localColumns` when props update after successful Inertia visit:
```javascript
watch(() => props.columns, (newColumns) => {
    localColumns.value = {
        backlog:     [...newColumns.backlog],
        en_progreso: [...newColumns.en_progreso],
        en_revision: [...newColumns.en_revision],
        finalizado:  [...newColumns.finalizado],
    }
}, { deep: true })
```

### Pitfall 4: VueDraggable @change Fires for Both add and remove Events
**What goes wrong:** `@change` emits `{ added: ... }`, `{ removed: ... }`, or `{ moved: ... }`. If you fire `router.put` on both `added` AND `removed` you'll trigger two API calls per drag.
**How to avoid:** Guard on `if (!event.added) return` — only the target column's `added` event should trigger the status update. The source column fires `removed` — ignore it.

### Pitfall 5: `fecha_limite` Date Format Mismatch
**What goes wrong:** HTML `<input type="date">` returns `YYYY-MM-DD`. If the model casts `fecha_limite` to Carbon date, Inertia serializes it as an ISO 8601 timestamp when sending back to Vue. Comparing or pre-filling the form may break.
**How to avoid:** In the controller, add `fecha_limite` to the resource serialization as `$task->fecha_limite?->format('Y-m-d')`. Or use `$casts = ['fecha_limite' => 'date:Y-m-d']` with format argument (not natively supported — use `->toDateString()` in the resource/controller response explicitly).

### Pitfall 6: MySQL ENUM Column Modification Costs
**What goes wrong:** Adding a new status value later (e.g., for Phase 6 or v2) requires an `ALTER TABLE` on the enum column, which in MySQL 8 can lock the table briefly.
**Why it matters for this phase:** The 4 status values are locked by CONTEXT.md. Do not add extra values "just in case". The table will need an `ALTER TABLE` for any future status.
**How to avoid:** Accept the constraint. The 4 values are sufficient for v1 + Phase 6 portal read-only.

### Pitfall 7: Filter State Lost After Drag Drop Navigation
**What goes wrong:** After drag triggers `router.put` with `preserveState: true`, the active filters are preserved in Vue state but the URL does not update. If the user refreshes, filters are lost.
**Why it happens:** `preserveState: true` on the PUT request keeps local Vue state but the GET `/tasks?cliente=X` URL is separate from the PUT `/tasks/{id}/status` call.
**How to avoid:** This is acceptable behavior — drag drop does not re-run the filter GET. The filters remain visually active because `preserveState` keeps the Vue form values. The user must explicitly apply filters to refresh the filtered view. Document this in comments.

---

## Code Examples

Verified patterns from the established project codebase:

### Existing: Filter Pattern (from ClientController)
```php
// Source: app/Http/Controllers/ClientController.php (Phase 2)
$clients = Client::query()
    ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
    ->latest()
    ->paginate(20)
    ->withQueryString();
```
Tasks use the same `->when()` chaining; no pagination (full load for Kanban).

### Existing: preserveState Filter in Vue (from Phase 2 pattern)
```javascript
// Source: established pattern, Inertia v2 docs
router.get('/clients', { estado: newValue }, {
    preserveState: true,
    preserveScroll: true,
})
```

### Existing: useForm Submit Pattern (from Accept.vue / Show.vue)
```javascript
// Source: resources/js/Pages/Admin/Clients/Show.vue (Phase 2)
const form = useForm({ ... })
form.post('/invitations', { preserveScroll: true })
// form.processing, form.errors.field available reactively
```

### Existing: PHP Enum Pattern (from app/Enums/Role.php)
```php
// Source: app/Enums/Role.php (Phase 1)
enum Role: string
{
    case Admin  = 'admin';
    case Client = 'client';
}
```
TaskStatus and TaskPriority follow identical structure.

### New: VueDraggable Multi-List Setup
```vue
<!-- Source: vue-draggable-plus docs + SortableJS group API -->
<VueDraggable
    v-model="localColumns[status]"
    :group="{ name: 'tasks', pull: true, put: true }"
    item-key="id"
    @change="(e) => onColumnChange(status, e)"
>
    <template #item="{ element: task }">
        <!-- task card here -->
    </template>
</VueDraggable>
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| vuedraggable (Vue 2 only) | vue-draggable-plus (Vue 3, SortableJS) | 2023 — vue.draggable.next went unmaintained | vue-draggable-plus is the maintained drop-in replacement |
| @dnd-kit/core (React) | @vue-dnd-kit/core for Vue-native composable approach | 2025 — community-built wrapper | Alternative to vue-draggable-plus; requires @vueuse/core |
| Inertia v1 `Inertia.put()` | Inertia v2 `router.put()` | 2024 Inertia v2 release | API already used in this project |
| Inertia v2 manual optimistic | Inertia v3 `.optimistic()` (beta) | 2025 v3 beta | v3 not stable yet; stay on v2 manual pattern |

**Deprecated/outdated:**
- `vue.draggable.next` (SortableJS/vue.draggable.next): abandoned, issues #216 and #260 confirm no maintenance
- `Inertia.put()` / `this.$inertia.put()`: old Inertia v1 API — replaced by `import { router } from '@inertiajs/vue3'`

---

## Open Questions

1. **Should `cascadeOnDelete` or `nullOnDelete` be used on `tasks.client_id`?**
   - What we know: Phase 2 used `nullOnDelete` on `users.client_id` and `invitations.client_id` to allow client deletion without cascade. Tasks are semantically owned by a client (not reusable).
   - What's unclear: If a client is deleted, should their tasks disappear too?
   - Recommendation: Use `cascadeOnDelete` for tasks — orphaned tasks with `client_id = null` have no business value and would break the Kanban (no client to display). This differs from users/invitations where the nullification preserved history.

2. **Should the Kanban load all tasks or cap at N per column?**
   - What we know: CONTEXT.md says "carga completa para vista global, máximo razonable" at Claude's discretion.
   - Recommendation: Load all tasks with no pagination for v1. At freelancer scale this is < 200 tasks total. Add `->latest()` sort so newest tasks appear first within each column.

3. **How should `fecha_limite` be serialized from Laravel to Vue for date input pre-fill?**
   - What we know: `casts` with `'date'` returns Carbon; Inertia JSON serializes Carbon as ISO 8601 (with time component).
   - What's unclear: Will `<input type="date" :value="task.fecha_limite">` auto-parse ISO 8601?
   - Recommendation: In the controller, serialize explicitly as `$task->fecha_limite?->format('Y-m-d')` in the response, or use a Task resource. Simplest: add `'fecha_limite' => $task->fecha_limite?->toDateString()` in the controller response mapping.

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | PHPUnit (Laravel built-in) |
| Config file | `phpunit.xml` (project root) |
| Quick run command | `php artisan test --filter TaskTest` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| TASK-01 | Admin can create task with required fields | feature | `php artisan test --filter TaskCrudTest::test_admin_can_create_task` | ❌ Wave 0 |
| TASK-01 | Create task validation (titulo required, client_id required) | feature | `php artisan test --filter TaskCrudTest::test_create_task_validation` | ❌ Wave 0 |
| TASK-02 | Admin can edit a task | feature | `php artisan test --filter TaskCrudTest::test_admin_can_update_task` | ❌ Wave 0 |
| TASK-02 | Admin can delete a task | feature | `php artisan test --filter TaskCrudTest::test_admin_can_delete_task` | ❌ Wave 0 |
| TASK-03 | Kanban index returns tasks grouped by status (scoped to client) | feature | `php artisan test --filter TaskKanbanTest::test_index_returns_columns_scoped_to_client` | ❌ Wave 0 |
| TASK-03 | PUT /tasks/{id}/status updates estado | feature | `php artisan test --filter TaskKanbanTest::test_update_status_changes_task_estado` | ❌ Wave 0 |
| TASK-03 | PUT /tasks/{id}/status rejects invalid estado | feature | `php artisan test --filter TaskKanbanTest::test_update_status_rejects_invalid_estado` | ❌ Wave 0 |
| TASK-04 | Global view returns tasks from all clients | feature | `php artisan test --filter TaskKanbanTest::test_global_view_returns_all_clients_tasks` | ❌ Wave 0 |
| TASK-05 | Filter by cliente returns only that client's tasks | feature | `php artisan test --filter TaskFilterTest::test_filter_by_cliente` | ❌ Wave 0 |
| TASK-05 | Filter by estado returns correct tasks | feature | `php artisan test --filter TaskFilterTest::test_filter_by_estado` | ❌ Wave 0 |
| TASK-05 | Filter by titulo search returns matching tasks | feature | `php artisan test --filter TaskFilterTest::test_filter_by_titulo` | ❌ Wave 0 |
| TASK-05 | Filter by prioridad returns correct tasks | feature | `php artisan test --filter TaskFilterTest::test_filter_by_prioridad` | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `php artisan test --filter Task`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Feature/Tasks/TaskCrudTest.php` — covers TASK-01, TASK-02
- [ ] `tests/Feature/Tasks/TaskKanbanTest.php` — covers TASK-03, TASK-04
- [ ] `tests/Feature/Tasks/TaskFilterTest.php` — covers TASK-05
- [ ] `database/factories/TaskFactory.php` — needed by all task tests
- [ ] `app/Enums/TaskStatus.php` — needed by migration and model
- [ ] `app/Enums/TaskPriority.php` — needed by migration and model

---

## Sources

### Primary (HIGH confidence)
- Project codebase — `app/Enums/Role.php`, `app/Http/Controllers/ClientController.php`, `app/Models/Client.php`, `routes/web.php`, `package.json` — direct file reads
- `npm view vue-draggable-plus version` — confirmed 0.6.1 on 2026-03-20
- `npm view @vue-dnd-kit/core version` — confirmed 2.2.0 on 2026-03-20
- Inertia v2 manual visits docs (https://inertiajs.com/manual-visits) — router options, preserveState, preserveScroll, only, onError

### Secondary (MEDIUM confidence)
- vue-draggable-plus official docs (https://vue-draggable-plus.pages.dev/en/guide/) — group prop, multi-list drag
- @vue-dnd-kit/core GitHub (https://github.com/ZiZIGY/vue-dnd-kit) — v2.2.0 release March 9 2026, Kanban component exists, 209 stars
- DEV.to article on Laravel+Inertia+Vue3 Kanban (https://dev.to/blamsa0mine/building-a-task-manager-with-laravel-inertiajs-vue-3-crud-tags-filters-and-a-kanban-board-34hh) — confirmed snapshot/rollback pattern
- Laravel 12 enum docs (https://www.cygner.net/blog/using-enum-in-laravel-12:-a-complete-guide-with-examples) — PHP backed enum + Eloquent cast pattern

### Tertiary (LOW confidence)
- GitHub issue #1221 clauderic/dnd-kit (https://github.com/clauderic/dnd-kit/issues/1221) — confirms no official Vue version planned (referenced in search results, not directly fetched)
- SortableJS vue.draggable.next issue #216 and #260 (maintenance abandoned) — referenced in search results

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — installed versions confirmed via npm view and node_modules
- Architecture: HIGH — patterns derived directly from existing project code (ClientController, Role enum, Phase 2 modal pattern)
- Drag & drop library choice: HIGH — @dnd-kit Vue incompatibility confirmed, vue-draggable-plus npm stats verified
- Optimistic update pattern: MEDIUM — confirmed approach from DEV.to article + Inertia v2 docs; Inertia v3 native approach not applicable
- Pitfalls: MEDIUM — derived from library behavior and Inertia mechanics; some edge cases (fecha_limite serialization) are LOW until validated in implementation

**Research date:** 2026-03-20
**Valid until:** 2026-05-01 (stable stack; vue-draggable-plus and @vue-dnd-kit/core are moving but the SortableJS group API is stable)
