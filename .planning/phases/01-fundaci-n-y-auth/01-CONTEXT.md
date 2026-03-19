# Phase 1: Fundación y Auth - Context

**Gathered:** 2026-03-19
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase entrega un sistema de autenticación funcional con dos roles (admin / client), flujo completo de invitación por email, y el scaffolding base de Laravel 11 + Breeze + Inertia + Vue 3. Es la base segura sobre la que se construyen todos los módulos siguientes. No incluye ningún módulo de negocio (CRM, tareas, facturación).

</domain>

<decisions>
## Implementation Decisions

### Instalación y Scaffolding
- `composer create-project laravel/laravel .` en el directorio existente del repo
- Breeze con preset Vue + Inertia **CSR** (sin SSR) — sin complejidad de servidor Node
- Base de datos de desarrollo: `hub`
- Sin TypeScript — JavaScript puro con Vue 3 Composition API

### Bootstrap del Admin
- Primer usuario admin creado via `DatabaseSeeder` (ejecutado con `db:seed`)
- Credenciales del admin en `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`) — no hardcodeadas
- Campo `role` como enum en tabla `users` con valores `admin` / `client`
- Sin UI para cambiar password del admin en v1 — se cambia via tinker/DB directamente

### Flujo de Invitación
- Links de invitación expiran en **72 horas**
- Link expirado muestra página de error clara con mensaje + indicación de contactar al admin
- Form de registro del cliente tiene nombre e email pre-llenados desde la invitación (solo define password)
- Tabla `invitations` en DB con campos: `token`, `email`, `client_name`, `expires_at`, `used_at`

### Routing y Middleware
- Admin redirige a `/dashboard` tras login
- Cliente redirige a `/portal` tras login
- Middlewares separados: `EnsureIsAdmin` y `EnsureIsClient`, registrados en `bootstrap/app.php`
- Página 403 custom como componente Inertia/Vue con mensaje "No tenés acceso" + link para volver

### Claude's Discretion
- Estructura interna de carpetas Vue (componentes, páginas, layouts) — a criterio de Claude siguiendo convenciones Breeze
- Nombres de rutas y grupos de rutas — a criterio de Claude
- Validación del form de registro (rules específicas) — a criterio de Claude

</decisions>

<code_context>
## Existing Code Insights

### Reusable Assets
- Ninguno — proyecto completamente nuevo, directorio vacío al iniciar la fase

### Established Patterns
- Stack definido: Laravel 11 + Inertia.js v2 + Vue 3 + MySQL
- Breeze como punto de partida (login, register pages ya incluidos)
- `URL::temporarySignedRoute()` para generar links de invitación (decisión PROJECT.md)

### Integration Points
- Tabla `users` es la base para todos los módulos siguientes — el campo `role` es crítico
- Tabla `invitations` es usada solo en esta fase (el admin enviará invitaciones desde el CRM en fases futuras)
- Las rutas `/dashboard` y `/portal` serán los puntos de entrada de fases futuras

</code_context>

<specifics>
## Specific Ideas

- El seeder debe ejecutarse limpio: si el admin ya existe (mismo email), no duplicar
- La página de error de invitación expirada debe ser una vista Inertia (no una página Blade genérica) para mantener consistencia visual
- Verificar la firma del método `share()` de Inertia v2 antes de implementar (nota en STATE.md: puede haber cambios respecto a v1)

</specifics>

<deferred>
## Deferred Ideas

- Envío de email real al invitar cliente — en v1 el admin copia el link manualmente (notificaciones automáticas out of scope)
- Página de perfil del admin para cambiar email/password — v2
- "Reenviar invitación" desde la UI del admin — se agrega cuando el CRM esté disponible en Phase 2

</deferred>
