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
                <Link href="/dashboard" class="text-xl font-bold text-slate-100 tracking-tight">Hub</Link>
            </div>

            <!-- Main nav -->
            <nav class="flex-1 py-4 space-y-0.5 overflow-y-auto">
                <Link href="/dashboard"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url === '/dashboard'
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Dashboard</Link>

                <Link href="/notes"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/notes')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Notas</Link>

                <Link href="/clients"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/clients')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Clientes</Link>

                <Link href="/tasks"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url === '/tasks' || ($page.url.startsWith('/tasks') && !$page.url.startsWith('/tasks/archived'))
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Tareas</Link>

                <Link href="/tasks/archived"
                    class="flex items-center gap-3 pl-8 pr-4 py-2 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/tasks/archived')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Archivadas</Link>

                <Link href="/billing"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/billing')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Facturación</Link>

                <Link href="/quotes"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150"
                    :class="$page.url.startsWith('/quotes')
                        ? 'bg-violet-600/10 text-violet-400 border-l-2 border-violet-500 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100 border-l-2 border-transparent'"
                >Presupuestos</Link>
            </nav>

            <!-- Knowledge section -->
            <div class="px-3 py-3 border-t border-slate-700/40">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-600 px-2 mb-1.5">Conocimiento</p>
                <Link href="/knowledge"
                    class="flex items-center gap-2.5 px-3 py-2.5 text-sm rounded-md transition-colors duration-150"
                    :class="$page.url.startsWith('/knowledge')
                        ? 'bg-indigo-500/10 text-indigo-300 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
                >
                    <img src="/images/avature.ico" alt="Avature" class="w-3.5 h-3.5 shrink-0 object-contain" />
                    Knowledge Base
                </Link>
            </div>

            <!-- Admin footer -->
            <div class="border-t border-slate-700/40 px-3 py-4 space-y-3">

                <!-- Invitar Cliente -->
                <Link href="/invitations/create"
                    class="flex items-center justify-center gap-2 w-full px-3 py-2 text-xs font-medium rounded-md border transition-colors duration-150"
                    :class="$page.url.startsWith('/invitations')
                        ? 'bg-violet-600/20 border-violet-400/60 text-violet-300'
                        : 'border-slate-600/60 text-slate-400 hover:border-violet-500/50 hover:text-violet-300 hover:bg-violet-600/10'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>
                    </svg>
                    Invitar Cliente
                </Link>

                <!-- User + logout -->
                <div class="flex items-center justify-between gap-2 px-1">
                    <p class="text-xs text-slate-500 truncate">{{ user?.name }}</p>
                    <button
                        @click="logout"
                        class="text-xs text-slate-600 hover:text-slate-300 transition-colors duration-150 shrink-0"
                    >Salir</button>
                </div>
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
