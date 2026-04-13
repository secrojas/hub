# Phase 1: Knowledge Base Module - Research

**Researched:** 2026-04-13
**Domain:** Laravel 12 + Inertia.js v2 + Vue 3 — new independent module replicating Notes module patterns
**Confidence:** HIGH

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| REQ-KB-1 | Modelo `KnowledgeEntry` con schema rico (id estable, type, status, confidence, source, domain, subdomain, summary, avature_version, embedding_priority) | Migration pattern verified from notes table. PHP Enums pattern verified from TaskStatus/TaskPriority. |
| REQ-KB-2 | Tabla `knowledge_links` con tipos de relación semántica (explains, depends_on, solves, contradicts, updates, source_of, example_of) | Design spec defines exact SQL. FK pattern verified from note_folders migration. ENUM type maps to Laravel migration. |
| REQ-KB-3 | Patrón Repository/Service con interface en Contracts/Repositories | Full pattern verified: Interface → Repository → Service → AppServiceProvider::bind. |
| REQ-KB-4 | CRUD completo en `/knowledge` con Inertia.js — rutas resource + FormRequests | NoteController pattern is the direct template. Route naming, FormRequest validation, redirect patterns all verified. |
| REQ-KB-5 | UI con filtros por type, status, domain (reemplaza folder sidebar de notas) | Index page pattern verified. Debounced router.get with preserveState. Dropdowns over sidebar. |
| REQ-KB-6 | Editor de contenido reutilizando NoteEditor (Tiptap) para el body de cada entrada | NoteEditor uses @tiptap/vue-3 v3.22.3 + StarterKit + CodeBlockLowlight. Already installed. |
| REQ-KB-7 | UI para crear links entre entradas (knowledge_links) — selector de entry + tipo de relación | No existing pattern — new UI work. Inertia form pattern with router.post applies. |
</phase_requirements>

---

## Summary

Este módulo replica la arquitectura del módulo de notas existente — mismo patrón Repository/Service, mismas convenciones de migración, mismo stack Inertia + Vue 3 — pero con un modelo de datos sustancialmente más rico y sin ninguna dependencia cruzada con el módulo de notas.

El diseño técnico está completamente especificado en `docs/knowledge-system-design.md`. La investigación confirma que todos los patrones necesarios ya existen en el proyecto y pueden replicarse directamente. El riesgo principal es la tabla `knowledge_links` con FK hacia `knowledge_entries` (self-referential relationship) y la decisión sobre el campo `entry_id` estable (slug human-readable vs. auto-increment).

**Primary recommendation:** Implementar en cinco capas secuenciales — migration → model/enums → repository/service → controller/routes → Vue pages — replicando el patrón de notas en cada capa y sin tocar ningún archivo del módulo de notas.

---

## Standard Stack

### Core (ya instalado — sin dependencias nuevas)

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel 12 | `^12.0` | Backend framework | Stack del proyecto — no negociable |
| inertiajs/inertia-laravel | `^2.0` | SSR bridge PHP → Vue | Stack del proyecto |
| @inertiajs/vue3 | `^2.0.0` | Cliente Inertia en Vue | Stack del proyecto |
| Vue 3 | `^3.4.0` | Frontend framework | Stack del proyecto |
| @tiptap/vue-3 | `^3.22.3` | Editor WYSIWYG | Ya instalado, usado en NoteEditor |
| @tiptap/starter-kit | `^3.22.3` | Extensiones base de Tiptap | Ya instalado |
| @tiptap/extension-code-block-lowlight | `^3.22.3` | Syntax highlighting | Ya instalado |
| tightenco/ziggy | `^2.0` | Route helper en JS (`route()`) | Ya instalado — usar `route('knowledge.index')` etc. |
| Tailwind CSS | `^3.2.1` | Styling | Stack del proyecto |
| @tailwindcss/typography | `^0.5.19` | Prose rendering (NoteViewer) | Ya instalado |
| MySQL 8.0 | — | Base de datos | Stack del proyecto — soporta FULLTEXT y ENUM |

### Sin dependencias nuevas

No se requiere instalar nada nuevo. Todo lo necesario para el módulo Knowledge ya está presente en el proyecto.

---

## Architecture Patterns

### Estructura de archivos a crear

```
app/
├── Enums/
│   ├── KnowledgeType.php          # concept | flow | bug | decision | runbook | glossary
│   ├── KnowledgeStatus.php        # draft | reviewed | verified | stale
│   ├── KnowledgeConfidence.php    # low | medium | high
│   ├── KnowledgeSource.php        # chatgpt | self | docs | colleague
│   ├── EmbeddingPriority.php      # high | normal
│   └── KnowledgeLinkRelation.php  # explains | depends_on | solves | contradicts | updates | source_of | example_of
├── Contracts/Repositories/
│   └── KnowledgeEntryRepositoryInterface.php
├── Models/
│   ├── KnowledgeEntry.php
│   └── KnowledgeLink.php
├── Repositories/
│   └── KnowledgeEntryRepository.php
├── Services/
│   └── KnowledgeEntryService.php
├── Http/
│   ├── Controllers/
│   │   └── KnowledgeEntryController.php
│   └── Requests/
│       ├── StoreKnowledgeEntryRequest.php
│       └── UpdateKnowledgeEntryRequest.php

database/migrations/
├── 2026_04_13_000001_create_knowledge_entries_table.php
└── 2026_04_13_000002_create_knowledge_links_table.php

resources/js/
├── Components/Knowledge/
│   ├── KnowledgeEditor.vue        # Wraps NoteEditor — o reusar NoteEditor directamente
│   └── KnowledgeFilters.vue       # type + status + domain filters (reemplaza FolderSidebar)
└── Pages/Admin/Knowledge/
    ├── Index.vue
    ├── Create.vue
    ├── Show.vue
    └── Edit.vue
```

### Pattern 1: Enum PHP Backed String (TaskStatus como referencia)

**What:** PHP 8.1+ backed enums con string values. Laravel los castea automáticamente en el modelo.
**When to use:** Siempre para campos de valor fijo como type, status, confidence, source.

```php
// app/Enums/KnowledgeType.php
namespace App\Enums;

enum KnowledgeType: string
{
    case Concept  = 'concept';
    case Flow     = 'flow';
    case Bug      = 'bug';
    case Decision = 'decision';
    case Runbook  = 'runbook';
    case Glossary = 'glossary';
}
```

En el modelo, castear con `'type' => KnowledgeType::class` en el array `casts()`.
En la migración, usar `$table->string('type')` (no columna ENUM de MySQL — los backed enums de PHP validan en app layer).

### Pattern 2: Repository/Service (NoteRepository como referencia exacta)

**What:** Interface en `Contracts/Repositories/`, implementación en `Repositories/`, Service en `Services/`, binding en `AppServiceProvider`.

```php
// app/Contracts/Repositories/KnowledgeEntryRepositoryInterface.php
namespace App\Contracts\Repositories;

use App\Models\KnowledgeEntry;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeEntryRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): KnowledgeEntry;
    public function create(array $data): KnowledgeEntry;
    public function update(KnowledgeEntry $entry, array $data): KnowledgeEntry;
    public function delete(KnowledgeEntry $entry): void;
    public function search(string $term): Collection;
    public function filterBy(array $filters): Collection;
}
```

```php
// En AppServiceProvider::register() — AGREGAR sin tocar los bindings de notas
$this->app->bind(KnowledgeEntryRepositoryInterface::class, KnowledgeEntryRepository::class);
```

### Pattern 3: Inertia Controller (NoteController como referencia exacta)

**What:** Constructor injection del Service, métodos index/create/store/show/edit/update/destroy, render con `Inertia::render('Admin/Knowledge/...')`.

```php
// app/Http/Controllers/KnowledgeEntryController.php
public function index(Request $request): Response
{
    $entries = $this->service->filterBy($request->only(['search', 'type', 'status', 'domain']));

    return Inertia::render('Admin/Knowledge/Index', [
        'entries' => $entries,
        'filters' => $request->only(['search', 'type', 'status', 'domain']),
        'types'   => KnowledgeType::cases(),
        'statuses' => KnowledgeStatus::cases(),
    ]);
}
```

### Pattern 4: Vue Page con defineOptions({ layout }) + useForm

**What:** Todas las páginas admin usan `defineOptions({ layout: AdminLayout })`. Forms usan `useForm` de `@inertiajs/vue3`.

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
    entry_id:          '',
    titulo:            '',
    type:              'concept',
    status:            'draft',
    confidence:        'medium',
    source:            'self',
    domain:            '',
    subdomain:         '',
    summary:           '',
    contenido:         '',
    avature_version:   '',
    embedding_priority: 'normal',
})
</script>
```

### Pattern 5: Ruta de búsqueda antes del resource (misma solución que notes)

**What:** Laravel route matching atrapa segmentos de path como parámetros. "search" se confundiría con `{entry}`.

```php
// routes/web.php — dentro del grupo middleware admin
Route::get('knowledge/search', [KnowledgeEntryController::class, 'index'])->name('knowledge.search');
Route::resource('knowledge', KnowledgeEntryController::class);
```

### Pattern 6: Migration — knowledge_entries

```php
Schema::create('knowledge_entries', function (Blueprint $table) {
    $table->id();
    $table->string('entry_id')->unique();          // slug human-readable: "avt-rtc-001"
    $table->string('titulo');
    $table->string('type');                         // backed enum validado en app layer
    $table->string('status')->default('draft');
    $table->string('confidence')->default('medium');
    $table->string('source')->default('self');
    $table->boolean('verified')->default(false);
    $table->string('domain')->nullable();
    $table->string('subdomain')->nullable();
    $table->json('tags')->nullable();
    $table->string('scope')->nullable();            // module | system | cross-system
    $table->text('summary')->nullable();
    $table->longText('contenido')->nullable();
    $table->string('avature_version')->nullable();
    $table->string('embedding_priority')->default('normal');
    $table->timestamps();
});
```

### Pattern 7: Migration — knowledge_links

**CRÍTICO:** El design doc referencia FKs a `notes(id)` pero este es un módulo SEPARADO. Las FKs deben apuntar a `knowledge_entries(id)`.

```php
Schema::create('knowledge_links', function (Blueprint $table) {
    $table->id();
    $table->foreignId('from_entry_id')->constrained('knowledge_entries')->cascadeOnDelete();
    $table->foreignId('to_entry_id')->constrained('knowledge_entries')->cascadeOnDelete();
    $table->string('relation_type');               // backed enum — validado en app
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Anti-Patterns a Evitar

- **NO tocar el módulo de notas**: Ningún archivo bajo `Notes/`, `NoteController.php`, `NoteService.php`, `NoteRepository.php`, `note_folders`, `notes` table. Zero modificaciones.
- **NO compartir modelos entre módulos**: `KnowledgeEntry` es completamente independiente. Si en el futuro se quiere vincular con notas, se hace vía tabla pivot nueva.
- **NO usar ENUM de MySQL**: Usar `string` en la migración + backed enum PHP + validation en FormRequest. MySQL ENUMs son difíciles de alterar en producción.
- **NO hardcodear los valores de tipo en Vue**: Pasar `types`, `statuses`, `confidences` como props desde el controller para que la UI sea DRY.
- **NO olvidar `entry_id` en `$fillable`**: Es el slug estable — campo crítico del diseño.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Editor WYSIWYG | Editor custom | NoteEditor.vue (ya existe con Tiptap) | Ya implementado, battle-tested en el módulo de notas |
| Form state y errores | Reactive refs manuales | `useForm` de @inertiajs/vue3 | Maneja processing, errors, dirty state automáticamente |
| URL routing en Vue | `window.location` o router manual | `route()` de Ziggy + `router.get()` de Inertia | Integrado, type-safe con Laravel routes |
| Búsqueda FULLTEXT | Algoritmo BM25 custom | MySQL FULLTEXT index sobre campo `summary` | MySQL 8.0 soporta FULLTEXT nativo — Etapa 1 del design doc |
| Filtros con debounce | setTimeout manual inconsistente | Patrón del NoteController index — debounce 300ms + `preserveState: true` | Ya probado en producción en módulo de notas |
| Badge/status display | Spans con clases inline | `Badge.vue` existente con `variant` prop | Ya tiene variants para draft, verified, etc. |

---

## Common Pitfalls

### Pitfall 1: FK en knowledge_links apuntando a notes en lugar de knowledge_entries

**What goes wrong:** El design doc original (`docs/knowledge-system-design.md`) especifica `FOREIGN KEY (from_note_id) REFERENCES notes(id)` — esto es de la versión donde el módulo knowledge extendía notas. En la implementación actual es un módulo separado.
**Why it happens:** Copiar el SQL del doc sin ajustar.
**How to avoid:** Las FKs de `knowledge_links` deben ser `from_entry_id` y `to_entry_id` apuntando a `knowledge_entries(id)`.
**Warning signs:** Error de migración por tabla `notes` referenciada en lugar de `knowledge_entries`.

### Pitfall 2: Ruta `/knowledge` capturada por segmento `{entry}` en search

**What goes wrong:** Con `Route::resource('knowledge', ...)` sin la ruta de search antes, `GET /knowledge/search` matchea el método `show()` con `$entry = "search"` — 404 o error de modelo.
**Why it happens:** Laravel evalúa rutas en orden. `{entry}` es greedy.
**How to avoid:** Declarar `Route::get('knowledge/search', ...)` ANTES de `Route::resource('knowledge', ...)`. Patrón ya existente en notas (línea 43 de web.php).
**Warning signs:** La página Index no filtra — el search endpoint devuelve 404.

### Pitfall 3: Enum values no mapeados en Vue

**What goes wrong:** El controller pasa `KnowledgeType::cases()` pero Vue recibe objetos PHP sin serializar correctamente.
**Why it happens:** `::cases()` retorna array de `UnitEnum` objects con `name` y `value`. Inertia los serializa, pero el frontend necesita `entry.value` no `entry.name`.
**How to avoid:** En el controller, mapear explícitamente: `collect(KnowledgeType::cases())->map(fn($c) => ['label' => $c->name, 'value' => $c->value])`. O directamente pasar un array de strings.
**Warning signs:** `<select>` muestra los valores correctos visualmente pero el value enviado al backend es el índice numérico.

### Pitfall 4: `entry_id` slug no unique con validación correcta

**What goes wrong:** Dos entradas con el mismo `entry_id` — rompe los links semánticos del grafo.
**Why it happens:** Si la validación en StoreRequest no incluye `unique:knowledge_entries,entry_id`.
**How to avoid:** En `StoreKnowledgeEntryRequest`: `'entry_id' => ['required', 'string', 'max:50', 'unique:knowledge_entries,entry_id', 'regex:/^[a-z0-9\-]+$/']`. En UpdateRequest: usar `Rule::unique('knowledge_entries', 'entry_id')->ignore($this->entry)`.
**Warning signs:** Colisión silenciosa de IDs que rompe links en knowledge_links.

### Pitfall 5: AdminLayout.vue necesita link a /knowledge — no olvidar

**What goes wrong:** El módulo existe pero no aparece en la navegación lateral.
**Why it happens:** AdminLayout es hardcoded con links fijos — no auto-discovery.
**How to avoid:** Agregar `<Link href="/knowledge" ...>Knowledge</Link>` en el nav de AdminLayout.vue. Esta es la ÚNICA modificación a un archivo existente que el módulo requiere.
**Warning signs:** Usuario no puede navegar al módulo desde el sidebar.

---

## Code Examples

### Binding en AppServiceProvider (AGREGAR, no reemplazar)

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Existentes — NO tocar
    $this->app->bind(NoteRepositoryInterface::class, NoteRepository::class);
    $this->app->bind(NoteFolderRepositoryInterface::class, NoteFolderRepository::class);

    // Nuevo
    $this->app->bind(KnowledgeEntryRepositoryInterface::class, KnowledgeEntryRepository::class);
}
```

### Index Vue con filtros múltiples (patrón extendido de notas)

```vue
<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    entries:  Array,
    filters:  Object,
    types:    Array,
    statuses: Array,
})

const search = ref(props.filters?.search ?? '')
const type   = ref(props.filters?.type ?? '')
const status = ref(props.filters?.status ?? '')
const domain = ref(props.filters?.domain ?? '')

let debounce = null

function applyFilters() {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('knowledge.index'), {
            search: search.value,
            type:   type.value,
            status: status.value,
            domain: domain.value,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }, 300)
}

watch([search, type, status, domain], applyFilters)
</script>
```

### FormRequest con validación de entry_id

```php
// app/Http/Requests/StoreKnowledgeEntryRequest.php
public function rules(): array
{
    return [
        'entry_id'          => ['required', 'string', 'max:50', 'unique:knowledge_entries,entry_id', 'regex:/^[a-z0-9\-]+$/'],
        'titulo'            => ['required', 'string', 'max:255'],
        'type'              => ['required', Rule::enum(KnowledgeType::class)],
        'status'            => ['required', Rule::enum(KnowledgeStatus::class)],
        'confidence'        => ['required', Rule::enum(KnowledgeConfidence::class)],
        'source'            => ['required', Rule::enum(KnowledgeSource::class)],
        'verified'          => ['boolean'],
        'domain'            => ['nullable', 'string', 'max:100'],
        'subdomain'         => ['nullable', 'string', 'max:100'],
        'tags'              => ['nullable', 'array'],
        'tags.*'            => ['string'],
        'scope'             => ['nullable', 'string', 'in:module,system,cross-system'],
        'summary'           => ['nullable', 'string', 'max:500'],
        'contenido'         => ['nullable', 'string'],
        'avature_version'   => ['nullable', 'string', 'max:100'],
        'embedding_priority' => ['required', Rule::enum(EmbeddingPriority::class)],
    ];
}
```

### Modelo KnowledgeEntry con casts

```php
// app/Models/KnowledgeEntry.php
protected function casts(): array
{
    return [
        'type'              => KnowledgeType::class,
        'status'            => KnowledgeStatus::class,
        'confidence'        => KnowledgeConfidence::class,
        'source'            => KnowledgeSource::class,
        'embedding_priority' => EmbeddingPriority::class,
        'verified'          => 'boolean',
        'tags'              => 'array',
    ];
}

public function links(): HasMany
{
    return $this->hasMany(KnowledgeLink::class, 'from_entry_id');
}

public function backlinks(): HasMany
{
    return $this->hasMany(KnowledgeLink::class, 'to_entry_id');
}
```

---

## State of the Art

| Old Approach | Current Approach | Impact |
|--------------|------------------|--------|
| Módulo de notas genérico (título + contenido + carpeta) | KnowledgeEntry con schema rico (type, status, confidence, source, domain, embedding_priority) | Conocimiento estructurado, queryable, AI-ready |
| Sin relaciones entre entradas | `knowledge_links` con 7 tipos de relación semántica | Knowledge graph — contexto automático, staleness cascade futuro |
| IDs auto-increment que rompen links al migrar | `entry_id` slug estable tipo `avt-rtc-001` | Links permanentes, grafo coherente a largo plazo |
| Búsqueda por LIKE en título | FULLTEXT sobre campo `summary` (planificado) | BM25 real, relevancia semántica |

---

## Open Questions

1. **¿`KnowledgeEditor` es un nuevo componente o reutiliza `NoteEditor` directamente?**
   - What we know: `NoteEditor.vue` ya tiene Tiptap configurado con StarterKit + CodeBlockLowlight. La funcionalidad es idéntica.
   - What's unclear: Si se quiere extensión específica (e.g., links entre entradas como menciones `@entry-id`) en el futuro.
   - Recommendation: Reutilizar `NoteEditor.vue` directamente en los formularios de Knowledge por ahora. Si se necesita comportamiento distinto, crear `KnowledgeEditor.vue` como wrapper.

2. **¿La UI de knowledge_links va en Show.vue o es una página separada?**
   - What we know: El diseño dice "UI para crear links entre notas". El patrón de TaskComments (inline en show) es una referencia válida.
   - What's unclear: Complejidad de la UI — un selector de entrada + tipo de relación puede ser inline en Show sin complejidad excesiva.
   - Recommendation: Implementar inline en Show.vue como sección colapsable "Relaciones". No requiere ruta separada.

3. **¿FULLTEXT index en `summary` ahora o diferido?**
   - What we know: El design doc dice "Etapa 1 — FULLTEXT sobre summary". Con MySQL 8.0 el soporte es nativo.
   - What's unclear: Si el volumen inicial justifica el overhead de configurarlo en la migración inicial.
   - Recommendation: Agregar `$table->fullText('summary')` en la migración inicial. Costo cero ahora, valor cuando haya 100+ entradas. La búsqueda sigue siendo LIKE hasta que se use `MATCH AGAINST`.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | PHPUnit 11.5.50 |
| Config file | `phpunit.xml` |
| Quick run command | `php artisan test --filter=KnowledgeEntry` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| REQ-KB-1 | KnowledgeEntry crea, edita, elimina con campos correctos | Feature/Unit | `php artisan test --filter=KnowledgeEntryTest` | ❌ Wave 0 |
| REQ-KB-2 | knowledge_links crea relación semántica entre dos entradas | Feature | `php artisan test --filter=KnowledgeLinkTest` | ❌ Wave 0 |
| REQ-KB-3 | Repository/Service retornan colecciones correctas | Unit | `php artisan test --filter=KnowledgeRepositoryTest` | ❌ Wave 0 |
| REQ-KB-4 | CRUD HTTP routes responden 200/302 correctos | Feature | `php artisan test --filter=KnowledgeControllerTest` | ❌ Wave 0 |
| REQ-KB-5 | Filtros type/status/domain retornan subset correcto | Feature | `php artisan test --filter=KnowledgeFilterTest` | ❌ Wave 0 |
| REQ-KB-6 | Editor contenido se guarda y recupera sin corrupción | Feature | `php artisan test --filter=KnowledgeContentTest` | ❌ Wave 0 |
| REQ-KB-7 | knowledge_links UI crea relación (smoke test Inertia) | Feature | `php artisan test --filter=KnowledgeLinkControllerTest` | ❌ Wave 0 |

### Sampling Rate

- **Per task commit:** `php artisan test --filter=KnowledgeEntry`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green antes de `/gsd:verify-work`

### Wave 0 Gaps

- [ ] `tests/Feature/KnowledgeEntryTest.php` — cubre REQ-KB-1, REQ-KB-4
- [ ] `tests/Feature/KnowledgeLinkTest.php` — cubre REQ-KB-2, REQ-KB-7
- [ ] `tests/Unit/KnowledgeRepositoryTest.php` — cubre REQ-KB-3
- [ ] `tests/Feature/KnowledgeFilterTest.php` — cubre REQ-KB-5

---

## Sources

### Primary (HIGH confidence)

- Código fuente verificado directamente: `app/Models/Note.php`, `app/Repositories/NoteRepository.php`, `app/Services/NoteService.php`, `app/Http/Controllers/NoteController.php`
- Código fuente verificado directamente: `app/Contracts/Repositories/NoteRepositoryInterface.php`
- Código fuente verificado directamente: `app/Providers/AppServiceProvider.php` (binding pattern)
- Código fuente verificado directamente: `routes/web.php` (route ordering pattern)
- Código fuente verificado directamente: `app/Enums/TaskStatus.php` (backed enum pattern)
- Código fuente verificado directamente: `resources/js/Pages/Admin/Notes/Index.vue`, `Create.vue`, `Show.vue`
- Código fuente verificado directamente: `resources/js/Layouts/AdminLayout.vue`
- Código fuente verificado directamente: `resources/js/Components/Notes/NoteEditor.vue`
- Código fuente verificado directamente: `resources/js/Components/UI/Badge.vue`
- Código fuente verificado directamente: `docs/knowledge-system-design.md` — spec completo del módulo
- Código fuente verificado directamente: `package.json` + `composer.json` — dependencias actuales
- Código fuente verificado directamente: todas las migrations relevantes

### Secondary (MEDIUM confidence)

- Ninguna fuente secundaria necesaria — toda la información proviene de código fuente verificado.

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — verificado en package.json y composer.json actuales
- Architecture patterns: HIGH — copiados directamente de código existente en producción
- DB schema: HIGH — design doc completo + migration patterns verificados
- Vue UI patterns: HIGH — Index/Create/Show de Notes son la referencia directa
- Pitfalls: HIGH — identificados analizando el código y el design doc en detalle

**Research date:** 2026-04-13
**Valid until:** 2026-07-13 (stack estable, sin dependencias nuevas)
