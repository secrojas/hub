<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div class="min-h-screen bg-surface-900 flex">
        <!-- Fixed sidebar -->
        <aside class="w-[220px] bg-surface-950 border-r border-slate-700/40 fixed inset-y-0 left-0 flex flex-col">
            <!-- Logo -->
            <div class="px-4 py-5 border-b border-slate-700/40">
                <Link href="/dashboard">
                    <img src="/images/logo.png" alt="srojas" class="h-7 brightness-0 invert" />
                </Link>
            </div>

            <!-- Nav items -->
            <nav class="flex-1 py-4 space-y-0.5">
                <Link href="/dashboard"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url === '/dashboard'
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Dashboard</Link>

                <Link href="/clients"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/clients')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Clientes</Link>

                <Link href="/tasks"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/tasks')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Tareas</Link>

                <Link href="/billing"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/billing')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Facturacion</Link>

                <Link href="/quotes"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/quotes')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Presupuestos</Link>

                <Link href="/invitations/create"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/invitations')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Invitar Cliente</Link>
            </nav>

            <!-- User + Logout at bottom -->
            <div class="border-t border-slate-700/40 px-4 py-4">
                <p class="text-sm text-slate-400 mb-2 truncate">{{ user?.name }}</p>
                <button
                    @click="logout"
                    class="text-sm text-slate-500 hover:text-slate-100 transition-colors duration-150"
                >Cerrar sesion</button>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="ml-[220px] flex-1 flex flex-col">
            <main class="p-6 flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
