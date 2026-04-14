<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import NoteEditor from '@/Components/Notes/NoteEditor.vue'
import InputError from '@/Components/InputError.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    entry:               Object,
    types:               Array,
    statuses:            Array,
    confidences:         Array,
    sources:             Array,
    embeddingPriorities: Array,
})

const form = useForm({
    entry_id:           props.entry.entry_id,
    titulo:             props.entry.titulo,
    type:               props.entry.type,
    status:             props.entry.status,
    confidence:         props.entry.confidence,
    source:             props.entry.source,
    verified:           props.entry.verified,
    domain:             props.entry.domain ?? '',
    subdomain:          props.entry.subdomain ?? '',
    tags:               Array.isArray(props.entry.tags) ? props.entry.tags.join(', ') : '',
    scope:              props.entry.scope ?? '',
    summary:            props.entry.summary ?? '',
    contenido:          props.entry.contenido ?? '',
    avature_version:    props.entry.avature_version ?? '',
    embedding_priority: props.entry.embedding_priority,
})

function submit() {
    const data = {
        ...form.data(),
        tags: form.tags ? form.tags.split(',').map(t => t.trim()).filter(Boolean) : [],
    }
    form.transform(() => data).put(route('knowledge.update', props.entry.id))
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <PageHeader title="Editar entrada" :subtitle="entry.entry_id">
            <Link
                :href="route('knowledge.show', entry.id)"
                class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-100 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver
            </Link>
        </PageHeader>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <!-- Grid de metadata -->
            <div class="bg-surface-800 border border-slate-700/50 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 mb-4 uppercase tracking-wider">Metadata</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">ID único <span class="text-red-400">*</span></label>
                        <input v-model="form.entry_id" type="text" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors" />
                        <p class="text-xs text-slate-500">Slug único: minúsculas, números y guiones</p>
                        <InputError :message="form.errors.entry_id" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Título <span class="text-red-400">*</span></label>
                        <input v-model="form.titulo" type="text" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors" />
                        <InputError :message="form.errors.titulo" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Tipo <span class="text-red-400">*</span></label>
                        <select v-model="form.type" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Estado <span class="text-red-400">*</span></label>
                        <select v-model="form.status" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Confianza <span class="text-red-400">*</span></label>
                        <select v-model="form.confidence" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option v-for="c in confidences" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                        <InputError :message="form.errors.confidence" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Fuente <span class="text-red-400">*</span></label>
                        <select v-model="form.source" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option v-for="s in sources" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <InputError :message="form.errors.source" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Dominio</label>
                        <input v-model="form.domain" type="text" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors" />
                        <InputError :message="form.errors.domain" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Subdominio</label>
                        <input v-model="form.subdomain" type="text" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors" />
                        <InputError :message="form.errors.subdomain" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Alcance</label>
                        <select v-model="form.scope" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option value="">Sin especificar</option>
                            <option value="module">Module</option>
                            <option value="system">System</option>
                            <option value="cross-system">Cross-system</option>
                        </select>
                        <InputError :message="form.errors.scope" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Versión Avature</label>
                        <input v-model="form.avature_version" type="text" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors" />
                        <InputError :message="form.errors.avature_version" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Prioridad embedding <span class="text-red-400">*</span></label>
                        <select v-model="form.embedding_priority" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-violet-500 transition-colors">
                            <option v-for="p in embeddingPriorities" :key="p.value" :value="p.value">{{ p.label }}</option>
                        </select>
                        <InputError :message="form.errors.embedding_priority" />
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <input v-model="form.verified" type="checkbox" id="verified" class="rounded border-slate-700 bg-surface-800 text-violet-500 focus:ring-violet-500 focus:ring-offset-0" />
                        <label for="verified" class="text-sm text-slate-400 cursor-pointer">Verificado en código real</label>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400 font-medium">Tags</label>
                        <input v-model="form.tags" type="text" placeholder="webrtc, signaling, rtctevent" class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors" />
                        <p class="text-xs text-slate-500">Separados por coma</p>
                        <InputError :message="form.errors.tags" />
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-1">
                    <label class="text-xs text-slate-400 font-medium">Summary</label>
                    <textarea
                        v-model="form.summary"
                        rows="2"
                        maxlength="500"
                        class="bg-surface-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors resize-none"
                    />
                    <p class="text-xs text-slate-500">{{ form.summary.length }}/500 caracteres</p>
                    <InputError :message="form.errors.summary" />
                </div>
            </div>

            <div class="bg-surface-800 border border-slate-700/50 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-slate-300 mb-4 uppercase tracking-wider">Contenido</h2>
                <NoteEditor v-model="form.contenido" />
                <InputError :message="form.errors.contenido" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</template>
