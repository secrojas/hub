<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    clients: Array,
})

const form = useForm({
    client_id: '',
    titulo:    '',
    notas:     '',
    items:     [{ descripcion: '', precio: '' }],
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

function formatMonto(value) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value)
}

function submit() {
    form.post(route('quotes.store'))
}
</script>

<template>
    <Head title="Nuevo Presupuesto" />
    <div class="max-w-3xl">
        <PageHeader title="Nuevo Presupuesto" subtitle="Crea un presupuesto para un cliente">
            <Link :href="route('quotes.index')">
                <Button variant="ghost" size="sm">Cancelar</Button>
            </Link>
        </PageHeader>

        <Card variant="default" padding="lg">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Client -->
                <div>
                    <label for="client_id" class="block text-sm font-medium text-slate-300 mb-1">Cliente <span class="text-red-400">*</span></label>
                    <select id="client_id" v-model="form.client_id" class="w-full">
                        <option value="">Seleccionar cliente...</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                    </select>
                    <p v-if="form.errors.client_id" class="text-red-400 text-sm mt-1">{{ form.errors.client_id }}</p>
                </div>

                <!-- Titulo -->
                <div>
                    <label for="titulo" class="block text-sm font-medium text-slate-300 mb-1">Titulo <span class="text-red-400">*</span></label>
                    <input id="titulo" v-model="form.titulo" type="text" class="w-full" />
                    <p v-if="form.errors.titulo" class="text-red-400 text-sm mt-1">{{ form.errors.titulo }}</p>
                </div>

                <!-- Notas -->
                <div>
                    <label for="notas" class="block text-sm font-medium text-slate-300 mb-1">Notas</label>
                    <textarea id="notas" v-model="form.notas" rows="3" class="w-full"></textarea>
                    <p v-if="form.errors.notas" class="text-red-400 text-sm mt-1">{{ form.errors.notas }}</p>
                </div>

                <!-- Items -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Items <span class="text-red-400">*</span></label>
                    <p v-if="form.errors.items" class="text-red-400 text-sm mb-2">{{ form.errors.items }}</p>

                    <div class="rounded-xl overflow-hidden border border-slate-700/50 mb-3">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-surface-800 border-b border-slate-700/40">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-400 uppercase">Descripcion</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-400 uppercase w-36">Precio</th>
                                    <th class="px-4 py-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in form.items" :key="index" class="border-b border-slate-700/20">
                                    <td class="px-4 py-2">
                                        <input
                                            v-model="item.descripcion"
                                            type="text"
                                            class="w-full text-sm"
                                            placeholder="Descripcion del item"
                                        />
                                        <p v-if="form.errors[`items.${index}.descripcion`]" class="text-xs text-red-400 mt-1">
                                            {{ form.errors[`items.${index}.descripcion`] }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input
                                            v-model="item.precio"
                                            type="text"
                                            class="w-full text-sm"
                                            placeholder="0.00"
                                        />
                                        <p v-if="form.errors[`items.${index}.precio`]" class="text-xs text-red-400 mt-1">
                                            {{ form.errors[`items.${index}.precio`] }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button
                                            v-if="form.items.length > 1"
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-400 hover:text-red-300 text-sm"
                                        >
                                            Quitar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button
                        type="button"
                        @click="addItem"
                        class="text-sm text-violet-400 hover:text-violet-300"
                    >
                        + Agregar item
                    </button>

                    <!-- Total -->
                    <div class="mt-3 text-right">
                        <span class="text-sm font-medium text-slate-400">Total: </span>
                        <span class="text-base font-semibold text-slate-100">{{ formatMonto(total) }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <Button type="submit" variant="primary" :disabled="form.processing">Guardar</Button>
                    <Link :href="route('quotes.index')" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</Link>
                </div>
            </form>
        </Card>
    </div>
</template>
