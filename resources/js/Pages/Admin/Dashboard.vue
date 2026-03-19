<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { defineOptions } from 'vue'

defineOptions({ layout: AdminLayout })
</script>

<template>
    <Head title="Dashboard" />
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Bienvenido al panel de administracion.</p>
    </div>
</template>
