<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    billings: Object,
    clients:  Array,
    filtros:  Object,
    summary:  Object,
})

const filters = ref({
    cliente: props.filtros?.cliente ?? '',
    estado:  props.filtros?.estado ?? '',
})

function applyFilters() {
    const params = Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null)
    )
    router.get('/billing', params, {
        preserveState:  true,
        preserveScroll: true,
        replace:        true,
    })
}

const billingAEliminar = ref(null)

function confirmDelete(billing) {
    billingAEliminar.value = billing
}

function cancelDelete() {
    billingAEliminar.value = null
}

function deleteBilling() {
    useForm({}).delete(`/billing/${billingAEliminar.value.id}`, {
        onFinish: () => { billingAEliminar.value = null },
    })
}

function estadoBadgeClass(estado) {
    if (estado === 'pagado')   return 'bg-green-100 text-green-800'
    if (estado === 'vencido')  return 'bg-red-100 text-red-800'
    return 'bg-yellow-100 text-yellow-800'
}

function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return dateStr.substring(0, 10)
}
</script>

<template>
    <Head title="Facturacion" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Facturacion</h1>
            <Link href="/billing/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Nuevo Cobro
            </Link>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-400">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Cobrado este mes</p>
                <p class="text-xl font-semibold text-green-700">{{ formatMonto(summary?.cobrado_mes ?? 0) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-yellow-400">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Deuda pendiente</p>
                <p class="text-xl font-semibold text-yellow-700">{{ formatMonto(summary?.pendiente_total ?? 0) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-red-400">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Cobros vencidos</p>
                <p class="text-xl font-semibold text-red-700">{{ summary?.vencidos_count ?? 0 }}</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="mb-6 flex flex-wrap gap-3 items-end">
            <!-- Estado dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select v-model="filters.estado" @change="applyFilters" class="border-gray-300 rounded text-sm">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>
            <!-- Cliente dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                <select v-model="filters.cliente" @change="applyFilters" class="border-gray-300 rounded text-sm">
                    <option value="">Todos</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
            </div>
        </div>

        <!-- Billing table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Emision</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="billing in billings.data" :key="billing.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ billing.concepto }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ billing.client?.nombre ?? 'Sin cliente' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatMonto(billing.monto) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(billing.fecha_emision) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', estadoBadgeClass(billing.estado)]">
                                {{ billing.estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                            <Link :href="`/billing/${billing.id}/edit`" class="text-gray-600 hover:text-gray-900">Editar</Link>
                            <button @click="confirmDelete(billing)" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                    <tr v-if="billings.data.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay registros de facturacion.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="billings.links && billings.links.length > 3" class="mt-4 flex items-center justify-center gap-1">
            <Link
                v-for="link in billings.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                :class="[
                    'px-3 py-1 text-sm rounded border',
                    link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50',
                    !link.url ? 'opacity-50 pointer-events-none' : '',
                ]"
                :preserve-state="true"
                :preserve-scroll="true"
            />
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="billingAEliminar" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Confirmar eliminacion</h2>
            <p class="text-gray-600 mb-6">
                Eliminar el cobro <strong>{{ billingAEliminar.concepto }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                >
                    Cancelar
                </button>
                <button
                    @click="deleteBilling"
                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                >
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</template>
