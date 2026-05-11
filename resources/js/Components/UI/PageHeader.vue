<script setup>
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: null },
})
</script>

<template>
  <div class="flex flex-wrap items-start justify-between gap-y-3 gap-x-4 mb-6">
    <div>
      <h1 class="text-xl md:text-2xl font-semibold text-slate-100 tracking-tight">{{ title }}</h1>
      <p v-if="subtitle" class="mt-1 text-sm text-slate-400">{{ subtitle }}</p>
    </div>
    <div v-if="$slots.default" class="flex items-center gap-3 flex-shrink-0">
      <slot />
    </div>
  </div>
</template>
