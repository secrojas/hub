<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import axios from 'axios'

const step = ref(1) // 1: upload, 2: preview & confirm
const uploading = ref(false)
const parseError = ref('')
const selectedFile = ref(null)
const fileInput = ref(null)

const form = reactive({
    periodo:          '',
    empresa:          '',
    fecha_pago:       '',
    banco:            '',
    sueldo_basico:    0,
    total_bruto:      0,
    total_sin_aporte: 0,
    total_descuentos: 0,
    total_neto:       0,
    conceptos:        [],
    archivo_path:     '',
})

const submitting = ref(false)
const errors = reactive({})

const fmt = (n) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(Number(n))

function onFileSelect(e) {
    const file = e.target.files[0]
    if (!file) return
    selectedFile.value = file
    parseError.value = ''
}

function onDrop(e) {
    e.preventDefault()
    const file = e.dataTransfer.files[0]
    if (file && file.type === 'application/pdf') {
        selectedFile.value = file
        parseError.value = ''
    }
}

async function analyzeWithGemini() {
    if (!selectedFile.value) return
    uploading.value = true
    parseError.value = ''

    const fd = new FormData()
    fd.append('archivo', selectedFile.value)

    try {
        const { data } = await axios.post(route('finance.payslips.parse'), fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        const parsed = data.parsed
        form.periodo          = parsed.periodo ?? ''
        form.empresa          = parsed.empresa ?? ''
        form.fecha_pago       = parsed.fecha_pago ?? ''
        form.banco            = parsed.banco ?? ''
        form.sueldo_basico    = parsed.sueldo_basico ?? 0
        form.total_bruto      = parsed.total_bruto ?? 0
        form.total_sin_aporte = parsed.total_sin_aporte ?? 0
        form.total_descuentos = parsed.total_descuentos ?? 0
        form.total_neto       = parsed.total_neto ?? 0
        form.conceptos        = parsed.conceptos ?? []
        form.archivo_path     = data.archivo_path

        step.value = 2
    } catch (err) {
        parseError.value = err.response?.data?.error ?? 'Error al analizar el PDF. Intentá de nuevo.'
    } finally {
        uploading.value = false
    }
}

function confirmAndSave() {
    submitting.value = true
    router.post(route('finance.payslips.store'), { ...form }, {
        onError: (errs) => { Object.assign(errors, errs) },
        onFinish: () => { submitting.value = false },
    })
}

function conceptoTipoBadge(tipo) {
    if (tipo === 'haber_con_aporte') return 'bg-emerald-500/10 text-emerald-400'
    if (tipo === 'haber_sin_aporte') return 'bg-blue-500/10 text-blue-400'
    if (tipo === 'descuento') return 'bg-red-500/10 text-red-400'
    return 'bg-slate-700/30 text-slate-400'
}

function conceptoTipoLabel(tipo) {
    if (tipo === 'haber_con_aporte') return 'Haber c/aporte'
    if (tipo === 'haber_sin_aporte') return 'Haber s/aporte'
    if (tipo === 'descuento') return 'Descuento'
    return tipo
}
</script>

<template>
    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('finance.payslips.index')" class="text-xs text-slate-400 hover:text-slate-200 transition-colors">← Recibos</Link>
                <h1 class="text-xl font-semibold text-slate-100">Subir Recibo de Sueldo</h1>
            </div>

            <!-- Step indicators -->
            <div class="flex items-center gap-2">
                <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold', step >= 1 ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-400']">1</div>
                <div class="flex-1 h-px bg-slate-700"></div>
                <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold', step >= 2 ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-400']">2</div>
            </div>

            <!-- Step 1: Upload -->
            <div v-if="step === 1" class="rounded-xl bg-surface-950 border border-slate-700/40 p-6 space-y-5">
                <p class="text-sm font-medium text-slate-300">Seleccioná el PDF del recibo</p>

                <!-- Dropzone -->
                <div
                    @click="fileInput.click()"
                    @dragover.prevent
                    @drop="onDrop"
                    :class="[
                        'rounded-lg border-2 border-dashed p-10 text-center cursor-pointer transition-colors',
                        selectedFile ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-slate-600 hover:border-emerald-500/40 hover:bg-slate-800/40'
                    ]"
                >
                    <input ref="fileInput" type="file" accept=".pdf" class="hidden" @change="onFileSelect" />

                    <template v-if="!selectedFile">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="text-sm text-slate-400">Arrastrá el PDF acá o <span class="text-emerald-400">hacé click</span></p>
                        <p class="text-xs text-slate-600 mt-1">Solo archivos PDF · máx. 20 MB</p>
                    </template>

                    <template v-else>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-emerald-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-300">{{ selectedFile.name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ (selectedFile.size / 1024).toFixed(1) }} KB · click para cambiar</p>
                    </template>
                </div>

                <!-- Error -->
                <p v-if="parseError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                    {{ parseError }}
                </p>

                <!-- Analyze button -->
                <button
                    @click="analyzeWithGemini"
                    :disabled="!selectedFile || uploading"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg font-medium text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed bg-emerald-600 hover:bg-emerald-500 text-white"
                >
                    <svg v-if="uploading" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>{{ uploading ? 'Analizando con Gemini…' : '✨ Analizar con Gemini' }}</span>
                </button>
            </div>

            <!-- Step 2: Preview & Confirm -->
            <div v-if="step === 2" class="space-y-4">

                <!-- Extracted data -->
                <div class="rounded-xl bg-surface-950 border border-slate-700/40 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-300">Datos extraídos — revisá antes de guardar</p>
                        <button @click="step = 1" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Cambiar PDF</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Período</label>
                            <input v-model="form.periodo" type="text" placeholder="2026-04"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Empresa</label>
                            <input v-model="form.empresa" type="text"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Fecha de Pago</label>
                            <input v-model="form.fecha_pago" type="date"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Banco</label>
                            <input v-model="form.banco" type="text"
                                class="w-full bg-slate-800 border border-slate-600/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/60" />
                        </div>
                    </div>

                    <!-- Totals summary -->
                    <div class="rounded-lg bg-slate-800/50 border border-slate-700/40 p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Sueldo Básico</span>
                            <span class="text-slate-200">{{ fmt(form.sueldo_basico) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Total Bruto (c/aporte)</span>
                            <span class="text-slate-200">{{ fmt(form.total_bruto) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Haberes s/aporte</span>
                            <span class="text-slate-200">{{ fmt(form.total_sin_aporte) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Total Descuentos</span>
                            <span class="text-red-400">-{{ fmt(form.total_descuentos) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold border-t border-slate-700 pt-2 mt-1">
                            <span class="text-slate-200">Total Neto</span>
                            <span class="text-emerald-400">{{ fmt(form.total_neto) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Conceptos -->
                <div v-if="form.conceptos?.length" class="rounded-xl bg-surface-950 border border-slate-700/40 p-5 space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Conceptos detectados</p>
                    <div class="space-y-1.5">
                        <div v-for="(c, i) in form.conceptos" :key="i"
                            class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500 font-mono text-xs">{{ c.codigo }}</span>
                                <span class="text-slate-300">{{ c.descripcion }}</span>
                                <span :class="['text-[10px] px-1.5 py-0.5 rounded-full font-medium', conceptoTipoBadge(c.tipo)]">
                                    {{ conceptoTipoLabel(c.tipo) }}
                                </span>
                            </div>
                            <span :class="c.tipo === 'descuento' ? 'text-red-400' : 'text-slate-200'">
                                {{ fmt(c.monto) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button @click="step = 1"
                        class="px-4 py-2.5 text-sm rounded-lg border border-slate-600/60 text-slate-400 hover:text-slate-200 hover:border-slate-500 transition-colors">
                        Volver
                    </button>
                    <button @click="confirmAndSave" :disabled="submitting"
                        class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition-colors disabled:opacity-50">
                        {{ submitting ? 'Guardando…' : 'Confirmar y Guardar' }}
                    </button>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>
