<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const page = usePage()
const invitationUrl = computed(() => page.props.flash.invitation_url)

const form = useForm({
    email: '',
    client_name: '',
})

function submit() {
    form.post('/invitations', {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Invitar Cliente" />
    <div class="max-w-lg">
        <PageHeader title="Invitar Cliente" subtitle="Genera un enlace de acceso al portal para un cliente" />

        <!-- Show generated link -->
        <div v-if="invitationUrl" class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
            <p class="text-sm text-green-400 font-medium mb-2">Link de invitacion generado:</p>
            <input
                type="text"
                :value="invitationUrl"
                readonly
                class="w-full p-2 text-sm font-mono rounded-lg cursor-pointer"
                @click="$event.target.select()"
            />
            <p class="text-xs text-green-500/70 mt-1">Copia este link y envialo al cliente. Expira en 72 horas.</p>
        </div>

        <Card variant="default" padding="lg">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="client_name" class="block text-sm font-medium text-slate-300 mb-1">Nombre del cliente</label>
                    <input id="client_name" v-model="form.client_name" type="text" class="w-full" />
                    <p v-if="form.errors.client_name" class="text-red-400 text-sm mt-1">{{ form.errors.client_name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email del cliente</label>
                    <input id="email" v-model="form.email" type="email" class="w-full" />
                    <p v-if="form.errors.email" class="text-red-400 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="pt-2">
                    <Button type="submit" variant="primary" :disabled="form.processing">Generar Link de Invitacion</Button>
                </div>
            </form>
        </Card>
    </div>
</template>
