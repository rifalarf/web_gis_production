<script setup lang="ts">
import { layout, updateLayout } from '@/composables/useLayout'
import { PanelTop, PanelLeft } from 'lucide-vue-next'


const tabs = [
  { value: 'header', Icon: PanelTop,    label: 'Header'  },
  { value: 'sidebar', Icon: PanelLeft, label: 'Sidebar' },
] as const
</script>

<template>
  <div
    class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1
           dark:bg-neutral-800"
  >
    <button
      v-for="{ value, Icon, label } in tabs"
      :key="value"
      @click="updateLayout(value)"
      :class="[
        'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
        layout === value
          ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
          : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black  \
             dark:text-neutral-400 dark:hover:bg-neutral-700/60',
      ]"
    >
      <component :is="Icon" class="-ml-1 h-4 w-4" />
      <span class="ml-1.5 text-sm">{{ label }}</span>
    </button>
  </div>
</template>
