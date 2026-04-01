<script setup>
const props = defineProps({
  variant: { type: String, default: 'default' }, // 'default' | 'glass' | 'elevated'
  padding: { type: String, default: 'md' },       // 'sm' | 'md' | 'lg' | 'none'
  glow:    { type: String, default: null },        // 'violet' | 'cyan' | 'blue' | null
})

const variantClasses = {
  default:  'bg-surface-800 border border-slate-700/50',
  glass:    'glass',
  elevated: 'bg-surface-700 border border-slate-600/50',
}

const paddingClasses = {
  sm:   'p-3',
  md:   'p-5',
  lg:   'p-6',
  none: '',
}

const glowClasses = {
  violet: 'shadow-glow-violet',
  cyan:   'shadow-glow-cyan',
  blue:   'shadow-glow-blue',
}
</script>

<template>
  <div
    :class="[
      'rounded-xl transition-shadow duration-200',
      variantClasses[variant],
      paddingClasses[padding],
      glow ? glowClasses[glow] : '',
    ]"
  >
    <slot />
  </div>
</template>
