<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    payslips: { type: Array, default: () => [] },
})

const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(n)

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR')
}

function destroy(id) {
    if (!confirm('¿Eliminar este recibo?')) return
    router.delete(route('finance.payslips.destroy', id))
}

function periodoLabel(periodo) {
    const [y, m] = periodo.split('-')
    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
    return `${meses[parseInt(m) - 1]} ${y}`
}
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-100">Recibos de Sueldo</h1>
                    <p class="text-sm text-slate-400 mt-0.5">Historial de haberes</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('finance.dashboard')" class="text-xs text-slate-400 hover:text-slate-200 transition-colors">← Dashboard</Link>
                    <Link :href="route('finance.payslips.create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Subir Recibo
                    </Link>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="payslips.length === 0"
                class="rounded-xl border border-dashed border-slate-700 p-12 text-center">
                <p class="text-slate-400 text-sm mb-3">Todavía no hay recibos cargados.</p>
                <Link :href="route('finance.payslips.create')"
                    class="px-4 py-2 text-sm rounded-lg bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                    Subir primer recibo
                </Link>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto rounded-xl border border-slate-700/40">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700/40 bg-surface-950">
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Período</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Empresa</th>
                            <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Bruto</th>
                            <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Neto</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Fecha Pago</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/30">
                        <tr v-for="p in payslips" :key="p.id"
                            class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-200">
                                <Link :href="route('finance.payslips.show', p.id)" class="hover:text-emerald-300 transition-colors">
                                    {{ p.periodo_formateado ?? periodoLabel(p.periodo) }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ p.empresa }}</td>
                            <td class="px-4 py-3 text-right text-slate-300">{{ fmt(p.total_bruto) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-400">{{ fmt(p.total_neto) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ fmtDate(p.fecha_pago) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="destroy(p.id)"
                                    class="text-xs text-slate-600 hover:text-red-400 transition-colors">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AdminLayout>
</template>
