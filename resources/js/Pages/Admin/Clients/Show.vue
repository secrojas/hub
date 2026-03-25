<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    client: Object,
    hasActiveUser: Boolean,
    billings: Array,
})

const page = usePage()
const invitationUrl = computed(() => page.props.flash?.invitation_url)

const inviteForm = useForm({
    email: props.client.email,
    client_name: props.client.nombre,
    client_id: props.client.id,
})

function invitar() {
    inviteForm.post('/invitations', { preserveScroll: true })
}

function formatDate(dateStr) {
    if (!dateStr) return '-'
    return dateStr.substring(0, 10)
}

function estadoBadgeClass(estado) {
    if (estado === 'activo') return 'bg-green-100 text-green-800'
    if (estado === 'potencial') return 'bg-blue-100 text-blue-800'
    return 'bg-gray-100 text-gray-700'
}

function billingBadgeClass(estado) {
    if (estado === 'pagado')  return 'bg-green-100 text-green-800'
    if (estado === 'vencido') return 'bg-red-100 text-red-800'
    return 'bg-yellow-100 text-yellow-800'
}

function formatARS(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}
</script>

<template>
    <Head :title="client.nombre" />

    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ client.nombre }}</h1>
            <div class="flex gap-3">
                <Link
                    :href="`/tasks?cliente=${client.id}`"
                    class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium"
                >
                    Ver Kanban
                </Link>
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

        <!-- Billing Section -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Facturación</h2>
            <div v-if="billings && billings.length">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Emisión</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="billing in billings" :key="billing.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ billing.concepto }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatARS(billing.monto) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(billing.fecha_emision) }}</td>
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', billingBadgeClass(billing.estado)]">
                                    {{ billing.estado }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm text-gray-500">No hay cobros registrados para este cliente.</p>
        </div>

        <!-- Portal Invitation Section -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Acceso al portal</h2>

            <p v-if="hasActiveUser" class="text-sm text-amber-600">
                Este cliente ya tiene una cuenta activa.
            </p>

            <template v-else>
                <p v-if="inviteForm.errors.email" class="text-sm text-red-600 mb-3">
                    {{ inviteForm.errors.email }}
                </p>
                <button
                    @click="invitar"
                    :disabled="inviteForm.processing"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-medium disabled:opacity-50"
                >
                    Invitar al portal
                </button>
            </template>

            <div v-if="invitationUrl" class="mt-4">
                <p class="text-sm font-medium text-green-700 mb-2">Enlace de invitacion generado:</p>
                <input
                    type="text"
                    :value="invitationUrl"
                    readonly
                    class="w-full text-sm border border-green-300 rounded px-3 py-2 bg-green-50 text-gray-800 cursor-pointer"
                    @click="$event.target.select()"
                />
            </div>
        </div>
    </div>
</template>
