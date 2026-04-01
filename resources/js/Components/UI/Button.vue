<script setup>
const props = defineProps({
  variant:  { type: String, default: 'primary' }, // 'primary' | 'secondary' | 'danger' | 'ghost'
  size:     { type: String, default: 'md' },       // 'sm' | 'md' | 'lg'
  disabled: { type: Boolean, default: false },
  type:     { type: String, default: 'button' },
})

const variantClasses = {
  primary:   'bg-gradient-to-r from-violet-600 to-blue-600 hover:from-violet-500 hover:to-blue-500 text-white btn-glow',
  secondary: 'bg-slate-700 hover:bg-slate-600 text-slate-200 border border-slate-600',
  danger:    'bg-red-600 hover:bg-red-500 text-white',
  ghost:     'text-slate-400 hover:text-slate-200 hover:bg-slate-800',
}

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-2.5 text-sm',
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[
      'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200',
      'focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-surface-900',
      variantClasses[variant],
      sizeClasses[size],
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
    ]"
  >
    <slot />
  </button>
</template>
