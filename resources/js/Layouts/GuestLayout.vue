<script setup>
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
</script>

<template>
    <div class="min-h-screen bg-surface-950 flex flex-col items-center justify-center px-4">
        <!-- Brand area -->
        <div class="mb-8 text-center">
            <Link href="/">
                <img src="/images/logo.png" alt="srojas" class="h-8 brightness-0 invert mx-auto" />
            </Link>
            <p class="mt-3 text-sm text-slate-500">Your workspace, organised</p>
        </div>

        <!-- Form card -->
        <div class="w-full max-w-md">
            <Card variant="glass" padding="lg">
                <slot />
            </Card>
        </div>
    </div>
</template>
