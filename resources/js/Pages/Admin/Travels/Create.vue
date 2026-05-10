<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    estados: Array,
})

const form = useForm({
    titulo:       '',
    destino:      '',
    descripcion:  '',
    fecha_inicio: '',
    fecha_fin:    '',
    estado:       'planificado',
    notas:        '',
})

function submit() {
    form.post('/travels')
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-8">
                <Link href="/travels" class="text-slate-500 hover:text-slate-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-slate-100">Nuevo viaje</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="bg-surface-900 border border-slate-800 rounded-xl p-6 space-y-5">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Título del viaje *</label>
                            <input v-model="form.titulo" type="text" placeholder="Ej: Córdoba Mayo 2026" class="w-full rounded-lg" />
                            <p v-if="form.errors.titulo" class="text-red-400 text-xs mt-1">{{ form.errors.titulo }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Destino *</label>
                            <input v-model="form.destino" type="text" placeholder="Ej: Córdoba, Argentina" class="w-full rounded-lg" />
                            <p v-if="form.errors.destino" class="text-red-400 text-xs mt-1">{{ form.errors.destino }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de inicio *</label>
                            <input v-model="form.fecha_inicio" type="date" class="w-full rounded-lg" />
                            <p v-if="form.errors.fecha_inicio" class="text-red-400 text-xs mt-1">{{ form.errors.fecha_inicio }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Fecha de regreso *</label>
                            <input v-model="form.fecha_fin" type="date" class="w-full rounded-lg" />
                            <p v-if="form.errors.fecha_fin" class="text-red-400 text-xs mt-1">{{ form.errors.fecha_fin }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Estado</label>
                            <select v-model="form.estado" class="w-full rounded-lg">
                                <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Descripción</label>
                            <textarea v-model="form.descripcion" rows="3" placeholder="Breve descripción del viaje..." class="w-full rounded-lg resize-none"></textarea>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Notas internas</label>
                            <textarea v-model="form.notas" rows="2" placeholder="Notas adicionales..." class="w-full rounded-lg resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link href="/travels" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">Cancelar</Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                    >
                        Crear viaje
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
