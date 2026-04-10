<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'

const props = defineProps({
    folders: Array,
    activeFolderId: [Number, String, null],
    filters: Object,
})

// ─── Color palette ────────────────────────────────────────────────────────────
const COLORS = [
    { label: 'Gris',    value: '#64748b' },
    { label: 'Violeta', value: '#7c3aed' },
    { label: 'Azul',    value: '#2563eb' },
    { label: 'Verde',   value: '#16a34a' },
    { label: 'Ámbar',   value: '#d97706' },
    { label: 'Rojo',    value: '#dc2626' },
    { label: 'Rosa',    value: '#db2777' },
    { label: 'Teal',    value: '#0d9488' },
]

// ─── Tree building ────────────────────────────────────────────────────────────
const collapsedFolders = ref(new Set())

function toggleCollapse(folderId) {
    if (collapsedFolders.value.has(folderId)) {
        collapsedFolders.value.delete(folderId)
    } else {
        collapsedFolders.value.add(folderId)
    }
    collapsedFolders.value = new Set(collapsedFolders.value)
}

const orderedFolders = computed(() => {
    const result = []

    function addFolder(folder, depth) {
        result.push({ ...folder, depth })
        if (!collapsedFolders.value.has(folder.id)) {
            const children = props.folders.filter(f => f.parent_id == folder.id)
            children.forEach(child => addFolder(child, depth + 1))
        }
    }

    props.folders
        .filter(f => !f.parent_id)
        .forEach(f => addFolder(f, 0))

    return result
})

function hasChildren(folderId) {
    return props.folders.some(f => f.parent_id == folderId)
}

// ─── New folder form ──────────────────────────────────────────────────────────
const showNewFolder = ref(false)
const newForm = useForm({ nombre: '', parent_id: null, color: null })

function submitNew() {
    newForm.post(route('note-folders.store'), {
        onSuccess: () => {
            newForm.reset()
            showNewFolder.value = false
        },
    })
}

// ─── Edit folder ─────────────────────────────────────────────────────────────
const editingId = ref(null)
const editForm = useForm({ nombre: '', parent_id: null, color: null })

function startEdit(folder) {
    editingId.value = folder.id
    editForm.nombre    = folder.nombre
    editForm.parent_id = folder.parent_id
    editForm.color     = folder.color
}

function cancelEdit() {
    editingId.value = null
    editForm.reset()
}

function submitEdit(folder) {
    editForm.patch(route('note-folders.update', folder.id), {
        onSuccess: () => {
            editingId.value = null
        },
    })
}

// ─── Navigation ──────────────────────────────────────────────────────────────
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

        <!-- Nueva nota button -->
        <Link
            :href="route('notes.create')"
            class="flex items-center justify-center gap-2 w-full px-3 py-2 mb-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva nota
        </Link>

        <!-- Carpetas header -->
        <div class="flex items-center justify-between mb-1">
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
        <div v-if="showNewFolder" class="mb-2 flex flex-col gap-2 p-2 bg-surface-800 border border-slate-700 rounded-lg">
            <input
                v-model="newForm.nombre"
                type="text"
                placeholder="Nombre de la carpeta..."
                class="w-full bg-surface-900 border border-slate-700 rounded px-2 py-1 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500"
                autofocus
            />
            <!-- Parent picker -->
            <select
                v-model="newForm.parent_id"
                class="w-full bg-surface-900 border border-slate-700 rounded px-2 py-1 text-xs text-slate-300 focus:outline-none focus:border-violet-500"
            >
                <option :value="null">Sin carpeta padre</option>
                <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.nombre }}</option>
            </select>
            <!-- Color picker -->
            <div class="flex items-center gap-1 flex-wrap">
                <button
                    type="button"
                    @click="newForm.color = null"
                    class="w-5 h-5 rounded-full border-2 transition-all bg-transparent"
                    :class="newForm.color === null ? 'border-slate-300' : 'border-slate-600 hover:border-slate-400'"
                    title="Sin color"
                >
                    <span class="sr-only">Sin color</span>
                </button>
                <button
                    v-for="c in COLORS"
                    :key="c.value"
                    type="button"
                    @click="newForm.color = c.value"
                    class="w-5 h-5 rounded-full border-2 transition-all"
                    :style="{ backgroundColor: c.value }"
                    :class="newForm.color === c.value ? 'border-white scale-110' : 'border-transparent hover:border-slate-300'"
                    :title="c.label"
                />
            </div>
            <div class="flex gap-1">
                <button @click="submitNew" type="button" class="flex-1 px-2 py-1 bg-violet-600 hover:bg-violet-500 text-white text-xs rounded transition-colors">Crear</button>
                <button @click="showNewFolder = false; newForm.reset()" type="button" class="px-2 py-1 text-slate-400 hover:text-slate-100 text-xs transition-colors">Cancelar</button>
            </div>
        </div>

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

        <!-- Folder tree -->
        <template v-for="item in orderedFolders" :key="item.id">

            <!-- Edit mode -->
            <div v-if="editingId === item.id" class="flex flex-col gap-2 p-2 bg-surface-800 border border-violet-500/40 rounded-lg" :style="{ marginLeft: item.depth * 12 + 'px' }">
                <input
                    v-model="editForm.nombre"
                    type="text"
                    class="w-full bg-surface-900 border border-slate-700 rounded px-2 py-1 text-sm text-slate-100 focus:outline-none focus:border-violet-500"
                    autofocus
                />
                <!-- Color picker -->
                <div class="flex items-center gap-1 flex-wrap">
                    <button
                        type="button"
                        @click="editForm.color = null"
                        class="w-5 h-5 rounded-full border-2 transition-all bg-transparent"
                        :class="editForm.color === null ? 'border-slate-300' : 'border-slate-600 hover:border-slate-400'"
                        title="Sin color"
                    />
                    <button
                        v-for="c in COLORS"
                        :key="c.value"
                        type="button"
                        @click="editForm.color = c.value"
                        class="w-5 h-5 rounded-full border-2 transition-all"
                        :style="{ backgroundColor: c.value }"
                        :class="editForm.color === c.value ? 'border-white scale-110' : 'border-transparent hover:border-slate-300'"
                        :title="c.label"
                    />
                </div>
                <div class="flex gap-1">
                    <button @click="submitEdit(item)" type="button" class="flex-1 px-2 py-1 bg-violet-600 hover:bg-violet-500 text-white text-xs rounded transition-colors">Guardar</button>
                    <button @click="cancelEdit" type="button" class="px-2 py-1 text-slate-400 hover:text-slate-100 text-xs transition-colors">Cancelar</button>
                </div>
            </div>

            <!-- Normal mode -->
            <div
                v-else
                class="group flex items-center gap-1.5 px-2 py-2 rounded text-sm transition-colors cursor-pointer"
                :style="{ paddingLeft: (item.depth * 12 + 8) + 'px' }"
                :class="activeFolderId == item.id
                    ? 'bg-violet-600/10 text-violet-400 font-medium'
                    : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-100'"
                @click="selectFolder(item.id)"
            >
                <!-- Collapse toggle -->
                <button
                    v-if="hasChildren(item.id)"
                    @click.stop="toggleCollapse(item.id)"
                    class="flex-shrink-0 text-slate-500 hover:text-slate-300 transition-colors"
                >
                    <svg
                        class="w-3 h-3 transition-transform"
                        :class="collapsedFolders.has(item.id) ? '-rotate-90' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <span v-else class="w-3 flex-shrink-0" />

                <!-- Color dot or folder icon -->
                <span
                    v-if="item.color"
                    class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: item.color }"
                />
                <svg v-else class="w-4 h-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>

                <span class="flex-1 truncate">{{ item.nombre }}</span>

                <span class="text-xs text-slate-600 flex-shrink-0">{{ item.notes_count || '' }}</span>

                <!-- Actions (visible on hover) -->
                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                    <button
                        @click.stop="startEdit(item)"
                        class="p-0.5 text-slate-500 hover:text-violet-400 transition-colors"
                        title="Editar carpeta"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button
                        @click.stop="deleteFolder(item)"
                        class="p-0.5 text-slate-500 hover:text-red-400 transition-colors"
                        title="Eliminar carpeta"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <p v-if="!folders.length" class="text-xs text-slate-600 px-3 mt-1">Sin carpetas</p>
    </aside>
</template>
