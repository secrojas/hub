<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    client: Object,
})

const form = useForm({
    nombre: props.client.nombre || '',
    email: props.client.email || '',
    empresa: props.client.empresa || '',
    telefono: props.client.telefono || '',
    stack_tecnologico: props.client.stack_tecnologico || '',
    estado: props.client.estado || 'activo',
    notas: props.client.notas || '',
    fecha_inicio: props.client.fecha_inicio?.substring(0, 10) || '',
})

function submit() {
    form.put(`/clients/${props.client.id}`, { preserveScroll: true })
}
</script>

<template>
    <Head title="Editar Cliente" />
    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Editar Cliente</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input id="nombre" v-model="form.nombre" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.nombre" class="text-sm text-red-600 mt-1">{{ form.errors.nombre }}</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input id="email" v-model="form.email" type="email" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="empresa" class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <input id="empresa" v-model="form.empresa" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.empresa" class="text-sm text-red-600 mt-1">{{ form.errors.empresa }}</p>
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                <input id="telefono" v-model="form.telefono" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.telefono" class="text-sm text-red-600 mt-1">{{ form.errors.telefono }}</p>
            </div>

            <div>
                <label for="stack_tecnologico" class="block text-sm font-medium text-gray-700 mb-1">Stack Tecnologico</label>
                <textarea id="stack_tecnologico" v-model="form.stack_tecnologico" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                <p v-if="form.errors.stack_tecnologico" class="text-sm text-red-600 mt-1">{{ form.errors.stack_tecnologico }}</p>
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="estado" v-model="form.estado" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="activo">Activo</option>
                    <option value="potencial">Potencial</option>
                    <option value="pausado">Pausado</option>
                </select>
                <p v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</p>
            </div>

            <div>
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea id="notas" v-model="form.notas" rows="4" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                <p v-if="form.errors.notas" class="text-sm text-red-600 mt-1">{{ form.errors.notas }}</p>
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                <input id="fecha_inicio" v-model="form.fecha_inicio" type="date" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.fecha_inicio" class="text-sm text-red-600 mt-1">{{ form.errors.fecha_inicio }}</p>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    Guardar
                </button>
                <Link :href="`/clients/${props.client.id}`" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</Link>
            </div>
        </form>
    </div>
</template>
