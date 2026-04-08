# Hub — Plataforma de gestión freelance

Herramienta de gestión todo-en-uno para freelancers. Centraliza clientes, tareas, facturación, presupuestos y una base de conocimiento interna en un solo panel de administración con portal de acceso para clientes.

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 · PHP 8.2 |
| Frontend | Vue 3 (Composition API `<script setup>`) |
| Bridge | Inertia.js v2 (sin API REST) |
| Estilos | Tailwind CSS v3 + Typography plugin |
| Base de datos | MySQL 8.0+ |
| Build | Vite 7 |
| PDF | barryvdh/laravel-dompdf |
| Editor WYSIWYG | Tiptap v2 + lowlight (syntax highlighting) |
| Auth scaffold | Laravel Breeze |

---

## Módulos

### Clientes (CRM)
CRUD completo. Campos: nombre, empresa, email, teléfono, estado (`activo / potencial / pausado`), stack tecnológico, notas internas (solo admin).

### Tareas + Kanban
Tareas vinculadas a cliente. Tablero Kanban drag-and-drop (vue-draggable-plus). Vista global de todas las tareas de todos los clientes. Comentarios por tarea.

Estados: `Backlog → En progreso → En revisión → Finalizado`  
Prioridades: `Baja · Media · Alta`

### Facturación
Registro manual de cobros en ARS. Tres estados: `Pendiente · Pagado · Vencido`.

### Presupuestos
Constructor con ítems de línea (descripción + precio). Cuatro estados: `Borrador → Enviado → Aceptado / Rechazado`. Generación de PDF con firma.

### Notas (Knowledge Base)
Base de conocimiento interna estilo Notion. Carpetas con jerarquía, editor WYSIWYG con syntax highlighting, búsqueda por título.

### Portal del Cliente
Vista de solo lectura para el cliente autenticado: tareas activas, presupuestos, estado de facturación.

---

## Arquitectura

**Monolito server-driven.** Laravel maneja todas las rutas y datos. Inertia.js conecta con Vue como SPA sin API REST.

```
Flujo de datos:
useForm().post/patch()
  → Laravel Controller
  → FormRequest (validación)
  → Service (lógica de negocio)
  → Repository (acceso a datos)
  → Model (Eloquent)
  → redirect() con flash
```

### Capa Repository/Service

Patrón adoptado a partir del módulo Notes. Las interfaces viven en `app/Contracts/Repositories/`, las implementaciones en `app/Repositories/`, y los servicios en `app/Services/`. Los bindings se registran en `AppServiceProvider`.

```
app/
├── Contracts/
│   └── Repositories/
│       ├── NoteRepositoryInterface.php
│       └── NoteFolderRepositoryInterface.php
├── Repositories/
│   ├── NoteRepository.php
│   └── NoteFolderRepository.php
└── Services/
    ├── NoteService.php
    └── NoteFolderService.php
```

### Roles y acceso

| Rol | Acceso | Middleware |
|---|---|---|
| `admin` | Panel completo `/dashboard` | `['auth', 'admin']` |
| `client` | Portal de solo lectura `/portal` | `['auth', 'client']` |

Los clientes se crean por invitación vía signed URL (`URL::temporarySignedRoute`). No hay registro público.

### Estructura de Pages

```
resources/js/
├── Layouts/
│   ├── AdminLayout.vue
│   └── PortalLayout.vue
└── Pages/
    ├── Admin/
    │   ├── Dashboard.vue
    │   ├── Clients/       (Index, Create, Edit, Show)
    │   ├── Tasks/         (Index — Kanban)
    │   ├── Billing/       (Index, Create, Edit)
    │   ├── Quotes/        (Index, Create, Edit)
    │   ├── Notes/         (Index, Show, Create, Edit)
    │   └── Invitations/
    └── Portal/
```

---

## Instalación local

### Requisitos

- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Laragon (o cualquier entorno equivalente)

### Setup

```bash
# 1. Clonar el repositorio
git clone <repo-url> hub-srojas
cd hub-srojas

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Variables de entorno
cp .env.example .env
php artisan key:generate
```

Configurar en `.env`:
```env
DB_DATABASE=hub_srojas
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. Migrar base de datos
php artisan migrate

# 6. (Opcional) Seeders
php artisan db:seed

# 7. Levantar el servidor
php artisan serve

# 8. En otra terminal — compilar assets
npm run dev
```

Navegar a `http://localhost:8000`.

---

## Estructura de la base de datos

| Tabla | Descripción |
|---|---|
| `users` | Usuarios (admin y clientes). Campo `role` enum + `client_id` FK |
| `clients` | Datos del cliente. FK para tareas, cobros y presupuestos |
| `tasks` | Tareas con `status` y `priority` enum, vinculadas a cliente |
| `task_comments` | Comentarios por tarea |
| `billings` | Registros de cobro por cliente |
| `quotes` | Presupuestos por cliente |
| `quote_items` | Ítems de línea de un presupuesto |
| `invitations` | Tokens de invitación por email (signed URL) |
| `note_folders` | Carpetas de notas. Self-referencing `parent_id` |
| `notes` | Notas WYSIWYG. FK a `note_folders` (nullable) |

Columnas monetarias en `decimal(15,2)` — ARS con centavos.  
Columnas de texto largo (`contenido` de notas) en `LONGTEXT`.

---

## Convenciones de código

### Backend

- **Modelos**: PascalCase en inglés (`Task`, `NoteFolder`, `QuoteItem`)
- **Columnas**: snake_case en español (`titulo`, `esta_fijada`, `fecha_limite`)
- **FK**: `{model_en_singular}_id` en inglés (`client_id`, `folder_id`, `task_id`)
- **Enums**: en `app/Enums/`, cast en el modelo via `casts()`
- **FormRequests**: `Store{Entity}Request` / `Update{Entity}Request`
- **Controllers**: devuelven `Inertia::render()` o `redirect()->route()`
- **Sin API routes**: todo por `routes/web.php`

### Frontend

- Todas las pages usan `defineOptions({ layout: AdminLayout })` o `PortalLayout`
- Forms con `useForm()` de `@inertiajs/vue3`
- Filtros y búsqueda vía `router.get()` con `preserveState: true`
- Componentes UI reutilizables en `Components/UI/`: `Button`, `Card`, `Badge`, `PageHeader`, `StatsCard`

---

## Componentes UI disponibles

### `<Button>`
```vue
<Button variant="primary" size="md">Guardar</Button>
<!-- variants: primary | secondary | danger | ghost -->
<!-- sizes: sm | md | lg -->
```

### `<Card>`
```vue
<Card variant="default" padding="md" :glow="'violet'">...</Card>
<!-- variants: default | glass | elevated -->
<!-- glow: violet | cyan | blue -->
```

### `<Badge>`
```vue
<Badge :status="task.estado" />
<!-- Mapea automáticamente estados a colores -->
```

### `<PageHeader>`
```vue
<PageHeader title="Clientes" subtitle="Gestión de clientes">
    <Button>Nueva acción</Button>  <!-- slot default -->
</PageHeader>
```

---

## Módulo de Notas — detalles técnicos

El módulo Notes introduce el patrón Repository/Service al proyecto. Los contenidos se almacenan como HTML (output de Tiptap), no como Markdown.

**Editor:** Tiptap v2 con `StarterKit` + `CodeBlockLowlight` (syntax highlighting via `lowlight`).  
**Viewer:** `v-html` + `hljs.highlightElement()` post-render + clases `prose` de `@tailwindcss/typography`.  
**Extracto:** generado automáticamente en `NoteService` con `strip_tags()` sobre el contenido HTML.

Rutas disponibles:
```
GET    /notes              → Index (listado + filtros)
GET    /notes/create       → Formulario nueva nota
POST   /notes              → Guardar nota
GET    /notes/{note}       → Detalle / viewer
GET    /notes/{note}/edit  → Editar nota
PUT    /notes/{note}       → Actualizar nota
DELETE /notes/{note}       → Eliminar nota
POST   /note-folders       → Crear carpeta
DELETE /note-folders/{id}  → Eliminar carpeta
```

---

## Variables de entorno relevantes

```env
APP_NAME=Hub
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hub_srojas

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@hub.dev
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Comandos útiles

```bash
# Migraciones
php artisan migrate
php artisan migrate:fresh --seed   # reset completo

# Cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas del módulo Notes
php artisan route:list --path=notes

# Formatear código PHP (Pint)
./vendor/bin/pint

# Build producción
npm run build
```

---

## Decisiones de diseño notables

- **Sin API REST**: Inertia elimina la necesidad. Todo es server-rendered + redirect.
- **Sin Pinia**: el estado global que se necesitaba era mínimo; `usePage().props` cubre los casos de auth/usuario.
- **DomPDF, no Snappy**: Snappy requiere `wkhtmltopdf` (binario externo). DomPDF es pure PHP — funciona igual en Laragon Windows y en producción Linux.
- **`vue-draggable-plus`**: reemplazo activo de `vuedraggable` (vue.draggable.next), compatible con Vue 3.
- **HTML en notas, no Markdown**: Tiptap almacena su output directamente como HTML. No se necesita `league/commonmark` ni un paso extra de rendering.
- **`nullOnDelete` en FKs de notas**: eliminar una carpeta no elimina las notas — quedan en "sin carpeta". Idem `note_folders.parent_id`.
