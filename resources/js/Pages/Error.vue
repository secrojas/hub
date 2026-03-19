<script setup>
const props = defineProps({
    status: Number,
    message: String,
})

const messages = {
    403: 'No tenes acceso a esta pagina.',
    404: 'La pagina que buscas no existe.',
    500: 'Error interno del servidor.',
    503: 'Servicio no disponible temporalmente.',
}

const displayMessage = props.message || messages[props.status] || 'Ha ocurrido un error.'
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-300">{{ status }}</h1>
            <p class="mt-4 text-xl text-gray-600">{{ displayMessage }}</p>
            <a href="/" class="mt-6 inline-block text-blue-600 hover:text-blue-800 underline">
                Volver al inicio
            </a>
        </div>
    </div>
</template>
