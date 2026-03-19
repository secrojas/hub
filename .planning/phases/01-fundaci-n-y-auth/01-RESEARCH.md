# Phase 1: Fundación y Auth - Research

**Researched:** 2026-03-19
**Domain:** Laravel 11 + Breeze + Inertia.js v2 + Vue 3 — Authentication, Role Middleware, Invitation System
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- `composer create-project laravel/laravel .` in the existing repo directory
- Breeze with Vue + Inertia **CSR** (no SSR) — no Node server complexity
- Development database: `hub`
- No TypeScript — plain JavaScript with Vue 3 Composition API
- First admin user created via `DatabaseSeeder` (run with `db:seed`)
- Admin credentials in `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`) — not hardcoded
- `role` field as enum in `users` table with values `admin` / `client`
- No admin password-change UI in v1 — changed via tinker/DB directly
- Invitation links expire in **72 hours**
- Expired link shows clear error page with message + contact-admin indication
- Client registration form pre-fills name and email from invitation (client only defines password)
- `invitations` table fields: `token`, `email`, `client_name`, `expires_at`, `used_at`
- Admin redirects to `/dashboard` after login
- Client redirects to `/portal` after login
- Separate middlewares: `EnsureIsAdmin` and `EnsureIsClient`, registered in `bootstrap/app.php`
- Custom 403 page as Inertia/Vue component with "No tenés acceso" message + back link
- `URL::temporarySignedRoute()` exclusively for invitation links

### Claude's Discretion
- Internal folder structure for Vue (components, pages, layouts) — follow Breeze conventions
- Route names and route groups
- Validation rules for the registration form

### Deferred Ideas (OUT OF SCOPE)
- Real email sending for invitations — v1: admin copies link manually
- Admin profile page for email/password change — v2
- "Resend invitation" from admin UI — added when CRM is available in Phase 2
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| AUTH-01 | El admin puede iniciar sesión con email y contraseña | Breeze ships login; `EnsureIsAdmin` middleware guards admin routes |
| AUTH-02 | El admin puede invitar a un cliente mediante un link firmado temporario | `URL::temporarySignedRoute()` + `invitations` table; link generated in controller, admin copies it manually |
| AUTH-03 | El cliente puede aceptar la invitación, definir su contraseña y acceder al portal | `signed` middleware on invitation route; invitation controller pre-fills name/email; `EnsureIsClient` guards portal |
| AUTH-04 | La sesión persiste al recargar el navegador | Laravel session (file/cookie driver) — default Breeze behavior; no extra config |
| AUTH-05 | El usuario puede hacer logout desde cualquier página | Breeze ships `AuthenticatedSessionController@destroy`; Inertia `router.post` to `/logout` from any layout |
</phase_requirements>

---

## Summary

Phase 1 builds the entire foundation: Laravel 11 + Breeze (Vue + Inertia CSR) scaffolded from scratch, custom role enum on the `users` table, a separate `invitations` table driven by `URL::temporarySignedRoute()`, two role-specific middleware aliases registered in `bootstrap/app.php`, and two distinct Vue layout trees (admin vs. client portal) assigned per page via Inertia's persistent layout pattern.

The key complexity areas are: (1) Inertia.js v2's `HandleInertiaRequests` middleware registration — there is a known bug in some versions of Breeze where it is not auto-appended to `bootstrap/app.php`, requiring manual verification post-install; (2) the `share()` method signature changed between Inertia v1 and v2 (return type is now `array`, no longer nullable, and `shareOnce()` is available as a companion); (3) `InvalidSignatureException` must be rendered as an Inertia response in `bootstrap/app.php` rather than a Blade view to maintain visual consistency.

**Primary recommendation:** Scaffold in order — Laravel install → Breeze install → verify `HandleInertiaRequests` in `bootstrap/app.php` → add `role` enum migration → build invitation table and controller → add middleware pair → wire up layouts. Any deviation in this order risks environment issues on Windows/Laragon.

---

## Standard Stack

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| laravel/laravel | 11.x | PHP framework, routing, ORM, sessions | Locked by project |
| laravel/breeze | latest (^2.x) | Auth scaffold (login, register, password reset pages + routes) | Locked — Vue+Inertia preset |
| @inertiajs/vue3 | 2.3.18 (latest stable) | Inertia client adapter for Vue 3 | v3 is beta — use v2 |
| inertiajs/inertia-laravel | ^2.0 | Server-side Inertia adapter + HandleInertiaRequests | Auto-installed by Breeze |
| vue | ^3.4 | UI framework | Locked by project |
| vite | ^8.0 | Build tool | Bundled with Breeze |
| laravel-vite-plugin | ^3.0 | Vite integration for Laravel | Bundled with Breeze |
| tailwindcss | ^3.x | CSS framework | Bundled with Breeze (optional to keep) |

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @vitejs/plugin-vue | ^5.x | Vue SFC support in Vite | Auto-installed by Breeze |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| URL::temporarySignedRoute() | custom token + DB | Locked — no DB roundtrip needed for signature validation, expiry built-in |
| Laravel Breeze | Custom auth | Locked — Breeze ships tested auth boilerplate |
| Inertia v2 | Inertia v3 beta | v3 beta as of March 2026 — not production-ready |

**Installation (after `composer create-project`):**
```bash
composer require laravel/breeze --dev
php artisan breeze:install vue --no-interaction
npm install
npm run dev
php artisan migrate
```

The `--no-interaction` flag installs Vue+Inertia CSR without prompting for SSR or TypeScript. Do NOT add `--ssr` or `--typescript`.

**Version verification (run before task execution):**
```bash
npm view @inertiajs/vue3 version
composer show inertiajs/inertia-laravel
```

---

## Architecture Patterns

### Recommended Project Structure

```
app/
├── Enums/
│   └── Role.php                 # PHP 8.1 backed enum: admin / client
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                # Breeze ships these — extend, don't replace
│   │   └── InvitationController.php  # generate link + accept invitation
│   └── Middleware/
│       ├── EnsureIsAdmin.php
│       └── EnsureIsClient.php
├── Models/
│   └── User.php                 # add role cast + redirect helper
database/
├── migrations/
│   ├── ..._add_role_to_users_table.php
│   └── ..._create_invitations_table.php
├── seeders/
│   └── DatabaseSeeder.php       # creates admin from .env vars
resources/js/
├── Layouts/
│   ├── AdminLayout.vue          # top nav, sidebar for admin area
│   └── PortalLayout.vue         # minimal layout for client portal
├── Pages/
│   ├── Auth/                    # Breeze ships these (Login, Register, etc.)
│   ├── Admin/                   # admin pages — use AdminLayout
│   │   └── Dashboard.vue
│   ├── Portal/                  # client pages — use PortalLayout
│   │   └── Index.vue
│   ├── Invitation/
│   │   └── Accept.vue           # client sets password here
│   └── Error.vue                # generic Inertia error page (403, 404, etc.)
routes/
├── web.php
└── auth.php                     # Breeze ships this
```

### Pattern 1: Breeze Install + Middleware Registration Verification

**What:** After `php artisan breeze:install vue`, verify `HandleInertiaRequests` is appended to the web group in `bootstrap/app.php`. There is a known bug (fixed in recent versions but worth verifying) where it may not be auto-added.

**When to use:** Immediately after Breeze install, before any other work.

**Expected state in `bootstrap/app.php`:**
```php
// Source: https://inertiajs.com/server-side-setup + issue #56053
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);
})
```

If these lines are missing after `breeze:install`, add them manually. The file is `app/Http/Middleware/HandleInertiaRequests.php` — published by Breeze automatically.

### Pattern 2: Role Enum (PHP 8.1 backed string enum)

**What:** Native PHP enum used as migration column type and Eloquent cast.

**Example:**
```php
// Source: https://laravel.com/docs/11.x/eloquent-mutators (casts section)
// app/Enums/Role.php
namespace App\Enums;

enum Role: string
{
    case Admin  = 'admin';
    case Client = 'client';
}
```

```php
// Migration
$table->enum('role', array_column(Role::cases(), 'value'))->default(Role::Client->value);

// User model
protected $casts = [
    'role' => Role::class,
    'email_verified_at' => 'datetime',
];
```

### Pattern 3: Inertia v2 `share()` in HandleInertiaRequests

**What:** The `share()` method returns an `array` (explicit return type in v2). Lazy-evaluated closures prevent unnecessary DB queries.

**Verified signature:**
```php
// Source: https://inertiajs.com/docs/v2/data-props/shared-data
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => fn () => $request->user()
                ? $request->user()->only('id', 'name', 'email', 'role')
                : null,
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
        ],
    ]);
}
```

**Accessing in Vue 3:**
```vue
<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
</script>
```

### Pattern 4: Middleware Aliases in bootstrap/app.php

**What:** Laravel 11 removed Kernel.php. Custom middleware aliases go in `bootstrap/app.php` using `$middleware->alias()`.

**Example:**
```php
// Source: https://laravel.com/docs/11.x/middleware
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
        'admin'  => \App\Http\Middleware\EnsureIsAdmin::class,
        'client' => \App\Http\Middleware\EnsureIsClient::class,
    ]);
})
```

**Route usage:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
});
```

### Pattern 5: URL::temporarySignedRoute() — Invitation System

**What:** Laravel signs the URL with HMAC and embeds an expiry timestamp. No DB lookup needed to validate authenticity — the `signed` middleware handles it automatically and throws `InvalidSignatureException` on failure.

**Generate (in InvitationController):**
```php
// Source: https://laravel.com/docs/11.x/urls
use Illuminate\Support\Facades\URL;

$url = URL::temporarySignedRoute(
    'invitation.accept',           // route name
    now()->addHours(72),           // expiry
    [
        'email'       => $invitation->email,
        'client_name' => $invitation->client_name,
        'token'       => $invitation->token,   // links URL to invitations row
    ]
);
// Admin copies this URL — no email sent in v1
```

**Route definition:**
```php
Route::get('/invitation/accept', [InvitationController::class, 'show'])
    ->name('invitation.accept')
    ->middleware('signed');

Route::post('/invitation/accept', [InvitationController::class, 'store'])
    ->name('invitation.accept.store')
    ->middleware('signed');
```

**Controller — show (GET):**
```php
public function show(Request $request): \Inertia\Response
{
    $invitation = Invitation::where('token', $request->token)
        ->whereNull('used_at')
        ->firstOrFail();  // 404 if already used

    return Inertia::render('Invitation/Accept', [
        'email'       => $invitation->email,
        'client_name' => $invitation->client_name,
        'token'       => $invitation->token,
    ]);
}
```

**CRITICAL:** The `signed` middleware validates expiry automatically. There is no need to call `$request->hasValidSignature()` manually when the middleware is on the route. Expired or tampered URLs are rejected with an `InvalidSignatureException` (HTTP 403).

**Mark invitation as used after password set:**
```php
$invitation->update(['used_at' => now()]);
```

### Pattern 6: InvalidSignatureException — Inertia error response

**What:** By default, expired signed URLs return a Blade 403 page. Override in `bootstrap/app.php` to return an Inertia component.

```php
// Source: https://inertiajs.com/docs/v2/advanced/error-handling
// bootstrap/app.php
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Inertia\Inertia;

->withExceptions(function (Exceptions $exceptions) {
    // Render Inertia error page for specific HTTP status codes
    $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
        if (in_array($response->getStatusCode(), [403, 404, 500, 503])) {
            return Inertia::render('Error', ['status' => $response->getStatusCode()])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        }
        // Handle expired CSRF (419)
        if ($response->getStatusCode() === 419) {
            return back()->with(['message' => 'La página expiró, intentá de nuevo.']);
        }
        return $response;
    });
})
```

The `Error.vue` component receives `status` prop and renders the appropriate message. For 403 from an expired invitation link, the message should indicate "El link de invitación expiró o ya fue utilizado. Contactá al administrador."

### Pattern 7: Persistent Layouts per Role (Vue 3 + script setup)

**What:** Inertia persistent layouts prevent re-mounting on navigation. With `<script setup>`, use `defineOptions()` (Vue 3.3+) to assign the layout.

**Option A — `defineOptions` in `<script setup>` (Vue 3.3+, recommended):**
```vue
<!-- Pages/Admin/Dashboard.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ /* ... */ })
</script>
```

**Option B — dual `<script>` blocks (wider compatibility, what Breeze currently uses):**
```vue
<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default { layout: AdminLayout }
</script>

<script setup>
const props = defineProps({ /* ... */ })
</script>
```

**app.js resolve — no default layout forced globally; each page declares its own:**
```js
// resources/js/app.js (Breeze default structure)
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'

createInertiaApp({
    title: (title) => `${title} - Hub`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
```

Note: Breeze uses `resolvePageComponent` (from `laravel-vite-plugin/inertia-helpers`) rather than raw `import.meta.glob`. This is the canonical Breeze pattern — do not replace it.

### Pattern 8: Post-login redirect by role

**What:** Override `redirectTo()` in `AuthenticatedSessionController` (or the `AuthenticatesUsers` concern) to redirect based on role.

```php
// Simplest approach — override in LoginController or middleware
// After login:
return redirect()->intended(
    $user->role === Role::Admin ? route('dashboard') : route('portal')
);
```

In Breeze's `AuthenticatedSessionController@store`, replace the hardcoded `redirect()->intended(RouteServiceProvider::HOME)` (or the `config('app.home')` string in newer Breeze) with the role-based redirect above.

### Anti-Patterns to Avoid

- **Checking role in Blade/Vue and hiding UI only:** Always enforce at the route/middleware level. UI-only checks are not security.
- **Storing the full signed URL in the `invitations` table:** The URL is ephemeral. Store only the token + email + expiry. The signed URL is reconstructed from those when needed (for resend in Phase 2).
- **Using `hasValidSignature()` in the controller when `signed` middleware is already on the route:** Double validation — the middleware already aborts with 403 before the controller runs.
- **Calling `url()->full()` in the POST form action on the invitation accept form:** The signed URL parameters must be preserved. Pass them as hidden inputs or use `url()->current()` with query params intact.
- **Mixing `admin` and `client` pages under the same layout tree:** Keep `Pages/Admin/` and `Pages/Portal/` strictly separated from day one; future phases depend on this separation.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| HMAC-signed expiring invitation URLs | Custom token hashing + DB expiry checker | `URL::temporarySignedRoute()` + `signed` middleware | Handles HMAC, expiry, tamper detection, and 403 response automatically |
| Authentication (login/logout/session) | Custom auth controllers | Laravel Breeze | Tested, battle-hardened, ships with Vue+Inertia pages |
| Password hashing | md5/sha1 | Laravel's `Hash::make()` (bcrypt by default) | Built-in, rounds configurable, auto-upgrades |
| CSRF protection | Manual token comparison | Laravel's CSRF middleware (already in web group) | Inertia automatically includes X-XSRF-TOKEN on every request |
| Middleware pipeline | Manual role checks in every controller | Middleware aliases + route groups | Single point of enforcement, no forget risk |

**Key insight:** The `signed` middleware is the security boundary for the invitation flow. Any custom re-implementation introduces timing attack risk or replay vulnerability.

---

## Common Pitfalls

### Pitfall 1: HandleInertiaRequests Not Auto-Registered
**What goes wrong:** After `breeze:install`, Inertia responses return raw JSON or fail with "All Inertia requests must receive a valid Inertia response" error.
**Why it happens:** Known bug (GitHub issue #56053) — Breeze install may not append `HandleInertiaRequests` to the web middleware group in `bootstrap/app.php`.
**How to avoid:** After every `breeze:install`, immediately open `bootstrap/app.php` and verify `HandleInertiaRequests::class` is in `$middleware->web(append: [...])`.
**Warning signs:** Any page load returns raw JSON data instead of the Inertia SPA shell.

### Pitfall 2: APP_URL Mismatch Breaking Signed URL Validation
**What goes wrong:** Signed URL validates correctly on generation but throws `InvalidSignatureException` when visited, even before expiry.
**Why it happens:** `URL::temporarySignedRoute()` embeds the APP_URL in the signature. If `APP_URL` in `.env` is `http://localhost` but Laragon serves the app at `http://hub-srojas.test`, the URL domain in the signature differs from the actual request domain, invalidating the signature.
**How to avoid:** Set `APP_URL=http://hub-srojas.test` (matching Laragon's auto-generated virtual host) in `.env` before generating any signed URLs.
**Warning signs:** 403 on invitation links that were just generated; `$request->hasValidSignature()` returns false immediately.

### Pitfall 3: Invitation Already Used — 404 vs 403 Confusion
**What goes wrong:** After a client uses an invitation, visiting the link again shows a generic 404 (from `firstOrFail`) instead of a meaningful "already used" message.
**Why it happens:** Using `firstOrFail()` without checking `used_at` separately conflates "not found" with "already used."
**How to avoid:** Fetch the invitation by token first, then check `used_at` explicitly:
```php
$invitation = Invitation::where('token', $request->token)->first();
if (! $invitation) abort(404);
if ($invitation->used_at) {
    return Inertia::render('Error', ['status' => 403, 'message' => 'Esta invitación ya fue utilizada.']);
}
```
**Warning signs:** QA tester reports that reusing a link shows a 404 instead of the expected expired/used message.

### Pitfall 4: Signed URL POST Form — Query Parameters Stripped
**What goes wrong:** The GET invitation acceptance page loads fine, but the POST form submission returns 403 because the signature query parameters are missing.
**Why it happens:** HTML forms only submit fields inside `<form>` — the URL query params (including `signature` and `expires`) are not automatically included.
**How to avoid:** Pass signed URL params as hidden form inputs, or have the controller pass them as props and include them in the Inertia form's `post()` URL:
```vue
// In Accept.vue — use the current full URL for the POST action
const form = useForm({ password: '', password_confirmation: '' })
form.post(route('invitation.accept.store', {
    token: props.token,
    email: props.email,
    client_name: props.client_name,
    // signature and expires are preserved by Inertia's router if you pass the full signed URL
}))
```
Simpler alternative: generate the Inertia POST to `url()->full()` from the controller and pass it as a prop.
**Warning signs:** GET works, POST 403.

### Pitfall 5: Role Not Cast — Comparing String to Enum
**What goes wrong:** Middleware `$user->role === 'admin'` works, but `$user->role === Role::Admin` fails (or vice versa).
**Why it happens:** If the `role` cast is missing from the User model, `$user->role` is a raw string. With the cast, it's a `Role` enum instance.
**How to avoid:** Always define the cast in the User model and use enum comparison consistently: `$user->role === Role::Admin`.
**Warning signs:** Auth tests pass in some places but fail in others depending on whether the user was fetched fresh vs. from session.

### Pitfall 6: Seeder Hardcodes Credentials
**What goes wrong:** `ADMIN_EMAIL` / `ADMIN_PASSWORD` end up committed to `DatabaseSeeder.php`.
**Why it happens:** Convenience during development.
**How to avoid:** Always read from env: `env('ADMIN_EMAIL')` and `env('ADMIN_PASSWORD')` — and add `.env` to `.gitignore` (already default in Laravel).
**Warning signs:** Grep for any literal email address in the seeders directory before committing.

---

## Code Examples

Verified patterns from official sources and cross-verified documentation:

### EnsureIsAdmin Middleware
```php
// app/Http/Middleware/EnsureIsAdmin.php
namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== Role::Admin) {
            abort(403);
        }

        return $next($request);
    }
}
```

### EnsureIsClient Middleware
```php
// app/Http/Middleware/EnsureIsClient.php
namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== Role::Client) {
            abort(403);
        }

        return $next($request);
    }
}
```

### Add Role Migration
```php
// database/migrations/YYYY_MM_DD_add_role_to_users_table.php
use App\Enums\Role;

public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', array_column(Role::cases(), 'value'))
              ->default(Role::Client->value)
              ->after('email');
    });
}
```

### Create Invitations Table Migration
```php
// database/migrations/YYYY_MM_DD_create_invitations_table.php
public function up(): void
{
    Schema::create('invitations', function (Blueprint $table) {
        $table->id();
        $table->string('token')->unique();
        $table->string('email');
        $table->string('client_name');
        $table->timestamp('expires_at');
        $table->timestamp('used_at')->nullable();
        $table->timestamps();
    });
}
```

### DatabaseSeeder — Idempotent Admin Creation
```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    User::firstOrCreate(
        ['email' => env('ADMIN_EMAIL')],
        [
            'name'              => 'Admin',
            'password'          => Hash::make(env('ADMIN_PASSWORD')),
            'role'              => Role::Admin,
            'email_verified_at' => now(),
        ]
    );
}
```

### Generate Invitation Link (InvitationController)
```php
public function store(Request $request): \Illuminate\Http\RedirectResponse
{
    $request->validate([
        'email'       => ['required', 'email', 'unique:users,email'],
        'client_name' => ['required', 'string', 'max:255'],
    ]);

    $token = \Illuminate\Support\Str::uuid()->toString();

    $invitation = Invitation::create([
        'token'       => $token,
        'email'       => $request->email,
        'client_name' => $request->client_name,
        'expires_at'  => now()->addHours(72),
    ]);

    $url = URL::temporarySignedRoute(
        'invitation.accept',
        now()->addHours(72),
        ['token' => $token]
    );

    return back()->with([
        'invitation_url' => $url,  // admin copies this from flash
    ]);
}
```

### Post-Login Role Redirect (AuthenticatedSessionController)
```php
// Override in Breeze's AuthenticatedSessionController@store
// Replace the default redirect with:
$user = Auth::user();

return redirect()->intended(
    $user->role === Role::Admin
        ? route('dashboard')
        : route('portal')
);
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| `app/Http/Kernel.php` for middleware | `bootstrap/app.php` with `withMiddleware()` | Laravel 11 (2024) | No more Kernel.php — alias registration syntax is different |
| `$protected $middlewareAliases` array in Kernel | `$middleware->alias([...])` in `withMiddleware()` | Laravel 11 | Must use new API or middleware won't resolve |
| Inertia v1 `share()` returning nullable/mixed | Inertia v2 `share(): array` with lazy closures | Inertia v2 (2024) | Return type is `array`; closures are lazy-evaluated |
| `Inertia::share()` static call from controller | `share()` in `HandleInertiaRequests` middleware | Current best practice | Middleware-only is canonical; static call still works but is discouraged |
| `defineComponent({ layout })` Options API | `defineOptions({ layout })` in `<script setup>` (Vue 3.3+) | Vue 3.3 (2023) | Cleaner single-block syntax available; dual-script block still works |

**Deprecated/outdated:**
- `app/Http/Kernel.php`: Does not exist in Laravel 11. Any tutorial referencing it is for Laravel 10 or older.
- `RouteServiceProvider::HOME` constant: Removed or deprecated in recent Breeze versions. Use `route('dashboard')` / `route('portal')` directly.
- Inertia v3: Currently in beta (`3.0.0-beta.3`). Do NOT use — breaking changes expected.

---

## Open Questions

1. **Breeze version shipping with Laravel 11 in March 2026**
   - What we know: `laravel/breeze` ^2.x; Inertia client `@inertiajs/vue3` 2.3.18 stable
   - What's unclear: Whether Breeze 2.x already fixed the `HandleInertiaRequests` auto-append bug for all install paths
   - Recommendation: Always verify `bootstrap/app.php` post-install as first step in Wave 0

2. **Laragon virtual host domain for APP_URL**
   - What we know: Laragon auto-generates `hub-srojas.test` for a folder named `hub-srojas`
   - What's unclear: Whether the developer has already set a custom domain in Laragon
   - Recommendation: Task should verify `APP_URL` in `.env` matches the actual Laragon-served domain before generating any signed URLs

3. **invitations table — relationship to users**
   - What we know: The invitation row links by `email`; after acceptance the user is created and the invitation is marked `used_at`
   - What's unclear: Whether a foreign key from `invitations.email` to `users.email` is needed
   - Recommendation: No FK needed — email is used only during acceptance. Post-acceptance, the `users` record is the source of truth. Simpler without FK.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | PHPUnit (Laravel default, installed by `composer create-project`) |
| Config file | `phpunit.xml` (root, created by Laravel) |
| Quick run command | `php artisan test --filter Auth` |
| Full suite command | `php artisan test` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| AUTH-01 | Admin logs in with email+password | Feature | `php artisan test --filter AuthenticationTest` | ❌ Wave 0 |
| AUTH-01 | Admin cannot log in with wrong password | Feature | `php artisan test --filter AuthenticationTest` | ❌ Wave 0 |
| AUTH-02 | Admin generates invitation link (signed URL created) | Feature | `php artisan test --filter InvitationTest::test_admin_can_generate_invitation` | ❌ Wave 0 |
| AUTH-03 | Client accepts valid invitation and sets password | Feature | `php artisan test --filter InvitationTest::test_client_can_accept_invitation` | ❌ Wave 0 |
| AUTH-03 | Expired invitation URL is rejected with 403 | Feature | `php artisan test --filter InvitationTest::test_expired_invitation_is_rejected` | ❌ Wave 0 |
| AUTH-03 | Used invitation URL is rejected | Feature | `php artisan test --filter InvitationTest::test_used_invitation_is_rejected` | ❌ Wave 0 |
| AUTH-04 | Session persists on reload | Feature | `php artisan test --filter SessionTest` | ❌ Wave 0 |
| AUTH-05 | Logout clears session completely | Feature | `php artisan test --filter AuthenticationTest::test_users_can_logout` | ❌ Wave 0 |
| AUTH-01+03 | Admin cannot access /portal; client cannot access /dashboard | Feature | `php artisan test --filter RoleMiddlewareTest` | ❌ Wave 0 |

### Sampling Rate

- **Per task commit:** `php artisan test --filter Auth`
- **Per wave merge:** `php artisan test`
- **Phase gate:** Full suite green before `/gsd:verify-work`

### Wave 0 Gaps

- [ ] `tests/Feature/AuthenticationTest.php` — covers AUTH-01, AUTH-05 (Breeze ships a version of this — verify it exists post-install)
- [ ] `tests/Feature/InvitationTest.php` — covers AUTH-02, AUTH-03 (expired, used, valid flows)
- [ ] `tests/Feature/RoleMiddlewareTest.php` — covers role separation (admin cannot access /portal and vice versa)
- [ ] Framework install: PHPUnit ships with Laravel — no extra install needed
- [ ] Verify `phpunit.xml` uses `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` for test isolation

---

## Sources

### Primary (HIGH confidence)
- `https://laravel.com/docs/11.x/starter-kits` — Breeze install commands, stack options
- `https://laravel.com/docs/11.x/urls` — `temporarySignedRoute()` full API, validation, exception handling
- `https://laravel.com/docs/11.x/middleware` — `withMiddleware()`, alias registration
- `https://inertiajs.com/docs/v2/data-props/shared-data` — `share()` method signature, `usePage()`
- `https://inertiajs.com/docs/v2/advanced/error-handling` — `respond` handler for Inertia error pages
- `https://inertiajs.com/pages` — `createInertiaApp` resolve, `resolvePageComponent`
- npm registry — `@inertiajs/vue3` latest stable: 2.3.18; dist-tags confirm v3 is beta

### Secondary (MEDIUM confidence)
- `https://github.com/laravel/framework/issues/56053` — HandleInertiaRequests not auto-added bug (fixed, but verify post-install)
- `https://artisan.page/11.x/breezeinstall` — `breeze:install` flags (--ssr, --typescript, --pest, --eslint, --dark)
- `https://pineco.de/inviting-users-with-laravels-singed-urls/` — invitation pattern with signed middleware
- `https://owenconti.com/posts/how-to-use-persistent-layouts-with-inertia-and-vue-3-setup-script-syntax` — dual `<script>` + `defineOptions` layout patterns

### Tertiary (LOW confidence)
- Multiple dev.to / Medium articles on middleware registration — cross-verified against official docs

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — verified package versions from npm registry; official Laravel 11 docs
- Architecture: HIGH — patterns drawn from official Inertia v2 docs and Laravel 11 docs
- Pitfalls: MEDIUM-HIGH — Pitfall 1 (HandleInertiaRequests) is HIGH from GitHub issue evidence; others are MEDIUM from community cross-verification
- Validation architecture: MEDIUM — PHPUnit ships with Laravel (HIGH); specific test file names are recommendations (MEDIUM)

**Research date:** 2026-03-19
**Valid until:** 2026-04-19 (stable stack; re-verify if Inertia v3 reaches stable or Laravel 12 LTS is released)
