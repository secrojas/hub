<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

defineOptions({ layout: GuestLayout })

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
    form.post(props.accept_url, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Aceptar Invitacion" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-xl font-semibold text-slate-100">Bienvenido a Hub</h2>
            <p class="mt-1 text-sm text-slate-400">Creá tu contraseña para acceder al portal.</p>
        </div>

        <!-- Read-only info -->
        <div class="flex flex-col gap-3">
            <div>
                <InputLabel value="Nombre" />
                <input
                    type="text"
                    :value="client_name"
                    disabled
                    class="mt-1 w-full bg-surface-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed"
                />
            </div>
            <div>
                <InputLabel value="Email" />
                <input
                    type="email"
                    :value="email"
                    disabled
                    class="mt-1 w-full bg-surface-900 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed"
                />
            </div>
        </div>

        <div class="border-t border-slate-700/60" />

        <!-- Password form -->
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <div>
                <InputLabel for="password" value="Contraseña" />
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Mínimo 8 caracteres"
                    class="mt-1 w-full bg-surface-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
                    required
                    autofocus
                />
                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar contraseña" />
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Repetí la contraseña"
                    class="mt-1 w-full bg-surface-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-violet-500 transition-colors"
                    required
                />
                <InputError :message="form.errors.password_confirmation" class="mt-1" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full mt-1 py-2.5 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors shadow-glow-violet"
            >
                {{ form.processing ? 'Creando cuenta...' : 'Crear mi cuenta' }}
            </button>
        </form>
    </div>
</template>
