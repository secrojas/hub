<script setup>
import { Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'

defineOptions({ layout: PortalLayout })

defineProps({
    billing: Object,
})

function formatMonto(monto) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(monto)
}

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR', { day: '2-digit', month: 'long', year: 'numeric' })
}

const estadoConfig = {
    pendiente: { label: 'Pendiente', color: 'text-amber-400', bg: 'bg-amber-400/10 border-amber-400/20' },
    pagado:    { label: 'Pagado',    color: 'text-green-400', bg: 'bg-green-400/10 border-green-400/20' },
    vencido:   { label: 'Vencido',   color: 'text-red-400',   bg: 'bg-red-400/10 border-red-400/20' },
}
</script>

<template>
    <div class="max-w-lg mx-auto flex flex-col gap-6">
        <!-- Back -->
        <Link href="/portal" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-100 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al portal
        </Link>

        <!-- Invoice card -->
        <Card variant="glass" padding="lg">
            <!-- Header row -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Comprobante</p>
                    <p class="text-slate-600 text-xs mt-0.5">#{{ String(billing.id).padStart(5, '0') }}</p>
                </div>
                <div
                    class="px-3 py-1 rounded-full border text-sm font-semibold"
                    :class="estadoConfig[billing.estado]?.bg"
                >
                    <span :class="estadoConfig[billing.estado]?.color">
                        {{ estadoConfig[billing.estado]?.label ?? billing.estado }}
                    </span>
                </div>
            </div>

            <!-- Concepto -->
            <div class="mb-6">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-medium mb-1">Concepto</p>
                <p class="text-lg font-semibold text-slate-100 leading-snug">{{ billing.concepto }}</p>
            </div>

            <!-- Amount -->
            <div class="bg-surface-800 rounded-xl px-5 py-4 mb-6">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-medium mb-1">Monto</p>
                <p class="text-3xl font-bold text-slate-100">{{ formatMonto(billing.monto) }}</p>
            </div>

            <!-- Dates -->
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium mb-1">Fecha de emisión</dt>
                    <dd class="text-sm text-slate-300">{{ formatDate(billing.fecha_emision) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wider font-medium mb-1">Fecha de pago</dt>
                    <dd class="text-sm" :class="billing.fecha_pago ? 'text-green-400' : 'text-slate-500'">
                        {{ formatDate(billing.fecha_pago) }}
                    </dd>
                </div>
            </dl>

            <!-- Nota aclaratoria -->
            <p class="mt-6 pt-4 border-t border-slate-700/40 text-xs text-slate-600 text-center">
                Este comprobante es informativo y no tiene validez fiscal.
            </p>
        </Card>
    </div>
</template>
