<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    travel: Object,
})

const estadoBadge = {
    planificado: 'bg-blue-500/15 text-blue-300 border border-blue-500/25',
    en_curso:    'bg-violet-500/15 text-violet-300 border border-violet-500/25',
    completado:  'bg-green-500/15 text-green-300 border border-green-500/25',
    cancelado:   'bg-slate-500/15 text-slate-400 border border-slate-500/25',
}

function formatDate(dateStr, opts = {}) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric', ...opts })
}

function formatShortDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' })
}

function formatTime(t) {
    if (!t) return null
    return t.substring(0, 5)
}

// Agrupar itinerario por día: segmentos + actividades
const diasOrdenados = computed(() => {
    const days = {}

    props.travel.segments.forEach(s => {
        const key = s.fecha_salida
        if (!days[key]) days[key] = { fecha: key, segments: [], activities: [] }
        days[key].segments.push(s)
    })

    props.travel.activities.forEach(a => {
        const key = a.fecha
        if (!days[key]) days[key] = { fecha: key, segments: [], activities: [] }
        days[key].activities.push(a)
    })

    return Object.values(days).sort((a, b) => a.fecha.localeCompare(b.fecha))
})

// Hospedaje agrupado para mostrar fechas únicas
const hasContent = computed(() =>
    diasOrdenados.value.length > 0 || props.travel.accommodations.length > 0
)
</script>

<template>
    <Head :title="`${travel.titulo} — Itinerario`" />

    <div class="min-h-screen bg-slate-950 text-slate-100">

        <!-- Header -->
        <div class="bg-gradient-to-b from-slate-900 to-slate-950 border-b border-slate-800/60 px-4 pt-8 pb-6">
            <div class="max-w-lg mx-auto">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-600">Itinerario</span>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="estadoBadge[travel.estado]">
                        {{ travel.estado_label }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-white leading-tight">{{ travel.titulo }}</h1>
                <p class="text-slate-400 text-sm mt-1">{{ travel.destino }}</p>

                <div class="flex items-center gap-4 mt-4 text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ formatDate(travel.fecha_inicio, { day: 'numeric', month: 'short' }) }} – {{ formatDate(travel.fecha_fin, { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        {{ travel.duracion_dias }} días
                    </div>
                </div>

                <p v-if="travel.descripcion" class="mt-3 text-sm text-slate-400 leading-relaxed">{{ travel.descripcion }}</p>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!hasContent" class="max-w-lg mx-auto px-4 py-20 text-center text-slate-600">
            <div class="text-4xl mb-4">✈️</div>
            <p>El itinerario todavía no tiene información cargada.</p>
        </div>

        <!-- Content -->
        <div v-else class="max-w-lg mx-auto px-4 py-6 space-y-8">

            <!-- ===== Itinerario por día ===== -->
            <section v-if="diasOrdenados.length > 0">
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-600 mb-4">Itinerario</h2>

                <div class="space-y-6">
                    <div v-for="dia in diasOrdenados" :key="dia.fecha">

                        <!-- Day header -->
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-500 shrink-0"></div>
                            <span class="text-sm font-semibold text-slate-300 capitalize">{{ formatShortDate(dia.fecha) }}</span>
                        </div>

                        <!-- Segments for this day -->
                        <div v-for="seg in dia.segments" :key="'seg-' + seg.id"
                            class="ml-4 bg-slate-900 border border-slate-800 rounded-xl p-4 mb-3"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">{{ seg.tipo_icon }}</span>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ seg.tipo_label }}</span>
                                <span v-if="seg.empresa" class="text-xs text-slate-600">· {{ seg.empresa }}</span>
                            </div>

                            <!-- Origen → Destino con horarios -->
                            <div class="flex items-center gap-3 mt-1">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-white leading-none">{{ seg.hora_salida ? formatTime(seg.hora_salida) : '—' }}</p>
                                    <p class="text-xs text-slate-400 mt-1 max-w-[80px] truncate">{{ seg.origen }}</p>
                                </div>
                                <div class="flex-1 flex items-center gap-1.5">
                                    <div class="h-px flex-1 bg-slate-700"></div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                    <div class="h-px flex-1 bg-slate-700"></div>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg font-bold text-white leading-none">{{ seg.hora_llegada ? formatTime(seg.hora_llegada) : '—' }}</p>
                                    <p class="text-xs text-slate-400 mt-1 max-w-[80px] truncate">{{ seg.destino }}</p>
                                </div>
                            </div>

                            <!-- Details chips -->
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span v-if="seg.numero_servicio" class="flex items-center gap-1 bg-slate-800 px-2.5 py-1 rounded-full text-xs text-slate-400">
                                    <span class="text-slate-600">Servicio</span>
                                    <span class="font-medium text-slate-200">{{ seg.numero_servicio }}</span>
                                </span>
                                <span v-if="seg.numero_asiento" class="flex items-center gap-1 bg-slate-800 px-2.5 py-1 rounded-full text-xs text-slate-400">
                                    <span class="text-slate-600">Asiento</span>
                                    <span class="font-medium text-slate-200">{{ seg.numero_asiento }}</span>
                                </span>
                                <span v-if="seg.localizador" class="flex items-center gap-1 bg-cyan-900/40 border border-cyan-700/30 px-2.5 py-1 rounded-full text-xs">
                                    <span class="text-cyan-700">PNR</span>
                                    <span class="font-mono font-bold text-cyan-300">{{ seg.localizador }}</span>
                                </span>
                                <span v-if="seg.numero_anden" class="flex items-center gap-1 bg-slate-800 px-2.5 py-1 rounded-full text-xs text-slate-400">
                                    <span class="text-slate-600">Andén</span>
                                    <span class="font-medium text-slate-200">{{ seg.numero_anden }}</span>
                                </span>
                            </div>

                            <p v-if="seg.notas" class="mt-2 text-xs text-slate-600 italic">{{ seg.notas }}</p>
                        </div>

                        <!-- Activities for this day -->
                        <div v-for="act in dia.activities" :key="'act-' + act.id"
                            class="ml-4 flex gap-3 mb-2"
                        >
                            <div class="flex flex-col items-center pt-1">
                                <div class="w-2 h-2 rounded-full bg-cyan-700 shrink-0"></div>
                                <div class="w-px flex-1 bg-slate-800 mt-1"></div>
                            </div>
                            <div class="pb-3 flex-1 min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span v-if="act.hora" class="text-xs font-semibold text-cyan-600 shrink-0">{{ formatTime(act.hora) }}</span>
                                    <span class="text-sm font-medium text-slate-200">{{ act.titulo }}</span>
                                </div>
                                <p v-if="act.lugar" class="text-xs text-slate-600 mt-0.5">📍 {{ act.lugar }}</p>
                                <p v-if="act.descripcion" class="text-xs text-slate-500 mt-1 leading-relaxed">{{ act.descripcion }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ===== Hospedaje ===== -->
            <section v-if="travel.accommodations.length > 0">
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-600 mb-4">Hospedaje</h2>

                <div class="space-y-3">
                    <div v-for="acc in travel.accommodations" :key="acc.id"
                        class="bg-slate-900 border border-slate-800 rounded-xl p-4"
                    >
                        <div class="flex items-start gap-3">
                            <span class="text-xl shrink-0">{{ acc.tipo_icon }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-100">{{ acc.nombre }}</p>
                                <p class="text-xs text-slate-600 mt-0.5">{{ acc.tipo_label }}</p>

                                <div class="flex gap-4 mt-3 text-xs">
                                    <div>
                                        <p class="text-slate-600 mb-0.5">Check-in</p>
                                        <p class="text-slate-200 font-medium">{{ formatDate(acc.fecha_checkin, { day: 'numeric', month: 'short' }) }}</p>
                                        <p v-if="acc.hora_checkin" class="text-cyan-500 font-semibold">{{ formatTime(acc.hora_checkin) }}</p>
                                    </div>
                                    <div class="w-px bg-slate-800"></div>
                                    <div>
                                        <p class="text-slate-600 mb-0.5">Check-out</p>
                                        <p class="text-slate-200 font-medium">{{ formatDate(acc.fecha_checkout, { day: 'numeric', month: 'short' }) }}</p>
                                        <p v-if="acc.hora_checkout" class="text-cyan-500 font-semibold">{{ formatTime(acc.hora_checkout) }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mt-3">
                                    <a v-if="acc.telefono" :href="`tel:${acc.telefono}`"
                                        class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-full text-xs text-slate-300 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        {{ acc.telefono }}
                                    </a>
                                    <span v-if="acc.numero_reserva" class="flex items-center gap-1 bg-cyan-900/40 border border-cyan-700/30 px-2.5 py-1.5 rounded-full text-xs">
                                        <span class="text-cyan-700">Reserva</span>
                                        <span class="font-mono font-bold text-cyan-300">{{ acc.numero_reserva }}</span>
                                    </span>
                                </div>

                                <p v-if="acc.direccion" class="flex items-center gap-1 mt-2 text-xs text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ acc.direccion }}
                                </p>

                                <p v-if="acc.notas" class="mt-2 text-xs text-slate-600 italic">{{ acc.notas }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- Footer -->
        <div class="max-w-lg mx-auto px-4 py-8 text-center">
            <p class="text-xs text-slate-800">Generado con secrojas · Hub</p>
        </div>

    </div>
</template>
