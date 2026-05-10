<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    travel: Object,
    tipos: Array,
})

const form = useForm({
    tipo:            'micro',
    origen:          '',
    destino:         '',
    fecha_salida:    '',
    hora_salida:     '',
    fecha_llegada:   '',
    hora_llegada:    '',
    empresa:         '',
    numero_servicio: '',
    numero_asiento:  '',
    localizador:     '',
    numero_anden:    '',
    notas:           '',
})

function submit() {
    form.post(`/travels/${props.travel.id}/segments`)
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-8">
                <Link :href="`/travels/${travel.id}`" class="text-slate-500 hover:text-slate-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-100">Agregar tramo</h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ travel.titulo }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-surface-900 border border-slate-800 rounded-xl p-6 space-y-4">

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Tipo de transporte *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="t in tipos"
                                :key="t.value"
                                type="button"
                                @click="form.tipo = t.value"
                                class="flex items-center gap-2 px-3 py-2.5 text-sm rounded-lg border transition-all duration-150"
                                :class="form.tipo === t.value
                                    ? 'bg-cyan-600/15 border-cyan-500/50 text-cyan-300 font-medium'
                                    : 'bg-surface-900 border-slate-700 text-slate-400 hover:border-slate-600'"
                            >
                                <span>{{ t.icon }}</span>
                                <span>{{ t.label }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Origen *</label>
                            <input v-model="form.origen" type="text" placeholder="Ej: Buenos Aires" class="w-full rounded-lg" />
                            <p v-if="form.errors.origen" class="text-red-400 text-xs mt-1">{{ form.errors.origen }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Destino *</label>
                            <input v-model="form.destino" type="text" placeholder="Ej: Córdoba" class="w-full rounded-lg" />
                            <p v-if="form.errors.destino" class="text-red-400 text-xs mt-1">{{ form.errors.destino }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de salida *</label>
                            <input v-model="form.fecha_salida" type="date" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hora de salida</label>
                            <input v-model="form.hora_salida" type="time" class="w-full rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de llegada *</label>
                            <input v-model="form.fecha_llegada" type="date" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hora de llegada</label>
                            <input v-model="form.hora_llegada" type="time" class="w-full rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Empresa / Aerolínea</label>
                            <input v-model="form.empresa" type="text" placeholder="Ej: Flecha Bus" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">N° servicio / Vuelo</label>
                            <input v-model="form.numero_servicio" type="text" placeholder="Ej: AR1234" class="w-full rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Asiento</label>
                            <input v-model="form.numero_asiento" type="text" placeholder="Ej: 12A" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Localizador / PNR</label>
                            <input v-model="form.localizador" type="text" placeholder="Ej: ABC123" class="w-full rounded-lg font-mono" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Andén / Puerta de embarque</label>
                            <input v-model="form.numero_anden" type="text" placeholder="Ej: Puerta A3" class="w-full rounded-lg" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Notas</label>
                            <textarea v-model="form.notas" rows="2" class="w-full rounded-lg resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link :href="`/travels/${travel.id}`" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                        Agregar tramo
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
