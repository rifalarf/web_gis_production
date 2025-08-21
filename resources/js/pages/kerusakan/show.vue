<script setup lang="ts">
import { ref, onMounted, computed }  from 'vue'
import AppLayout                     from '@/layouts/AppLayout.vue'
import LeafletMap                    from '@/components/LeafletMap.vue'
import { Head }                      from '@inertiajs/vue3'
import type { Feature, FeatureCollection } from 'geojson'
import type { BreadcrumbItem }       from '@/types'
import { usePage } from '@inertiajs/vue3'




/* ---------- NEW: viewer.js ---------- */
import Viewer from 'viewerjs'
import 'viewerjs/dist/viewer.css'

/* ---------- props ---------- */

const props = defineProps<{
  marker        : Feature
  lines         : FeatureCollection
  info          : { sta:string|null; nama_ruas:string; image:string|null }
}>()

/* ---------- lat / lon & Street-View ---------- */
const [lon, lat] = (props.marker.geometry as any).coordinates as [number,number]
const streetURL = `https://www.google.com/maps?q=&layer=c&cbll=${lat},${lon}`

/* ---------- breadcrumbs ---------- */
const prop = (props.marker.properties ?? {}) as { id?:number; ruas_code?:string }
const breadcrumbs: BreadcrumbItem[] = [
  { title:'Dashboard',           href:'/' },
  { title:'Titik Kerusakan',href:'/kerusakan' },
  { title:`ID #${prop.id ?? props.marker.id}`, href:'#' },
]

/* ---------- single-pin GeoJSON ---------- */
const pinGJ:FeatureCollection = { type:'FeatureCollection', features:[props.marker] }

/* ---------- mount Viewer.js when image exists ---------- */
const imgEl = ref<HTMLImageElement>()
onMounted(() => {
  if (imgEl.value) {                    // only when image is present
    // eslint-disable-next-line no-new
    new Viewer(imgEl.value, {
      navbar : false,                   // hide film-strip (only 1 img)
      title  : false,                   // no caption
      toolbar: {
        zoomIn: 1, zoomOut: 1, oneToOne: 1,
        reset: 1, rotateLeft: 1, rotateRight: 1,
        flipHorizontal: 1, flipVertical: 1,
        fullscreen: 1,
      },
      hidden() { document.body.style.overflow='' },   // restore scroll
      shown () { document.body.style.overflow='hidden' },
    })
  }
})
const auth = usePage().props.auth
</script>


<template>
  <Head :title="`Ruas ${prop.id ?? props.marker.id}`"/>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4 lg:flex-row">

      <!-- map -->
      <div class="relative flex-1 min-h-[70vh] rounded-xl border dark:border-sidebar-border">
        <LeafletMap
          :geojson="props.lines"
          :points-geojson="pinGJ"
          :autoFit="true"
          :showLegend="true"
          :detailPopups="true"
          :hideDelete="true"
          class="absolute inset-0 rounded-b-xl"
        />
      </div>

      <!-- ▸ info card -->
      <div class="w-full lg:w-1/4">
        <div class="space-y-3 rounded-xl border p-6 text-sm dark:border-sidebar-border">

            <!-- thumbnail (Viewer.js will hijack click) -->
          <img
            v-if="props.info.image"
            :src="props.info.image"
            ref="imgEl"
            class="mt-3 w-full max-h-60 cursor-zoom-in rounded object-cover"
          />

          <div><strong>Nama Ruas:</strong> {{ props.info.nama_ruas }}</div>
          <div><strong>STA:</strong> {{ props.info.sta ?? '−' }}</div>

          <div><strong>Koordinat:</strong> {{ lat.toFixed(6) }}, {{ lon.toFixed(6) }}</div>
          <div class="flex flex-col sm:flex-row gap-2 pt-2">
            <a
                :href="`/ruas-jalan/${prop.ruas_code ?? ''}`"
                class="w-full sm:w-1/2 inline-flex justify-center items-center rounded-md bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium px-3 py-1 transition-all"
            >
                Lihat Ruas
            </a>
            <a
                :href="streetURL"
                target="_blank"
                class="w-full sm:w-1/2 inline-flex justify-center items-center rounded-md bg-amber-500 text-white hover:bg-amber-600 text-sm font-medium px-3 py-1 transition-all"
            >
                Street View
            </a>
            </div>

        </div>
	<a
          v-if="auth?.user"
          :href="`/kerusakan/${prop.id ?? props.marker.id}/edit`"
          class="mt-2 inline-block text-yellow-600 hover:underline text-sm"
       	>
          Edit
        </a>
      </div>
    </div>
  </AppLayout>
</template>



