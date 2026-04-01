<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <h2 class="text-xl font-semibold text-slate-100 mb-1">Verify your email</h2>
        <p class="text-sm text-slate-400 mb-6">
            Thanks for signing up! Before getting started, please verify your email address
            by clicking the link we just sent you. If you didn't receive it, we can resend it.
        </p>

        <div class="mb-4 text-sm font-medium text-green-400" v-if="verificationLinkSent">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-between gap-4">
                <Button
                    type="submit"
                    variant="primary"
                    size="md"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-violet-400 hover:text-violet-300 transition-colors"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
