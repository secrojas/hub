<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    quotes:  Object,
    clients: Array,
})

const quoteAEliminar = ref(null)

function confirmDelete(quote) {
    quoteAEliminar.value = quote
}

function cancelDelete() {
    quoteAEliminar.value = null
}

function deleteQuote() {
    useForm({}).delete(route('quotes.destroy', quoteAEliminar.value.id), {
        onFinish: () => { quoteAEliminar.value = null },
    })
}

function changeEstado(quote, newEstado) {
    useForm({ estado: newEstado }).patch(route('quotes.updateEstado', quote.id), {
        preserveScroll: true,
    })
}

function estadoBadgeClass(estado) {
    if (estado === 'borrador')  return 'bg-gray-100 text-gray-800'
    if (estado === 'enviado')   return 'bg-blue-100 text-blue-800'
    if (estado === 'aceptado')  return 'bg-green-100 text-green-800'
    if (estado === 'rechazado') return 'bg-red-100 text-red-800'
    return 'bg-gray-100 text-gray-800'
}

function formatMonto(value) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value)
}
</script>

<template>
    <Head title="Presupuestos" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Presupuestos</h1>
            <Link :href="route('quotes.create')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Nuevo Presupuesto
            </Link>
        </div>

        <!-- Quotes table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titulo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="quote in quotes" :key="quote.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ quote.client?.nombre ?? 'Sin cliente' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ quote.titulo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ quote.items?.length ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatMonto(quote.total ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', estadoBadgeClass(quote.estado)]">
                                {{ quote.estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <!-- Edit always visible -->
                            <Link :href="route('quotes.edit', quote.id)" class="text-gray-600 hover:text-gray-900">Editar</Link>

                            <!-- Estado change select -->
                            <select
                                :value="quote.estado"
                                @change="changeEstado(quote, $event.target.value)"
                                class="border-gray-300 rounded text-xs"
                            >
                                <option value="borrador">Borrador</option>
                                <option value="enviado">Enviado</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>

                            <!-- Delete only for borrador -->
                            <button
                                v-if="quote.estado === 'borrador'"
                                @click="confirmDelete(quote)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Eliminar
                            </button>

                            <!-- PDF download for non-borrador -->
                            <a
                                v-if="quote.estado !== 'borrador'"
                                :href="route('quotes.pdf', quote.id)"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                Descargar PDF
                            </a>
                        </td>
                    </tr>
                    <tr v-if="!quotes || quotes.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay presupuestos.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="quoteAEliminar" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Confirmar eliminacion</h2>
            <p class="text-gray-600 mb-6">
                Eliminar el presupuesto <strong>{{ quoteAEliminar.titulo }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                >
                    Cancelar
                </button>
                <button
                    @click="deleteQuote"
                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                >
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</template>
