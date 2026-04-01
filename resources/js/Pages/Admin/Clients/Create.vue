<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { defineOptions } from 'vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
    nombre: '',
    email: '',
    empresa: '',
    telefono: '',
    stack_tecnologico: '',
    estado: 'activo',
    notas: '',
    fecha_inicio: '',
})

function submit() {
    form.post('/clients', { preserveScroll: true })
}
</script>

<template>
    <Head title="Nuevo Cliente" />
    <div class="max-w-2xl">
        <PageHeader title="Nuevo Cliente" subtitle="Registra un nuevo cliente en el sistema">
            <Link href="/clients">
                <Button variant="ghost" size="sm">Cancelar</Button>
            </Link>
        </PageHeader>

        <Card variant="default" padding="lg">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-300 mb-1">Nombre <span class="text-red-400">*</span></label>
                    <input id="nombre" v-model="form.nombre" type="text" class="w-full" />
                    <p v-if="form.errors.nombre" class="text-red-400 text-sm mt-1">{{ form.errors.nombre }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email <span class="text-red-400">*</span></label>
                    <input id="email" v-model="form.email" type="email" class="w-full" />
                    <p v-if="form.errors.email" class="text-red-400 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="empresa" class="block text-sm font-medium text-slate-300 mb-1">Empresa</label>
                    <input id="empresa" v-model="form.empresa" type="text" class="w-full" />
                    <p v-if="form.errors.empresa" class="text-red-400 text-sm mt-1">{{ form.errors.empresa }}</p>
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-slate-300 mb-1">Telefono</label>
                    <input id="telefono" v-model="form.telefono" type="text" class="w-full" />
                    <p v-if="form.errors.telefono" class="text-red-400 text-sm mt-1">{{ form.errors.telefono }}</p>
                </div>

                <div>
                    <label for="stack_tecnologico" class="block text-sm font-medium text-slate-300 mb-1">Stack Tecnologico</label>
                    <textarea id="stack_tecnologico" v-model="form.stack_tecnologico" rows="3" class="w-full"></textarea>
                    <p v-if="form.errors.stack_tecnologico" class="text-red-400 text-sm mt-1">{{ form.errors.stack_tecnologico }}</p>
                </div>

                <div>
                    <label for="estado" class="block text-sm font-medium text-slate-300 mb-1">Estado</label>
                    <select id="estado" v-model="form.estado" class="w-full">
                        <option value="activo">Activo</option>
                        <option value="potencial">Potencial</option>
                        <option value="pausado">Pausado</option>
                    </select>
                    <p v-if="form.errors.estado" class="text-red-400 text-sm mt-1">{{ form.errors.estado }}</p>
                </div>

                <div>
                    <label for="notas" class="block text-sm font-medium text-slate-300 mb-1">Notas</label>
                    <textarea id="notas" v-model="form.notas" rows="4" class="w-full"></textarea>
                    <p v-if="form.errors.notas" class="text-red-400 text-sm mt-1">{{ form.errors.notas }}</p>
                </div>

                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-slate-300 mb-1">Fecha de Inicio</label>
                    <input id="fecha_inicio" v-model="form.fecha_inicio" type="date" class="w-full" />
                    <p v-if="form.errors.fecha_inicio" class="text-red-400 text-sm mt-1">{{ form.errors.fecha_inicio }}</p>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <Button type="submit" variant="primary" :disabled="form.processing">Guardar</Button>
                    <Link href="/clients" class="text-sm text-slate-400 hover:text-slate-200">Cancelar</Link>
                </div>
            </form>
        </Card>
    </div>
</template>
