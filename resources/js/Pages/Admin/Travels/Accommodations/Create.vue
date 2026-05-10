<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    travel: Object,
    tipos: Array,
})

const form = useForm({
    nombre:         '',
    tipo:           'hotel',
    direccion:      '',
    telefono:       '',
    fecha_checkin:  '',
    hora_checkin:   '',
    fecha_checkout: '',
    hora_checkout:  '',
    numero_reserva: '',
    notas:          '',
})

function submit() {
    form.post(`/travels/${props.travel.id}/accommodations`)
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
                    <h1 class="text-xl font-bold text-slate-100">Agregar hospedaje</h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ travel.titulo }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-surface-900 border border-slate-800 rounded-xl p-6 space-y-4">

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Tipo de hospedaje *</label>
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
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nombre del lugar *</label>
                            <input v-model="form.nombre" type="text" placeholder="Ej: Hotel Sheraton Córdoba" class="w-full rounded-lg" />
                            <p v-if="form.errors.nombre" class="text-red-400 text-xs mt-1">{{ form.errors.nombre }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Dirección</label>
                            <input v-model="form.direccion" type="text" placeholder="Ej: Av. Colón 123" class="w-full rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Teléfono</label>
                            <input v-model="form.telefono" type="text" placeholder="+54 351 xxx-xxxx" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">N° de reserva</label>
                            <input v-model="form.numero_reserva" type="text" placeholder="Código de reserva" class="w-full rounded-lg font-mono" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de check-in *</label>
                            <input v-model="form.fecha_checkin" type="date" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hora de check-in</label>
                            <input v-model="form.hora_checkin" type="time" class="w-full rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de check-out *</label>
                            <input v-model="form.fecha_checkout" type="date" class="w-full rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hora de check-out</label>
                            <input v-model="form.hora_checkout" type="time" class="w-full rounded-lg" />
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
                        Agregar hospedaje
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
