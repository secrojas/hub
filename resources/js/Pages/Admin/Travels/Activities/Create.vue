<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    travel: Object,
})

const form = useForm({
    fecha:       '',
    hora:        '',
    titulo:      '',
    descripcion: '',
    lugar:       '',
    notas:       '',
})

function submit() {
    form.post(`/travels/${props.travel.id}/activities`)
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
                    <h1 class="text-xl font-bold text-slate-100">Agregar actividad</h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ travel.titulo }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-surface-900 border border-slate-800 rounded-xl p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha *</label>
                            <input v-model="form.fecha" type="date" class="w-full rounded-lg" />
                            <p v-if="form.errors.fecha" class="text-red-400 text-xs mt-1">{{ form.errors.fecha }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Hora</label>
                            <input v-model="form.hora" type="time" class="w-full rounded-lg" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Actividad *</label>
                            <input v-model="form.titulo" type="text" placeholder="Ej: Visita al Museo Histórico" class="w-full rounded-lg" />
                            <p v-if="form.errors.titulo" class="text-red-400 text-xs mt-1">{{ form.errors.titulo }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Lugar</label>
                            <input v-model="form.lugar" type="text" placeholder="Ej: Centro de Córdoba" class="w-full rounded-lg" />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Descripción</label>
                            <textarea v-model="form.descripcion" rows="2" class="w-full rounded-lg resize-none" placeholder="Detalles adicionales..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link :href="`/travels/${travel.id}`" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                        Agregar actividad
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
