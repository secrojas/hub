<script setup>
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import InputError from '@/Components/InputError.vue'
import { formatHoras } from '@/composables/useFormatHoras.js'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    billing: Object,
    clients: Array,
    tareas_finalizadas: Array,
})

const form = useForm({
    client_id:     props.billing.client_id,
    concepto:      props.billing.concepto,
    fecha_emision: props.billing.fecha_emision ? props.billing.fecha_emision.substring(0, 10) : '',
    fecha_pago:    props.billing.fecha_pago,
    estado:        props.billing.estado,
    items:         (props.billing.items ?? []).map(i => ({
        task_id:  i.task_id,
        concepto: i.concepto,
        monto:    i.monto,
    })),
})

// ── Helpers ────────────────────────────────────────────────────
const selectedClient = computed(() => props.clients.find(c => c.id == form.client_id))
const valorHora      = computed(() => parseFloat(selectedClient.value?.valor_hora ?? 0))

const tareasDisponibles = computed(() =>
    (props.tareas_finalizadas ?? []).filter(
        t => t.client_id == form.client_id && !form.items.some(i => i.task_id === t.id)
    )
)

const total = computed(() =>
    form.items.reduce((sum, item) => sum + parseFloat(item.monto || 0), 0)
)

const formatMonto = (n) =>
    new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(n ?? 0)

// ── Items ──────────────────────────────────────────────────────
const selectedTaskId = ref('')

function onTaskSelect() {
    if (!selectedTaskId.value) return
    const task = props.tareas_finalizadas.find(t => t.id == selectedTaskId.value)
    if (!task) return
    form.items.push({
        task_id:  task.id,
        concepto: task.titulo,
        monto:    parseFloat((task.horas * valorHora.value).toFixed(2)),
    })
    selectedTaskId.value = ''
}

function addManual() {
    form.items.push({ task_id: null, concepto: '', monto: '' })
}

function removeItem(index) {
    form.items.splice(index, 1)
}

function submit() {
    form.put(route('billing.update', props.billing.id))
}

// ── Delete ─────────────────────────────────────────────────────
const confirmando = ref(false)
function deleteBilling() {
    useForm({}).delete(route('billing.destroy', props.billing.id))
}
</script>

<template>
    <div class="max-w-3xl">
        <PageHeader title="Editar Cobro" :subtitle="billing.concepto">
            <Link href="/billing">
                <Button variant="ghost" size="sm">Cancelar</Button>
            </Link>
        </PageHeader>

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <!-- Header data -->
            <Card variant="default" padding="lg">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Cliente <span class="text-red-400">*</span></label>
                        <select v-model="form.client_id" class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500">
                            <option value="">Seleccionar cliente...</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">
                                {{ c.nombre }}{{ c.valor_hora ? ` — $${c.valor_hora}/h` : '' }}
                            </option>
                        </select>
                        <InputError :message="form.errors.client_id" class="mt-1" />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Título del cobro <span class="text-red-400">*</span></label>
                        <input v-model="form.concepto" type="text"
                            class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500" />
                        <InputError :message="form.errors.concepto" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Fecha de emisión <span class="text-red-400">*</span></label>
                        <input v-model="form.fecha_emision" type="date"
                            class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500" />
                        <InputError :message="form.errors.fecha_emision" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Estado <span class="text-red-400">*</span></label>
                        <select v-model="form.estado"
                            class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <div v-if="form.estado === 'pagado'" class="col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-1">Fecha de pago</label>
                        <input v-model="form.fecha_pago" type="date"
                            class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-violet-500" />
                        <InputError :message="form.errors.fecha_pago" class="mt-1" />
                    </div>
                </div>
            </Card>

            <!-- Items -->
            <Card variant="default" padding="lg">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Ítems</h2>
                    <div class="flex items-center gap-2">
                        <div v-if="form.client_id && tareasDisponibles.length" class="flex items-center gap-2">
                            <select v-model="selectedTaskId" @change="onTaskSelect"
                                class="bg-surface-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:outline-none focus:border-violet-500">
                                <option value="">+ Agregar tarea finalizada</option>
                                <option v-for="t in tareasDisponibles" :key="t.id" :value="t.id">
                                    {{ t.titulo }} ({{ formatHoras(t.horas) }}{{ valorHora ? ` — ${formatMonto(t.horas * valorHora)}` : '' }})
                                </option>
                            </select>
                        </div>
                        <p v-else-if="form.client_id && !tareasDisponibles.length" class="text-xs text-slate-500">Sin tareas finalizadas disponibles</p>
                        <button type="button" @click="addManual"
                            class="px-3 py-1.5 bg-surface-700 hover:bg-surface-600 border border-slate-600 text-sm text-slate-300 rounded-lg transition-colors">
                            + Ítem manual
                        </button>
                    </div>
                </div>

                <InputError :message="form.errors.items" class="mb-2" />

                <p v-if="!form.items.length" class="text-sm text-slate-500 text-center py-6 border border-dashed border-slate-700 rounded-lg">
                    Agregá al menos un ítem.
                </p>

                <div v-else class="flex flex-col gap-2">
                    <div class="grid grid-cols-[1fr_160px_32px] gap-3 px-1 mb-1">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Concepto</span>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Monto (ARS)</span>
                        <span />
                    </div>

                    <div v-for="(item, i) in form.items" :key="i"
                        class="grid grid-cols-[1fr_160px_32px] gap-3 items-center">
                        <div>
                            <input v-model="item.concepto" type="text"
                                class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500" />
                            <InputError :message="form.errors[`items.${i}.concepto`]" class="mt-0.5 text-xs" />
                        </div>
                        <div>
                            <input v-model="item.monto" type="number" step="0.01" min="0"
                                class="w-full bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 text-right" />
                            <InputError :message="form.errors[`items.${i}.monto`]" class="mt-0.5 text-xs" />
                        </div>
                        <button type="button" @click="removeItem(i)"
                            class="text-slate-600 hover:text-red-400 transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex justify-end mt-3 pt-3 border-t border-slate-700/60">
                        <div class="text-right">
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Total</p>
                            <p class="text-xl font-bold text-slate-100 mt-0.5">{{ formatMonto(total) }}</p>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <Button type="button" variant="danger" @click="confirmando = true">Eliminar cobro</Button>
                <div class="flex items-center gap-3">
                    <Link href="/billing" class="px-4 py-2 text-sm text-slate-400 hover:text-slate-100 transition-colors">Cancelar</Link>
                    <Button type="submit" variant="primary" :disabled="form.processing || !form.items.length">
                        Actualizar cobro
                    </Button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete modal -->
    <div v-if="confirmando" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Eliminar cobro</h2>
            <p class="text-slate-400 mb-6">
                ¿Eliminar <strong class="text-slate-200">{{ billing.concepto }}</strong>? Esta acción no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="confirmando = false">Cancelar</Button>
                <Button variant="danger" @click="deleteBilling">Eliminar</Button>
            </div>
        </div>
    </div>
</template>
