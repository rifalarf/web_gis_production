<script setup lang="ts">
import NavFooter    from '@/components/NavFooter.vue'
import NavMain      from '@/components/NavMain.vue'
import NavUser      from '@/components/NavUser.vue'
import {
  Sidebar, SidebarContent, SidebarFooter,
  SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem
} from '@/components/ui/sidebar'
import AppLogo      from './AppLogo.vue'

import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import { Map, Route, MapPinX, ChartPie, Settings } from 'lucide-vue-next'
import { computed } from 'vue'

/* ---------- auth state ---------- */
const page = usePage()
const auth = computed(() => page.props.auth)

/* ---------- main nav (unchanged) ---------- */
const mainNavItems: NavItem[] = [
  { title:'Peta',           href:'/',            icon:Map   },
  { title:'Daftar Jalan',   href:'/ruas-jalan',  icon:Route },
  { title:'Titik Kerusakan',href:'/kerusakan',   icon:MapPinX},
  { title:'Statistik',      href:'/statistik',   icon:ChartPie },
]

/* ---------- footer nav master list ---------- */
const baseFooterItems: (NavItem & { show:'guest'|'auth'|'all' })[] = [
  { title:'Settings', href:'/settings/appearance', icon:Settings, show:'guest' },
  // Add more items here, with show:'auth' | 'guest' | 'all'
]

/* ---------- guest / auth filtering ---------- */
const footerNavItems = computed(() =>
  baseFooterItems.filter(i =>
    i.show === 'all' ||
    (i.show === 'guest' && !auth.value.user) ||
    (i.show === 'auth'  &&  auth.value.user)
  )
)
</script>


<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
