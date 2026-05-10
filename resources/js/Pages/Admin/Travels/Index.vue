<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    travelsByYear: Object,
})

const estadoClasses = {
    blue:   'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    violet: 'bg-violet-500/10 text-violet-400 border border-violet-500/20',
    green:  'bg-green-500/10 text-green-400 border border-green-500/20',
    slate:  'bg-slate-500/10 text-slate-400 border border-slate-500/20',
}

function formatDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-AR', { day: 'numeric', month: 'short' })
}

function formatYear(dateStr) {
    return new Date(dateStr + 'T00:00:00').getFullYear()
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-100">Viajes</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Todos los viajes con Nadia</p>
                </div>
                <Link
                    href="/travels/create"
                    class="flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nuevo viaje
                </Link>
            </div>

            <!-- Empty state -->
            <div v-if="!Object.keys(travelsByYear).length" class="text-center py-24">
                <div class="text-5xl mb-4">✈️</div>
                <p class="text-slate-400 text-lg font-medium">Todavía no hay viajes registrados</p>
                <p class="text-slate-600 text-sm mt-1 mb-6">Empezá agregando el primer viaje con Nadia</p>
                <Link href="/travels/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-medium rounded-lg transition-colors duration-150">
                    Agregar primer viaje
                </Link>
            </div>

            <!-- Travels grouped by year -->
            <div v-else class="space-y-10">
                <div v-for="(travels, year) in travelsByYear" :key="year">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-600">{{ year }}</span>
                        <div class="flex-1 h-px bg-slate-800"></div>
                        <span class="text-xs text-slate-700">{{ travels.length }} {{ travels.length === 1 ? 'viaje' : 'viajes' }}</span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <Link
                            v-for="travel in travels"
                            :key="travel.id"
                            :href="`/travels/${travel.id}`"
                            class="group block bg-surface-900 border border-slate-800 hover:border-cyan-700/50 rounded-xl p-5 transition-all duration-200 hover:shadow-glow-cyan"
                        >
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-100 group-hover:text-cyan-300 transition-colors truncate">{{ travel.titulo }}</h3>
                                    <p class="text-sm text-slate-500 mt-0.5 truncate">{{ travel.destino }}</p>
                                </div>
                                <span class="shrink-0 text-xs px-2 py-1 rounded-full font-medium" :class="estadoClasses[travel.estado_color]">
                                    {{ travel.estado_label }}
                                </span>
                            </div>

                            <div class="flex items-center gap-4 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    {{ formatDate(travel.fecha_inicio) }} – {{ formatDate(travel.fecha_fin) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ travel.duracion_dias }} {{ travel.duracion_dias === 1 ? 'día' : 'días' }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
