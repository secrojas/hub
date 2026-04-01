<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    billing: Object,
    clients: Array,
})

const form = useForm({
    client_id:     props.billing.client_id,
    concepto:      props.billing.concepto,
    monto:         props.billing.monto,
    fecha_emision: props.billing.fecha_emision ? props.billing.fecha_emision.substring(0, 10) : '',
    fecha_pago:    props.billing.fecha_pago,
    estado:        props.billing.estado,
})

function submit() {
    form.put(`/billing/${props.billing.id}`)
}

// Delete modal — ref(null) sentinel pattern
const billingAEliminar = ref(null)

function confirmDelete() {
    billingAEliminar.value = props.billing
}

function cancelDelete() {
    billingAEliminar.value = null
}

function deleteBilling() {
    useForm({}).delete(`/billing/${props.billing.id}`)
}
</script>

<template>
    <Head title="Editar Cobro" />
    <div class="max-w-2xl">
        <PageHeader title="Editar Cobro" :subtitle="props.billing.concepto">
            <Link href="/billing">
                <Button variant="ghost" size="sm">Cancelar</Button>
            </Link>
        </PageHeader>

        <Card variant="default" padding="lg">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-slate-300 mb-1">Cliente <span class="text-red-400">*</span></label>
                    <select id="client_id" v-model="form.client_id" class="w-full">
                        <option value="">Seleccionar cliente...</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                    </select>
                    <p v-if="form.errors.client_id" class="text-red-400 text-sm mt-1">{{ form.errors.client_id }}</p>
                </div>

                <div>
                    <label for="concepto" class="block text-sm font-medium text-slate-300 mb-1">Concepto <span class="text-red-400">*</span></label>
                    <input id="concepto" v-model="form.concepto" type="text" class="w-full" />
                    <p v-if="form.errors.concepto" class="text-red-400 text-sm mt-1">{{ form.errors.concepto }}</p>
                </div>

                <div>
                    <label for="monto" class="block text-sm font-medium text-slate-300 mb-1">Monto <span class="text-red-400">*</span></label>
                    <input id="monto" v-model="form.monto" type="text" placeholder="1500.50" class="w-full" />
                    <p v-if="form.errors.monto" class="text-red-400 text-sm mt-1">{{ form.errors.monto }}</p>
                </div>

                <div>
                    <label for="fecha_emision" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Emision <span class="text-red-400">*</span></label>
                    <input id="fecha_emision" v-model="form.fecha_emision" type="date" class="w-full" />
                    <p v-if="form.errors.fecha_emision" class="text-red-400 text-sm mt-1">{{ form.errors.fecha_emision }}</p>
                </div>

                <div>
                    <label for="estado" class="block text-sm font-medium text-slate-300 mb-1">Estado <span class="text-red-400">*</span></label>
                    <select id="estado" v-model="form.estado" class="w-full">
                        <option value="pendiente">Pendiente</option>
                        <option value="pagado">Pagado</option>
                        <option value="vencido">Vencido</option>
                    </select>
                    <p v-if="form.errors.estado" class="text-red-400 text-sm mt-1">{{ form.errors.estado }}</p>
                </div>

                <div>
                    <label for="fecha_pago" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Pago</label>
                    <input id="fecha_pago" v-model="form.fecha_pago" type="date" class="w-full" />
                    <p v-if="form.errors.fecha_pago" class="text-red-400 text-sm mt-1">{{ form.errors.fecha_pago }}</p>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Button type="button" variant="danger" @click="confirmDelete">Eliminar</Button>
                    <div class="flex items-center gap-4">
                        <Button type="submit" variant="primary" :disabled="form.processing">Guardar</Button>
                        <Link href="/billing" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</Link>
                    </div>
                </div>
            </form>
        </Card>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="billingAEliminar" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-slate-100 mb-2">Confirmar eliminacion</h2>
            <p class="text-slate-400 mb-6">
                Eliminar el cobro <strong class="text-slate-200">{{ billingAEliminar.concepto }}</strong>? Esta accion no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <Button variant="ghost" @click="cancelDelete">Cancelar</Button>
                <Button variant="danger" @click="deleteBilling">Eliminar</Button>
            </div>
        </div>
    </div>
</template>
