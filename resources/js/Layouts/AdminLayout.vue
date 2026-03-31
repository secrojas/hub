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
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Fixed sidebar -->
        <aside class="w-[220px] bg-white border-r border-gray-200 fixed inset-y-0 left-0 flex flex-col">
            <!-- Logo -->
            <div class="px-4 py-5 border-b border-gray-200">
                <Link href="/dashboard" class="text-xl font-bold text-gray-900">Hub</Link>
            </div>

            <!-- Nav items -->
            <nav class="flex-1 py-4 space-y-1">
                <Link href="/dashboard"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url === '/dashboard'
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Dashboard</Link>

                <Link href="/clients"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url.startsWith('/clients')
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Clientes</Link>

                <Link href="/tasks"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url.startsWith('/tasks')
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Tareas</Link>

                <Link href="/billing"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url.startsWith('/billing')
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Facturacion</Link>

                <Link href="/quotes"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url.startsWith('/quotes')
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Presupuestos</Link>

                <Link href="/invitations/create"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition"
                    :class="$page.url.startsWith('/invitations')
                        ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                >Invitar Cliente</Link>
            </nav>

            <!-- User + Logout at bottom -->
            <div class="border-t border-gray-200 px-4 py-4">
                <p class="text-sm text-gray-600 mb-2 truncate">{{ user?.name }}</p>
                <button
                    @click="logout"
                    class="text-sm text-gray-600 hover:text-gray-900 transition"
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
