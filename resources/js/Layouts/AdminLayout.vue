<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const sidebarOpen = ref(false)

watch(() => page.url, () => { sidebarOpen.value = false })

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div class="min-h-screen bg-surface-900">

        <!-- Mobile top bar -->
        <div class="lg:hidden fixed top-0 left-0 right-0 z-30 flex items-center justify-between px-4 h-14 bg-surface-950 border-b border-slate-700/40">
            <Link href="/dashboard" class="text-xl font-bold text-slate-100 tracking-tight">Hub</Link>
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="p-2 text-slate-400 hover:text-slate-100 transition-colors"
                aria-label="Menú"
            >
                <svg v-if="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Overlay (mobile) -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="lg:hidden fixed inset-0 z-40 bg-black/60"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-[220px] bg-surface-950 border-r border-slate-700/40 flex flex-col',
                'transition-transform duration-200 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo (desktop only — mobile shows it in top bar) -->
            <div class="hidden lg:flex px-4 py-5 border-b border-slate-700/40">
                <Link href="/dashboard" class="text-xl font-bold text-slate-100 tracking-tight">Hub</Link>
            </div>

            <!-- Spacer on mobile to account for top bar height -->
            <div class="lg:hidden h-14 border-b border-slate-700/40 flex items-center px-4">
                <span class="text-xl font-bold text-slate-100 tracking-tight">Hub</span>
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

            <!-- Personal section -->
            <div class="px-3 py-3 border-t border-slate-700/40">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-600 px-2 mb-1.5">Personal</p>
                <Link href="/travels"
                    class="flex items-center gap-2.5 px-3 py-2.5 text-sm rounded-md transition-colors duration-150"
                    :class="$page.url.startsWith('/travels')
                        ? 'bg-cyan-500/10 text-cyan-300 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19 2c-2-2-4-2-5.5-.5L10 5 1.8 6.2c-.5.1-.8.7-.5 1.1l5.4 5.4-1.5 4.5c-.2.5.3 1 .8.8l4.5-1.5 5.4 5.4c.4.3 1 0 1.1-.5z"/>
                    </svg>
                    Viajes
                </Link>
                <Link href="/finance"
                    class="flex items-center gap-2.5 px-3 py-2.5 text-sm rounded-md transition-colors duration-150"
                    :class="$page.url.startsWith('/finance')
                        ? 'bg-emerald-500/10 text-emerald-300 font-semibold'
                        : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Finanzas
                </Link>
            </div>

            <!-- Admin footer -->
            <div class="border-t border-slate-700/40 px-3 py-4 space-y-3">
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
        <div class="lg:ml-[220px] pt-14 lg:pt-0 flex flex-col min-h-screen">
            <main class="p-4 md:p-6 flex-1">
                <slot />
            </main>
        </div>

    </div>
</template>
