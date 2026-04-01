<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <h2 class="text-xl font-semibold text-slate-100 mb-1">Create an account</h2>
        <p class="text-sm text-slate-400 mb-6">Get started with Hub today</p>

        <form @submit.prevent="submit">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Name</label>

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">
                    Confirm Password
                </label>

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-sm text-violet-400 hover:text-violet-300 transition-colors"
                >
                    Already registered?
                </Link>

                <Button
                    type="submit"
                    variant="primary"
                    size="md"
                    :disabled="form.processing"
                >
                    Register
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
