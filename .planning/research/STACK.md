# Stack Research: Hub — Plataforma de gestión de clientes freelance

**Domain:** Freelance client management platform
**Stack constraint:** Laravel 11 + Inertia.js + Vue 3 + MySQL (non-negotiable)
**Generated:** 2026-03-19

---

## Core Stack (Project-Defined)

| Layer | Technology | Version | Notes |
|-------|-----------|---------|-------|
| Backend | Laravel | 11.x | LTS, current stable |
| Frontend bridge | Inertia.js | v2.x | Monolithic SPA without REST API |
| Frontend | Vue 3 | 3.x (Composition API) | With `<script setup>` |
| Database | MySQL | 8.0+ | Decimal support for ARS amounts |
| Build tool | Vite | 5.x | Installed by default with Laravel 11 |

---

## Recommended Packages

### Authentication & Authorization
- **Laravel Breeze** (Vue + Inertia option) — minimal auth scaffold compatible with this exact stack
  - Rationale: Breeze installs Inertia + Vue 3 + Tailwind automatically. Jetstream is too heavy.
  - NOT Spatie Laravel-Permission — two roles (admin/client) don't warrant the overhead
  - Confidence: HIGH

### PDF Generation
- **`barryvdh/laravel-dompdf` ^3.0** — pure PHP, no system binary required
  - Rationale: Critical for Laragon (Windows dev) + Linux production parity. Snappy and Browsershot both require `wkhtmltopdf` binary — painful to maintain across environments.
  - Inlined styles only (no external CSS URLs)
  - Confidence: HIGH

### Email
- **Laravel built-in Mail** + **`URL::temporarySignedRoute()`** for invitations
  - Dev: Mailtrap / Laravel's log driver
  - Production: Resend (simple HTTP API, no SMTP config)
  - Confidence: HIGH

### Kanban Drag-and-Drop
- **`vuedraggable` (vue.draggable.next)** — wraps SortableJS, handles cross-column drag natively
  - Usage: `<draggable v-model="tasks" group="tasks">`
  - Confidence: MEDIUM — historically had maintenance gaps, verify latest release before using

### UI & Styling
- **Tailwind CSS v3** — installed by Breeze
  - NOT Tailwind v4 (was in alpha/beta as of August 2025, breaking changes)
  - **Headless UI Vue** for accessible dropdowns, modals, etc.
  - Confidence: HIGH

### State Management & Forms
- **Pinia** — global state (e.g., shared auth user, notifications)
- **Inertia's `useForm()`** — all form handling (progress tracking, error handling built-in)
  - NOT VeeValidate — unnecessary complexity with Inertia's built-in form handling
  - Confidence: HIGH

### Dev Tools
- **Laravel Telescope** — request/query debugging
- **Laravel Pail** — real-time log tailing
- **Pest** — testing (Laravel 11 default)

---

## What NOT to Use

| Package | Reason |
|---------|--------|
| Jetstream | Too heavy, adds Livewire/teams complexity not needed |
| Sanctum (standalone) | Not needed — Inertia uses session auth, not token auth |
| Vuex | Replaced by Pinia in Vue 3 ecosystem |
| Snappy / Browsershot | Require system binary — breaks on Windows dev / Linux prod parity |
| Tailwind v4 | Too new, breaking changes, not stable as of mid-2025 |
| vue-kanban | Mostly unmaintained Vue 2 libraries |
| Spatie Laravel-Permission | Overkill for 2-role system |

---

## Key Architectural Decision

**No REST API.** Laravel owns routing + data layer; Inertia bridges to Vue. All mutations go through `Inertia.post/patch()` → Laravel controller → redirect. No client-side fetching. This keeps the codebase unified and avoids auth token complexity.
