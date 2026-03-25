<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    clients: Array,
})

const form = useForm({
    client_id:     '',
    concepto:      '',
    monto:         '',
    fecha_emision: '',
    fecha_pago:    null,
    estado:        'pendiente',
})

function submit() {
    form.post('/billing')
}
</script>

<template>
    <Head title="Nuevo Cobro" />
    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Nuevo Cobro</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                <select id="client_id" v-model="form.client_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccionar cliente...</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                </select>
                <p v-if="form.errors.client_id" class="text-sm text-red-600 mt-1">{{ form.errors.client_id }}</p>
            </div>

            <div>
                <label for="concepto" class="block text-sm font-medium text-gray-700 mb-1">Concepto <span class="text-red-500">*</span></label>
                <input id="concepto" v-model="form.concepto" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.concepto" class="text-sm text-red-600 mt-1">{{ form.errors.concepto }}</p>
            </div>

            <div>
                <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto <span class="text-red-500">*</span></label>
                <input id="monto" v-model="form.monto" type="text" placeholder="1500.50" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.monto" class="text-sm text-red-600 mt-1">{{ form.errors.monto }}</p>
            </div>

            <div>
                <label for="fecha_emision" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Emision <span class="text-red-500">*</span></label>
                <input id="fecha_emision" v-model="form.fecha_emision" type="date" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.fecha_emision" class="text-sm text-red-600 mt-1">{{ form.errors.fecha_emision }}</p>
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                <select id="estado" v-model="form.estado" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="vencido">Vencido</option>
                </select>
                <p v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</p>
            </div>

            <div>
                <label for="fecha_pago" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Pago</label>
                <input id="fecha_pago" v-model="form.fecha_pago" type="date" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.fecha_pago" class="text-sm text-red-600 mt-1">{{ form.errors.fecha_pago }}</p>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    Guardar
                </button>
                <Link href="/billing" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</Link>
            </div>
        </form>
    </div>
</template>
