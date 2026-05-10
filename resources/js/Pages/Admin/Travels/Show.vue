<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    travel: Object,
})

const activeTab    = ref('itinerario')
const showDocForm  = ref(false)
const copied       = ref(false)

const docForm = useForm({
    archivo:                  null,
    nombre:                   '',
    tipo:                     'pasaje',
    travel_segment_id:        '',
    travel_accommodation_id:  '',
    notas:                    '',
})

const estadoClasses = {
    blue:   'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    violet: 'bg-violet-500/10 text-violet-400 border border-violet-500/20',
    green:  'bg-green-500/10 text-green-400 border border-green-500/20',
    slate:  'bg-slate-500/10 text-slate-400 border border-slate-500/20',
}

function formatDate(dateStr, opts = {}) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric', ...opts })
}

function formatShortDate(dateStr) {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('es-AR', { day: 'numeric', month: 'short' })
}

function copyShareLink() {
    const url = `${window.location.origin}/v/${props.travel.share_token}`
    navigator.clipboard.writeText(url)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
}

function deleteSegment(segmentId) {
    if (!confirm('¿Eliminar este tramo?')) return
    router.delete(`/travels/${props.travel.id}/segments/${segmentId}`)
}

function deleteAccommodation(id) {
    if (!confirm('¿Eliminar este hospedaje?')) return
    router.delete(`/travels/${props.travel.id}/accommodations/${id}`)
}

function deleteActivity(id) {
    if (!confirm('¿Eliminar esta actividad?')) return
    router.delete(`/travels/${props.travel.id}/activities/${id}`)
}

function deleteDocument(id) {
    if (!confirm('¿Eliminar este documento?')) return
    router.delete(`/travels/${props.travel.id}/documents/${id}`)
}

function submitDoc() {
    docForm.post(`/travels/${props.travel.id}/documents`, {
        onSuccess: () => {
            docForm.reset()
            showDocForm.value = false
        },
    })
}

// Group itinerary items (segments + activities) by date
const itinerarioPorDia = computed(() => {
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
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-start gap-3 min-w-0">
                    <Link href="/travels" class="mt-1 text-slate-500 hover:text-slate-300 transition-colors shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </Link>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl font-bold text-slate-100">{{ travel.titulo }}</h1>
                            <span class="text-sm px-2.5 py-1 rounded-full font-medium" :class="estadoClasses[travel.estado_color]">
                                {{ travel.estado_label }}
                            </span>
                        </div>
                        <p class="text-slate-500 mt-1 text-sm">
                            {{ travel.destino }} · {{ formatShortDate(travel.fecha_inicio) }} – {{ formatShortDate(travel.fecha_fin) }} · {{ travel.duracion_dias }} días
                        </p>
                        <p v-if="travel.descripcion" class="text-slate-400 mt-2 text-sm">{{ travel.descripcion }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        @click="copyShareLink"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors duration-150"
                        :class="copied
                            ? 'border-green-500/40 text-green-400 bg-green-500/10'
                            : 'border-slate-700 text-slate-400 hover:border-cyan-600/50 hover:text-cyan-400 hover:bg-cyan-500/5'"
                    >
                        <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        {{ copied ? 'Copiado' : 'Compartir' }}
                    </button>
                    <Link :href="`/travels/${travel.id}/edit`" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-slate-700 text-slate-400 hover:border-slate-600 hover:text-slate-200 rounded-lg transition-colors duration-150">
                        Editar
                    </Link>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-0 border-b border-slate-800 mb-6">
                <button
                    v-for="tab in [
                        { key: 'itinerario', label: 'Itinerario', count: travel.segments.length + travel.activities.length },
                        { key: 'hospedaje', label: 'Hospedaje', count: travel.accommodations.length },
                        { key: 'documentos', label: 'Documentos', count: travel.documents.length },
                    ]"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="flex items-center gap-1.5 px-4 py-3 text-sm transition-colors duration-150 border-b-2 -mb-px"
                    :class="activeTab === tab.key
                        ? 'text-cyan-400 border-cyan-500 font-medium'
                        : 'text-slate-500 border-transparent hover:text-slate-300'"
                >
                    {{ tab.label }}
                    <span v-if="tab.count > 0" class="text-xs px-1.5 py-0.5 rounded-full"
                        :class="activeTab === tab.key ? 'bg-cyan-500/15 text-cyan-400' : 'bg-slate-800 text-slate-600'"
                    >{{ tab.count }}</span>
                </button>
            </div>

            <!-- ===== TAB: Itinerario ===== -->
            <div v-if="activeTab === 'itinerario'">
                <div class="flex justify-end gap-2 mb-4">
                    <Link :href="`/travels/${travel.id}/segments/create`"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface-900 border border-slate-700 hover:border-slate-600 text-slate-300 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Agregar tramo
                    </Link>
                    <Link :href="`/travels/${travel.id}/activities/create`"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface-900 border border-slate-700 hover:border-slate-600 text-slate-300 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Agregar actividad
                    </Link>
                </div>

                <div v-if="itinerarioPorDia.length === 0" class="text-center py-16 text-slate-600">
                    <div class="text-3xl mb-3">🗺️</div>
                    <p class="text-sm">Todavía no hay tramos ni actividades.<br>Empezá agregando el primer tramo del viaje.</p>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="dia in itinerarioPorDia" :key="dia.fecha">
                        <!-- Day label -->
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
                                {{ formatDate(dia.fecha, { weekday: 'long', day: 'numeric', month: 'long' }) }}
                            </span>
                            <div class="flex-1 h-px bg-slate-800"></div>
                        </div>

                        <!-- Segments -->
                        <div v-for="segment in dia.segments" :key="'seg-' + segment.id"
                            class="group relative bg-surface-900 border border-slate-800 rounded-xl p-4 mb-3"
                        >
                            <div class="flex items-start gap-3">
                                <span class="text-xl shrink-0 mt-0.5">{{ segment.tipo_icon }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ segment.tipo_label }}</span>
                                        <span v-if="segment.empresa" class="text-xs text-slate-600">· {{ segment.empresa }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-base font-medium text-slate-100">
                                        <span>{{ segment.origen }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                                        </svg>
                                        <span>{{ segment.destino }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-slate-500">
                                        <span v-if="segment.hora_salida">Salida: <span class="text-slate-300 font-medium">{{ segment.hora_salida }}</span></span>
                                        <span v-if="segment.hora_llegada">Llegada: <span class="text-slate-300 font-medium">{{ segment.hora_llegada }}</span></span>
                                        <span v-if="segment.numero_servicio">N° servicio: <span class="text-slate-300">{{ segment.numero_servicio }}</span></span>
                                        <span v-if="segment.numero_asiento">Asiento: <span class="text-slate-300">{{ segment.numero_asiento }}</span></span>
                                        <span v-if="segment.localizador">Localizador: <span class="font-mono text-cyan-400 font-semibold">{{ segment.localizador }}</span></span>
                                        <span v-if="segment.numero_anden">Andén / Puerta: <span class="text-slate-300">{{ segment.numero_anden }}</span></span>
                                    </div>
                                    <p v-if="segment.notas" class="mt-2 text-xs text-slate-500 italic">{{ segment.notas }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <Link :href="`/travels/${travel.id}/segments/${segment.id}/edit`" class="p-1.5 text-slate-500 hover:text-slate-200 hover:bg-slate-800 rounded transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </Link>
                                    <button @click="deleteSegment(segment.id)" class="p-1.5 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Activities -->
                        <div v-for="activity in dia.activities" :key="'act-' + activity.id"
                            class="group relative bg-surface-900 border border-slate-800 rounded-xl p-4 mb-3 border-l-2 border-l-cyan-800"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 shrink-0 mt-0.5 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-cyan-600"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2">
                                        <span v-if="activity.hora" class="text-xs font-semibold text-cyan-600">{{ activity.hora }}</span>
                                        <span class="text-sm font-medium text-slate-200">{{ activity.titulo }}</span>
                                    </div>
                                    <p v-if="activity.lugar" class="text-xs text-slate-500 mt-0.5">{{ activity.lugar }}</p>
                                    <p v-if="activity.descripcion" class="text-xs text-slate-400 mt-1">{{ activity.descripcion }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <Link :href="`/travels/${travel.id}/activities/${activity.id}/edit`" class="p-1.5 text-slate-500 hover:text-slate-200 hover:bg-slate-800 rounded transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </Link>
                                    <button @click="deleteActivity(activity.id)" class="p-1.5 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB: Hospedaje ===== -->
            <div v-else-if="activeTab === 'hospedaje'">
                <div class="flex justify-end mb-4">
                    <Link :href="`/travels/${travel.id}/accommodations/create`"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface-900 border border-slate-700 hover:border-slate-600 text-slate-300 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Agregar hospedaje
                    </Link>
                </div>

                <div v-if="travel.accommodations.length === 0" class="text-center py-16 text-slate-600">
                    <div class="text-3xl mb-3">🏨</div>
                    <p class="text-sm">Todavía no hay hospedajes cargados.</p>
                </div>

                <div v-else class="space-y-3">
                    <div v-for="acc in travel.accommodations" :key="acc.id"
                        class="group bg-surface-900 border border-slate-800 rounded-xl p-5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="text-xl shrink-0">{{ acc.tipo_icon }}</span>
                                <div class="min-w-0">
                                    <div class="flex items-baseline gap-2">
                                        <h3 class="font-semibold text-slate-100">{{ acc.nombre }}</h3>
                                        <span class="text-xs text-slate-600">{{ acc.tipo_label }}</span>
                                    </div>
                                    <p v-if="acc.direccion" class="text-xs text-slate-500 mt-0.5">{{ acc.direccion }}</p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-slate-500">
                                        <span>Check-in: <span class="text-slate-300 font-medium">{{ formatShortDate(acc.fecha_checkin) }}{{ acc.hora_checkin ? ' · ' + acc.hora_checkin : '' }}</span></span>
                                        <span>Check-out: <span class="text-slate-300 font-medium">{{ formatShortDate(acc.fecha_checkout) }}{{ acc.hora_checkout ? ' · ' + acc.hora_checkout : '' }}</span></span>
                                        <span v-if="acc.numero_reserva">Reserva: <span class="font-mono text-cyan-400 font-semibold">{{ acc.numero_reserva }}</span></span>
                                        <span v-if="acc.telefono">Tel: <span class="text-slate-300">{{ acc.telefono }}</span></span>
                                    </div>
                                    <p v-if="acc.notas" class="mt-2 text-xs text-slate-500 italic">{{ acc.notas }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                <Link :href="`/travels/${travel.id}/accommodations/${acc.id}/edit`" class="p-1.5 text-slate-500 hover:text-slate-200 hover:bg-slate-800 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </Link>
                                <button @click="deleteAccommodation(acc.id)" class="p-1.5 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB: Documentos ===== -->
            <div v-else-if="activeTab === 'documentos'">
                <div class="flex justify-end mb-4">
                    <button
                        @click="showDocForm = !showDocForm"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface-900 border border-slate-700 hover:border-slate-600 text-slate-300 rounded-lg transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Subir documento
                    </button>
                </div>

                <!-- Upload form -->
                <div v-if="showDocForm" class="bg-surface-900 border border-slate-700 rounded-xl p-5 mb-5">
                    <h3 class="text-sm font-semibold text-slate-200 mb-4">Subir documento</h3>
                    <form @submit.prevent="submitDoc" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Archivo (PDF o imagen, máx. 20 MB) *</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" @change="e => docForm.archivo = e.target.files[0]" class="w-full text-xs text-slate-400 file:mr-3 file:px-3 file:py-1.5 file:rounded file:border-0 file:text-xs file:bg-slate-700 file:text-slate-200 hover:file:bg-slate-600 file:cursor-pointer" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">Nombre del documento</label>
                                <input v-model="docForm.nombre" type="text" placeholder="Ej: Pasaje Córdoba ida" class="w-full rounded-lg text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tipo *</label>
                                <select v-model="docForm.tipo" class="w-full rounded-lg text-sm">
                                    <option value="pasaje">Pasaje</option>
                                    <option value="reserva">Reserva</option>
                                    <option value="voucher">Voucher</option>
                                    <option value="foto">Foto</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div v-if="travel.segments.length">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tramo (opcional)</label>
                                <select v-model="docForm.travel_segment_id" class="w-full rounded-lg text-sm">
                                    <option value="">Sin asociar</option>
                                    <option v-for="s in travel.segments" :key="s.id" :value="s.id">
                                        {{ s.tipo_icon }} {{ s.origen }} → {{ s.destino }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="travel.accommodations.length">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">Hospedaje (opcional)</label>
                                <select v-model="docForm.travel_accommodation_id" class="w-full rounded-lg text-sm">
                                    <option value="">Sin asociar</option>
                                    <option v-for="a in travel.accommodations" :key="a.id" :value="a.id">
                                        {{ a.tipo_icon }} {{ a.nombre }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="showDocForm = false" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="docForm.processing" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 text-white text-xs font-medium rounded-lg transition-colors">
                                Subir
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="travel.documents.length === 0 && !showDocForm" class="text-center py-16 text-slate-600">
                    <div class="text-3xl mb-3">📎</div>
                    <p class="text-sm">No hay documentos cargados todavía.</p>
                </div>

                <div v-else-if="travel.documents.length" class="space-y-2">
                    <div v-for="doc in travel.documents" :key="doc.id"
                        class="group flex items-center gap-3 bg-surface-900 border border-slate-800 rounded-lg px-4 py-3"
                    >
                        <div class="text-slate-500 shrink-0">
                            <svg v-if="doc.is_pdf" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <svg v-else-if="doc.is_image" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-200 truncate">{{ doc.nombre }}</p>
                            <p class="text-xs text-slate-600">{{ doc.tipo_label }} · {{ doc.tamanio_formateado }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                            <a :href="`/travels/${travel.id}/documents/${doc.id}/download`" class="p-1.5 text-slate-500 hover:text-cyan-400 hover:bg-cyan-500/10 rounded transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </a>
                            <button @click="deleteDocument(doc.id)" class="p-1.5 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
