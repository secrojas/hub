<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <h2 class="text-xl font-semibold text-slate-100 mb-1">Confirm your password</h2>
        <p class="text-sm text-slate-400 mb-6">
            This is a secure area. Please confirm your password before continuing.
        </p>

        <form @submit.prevent="submit">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6 flex justify-end">
                <Button
                    type="submit"
                    variant="primary"
                    size="md"
                    :disabled="form.processing"
                >
                    Confirm
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
