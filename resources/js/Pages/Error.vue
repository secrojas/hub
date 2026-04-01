<script setup>
import Button from '@/Components/UI/Button.vue';

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
    <div class="min-h-screen bg-surface-950 flex items-center justify-center px-4">
        <div class="text-center">
            <h1 class="text-8xl font-bold text-gradient leading-none">{{ status }}</h1>
            <p class="mt-6 text-lg text-slate-400">{{ displayMessage }}</p>
            <div class="mt-8">
                <a href="/">
                    <Button variant="secondary" size="md">
                        Volver al inicio
                    </Button>
                </a>
            </div>
        </div>
    </div>
</template>
