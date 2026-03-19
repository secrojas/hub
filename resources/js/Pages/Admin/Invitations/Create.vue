<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })

const page = usePage()
const invitationUrl = computed(() => page.props.flash.invitation_url)

const form = useForm({
    email: '',
    client_name: '',
})

function submit() {
    form.post('/invitations', {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Invitar Cliente" />
    <div class="max-w-lg">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Invitar Cliente</h1>

        <!-- Show generated link -->
        <div v-if="invitationUrl" class="mb-6 p-4 bg-green-50 border border-green-200 rounded">
            <p class="text-sm text-green-800 font-medium mb-2">Link de invitacion generado:</p>
            <input
                type="text"
                :value="invitationUrl"
                readonly
                class="w-full p-2 text-sm bg-white border rounded font-mono"
                @click="$event.target.select()"
            />
            <p class="text-xs text-green-600 mt-1">Copia este link y envialo al cliente. Expira en 72 horas.</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del cliente</label>
                <input id="client_name" v-model="form.client_name" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.client_name" class="text-sm text-red-600 mt-1">{{ form.errors.client_name }}</p>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email del cliente</label>
                <input id="email" v-model="form.email" type="email" class="w-full border-gray-300 rounded-md shadow-sm" />
                <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
            </div>

            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                Generar Link de Invitacion
            </button>
        </form>
    </div>
</template>
