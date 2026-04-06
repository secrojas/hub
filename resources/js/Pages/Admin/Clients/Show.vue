<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    client: Object,
    hasActiveUser: Boolean,
    billings: Array,
    horasBilling: Object,
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

function formatARS(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}
</script>

<template>
    <Head :title="client.nombre" />

    <div class="max-w-2xl space-y-6">
        <PageHeader :title="client.nombre">
            <Link :href="`/tasks?cliente=${client.id}`">
                <Button variant="secondary" size="sm">Ver Kanban</Button>
            </Link>
            <Link :href="`/clients/${client.id}/edit`">
                <Button variant="primary" size="sm">Editar</Button>
            </Link>
            <Link href="/clients">
                <Button variant="ghost" size="sm">Volver</Button>
            </Link>
        </PageHeader>

        <!-- Client details -->
        <Card variant="default" padding="none">
            <dl class="divide-y divide-slate-700/30">
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Nombre</dt>
                    <dd class="col-span-2 text-sm text-slate-100">{{ client.nombre }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Email</dt>
                    <dd class="col-span-2 text-sm text-slate-100">{{ client.email }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Empresa</dt>
                    <dd class="col-span-2 text-sm text-slate-100">{{ client.empresa || '-' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Telefono</dt>
                    <dd class="col-span-2 text-sm text-slate-100">{{ client.telefono || '-' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Stack Tecnologico</dt>
                    <dd class="col-span-2 text-sm text-slate-100 whitespace-pre-wrap">{{ client.stack_tecnologico || '-' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Estado</dt>
                    <dd class="col-span-2">
                        <Badge :variant="client.estado" />
                    </dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Notas</dt>
                    <dd class="col-span-2 text-sm text-slate-100 whitespace-pre-wrap">{{ client.notas || '-' }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                    <dt class="text-sm font-medium text-slate-400">Fecha de Inicio</dt>
                    <dd class="col-span-2 text-sm text-slate-100">{{ formatDate(client.fecha_inicio) }}</dd>
                </div>
            </dl>
        </Card>

        <!-- Billing Section -->
        <Card variant="default" padding="none">
            <div class="px-6 py-4 border-b border-slate-700/40">
                <h2 class="text-base font-semibold text-slate-100">Facturacion</h2>
            </div>
            <div v-if="billings && billings.length">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-800 border-b border-slate-700/40">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Concepto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Monto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Fecha Emision</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="billing in billings" :key="billing.id" class="border-b border-slate-700/20 hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 text-slate-100 font-medium">{{ billing.concepto }}</td>
                            <td class="px-4 py-3 text-slate-100">{{ formatARS(billing.monto) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(billing.fecha_emision) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="billing.estado" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="px-6 py-8 text-center text-slate-500">No hay cobros registrados para este cliente.</p>
        </Card>

        <!-- Facturación por Tareas -->
        <Card variant="default" padding="none">
            <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-100">Facturacion por Tareas</h2>
                <span class="text-xs text-slate-500">
                    Valor hora:
                    <span class="text-slate-300 font-medium">
                        {{ horasBilling.valor_hora ? formatARS(horasBilling.valor_hora) + '/h' : 'No configurado' }}
                    </span>
                </span>
            </div>

            <!-- Totales semanal / mensual -->
            <div class="grid grid-cols-2 divide-x divide-slate-700/30 border-b border-slate-700/40">
                <div class="px-6 py-4">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Esta semana</p>
                    <p class="text-xl font-semibold text-cyan-400">{{ formatARS(horasBilling.total_semanal) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Este mes</p>
                    <p class="text-xl font-semibold text-violet-400">{{ formatARS(horasBilling.total_mensual) }}</p>
                </div>
            </div>

            <!-- Tabla de tareas finalizadas con horas -->
            <div v-if="horasBilling.tareas?.length">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-800 border-b border-slate-700/40">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Tarea</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Finalizada</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Horas</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in horasBilling.tareas" :key="t.id" class="border-b border-slate-700/20 hover:bg-surface-700/40 transition-colors">
                            <td class="px-4 py-3 text-slate-100">{{ t.titulo }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ formatDate(t.fecha_finalizacion) }}</td>
                            <td class="px-4 py-3 text-slate-100 text-right">{{ t.horas }}h</td>
                            <td class="px-4 py-3 text-green-400 text-right font-medium">
                                {{ horasBilling.valor_hora ? formatARS(t.monto) : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="px-6 py-8 text-center text-slate-500">No hay tareas finalizadas con horas registradas.</p>
        </Card>

        <!-- Portal Invitation Section -->
        <Card variant="default" padding="md">
            <h2 class="text-base font-semibold text-slate-100 mb-3">Acceso al portal</h2>

            <p v-if="hasActiveUser" class="text-sm text-amber-400">
                Este cliente ya tiene una cuenta activa.
            </p>

            <template v-else>
                <p v-if="inviteForm.errors.email" class="text-sm text-red-400 mb-3">
                    {{ inviteForm.errors.email }}
                </p>
                <Button
                    variant="primary"
                    :disabled="inviteForm.processing"
                    @click="invitar"
                >
                    Invitar al portal
                </Button>
            </template>

            <div v-if="invitationUrl" class="mt-4">
                <p class="text-sm font-medium text-green-400 mb-2">Enlace de invitacion generado:</p>
                <input
                    type="text"
                    :value="invitationUrl"
                    readonly
                    class="w-full text-sm rounded-lg px-3 py-2 cursor-pointer font-mono"
                    @click="$event.target.select()"
                />
            </div>
        </Card>
    </div>
</template>
