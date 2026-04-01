<script setup>
const props = defineProps({
  label:  { type: String, required: true },
  value:  { type: [String, Number], required: true },
  accent: { type: String, default: 'violet' }, // 'violet' | 'cyan' | 'blue'
})

const accentClasses = {
  violet: 'from-violet-500 to-violet-600',
  cyan:   'from-cyan-400 to-cyan-500',
  blue:   'from-blue-500 to-blue-600',
}

const glowClasses = {
  violet: 'shadow-glow-violet',
  cyan:   'shadow-glow-cyan',
  blue:   'shadow-glow-blue',
}
</script>

<template>
  <div :class="['bg-surface-800 rounded-xl border border-slate-700/50 p-5 flex flex-col gap-2', glowClasses[accent]]">
    <div :class="['w-1 h-8 rounded-full bg-gradient-to-b self-start', accentClasses[accent]]" />
    <p class="text-sm text-slate-400 font-medium">{{ label }}</p>
    <p class="text-2xl font-semibold text-slate-100 tracking-tight">{{ value }}</p>
  </div>
</template>
