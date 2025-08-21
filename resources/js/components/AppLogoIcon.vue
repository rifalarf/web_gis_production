<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 708.66 708.66" :class="className" v-bind="$attrs">
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M0,456.2v252.46l35-.04c0-13.33,2.06-24.9,4.28-38.52,15.21-97,136.26-166.21,246.73-119.31,51.1,21.7,89,68.94,96.96,119.67,2.25,14.34.89,24.36,1.28,38.19h324.42v-234.54l-39.01.04c-1.96,18.26-.34,28.37-7.85,56.56-17.36,45.52-60.85,79.54-116.88,84.31-81.76,6.96-146-49.18-150.62-115.18-1.81-25.82-.33-94.52-.33-124.99V0H107.26l.05,47.68c41.33,1.66,70.85,7.35,102.55,22.29,126.03,59.39,161.57,202,79.76,302.63-37.24,45.81-95.76,77.68-162.24,82.76-24.4,1.87-98.17.84-127.37.84h0Z"
        />
    </svg>
</template>
