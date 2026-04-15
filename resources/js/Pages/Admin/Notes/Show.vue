<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FolderSidebar from '@/Components/Notes/FolderSidebar.vue'
import NoteViewer from '@/Components/Notes/NoteViewer.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    note: Object,
    folders: Array,
})

function togglePin() {
    router.put(route('notes.update', props.note.id), {
        titulo:       props.note.titulo,
        contenido:    props.note.contenido,
        folder_id:    props.note.folder_id,
        esta_fijada:  !props.note.esta_fijada,
        en_dashboard: props.note.en_dashboard,
    }, { preserveScroll: true })
}

function toggleDashboard() {
    router.put(route('notes.update', props.note.id), {
        titulo:       props.note.titulo,
        contenido:    props.note.contenido,
        folder_id:    props.note.folder_id,
        esta_fijada:  props.note.esta_fijada,
        en_dashboard: !props.note.en_dashboard,
    }, { preserveScroll: true })
}

function destroy() {
    if (!confirm('¿Eliminar esta nota?')) return
    router.delete(route('notes.destroy', props.note.id))
}
</script>

<template>
    <Head :title="note.titulo" />
    <div class="flex gap-6 items-start">
        <!-- Sidebar -->
        <FolderSidebar :folders="folders" :active-folder-id="note.folder_id" :filters="{}" />

        <!-- Note content -->
        <div class="flex-1 min-w-0 flex flex-col gap-5">
            <!-- Header -->
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span v-if="note.folder" class="text-xs text-slate-500">{{ note.folder.nombre }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-100 leading-tight">{{ note.titulo }}</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Actualizada el {{ new Date(note.updated_at).toLocaleDateString('es-AR', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                        @click="togglePin"
                        class="p-2 rounded-lg transition-colors"
                        :class="note.esta_fijada ? 'text-amber-400 bg-amber-400/10' : 'text-slate-500 hover:text-amber-400 hover:bg-slate-800'"
                        title="Fijar nota en el listado"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                    <button
                        @click="toggleDashboard"
                        class="p-2 rounded-lg transition-colors"
                        :class="note.en_dashboard ? 'text-emerald-400 bg-emerald-400/10' : 'text-slate-500 hover:text-emerald-400 hover:bg-slate-800'"
                        title="Mostrar en el dashboard"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                    </button>
                    <Link
                        :href="route('notes.edit', note.id)"
                        class="px-3 py-1.5 bg-surface-800 hover:bg-surface-700 border border-slate-700 text-sm text-slate-300 rounded-lg transition-colors"
                    >
                        Editar
                    </Link>
                    <button
                        @click="destroy"
                        class="px-3 py-1.5 bg-red-600/10 hover:bg-red-600/20 border border-red-500/20 text-sm text-red-400 rounded-lg transition-colors"
                    >
                        Eliminar
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-surface-800 border border-slate-700/40 rounded-xl p-6">
                <NoteViewer :contenido="note.contenido" />
            </div>
        </div>
    </div>
</template>
