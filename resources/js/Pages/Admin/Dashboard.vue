<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    enProgreso:      Array,
    vencenProonto:   Array,
    notasDestacadas: Array,
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

        <!-- Section 0: Notas destacadas -->
        <section v-if="notasDestacadas.length">
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                Notas destacadas
                <Link :href="route('notes.index')" class="ml-auto text-xs text-slate-500 hover:text-violet-400 font-normal transition-colors">Ver todas →</Link>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Link
                    v-for="nota in notasDestacadas"
                    :key="nota.id"
                    :href="route('notes.show', nota.id)"
                    class="relative flex flex-col gap-2 p-4 bg-surface-800 border border-slate-700/40 rounded-xl hover:border-slate-600 hover:bg-surface-700 transition-all overflow-hidden"
                >
                    <!-- Color accent -->
                    <span
                        v-if="nota.folder?.color"
                        class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl"
                        :style="{ backgroundColor: nota.folder.color }"
                    />
                    <span class="text-sm font-semibold text-slate-100 leading-snug line-clamp-2">{{ nota.titulo }}</span>
                    <p v-if="nota.extracto" class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ nota.extracto }}</p>
                    <div class="flex items-center gap-2 mt-auto pt-1">
                        <span
                            v-if="nota.folder"
                            class="text-xs px-1.5 py-0.5 rounded-md"
                            :style="nota.folder.color ? { backgroundColor: nota.folder.color + '22', color: nota.folder.color } : {}"
                            :class="!nota.folder.color ? 'text-slate-500 bg-slate-800' : ''"
                        >{{ nota.folder.nombre }}</span>
                        <span class="text-xs text-slate-600 ml-auto">{{ new Date(nota.updated_at).toLocaleDateString('es-AR') }}</span>
                    </div>
                </Link>
            </div>
        </section>

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
