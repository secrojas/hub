<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    email: String,
    client_name: String,
    token: String,
    accept_url: String,
})

const form = useForm({
    token: props.token,
    password: '',
    password_confirmation: '',
})

function submit() {
    // POST to the full signed URL (accept_url) to preserve signature params
    form.post(props.accept_url, {
        onFinish: () => {
            form.reset('password', 'password_confirmation')
        },
    })
}
</script>

<template>
    <Head title="Aceptar Invitacion" />
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">Bienvenido a Hub</h1>
            <p class="text-gray-600 mb-6">Completa tu registro para acceder al portal.</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" :value="client_name" disabled class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" :value="email" disabled class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50" />
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contrasena</label>
                    <input id="password" v-model="form.password" type="password" class="w-full border-gray-300 rounded-md shadow-sm" required />
                    <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasena</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="w-full border-gray-300 rounded-md shadow-sm" required />
                </div>

                <button type="submit" :disabled="form.processing" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    Crear mi cuenta
                </button>
            </form>
        </div>
    </div>
</template>
