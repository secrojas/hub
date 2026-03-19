# Architecture Research: Hub — Plataforma de gestión de clientes freelance

**Domain:** Freelance client management platform (Laravel 11 + Inertia.js + Vue 3)
**Generated:** 2026-03-19

---

## Recommended Architecture

**Monolithic Laravel 11 app with Inertia.js as the rendering bridge.**

No REST API. No separate SPA. Laravel owns routing, business logic, and data. Vue 3 owns rendering. Inertia connects them via server-driven page components.

---

## Component Boundaries

### Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── ClientController.php
│   │   │   ├── TaskController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── QuoteController.php
│   │   │   └── DashboardController.php
│   │   └── Portal/
│   │       └── PortalController.php   ← client-facing only
│   ├── Middleware/
│   │   ├── EnsureIsAdmin.php
│   │   └── EnsureIsClient.php
│   └── Requests/                      ← Form Requests per resource
├── Models/
│   ├── User.php                       ← role: admin | client
│   ├── Client.php                     ← has many tasks, invoices, quotes
│   ├── Task.php
│   ├── Invoice.php
│   └── Quote.php / QuoteItem.php
├── Services/
│   └── PdfService.php                 ← DomPDF wrapper
└── Policies/
    ├── ClientPolicy.php
    ├── TaskPolicy.php
    ├── InvoicePolicy.php
    └── QuotePolicy.php
```

### Frontend (Vue 3 + Inertia)

```
resources/js/
├── Layouts/
│   ├── AdminLayout.vue               ← sidebar nav, admin shell
│   └── PortalLayout.vue              ← minimal client shell
├── Pages/
│   ├── Admin/
│   │   ├── Dashboard.vue
│   │   ├── Clients/
│   │   │   ├── Index.vue
│   │   │   ├── Show.vue
│   │   │   └── Form.vue
│   │   ├── Tasks/
│   │   │   ├── Kanban.vue            ← per-client board
│   │   │   └── GlobalKanban.vue      ← all clients view
│   │   ├── Invoices/
│   │   │   ├── Index.vue
│   │   │   └── Form.vue
│   │   └── Quotes/
│   │       ├── Index.vue
│   │       └── Builder.vue
│   └── Portal/
│       ├── Dashboard.vue             ← client home
│       ├── Tasks.vue                 ← read-only list
│       ├── Quotes.vue
│       └── Invoices.vue
└── Components/
    ├── Admin/
    │   ├── KanbanColumn.vue
    │   ├── KanbanCard.vue
    │   └── InvoiceSummary.vue
    └── Portal/
        └── TaskListItem.vue
```

---

## Data Flow

### Reads (all server-driven)
```
URL request
  → Laravel Router
  → Controller (loads data with eager loading)
  → Inertia::render('Page/Component', $props)
  → Vue renders with typed props
```

### Mutations (Inertia forms)
```
User action
  → Vue form submit via useForm().post/patch()
  → Laravel Controller
  → Form Request validation
  → Service layer (business logic)
  → Model save
  → redirect()->back() with flash
  → Inertia intercepts redirect → re-renders page with new data
```

### PDF Generation
```
Admin clicks "Marcar como Enviado"
  → QuoteController::send()
  → PdfService::generateQuotePdf($quote)
  → DomPDF renders Blade template
  → Stored to storage/app/quotes/{id}.pdf
  → Served via signed URL or direct download response
```

### Client Invitation
```
Admin creates client → fills email
  → InvitationController::send()
  → URL::temporarySignedRoute('portal.accept', now()->addDays(7), ['token' => $token])
  → Mail::to($email)->send(new ClientInvitation($url))
  → Client clicks link → validates signature → sets password → logs in
```

---

## Suggested Build Order

| Step | Component | Why First |
|------|-----------|-----------|
| 1 | Foundation (Laravel + Inertia wiring + layouts) | Everything depends on this |
| 2 | Auth + roles + invitation flow | Gate to all other features |
| 3 | Clients module (CRUD) | Foreign key for all other models |
| 4 | Tasks + Kanban (per-client → global) | Core value of the product |
| 5 | Invoices + billing dashboard | Revenue visibility |
| 6 | Quotes + PDF generation | Budget workflow |
| 7 | Client portal (scoped read-only views) | Depends on tasks, quotes, invoices |
| 8 | Admin dashboard (aggregated view) | Most valuable with real data |

---

## Anti-Patterns to Avoid

1. **Fat controllers** — business logic belongs in Services, not controllers
2. **N+1 on global Kanban** — always eager load `with(['client', 'assignee'])` + add DB indexes on `status`, `client_id`
3. **Portal without scoping guard** — every portal query must be scoped to the authenticated client's `client_id`
4. **Vue Router alongside Inertia** — Inertia IS the router; adding Vue Router creates conflicts
5. **PDFs stored in database** — store on filesystem, serve via URL; never blob in DB

---

## Key Decisions

- **No REST API** — Inertia's server-driven model eliminates the need; simpler auth, fewer endpoints
- **Two layout shells** — `AdminLayout` and `PortalLayout` prevent accidental component sharing between roles
- **Policies on every resource** — never trust route parameters alone for ownership validation
- **Inertia shared props are role-scoped** — `HandleInertiaRequests::share()` conditionally shares based on role

---

*Confidence: MEDIUM — patterns are well-established for this stack. Verify Inertia.js v2 `share()` API against current docs before implementation.*
