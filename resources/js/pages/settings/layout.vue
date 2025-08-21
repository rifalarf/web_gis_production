<script setup lang="ts">
import AppLayout       from '@/layouts/AppLayout.vue'
import SettingsLayout  from '@/layouts/settings/Layout.vue'
import HeadingSmall    from '@/components/HeadingSmall.vue'
import LayoutTabs      from '@/components/LayoutTabs.vue'
import { Head }        from '@inertiajs/vue3'

import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Layout settings',
        href: '/settings/layout',
    },
];

</script>


<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Layout settings" />

    <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Pengaturan layout" description="Perbarui pengaturan layout Anda" />
                <AppearanceTabs />
            </div>
      <LayoutTabs />
    </SettingsLayout>
  </AppLayout>
</template>
