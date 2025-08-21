<script setup lang="ts">
/* ───────── imports ───────── */
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'          /* 👈  add this */
import AppLayout      from '@/layouts/AppLayout.vue'
import LeafletMap     from '@/components/LeafletMap.vue'
import type { BreadcrumbItem } from '@/types'
import type { FeatureCollection } from 'geojson'

import EasyDataTable  from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Eye, Search } from 'lucide-vue-next'

/* ───────── props FIRST (so it’s in scope) ───────── */
const props = defineProps<{
  ruas: { code: string; nm_ruas: string; kecamatan: string; panjang: string }
  geojson: FeatureCollection
  markersGeojson: FeatureCollection
  markerRows: { id: number; sta: string; lat: number; lon: number }[]
}>()

/* ───────── breadcrumbs ───────── */
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard',         href: '/' },
  { title: 'Daftar Jalan', href: '/ruas-jalan' },
  { title: props.ruas.code, href: `/ruas-jalan/${props.ruas.code}` },
]

/* ───────── table config ───────── */
const ktHeaders = [
  { text:'No',        value:'no', width:60,  sortable:true  },
  { text:'STA',       value:'sta',          sortable:true  },
  { text:'Koordinat', value:'coord',        sortable:true  },
  { text:'Aksi',      value:'action',width:90,sortable:false},
]

const ktRows = computed(() =>
  props.markerRows.map((r,i)=>({
    no:i+1,
    sta:r.sta??'–',
    coord:`${r.lat.toFixed(6)}, ${r.lon.toFixed(6)}`,
    id:r.id,
  }))
)

const ktSearch = ref('')
</script>


<template>
  <Head :title="`Ruas ${props.ruas.code}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-col gap-4 rounded-xl p-4">

      <!-- Map -->
      <div class="relative min-h-[70vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <LeafletMap
            :geojson="props.geojson"
            :points-geojson="props.markersGeojson"
            :autoFit="true"
            :showLegend="true"
            :detailPopups="true"
            class="absolute inset-0 rounded-b-xl"
        />
      </div>

      <!-- info card -->
    <div class="rounded-xl border p-4 dark:border-sidebar-border">
    <h2 class="text-lg font-semibold mb-2">
        CODE: {{ props.ruas.code }}
    </h2>
    <CardContent>
    <p><strong>Nama Ruas:</strong> {{ props.ruas.nm_ruas }}</p>
    <p><strong>Panjang:</strong> {{ Number(props.ruas.panjang ?? 0).toFixed(2) }} km</p>
    <p><strong>Kecamatan:</strong> {{ props.ruas.kecamatan ?? '−' }}</p>
    </CardContent>
    </div>

      <!-- ▼ kerusakan table (search + paging) -->
        <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">

        <!-- search bar -->
        <div class="mb-4 flex items-center">
            <div class="relative w-full sm:w-1/2 lg:w-1/3">
            <input v-model="ktSearch"
                    placeholder="Search…"
                    class="w-full rounded-lg border px-4 py-2 pl-10 shadow-sm
                            placeholder-gray-400 dark:bg-neutral-800 dark:border-neutral-600 dark:text-white" />
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"/>
            </div>
        </div>

        <!-- @ts-expect-error  easy-table ts quirks -->
        <EasyDataTable
            class="w-full"
            :headers="ktHeaders"
            :items="ktRows"
            :search-value="ktSearch"
            :rows-per-page="10"
            alternating
            header-text-direction="left"
            table-class="border-collapse text-base"
            body-row-class="border-t border-gray-200 dark:border-gray-700"
            style="--easy-table-body-row-height   : 50px;
            --easy-table-header-height : 50px;
            --easy-table-footer-height : 50px;"
        >
            <!-- action button -->
            <!-- @vue-ignore -->
            <template #item-action="{ id }">
            <a :href="`/kerusakan/${id}`"
                class="flex justify-center text-blue-600 hover:text-blue-800">
                <Eye :size="20"/>
            </a>
            </template>
        </EasyDataTable>
        </div>
    </div>
  </AppLayout>
</template>

<style scoped>
::v-deep(.vue3-easy-data-table__body td),
::v-deep(.vue3-easy-data-table__header .header-text),
::v-deep(.vue3-easy-data-table__footer),
::v-deep(.vue3-easy-data-table__footer *) {
  font-size  : 1rem;   /* Tailwind text-base */
  line-height: 1.5rem; /* Tailwind leading-6 */
}
</style>
