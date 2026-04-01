<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

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
        <PageHeader title="Facturacion" subtitle="Control de cobros y facturacion">
            <Link href="/billing/create">
                <Button variant="primary">Nuevo Cobro</Button>
            </Link>
        </PageHeader>

        <!-- Summary cards -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-surface-800 border border-green-500/20 rounded-xl p-4 border-l-4 border-l-green-500">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Cobrado este mes</p>
                <p class="text-xl font-semibold text-green-400">{{ formatMonto(summary?.cobrado_mes ?? 0) }}</p>
            </div>
            <div class="bg-surface-800 border border-amber-500/20 rounded-xl p-4 border-l-4 border-l-amber-500">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Deuda pendiente</p>
                <p class="text-xl font-semibold text-amber-400">{{ formatMonto(summary?.pendiente_total ?? 0) }}</p>
            </div>
            <div class="bg-surface-800 border border-red-500/20 rounded-xl p-4 border-l-4 border-l-red-500">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Cobros vencidos</p>
                <p class="text-xl font-semibold text-red-400">{{ summary?.vencidos_count ?? 0 }}</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="glass rounded-xl px-4 py-3 mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Estado</label>
                <select v-model="filters.estado" @change="applyFilters" class="text-sm rounded-lg">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Cliente</label>
                <select v-model="filters.cliente" @change="applyFilters" class="text-sm rounded-lg">
                    <option value="">Todos</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
            </div>
        </div>

        <!-- Billing table -->
        <Card variant="default" padding="none">
            <div class="overflow-hidden rounded-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-800 border-b border-slate-700/40">
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Concepto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha Emision</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="billing in billings.data" :key="billing.id" class="border-b border-slate-700/20 hover:bg-surface-700/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-100 font-medium">{{ billing.concepto }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ billing.client?.nombre ?? 'Sin cliente' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-100">{{ formatMonto(billing.monto) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ formatDate(billing.fecha_emision) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Badge :variant="billing.estado" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-3">
                                <Link :href="`/billing/${billing.id}/edit`" class="text-violet-400 hover:text-violet-300">Editar</Link>
                                <button @click="confirmDelete(billing)" class="text-red-400 hover:text-red-300">Eliminar</button>
                            </td>
                        </tr>
                        <tr v-if="billings.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No hay registros de facturacion.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Pagination -->
        <div v-if="billings.links && billings.links.length > 3" class="mt-4 flex items-center justify-center gap-1">
            <Link
                v-for="link in billings.links"
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
    <div v-if="billingAEliminar" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Confirmar eliminacion</h2>
            <p class="text-slate-400 mb-6">
                Eliminar el cobro <strong class="text-slate-200">{{ billingAEliminar.concepto }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="cancelDelete">Cancelar</Button>
                <Button variant="danger" @click="deleteBilling">Eliminar</Button>
            </div>
        </div>
    </div>
</template>
