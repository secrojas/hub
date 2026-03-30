<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    quote:   Object,
    clients: Array,
})

const form = useForm({
    client_id: props.quote.client_id,
    titulo:    props.quote.titulo,
    notas:     props.quote.notas ?? '',
    items:     props.quote.items.map(i => ({ descripcion: i.descripcion, precio: i.precio })),
})

function addItem() {
    form.items.push({ descripcion: '', precio: '' })
}

function removeItem(index) {
    form.items.splice(index, 1)
}

const total = computed(() =>
    form.items.reduce((sum, item) => sum + (parseFloat(item.precio) || 0), 0)
)

const readOnlyTotal = computed(() =>
    (props.quote.items ?? []).reduce((sum, item) => sum + (parseFloat(item.precio) || 0), 0)
)

function formatMonto(value) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value)
}

function submit() {
    form.put(route('quotes.update', props.quote.id))
}

function changeEstado(quote, newEstado) {
    useForm({ estado: newEstado }).patch(route('quotes.updateEstado', quote.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Editar Presupuesto" />
    <div class="max-w-3xl">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Editar Presupuesto</h1>

        <!-- BORRADOR MODE: editable form -->
        <div v-if="quote.estado === 'borrador'">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Client -->
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                    <select id="client_id" v-model="form.client_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccionar cliente...</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                    </select>
                    <p v-if="form.errors.client_id" class="text-sm text-red-600 mt-1">{{ form.errors.client_id }}</p>
                </div>

                <!-- Titulo -->
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Titulo <span class="text-red-500">*</span></label>
                    <input id="titulo" v-model="form.titulo" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                    <p v-if="form.errors.titulo" class="text-sm text-red-600 mt-1">{{ form.errors.titulo }}</p>
                </div>

                <!-- Notas -->
                <div>
                    <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea id="notas" v-model="form.notas" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    <p v-if="form.errors.notas" class="text-sm text-red-600 mt-1">{{ form.errors.notas }}</p>
                </div>

                <!-- Items -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items <span class="text-red-500">*</span></label>
                    <p v-if="form.errors.items" class="text-sm text-red-600 mb-2">{{ form.errors.items }}</p>

                    <table class="w-full border border-gray-200 rounded-md overflow-hidden mb-3">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">Precio</th>
                                <th class="px-4 py-2 w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="px-4 py-2">
                                    <input
                                        v-model="item.descripcion"
                                        type="text"
                                        class="w-full border-gray-300 rounded text-sm"
                                        placeholder="Descripcion del item"
                                    />
                                    <p v-if="form.errors[`items.${index}.descripcion`]" class="text-xs text-red-600 mt-1">
                                        {{ form.errors[`items.${index}.descripcion`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-2">
                                    <input
                                        v-model="item.precio"
                                        type="text"
                                        class="w-full border-gray-300 rounded text-sm"
                                        placeholder="0.00"
                                    />
                                    <p v-if="form.errors[`items.${index}.precio`]" class="text-xs text-red-600 mt-1">
                                        {{ form.errors[`items.${index}.precio`] }}
                                    </p>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button
                                        v-if="form.items.length > 1"
                                        type="button"
                                        @click="removeItem(index)"
                                        class="text-red-500 hover:text-red-700 text-sm"
                                    >
                                        Quitar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button
                        type="button"
                        @click="addItem"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        + Agregar item
                    </button>

                    <!-- Total -->
                    <div class="mt-3 text-right">
                        <span class="text-sm font-medium text-gray-700">Total: </span>
                        <span class="text-base font-semibold text-gray-900">{{ formatMonto(total) }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        Guardar
                    </button>
                    <Link :href="route('quotes.index')" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</Link>
                </div>
            </form>
        </div>

        <!-- POST-BORRADOR MODE: read-only -->
        <div v-else class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Cliente</p>
                    <p class="text-sm text-gray-900">{{ quote.client?.nombre ?? 'Sin cliente' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Titulo</p>
                    <p class="text-sm font-medium text-gray-900">{{ quote.titulo }}</p>
                </div>
                <div v-if="quote.notas">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Notas</p>
                    <p class="text-sm text-gray-700">{{ quote.notas }}</p>
                </div>

                <!-- Read-only items table -->
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Items</p>
                    <table class="w-full border border-gray-200 rounded-md overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-36">Precio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in quote.items" :key="item.id">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ item.descripcion }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatMonto(item.precio) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 text-right">
                        <span class="text-sm font-medium text-gray-700">Total: </span>
                        <span class="text-base font-semibold text-gray-900">{{ formatMonto(readOnlyTotal) }}</span>
                    </div>
                </div>

                <!-- Estado change -->
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Estado</p>
                    <select
                        :value="quote.estado"
                        @change="changeEstado(quote, $event.target.value)"
                        class="border-gray-300 rounded text-sm"
                    >
                        <option value="borrador">Borrador</option>
                        <option value="enviado">Enviado</option>
                        <option value="aceptado">Aceptado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>

                <!-- PDF download -->
                <div>
                    <a
                        :href="route('quotes.pdf', quote.id)"
                        target="_blank"
                        class="inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium"
                    >
                        Descargar PDF
                    </a>
                </div>
            </div>

            <Link :href="route('quotes.index')" class="text-sm text-gray-600 hover:text-gray-900">Volver</Link>
        </div>
    </div>
</template>
