<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

defineProps({
    client: Object,
    hasActiveUser: Boolean,
})

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return dateStr.substring(0, 10)
}

function estadoBadgeClass(estado) {
    if (estado === 'activo') return 'bg-green-100 text-green-800'
    if (estado === 'potencial') return 'bg-blue-100 text-blue-800'
    return 'bg-gray-100 text-gray-700'
}
</script>

<template>
    <Head :title="client.nombre" />

    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ client.nombre }}</h1>
            <div class="flex gap-3">
                <Link :href="`/clients/${client.id}/edit`" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Editar
                </Link>
                <Link href="/clients" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium">
                    Volver
                </Link>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ client.nombre }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ client.email }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Empresa</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ client.empresa || '-' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Telefono</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ client.telefono || '-' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Stack Tecnologico</dt>
                <dd class="col-span-2 text-sm text-gray-900 whitespace-pre-wrap">{{ client.stack_tecnologico || '-' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                <dd class="col-span-2">
                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', estadoBadgeClass(client.estado)]">
                        {{ client.estado }}
                    </span>
                </dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Notas</dt>
                <dd class="col-span-2 text-sm text-gray-900 whitespace-pre-wrap">{{ client.notas || '-' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ formatDate(client.fecha_inicio) }}</dd>
            </div>
        </div>
    </div>
</template>
