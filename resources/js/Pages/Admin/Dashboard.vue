<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    enProgreso: Array,
    vencenProonto: Array,
})

const prioridadBadgeClass = {
    alta:  'bg-red-100 text-red-800',
    media: 'bg-yellow-100 text-yellow-800',
    baja:  'bg-green-100 text-green-800',
}

function urgencyClass(fechaLimite) {
    const days = Math.ceil((new Date(fechaLimite) - new Date()) / (1000 * 60 * 60 * 24))
    if (days <= 1)  return 'text-red-600 font-semibold'
    if (days <= 3)  return 'text-orange-500 font-medium'
    if (days <= 7)  return 'text-yellow-600 font-medium'
    return 'text-gray-400'
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
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

        <!-- Section 1: En progreso -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">En progreso</h2>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Empty state -->
                <div v-if="enProgreso.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                    No hay tareas en progreso
                </div>
                <!-- Task rows -->
                <div
                    v-for="task in enProgreso"
                    :key="task.id"
                    @click="goToClient(task.client_id)"
                    class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                    <span class="flex-1 text-sm font-medium text-gray-900 truncate">{{ task.titulo }}</span>
                    <span class="text-xs text-gray-500 w-32 truncate">{{ task.client.nombre }}</span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="prioridadBadgeClass[task.prioridad]">{{ task.prioridad }}</span>
                    <span class="text-xs w-24 text-right text-gray-400">{{ task.fecha_limite ?? '-' }}</span>
                    <select
                        :value="task.estado"
                        @change.stop="updateStatus(task.id, $event)"
                        class="text-xs border-gray-300 rounded text-gray-700 py-0.5 bg-white"
                    >
                        <option value="backlog">Backlog</option>
                        <option value="en_progreso">En progreso</option>
                        <option value="en_revision">En revision</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Section 2: Vencen pronto -->
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Vencen pronto</h2>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Empty state -->
                <div v-if="vencenProonto.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
                    Nada vence en los proximos 7 dias
                </div>
                <!-- Task rows -->
                <div
                    v-for="task in vencenProonto"
                    :key="task.id"
                    @click="goToClient(task.client_id)"
                    class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0"
                >
                    <span class="flex-1 text-sm font-medium text-gray-900 truncate">{{ task.titulo }}</span>
                    <span class="text-xs text-gray-500 w-32 truncate">{{ task.client.nombre }}</span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="prioridadBadgeClass[task.prioridad]">{{ task.prioridad }}</span>
                    <span class="text-xs w-24 text-right" :class="urgencyClass(task.fecha_limite)">{{ task.fecha_limite }}</span>
                    <select
                        :value="task.estado"
                        @change.stop="updateStatus(task.id, $event)"
                        class="text-xs border-gray-300 rounded text-gray-700 py-0.5 bg-white"
                    >
                        <option value="backlog">Backlog</option>
                        <option value="en_progreso">En progreso</option>
                        <option value="en_revision">En revision</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
            </div>
        </section>
    </div>
</template>
