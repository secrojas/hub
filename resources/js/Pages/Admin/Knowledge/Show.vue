<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    entry:         Object,
    allEntries:    Array,
    relationTypes: Array,
})

const linkForm = useForm({
    to_entry_id:   '',
    relation_type: '',
    notes:         '',
})

function submitLink() {
    linkForm.post(route('knowledge-links.store', props.entry.id), {
        onSuccess: () => linkForm.reset(),
    })
}

function deleteLink(linkId) {
    if (confirm('¿Eliminar esta relación?')) {
        router.delete(route('knowledge-links.destroy', linkId))
    }
}

function deleteEntry() {
    if (confirm('¿Eliminar esta entrada? Esta acción no se puede deshacer.')) {
        router.delete(route('knowledge.destroy', props.entry.id))
    }
}

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

const otherEntries = props.allEntries.filter(e => e.id !== props.entry.id)
</script>

<template>
    <div class="flex flex-col gap-6">
        <PageHeader :title="entry.titulo" :subtitle="entry.entry_id">
            <div class="flex items-center gap-2">
                <Link
                    :href="route('knowledge.edit', entry.id)"
                    class="px-3 py-1.5 bg-surface-800 border border-slate-700 hover:border-violet-500 text-slate-300 hover:text-violet-300 text-sm rounded-lg transition-colors"
                >
                    Editar
                </Link>
                <button
                    @click="deleteEntry"
                    class="px-3 py-1.5 bg-surface-800 border border-slate-700 hover:border-red-500 text-slate-300 hover:text-red-400 text-sm rounded-lg transition-colors"
                >
                    Eliminar
                </button>
                <Link
                    :href="route('knowledge.index')"
                    class="text-sm text-slate-400 hover:text-slate-100 transition-colors"
                >
                    ← Volver
                </Link>
            </div>
        </PageHeader>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main content -->
            <div class="lg:col-span-2 flex flex-col gap-6">

                <!-- Summary -->
                <div v-if="entry.summary" class="bg-violet-600/5 border border-violet-500/20 rounded-xl p-4">
                    <p class="text-xs text-violet-400 font-medium uppercase tracking-wider mb-1">Summary</p>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ entry.summary }}</p>
                </div>

                <!-- Tags -->
                <div v-if="entry.tags && entry.tags.length" class="flex flex-wrap gap-2">
                    <span
                        v-for="tag in entry.tags"
                        :key="tag"
                        class="px-2 py-0.5 bg-slate-700/50 text-slate-400 text-xs rounded-full"
                    >{{ tag }}</span>
                </div>

                <!-- Contenido -->
                <div class="bg-surface-800 border border-slate-700/50 rounded-xl p-6 min-w-0 overflow-hidden">
                    <div class="kb-content prose prose-invert max-w-none" v-html="entry.contenido" />
                </div>

                <!-- Links / Relaciones -->
                <div class="bg-surface-800 border border-slate-700/50 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-slate-300 mb-4 uppercase tracking-wider">Relaciones</h2>

                    <!-- Outgoing links -->
                    <div v-if="entry.links && entry.links.length" class="mb-4">
                        <p class="text-xs text-slate-500 mb-2">Este nodo explica / depende de:</p>
                        <div class="flex flex-col gap-2">
                            <div
                                v-for="link in entry.links"
                                :key="link.id"
                                class="flex items-center gap-3 p-2.5 bg-surface-900 rounded-lg"
                            >
                                <span class="px-2 py-0.5 bg-slate-700/50 text-slate-400 text-xs rounded font-mono">{{ link.relation_type }}</span>
                                <Link
                                    :href="route('knowledge.show', link.to_entry.id)"
                                    class="text-sm text-slate-200 hover:text-violet-300 transition-colors flex-1"
                                >
                                    {{ link.to_entry.titulo }}
                                </Link>
                                <button
                                    @click="deleteLink(link.id)"
                                    class="text-slate-600 hover:text-red-400 transition-colors text-xs"
                                >✕</button>
                            </div>
                        </div>
                    </div>

                    <!-- Backlinks -->
                    <div v-if="entry.backlinks && entry.backlinks.length" class="mb-4">
                        <p class="text-xs text-slate-500 mb-2">Referenciado por:</p>
                        <div class="flex flex-col gap-2">
                            <div
                                v-for="link in entry.backlinks"
                                :key="link.id"
                                class="flex items-center gap-3 p-2.5 bg-surface-900 rounded-lg"
                            >
                                <span class="px-2 py-0.5 bg-slate-700/50 text-slate-400 text-xs rounded font-mono">{{ link.relation_type }}</span>
                                <Link
                                    :href="route('knowledge.show', link.from_entry.id)"
                                    class="text-sm text-slate-200 hover:text-violet-300 transition-colors flex-1"
                                >
                                    {{ link.from_entry.titulo }}
                                </Link>
                                <button
                                    @click="deleteLink(link.id)"
                                    class="text-slate-600 hover:text-red-400 transition-colors text-xs"
                                >✕</button>
                            </div>
                        </div>
                    </div>

                    <div v-if="!entry.links?.length && !entry.backlinks?.length" class="text-sm text-slate-600 mb-4">
                        Sin relaciones aún.
                    </div>

                    <!-- Add link form -->
                    <div class="border-t border-slate-700/50 pt-4 mt-4">
                        <p class="text-xs text-slate-500 mb-3">Agregar relación</p>
                        <form @submit.prevent="submitLink" class="flex flex-wrap gap-2 items-end">
                            <div class="flex flex-col gap-1 flex-1 min-w-[180px]">
                                <label class="text-xs text-slate-500">Entrada destino</label>
                                <select
                                    v-model="linkForm.to_entry_id"
                                    class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors"
                                >
                                    <option value="">Seleccionar entrada...</option>
                                    <option v-for="e in otherEntries" :key="e.id" :value="e.id">
                                        {{ e.entry_id }}: {{ e.titulo }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-slate-500">Tipo de relación</label>
                                <select
                                    v-model="linkForm.relation_type"
                                    class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors"
                                >
                                    <option value="">Tipo...</option>
                                    <option v-for="r in relationTypes" :key="r.value" :value="r.value">{{ r.label }}</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1 flex-1 min-w-[140px]">
                                <label class="text-xs text-slate-500">Nota (opcional)</label>
                                <input
                                    v-model="linkForm.notes"
                                    type="text"
                                    placeholder="Contexto adicional..."
                                    class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="linkForm.processing || !linkForm.to_entry_id || !linkForm.relation_type"
                                class="px-4 py-2 bg-violet-600 hover:bg-violet-500 disabled:opacity-40 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Agregar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar metadata -->
            <div class="flex flex-col gap-4">
                <div class="bg-surface-800 border border-slate-700/50 rounded-xl p-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Metadata</h3>
                    <dl class="flex flex-col gap-2.5 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Tipo</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-medium" :class="typeColors[entry.type] ?? 'bg-slate-500/10 text-slate-400'">
                                    {{ entry.type }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Estado</dt>
                            <dd>
                                <span class="px-2 py-0.5 rounded text-xs font-medium" :class="statusColors[entry.status] ?? 'bg-slate-500/10 text-slate-400'">
                                    {{ entry.status }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Confianza</dt>
                            <dd class="text-slate-300">{{ entry.confidence }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Fuente</dt>
                            <dd class="text-slate-300">{{ entry.source }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Verificado</dt>
                            <dd class="text-slate-300">{{ entry.verified ? '✓ Sí' : '✗ No' }}</dd>
                        </div>
                        <div v-if="entry.domain" class="flex items-center justify-between">
                            <dt class="text-slate-500">Dominio</dt>
                            <dd class="text-slate-300">{{ entry.domain }}</dd>
                        </div>
                        <div v-if="entry.subdomain" class="flex items-center justify-between">
                            <dt class="text-slate-500">Subdominio</dt>
                            <dd class="text-slate-300">{{ entry.subdomain }}</dd>
                        </div>
                        <div v-if="entry.scope" class="flex items-center justify-between">
                            <dt class="text-slate-500">Alcance</dt>
                            <dd class="text-slate-300">{{ entry.scope }}</dd>
                        </div>
                        <div v-if="entry.avature_version" class="flex items-center justify-between">
                            <dt class="text-slate-500">Versión</dt>
                            <dd class="text-slate-300 text-xs font-mono">{{ entry.avature_version }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Embedding</dt>
                            <dd class="text-slate-300">{{ entry.embedding_priority }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>

