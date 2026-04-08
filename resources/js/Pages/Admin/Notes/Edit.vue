<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import NoteEditor from '@/Components/Notes/NoteEditor.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    note: Object,
    folders: Array,
})

const form = useForm({
    titulo: props.note.titulo,
    contenido: props.note.contenido ?? '',
    folder_id: props.note.folder_id,
    esta_fijada: props.note.esta_fijada,
})

function submit() {
    form.put(route('notes.update', props.note.id))
}
</script>

<template>
    <div class="flex flex-col gap-6 max-w-4xl">
        <PageHeader :title="`Editar: ${note.titulo}`" subtitle="">
            <Link :href="route('notes.show', note.id)" class="text-sm text-slate-400 hover:text-slate-100 transition-colors">
                Cancelar
            </Link>
        </PageHeader>

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <!-- Title -->
            <div>
                <InputLabel value="Título" />
                <input
                    v-model="form.titulo"
                    type="text"
                    class="mt-1 w-full bg-surface-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
                />
                <InputError :message="form.errors.titulo" class="mt-1" />
            </div>

            <!-- Folder + Pin row -->
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <InputLabel value="Carpeta" />
                    <select
                        v-model="form.folder_id"
                        class="mt-1 w-full bg-surface-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-violet-500 transition-colors"
                    >
                        <option :value="null">Sin carpeta</option>
                        <option v-for="folder in folders" :key="folder.id" :value="folder.id">
                            {{ folder.nombre }}
                        </option>
                    </select>
                </div>

                <label class="flex items-center gap-2 cursor-pointer mt-5">
                    <input v-model="form.esta_fijada" type="checkbox" class="rounded border-slate-700 bg-surface-800 text-violet-600 focus:ring-violet-500" />
                    <span class="text-sm text-slate-400">Fijar nota</span>
                </label>
            </div>

            <!-- Editor -->
            <div>
                <InputLabel value="Contenido" class="mb-1" />
                <NoteEditor v-model="form.contenido" />
                <InputError :message="form.errors.contenido" class="mt-1" />
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Actualizar nota
                </button>
            </div>
        </form>
    </div>
</template>
