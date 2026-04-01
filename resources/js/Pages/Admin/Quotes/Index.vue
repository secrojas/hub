<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

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

function formatMonto(value) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value)
}
</script>

<template>
    <Head title="Presupuestos" />

    <div>
        <PageHeader title="Presupuestos" subtitle="Gestion de presupuestos y cotizaciones">
            <Link :href="route('quotes.create')">
                <Button variant="primary">Nuevo Presupuesto</Button>
            </Link>
        </PageHeader>

        <!-- Quotes table -->
        <Card variant="default" padding="none">
            <div class="overflow-hidden rounded-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-800 border-b border-slate-700/40">
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Titulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="quote in quotes" :key="quote.id" class="border-b border-slate-700/20 hover:bg-surface-700/40 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ quote.client?.nombre ?? 'Sin cliente' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-100 font-medium">{{ quote.titulo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ quote.items?.length ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-100">{{ formatMonto(quote.total ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Badge :variant="quote.estado" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <!-- Edit always visible -->
                                <Link :href="route('quotes.edit', quote.id)" class="text-violet-400 hover:text-violet-300">Editar</Link>

                                <!-- Estado change select -->
                                <select
                                    :value="quote.estado"
                                    @change="changeEstado(quote, $event.target.value)"
                                    class="text-xs rounded-lg"
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
                                    class="text-red-400 hover:text-red-300"
                                >
                                    Eliminar
                                </button>

                                <!-- PDF download for non-borrador -->
                                <a
                                    v-if="quote.estado !== 'borrador'"
                                    :href="route('quotes.pdf', quote.id)"
                                    target="_blank"
                                    class="text-violet-400 hover:text-violet-300"
                                >
                                    Descargar PDF
                                </a>
                            </td>
                        </tr>
                        <tr v-if="!quotes || quotes.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No hay presupuestos.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="quoteAEliminar" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Confirmar eliminacion</h2>
            <p class="text-slate-400 mb-6">
                Eliminar el presupuesto <strong class="text-slate-200">{{ quoteAEliminar.titulo }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="cancelDelete">Cancelar</Button>
                <Button variant="danger" @click="deleteQuote">Eliminar</Button>
            </div>
        </div>
    </div>
</template>
