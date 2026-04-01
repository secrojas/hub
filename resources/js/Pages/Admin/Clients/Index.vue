<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

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

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return dateStr.substring(0, 10)
}
</script>

<template>
    <Head title="Clientes" />

    <div>
        <PageHeader title="Clientes" subtitle="Gestiona tu base de clientes">
            <Link href="/clients/create">
                <Button variant="primary">Nuevo Cliente</Button>
            </Link>
        </PageHeader>

        <!-- Estado filter -->
        <div class="glass rounded-xl px-4 py-3 mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Estado</label>
                <select
                    :value="selectedEstado"
                    @change="filterByEstado($event.target.value)"
                    class="text-sm rounded-lg"
                >
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="potencial">Potencial</option>
                    <option value="pausado">Pausado</option>
                </select>
            </div>
        </div>

        <!-- Clients table -->
        <Card variant="default" padding="none">
            <div class="overflow-hidden rounded-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-800 border-b border-slate-700/40">
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="client in clients.data" :key="client.id" class="border-b border-slate-700/20 hover:bg-surface-700/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="`/clients/${client.id}`" class="text-violet-400 hover:text-violet-300 font-medium">
                                    {{ client.nombre }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ client.empresa || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Badge :variant="client.estado" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ formatDate(client.fecha_inicio) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-3">
                                <Link :href="`/clients/${client.id}`" class="text-violet-400 hover:text-violet-300">Ver</Link>
                                <Link :href="`/clients/${client.id}/edit`" class="text-violet-400 hover:text-violet-300">Editar</Link>
                                <button @click="confirmDelete(client)" class="text-red-400 hover:text-red-300">Eliminar</button>
                            </td>
                        </tr>
                        <tr v-if="clients.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No hay clientes registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Pagination -->
        <div v-if="clients.links && clients.links.length > 3" class="mt-4 flex items-center justify-center gap-1">
            <Link
                v-for="link in clients.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                :class="[
                    'px-3 py-1 text-sm rounded-lg border',
                    link.active ? 'bg-violet-600 text-white border-violet-600' : 'bg-surface-800 text-slate-400 border-slate-700/50 hover:bg-surface-700',
                    !link.url ? 'opacity-50 pointer-events-none' : '',
                ]"
                :preserve-state="true"
                :preserve-scroll="true"
            />
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="clienteAEliminar" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Confirmar eliminacion</h2>
            <p class="text-slate-400 mb-6">
                Eliminar a <strong class="text-slate-200">{{ clienteAEliminar.nombre }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="cancelDelete">Cancelar</Button>
                <Button variant="danger" @click="deleteClient">Eliminar</Button>
            </div>
        </div>
    </div>
</template>
