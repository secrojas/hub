<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    notes: Array,
    activeNoteId: [Number, null],
})
</script>

<template>
    <div class="flex flex-col gap-2">
        <p v-if="!notes.length" class="text-sm text-slate-500 px-4 py-10 text-center">
            Sin notas para mostrar.
        </p>

        <Link
            v-for="note in notes"
            :key="note.id"
            :href="route('notes.show', note.id)"
            class="group relative flex flex-col gap-1.5 px-4 py-3.5 rounded-lg border transition-all cursor-pointer overflow-hidden"
            :class="activeNoteId === note.id
                ? 'bg-violet-600/10 border-violet-500/40'
                : 'bg-surface-800 border-slate-700/40 hover:border-slate-600 hover:bg-surface-700'"
        >
            <!-- Left accent bar (folder color or pinned) -->
            <span
                v-if="note.folder?.color || note.esta_fijada"
                class="absolute left-0 top-0 bottom-0 w-0.5 rounded-l-lg"
                :style="note.folder?.color
                    ? { backgroundColor: note.folder.color }
                    : { backgroundColor: '#f59e0b' }"
            />

            <!-- Title row -->
            <div class="flex items-center gap-2">
                <svg v-if="note.esta_fijada" class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <svg v-if="note.en_dashboard" class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                <span class="text-sm font-semibold text-slate-100 truncate leading-snug">{{ note.titulo }}</span>
            </div>

            <!-- Excerpt -->
            <p v-if="note.extracto" class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                {{ note.extracto }}
            </p>

            <!-- Meta row -->
            <div class="flex items-center gap-2 mt-0.5">
                <span
                    v-if="note.folder"
                    class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded-md font-medium"
                    :style="note.folder.color
                        ? { backgroundColor: note.folder.color + '22', color: note.folder.color }
                        : {}"
                    :class="!note.folder.color ? 'text-slate-500 bg-slate-800' : ''"
                >
                    <span
                        v-if="note.folder.color"
                        class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                        :style="{ backgroundColor: note.folder.color }"
                    />
                    {{ note.folder.nombre }}
                </span>
                <span class="text-xs text-slate-600 ml-auto">
                    {{ new Date(note.updated_at).toLocaleDateString('es-AR') }}
                </span>
            </div>
        </Link>
    </div>
</template>
