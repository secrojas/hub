<script setup>
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    accounts: { type: Array, default: () => [] },
})

const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(Number(n ?? 0))

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR')
}

// New account form
const showNewForm = ref(false)
const newAccount = useForm({
    nombre: '',
    tipo:   'caja_ahorro',
    color:  'slate',
    orden:  0,
})

function createAccount() {
    newAccount.post(route('finance.accounts.store'), {
        onSuccess: () => { newAccount.reset(); showNewForm.value = false },
    })
}

// Balance modal
const balanceModal = reactive({ open: false, accountId: null, accountName: '' })
const balanceForm = useForm({ monto: '', fecha: new Date().toISOString().slice(0, 10), nota: '' })

function openBalanceModal(account) {
    balanceModal.open      = true
    balanceModal.accountId = account.id
    balanceModal.accountName = account.nombre
    balanceForm.monto = ''
    balanceForm.nota  = ''
    balanceForm.fecha = new Date().toISOString().slice(0, 10)
}

function submitBalance() {
    balanceForm.post(route('finance.accounts.balances.store', balanceModal.accountId), {
        onSuccess: () => { balanceModal.open = false; balanceForm.reset() },
    })
}

function destroyAccount(id) {
    if (!confirm('¿Eliminar esta cuenta?')) return
    router.delete(route('finance.accounts.destroy', id))
}

const colorOptions = [
    { value: 'red',   label: 'Rojo'    },
    { value: 'blue',  label: 'Azul'    },
    { value: 'amber', label: 'Amarillo'},
    { value: 'sky',   label: 'Celeste' },
    { value: 'emerald', label: 'Verde' },
    { value: 'slate', label: 'Gris'    },
]

const colorDot = {
    red:     'bg-red-400',
    blue:    'bg-blue-400',
    amber:   'bg-amber-400',
    sky:     'bg-sky-400',
    emerald: 'bg-emerald-400',
    slate:   'bg-slate-400',
}

const colorBg = {
    red:     'bg-red-500/10 border-red-500/20',
    blue:    'bg-blue-500/10 border-blue-500/20',
    amber:   'bg-amber-500/10 border-amber-500/20',
    sky:     'bg-sky-500/10 border-sky-500/20',
    emerald: 'bg-emerald-500/10 border-emerald-500/20',
    slate:   'bg-slate-700/30 border-slate-600/20',
}

const colorText = {
    red:     'text-red-400',
    blue:    'text-blue-400',
    amber:   'text-amber-400',
    sky:     'text-sky-400',
    emerald: 'text-emerald-400',
    slate:   'text-slate-300',
}
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link :href="route('finance.dashboard')" class="text-xs text-slate-400 hover:text-slate-200 transition-colors">← Finanzas</Link>
                    </div>
                    <h1 class="text-xl font-semibold text-slate-100">Cuentas Bancarias</h1>
                </div>
                <button @click="showNewForm = !showNewForm"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nueva Cuenta
                </button>
            </div>

            <!-- New account form -->
            <div v-if="showNewForm" class="rounded-xl bg-surface-950 border border-emerald-500/20 p-5 space-y-4">
                <p class="text-sm font-medium text-slate-300">Nueva cuenta</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                        <input v-model="newAccount.nombre" type="text" placeholder="Santander"
                            class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                        <select v-model="newAccount.tipo"
                            class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60">
                            <option value="caja_ahorro">Caja de Ahorro</option>
                            <option value="cuenta_corriente">Cuenta Corriente</option>
                            <option value="billetera_digital">Billetera Digital</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Color</label>
                        <select v-model="newAccount.color"
                            class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60">
                            <option v-for="c in colorOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Orden</label>
                        <input v-model="newAccount.orden" type="number" min="0"
                            class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="createAccount" :disabled="newAccount.processing"
                        class="px-4 py-2 text-sm rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-colors disabled:opacity-50">
                        Crear Cuenta
                    </button>
                    <button @click="showNewForm = false" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Accounts list -->
            <div class="space-y-4">
                <div v-for="account in accounts" :key="account.id"
                    :class="['rounded-xl border p-5 space-y-4', colorBg[account.color] ?? colorBg.slate]">

                    <!-- Account header -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span :class="['w-2.5 h-2.5 rounded-full', colorDot[account.color] ?? colorDot.slate]"></span>
                                <h3 :class="['font-semibold', colorText[account.color] ?? colorText.slate]">{{ account.nombre }}</h3>
                                <span class="text-xs text-slate-500">{{ account.tipo_label ?? account.tipo }}</span>
                            </div>
                            <p class="text-2xl font-bold text-slate-100 mt-2">
                                {{ fmt(account.latest_balance?.monto ?? 0) }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Actualizado: {{ fmtDate(account.latest_balance?.fecha) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="openBalanceModal(account)"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                                + Actualizar Saldo
                            </button>
                            <button @click="destroyAccount(account.id)"
                                class="text-xs text-slate-600 hover:text-red-400 transition-colors">
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Balance history -->
                    <div v-if="account.balances?.length > 1" class="space-y-1.5">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-600">Historial reciente</p>
                        <div class="space-y-1">
                            <div v-for="b in account.balances.slice(1, 6)" :key="b.id"
                                class="flex items-center justify-between text-xs text-slate-500 py-1 border-t border-slate-700/20">
                                <span>{{ fmtDate(b.fecha) }}</span>
                                <span class="text-slate-400">{{ fmt(b.monto) }}</span>
                                <span class="text-slate-600 italic max-w-[200px] truncate">{{ b.nota ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <div v-if="accounts.length === 0"
                    class="rounded-xl border border-dashed border-slate-700 p-12 text-center text-sm text-slate-500">
                    Sin cuentas. Creá la primera con el botón de arriba.
                </div>
            </div>

        </div>

        <!-- Balance Modal -->
        <Teleport to="body">
            <div v-if="balanceModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                @click.self="balanceModal.open = false">
                <div class="w-full max-w-sm rounded-xl bg-surface-950 border border-slate-700/60 p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-200">Actualizar saldo — {{ balanceModal.accountName }}</h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Monto</label>
                            <input v-model="balanceForm.monto" type="number" min="0" step="1" placeholder="0"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Fecha</label>
                            <input v-model="balanceForm.fecha" type="date"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Nota (opcional)</label>
                            <input v-model="balanceForm.nota" type="text" placeholder="ej: después de cobrar"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="balanceModal.open = false"
                            class="flex-1 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors">
                            Cancelar
                        </button>
                        <button @click="submitBalance" :disabled="balanceForm.processing"
                            class="flex-1 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-colors disabled:opacity-50">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>
