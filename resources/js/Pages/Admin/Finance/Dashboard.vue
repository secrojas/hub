<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    accounts:         { type: Array,  default: () => [] },
    total_saldo:      { type: Number, default: 0 },
    last_payslip:     { type: Object, default: null },
    fixed_expenses:   { type: Array,  default: () => [] },
    total_fijos:      { type: Number, default: 0 },
    variable_expenses:{ type: Array,  default: () => [] },
    total_variables:  { type: Number, default: 0 },
    saldo_disponible: { type: Number, default: 0 },
    mes_actual:       { type: String, default: '' },
})

const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(n)

const accountColorClasses = {
    red:   { bg: 'bg-red-500/10',   text: 'text-red-400',   border: 'border-red-500/20' },
    blue:  { bg: 'bg-blue-500/10',  text: 'text-blue-400',  border: 'border-blue-500/20' },
    amber: { bg: 'bg-amber-500/10', text: 'text-amber-400', border: 'border-amber-500/20' },
    sky:   { bg: 'bg-sky-500/10',   text: 'text-sky-400',   border: 'border-sky-500/20' },
    slate: { bg: 'bg-slate-700/30', text: 'text-slate-300', border: 'border-slate-600/20' },
}

function colorClass(color, type) {
    return (accountColorClasses[color] ?? accountColorClasses.slate)[type]
}

const mesLabel = computed(() => {
    if (!props.mes_actual) return ''
    const [y, m] = props.mes_actual.split('-')
    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
    return `${meses[parseInt(m) - 1]} ${y}`
})

const saldoColor = computed(() => {
    if (props.saldo_disponible > props.total_fijos) return 'text-emerald-400'
    if (props.saldo_disponible > 0) return 'text-yellow-400'
    return 'text-red-400'
})
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-100">Finanzas Personales</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ mesLabel }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('finance.payslips.create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Subir Recibo
                    </Link>
                </div>
            </div>

            <!-- Account cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div v-for="account in accounts" :key="account.id"
                    :class="['rounded-xl border p-4 space-y-2', colorClass(account.color, 'bg'), colorClass(account.color, 'border')]">
                    <p :class="['text-[11px] font-semibold uppercase tracking-widest', colorClass(account.color, 'text')]">
                        {{ account.nombre }}
                    </p>
                    <p class="text-lg font-bold text-slate-100">
                        {{ fmt(account.latest_balance?.monto ?? 0) }}
                    </p>
                    <p class="text-[11px] text-slate-500">{{ account.tipo_label ?? account.tipo.replace('_', ' ') }}</p>
                </div>

                <div v-if="accounts.length === 0"
                    class="col-span-4 rounded-xl border border-dashed border-slate-700 p-6 text-center text-sm text-slate-500">
                    Sin cuentas configuradas.
                    <Link :href="route('finance.accounts.index')" class="ml-1 text-emerald-400 hover:underline">Agregar cuentas</Link>
                </div>
            </div>

            <!-- Saldo disponible -->
            <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-6 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1">Saldo Disponible</p>
                <p :class="['text-4xl font-bold', saldoColor]">{{ fmt(saldo_disponible) }}</p>
                <p class="text-xs text-slate-500 mt-2">
                    {{ fmt(total_saldo) }} total en cuentas — {{ fmt(total_variables) }} gastado este mes
                </p>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Ingreso neto -->
                <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 mb-3">Último Ingreso Neto</p>
                    <template v-if="last_payslip">
                        <p class="text-2xl font-bold text-emerald-400">{{ fmt(last_payslip.total_neto) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ last_payslip.periodo_formateado ?? last_payslip.periodo }} · {{ last_payslip.empresa }}</p>
                    </template>
                    <template v-else>
                        <p class="text-sm text-slate-500">Sin recibos cargados</p>
                    </template>
                </div>

                <!-- Gastos fijos -->
                <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 mb-3">Gastos Fijos / Mes</p>
                    <p class="text-2xl font-bold text-orange-400">{{ fmt(total_fijos) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ fixed_expenses.filter(e => e.activo).length }} compromisos activos</p>
                </div>

                <!-- Gastos variables del mes -->
                <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 mb-3">Gastos Variables ({{ mesLabel }})</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ fmt(total_variables) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ variable_expenses.length }} gastos registrados</p>
                </div>
            </div>

            <!-- Quick links -->
            <div class="flex flex-wrap gap-3">
                <Link :href="route('finance.accounts.index')"
                    class="text-sm text-slate-400 hover:text-emerald-300 transition-colors">
                    → Gestionar cuentas
                </Link>
                <Link :href="route('finance.expenses.index')"
                    class="text-sm text-slate-400 hover:text-emerald-300 transition-colors">
                    → Ver gastos
                </Link>
                <Link :href="route('finance.payslips.index')"
                    class="text-sm text-slate-400 hover:text-emerald-300 transition-colors">
                    → Historial de recibos
                </Link>
            </div>

        </div>
    </AdminLayout>
</template>
