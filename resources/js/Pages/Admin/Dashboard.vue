<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    enProgreso: Array,
    vencenProonto: Array,
})

function formatDate(dateStr) {
    if (!dateStr) return '-'
    const d = new Date(dateStr)
    const dd = String(d.getUTCDate()).padStart(2, '0')
    const mm = String(d.getUTCMonth() + 1).padStart(2, '0')
    const yyyy = d.getUTCFullYear()
    return `${dd}/${mm}/${yyyy}`
}

function urgencyClass(fechaLimite) {
    const days = Math.ceil((new Date(fechaLimite) - new Date()) / (1000 * 60 * 60 * 24))
    if (days <= 1)  return 'text-red-400 font-semibold'
    if (days <= 3)  return 'text-orange-400 font-medium'
    if (days <= 7)  return 'text-amber-400 font-medium'
    return 'text-slate-500'
}

function goToClient(clientId) {
    router.get('/tasks', { cliente: clientId }, { preserveState: true })
}

function updateStatus(taskId, event) {
    router.put(route('tasks.updateStatus', taskId), { estado: event.target.value }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Dashboard" />
    <div class="space-y-8">

        <PageHeader title="Dashboard" subtitle="Resumen de tareas activas" />

        <!-- Section 1: En progreso -->
        <section>
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-violet-500 inline-block"></span>
                En progreso
                <span class="ml-1 text-xs text-slate-500 font-normal">({{ enProgreso.length }})</span>
            </h2>
            <Card variant="default" padding="none">
                <!-- Empty state -->
                <div v-if="enProgreso.length === 0" class="px-4 py-10 text-center text-sm text-slate-500">
                    No hay tareas en progreso
                </div>
                <!-- Task rows -->
                <div
                    v-for="task in enProgreso"
                    :key="task.id"
                    @click="goToClient(task.client_id)"
                    class="flex items-center gap-4 px-4 py-3 hover:bg-surface-700/60 cursor-pointer border-b border-slate-700/40 last:border-0 transition-colors duration-150"
                >
                    <span class="flex-1 text-sm font-medium text-slate-100 truncate">{{ task.titulo }}</span>
                    <span class="text-xs text-slate-400 w-32 truncate">{{ task.client.nombre }}</span>
                    <Badge :variant="task.prioridad" />
                    <span class="text-xs w-24 text-right text-slate-400">{{ formatDate(task.fecha_limite) }}</span>
                    <select
                        :value="task.estado"
                        @click.stop
                        @change.stop="updateStatus(task.id, $event)"
                        class="text-xs rounded-md px-2 py-1 bg-surface-700 border border-slate-600/50 text-slate-300 focus:outline-none focus:ring-1 focus:ring-violet-500/50"
                    >
                        <option value="backlog">Backlog</option>
                        <option value="en_progreso">En progreso</option>
                        <option value="en_revision">En revision</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
            </Card>
        </section>

        <!-- Section 2: Vencen pronto -->
        <section>
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                Vencen pronto
                <span class="ml-1 text-xs text-slate-500 font-normal">({{ vencenProonto.length }})</span>
            </h2>
            <Card variant="default" padding="none">
                <!-- Empty state -->
                <div v-if="vencenProonto.length === 0" class="px-4 py-10 text-center text-sm text-slate-500">
                    Nada vence en los proximos 7 dias
                </div>
                <!-- Task rows -->
                <div
                    v-for="task in vencenProonto"
                    :key="task.id"
                    @click="goToClient(task.client_id)"
                    class="flex items-center gap-4 px-4 py-3 hover:bg-surface-700/60 cursor-pointer border-b border-slate-700/40 last:border-0 transition-colors duration-150"
                >
                    <span class="flex-1 text-sm font-medium text-slate-100 truncate">{{ task.titulo }}</span>
                    <span class="text-xs text-slate-400 w-32 truncate">{{ task.client.nombre }}</span>
                    <Badge :variant="task.prioridad" />
                    <span class="text-xs w-24 text-right" :class="urgencyClass(task.fecha_limite)">{{ formatDate(task.fecha_limite) }}</span>
                    <select
                        :value="task.estado"
                        @click.stop
                        @change.stop="updateStatus(task.id, $event)"
                        class="text-xs rounded-md px-2 py-1 bg-surface-700 border border-slate-600/50 text-slate-300 focus:outline-none focus:ring-1 focus:ring-violet-500/50"
                    >
                        <option value="backlog">Backlog</option>
                        <option value="en_progreso">En progreso</option>
                        <option value="en_revision">En revision</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
            </Card>
        </section>

    </div>
</template>
