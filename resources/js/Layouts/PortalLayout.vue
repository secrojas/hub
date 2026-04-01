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
    <div class="min-h-screen bg-surface-900">
        <nav class="bg-surface-950 border-b border-slate-700/40 backdrop-blur-md sticky top-0 z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center space-x-8">
                        <Link href="/portal" class="flex items-center gap-2">
                            <img src="/images/icon.png" alt="srojas" class="h-6 brightness-0 invert" />
                            <span class="text-sm font-semibold text-slate-400">Portal</span>
                        </Link>
                        <Link
                            href="/portal"
                            class="text-sm font-medium transition-colors duration-150 pb-0.5"
                            :class="$page.url.startsWith('/portal')
                                ? 'text-cyan-400 border-b-2 border-cyan-400'
                                : 'text-slate-400 hover:text-slate-100 border-b-2 border-transparent'"
                        >
                            Portal
                        </Link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-slate-400">{{ user?.name }}</span>
                        <button
                            @click="logout"
                            class="text-sm font-medium text-slate-500 hover:text-slate-100 transition-colors duration-150"
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
