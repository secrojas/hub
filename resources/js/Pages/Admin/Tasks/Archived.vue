<script setup>
import { ref } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import { formatHoras } from '@/composables/useFormatHoras.js'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    tasks:   Array,
    clients: Array,
    filtros: Object,
})

const filters = ref({
    cliente: props.filtros?.cliente ?? '',
    titulo:  props.filtros?.titulo ?? '',
})

function applyFilters() {
    const params = Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null)
    )
    router.get('/tasks/archived', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

let searchTimeout = null
function onTituloInput() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 300)
}

function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('es-AR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
    })
}

function restoreTask(taskId) {
    router.put(`/tasks/${taskId}/status`, { estado: 'finalizado' }, { preserveScroll: true })
}
</script>

<template>
    <Head title="Tareas archivadas" />

    <div>
        <PageHeader title="Tareas archivadas" subtitle="Historial">
            <Link href="/tasks">
                <Button variant="ghost">Volver al kanban</Button>
            </Link>
        </PageHeader>

        <!-- Filter bar -->
        <div class="glass rounded-xl px-4 py-3 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Cliente</label>
                <select v-model="filters.cliente" @change="applyFilters" class="rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Buscar</label>
                <input
                    v-model="filters.titulo"
                    @input="onTituloInput"
                    type="text"
                    placeholder="Buscar por titulo..."
                    class="rounded-lg text-sm w-48"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="glass rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Titulo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Cliente</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Prioridad</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Horas</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Finalizado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="task in tasks"
                        :key="task.id"
                        class="border-b border-slate-700/20 hover:bg-slate-800/30 transition-colors"
                    >
                        <td class="px-4 py-3 text-slate-200 font-medium">{{ task.titulo }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ task.client?.nombre }}</td>
                        <td class="px-4 py-3">
                            <Badge v-if="task.prioridad" :variant="task.prioridad" />
                        </td>
                        <td class="px-4 py-3 text-slate-400">
                            {{ task.horas ? formatHoras(task.horas) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ formatDate(task.fecha_finalizacion) }}</td>
                        <td class="px-4 py-3 text-right">
                            <button
                                @click="restoreTask(task.id)"
                                class="text-xs text-slate-500 hover:text-violet-400 transition-colors"
                            >
                                Restaurar
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!tasks.length">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500 text-sm">
                            No hay tareas archivadas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
