<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { Head } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
    tasks: Array,
    quotes: Array,
    billings: Array,
    dashboard: Object,
})

function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}

const taskLabels = { backlog: 'Backlog', en_progreso: 'En progreso', en_revision: 'En revision', finalizado: 'Finalizado' }
const quoteLabels = { borrador: 'Borrador', enviado: 'Enviado', aceptado: 'Aceptado', rechazado: 'Rechazado' }
const billingLabels = { pendiente: 'Pendiente', pagado: 'Pagado', vencido: 'Vencido' }

const taskBadgeClass = {
    backlog:     'bg-gray-100 text-gray-700',
    en_progreso: 'bg-blue-100 text-blue-700',
    en_revision: 'bg-yellow-100 text-yellow-700',
    finalizado:  'bg-green-100 text-green-700',
}

const quoteBadgeClass = {
    borrador:  'bg-gray-100 text-gray-700',
    enviado:   'bg-blue-100 text-blue-700',
    aceptado:  'bg-green-100 text-green-700',
    rechazado: 'bg-red-100 text-red-700',
}

const billingBadgeClass = {
    pendiente: 'bg-yellow-100 text-yellow-700',
    pagado:    'bg-green-100 text-green-700',
    vencido:   'bg-red-100 text-red-700',
}

function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('es-AR')
}
</script>

<template>
    <Head title="Mi Portal" />

    <div class="space-y-8">

        <!-- Dashboard summary cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Mis Tareas -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Mis Tareas</h3>
                <dl class="space-y-2">
                    <div v-for="(label, status) in taskLabels" :key="status" class="flex justify-between text-sm">
                        <dt class="text-gray-600">{{ label }}</dt>
                        <dd class="font-medium text-gray-900">{{ dashboard.tareas[status] || 0 }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Mis Presupuestos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Mis Presupuestos</h3>
                <dl class="space-y-2">
                    <div v-for="(label, status) in quoteLabels" :key="status" class="flex justify-between text-sm">
                        <dt class="text-gray-600">{{ label }}</dt>
                        <dd class="font-medium text-gray-900">{{ dashboard.presupuestos[status] || 0 }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Mi Facturacion -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Mi Facturacion</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">Pendiente</dt>
                        <dd class="font-medium text-red-600">{{ formatMonto(dashboard.facturacion.pendiente) }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">Pagado</dt>
                        <dd class="font-medium text-green-600">{{ formatMonto(dashboard.facturacion.pagado) }}</dd>
                    </div>
                </dl>
            </div>

        </div>

        <!-- Tareas list -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Tareas</h2>
            <p v-if="tasks.length === 0" class="text-gray-500">No tenes tareas asignadas.</p>
            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Limite</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="task in tasks" :key="task.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ task.titulo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', taskBadgeClass[task.estado] ?? 'bg-gray-100 text-gray-700']">
                                    {{ taskLabels[task.estado] ?? task.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(task.fecha_limite) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Presupuestos list -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Presupuestos</h2>
            <p v-if="quotes.length === 0" class="text-gray-500">No tenes presupuestos.</p>
            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PDF</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="quote in quotes" :key="quote.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ quote.titulo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', quoteBadgeClass[quote.estado] ?? 'bg-gray-100 text-gray-700']">
                                    {{ quoteLabels[quote.estado] ?? quote.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatMonto(quote.total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a :href="'/portal/quotes/' + quote.id + '/pdf'" class="text-blue-600 hover:text-blue-800 underline">Descargar PDF</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Facturacion list -->
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Facturacion</h2>
            <p v-if="billings.length === 0" class="text-gray-500">No tenes cobros registrados.</p>
            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Emision</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="billing in billings" :key="billing.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ billing.concepto }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatMonto(billing.monto) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(billing.fecha_emision) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', billingBadgeClass[billing.estado] ?? 'bg-gray-100 text-gray-700']">
                                    {{ billingLabels[billing.estado] ?? billing.estado }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
