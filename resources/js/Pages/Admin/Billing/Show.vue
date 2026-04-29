<script setup>
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import { formatHoras } from '@/composables/useFormatHoras.js'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    billing: Object,
})

const page = usePage()
const flash = computed(() => page.props.flash?.message ?? null)

const confirmEnvio = ref(false)
const enviando     = ref(false)

const numero = computed(() => '#' + String(props.billing.id).padStart(5, '0'))

function formatMonto(n) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(n ?? 0)
}

function formatDate(d) {
    if (!d) return '—'
    const [y, m, day] = d.split('-')
    return `${day}/${m}/${y}`
}

function sendEmail() {
    enviando.value = true
    router.post(route('billing.send-email', props.billing.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            enviando.value    = false
            confirmEnvio.value = false
        },
    })
}
</script>

<template>
    <Head :title="`${numero} — ${billing.concepto}`" />

    <div class="max-w-4xl">
        <PageHeader :title="`${numero} — ${billing.concepto}`" subtitle="Detalle del cobro">
            <div class="flex items-center gap-3">
                <Link :href="route('billing.edit', billing.id)">
                    <Button variant="ghost" size="sm">Editar</Button>
                </Link>
                <Link href="/billing">
                    <Button variant="ghost" size="sm">Volver</Button>
                </Link>
            </div>
        </PageHeader>

        <!-- Flash message -->
        <div v-if="flash" class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-xl text-sm text-green-400">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ flash }}
        </div>

        <!-- Header info bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-surface-800 border border-slate-700/40 rounded-xl p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Cliente</p>
                <p class="text-sm font-medium text-slate-100">{{ billing.client?.nombre ?? '—' }}</p>
                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ billing.client?.email ?? '' }}</p>
            </div>

            <div class="bg-surface-800 border border-slate-700/40 rounded-xl p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Emisión</p>
                <p class="text-sm font-medium text-slate-100">{{ formatDate(billing.fecha_emision) }}</p>
            </div>

            <div class="bg-surface-800 border border-slate-700/40 rounded-xl p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Estado</p>
                <Badge :variant="billing.estado" class="mt-0.5" />
                <p v-if="billing.fecha_pago" class="text-xs text-slate-500 mt-1.5">Pagado: {{ formatDate(billing.fecha_pago) }}</p>
            </div>

            <div class="bg-surface-800 border border-violet-500/20 border-l-4 border-l-violet-500 rounded-xl p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Total</p>
                <p class="text-xl font-bold text-violet-300">{{ formatMonto(billing.monto) }}</p>
            </div>
        </div>

        <!-- Items table -->
        <Card variant="default" padding="none" class="mb-5">
            <div class="px-6 py-4 border-b border-slate-700/40">
                <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Ítems del cobro</h2>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-28">Tipo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-24">Horas</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider w-36">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in billing.items"
                        :key="item.id"
                        class="border-b border-slate-700/20 last:border-0"
                    >
                        <td class="px-6 py-4 text-slate-100">
                            <span class="font-medium">{{ item.concepto }}</span>
                            <span v-if="item.task" class="block text-xs text-slate-500 mt-0.5">{{ item.task.titulo }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span v-if="item.task_id"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-500/10 text-violet-400 border border-violet-500/20">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Tarea
                            </span>
                            <span v-else class="text-xs text-slate-500">Manual</span>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-400">
                            {{ item.task?.horas ? formatHoras(item.task.horas) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-slate-100">
                            {{ formatMonto(item.monto) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700/60 bg-surface-800/50">
                        <td colspan="3" class="px-6 py-4 text-sm font-semibold text-slate-400 uppercase tracking-wider">Total</td>
                        <td class="px-6 py-4 text-right text-lg font-bold text-slate-100">
                            {{ formatMonto(billing.monto) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </Card>

        <!-- Actions bar -->
        <div class="flex flex-wrap items-center justify-between gap-4">

            <!-- PDF AFIP status -->
            <div class="flex items-center gap-3">
                <template v-if="billing.has_afip_pdf">
                    <div class="flex items-center gap-2 text-sm text-green-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        PDF AFIP subido
                    </div>
                    <a
                        :href="route('billing.afip-pdf.download', billing.id)"
                        target="_blank"
                        class="text-xs text-violet-400 hover:text-violet-300 transition-colors underline underline-offset-2"
                    >Descargar</a>
                </template>
                <span v-else class="text-sm text-slate-500">Sin PDF AFIP</span>
            </div>

            <!-- Send email -->
            <div class="flex items-center gap-3">
                <span v-if="!confirmEnvio" class="text-xs text-slate-500">
                    Se enviará a <strong class="text-slate-400">{{ billing.client?.email }}</strong>
                </span>

                <template v-if="!confirmEnvio">
                    <Button variant="primary" @click="confirmEnvio = true">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Enviar por email
                    </Button>
                </template>
                <template v-else>
                    <span class="text-sm text-amber-400">¿Confirmar envío?</span>
                    <Button variant="ghost" size="sm" @click="confirmEnvio = false">Cancelar</Button>
                    <Button variant="primary" size="sm" :disabled="enviando" @click="sendEmail">
                        {{ enviando ? 'Enviando...' : 'Sí, enviar' }}
                    </Button>
                </template>
            </div>
        </div>
    </div>
</template>
