<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, required: true },
  label:   { type: String, default: null },
})

const variantMap = {
  alta:         { class: 'bg-red-500/20 text-red-400',       text: 'Alta' },
  media:        { class: 'bg-amber-500/20 text-amber-400',   text: 'Media' },
  baja:         { class: 'bg-green-500/20 text-green-400',   text: 'Baja' },
  backlog:      { class: 'bg-slate-600/40 text-slate-400',   text: 'Backlog' },
  en_progreso:  { class: 'bg-blue-500/20 text-blue-400',     text: 'En progreso' },
  en_revision:  { class: 'bg-violet-500/20 text-violet-400', text: 'En revisión' },
  finalizado:   { class: 'bg-green-500/20 text-green-400',   text: 'Finalizado' },
  borrador:     { class: 'bg-slate-600/40 text-slate-400',   text: 'Borrador' },
  enviado:      { class: 'bg-blue-500/20 text-blue-400',     text: 'Enviado' },
  aceptado:     { class: 'bg-green-500/20 text-green-400',   text: 'Aceptado' },
  rechazado:    { class: 'bg-red-500/20 text-red-400',       text: 'Rechazado' },
  pendiente:    { class: 'bg-amber-500/20 text-amber-400',   text: 'Pendiente' },
  pagado:       { class: 'bg-green-500/20 text-green-400',   text: 'Pagado' },
  vencido:      { class: 'bg-red-500/20 text-red-400',       text: 'Vencido' },
  activo:       { class: 'bg-green-500/20 text-green-400',   text: 'Activo' },
  potencial:    { class: 'bg-cyan-500/20 text-cyan-400',     text: 'Potencial' },
  pausado:      { class: 'bg-slate-600/40 text-slate-400',   text: 'Pausado' },
  success:      { class: 'bg-green-500/20 text-green-400',   text: 'Success' },
  warning:      { class: 'bg-amber-500/20 text-amber-400',   text: 'Warning' },
  danger:       { class: 'bg-red-500/20 text-red-400',       text: 'Danger' },
  info:         { class: 'bg-blue-500/20 text-blue-400',     text: 'Info' },
  neutral:      { class: 'bg-slate-600/40 text-slate-400',   text: 'Neutral' },
}

const resolved = computed(() => variantMap[props.variant] ?? variantMap.neutral)
</script>

<template>
  <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', resolved.class]">
    {{ label ?? resolved.text }}
  </span>
</template>
