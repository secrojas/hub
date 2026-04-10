<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import NoteEditor from '@/Components/Notes/NoteEditor.vue'
import FolderSidebar from '@/Components/Notes/FolderSidebar.vue'
import InputError from '@/Components/InputError.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    note: Object,
    folders: Array,
})

const form = useForm({
    titulo:       props.note.titulo,
    contenido:    props.note.contenido ?? '',
    folder_id:    props.note.folder_id,
    esta_fijada:  props.note.esta_fijada,
    en_dashboard: props.note.en_dashboard,
})

function submit() {
    form.put(route('notes.update', props.note.id))
}
</script>

<template>
    <!-- Sticky top action bar -->
    <div class="sticky top-0 z-20 -mx-6 -mt-6 px-6 py-3 bg-surface-900/95 backdrop-blur border-b border-slate-700/60 flex items-center gap-3 mb-6">
        <Link
            :href="route('notes.show', note.id)"
            class="flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-100 transition-colors mr-auto"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Cancelar
        </Link>

        <!-- Toggles in the bar -->
        <label class="flex items-center gap-1.5 cursor-pointer select-none" title="Fijar nota en el listado">
            <input v-model="form.esta_fijada" type="checkbox" class="rounded border-slate-700 bg-surface-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-0" />
            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <span class="text-xs text-slate-400">Fijada</span>
        </label>

        <label class="flex items-center gap-1.5 cursor-pointer select-none" title="Mostrar en el dashboard">
            <input v-model="form.en_dashboard" type="checkbox" class="rounded border-slate-700 bg-surface-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0" />
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
            <span class="text-xs text-slate-400">Dashboard</span>
        </label>

        <button
            type="button"
            @click="submit"
            :disabled="form.processing"
            class="px-4 py-1.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors"
        >
            Guardar
        </button>
    </div>

    <form @submit.prevent="submit" class="flex gap-6 items-start">
        <!-- Sidebar -->
        <FolderSidebar :folders="folders" :active-folder-id="note.folder_id" :filters="{}" />

        <!-- Main editing area -->
        <div class="flex-1 min-w-0 flex flex-col gap-4">

            <!-- Title -->
            <div>
                <input
                    v-model="form.titulo"
                    type="text"
                    placeholder="Título de la nota..."
                    class="w-full bg-transparent border-0 border-b border-slate-700 px-0 py-2 text-2xl font-bold text-slate-100 placeholder-slate-600 focus:outline-none focus:border-violet-500 transition-colors"
                />
                <InputError :message="form.errors.titulo" class="mt-1" />
            </div>

            <!-- Folder selector -->
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                <select
                    v-model="form.folder_id"
                    class="bg-surface-800 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:outline-none focus:border-violet-500 transition-colors"
                >
                    <option :value="null">Sin carpeta</option>
                    <option v-for="folder in folders" :key="folder.id" :value="folder.id">
                        {{ folder.nombre }}
                    </option>
                </select>
                <InputError :message="form.errors.folder_id" />
            </div>

            <!-- Editor -->
            <div>
                <NoteEditor v-model="form.contenido" />
                <InputError :message="form.errors.contenido" class="mt-1" />
            </div>
        </div>
    </form>
</template>
