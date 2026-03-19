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
    <div class="min-h-screen bg-blue-50">
        <nav class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center space-x-8">
                        <Link href="/portal" class="text-xl font-bold text-blue-700">Hub Portal</Link>
                        <Link
                            href="/portal"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 transition"
                            :class="{ 'text-gray-900 font-semibold': $page.url.startsWith('/portal') }"
                        >
                            Portal
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ user?.name }}</span>
                        <button
                            @click="logout"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 transition"
                        >
                            Cerrar sesion
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
