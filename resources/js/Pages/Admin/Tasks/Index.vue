<script setup>
import { ref } from 'vue'
import { router, useForm, Link, Head } from '@inertiajs/vue3'
import { VueDraggable } from 'vue-draggable-plus'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    columns: Object,
    clients: Array,
    filtros: Object,
})

// Filter state — initialized from server-side filtros prop
const filters = ref({
    cliente:   props.filtros?.cliente ?? '',
    estado:    props.filtros?.estado ?? '',
    prioridad: props.filtros?.prioridad ?? '',
    titulo:    props.filtros?.titulo ?? '',
})

function applyFilters() {
    // Strip empty values so URL stays clean
    const params = Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== '' && v !== null)
    )
    router.get('/tasks', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// Debounce titulo input to avoid excessive requests
let searchTimeout = null
function onTituloInput() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 300)
}

// Local columns state for optimistic update
const localColumns = ref({
    backlog:     [...(props.columns.backlog || [])],
    en_progreso: [...(props.columns.en_progreso || [])],
    en_revision: [...(props.columns.en_revision || [])],
    finalizado:  [...(props.columns.finalizado || [])],
})

const columnLabels = {
    backlog:     'Backlog',
    en_progreso: 'En progreso',
    en_revision: 'En revision',
    finalizado:  'Finalizado',
}

const columnColors = {
    backlog:     'bg-slate-500',
    en_progreso: 'bg-blue-500',
    en_revision: 'bg-amber-500',
    finalizado:  'bg-green-500',
}

// Drag handler — fires only when a card lands in this column from another
function onColumnAdd(newStatus, event) {
    const task = event.data
    const previousColumns = JSON.parse(JSON.stringify(localColumns.value))

    router.put(`/tasks/${task.id}/status`, { estado: newStatus }, {
        preserveScroll: true,
        onError: () => {
            localColumns.value = previousColumns
        },
    })
}

// Create modal
const showCreateModal = ref(false)
const createForm = useForm({
    titulo:       '',
    client_id:    null,
    descripcion:  '',
    prioridad:    'media',
    fecha_limite: '',
})

function openCreateModal() {
    createForm.reset()
    showCreateModal.value = true
}

function submitCreate() {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

// Edit modal — sentinel pattern
const editingTask = ref(null)
const editForm = useForm({
    titulo:       '',
    client_id:    null,
    descripcion:  '',
    prioridad:    'media',
    fecha_limite: '',
})

function openEditModal(task) {
    editingTask.value = task
    editForm.titulo       = task.titulo
    editForm.client_id    = task.client_id
    editForm.descripcion  = task.descripcion || ''
    editForm.prioridad    = task.prioridad || 'media'
    editForm.fecha_limite = task.fecha_limite ? task.fecha_limite.substring(0, 10) : ''
}

function submitEdit() {
    editForm.put(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTask.value = null
            editForm.reset()
        },
    })
}

function confirmDeleteTask() {
    if (!confirm('Eliminar esta tarea? Esta accion no se puede deshacer.')) return
    router.delete(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTask.value = null
        },
    })
}
</script>

<template>
    <Head title="Tareas" />

    <div>
        <!-- Header -->
        <PageHeader title="Tareas" subtitle="Kanban">
            <Button variant="primary" @click="openCreateModal">
                Nueva tarea
            </Button>
        </PageHeader>

        <!-- Filter bar -->
        <div class="glass rounded-xl px-4 py-3 mb-6 flex flex-wrap gap-3 items-end">
            <!-- Client dropdown -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Cliente</label>
                <select v-model="filters.cliente" @change="applyFilters" class="rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
            </div>
            <!-- Estado dropdown -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Estado</label>
                <select v-model="filters.estado" @change="applyFilters" class="rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="backlog">Backlog</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="en_revision">En revision</option>
                    <option value="finalizado">Finalizado</option>
                </select>
            </div>
            <!-- Prioridad dropdown -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Prioridad</label>
                <select v-model="filters.prioridad" @change="applyFilters" class="rounded-lg text-sm">
                    <option value="">Todas</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <!-- Titulo search -->
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Buscar</label>
                <input
                    v-model="filters.titulo"
                    @input="onTituloInput"
                    type="text"
                    placeholder="Buscar por titulo..."
                    class="rounded-lg text-sm w-48"
                />
            </div>
        </div>

        <!-- Kanban board: 4 columns -->
        <div class="flex gap-4 overflow-x-auto pb-4 items-start min-h-[calc(100vh-12rem)]">
            <div
                v-for="(label, status) in columnLabels"
                :key="status"
                class="flex flex-col w-72 flex-shrink-0"
            >
                <!-- Column header -->
                <div class="glass rounded-xl p-3 mb-2">
                    <div :class="['h-0.5 w-full rounded-full mb-3', columnColors[status]]"></div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-200 uppercase tracking-wider">
                            {{ label }}
                        </h2>
                        <Badge :variant="status" :label="String(localColumns[status].length)" />
                    </div>
                </div>

                <!-- Draggable column -->
                <VueDraggable
                    v-model="localColumns[status]"
                    :group="{ name: 'tasks', pull: true, put: true }"
                    item-key="id"
                    class="flex-1 space-y-2 min-h-[100px]"
                    @add="(e) => onColumnAdd(status, e)"
                >
                    <div
                        v-for="task in localColumns[status]"
                        :key="task.id"
                        @click="openEditModal(task)"
                        class="group glass glass-hover rounded-xl p-3 cursor-grab active:cursor-grabbing"
                    >
                        <p class="text-sm font-medium text-slate-100 mb-1">{{ task.titulo }}</p>
                        <p class="text-xs text-slate-400 mb-2">{{ task.client?.nombre }}</p>
                        <div class="flex items-center justify-between">
                            <Badge v-if="task.prioridad" :variant="task.prioridad" />
                            <span v-if="task.fecha_limite" class="text-xs text-slate-500">
                                {{ task.fecha_limite.substring(0, 10) }}
                            </span>
                        </div>
                    </div>
                </VueDraggable>
            </div>
        </div>
    </div>

    <!-- Create modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-surface-800 rounded-2xl shadow-2xl border border-slate-700/40 w-full max-w-lg mx-4">
            <!-- Modal header -->
            <div class="px-6 pt-6 pb-4 border-b border-slate-700/40">
                <h2 class="text-lg font-semibold text-slate-100">Nueva tarea</h2>
            </div>
            <!-- Modal body -->
            <form @submit.prevent="submitCreate">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Titulo *</label>
                        <input
                            v-model="createForm.titulo"
                            type="text"
                            class="w-full rounded-lg text-sm"
                            placeholder="Titulo de la tarea"
                        />
                        <p v-if="createForm.errors.titulo" class="mt-1 text-xs text-red-400">{{ createForm.errors.titulo }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Cliente *</label>
                        <select v-model="createForm.client_id" class="w-full rounded-lg text-sm">
                            <option :value="null">Seleccionar cliente...</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                        </select>
                        <p v-if="createForm.errors.client_id" class="mt-1 text-xs text-red-400">{{ createForm.errors.client_id }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Descripcion</label>
                        <textarea
                            v-model="createForm.descripcion"
                            rows="3"
                            class="w-full rounded-lg text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Prioridad</label>
                        <select v-model="createForm.prioridad" class="w-full rounded-lg text-sm">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Fecha limite</label>
                        <input
                            v-model="createForm.fecha_limite"
                            type="date"
                            class="w-full rounded-lg text-sm"
                        />
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="px-6 py-4 border-t border-slate-700/40 flex justify-end gap-3">
                    <Button type="button" variant="ghost" @click="showCreateModal = false">
                        Cancelar
                    </Button>
                    <Button type="submit" variant="primary" :disabled="createForm.processing">
                        Crear
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit modal -->
    <div v-if="editingTask" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-surface-800 rounded-2xl shadow-2xl border border-slate-700/40 w-full max-w-lg mx-4">
            <!-- Modal header -->
            <div class="px-6 pt-6 pb-4 border-b border-slate-700/40">
                <h2 class="text-lg font-semibold text-slate-100">Editar tarea</h2>
            </div>
            <!-- Modal body -->
            <form @submit.prevent="submitEdit">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Titulo *</label>
                        <input
                            v-model="editForm.titulo"
                            type="text"
                            class="w-full rounded-lg text-sm"
                        />
                        <p v-if="editForm.errors.titulo" class="mt-1 text-xs text-red-400">{{ editForm.errors.titulo }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Cliente *</label>
                        <select v-model="editForm.client_id" class="w-full rounded-lg text-sm">
                            <option :value="null">Seleccionar cliente...</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                        </select>
                        <p v-if="editForm.errors.client_id" class="mt-1 text-xs text-red-400">{{ editForm.errors.client_id }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Descripcion</label>
                        <textarea
                            v-model="editForm.descripcion"
                            rows="3"
                            class="w-full rounded-lg text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Prioridad</label>
                        <select v-model="editForm.prioridad" class="w-full rounded-lg text-sm">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-medium mb-1">Fecha limite</label>
                        <input
                            v-model="editForm.fecha_limite"
                            type="date"
                            class="w-full rounded-lg text-sm"
                        />
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="px-6 py-4 border-t border-slate-700/40 flex justify-between">
                    <Button type="button" variant="danger" @click="confirmDeleteTask">
                        Eliminar
                    </Button>
                    <div class="flex gap-3">
                        <Button type="button" variant="ghost" @click="editingTask = null">
                            Cancelar
                        </Button>
                        <Button type="submit" variant="primary" :disabled="editForm.processing">
                            Guardar
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
