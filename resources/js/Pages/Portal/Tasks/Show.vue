<script setup>
import { Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'

defineOptions({ layout: PortalLayout })

defineProps({
    task: Object,
})

const priorityLabel = { alta: 'Alta', media: 'Media', baja: 'Baja' }
const priorityColor = { alta: 'text-red-400', media: 'text-amber-400', baja: 'text-green-400' }

function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-AR', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

<template>
    <div class="max-w-2xl mx-auto flex flex-col gap-6">
        <!-- Back -->
        <Link href="/portal" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-slate-100 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al portal
        </Link>

        <!-- Title + status -->
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl font-bold text-slate-100 leading-tight">{{ task.titulo }}</h1>
            <Badge :variant="task.estado" class="flex-shrink-0 mt-1" />
        </div>

        <!-- Meta grid -->
        <Card variant="glass" padding="md">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Prioridad</dt>
                    <dd class="text-sm font-semibold" :class="priorityColor[task.prioridad] ?? 'text-slate-300'">
                        {{ priorityLabel[task.prioridad] ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Fecha límite</dt>
                    <dd class="text-sm text-slate-300">{{ formatDate(task.fecha_limite) }}</dd>
                </div>
                <div v-if="task.horas">
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Horas estimadas</dt>
                    <dd class="text-sm text-slate-300">{{ task.horas }}h</dd>
                </div>
                <div v-if="task.source_url">
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Referencia</dt>
                    <dd class="text-sm">
                        <a :href="task.source_url" target="_blank" rel="noopener" class="text-cyan-400 hover:text-cyan-300 underline underline-offset-2 break-all">
                            Ver enlace
                        </a>
                    </dd>
                </div>
            </dl>
        </Card>

        <!-- Description -->
        <Card v-if="task.descripcion" variant="default" padding="md">
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Descripción</h2>
            <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">{{ task.descripcion }}</p>
        </Card>

        <!-- Comments -->
        <div v-if="task.comments?.length">
            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Actualizaciones</h2>
            <div class="flex flex-col gap-3">
                <Card
                    v-for="comment in task.comments"
                    :key="comment.id"
                    variant="default"
                    padding="sm"
                >
                    <p class="text-sm text-slate-300 leading-relaxed">{{ comment.body }}</p>
                    <p class="text-xs text-slate-500 mt-2">
                        {{ formatDate(comment.created_at) }}
                    </p>
                </Card>
            </div>
        </div>
    </div>
</template>
