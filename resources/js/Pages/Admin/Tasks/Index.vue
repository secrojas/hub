<script setup>
import { ref } from 'vue'
import { router, useForm, Link, Head } from '@inertiajs/vue3'
import { VueDraggable } from 'vue-draggable-plus'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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

function prioridadBadgeClass(prioridad) {
    if (prioridad === 'alta') return 'bg-red-100 text-red-800'
    if (prioridad === 'media') return 'bg-yellow-100 text-yellow-800'
    return 'bg-green-100 text-green-800'
}
</script>

<template>
    <Head title="Tareas" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Tareas</h1>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium"
            >
                Nueva tarea
            </button>
        </div>

        <!-- Filter bar -->
        <div class="mb-6 flex flex-wrap gap-3 items-end">
            <!-- Client dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cliente</label>
                <select v-model="filters.cliente" @change="applyFilters" class="border-gray-300 rounded text-sm">
                    <option value="">Todos</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
            </div>
            <!-- Estado dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select v-model="filters.estado" @change="applyFilters" class="border-gray-300 rounded text-sm">
                    <option value="">Todos</option>
                    <option value="backlog">Backlog</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="en_revision">En revision</option>
                    <option value="finalizado">Finalizado</option>
                </select>
            </div>
            <!-- Prioridad dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Prioridad</label>
                <select v-model="filters.prioridad" @change="applyFilters" class="border-gray-300 rounded text-sm">
                    <option value="">Todas</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <!-- Titulo search -->
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                <input
                    v-model="filters.titulo"
                    @input="onTituloInput"
                    type="text"
                    placeholder="Buscar por titulo..."
                    class="border-gray-300 rounded text-sm w-48"
                />
            </div>
        </div>

        <!-- Kanban board: 4 columns -->
        <div class="flex gap-4 overflow-x-auto pb-4 items-start">
            <div
                v-for="(label, status) in columnLabels"
                :key="status"
                class="flex-shrink-0 w-72 bg-gray-50 rounded-lg p-3"
            >
                <!-- Column header -->
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
                    {{ label }}
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ localColumns[status].length }})</span>
                </h2>

                <!-- Draggable column -->
                <VueDraggable
                    v-model="localColumns[status]"
                    :group="{ name: 'tasks', pull: true, put: true }"
                    item-key="id"
                    class="min-h-16 space-y-2"
                    @add="(e) => onColumnAdd(status, e)"
                >
                    <div
                        v-for="task in localColumns[status]"
                        :key="task.id"
                        @click="openEditModal(task)"
                        class="bg-white rounded shadow-sm p-3 cursor-pointer hover:shadow-md transition-shadow"
                    >
                        <p class="text-sm font-medium text-gray-900 mb-1">{{ task.titulo }}</p>
                        <p class="text-xs text-gray-500 mb-2">{{ task.client?.nombre }}</p>
                        <div class="flex items-center justify-between">
                            <span
                                v-if="task.prioridad"
                                :class="['px-2 py-0.5 text-xs font-medium rounded-full', prioridadBadgeClass(task.prioridad)]"
                            >
                                {{ task.prioridad }}
                            </span>
                            <span v-if="task.fecha_limite" class="text-xs text-gray-400">
                                {{ task.fecha_limite.substring(0, 10) }}
                            </span>
                        </div>
                    </div>
                </VueDraggable>
            </div>
        </div>
    </div>

    <!-- Create modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Nueva tarea</h2>
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                    <input
                        v-model="createForm.titulo"
                        type="text"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                        placeholder="Titulo de la tarea"
                    />
                    <p v-if="createForm.errors.titulo" class="mt-1 text-xs text-red-600">{{ createForm.errors.titulo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select v-model="createForm.client_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option :value="null">Seleccionar cliente...</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                    </select>
                    <p v-if="createForm.errors.client_id" class="mt-1 text-xs text-red-600">{{ createForm.errors.client_id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                    <textarea
                        v-model="createForm.descripcion"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                    <select v-model="createForm.prioridad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha limite</label>
                    <input
                        v-model="createForm.fecha_limite"
                        type="date"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        Crear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit modal -->
    <div v-if="editingTask" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Editar tarea</h2>
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                    <input
                        v-model="editForm.titulo"
                        type="text"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                    <p v-if="editForm.errors.titulo" class="mt-1 text-xs text-red-600">{{ editForm.errors.titulo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select v-model="editForm.client_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option :value="null">Seleccionar cliente...</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nombre }}</option>
                    </select>
                    <p v-if="editForm.errors.client_id" class="mt-1 text-xs text-red-600">{{ editForm.errors.client_id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                    <textarea
                        v-model="editForm.descripcion"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                    <select v-model="editForm.prioridad" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha limite</label>
                    <input
                        v-model="editForm.fecha_limite"
                        type="date"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                    />
                </div>
                <div class="flex justify-between pt-2">
                    <button
                        type="button"
                        @click="confirmDeleteTask"
                        class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                    >
                        Eliminar
                    </button>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="editingTask = null"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
