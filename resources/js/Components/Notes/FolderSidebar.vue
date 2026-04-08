<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    folders: Array,
    activeFolderId: [Number, String, null],
    filters: Object,
})

const showNewFolder = ref(false)

const form = useForm({
    nombre: '',
    parent_id: null,
})

function submit() {
    form.post(route('note-folders.store'), {
        onSuccess: () => {
            form.reset()
            showNewFolder.value = false
        },
    })
}

function selectFolder(folderId) {
    router.get(route('notes.index'), { folder_id: folderId }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function clearFolder() {
    router.get(route('notes.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    })
}

function deleteFolder(folder) {
    if (!confirm(`¿Eliminar la carpeta "${folder.nombre}"? Las notas quedarán sin carpeta.`)) return
    router.delete(route('note-folders.destroy', folder.id))
}
</script>

<template>
    <aside class="w-56 flex-shrink-0 flex flex-col gap-1">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Carpetas</span>
            <button
                @click="showNewFolder = !showNewFolder"
                class="text-slate-500 hover:text-violet-400 transition-colors"
                title="Nueva carpeta"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>

        <!-- New folder form -->
        <form v-if="showNewFolder" @submit.prevent="submit" class="mb-2 flex gap-1">
            <input
                v-model="form.nombre"
                type="text"
                placeholder="Nombre..."
                class="flex-1 bg-surface-800 border border-slate-700 rounded px-2 py-1 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500"
                autofocus
            />
            <button type="submit" class="px-2 py-1 bg-violet-600 hover:bg-violet-500 text-white text-sm rounded transition-colors">
                OK
            </button>
        </form>

        <!-- All notes -->
        <button
            @click="clearFolder"
            class="flex items-center gap-2 px-3 py-2 rounded text-sm text-left w-full transition-colors"
            :class="!activeFolderId
                ? 'bg-violet-600/10 text-violet-400 font-medium'
                : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0-4-4m4 4-4 4"/>
            </svg>
            Todas las notas
        </button>

        <!-- Folder list -->
        <div
            v-for="folder in folders"
            :key="folder.id"
            class="group flex items-center gap-2 px-3 py-2 rounded text-sm transition-colors cursor-pointer"
            :class="activeFolderId == folder.id
                ? 'bg-violet-600/10 text-violet-400 font-medium'
                : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
            @click="selectFolder(folder.id)"
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            <span class="flex-1 truncate">{{ folder.nombre }}</span>
            <span class="text-xs text-slate-600">{{ folder.notes_count }}</span>
            <button
                @click.stop="deleteFolder(folder)"
                class="opacity-0 group-hover:opacity-100 text-slate-600 hover:text-red-400 transition-all"
                title="Eliminar carpeta"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <p v-if="!folders.length" class="text-xs text-slate-600 px-3 mt-1">Sin carpetas</p>
    </aside>
</template>
