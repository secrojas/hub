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
| Email | SMTP cPanel — `Mail::send()` con Mailable |

---

## Módulos

### Clientes (CRM)
CRUD completo. Campos: nombre, empresa, email, teléfono, estado (`activo / potencial / pausado`), stack tecnológico, notas internas (solo admin), valor por hora (`valor_hora`).

### Tareas + Kanban
Tareas vinculadas a cliente. Tablero Kanban drag-and-drop (vue-draggable-plus). Vista global de todas las tareas de todos los clientes. Comentarios por tarea.

Estados: `Backlog → En progreso → En revisión → Finalizado`  
Prioridades: `Baja · Media · Alta`  
Horas: decimal con precisión de 15 minutos (`0.25`, `0.5`, `1.75`, etc.). Se muestran como `"1h 30min"` via composable `formatHoras`.

### Facturación
Cobros con ítems de línea. Cada factura tiene un título (concepto) y una lista de ítems que pueden ser:
- **Tarea finalizada** del cliente (monto calculado como `horas × valor_hora`, guardado como snapshot)
- **Ítem manual** (concepto y monto libre)

El `monto` total en `billings` siempre se computa como suma de `billing_items`. Tres estados: `Pendiente · Pagado · Vencido`.

### Presupuestos
Constructor con ítems de línea (descripción + precio). Cuatro estados: `Borrador → Enviado → Aceptado / Rechazado`. Generación de PDF con firma.

### Notas (Knowledge Base)
Base de conocimiento interna estilo Notion. Carpetas con jerarquía, editor WYSIWYG con syntax highlighting, búsqueda por título.

### Portal del Cliente
Vista de solo lectura para el cliente autenticado: tareas activas (con detalle), presupuestos (con PDF), facturación (con detalle de ítems).

### Invitaciones + Email
Los clientes se crean por invitación. El admin genera una signed URL (válida 72h) y el sistema dispara automáticamente un email al cliente con un template dark branded y botón CTA. El link también queda disponible en pantalla como fallback.

---

## Arquitectura

**Monolito server-driven.** Laravel maneja todas las rutas y datos. Inertia.js conecta con Vue como SPA sin API REST.

```
Flujo de datos:
useForm().post/patch()
  → Laravel Controller
  → FormRequest (validación)
  → Service (lógica de negocio)         ← módulo Notes
  → Repository (acceso a datos)         ← módulo Notes
  → Model (Eloquent)
  → redirect() con flash
```

### Capa Repository/Service

Patrón introducido en el módulo Notes. Interfaces en `app/Contracts/Repositories/`, implementaciones en `app/Repositories/`, servicios en `app/Services/`. Bindings en `AppServiceProvider::register()`.

```
app/
├── Contracts/Repositories/
│   ├── NoteRepositoryInterface.php
│   └── NoteFolderRepositoryInterface.php
├── Repositories/
│   ├── NoteRepository.php
│   └── NoteFolderRepository.php
└── Services/
    ├── NoteService.php
    └── NoteFolderService.php
```

> Los módulos anteriores (Tasks, Billing, Quotes, Clients) usan Eloquent directo en controllers — consistencia con código existente.

### Roles y acceso

| Rol | Acceso | Middleware |
|---|---|---|
| `admin` | Panel completo `/dashboard` | `['auth', 'admin']` |
| `client` | Portal de solo lectura `/portal` | `['auth', 'client']` |

### Estructura de Pages

```
resources/js/
├── Layouts/
│   ├── AdminLayout.vue
│   ├── PortalLayout.vue
│   └── GuestLayout.vue          ← invitaciones + login
└── Pages/
    ├── Admin/
    │   ├── Dashboard.vue
    │   ├── Clients/              (Index, Create, Edit, Show)
    │   ├── Tasks/                (Index — Kanban)
    │   ├── Billing/              (Index, Create, Edit)
    │   ├── Quotes/               (Index, Create, Edit)
    │   ├── Notes/                (Index, Show, Create, Edit)
    │   └── Invitations/
    ├── Portal/
    │   ├── Index.vue
    │   ├── Tasks/Show.vue        ← detalle de tarea
    │   └── Billing/Show.vue      ← comprobante con ítems
    └── Invitation/
        └── Accept.vue            ← dark theme con GuestLayout
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
| `users` | Usuarios. Campo `role` enum (`admin`/`client`) + `client_id` FK |
| `clients` | CRM. FK para tareas, cobros y presupuestos. Incluye `valor_hora decimal(10,2)` |
| `tasks` | Tareas. `estado` y `prioridad` enum. `horas decimal(5,2)` (mín 0.25) |
| `task_comments` | Comentarios por tarea |
| `billings` | Cabecera de cobro. `monto` = suma de `billing_items` (snapshot) |
| `billing_items` | Ítems de un cobro. `task_id` nullable (tarea vinculada o ítem libre) |
| `quotes` | Presupuestos por cliente |
| `quote_items` | Ítems de línea de un presupuesto |
| `invitations` | Tokens de invitación por email (signed URL, expira 72h) |
| `note_folders` | Carpetas de notas. `parent_id` self-referencing, `nullOnDelete` |
| `notes` | Notas WYSIWYG HTML. `folder_id` nullable, `esta_fijada` boolean |

Columnas monetarias en `decimal(12,2)` o `decimal(15,2)` — ARS con centavos.  
`tasks.horas` en `decimal(5,2) unsigned` — mínimo 0.25 (15 min).

---

## Convenciones de código

### Backend

- **Modelos**: PascalCase inglés (`Task`, `NoteFolder`, `BillingItem`)
- **Columnas**: snake_case español (`titulo`, `esta_fijada`, `fecha_limite`, `valor_hora`)
- **FK**: `{singular_model}_id` inglés (`client_id`, `folder_id`, `billing_id`)
- **Enums**: en `app/Enums/`, cast en modelo via `casts()`
- **FormRequests**: `Store{Entity}Request` / `Update{Entity}Request`
- **Controllers**: devuelven `Inertia::render()` o `redirect()->route()`
- **Mailables**: en `app/Mail/`, templates en `resources/views/emails/`
- **Sin API routes**: todo por `routes/web.php`

### Frontend

- Pages usan `defineOptions({ layout: AdminLayout })` o `PortalLayout` o `GuestLayout`
- Forms con `useForm()` de `@inertiajs/vue3`
- Filtros y búsqueda vía `router.get()` con `preserveState: true`
- Composables en `resources/js/composables/`
- Componentes UI reutilizables en `Components/UI/`

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
<Badge :variant="task.estado" />
<!-- Mapea automáticamente estados a colores -->
```

### `<PageHeader>`
```vue
<PageHeader title="Clientes" subtitle="...">
    <Button>Acción</Button>   <!-- slot default -->
</PageHeader>
```

---

## Composables

### `formatHoras(h)` — `resources/js/composables/useFormatHoras.js`

Convierte horas decimales a formato legible:

```js
import { formatHoras } from '@/composables/useFormatHoras.js'

formatHoras(0.25)  // → "15min"
formatHoras(0.5)   // → "30min"
formatHoras(1)     // → "1h"
formatHoras(1.75)  // → "1h 45min"
formatHoras(null)  // → "—"
```

Usado en: Tasks/Index (Kanban cards), Portal/Tasks/Show, Portal/Index (tabla horas), Clients/Show, Billing/Create y Edit (selector de tareas).

---

## Módulo de Notas — detalles técnicos

Contenido almacenado como HTML (output de Tiptap), no como Markdown.

**Editor:** Tiptap v2 con `StarterKit` (codeBlock deshabilitado) + `CodeBlockLowlight` via `lowlight`.  
**Viewer:** `v-html` + `hljs.highlightElement()` post-render + clases `prose` de `@tailwindcss/typography`.  
**Extracto:** generado en `NoteService::generateExcerpt()` con `strip_tags()` + `Str::limit(250)`.

---

## Módulo de Facturación — detalles técnicos

El `billing.monto` es siempre un total computado — nunca se ingresa manualmente.

```
billing (cabecera)
  └── billing_items[]
        ├── task_id = null  → ítem libre (concepto + monto manual)
        └── task_id = X     → tarea vinculada (monto snapshot: horas × valor_hora al momento de facturar)
```

**Migración de datos existentes:** la migration `2026_04_08_000004` crea automáticamente un `billing_item` por cada registro de `billings` existente, preservando el concepto y monto original.

---

## Email — Invitaciones

**Mailable:** `App\Mail\InvitacionCliente` (props: `clientName`, `invitationUrl`)  
**Template:** `resources/views/emails/invitacion.blade.php` — diseño dark con inline styles (compatible Gmail, Outlook, Apple Mail)  
**Disparado en:** `InvitationController::store()` — simultáneo a la generación del signed URL

**Configuración SMTP (cPanel, puerto 465 SSL):**
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.srojas.app
MAIL_PORT=465
MAIL_USERNAME=delivery@srojas.app
MAIL_PASSWORD=<ver credenciales>
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="delivery@srojas.app"
MAIL_FROM_NAME="Hub"
```

> Para desarrollo local sin enviar emails reales: `MAIL_MAILER=log`

---

## Deploy (GitHub Actions)

El workflow `.github/workflows/deploy.yml` corre en cada push a `master`:

1. **Build PHP** — `composer install --no-dev`
2. **Build assets** — `npm ci && npm run build`
3. **Setup SSH** — escribe `~/.ssh/deploy_key` + config con `StrictHostKeyChecking no`
4. **Pull & deploy** — `git pull` + `composer install` + `migrate --force` + `optimize` en el servidor
5. **Sync assets** — sube `public/build/` via `tar` por SSH

**Secrets requeridos en GitHub:**

| Secret | Descripción |
|---|---|
| `SSH_PRIVATE_KEY` | Clave privada SSH para el servidor |
| `SSH_HOST` | Hostname o IP del servidor |
| `SSH_PORT` | Puerto SSH (default 22) |
| `SSH_USER` | Usuario SSH |

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
MAIL_HOST=mail.srojas.app
MAIL_PORT=465
MAIL_USERNAME=delivery@srojas.app
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="delivery@srojas.app"
MAIL_FROM_NAME="Hub"
```

---

## Comandos útiles

```bash
# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Ver rutas por módulo
php artisan route:list --path=notes
php artisan route:list --path=billing
php artisan route:list --path=portal

# Lint PHP
php artisan route:list  # fuerza autoload — revela errores de sintaxis
./vendor/bin/pint       # formatea con Laravel Pint

# Build producción
npm run build
```

---

## Decisiones de diseño notables

- **Sin API REST**: Inertia elimina la necesidad. Todo es server-rendered + redirect.
- **Sin Pinia**: estado global mínimo; `usePage().props` cubre los casos de auth.
- **DomPDF, no Snappy**: Snappy requiere `wkhtmltopdf` (binario externo). DomPDF es pure PHP.
- **`vue-draggable-plus`**: reemplazo activo de `vuedraggable` (vue.draggable.next), compatible con Vue 3.
- **HTML en notas, no Markdown**: Tiptap almacena HTML directamente. Sin paso extra de rendering.
- **`nullOnDelete` en FKs de notas**: carpeta eliminada → notas quedan en "sin carpeta". Idem `parent_id`.
- **Billing items snapshot**: el monto de una tarea en una factura se guarda en el momento de facturar. Cambios posteriores en las horas no afectan la factura.
- **`step="0.25"` en input horas**: el browser nativo muestra flechas de ±15min. Sin `step`, asume `step=1` y bloquea decimales.
- **Port 465 = `MAIL_ENCRYPTION=ssl`**: no STARTTLS (587). Si se usa 465 con `tls`, la conexión falla silenciosamente.
- **SSH config en CI**: `StrictHostKeyChecking no` en `~/.ssh/config` en lugar de `ssh-keyscan` en runtime — evita fallos por conectividad durante el build.
