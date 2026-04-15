<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    entries:  Array,
    filters:  Object,
    types:    Array,
    statuses: Array,
})

const search = ref(props.filters?.search ?? '')
const type   = ref(props.filters?.type ?? '')
const status = ref(props.filters?.status ?? '')
const domain = ref(props.filters?.domain ?? '')

let debounce = null
function applyFilters() {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('knowledge.index'), {
            search: search.value || undefined,
            type:   type.value || undefined,
            status: status.value || undefined,
            domain: domain.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }, 300)
}
watch([search, type, status, domain], applyFilters)

const typeColors = {
    concept:  'bg-blue-500/10 text-blue-400',
    flow:     'bg-cyan-500/10 text-cyan-400',
    bug:      'bg-red-500/10 text-red-400',
    decision: 'bg-amber-500/10 text-amber-400',
    runbook:  'bg-green-500/10 text-green-400',
    glossary: 'bg-purple-500/10 text-purple-400',
}

const statusColors = {
    draft:    'bg-slate-500/10 text-slate-400',
    reviewed: 'bg-blue-500/10 text-blue-400',
    verified: 'bg-green-500/10 text-green-400',
    stale:    'bg-red-500/10 text-red-400',
}
</script>

<template>
    <Head title="Knowledge Base" />
    <div class="flex flex-col gap-6">
        <PageHeader title="Knowledge Base" subtitle="Conocimiento técnico de trabajo en empresa">
            <Link
                :href="route('knowledge.create')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva entrada
            </Link>
        </PageHeader>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar por título o summary..."
                    class="w-full bg-surface-800 border border-slate-700 rounded-lg pl-9 pr-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
                />
            </div>

            <select
                v-model="type"
                class="bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors"
            >
                <option value="">Todos los tipos</option>
                <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>

            <select
                v-model="status"
                class="bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors"
            >
                <option value="">Todos los estados</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>

            <input
                v-model="domain"
                type="text"
                placeholder="Dominio..."
                class="bg-surface-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
            />
        </div>

        <!-- Table -->
        <div class="bg-surface-800 border border-slate-700/50 rounded-xl overflow-hidden">
            <table v-if="entries.length" class="w-full text-sm">
                <thead class="border-b border-slate-700/50">
                    <tr class="text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Título</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-left">Dominio</th>
                        <th class="px-4 py-3 text-left">Actualizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    <tr
                        v-for="entry in entries"
                        :key="entry.id"
                        class="hover:bg-slate-700/20 transition-colors cursor-pointer"
                        @click="router.visit(route('knowledge.show', entry.id))"
                    >
                        <td class="px-4 py-3">
                            <code class="text-xs text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded">{{ entry.entry_id }}</code>
                        </td>
                        <td class="px-4 py-3 text-slate-100 font-medium">{{ entry.titulo }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="typeColors[entry.type] ?? 'bg-slate-500/10 text-slate-400'">
                                {{ entry.type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="statusColors[entry.status] ?? 'bg-slate-500/10 text-slate-400'">
                                {{ entry.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ entry.domain ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ new Date(entry.updated_at).toLocaleDateString('es-AR') }}</td>
                    </tr>
                </tbody>
            </table>

            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-10 h-10 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-slate-500 text-sm">No hay entradas de conocimiento</p>
                <Link :href="route('knowledge.create')" class="mt-3 text-violet-400 hover:text-violet-300 text-sm transition-colors">
                    Crear la primera entrada
                </Link>
            </div>
        </div>
    </div>
</template>
