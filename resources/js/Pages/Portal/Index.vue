<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { Head } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: PortalLayout })
</script>

<template>
    <Head title="Portal" />
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Mi Portal</h1>
        <p class="mt-2 text-gray-600">Bienvenido a tu portal de cliente.</p>
    </div>
</template>
