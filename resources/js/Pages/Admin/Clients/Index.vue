<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    clients: Object,
    filtroEstado: String,
})

const selectedEstado = ref(props.filtroEstado || '')
const clienteAEliminar = ref(null)

function filterByEstado(value) {
    selectedEstado.value = value
    router.get('/clients', { estado: value || undefined }, { preserveState: true, preserveScroll: true })
}

function confirmDelete(client) {
    clienteAEliminar.value = client
}

function cancelDelete() {
    clienteAEliminar.value = null
}

function deleteClient() {
    router.delete(`/clients/${clienteAEliminar.value.id}`, {
        onFinish: () => { clienteAEliminar.value = null },
    })
}

function estadoBadgeClass(estado) {
    if (estado === 'activo') return 'bg-green-100 text-green-800'
    if (estado === 'potencial') return 'bg-blue-100 text-blue-800'
    return 'bg-gray-100 text-gray-700'
}

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return dateStr.substring(0, 10)
}
</script>

<template>
    <Head title="Clientes" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Clientes</h1>
            <Link href="/clients/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Nuevo Cliente
            </Link>
        </div>

        <!-- Estado filter -->
        <div class="mb-4">
            <select
                :value="selectedEstado"
                @change="filterByEstado($event.target.value)"
                class="border-gray-300 rounded-md shadow-sm text-sm"
            >
                <option value="">Todos</option>
                <option value="activo">Activo</option>
                <option value="potencial">Potencial</option>
                <option value="pausado">Pausado</option>
            </select>
        </div>

        <!-- Clients table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <Link :href="`/clients/${client.id}`" class="text-blue-600 hover:text-blue-900 font-medium">
                                {{ client.nombre }}
                            </Link>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ client.empresa || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', estadoBadgeClass(client.estado)]">
                                {{ client.estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(client.fecha_inicio) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                            <Link :href="`/clients/${client.id}`" class="text-gray-600 hover:text-gray-900">Ver</Link>
                            <Link :href="`/clients/${client.id}/edit`" class="text-gray-600 hover:text-gray-900">Editar</Link>
                            <button @click="confirmDelete(client)" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </td>
                    </tr>
                    <tr v-if="clients.data.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay clientes registrados.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="clients.links && clients.links.length > 3" class="mt-4 flex items-center justify-center gap-1">
            <Link
                v-for="link in clients.links"
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
    <div v-if="clienteAEliminar" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Confirmar eliminacion</h2>
            <p class="text-gray-600 mb-6">
                Eliminar a <strong>{{ clienteAEliminar.nombre }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                >
                    Cancelar
                </button>
                <button
                    @click="deleteClient"
                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                >
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</template>
