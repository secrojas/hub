<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import FolderSidebar from '@/Components/Notes/FolderSidebar.vue'
import NotesList from '@/Components/Notes/NotesList.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    notes: Array,
    folders: Array,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

let debounce = null
watch(search, (val) => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.get(route('notes.index'), { search: val, folder_id: props.filters?.folder_id }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, 300)
})
</script>

<template>
    <div class="flex flex-col gap-6">
        <PageHeader title="Notas" subtitle="Base de conocimiento interna">
            <Link :href="route('notes.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva nota
            </Link>
        </PageHeader>

        <!-- Search -->
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
            </svg>
            <input
                v-model="search"
                type="text"
                placeholder="Buscar notas..."
                class="w-full bg-surface-800 border border-slate-700 rounded-lg pl-9 pr-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
            />
        </div>

        <!-- Body: sidebar + list -->
        <div class="flex gap-6 items-start">
            <FolderSidebar
                :folders="folders"
                :active-folder-id="filters?.folder_id"
                :filters="filters"
            />

            <div class="flex-1 min-w-0">
                <NotesList :notes="notes" :active-note-id="null" />
            </div>
        </div>
    </div>
</template>
