<script setup lang="ts">
import { computed, h } from 'vue'
import { layout } from '@/composables/useLayout'

import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout  from '@/layouts/app/AppSidebarLayout.vue'

import type { BreadcrumbItemType } from '@/types';


interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const CurrentLayout = computed(() =>
  layout.value === 'sidebar' ? AppSidebarLayout : AppHeaderLayout,
)
</script>

<template>
  <component :is="CurrentLayout" :breadcrumbs="breadcrumbs">
    <slot />
  </component>
</template>
