<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    payslip: { type: Object, required: true },
})

const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(Number(n))

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR')
}

function conceptoBadge(tipo) {
    if (tipo === 'haber_con_aporte') return 'bg-emerald-500/10 text-emerald-400'
    if (tipo === 'haber_sin_aporte') return 'bg-blue-500/10 text-blue-400'
    if (tipo === 'descuento') return 'bg-red-500/10 text-red-400'
    return 'bg-slate-700/30 text-slate-400'
}

function conceptoLabel(tipo) {
    if (tipo === 'haber_con_aporte') return 'Haber c/aporte'
    if (tipo === 'haber_sin_aporte') return 'Haber s/aporte'
    if (tipo === 'descuento') return 'Descuento'
    return tipo
}

function destroy() {
    if (!confirm('¿Eliminar este recibo?')) return
    router.delete(route('finance.payslips.destroy', props.payslip.id))
}

function periodoLabel(periodo) {
    const [y, m] = periodo.split('-')
    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
    return `${meses[parseInt(m) - 1]} ${y}`
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <Link :href="route('finance.payslips.index')" class="text-xs text-slate-400 hover:text-slate-200 transition-colors">← Recibos</Link>
                    <h1 class="text-xl font-semibold text-slate-100 mt-1">
                        {{ payslip.periodo_formateado ?? periodoLabel(payslip.periodo) }}
                    </h1>
                    <p class="text-sm text-slate-400">{{ payslip.empresa }}</p>
                </div>
                <button @click="destroy" class="text-xs text-slate-600 hover:text-red-400 transition-colors mt-1">
                    Eliminar recibo
                </button>
            </div>

            <!-- Meta info -->
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-surface-950 border border-slate-700/40 px-4 py-3">
                    <p class="text-[11px] text-slate-500 mb-0.5">Fecha de Pago</p>
                    <p class="text-sm text-slate-200">{{ fmtDate(payslip.fecha_pago) }}</p>
                </div>
                <div class="rounded-lg bg-surface-950 border border-slate-700/40 px-4 py-3">
                    <p class="text-[11px] text-slate-500 mb-0.5">Banco</p>
                    <p class="text-sm text-slate-200">{{ payslip.banco ?? '—' }}</p>
                </div>
            </div>

            <!-- Totals -->
            <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-5 space-y-3">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Resumen</p>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Sueldo Básico</span>
                        <span class="text-slate-200">{{ fmt(payslip.sueldo_basico) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Total Bruto (c/aporte)</span>
                        <span class="text-slate-200">{{ fmt(payslip.total_bruto) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Haberes s/aporte</span>
                        <span class="text-slate-200">{{ fmt(payslip.total_sin_aporte) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Total Descuentos</span>
                        <span class="text-red-400">-{{ fmt(payslip.total_descuentos) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold border-t border-slate-700 pt-2 mt-1">
                        <span class="text-slate-100">Total Neto</span>
                        <span class="text-emerald-400">{{ fmt(payslip.total_neto) }}</span>
                    </div>
                </div>
            </div>

            <!-- Conceptos -->
            <div v-if="payslip.conceptos?.length" class="rounded-xl bg-surface-950 border border-slate-700/40 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-700/40">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Detalle de Conceptos</p>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700/30">
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-600">Código</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-600">Descripción</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-600">Tipo</th>
                            <th class="text-right px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-600">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/20">
                        <tr v-for="(c, i) in payslip.conceptos" :key="i" class="hover:bg-slate-800/20">
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ c.codigo }}</td>
                            <td class="px-4 py-2.5 text-slate-300">{{ c.descripcion }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span :class="['text-[10px] px-2 py-0.5 rounded-full font-medium', conceptoBadge(c.tipo)]">
                                    {{ conceptoLabel(c.tipo) }}
                                </span>
                            </td>
                            <td :class="['px-4 py-2.5 text-right font-medium', c.tipo === 'descuento' ? 'text-red-400' : 'text-slate-200']">
                                {{ fmt(c.monto) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>
