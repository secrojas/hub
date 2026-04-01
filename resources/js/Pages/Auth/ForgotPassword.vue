<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <h2 class="text-xl font-semibold text-slate-100 mb-1">Reset your password</h2>
        <p class="text-sm text-slate-400 mb-6">
            Enter your email and we'll send you a link to reset your password.
        </p>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6 flex items-center justify-end">
                <Button
                    type="submit"
                    variant="primary"
                    size="md"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
