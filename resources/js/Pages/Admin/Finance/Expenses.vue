<script setup>
import { ref, computed, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    fixed_expenses:    { type: Array, default: () => [] },
    variable_expenses: { type: Array, default: () => [] },
    categories:        { type: Array, default: () => [] },
})

const activeTab = ref('fijos')
const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(Number(n ?? 0))

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR')
}

const totalFijos = computed(() => props.fixed_expenses.filter(e => e.activo).reduce((s, e) => s + Number(e.monto), 0))
const totalVariables = computed(() => props.variable_expenses.reduce((s, e) => s + Number(e.monto), 0))

// Category helpers
function catLabel(value) {
    return props.categories.find(c => c.value === value)?.label ?? value
}
function catColor(value) {
    const map = {
        alquiler: 'bg-violet-500/10 text-violet-400',
        cochera:  'bg-slate-700/30 text-slate-400',
        servicios:'bg-blue-500/10 text-blue-400',
        tarjetas: 'bg-orange-500/10 text-orange-400',
        credito:  'bg-red-500/10 text-red-400',
        suscripciones: 'bg-emerald-500/10 text-emerald-400',
        alimentacion:  'bg-yellow-500/10 text-yellow-400',
        transporte:    'bg-cyan-500/10 text-cyan-400',
        salud:    'bg-pink-500/10 text-pink-400',
        entretenimiento: 'bg-purple-500/10 text-purple-400',
        otros:    'bg-slate-700/30 text-slate-400',
    }
    return map[value] ?? map.otros
}

// Fixed expense modal
const fixedModal = reactive({ open: false, editing: null })
const fixedForm = useForm({
    nombre: '', monto: '', dia_vencimiento: '', categoria: 'otros', activo: true, descripcion: '',
})

function openNewFixed() {
    fixedModal.editing = null
    fixedForm.reset()
    fixedForm.categoria = 'otros'
    fixedForm.activo = true
    fixedModal.open = true
}

function openEditFixed(expense) {
    fixedModal.editing = expense
    fixedForm.nombre          = expense.nombre
    fixedForm.monto           = expense.monto
    fixedForm.dia_vencimiento = expense.dia_vencimiento ?? ''
    fixedForm.categoria       = expense.categoria?.value ?? expense.categoria
    fixedForm.activo          = expense.activo
    fixedForm.descripcion     = expense.descripcion ?? ''
    fixedModal.open = true
}

function saveFixed() {
    if (fixedModal.editing) {
        fixedForm.patch(route('finance.fixed-expenses.update', fixedModal.editing.id), {
            onSuccess: () => { fixedModal.open = false },
        })
    } else {
        fixedForm.post(route('finance.fixed-expenses.store'), {
            onSuccess: () => { fixedModal.open = false; fixedForm.reset() },
        })
    }
}

function destroyFixed(id) {
    if (!confirm('¿Eliminar este gasto fijo?')) return
    router.delete(route('finance.fixed-expenses.destroy', id))
}

function toggleFixed(expense) {
    router.patch(route('finance.fixed-expenses.update', expense.id), {
        ...expense,
        categoria: expense.categoria?.value ?? expense.categoria,
        activo: !expense.activo,
    }, { preserveScroll: true })
}

// Variable expense modal
const varModal = reactive({ open: false, editing: null })
const varForm = useForm({
    fecha: new Date().toISOString().slice(0, 10),
    monto: '',
    descripcion: '',
    categoria: 'otros',
})

function openNewVar() {
    varModal.editing = null
    varForm.reset()
    varForm.fecha     = new Date().toISOString().slice(0, 10)
    varForm.categoria = 'otros'
    varModal.open = true
}

function openEditVar(expense) {
    varModal.editing = expense
    varForm.fecha       = expense.fecha?.slice(0, 10) ?? new Date().toISOString().slice(0, 10)
    varForm.monto       = expense.monto
    varForm.descripcion = expense.descripcion
    varForm.categoria   = expense.categoria?.value ?? expense.categoria
    varModal.open = true
}

function saveVar() {
    if (varModal.editing) {
        varForm.patch(route('finance.variable-expenses.update', varModal.editing.id), {
            onSuccess: () => { varModal.open = false },
        })
    } else {
        varForm.post(route('finance.variable-expenses.store'), {
            onSuccess: () => { varModal.open = false; varForm.reset() },
        })
    }
}

function destroyVar(id) {
    if (!confirm('¿Eliminar este gasto?')) return
    router.delete(route('finance.variable-expenses.destroy', id))
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
                    <h1 class="text-xl font-semibold text-slate-100">Gastos</h1>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 bg-surface-950 border border-slate-700/40 rounded-lg p-1 w-fit">
                <button
                    v-for="tab in [{ key: 'fijos', label: 'Fijos', total: totalFijos }, { key: 'variables', label: 'Variables', total: totalVariables }]"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="[
                        'px-4 py-1.5 text-sm rounded-md transition-colors font-medium',
                        activeTab === tab.key
                            ? 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30'
                            : 'text-slate-400 hover:text-slate-200'
                    ]"
                >
                    {{ tab.label }}
                    <span class="ml-1.5 text-xs opacity-70">{{ fmt(tab.total) }}</span>
                </button>
            </div>

            <!-- ===== FIXED EXPENSES ===== -->
            <div v-if="activeTab === 'fijos'" class="space-y-4">

                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-400">
                        <span class="text-slate-200 font-semibold">{{ fmt(totalFijos) }}</span>
                        / mes en gastos activos
                    </p>
                    <button @click="openNewFixed"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-700/40">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700/40 bg-surface-950">
                                <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Nombre</th>
                                <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Categoría</th>
                                <th class="text-center px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Vence</th>
                                <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Monto</th>
                                <th class="text-center px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Activo</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <tr v-for="e in fixed_expenses" :key="e.id"
                                :class="['transition-colors', e.activo ? 'hover:bg-slate-800/30' : 'opacity-40 hover:opacity-60']">
                                <td class="px-4 py-3 text-slate-200">{{ e.nombre }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['text-[11px] px-2 py-0.5 rounded-full font-medium', catColor(e.categoria?.value ?? e.categoria)]">
                                        {{ catLabel(e.categoria?.value ?? e.categoria) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-400">
                                    {{ e.dia_vencimiento ? `Día ${e.dia_vencimiento}` : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-200">{{ fmt(e.monto) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="toggleFixed(e)"
                                        :class="['w-8 h-4 rounded-full transition-colors relative', e.activo ? 'bg-emerald-600' : 'bg-slate-600']">
                                        <span :class="['absolute top-0.5 w-3 h-3 bg-white rounded-full transition-transform shadow-sm', e.activo ? 'translate-x-4' : 'translate-x-0.5']"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <button @click="openEditFixed(e)" class="text-xs text-slate-500 hover:text-slate-200 transition-colors">Editar</button>
                                        <button @click="destroyFixed(e.id)" class="text-xs text-slate-600 hover:text-red-400 transition-colors">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== VARIABLE EXPENSES ===== -->
            <div v-if="activeTab === 'variables'" class="space-y-4">

                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-400">
                        <span class="text-slate-200 font-semibold">{{ fmt(totalVariables) }}</span>
                        este mes · {{ variable_expenses.length }} gastos
                    </p>
                    <button @click="openNewVar"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar
                    </button>
                </div>

                <div v-if="variable_expenses.length === 0"
                    class="rounded-xl border border-dashed border-slate-700 p-10 text-center text-sm text-slate-500">
                    Sin gastos variables registrados este mes.
                </div>

                <div v-else class="overflow-x-auto rounded-xl border border-slate-700/40">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700/40 bg-surface-950">
                                <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Fecha</th>
                                <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Descripción</th>
                                <th class="text-left px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Categoría</th>
                                <th class="text-right px-4 py-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Monto</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/30">
                            <tr v-for="e in variable_expenses" :key="e.id" class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-3 text-slate-400">{{ fmtDate(e.fecha) }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ e.descripcion }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['text-[11px] px-2 py-0.5 rounded-full font-medium', catColor(e.categoria?.value ?? e.categoria)]">
                                        {{ catLabel(e.categoria?.value ?? e.categoria) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-yellow-400">{{ fmt(e.monto) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <button @click="openEditVar(e)" class="text-xs text-slate-500 hover:text-slate-200 transition-colors">Editar</button>
                                        <button @click="destroyVar(e.id)" class="text-xs text-slate-600 hover:text-red-400 transition-colors">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Fixed expense modal -->
        <Teleport to="body">
            <div v-if="fixedModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                @click.self="fixedModal.open = false">
                <div class="w-full max-w-md rounded-xl bg-surface-950 border border-slate-700/60 p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-200">
                        {{ fixedModal.editing ? 'Editar' : 'Nuevo' }} Gasto Fijo
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Nombre</label>
                            <input v-model="fixedForm.nombre" type="text"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Monto</label>
                                <input v-model="fixedForm.monto" type="number" min="0" step="1"
                                    class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Día de Vencimiento</label>
                                <input v-model="fixedForm.dia_vencimiento" type="number" min="1" max="31" placeholder="opcional"
                                    class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Categoría</label>
                            <select v-model="fixedForm.categoria"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60">
                                <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Descripción (opcional)</label>
                            <input v-model="fixedForm.descripcion" type="text"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="fixedModal.open = false" class="flex-1 py-2 text-sm text-slate-400 hover:text-slate-200">Cancelar</button>
                        <button @click="saveFixed" :disabled="fixedForm.processing"
                            class="flex-1 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white disabled:opacity-50">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Variable expense modal -->
        <Teleport to="body">
            <div v-if="varModal.open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                @click.self="varModal.open = false">
                <div class="w-full max-w-md rounded-xl bg-surface-950 border border-slate-700/60 p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-200">
                        {{ varModal.editing ? 'Editar' : 'Nuevo' }} Gasto Variable
                    </h3>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Fecha</label>
                                <input v-model="varForm.fecha" type="date"
                                    class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Monto</label>
                                <input v-model="varForm.monto" type="number" min="0" step="1"
                                    class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Descripción</label>
                            <input v-model="varForm.descripcion" type="text" placeholder="ej: Supermercado Carrefour"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Categoría</label>
                            <select v-model="varForm.categoria"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60">
                                <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="varModal.open = false" class="flex-1 py-2 text-sm text-slate-400 hover:text-slate-200">Cancelar</button>
                        <button @click="saveVar" :disabled="varForm.processing"
                            class="flex-1 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white disabled:opacity-50">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AdminLayout>
</template>
