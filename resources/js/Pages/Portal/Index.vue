<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import { formatHoras } from '@/composables/useFormatHoras.js'

defineOptions({ layout: PortalLayout })

const props = defineProps({
    tasks: Array,
    quotes: Array,
    billings: Array,
    dashboard: Object,
    horasBilling: Object,
})

function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}

const taskLabels = { backlog: 'Backlog', en_progreso: 'En progreso', en_revision: 'En revision', finalizado: 'Finalizado' }
const quoteLabels = { borrador: 'Borrador', enviado: 'Enviado', aceptado: 'Aceptado', rechazado: 'Rechazado' }
const billingLabels = { pendiente: 'Pendiente', pagado: 'Pagado', vencido: 'Vencido' }

function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('es-AR')
}
</script>

<template>
    <Head title="Mi Portal" />

    <div class="space-y-8">

        <PageHeader title="Mi Portal" subtitle="Estado de tus proyectos, presupuestos y facturacion" />

        <!-- Summary cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Mis Tareas -->
            <Card variant="glass" padding="md">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Mis Tareas</h3>
                <dl class="space-y-2">
                    <div v-for="(label, status) in taskLabels" :key="status" class="flex items-center justify-between text-sm">
                        <dt class="text-slate-400">{{ label }}</dt>
                        <dd class="font-semibold text-slate-100">{{ dashboard.tareas[status] || 0 }}</dd>
                    </div>
                </dl>
            </Card>

            <!-- Mis Presupuestos -->
            <Card variant="glass" padding="md">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Mis Presupuestos</h3>
                <dl class="space-y-2">
                    <div v-for="(label, status) in quoteLabels" :key="status" class="flex items-center justify-between text-sm">
                        <dt class="text-slate-400">{{ label }}</dt>
                        <dd class="font-semibold text-slate-100">{{ dashboard.presupuestos[status] || 0 }}</dd>
                    </div>
                </dl>
            </Card>

            <!-- Mi Facturacion -->
            <Card variant="glass" padding="md">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Mi Facturacion</h3>
                <dl class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-slate-400">Pendiente</dt>
                        <dd class="font-semibold text-amber-400">{{ formatMonto(dashboard.facturacion.pendiente) }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-slate-400">Pagado</dt>
                        <dd class="font-semibold text-green-400">{{ formatMonto(dashboard.facturacion.pagado) }}</dd>
                    </div>
                </dl>
            </Card>

        </div>

        <!-- Tareas list -->
        <section>
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-500 inline-block"></span>
                Tareas
            </h2>
            <p v-if="tasks.length === 0" class="text-sm text-slate-500 px-1">No tenes tareas asignadas.</p>
            <Card v-else variant="default" padding="none">
                <!-- Header -->
                <div class="grid grid-cols-[1fr_140px_120px] px-4 py-2 border-b border-slate-700/40">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Titulo</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha Limite</span>
                </div>
                <!-- Rows -->
                <Link
                    v-for="task in tasks"
                    :key="task.id"
                    :href="route('portal.tasks.show', task.id)"
                    class="grid grid-cols-[1fr_140px_120px] px-4 py-3 border-b border-slate-700/40 last:border-0 hover:bg-surface-700/40 transition-colors duration-150 group"
                >
                    <span class="text-sm text-slate-100 truncate pr-4 group-hover:text-cyan-400 transition-colors">{{ task.titulo }}</span>
                    <span>
                        <Badge :variant="task.estado" />
                    </span>
                    <span class="text-sm text-slate-400">{{ formatDate(task.fecha_limite) }}</span>
                </Link>
            </Card>
        </section>

        <!-- Presupuestos list -->
        <section>
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-violet-500 inline-block"></span>
                Presupuestos
            </h2>
            <p v-if="quotes.length === 0" class="text-sm text-slate-500 px-1">No tenes presupuestos.</p>
            <Card v-else variant="default" padding="none">
                <!-- Header -->
                <div class="grid grid-cols-[1fr_140px_140px_80px] px-4 py-2 border-b border-slate-700/40">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Titulo</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">PDF</span>
                </div>
                <!-- Rows -->
                <div
                    v-for="quote in quotes"
                    :key="quote.id"
                    class="grid grid-cols-[1fr_140px_140px_80px] px-4 py-3 border-b border-slate-700/40 last:border-0 hover:bg-surface-700/40 transition-colors duration-150"
                >
                    <span class="text-sm text-slate-100 truncate pr-4">{{ quote.titulo }}</span>
                    <span>
                        <Badge :variant="quote.estado" />
                    </span>
                    <span class="text-sm text-slate-100">{{ formatMonto(quote.total) }}</span>
                    <span class="text-sm">
                        <a
                            :href="'/portal/quotes/' + quote.id + '/pdf'"
                            class="text-cyan-400 hover:text-cyan-300 underline underline-offset-2 transition-colors"
                        >PDF</a>
                    </span>
                </div>
            </Card>
        </section>

        <!-- Horas trabajadas -->
        <section v-if="horasBilling?.tareas?.length || horasBilling?.total_mensual > 0">
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                Horas Trabajadas
            </h2>

            <!-- Totales semanal / mensual -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <Card variant="glass" padding="md">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Esta semana</p>
                    <p class="text-2xl font-semibold text-cyan-400">{{ formatMonto(horasBilling.total_semanal) }}</p>
                </Card>
                <Card variant="glass" padding="md">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Este mes</p>
                    <p class="text-2xl font-semibold text-violet-400">{{ formatMonto(horasBilling.total_mensual) }}</p>
                </Card>
            </div>

            <!-- Tabla de tareas finalizadas -->
            <Card v-if="horasBilling.tareas?.length" variant="default" padding="none">
                <div class="grid grid-cols-[1fr_120px_80px_140px] px-4 py-2 border-b border-slate-700/40">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tarea</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Finalizada</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider text-right">Horas</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider text-right">Monto</span>
                </div>
                <div
                    v-for="t in horasBilling.tareas"
                    :key="t.id"
                    class="grid grid-cols-[1fr_120px_80px_140px] px-4 py-3 border-b border-slate-700/40 last:border-0 hover:bg-surface-700/40 transition-colors duration-150"
                >
                    <span class="text-sm text-slate-100 truncate pr-4">{{ t.titulo }}</span>
                    <span class="text-sm text-slate-400">{{ formatDate(t.fecha_finalizacion) }}</span>
                    <span class="text-sm text-slate-100 text-right">{{ formatHoras(t.horas) }}</span>
                    <span class="text-sm font-medium text-green-400 text-right">
                        {{ horasBilling.valor_hora ? formatMonto(t.monto) : '—' }}
                    </span>
                </div>
            </Card>
        </section>

        <!-- Facturacion list -->
        <section>
            <h2 class="text-base font-semibold text-slate-100 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                Facturacion
            </h2>
            <p v-if="billings.length === 0" class="text-sm text-slate-500 px-1">No tenes cobros registrados.</p>
            <Card v-else variant="default" padding="none">
                <!-- Header -->
                <div class="grid grid-cols-[1fr_160px_140px_120px] px-4 py-2 border-b border-slate-700/40">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Concepto</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Monto</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha Emision</span>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</span>
                </div>
                <!-- Rows -->
                <Link
                    v-for="billing in billings"
                    :key="billing.id"
                    :href="route('portal.billing.show', billing.id)"
                    class="grid grid-cols-[1fr_160px_140px_120px] px-4 py-3 border-b border-slate-700/40 last:border-0 hover:bg-surface-700/40 transition-colors duration-150 group"
                >
                    <span class="text-sm text-slate-100 truncate pr-4 group-hover:text-cyan-400 transition-colors">{{ billing.concepto }}</span>
                    <span class="text-sm text-slate-100">{{ formatMonto(billing.monto) }}</span>
                    <span class="text-sm text-slate-400">{{ formatDate(billing.fecha_emision) }}</span>
                    <span>
                        <Badge :variant="billing.estado" />
                    </span>
                </Link>
            </Card>
        </section>

    </div>
</template>
