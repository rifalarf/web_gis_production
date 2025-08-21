<script setup lang="ts">
defineProps<{
  icon : string   // SVG path string
  label: string
  value: string | number
}>()
</script>

<template>
  <div
    class="flex flex-col items-center justify-center gap-2
         rounded-xl border p-6 dark:border-sidebar-border
         w-full h-40"
  >
    <slot name="icon">
      <svg v-if="icon" viewBox="0 0 24 24" class="h-10 w-10 fill-current text-gray-600">
        <path :d="icon" />
      </svg>
    </slot>
    <div class="text-xs text-gray-500">{{ label }}</div>
    <div class="text-lg font-semibold">{{ value }}</div>
  </div>
</template>
